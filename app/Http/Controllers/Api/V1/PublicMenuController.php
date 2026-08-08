<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\MenuCategoryResource;
use App\Http\Resources\Api\V1\MenuItemResource;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Business;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicMenuController extends ApiController
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function menu(string $qr): JsonResponse
    {
        [$business, $context] = $this->businessFromQr($qr);

        $categories = $business->categories()
            ->where('active', true)
            ->where('status', 'active')
            ->with(['menuItems' => function ($query) {
                $query->where('status', 'active')
                    ->where('availability', true)
                    ->where('stock', true)
                    ->with(['variants', 'presetImage'])
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success([
            'business' => [
                'name' => $business->name,
                'type' => $business->type,
                'logo_path' => $business->logo_path,
                'timezone' => $business->timezone,
            ],
            'context' => $context,
            'categories' => MenuCategoryResource::collection($categories),
        ], 'Public menu');
    }

    public function item(string $qr, MenuItem $menuItem): JsonResponse
    {
        [$business] = $this->businessFromQr($qr);

        if ($menuItem->business_id !== $business->id || $menuItem->status !== 'active') {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new MenuItemResource($menuItem->load(['variants', 'presetImage'])), 'Menu item details');
    }

    public function createOrder(Request $request, string $qr): JsonResponse
    {
        [$business, $context] = $this->businessFromQr($qr);

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'in:cash,online,razorpay'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:menu_item_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['order_type'] = $context['type'] === 'room' ? 'room_service' : 'dine_in';
        $validated['table_id'] = $context['type'] === 'table' ? $context['id'] : null;
        $validated['room_id'] = $context['type'] === 'room' ? $context['id'] : null;
        $validated['service_point_id'] = $context['type'] === 'service_point' ? $context['id'] : null;

        $order = $this->orderService->create($business, $validated);

        return $this->success(new OrderResource($order), 'Order created successfully', 201);
    }

    public function orderStatus(string $orderNumber): JsonResponse
    {
        $order = Order::with('items')
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return $this->error('Resource not found', 404);
        }

        return $this->success([
            'order_number' => $order->order_number,
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'total' => (float) $order->total,
            'created_at' => $order->created_at?->toISOString(),
        ], 'Order status');
    }

    private function businessFromQr(string $qr): array
    {
        $table = RestaurantTable::with('business')
            ->where('qr_identifier', $qr)
            ->where('is_active', true)
            ->first();

        if ($table && $table->business && $table->business->status === 'active') {
            return [$table->business, [
                'type' => 'table',
                'id' => $table->id,
                'name' => $table->name,
                'qr_identifier' => $table->qr_identifier,
            ]];
        }

        $room = Room::with('business')
            ->where('qr_identifier', $qr)
            ->where('is_active', true)
            ->first();

        if ($room && $room->business && $room->business->status === 'active') {
            return [$room->business, [
                'type' => 'room',
                'id' => $room->id,
                'name' => $room->name,
                'qr_identifier' => $room->qr_identifier,
            ]];
        }

        $servicePoint = ServicePoint::with('business')
            ->where('qr_identifier', $qr)
            ->where('is_active', true)
            ->first();

        if ($servicePoint && $servicePoint->business && $servicePoint->business->status === 'active') {
            return [$servicePoint->business, [
                'type' => 'service_point',
                'id' => $servicePoint->id,
                'name' => $servicePoint->name,
                'category' => $servicePoint->category,
                'point_type' => $servicePoint->point_type,
                'qr_identifier' => $servicePoint->qr_identifier,
            ]];
        }

        throw ValidationException::withMessages(['qr' => ['QR identifier is invalid or inactive.']]);
    }
}
