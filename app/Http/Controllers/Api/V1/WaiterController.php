<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\RestaurantTableResource;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaiterController extends ApiController
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function dashboard(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request);

        return $this->success([
            'assigned_tables' => RestaurantTableResource::collection(
                RestaurantTable::where('business_id', $businessId)->where('is_active', true)->orderBy('name')->get()
            ),
            'active_orders' => OrderResource::collection(
                Order::with('items.menuItem.presetImage')->where('business_id', $businessId)->whereIn('order_status', Order::ACTIVE_STATUSES)->latest()->limit(20)->get()
            ),
        ], 'Waiter dashboard');
    }

    public function markServed(Request $request, Order $order): JsonResponse
    {
        if ($order->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $order = $this->orderService->updateStatus($order, 'served', $request->user());

        return $this->success(new OrderResource($order), 'Order marked served');
    }
}
