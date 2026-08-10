<?php

namespace App\Services;

use App\Models\Business;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly CouponService $couponService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function create(Business $business, array $data, ?User $user = null): Order
    {
        return DB::transaction(function () use ($business, $data, $user) {
            $tableId = $data['table_id'] ?? null;
            $roomId = $data['room_id'] ?? null;
            $servicePointId = $data['service_point_id'] ?? null;

            $this->assertLocationBelongsToBusiness($business->id, $tableId, $roomId, $servicePointId);

            [$lineItems, $subtotal, $tax] = $this->buildLineItems($business->id, $data['items']);

            $coupon = $this->couponService->findValidCoupon(
                $business->id,
                $data['coupon_code'] ?? null,
                $subtotal,
                $user,
            );

            $discount = $coupon ? $this->couponService->discountFor($coupon, $subtotal) : 0.0;
            $total = round(max(0, $subtotal + $tax - $discount), 2);

            $order = Order::create([
                'business_id' => $business->id,
                'table_id' => $tableId,
                'room_id' => $roomId,
                'service_point_id' => $servicePointId,
                'user_id' => $user?->id,
                'coupon_id' => $coupon?->id,
                'order_number' => $this->generateOrderNumber(),
                'order_type' => $data['order_type'] ?? ($roomId ? 'room_service' : 'dine_in'),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'payment_status' => 'unpaid',
                'order_status' => 'preparing',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            if (! empty($data['payment_method'])) {
                Payment::create([
                    'order_id' => $order->id,
                    'business_id' => $business->id,
                    'payment_method' => $data['payment_method'],
                    'payment_gateway' => $data['payment_method'] === 'razorpay' ? 'razorpay' : null,
                    'amount' => $total,
                    'status' => 'pending',
                ]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            $this->notificationService->notifyBusiness(
                $business->id,
                'order_created',
                'New order received',
                "Order {$order->order_number} is pending.",
                ['order_id' => $order->id, 'order_number' => $order->order_number],
            );

            $this->auditLogService->record($user, $business->id, 'order.created', $order, [
                'total' => $total,
                'source' => $user ? 'authenticated_api' : 'public_qr',
            ]);

            return $order->load(['items.menuItem.presetImage', 'payments']);
        });
    }

    public function updateStatus(Order $order, string $status, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $status, $user) {
            $oldStatus = $order->order_status;
            $order->update(['order_status' => $status]);
            $itemStatus = $this->itemStatusForOrderStatus($status);
            if ($itemStatus) {
                $order->items()->update(['status' => $itemStatus]);
            }

            if ($status === 'completed') {
                $order->update(['payment_status' => $order->payment_status === 'unpaid' ? 'pending' : $order->payment_status]);
            }

            $this->notificationService->notifyBusiness(
                $order->business_id,
                'order_status_changed',
                'Order status updated',
                "Order {$order->order_number} changed to {$status}.",
                ['order_id' => $order->id, 'old_status' => $oldStatus, 'new_status' => $status],
            );

            $this->auditLogService->record($user, $order->business_id, 'order.status_changed', $order, [
                'old_status' => $oldStatus,
                'new_status' => $status,
            ]);

            $this->releaseLocationIfSettled($order);

            return $order->fresh(['items.menuItem.presetImage', 'payments', 'restaurantTable', 'room', 'servicePoint', 'user']);
        });
    }

    public function addItem(Order $order, array $data, ?User $user = null, bool $includeGlobalMenuItems = false): Order
    {
        return DB::transaction(function () use ($order, $data, $user, $includeGlobalMenuItems) {
            $order = $order->fresh(['coupon', 'payments']);

            if (in_array($order->order_status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'order' => ['Items cannot be added to completed or cancelled orders.'],
                ]);
            }

            [$lineItems, $addedSubtotal, $addedTax] = $this->buildLineItems($order->business_id, [$data], $includeGlobalMenuItems);
            $lineItem = $order->items()->create($lineItems[0]);

            $subtotal = round((float) $order->subtotal + $addedSubtotal, 2);
            $tax = round((float) $order->tax + $addedTax, 2);
            $discount = $order->coupon
                ? $this->couponService->discountFor($order->coupon, $subtotal)
                : min((float) $order->discount, $subtotal);
            $total = round(max(0, $subtotal + $tax - $discount), 2);

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'payment_status' => $order->payment_status === 'paid' ? 'pending' : $order->payment_status,
            ]);
            $this->syncOrderStatusFromItems($order);

            if ($order->payment_status !== 'paid') {
                $order->payments()
                    ->whereIn('status', ['pending', 'failed'])
                    ->update(['amount' => $total]);
            }

            $this->notificationService->notifyBusiness(
                $order->business_id,
                'order_item_added',
                'Order item added',
                "{$lineItem->quantity} x {$lineItem->item_name} was added to {$order->order_number}.",
                ['order_id' => $order->id, 'order_item_id' => $lineItem->id],
            );

            $this->auditLogService->record($user, $order->business_id, 'order.item_added', $order, [
                'order_item_id' => $lineItem->id,
                'item_name' => $lineItem->item_name,
                'quantity' => $lineItem->quantity,
                'total' => $lineItem->total,
            ]);

            return $order->fresh(['items.menuItem.presetImage', 'payments', 'restaurantTable', 'room', 'servicePoint', 'user']);
        });
    }

    public function updateItemStatus(Order $order, OrderItem $item, string $status, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $item, $status, $user) {
            if ($item->order_id !== $order->id) {
                throw ValidationException::withMessages([
                    'item' => ['Order item does not belong to this order.'],
                ]);
            }

            if (in_array($order->order_status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'order' => ['Items cannot be updated on completed or cancelled orders.'],
                ]);
            }

            $oldStatus = $item->status ?: $order->order_status;
            $item->update(['status' => $status]);
            $this->syncOrderStatusFromItems($order);

            $this->notificationService->notifyBusiness(
                $order->business_id,
                'order_item_status_changed',
                'Order item updated',
                "{$item->item_name} changed to {$status} on {$order->order_number}.",
                ['order_id' => $order->id, 'order_item_id' => $item->id, 'old_status' => $oldStatus, 'new_status' => $status],
            );

            $this->auditLogService->record($user, $order->business_id, 'order.item_status_changed', $order, [
                'order_item_id' => $item->id,
                'item_name' => $item->item_name,
                'old_status' => $oldStatus,
                'new_status' => $status,
            ]);

            return $order->fresh(['items.menuItem.presetImage', 'payments', 'restaurantTable', 'room', 'servicePoint', 'user']);
        });
    }

    public function updatePayment(Order $order, array $data, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $data, $user) {
            $order = $order->fresh(['payments']);
            $paymentStatus = $data['payment_status'];
            $method = $data['payment_method'] ?? $order->payments()->latest()->value('payment_method') ?? 'cash';
            $amount = round((float) ($data['amount'] ?? $order->total), 2);

            if ($paymentStatus === 'unpaid') {
                $order->update(['payment_status' => 'unpaid']);
                $payment = null;
            } else {
                $gatewayStatus = $paymentStatus === 'paid' ? 'paid' : ($paymentStatus === 'refunded' ? 'refunded' : 'pending');
                $payment = $order->payments()->latest()->first();

                if (! $payment || $payment->status === 'paid' && $gatewayStatus !== 'paid') {
                    $payment = new Payment([
                        'order_id' => $order->id,
                        'business_id' => $order->business_id,
                    ]);
                }

                $payment->fill([
                    'business_id' => $order->business_id,
                    'payment_method' => $method,
                    'payment_gateway' => $method === 'razorpay' ? 'razorpay' : null,
                    'transaction_id' => $data['transaction_id'] ?? $payment->transaction_id,
                    'amount' => $amount,
                    'status' => $gatewayStatus,
                    'paid_at' => $gatewayStatus === 'paid' ? ($payment->paid_at ?: now()) : null,
                ])->save();

                $order->update(['payment_status' => $paymentStatus === 'refunded' ? 'pending' : $paymentStatus]);
            }

            $this->notificationService->notifyBusiness(
                $order->business_id,
                'order_payment_updated',
                'Payment updated',
                "Order {$order->order_number} payment changed to {$paymentStatus}.",
                ['order_id' => $order->id, 'payment_id' => $payment?->id, 'payment_status' => $paymentStatus],
            );

            $this->auditLogService->record($user, $order->business_id, 'order.payment_updated', $order, [
                'payment_id' => $payment?->id,
                'payment_method' => $method,
                'payment_status' => $paymentStatus,
                'amount' => $amount,
            ]);

            $this->releaseLocationIfSettled($order);

            return $order->fresh(['items.menuItem.presetImage', 'payments', 'restaurantTable', 'room', 'servicePoint', 'user']);
        });
    }

    private function releaseLocationIfSettled(Order $order): void
    {
        $order->refresh();

        if ($order->order_status !== 'completed' || $order->payment_status !== 'paid') {
            return;
        }

        if ($order->service_point_id) {
            if (! $this->hasLiveOrdersForLocation($order, 'service_point_id', $order->service_point_id)) {
                ServicePoint::whereKey($order->service_point_id)->update([
                    'status' => 'available',
                    'order_number' => null,
                    'amount' => 0,
                    'items' => null,
                ]);
            }
        }

        if ($order->table_id) {
            if (! $this->hasLiveOrdersForLocation($order, 'table_id', $order->table_id)) {
                RestaurantTable::whereKey($order->table_id)->update(['status' => 'available']);
            }
        }

        if ($order->room_id) {
            if (! $this->hasLiveOrdersForLocation($order, 'room_id', $order->room_id)) {
                Room::whereKey($order->room_id)->update(['status' => 'available']);
            }
        }
    }

    private function hasLiveOrdersForLocation(Order $order, string $column, int $locationId): bool
    {
        return Order::where('business_id', $order->business_id)
            ->where($column, $locationId)
            ->live()
            ->exists();
    }

    private function assertLocationBelongsToBusiness(int $businessId, ?int $tableId, ?int $roomId, ?int $servicePointId): void
    {
        if ($tableId && ! RestaurantTable::where('business_id', $businessId)->whereKey($tableId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['table_id' => ['Table does not belong to this business or is inactive.']]);
        }

        if ($roomId && ! Room::where('business_id', $businessId)->whereKey($roomId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['room_id' => ['Room does not belong to this business or is inactive.']]);
        }

        if ($servicePointId && ! ServicePoint::where('business_id', $businessId)->whereKey($servicePointId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['service_point_id' => ['Service point does not belong to this business or is inactive.']]);
        }
    }

    private function buildLineItems(int $businessId, array $items, bool $includeGlobalMenuItems = false): array
    {
        $lineItems = [];
        $subtotal = 0.0;
        $tax = 0.0;

        foreach ($items as $itemData) {
            $menuItem = MenuItem::with('variants')
                ->where(function ($query) use ($businessId, $includeGlobalMenuItems) {
                    $query->where('business_id', $businessId);

                    if ($includeGlobalMenuItems) {
                        $query->orWhereNull('business_id');
                    }
                })
                ->where('status', 'active')
                ->where('availability', true)
                ->where('stock', true)
                ->find($itemData['menu_item_id']);

            if (! $menuItem) {
                throw ValidationException::withMessages(['items' => ['One or more menu items are unavailable.']]);
            }

            $variant = null;
            if (! empty($itemData['variant_id'])) {
                $variant = $menuItem->variants->firstWhere('id', (int) $itemData['variant_id']);

                if (! $variant) {
                    throw ValidationException::withMessages(['items' => ['Selected variant does not belong to the menu item.']]);
                }
            }

            if (! $variant && (float) $menuItem->price <= 0 && $menuItem->variants->isNotEmpty()) {
                $variant = $menuItem->variants->sortBy('price')->first();
            }

            $price = round((float) ($variant?->price ?? $menuItem->price), 2);
            $quantity = (int) $itemData['quantity'];
            $lineSubtotal = round($price * $quantity, 2);
            $lineTax = round($lineSubtotal * ((float) $menuItem->tax_rate / 100), 2);
            $lineTotal = round($lineSubtotal + $lineTax, 2);

            $lineItems[] = [
                'menu_item_id' => $menuItem->id,
                'menu_item_variant_id' => $variant?->id,
                'item_name' => $menuItem->name,
                'variant_label' => $variant?->label,
                'price' => $price,
                'quantity' => $quantity,
                'status' => $itemData['status'] ?? 'preparing',
                'tax' => $lineTax,
                'discount' => 0,
                'total' => $lineTotal,
                'special_instructions' => $itemData['special_instructions'] ?? null,
            ];

            $subtotal += $lineSubtotal;
            $tax += $lineTax;
        }

        return [$lineItems, round($subtotal, 2), round($tax, 2)];
    }

    private function syncOrderStatusFromItems(Order $order): void
    {
        if (in_array($order->order_status, ['completed', 'cancelled'], true)) {
            return;
        }

        $statuses = $order->items()
            ->get(['status'])
            ->map(fn (OrderItem $item) => $item->status ?: $this->itemStatusForOrderStatus($order->order_status) ?: 'preparing')
            ->values();

        if ($statuses->isEmpty()) {
            return;
        }

        $newStatus = $this->aggregateItemStatuses($statuses);

        if ($newStatus !== $order->order_status) {
            $order->update(['order_status' => $newStatus]);
        }
    }

    private function aggregateItemStatuses(Collection $statuses): string
    {
        if ($statuses->every(fn (string $status) => $status === 'cancelled')) {
            return 'cancelled';
        }

        if ($statuses->every(fn (string $status) => in_array($status, ['served', 'cancelled'], true))) {
            return 'served';
        }

        if ($statuses->every(fn (string $status) => in_array($status, ['ready', 'served', 'cancelled'], true))) {
            return 'ready';
        }

        return 'preparing';
    }

    private function itemStatusForOrderStatus(string $status): ?string
    {
        if ($status === 'completed') {
            return 'served';
        }

        return in_array($status, OrderItem::STATUSES, true) ? $status : null;
    }

    private function generateOrderNumber(): string
    {
        $nextNumber = ((int) Order::max('id')) + 1001;

        do {
            $number = 'ORD-'.$nextNumber;
            $nextNumber++;
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
