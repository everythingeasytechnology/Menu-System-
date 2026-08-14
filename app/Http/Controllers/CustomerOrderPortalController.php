<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CustomerOrderExperienceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderPortalController extends Controller
{
    public function __construct(
        private readonly CustomerOrderExperienceService $customerOrderExperienceService,
    ) {
    }

    public function show(string $orderNumber)
    {
        $payload = $this->customerOrderExperienceService->payload($this->orderByNumber($orderNumber));

        return view('orders.customer-track', [
            'payload' => $payload,
        ]);
    }

    public function data(string $orderNumber): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->customerOrderExperienceService->payload($this->orderByNumber($orderNumber)),
        ]);
    }

    public function bill(Request $request, string $orderNumber)
    {
        return view('orders.customer-bill', [
            'payload' => $this->customerOrderExperienceService->payload($this->orderByNumber($orderNumber)),
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    private function orderByNumber(string $orderNumber): Order
    {
        return Order::with([
            'business',
            'items',
            'payments',
            'restaurantTable',
            'room',
            'servicePoint',
            'user',
            'coupon',
        ])->where('order_number', $orderNumber)->firstOrFail();
    }
}
