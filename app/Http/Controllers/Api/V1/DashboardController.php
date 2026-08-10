<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AppNotification;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request);
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $orderSummary = Order::where('business_id', $businessId)
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as todays_orders,
                SUM(CASE WHEN order_status = "pending" THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN order_status IN ("confirmed", "preparing") THEN 1 ELSE 0 END) as preparing_orders,
                SUM(CASE WHEN order_status = "ready" THEN 1 ELSE 0 END) as ready_orders,
                SUM(CASE WHEN order_status = "served" THEN 1 ELSE 0 END) as served_orders,
                SUM(CASE WHEN order_status = "completed" THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN order_status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders,
                COALESCE(SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END), 0) as todays_revenue,
                COALESCE(AVG(CASE WHEN order_status != "cancelled" THEN total END), 0) as avg_order_value,
                COALESCE(AVG(CASE WHEN order_status = "completed" THEN TIMESTAMPDIFF(MINUTE, created_at, updated_at) END), 0) as avg_order_minutes
            ')
            ->first();

        $monthRevenue = (float) Order::where('business_id', $businessId)
            ->whereDate('created_at', '>=', $monthStart)
            ->where('payment_status', 'paid')
            ->sum('total');

        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $lastMonthEnd = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
        $lastMonthRevenue = (float) Order::where('business_id', $businessId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$lastMonthStart, $lastMonthEnd])
            ->where('payment_status', 'paid')
            ->sum('total');

        $monthGrowthPercent = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($monthRevenue > 0 ? 100.0 : 0.0);

        $revenueTrend = Order::where('business_id', $businessId)
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', now()->subDays(5)->toDateString())
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'revenue' => (float) $row->revenue])
            ->values();

        $menuSummary = MenuItem::where('business_id', $businessId)
            ->selectRaw('
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_menu_items,
                SUM(CASE WHEN stock = 0 OR availability = 0 OR status != "active" THEN 1 ELSE 0 END) as unavailable_items
            ')
            ->first();

        $tablesStatus = RestaurantTable::where('business_id', $businessId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $roomsStatus = Room::where('business_id', $businessId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $tablesTotal = (int) RestaurantTable::where('business_id', $businessId)->count();
        $tablesOccupied = (int) ($tablesStatus['occupied'] ?? 0);
        $tablesAvailable = (int) ($tablesStatus['available'] ?? 0);
        $tablesReserved = (int) ($tablesStatus['reserved'] ?? 0);

        $recentOrders = Order::with(['items', 'restaurantTable', 'room', 'servicePoint'])
            ->where('business_id', $businessId)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Order $order) => $this->formatRecentOrder($order))
            ->values();

        $topItems = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.business_id', $businessId)
            ->whereDate('orders.created_at', '>=', $monthStart)
            ->where('orders.order_status', '!=', 'cancelled')
            ->selectRaw('order_items.item_name, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.item_name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        $topQuantityMax = max(1, (int) ($topItems->first()->quantity ?? 1));
        $topItems = $topItems->map(fn ($row) => [
            'name' => $row->item_name,
            'sold' => (int) $row->quantity,
            'percentage' => (int) round(($row->quantity / $topQuantityMax) * 100),
        ])->values();

        $unreadNotifications = AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return $this->success([
            'todays_orders' => (int) $orderSummary->todays_orders,
            'pending_orders' => (int) $orderSummary->pending_orders,
            'preparing_orders' => (int) $orderSummary->preparing_orders,
            'ready_orders' => (int) $orderSummary->ready_orders,
            'served_orders' => (int) $orderSummary->served_orders,
            'completed_orders' => (int) $orderSummary->completed_orders,
            'cancelled_orders' => (int) $orderSummary->cancelled_orders,
            'todays_revenue' => (float) $orderSummary->todays_revenue,
            'month_revenue' => $monthRevenue,
            'month_growth_percent' => $monthGrowthPercent,
            'revenue_trend' => $revenueTrend,
            'avg_order_value' => round((float) $orderSummary->avg_order_value, 2),
            'avg_order_minutes' => (int) round((float) $orderSummary->avg_order_minutes),
            'active_menu_items' => (int) $menuSummary->active_menu_items,
            'unavailable_items' => (int) $menuSummary->unavailable_items,
            'tables_status' => $tablesStatus,
            'rooms_status' => $roomsStatus,
            'tables_summary' => [
                'total' => $tablesTotal,
                'occupied' => $tablesOccupied,
                'available' => $tablesAvailable,
                'reserved' => $tablesReserved,
            ],
            'recent_orders' => $recentOrders,
            'top_selling_items' => $topItems,
            'unread_notifications' => $unreadNotifications,
        ], 'Dashboard');
    }

    private function formatRecentOrder(Order $order): array
    {
        $location = $order->servicePoint?->name
            ?? $order->restaurantTable?->name
            ?? $order->room?->name
            ?? ucfirst(str_replace('_', ' ', $order->order_type ?? 'order'));

        $itemsSummary = $order->items
            ->map(fn (OrderItem $item) => $item->quantity.'x '.$item->item_name)
            ->implode(', ');

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'display_order_id' => $order->compactNumber(),
            'table' => $location,
            'items' => $itemsSummary,
            'total' => (float) $order->total,
            'status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'time' => $order->created_at?->format('h:i A'),
            'created_at' => $order->created_at?->toISOString(),
        ];
    }
}
