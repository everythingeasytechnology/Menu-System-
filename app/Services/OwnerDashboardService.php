<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Collection;

class OwnerDashboardService
{
    public const STATUS_LABELS = [
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'served' => 'Served',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public function businessFor(User $user): Business
    {
        if ($user->business) {
            return $user->business;
        }

        $ownedBusiness = $user->ownedBusinesses()->latest()->first();
        if ($ownedBusiness) {
            $user->update(['business_id' => $ownedBusiness->id]);

            return $ownedBusiness;
        }

        $settings = BusinessSetting::first();
        $business = Business::create([
            'owner_user_id' => $user->id,
            'name' => $settings?->brand_name ?: 'EverythingEasy',
            'type' => 'restaurant',
            'email' => $settings?->business_email ?: $user->email,
            'address' => $settings?->address,
            'state' => $settings?->state,
            'country' => $settings?->country ?: 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        $user->update(['business_id' => $business->id]);

        return $business;
    }

    public function settingsFor(Business $business): BusinessSetting
    {
        return BusinessSetting::where('business_id', $business->id)->first()
            ?? BusinessSetting::first()
            ?? new BusinessSetting([
                'brand_name' => $business->name,
                'gst_enabled' => false,
                'cgst' => 2.5,
                'sgst' => 2.5,
            ]);
    }

    public function dashboardData(User $user): array
    {
        $business = $this->businessFor($user);
        $todayOrders = Order::where('business_id', $business->id)->whereDate('created_at', today());
        $todayBillable = (clone $todayOrders)->where('order_status', '!=', 'cancelled');
        $todayOrderCount = (clone $todayOrders)->count();
        $todaySales = (float) (clone $todayBillable)->sum('total');

        return [
            'business' => $business,
            'stats' => [
                'orders' => $todayOrderCount,
                'sales' => $todaySales,
                'average_order' => $todayOrderCount > 0 ? round($todaySales / $todayOrderCount, 2) : 0.0,
                'completed' => (clone $todayOrders)->whereIn('order_status', ['served', 'completed'])->count(),
                'pending' => (clone $todayOrders)->whereIn('order_status', ['preparing', 'ready'])->count(),
            ],
            'liveOrders' => $this->ordersFor($business, 100, true)->map(fn (Order $order) => $this->formatOrder($order))->values(),
            'statusOverview' => $this->statusOverview($business),
            'topItems' => $this->topSellingItems($business),
        ];
    }

    public function ordersPageData(User $user): array
    {
        $business = $this->businessFor($user);
        $orders = $this->ordersFor($business, 100, true)->map(fn (Order $order) => $this->formatOrder($order))->values();

        return [
            'business' => $business,
            'settings' => $this->settingsFor($business),
            'ordersPayload' => $orders,
            'selectedOrderId' => $orders->first()['id'] ?? null,
            'statuses' => self::STATUS_LABELS,
            'itemStatuses' => $this->itemStatuses(),
            'menuItemsPayload' => $this->menuItemsForOrderAdd($business),
        ];
    }

    public function itemStatuses(): array
    {
        return collect(OrderItem::STATUSES)
            ->map(fn (string $status) => [
                'value' => $status,
                'label' => self::STATUS_LABELS[$status] ?? ucfirst($status),
            ])
            ->values()
            ->all();
    }

    public function menuItemsForOrderAdd(Business $business): Collection
    {
        return MenuItem::with('variants')
            ->where(function ($query) use ($business) {
                $query->where('business_id', $business->id)
                    ->orWhereNull('business_id');
            })
            ->where('status', 'active')
            ->where('availability', true)
            ->where('stock', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (MenuItem $item) {
                $variants = $item->variants
                    ->sortBy('price')
                    ->map(fn ($variant) => [
                        'id' => $variant->id,
                        'label' => $variant->label,
                        'price' => (float) $variant->price,
                        'priceLabel' => 'Rs. '.number_format((float) $variant->price, 2),
                    ])
                    ->values();

                $basePrice = (float) ($item->price ?: ($variants->first()['price'] ?? 0));

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'price' => $basePrice,
                    'priceLabel' => 'Rs. '.number_format($basePrice, 2),
                    'variants' => $variants,
                ];
            })
            ->values();
    }

    public function ordersFor(Business $business, int $limit = 50, bool $activeOnly = false): Collection
    {
        return Order::with(['items', 'payments', 'restaurantTable', 'room', 'servicePoint', 'user', 'coupon'])
            ->where('business_id', $business->id)
            ->when($activeOnly, fn ($query) => $query->live())
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function formatOrder(Order $order): array
    {
        $order->loadMissing(['items', 'payments', 'restaurantTable', 'room', 'servicePoint', 'user', 'coupon']);

        $payment = $order->payments->sortByDesc('created_at')->first();
        $method = $payment?->payment_method;
        $items = $order->items->map(function (OrderItem $item) use ($order) {
            $label = $item->quantity.' x '.$item->item_name;
            if ($item->variant_label) {
                $label .= ' ('.$item->variant_label.')';
            }

            $itemStatus = $item->status ?: ($order->order_status === 'completed' ? 'served' : $order->order_status);
            if (! in_array($itemStatus, OrderItem::STATUSES, true)) {
                $itemStatus = 'pending';
            }

            return [
                'id' => $item->id,
                'name' => $item->item_name,
                'variantLabel' => $item->variant_label,
                'qty' => $item->quantity,
                'status' => $itemStatus,
                'statusLabel' => self::STATUS_LABELS[$itemStatus] ?? ucfirst($itemStatus),
                'label' => $label,
                'unitPrice' => (float) $item->price,
                'lineSubtotal' => round((float) $item->price * (int) $item->quantity, 2),
                'tax' => (float) $item->tax,
                'discount' => (float) $item->discount,
                'total' => (float) $item->total,
                'specialInstructions' => $item->special_instructions,
            ];
        })->values();

        return [
            'id' => $order->id,
            'orderNumber' => $order->order_number,
            'displayId' => $order->compactNumber(),
            'channel' => $this->channelLabel($order),
            'channelType' => str_replace('_', '-', $order->order_type),
            'type' => $order->order_type === 'takeaway' ? 'packed' : 'dine-in',
            'location' => $this->locationLabel($order),
            'customer' => $order->customer_name ?: 'Walk-in Customer',
            'phone' => $order->customer_phone ?: 'N/A',
            'email' => $order->user?->email,
            'amount' => 'Rs. '.number_format((float) $order->total, 2),
            'subtotal' => 'Rs. '.number_format((float) $order->subtotal, 2),
            'tax' => 'Rs. '.number_format((float) $order->tax, 2),
            'discount' => 'Rs. '.number_format((float) $order->discount, 2),
            'rawSubtotal' => (float) $order->subtotal,
            'rawTax' => (float) $order->tax,
            'rawDiscount' => (float) $order->discount,
            'rawTotal' => (float) $order->total,
            'couponCode' => $order->coupon?->code,
            'couponType' => $order->coupon?->type,
            'couponValue' => $order->coupon?->value,
            'payment' => $method ? ucfirst($method) : ucfirst($order->payment_status),
            'paymentMethod' => $method ?: 'cash',
            'paymentStatus' => $order->payment_status,
            'paymentLabel' => ucfirst($order->payment_status).($method ? ' via '.ucfirst($method) : ''),
            'status' => $order->order_status,
            'statusLabel' => self::STATUS_LABELS[$order->order_status] ?? ucfirst($order->order_status),
            'time' => $order->created_at?->format('h:i A') ?? '',
            'date' => $order->created_at?->format('d M Y') ?? '',
            'sortKey' => $order->created_at?->timestamp ?? 0,
            'elapsed' => $order->created_at?->diffForHumans() ?? '',
            'note' => $order->notes,
            'items' => $items,
            'itemCount' => $items->count(),
            'extra' => max(0, $items->count() - 2),
        ];
    }

    public function statusOverview(Business $business): Collection
    {
        $counts = Order::where('business_id', $business->id)
            ->whereDate('created_at', today())
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $total = max(1, (int) $counts->sum());

        return collect(self::STATUS_LABELS)->map(function (string $label, string $status) use ($counts, $total) {
            $count = (int) ($counts[$status] ?? 0);

            return [
                'status' => $status,
                'label' => $label,
                'count' => $count,
                'percentage' => round(($count / $total) * 100),
            ];
        })->values();
    }

    public function topSellingItems(Business $business, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.business_id', $business->id)
            ->whereDate('orders.created_at', today())
            ->where('orders.order_status', '!=', 'cancelled')
            ->selectRaw('order_items.item_name, SUM(order_items.quantity) as quantity, SUM(order_items.total) as revenue')
            ->groupBy('order_items.item_name')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->item_name,
                'quantity' => (int) $row->quantity,
                'revenue' => (float) $row->revenue,
            ]);
    }

    private function channelLabel(Order $order): string
    {
        return match ($order->order_type) {
            'room_service' => 'Room Service',
            'takeaway' => 'Packed',
            default => 'Dining',
        };
    }

    private function locationLabel(Order $order): string
    {
        if ($order->servicePoint) {
            return $order->servicePoint->name;
        }

        if ($order->restaurantTable) {
            return $order->restaurantTable->name;
        }

        if ($order->room) {
            return $order->room->name;
        }

        return $order->order_type === 'takeaway' ? 'Counter / Takeaway' : 'Direct Order';
    }
}
