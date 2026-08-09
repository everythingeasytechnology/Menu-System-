<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ServicePoint;
use App\Services\OrderService;
use App\Services\OwnerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderOperationsController extends Controller
{
    public function __construct(
        private readonly OwnerDashboardService $dashboardService,
        private readonly OrderService $orderService,
    ) {
    }

    public function index()
    {
        return view('orders', $this->dashboardService->ordersPageData(auth()->user()));
    }

    public function kitchenDisplay()
    {
        return view('kitchen-display', $this->dashboardService->ordersPageData(auth()->user()));
    }

    public function history(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $typeOptions = $this->orderTypeOptions($business->id);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'string', Rule::in(Order::STATUSES)],
            'payment_status' => ['nullable', 'string', Rule::in(['unpaid', 'pending', 'paid', 'refunded'])],
            'order_type' => ['nullable', 'string', Rule::in(array_keys($typeOptions))],
            'service_point_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
        ]);

        $query = Order::with(['items', 'payments', 'restaurantTable', 'room', 'servicePoint', 'user'])
            ->where('business_id', $business->id)
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['order_type'] ?? null, fn ($query, $type) => $query->where('order_type', $type))
            ->when($filters['service_point_id'] ?? null, fn ($query, $servicePointId) => $query->where('service_point_id', $servicePointId))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%')
                        ->orWhereHas('items', fn ($items) => $items->where('item_name', 'like', '%'.$search.'%'))
                        ->orWhereHas('servicePoint', function ($point) use ($search) {
                            $point->where('name', 'like', '%'.$search.'%')
                                ->orWhere('code', 'like', '%'.$search.'%');
                        });
                });
            });

        $totalOrders = (clone $query)->count();
        $billableQuery = (clone $query)->where('order_status', '!=', 'cancelled');
        $summary = [
            'orders' => $totalOrders,
            'gross_sales' => (float) (clone $billableQuery)->sum('total'),
            'paid_sales' => (float) (clone $query)->where('payment_status', 'paid')->sum('total'),
            'pending_collection' => (float) (clone $billableQuery)->whereIn('payment_status', ['unpaid', 'pending'])->sum('total'),
        ];

        $orders = $query
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();
        $servicePoints = ServicePoint::where('business_id', $business->id)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'category']);

        return view('order-history', [
            'business' => $business,
            'orders' => $orders,
            'filters' => $filters,
            'summary' => $summary,
            'statuses' => OwnerDashboardService::STATUS_LABELS,
            'paymentStatuses' => ['unpaid' => 'Unpaid', 'pending' => 'Pending', 'paid' => 'Paid', 'refunded' => 'Refunded'],
            'typeOptions' => $typeOptions,
            'servicePoints' => $servicePoints,
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());
        $limit = min(max((int) $request->input('limit', 50), 1), 150);
        $activeOnly = $request->boolean('active_only');

        return response()->json([
            'success' => true,
            'orders' => $this->dashboardService->ordersFor($business, $limit, $activeOnly)
                ->map(fn (Order $order) => $this->dashboardService->formatOrder($order))
                ->values(),
        ]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());
        abort_unless($order->business_id === $business->id, 404);

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(Order::STATUSES)],
        ]);

        $updatedOrder = $this->orderService->updateStatus($order, $data['status'], $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Order status updated.',
            'order' => $this->dashboardService->formatOrder($updatedOrder),
        ]);
    }

    public function addItem(Request $request, Order $order): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());
        abort_unless($order->business_id === $business->id, 404);

        try {
            $data = $request->validate([
                'menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
                'variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
                'quantity' => ['required', 'integer', 'min:1', 'max:99'],
                'special_instructions' => ['nullable', 'string', 'max:500'],
            ]);

            $updatedOrder = $this->orderService->addItem($order, $data, $request->user(), true);
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return response()->json([
                'success' => false,
                'message' => collect($errors)->flatten()->first() ?: 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to order.',
            'order' => $this->dashboardService->formatOrder($updatedOrder),
        ]);
    }

    public function updateItemStatus(Request $request, Order $order, OrderItem $item): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());
        abort_unless($order->business_id === $business->id && $item->order_id === $order->id, 404);

        try {
            $data = $request->validate([
                'status' => ['required', 'string', Rule::in(OrderItem::STATUSES)],
            ]);

            $updatedOrder = $this->orderService->updateItemStatus($order, $item, $data['status'], $request->user());
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return response()->json([
                'success' => false,
                'message' => collect($errors)->flatten()->first() ?: 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item status updated.',
            'order' => $this->dashboardService->formatOrder($updatedOrder),
        ]);
    }

    public function updatePayment(Request $request, Order $order): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());
        abort_unless($order->business_id === $business->id, 404);

        try {
            $data = $request->validate([
                'payment_status' => ['required', 'string', Rule::in(['unpaid', 'pending', 'paid', 'refunded'])],
                'payment_method' => ['nullable', 'string', Rule::in(Payment::METHODS)],
                'amount' => ['nullable', 'numeric', 'min:0'],
                'transaction_id' => ['nullable', 'string', 'max:255'],
            ]);

            $updatedOrder = $this->orderService->updatePayment($order, $data, $request->user());
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return response()->json([
                'success' => false,
                'message' => collect($errors)->flatten()->first() ?: 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment updated.',
            'order' => $this->dashboardService->formatOrder($updatedOrder),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $business = $this->dashboardService->businessFor($request->user());
        abort_unless($order->business_id === $business->id, 404);

        if (in_array($order->order_status, ['completed', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled from its current status.',
            ], 422);
        }

        $updatedOrder = $this->orderService->updateStatus($order, 'cancelled', $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled.',
            'order' => $this->dashboardService->formatOrder($updatedOrder),
        ]);
    }

    private function orderTypeOptions(int $businessId): array
    {
        $labels = [
            'dine_in' => 'Dining',
            'takeaway' => 'Packed / Takeaway',
            'room_service' => 'Room Service',
            'delivery' => 'Delivery',
        ];

        Order::where('business_id', $businessId)
            ->whereNotNull('order_type')
            ->distinct()
            ->pluck('order_type')
            ->each(function (string $type) use (&$labels) {
                $labels[$type] ??= ucwords(str_replace('_', ' ', $type));
            });

        return $labels;
    }
}
