<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\OwnerDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private readonly OwnerDashboardService $dashboardService)
    {
    }

    public function index(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());

        [$from, $to, $period] = $this->resolvePeriod($request);

        $ordersInRange = Order::where('business_id', $business->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $billableOrders = (clone $ordersInRange)->where('order_status', '!=', 'cancelled');

        $totalRevenue = (float) (clone $billableOrders)->sum('total');
        $totalOrders = (clone $ordersInRange)->count();
        $billableCount = (clone $billableOrders)->count();
        $avgOrderValue = $billableCount > 0 ? round($totalRevenue / $billableCount, 2) : 0.0;

        $salesSummary = [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'avg_order_value' => $avgOrderValue,
        ];

        $paymentBreakdown = Payment::where('business_id', $business->id)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw('payment_method, COALESCE(SUM(amount), 0) as amount')
            ->groupBy('payment_method')
            ->get();
        $paymentTotal = (float) $paymentBreakdown->sum('amount');
        $paymentBreakdown = $paymentBreakdown->map(fn ($row) => [
            'method' => $this->paymentMethodLabel($row->payment_method),
            'amount' => (float) $row->amount,
            'percentage' => $paymentTotal > 0 ? (int) round(($row->amount / $paymentTotal) * 100) : 0,
        ])->values();

        $salesTrends = (clone $billableOrders)
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'day' => \Illuminate\Support\Carbon::parse($row->date)->format('D'),
                'date' => $row->date,
                'revenue' => (float) $row->revenue,
            ])
            ->values();

        $detailedTransactions = (clone $ordersInRange)
            ->with(['items', 'payments', 'restaurantTable', 'room', 'servicePoint'])
            ->latest()
            ->limit(25)
            ->get()
            ->map(function (Order $order) {
                $payment = $order->payments->sortByDesc('created_at')->first();

                return [
                    'order_number' => $order->order_number,
                    'display_id' => $order->compactNumber(),
                    'date' => $order->created_at?->format('Y-m-d H:i'),
                    'location' => $order->servicePoint?->name ?? $order->restaurantTable?->name ?? $order->room?->name ?? ucfirst(str_replace('_', ' ', $order->order_type)),
                    'items' => $order->items->map(fn ($item) => $item->quantity.' x '.$item->item_name)->implode(', '),
                    'amount' => (float) $order->total,
                    'method' => $payment ? $this->paymentMethodLabel($payment->payment_method) : '—',
                    'status' => ucfirst($order->payment_status),
                ];
            });

        $ordersSummary = [
            'total' => $totalOrders,
            'completed' => (clone $ordersInRange)->where('order_status', 'completed')->count(),
            'cancelled' => (clone $ordersInRange)->where('order_status', 'cancelled')->count(),
        ];
        $ordersSummary['cancel_rate'] = $totalOrders > 0
            ? round(($ordersSummary['cancelled'] / $totalOrders) * 100, 1)
            : 0.0;

        $channelBreakdown = (clone $ordersInRange)
            ->selectRaw('order_type, COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue')
            ->groupBy('order_type')
            ->get()
            ->map(fn ($row) => [
                'channel' => $this->channelLabel($row->order_type),
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
                'percentage' => $totalOrders > 0 ? (int) round(($row->orders / $totalOrders) * 100) : 0,
            ])
            ->values();

        $cancelledOrdersList = (clone $ordersInRange)
            ->where('order_status', 'cancelled')
            ->with(['items', 'restaurantTable', 'room', 'servicePoint'])
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Order $order) => [
                'order_number' => $order->order_number,
                'display_id' => $order->compactNumber(),
                'date' => $order->created_at?->format('Y-m-d H:i'),
                'location' => $order->servicePoint?->name ?? $order->restaurantTable?->name ?? $order->room?->name ?? ucfirst(str_replace('_', ' ', $order->order_type)),
                'items' => $order->items->map(fn ($item) => $item->quantity.' x '.$item->item_name)->implode(', '),
                'amount' => (float) $order->total,
                'notes' => $order->notes,
            ]);

        $topItems = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.business_id', $business->id)
            ->where('orders.order_status', '!=', 'cancelled')
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to)
            ->selectRaw('order_items.item_name, SUM(order_items.quantity) as quantity, COALESCE(SUM(order_items.total), 0) as revenue')
            ->groupBy('order_items.item_name')
            ->orderByDesc('quantity')
            ->limit(15)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->item_name,
                'quantity' => (int) $row->quantity,
                'revenue' => (float) $row->revenue,
            ]);

        return view('reports', [
            'business' => $business,
            'period' => $period,
            'salesSummary' => $salesSummary,
            'paymentBreakdown' => $paymentBreakdown,
            'salesTrends' => $salesTrends,
            'detailedTransactions' => $detailedTransactions,
            'ordersSummary' => $ordersSummary,
            'channelBreakdown' => $channelBreakdown,
            'cancelledOrdersList' => $cancelledOrdersList,
            'topItems' => $topItems,
        ]);
    }

    /**
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon, 2: string}
     */
    private function resolvePeriod(Request $request): array
    {
        $period = $request->input('period', '7days');

        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay(), 'today'],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), 'yesterday'],
            default => [now()->subDays(6)->startOfDay(), now()->endOfDay(), '7days'],
        };
    }

    private function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'cash' => 'Cash',
            'online' => 'Online',
            'razorpay' => 'Razorpay',
            default => ucfirst($method ?? 'Unknown'),
        };
    }

    private function channelLabel(?string $orderType): string
    {
        return match ($orderType) {
            'room_service' => 'Room Service',
            'takeaway' => 'Takeaway',
            default => 'Dine-In',
        };
    }
}
