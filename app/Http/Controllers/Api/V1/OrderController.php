<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Orders\DirectOrderRequest;
use App\Http\Requests\Api\V1\Orders\StoreOrderRequest;
use App\Http\Requests\Api\V1\Orders\UpdateOrderItemStatusRequest;
use App\Http\Requests\Api\V1\Orders\UpdateOrderStatusRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->filteredQuery($request)
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(OrderResource::collection($orders), 'Orders');
    }

    public function all(Request $request): JsonResponse
    {
        $orders = $this->filteredQuery($request)
            ->latest()
            ->get();

        return $this->success(OrderResource::collection($orders), 'All orders');
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        $order = $this->orderService->create($business, $request->validated(), $request->user());

        return $this->success(new OrderResource($order), 'Order created successfully', 201);
    }

    public function directStore(DirectOrderRequest $request): JsonResponse
    {
        $business = $this->business($request);

        if (! $business) {
            return $this->error('Business profile not found', 404);
        }

        $data = $request->validated();
        $data['order_type'] ??= 'takeaway';

        $order = $this->orderService->create($business, $data, $request->user());

        return $this->success(new OrderResource($order), 'Direct order created successfully', 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new OrderResource($order->load(['items.menuItem.presetImage', 'payments'])), 'Order details');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        if ($order->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $data = $request->validated();

        if (! empty($data['status'])) {
            $order = $this->orderService->updateStatus($order, $data['status'], $request->user());
        }

        if (! empty($data['items'])) {
            $order->loadMissing('items');
        }

        foreach ($data['items'] ?? [] as $itemUpdate) {
            $item = $order->items->firstWhere('id', $itemUpdate['id']);

            if (! $item) {
                return $this->error('Order item does not belong to this order', 404);
            }

            $order = $this->orderService->updateItemStatus($order, $item, $itemUpdate['status'], $request->user());
        }

        return $this->success(new OrderResource($order), 'Order status updated');
    }

    public function updateItemStatus(UpdateOrderItemStatusRequest $request, Order $order, OrderItem $item): JsonResponse
    {
        if ($order->business_id !== $this->businessId($request) || $item->order_id !== $order->id) {
            return $this->error('Resource not found', 404);
        }

        $order = $this->orderService->updateItemStatus($order, $item, $request->validated('status'), $request->user());

        return $this->success(new OrderResource($order), 'Order item status updated');
    }

    public function active(Request $request): JsonResponse
    {
        $orders = $this->baseQuery($request)
            ->whereIn('order_status', Order::ACTIVE_STATUSES)
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(OrderResource::collection($orders), 'Active orders');
    }

    public function byStatus(Request $request, string $status): JsonResponse
    {
        if (! in_array($status, Order::STATUSES, true)) {
            return $this->error('Invalid order status', 422);
        }

        $orders = $this->baseQuery($request)
            ->where('order_status', $status)
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(OrderResource::collection($orders), ucfirst($status).' orders');
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        if (in_array($order->order_status, ['completed', 'cancelled'], true)) {
            return $this->error('Order cannot be cancelled from its current status', 422);
        }

        $order = $this->orderService->updateStatus($order, 'cancelled', $request->user());

        return $this->success(new OrderResource($order), 'Order cancelled');
    }

    private function baseQuery(Request $request)
    {
        return Order::with(['items.menuItem.presetImage', 'payments'])
            ->where('business_id', $this->businessId($request))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')));
    }

    private function filteredQuery(Request $request)
    {
        return $this->baseQuery($request)
            ->when($request->filled('status'), fn ($query) => $query->where('order_status', $request->input('status')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->input('payment_status')))
            ->when($request->filled('order_type'), fn ($query) => $query->where('order_type', $request->input('order_type')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%');
                });
            });
    }
}
