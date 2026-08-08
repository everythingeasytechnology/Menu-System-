<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\OrderResource;
use App\Models\AppNotification;
use App\Models\MenuItem;
use App\Models\Order;
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

        $orderSummary = Order::where('business_id', $businessId)
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as todays_orders,
                SUM(CASE WHEN order_status = "pending" THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN order_status = "preparing" THEN 1 ELSE 0 END) as preparing_orders,
                SUM(CASE WHEN order_status = "ready" THEN 1 ELSE 0 END) as ready_orders,
                SUM(CASE WHEN order_status = "completed" THEN 1 ELSE 0 END) as completed_orders,
                COALESCE(SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END), 0) as todays_revenue
            ')
            ->first();

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

        $recentOrders = Order::with('items')
            ->where('business_id', $businessId)
            ->latest()
            ->limit(10)
            ->get();

        $unreadNotifications = AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return $this->success([
            'todays_orders' => (int) $orderSummary->todays_orders,
            'pending_orders' => (int) $orderSummary->pending_orders,
            'preparing_orders' => (int) $orderSummary->preparing_orders,
            'ready_orders' => (int) $orderSummary->ready_orders,
            'completed_orders' => (int) $orderSummary->completed_orders,
            'todays_revenue' => (float) $orderSummary->todays_revenue,
            'active_menu_items' => (int) $menuSummary->active_menu_items,
            'unavailable_items' => (int) $menuSummary->unavailable_items,
            'tables_status' => $tablesStatus,
            'rooms_status' => $roomsStatus,
            'recent_orders' => OrderResource::collection($recentOrders),
            'unread_notifications' => $unreadNotifications,
        ], 'Dashboard');
    }
}
