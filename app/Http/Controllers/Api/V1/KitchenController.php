<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KitchenController extends ApiController
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function active(Request $request): JsonResponse
    {
        $orders = Order::with('items.menuItem.presetImage')
            ->where('business_id', $this->businessId($request))
            ->whereIn('order_status', ['pending', 'confirmed', 'preparing'])
            ->oldest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(OrderResource::collection($orders), 'Kitchen orders');
    }

    public function accept(Request $request, Order $order): JsonResponse
    {
        return $this->transition($request, $order, 'confirmed');
    }

    public function preparing(Request $request, Order $order): JsonResponse
    {
        return $this->transition($request, $order, 'preparing');
    }

    public function ready(Request $request, Order $order): JsonResponse
    {
        return $this->transition($request, $order, 'ready');
    }

    public function completed(Request $request, Order $order): JsonResponse
    {
        return $this->transition($request, $order, 'completed');
    }

    private function transition(Request $request, Order $order, string $status): JsonResponse
    {
        if ($order->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $order = $this->orderService->updateStatus($order, $status, $request->user());

        return $this->success(new OrderResource($order), 'Kitchen order updated');
    }
}
