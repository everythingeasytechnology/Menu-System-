<?php

namespace App\Services;

use App\Models\Business;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use App\Models\User;
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
                'order_status' => 'pending',
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

            return $order->load(['items', 'payments']);
        });
    }

    public function updateStatus(Order $order, string $status, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $status, $user) {
            $oldStatus = $order->order_status;
            $order->update(['order_status' => $status]);

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

            return $order->fresh(['items', 'payments']);
        });
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

    private function buildLineItems(int $businessId, array $items): array
    {
        $lineItems = [];
        $subtotal = 0.0;
        $tax = 0.0;

        foreach ($items as $itemData) {
            $menuItem = MenuItem::with('variants')
                ->where('business_id', $businessId)
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

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
