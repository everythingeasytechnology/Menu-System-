<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ScanUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicePointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeOrdersLoaded = $this->relationLoaded('activeOrders');
        $activeOrders = $activeOrdersLoaded ? $this->activeOrders : collect();
        $activeOrderCount = $activeOrdersLoaded
            ? $activeOrders->count()
            : ($this->active_orders_count ?? null);
        $hasActiveOrders = $activeOrderCount !== null && $activeOrderCount > 0;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'qr_identifier' => $this->qr_identifier,
            'name' => $this->name,
            'seats' => $this->seats,
            'category' => $this->category,
            'point_type' => $this->point_type,
            'status' => $hasActiveOrders ? 'occupied' : $this->status,
            'is_active' => $this->is_active,
            'scan_url' => $this->qr_identifier ? ScanUrlService::forQr($this->qr_identifier) : null,
            'scanner_download_url' => url('/api/v1/service-points/'.$this->id.'/scanner'),
            'order_number' => $hasActiveOrders && $activeOrdersLoaded ? $this->billLabel($activeOrders) : $this->order_number,
            'amount' => $hasActiveOrders && $activeOrdersLoaded
                ? (float) $activeOrders->sum(fn ($order) => (float) $order->total)
                : (float) $this->amount,
            'items' => $hasActiveOrders && $activeOrdersLoaded ? $this->orderItemPayload($activeOrders) : ($this->items ?? []),
            'active_order_count' => $this->when($activeOrderCount !== null, (int) $activeOrderCount),
            'active_orders' => $this->when($activeOrdersLoaded, fn () => $this->activeOrderPayload($activeOrders)),
        ];
    }

    private function activeOrderPayload($orders): array
    {
        return $orders->map(fn ($order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'display_id' => $order->compactNumber(),
            'status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'total' => (float) $order->total,
            'customer' => $order->customer_name ?: 'Walk-in Customer',
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'label' => $item->quantity.'x '.$item->item_name.($item->variant_label ? ' ('.$item->variant_label.')' : ''),
                'status' => $item->status ?: $order->order_status,
                'total' => (float) $item->total,
            ])->values()->all(),
        ])->values()->all();
    }

    private function orderItemPayload($orders): array
    {
        return $orders->flatMap(fn ($order) => $order->items->map(fn ($item) => [
            'label' => $item->quantity.'x '.$item->item_name.($item->variant_label ? ' ('.$item->variant_label.')' : ''),
            'status' => $item->status ?: $order->order_status,
            'order_number' => $order->compactNumber(),
            'total' => (float) $item->total,
        ]))->values()->all();
    }

    private function billLabel($orders): string
    {
        if ($orders->count() === 1) {
            return $orders->first()->compactNumber();
        }

        return $orders->count().' active orders';
    }
}
