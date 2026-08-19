<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Customers\StoreCustomerOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Business;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CustomerOrderExperienceService;
use App\Services\Customers\ScannerContextResolver;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class CustomerOrderController extends ApiController
{
    public function __construct(
        private readonly ScannerContextResolver $scannerContextResolver,
        private readonly OrderService $orderService,
        private readonly CustomerOrderExperienceService $customerOrderExperienceService,
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

        $existingOrder = $this->activeOrderForContext($business, $context);
        $wasUpdated = $existingOrder !== null;

        if ($existingOrder) {
            $order = $this->orderService->addItems($existingOrder, $data['items']);
            $this->syncCustomerDetails($order, $data);
            $order->refresh();
        } else {
            $order = $this->orderService->create($business, $data);
        }

        $order->load(['items.menuItem.presetImage', 'payments', 'restaurantTable', 'room', 'servicePoint']);
        $this->customerOrderExperienceService->sendConfirmationIfPossible($order, $wasUpdated);

        return $this->success([
            'context' => $context,
            'order' => new OrderResource($order),
            'links' => $this->customerOrderExperienceService->links($order),
            'mode' => $wasUpdated ? 'updated' : 'created',
            'merged_into_existing_order' => $wasUpdated,
        ], $wasUpdated ? 'Items added to existing order successfully' : 'Order created successfully', $wasUpdated ? 200 : 201);
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

    public function occupancy(string $qr): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);
        $order = $this->activeOrderForContext($business, $context)?->loadMissing(['items.menuItem.presetImage', 'payments']);

        return $this->success([
            'context' => $context,
            'occupied' => $order !== null,
            'status' => $order ? 'occupied' : 'available',
            'order' => $order ? $this->occupancyOrderPayload($order) : null,
        ], 'Scanner occupancy status');
    }

    private function activeOrderForContext(Business $business, array $context): ?Order
    {
        return $this->ordersForContext($business, $context)
            ->live()
            ->oldest('created_at')
            ->first();
    }

    private function ordersForContext(Business $business, array $context): Builder
    {
        return Order::query()
            ->where('business_id', $business->id)
            ->when($context['type'] === 'table', fn ($query) => $query->where('table_id', $context['id']))
            ->when($context['type'] === 'room', fn ($query) => $query->where('room_id', $context['id']))
            ->when($context['type'] === 'service_point', fn ($query) => $query->where('service_point_id', $context['id']));
    }

    private function occupancyOrderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'display_order_id' => $order->compactNumber(),
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'subtotal' => (float) $order->subtotal,
            'tax' => (float) $order->tax,
            'discount' => (float) $order->discount,
            'total' => (float) $order->total,
            'notes' => $order->notes,
            'created_at' => $order->created_at?->toISOString(),
            'items' => $order->items->map(fn (OrderItem $item) => $this->occupancyItemPayload($item, $order))->values()->all(),
        ];
    }

    private function occupancyItemPayload(OrderItem $item, Order $order): array
    {
        return [
            'id' => $item->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'display_order_id' => $order->compactNumber(),
            'menu_item_id' => $item->menu_item_id,
            'menu_item_variant_id' => $item->menu_item_variant_id,
            'item_name' => $item->item_name,
            'variant_label' => $item->variant_label,
            'quantity' => (int) $item->quantity,
            'price' => (float) $item->price,
            'tax' => (float) $item->tax,
            'discount' => (float) $item->discount,
            'total' => (float) $item->total,
            'status' => $item->status ?: $order->order_status,
            'special_instructions' => $item->special_instructions,
            'created_at' => $item->created_at?->toISOString(),
        ];
    }

    private function syncCustomerDetails(Order $order, array $data): void
    {
        $updates = [];

        if (filled($data['customer_name'] ?? null)) {
            $updates['customer_name'] = $data['customer_name'];
        }

        if (filled($data['customer_phone'] ?? null)) {
            $updates['customer_phone'] = $data['customer_phone'];
        }

        if (filled($data['customer_email'] ?? null)) {
            $updates['customer_email'] = $data['customer_email'];
        }

        if (filled($data['notes'] ?? null)) {
            $currentNotes = trim((string) $order->notes);
            $newNotes = trim((string) $data['notes']);

            $updates['notes'] = $currentNotes !== '' && $currentNotes !== $newNotes
                ? $currentNotes."\n".$newNotes
                : $newNotes;
        }

        if ($updates !== []) {
            $order->update($updates);
        }
    }
}
