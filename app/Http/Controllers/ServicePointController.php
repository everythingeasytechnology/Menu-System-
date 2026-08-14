<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use App\Services\OrderService;
use App\Services\OwnerDashboardService;
use App\Services\QrCodeService;
use App\Services\ScanUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ServicePointController extends Controller
{
    public function __construct(
        private readonly OwnerDashboardService $dashboardService,
        private readonly OrderService $orderService,
        private readonly QrCodeService $qrCodeService,
    ) {
    }

    /**
     * Display a listing of service points.
     */
    public function index(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $points = ServicePoint::where(function ($query) use ($business) {
                $query->where('business_id', $business->id)
                    ->orWhereNull('business_id');
            })
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function (ServicePoint $point) use ($business) {
                $this->ensurePointReady($point, $business);

                return $this->pointPayload($point->fresh(), $business);
            });
        $categories = $points->pluck('category')->unique()->values()->all();

        return view('service-points', compact('points', 'categories', 'business'));
    }

    /**
     * Store a newly created service point.
     */
    public function store(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'seats' => 'required|integer|min:1',
            'category' => 'required|string|max:255',
        ]);

        // Auto-generate unique code in the backend
        $count = ServicePoint::count();
        $code = 'SP-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        while (ServicePoint::where('code', $code)->exists()) {
            $count++;
            $code = 'SP-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        }

        ServicePoint::create([
            'business_id' => $business->id,
            'code' => $code,
            'qr_identifier' => $this->generateQr(),
            'name' => $validated['name'],
            'seats' => intval($validated['seats']),
            'category' => $validated['category'],
            'point_type' => 'table',
            'status' => 'available',
            'is_active' => true,
            'amount' => 0.00,
            'items' => [],
        ]);

        return redirect()->back()->with('success', 'Service Point created successfully!');
    }

    /**
     * Update the state of a service point (quick adds, status toggle, checkout).
     */
    public function update(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $point = $this->pointForBusiness((int) $id, $business);
        $this->ensurePointReady($point, $business);

        if ($request->has('status')) {
            $point->status = $request->input('status');
            if ($point->status === 'available') {
                if ($this->activeOrdersForPoint($point, $business)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Settle active orders before freeing this service point.',
                    ], 422);
                }

                $point->items = [];
                $point->amount = 0.00;
                $point->order_number = null;
            } elseif ($point->status === 'occupied' && !$point->order_number) {
                $point->order_number = '#KFC' . rand(1000, 9999);
                $point->amount = 0.00;
                $point->items = [];
            }
        }

        if ($request->has('items')) {
            $point->items = $request->input('items');
        }

        if ($request->has('amount')) {
            $point->amount = floatval($request->input('amount'));
        }

        $point->save();

        return response()->json([
            'success' => true,
            'point' => $this->pointPayload($point->fresh(), $business),
        ]);
    }

    public function settle(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $point = $this->pointForBusiness((int) $id, $business);
        $this->ensurePointReady($point, $business);

        $orders = $this->activeOrdersForPoint($point, $business)->oldest()->get();

        foreach ($orders as $order) {
            $freshOrder = $order->fresh(['items', 'payments']);

            if ($freshOrder->order_status !== 'completed') {
                $freshOrder = $this->orderService->updateStatus($freshOrder, 'completed', $request->user());
            }

            if ($freshOrder->payment_status !== 'paid') {
                $this->orderService->updatePayment($freshOrder, [
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'amount' => $freshOrder->total,
                ], $request->user());
            }
        }

        if ($orders->isEmpty()) {
            $point->update([
                'status' => 'available',
                'order_number' => null,
                'amount' => 0,
                'items' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $orders->isEmpty()
                ? 'Service point freed.'
                : 'Active orders settled and service point freed.',
            'settled_order_count' => $orders->count(),
            'point' => $this->pointPayload($point->fresh(), $business),
        ]);
    }

    public function scanner(Request $request, $id): Response
    {
        $business = $this->dashboardService->businessFor($request->user());
        $point = $this->pointForBusiness((int) $id, $business);
        $this->ensurePointReady($point, $business);

        $svg = $this->qrCodeService->svg($this->scanUrl($point));
        $filename = Str::slug($point->code.'-'.$point->name).'-scanner.svg';

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Remove the specified service point.
     */
    public function destroy(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $point = $this->pointForBusiness((int) $id, $business);
        $point->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    private function activeOrdersForPoint(ServicePoint $point, Business $business)
    {
        return Order::with(['items', 'payments'])
            ->where('business_id', $business->id)
            ->where('service_point_id', $point->id)
            ->live();
    }

    private function pointForBusiness(int $id, Business $business): ServicePoint
    {
        return ServicePoint::whereKey($id)
            ->where(function ($query) use ($business) {
                $query->where('business_id', $business->id)
                    ->orWhereNull('business_id');
            })
            ->firstOrFail();
    }

    private function ensurePointReady(ServicePoint $point, Business $business): void
    {
        $updates = [];

        if (! $point->business_id) {
            $updates['business_id'] = $business->id;
        }

        if (! $point->qr_identifier) {
            $updates['qr_identifier'] = $this->generateQr();
        }

        if (! $point->point_type) {
            $updates['point_type'] = 'table';
        }

        if ($point->is_active === null) {
            $updates['is_active'] = true;
        }

        if ($updates !== []) {
            $point->update($updates);
        }
    }

    private function pointPayload(ServicePoint $point, Business $business): array
    {
        $orders = $this->activeOrdersForPoint($point, $business)->latest()->get();
        $runningBalance = $orders->sum(fn (Order $order) => (float) $order->total);
        $activeStatus = $orders->isEmpty()
            ? $point->status
            : ($orders->every(fn (Order $order) => $order->order_status === 'completed') ? 'bill-pending' : 'occupied');

        return [
            'id' => $point->id,
            'business_id' => $point->business_id,
            'code' => $point->code,
            'qr_identifier' => $point->qr_identifier,
            'name' => $point->name,
            'seats' => (int) $point->seats,
            'category' => $point->category,
            'point_type' => $point->point_type,
            'status' => $activeStatus,
            'is_active' => (bool) $point->is_active,
            'scan_url' => $this->scanUrl($point),
            'scanner_url' => route('service-points.scanner', $point->id),
            'order_number' => $orders->isNotEmpty() ? $this->billLabel($orders) : $point->order_number,
            'amount' => $orders->isNotEmpty() ? $runningBalance : (float) $point->amount,
            'items' => $orders->isNotEmpty() ? $this->orderItemPayload($orders) : $this->legacyItems($point),
            'active_order_count' => $orders->count(),
            'active_orders' => $orders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'display_id' => $order->compactNumber(),
                'status' => $order->order_status,
                'status_label' => ucfirst(str_replace('_', ' ', $order->order_status)),
                'payment_status' => $order->payment_status,
                'total' => (float) $order->total,
                'amount_label' => 'Rs. '.number_format((float) $order->total, 2),
                'customer' => $order->customer_name ?: 'Walk-in Customer',
                'customer_email' => $order->customer_email,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'label' => $item->quantity.'x '.$item->item_name.($item->variant_label ? ' ('.$item->variant_label.')' : ''),
                    'status' => $item->status ?: $order->order_status,
                    'status_label' => ucfirst(str_replace('_', ' ', $item->status ?: $order->order_status)),
                    'total' => (float) $item->total,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    private function orderItemPayload(Collection $orders): array
    {
        return $orders->flatMap(fn (Order $order) => $order->items->map(fn ($item) => [
            'label' => $item->quantity.'x '.$item->item_name.($item->variant_label ? ' ('.$item->variant_label.')' : ''),
            'status' => $item->status ?: $order->order_status,
            'status_label' => ucfirst(str_replace('_', ' ', $item->status ?: $order->order_status)),
            'order_number' => $order->compactNumber(),
            'total' => (float) $item->total,
        ]))->values()->all();
    }

    private function legacyItems(ServicePoint $point): array
    {
        return collect($point->items ?? [])
            ->map(fn ($item) => is_array($item)
                ? [
                    'label' => $item['label'] ?? $item['name'] ?? 'Item',
                    'status' => $item['status'] ?? null,
                    'status_label' => $item['status_label'] ?? null,
                    'order_number' => null,
                    'total' => (float) ($item['total'] ?? 0),
                ]
                : [
                    'label' => (string) $item,
                    'status' => null,
                    'status_label' => null,
                    'order_number' => null,
                    'total' => 0,
                ])
            ->values()
            ->all();
    }

    private function billLabel(Collection $orders): string
    {
        if ($orders->count() === 1) {
            return $orders->first()->compactNumber();
        }

        return $orders->count().' active orders';
    }

    private function generateQr(): string
    {
        do {
            $qr = 'sp_'.Str::lower(Str::random(32));
        } while (
            ServicePoint::where('qr_identifier', $qr)->exists()
            || RestaurantTable::where('qr_identifier', $qr)->exists()
            || Room::where('qr_identifier', $qr)->exists()
        );

        return $qr;
    }

    private function scanUrl(ServicePoint $point): string
    {
        return ScanUrlService::forQr($point->qr_identifier);
    }
}
