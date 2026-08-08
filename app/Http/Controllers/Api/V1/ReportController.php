<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends ApiController
{
    public function dailySales(Request $request): JsonResponse
    {
        $rows = $this->ordersBetween($request)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, COALESCE(SUM(total), 0) as sales')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return $this->success($rows, 'Daily sales report');
    }

    public function monthlySales(Request $request): JsonResponse
    {
        $rows = $this->ordersBetween($request)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as orders, COALESCE(SUM(total), 0) as sales')
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return $this->success($rows, 'Monthly sales report');
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = $this->ordersBetween($request)
            ->with('items')
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(OrderResource::collection($orders), 'Order report');
    }

    public function itemSales(Request $request): JsonResponse
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.business_id', $this->businessId($request))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('orders.created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('orders.created_at', '<=', $request->input('to')))
            ->selectRaw('order_items.item_name, SUM(order_items.quantity) as quantity, COALESCE(SUM(order_items.total), 0) as sales')
            ->groupBy('order_items.item_name')
            ->orderByDesc('quantity')
            ->paginate((int) $request->input('per_page', 25));

        return $this->success($rows->items(), 'Item sales report');
    }

    public function paymentReport(Request $request): JsonResponse
    {
        $payments = Payment::where('business_id', $this->businessId($request))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')))
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(PaymentResource::collection($payments), 'Payment report');
    }

    public function cancelledOrders(Request $request): JsonResponse
    {
        $orders = $this->ordersBetween($request)
            ->where('order_status', 'cancelled')
            ->with('items')
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(OrderResource::collection($orders), 'Cancelled orders report');
    }

    public function topSellingItems(Request $request): JsonResponse
    {
        return $this->itemSales($request);
    }

    private function ordersBetween(Request $request)
    {
        return Order::where('business_id', $this->businessId($request))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')));
    }
}
