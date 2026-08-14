<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Customers\StoreCustomerOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\Customers\ScannerContextResolver;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class CustomerOrderController extends ApiController
{
    public function __construct(
        private readonly ScannerContextResolver $scannerContextResolver,
        private readonly OrderService $orderService,
    ) {
    }

    public function store(StoreCustomerOrderRequest $request, string $qr): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);
        $data = $request->validated();
        $data['order_type'] = $context['type'] === 'room' ? 'room_service' : 'dine_in';
        $data['table_id'] = $context['type'] === 'table' ? $context['id'] : null;
        $data['room_id'] = $context['type'] === 'room' ? $context['id'] : null;
        $data['service_point_id'] = $context['type'] === 'service_point' ? $context['id'] : null;

        $order = $this->orderService->create($business, $data);
        $order->load(['items.menuItem.presetImage', 'payments', 'restaurantTable', 'room', 'servicePoint']);

        return $this->success([
            'context' => $context,
            'order' => new OrderResource($order),
        ], 'Order created successfully', 201);
    }

    public function show(string $orderNumber): JsonResponse
    {
        $order = Order::with(['items.menuItem.presetImage', 'payments', 'restaurantTable', 'room', 'servicePoint'])
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new OrderResource($order), 'Order details');
    }
}
