@extends('layouts.app')

@section('title', 'Order History')

@section('content')
@php
    $statusClasses = [
        'preparing' => 'bg-orange/10 text-orange border border-orange/10',
        'ready' => 'bg-teal/10 text-teal border border-teal/10',
        'served' => 'bg-success/10 text-success border border-success/10',
        'completed' => 'bg-success/10 text-success border border-success/10',
        'cancelled' => 'bg-danger/10 text-danger border border-danger/10',
    ];
    $paymentClasses = [
        'paid' => 'bg-success/10 text-success border border-success/10',
        'pending' => 'bg-orange/10 text-orange border border-orange/10',
        'unpaid' => 'bg-danger/10 text-danger border border-danger/10',
        'refunded' => 'bg-slate-100 text-slate-600 border border-slate-200',
    ];
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-black tracking-tight text-ink">Order History</h1>
            <p class="mt-0.5 text-xs font-semibold text-muted">All database orders for {{ $business->name }} with date, status, payment, type, service point, and search filters.</p>
        </div>
        <a href="{{ route('dashboard.orders.index') }}" class="inline-flex items-center justify-center rounded-xl border border-border bg-card px-4 py-2 text-xs font-bold text-ink hover:bg-card-tint">
            Live Orders
        </a>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-card class="p-4">
            <span class="block text-[9px] font-black uppercase tracking-wider text-muted">Filtered Orders</span>
            <strong class="mt-2 block text-xl font-black text-ink">{{ number_format($summary['orders']) }}</strong>
        </x-card>
        <x-card class="p-4">
            <span class="block text-[9px] font-black uppercase tracking-wider text-muted">Gross Sales</span>
            <strong class="mt-2 block text-xl font-black text-ink">Rs. {{ number_format($summary['gross_sales'], 2) }}</strong>
        </x-card>
        <x-card class="p-4">
            <span class="block text-[9px] font-black uppercase tracking-wider text-muted">Paid Collection</span>
            <strong class="mt-2 block text-xl font-black text-success">Rs. {{ number_format($summary['paid_sales'], 2) }}</strong>
        </x-card>
        <x-card class="p-4">
            <span class="block text-[9px] font-black uppercase tracking-wider text-muted">Pending Collection</span>
            <strong class="mt-2 block text-xl font-black text-orange">Rs. {{ number_format($summary['pending_collection'], 2) }}</strong>
        </x-card>
    </div>

    <x-card class="p-4">
        <form method="GET" action="{{ route('dashboard.orders.history') }}" class="grid gap-3 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <label class="mb-1 block text-[9px] font-black uppercase tracking-wider text-muted">Search</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Order, customer, phone, item" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none focus:border-orange">
            </div>
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[9px] font-black uppercase tracking-wider text-muted">From Date</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none focus:border-orange">
            </div>
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[9px] font-black uppercase tracking-wider text-muted">To Date</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none focus:border-orange">
            </div>
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[9px] font-black uppercase tracking-wider text-muted">Order Status</label>
                <select name="status" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none focus:border-orange">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-3">
                <label class="mb-1 block text-[9px] font-black uppercase tracking-wider text-muted">Service Point</label>
                <select name="service_point_id" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none focus:border-orange">
                    <option value="">All Locations</option>
                    @foreach($servicePoints as $point)
                        <option value="{{ $point->id }}" @selected((string) ($filters['service_point_id'] ?? '') === (string) $point->id)>{{ $point->name }} ({{ $point->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[9px] font-black uppercase tracking-wider text-muted">Payment</label>
                <select name="payment_status" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none focus:border-orange">
                    <option value="">All Payments</option>
                    @foreach($paymentStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[9px] font-black uppercase tracking-wider text-muted">Order Type</label>
                <select name="order_type" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none focus:border-orange">
                    <option value="">All Types</option>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['order_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[9px] font-black uppercase tracking-wider text-muted">Per Page</label>
                <select name="per_page" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none focus:border-orange">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 25) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 lg:col-span-8">
                <button type="submit" class="rounded-xl bg-orange px-4 py-2 text-xs font-black text-white shadow-md shadow-orange/20 hover:bg-orange/95">Apply Filters</button>
                <a href="{{ route('dashboard.orders.history') }}" class="rounded-xl border border-border bg-card px-4 py-2 text-xs font-black text-ink hover:bg-card-tint">Reset</a>
            </div>
        </form>
    </x-card>

    <x-card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border text-left">
                <thead class="bg-card-tint">
                    <tr>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-muted">Order</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-muted">Date</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-muted">Customer</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-muted">Location</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-muted">Items</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-muted">Status</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-muted">Payment</th>
                        <th class="px-5 py-3 text-right text-[10px] font-black uppercase tracking-wider text-muted">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border bg-card">
                    @forelse($orders as $order)
                        @php
                            $latestPayment = $order->payments->sortByDesc('created_at')->first();
                            $location = $order->servicePoint?->name
                                ?? $order->restaurantTable?->name
                                ?? $order->room?->name
                                ?? ($order->order_type === 'takeaway' ? 'Counter / Takeaway' : 'Direct Order');
                            $typeLabel = $typeOptions[$order->order_type] ?? ucwords(str_replace('_', ' ', $order->order_type));
                        @endphp
                        <tr class="hover:bg-card-tint/40">
                            <td class="px-5 py-4 align-top">
                                <span class="block text-sm font-black text-orange">{{ $order->compactNumber() }}</span>
                                <span class="mt-0.5 block text-[10px] font-semibold text-muted">{{ $order->order_number }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-top">
                                <span class="block text-xs font-bold text-ink">{{ $order->created_at?->format('d M Y') }}</span>
                                <span class="mt-0.5 block text-[10px] font-semibold text-muted">{{ $order->created_at?->format('h:i A') }}</span>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="block text-xs font-black text-ink">{{ $order->customer_name ?: 'Walk-in Customer' }}</span>
                                <span class="mt-0.5 block text-[10px] font-semibold text-muted">{{ $order->customer_phone ?: 'N/A' }}</span>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="block max-w-[160px] truncate text-xs font-bold text-ink">{{ $location }}</span>
                                <span class="mt-0.5 block text-[10px] font-semibold text-muted">{{ $typeLabel }}</span>
                            </td>
                            <td class="min-w-[220px] px-5 py-4 align-top">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($order->items->take(3) as $item)
                                        <span class="rounded-lg bg-card-tint px-2 py-1 text-[10px] font-bold text-ink">
                                            {{ $item->quantity }}x {{ $item->item_name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }}
                                        </span>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <span class="rounded-lg bg-orange/10 px-2 py-1 text-[10px] font-bold text-orange">+{{ $order->items->count() - 3 }} more</span>
                                    @endif
                                </div>
                                @if($order->notes)
                                    <p class="mt-2 line-clamp-1 text-[10px] font-semibold italic text-muted">{{ $order->notes }}</p>
                                @endif
                                <details class="group mt-3 rounded-xl border border-border bg-card-tint/60 p-0">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 rounded-xl px-3 py-2 text-[10px] font-black uppercase tracking-wider text-ink marker:hidden">
                                        <span>Full Detail</span>
                                        <span class="rounded-md bg-card px-2 py-1 text-[9px] font-black text-orange transition group-open:rotate-180">+</span>
                                    </summary>
                                    <div class="space-y-3 border-t border-border px-3 py-3">
                                        <div class="space-y-2">
                                            <span class="block text-[9px] font-black uppercase tracking-wider text-muted">All Items</span>
                                            @foreach($order->items as $item)
                                                <div class="rounded-lg border border-border bg-card px-3 py-2">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <p class="text-xs font-black text-ink">
                                                                {{ $item->quantity }}x {{ $item->item_name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }}
                                                            </p>
                                                            <p class="mt-0.5 text-[10px] font-semibold text-muted">
                                                                Rs. {{ number_format((float) $item->price, 2) }} each
                                                                <span class="mx-1">&middot;</span>
                                                                Line total Rs. {{ number_format((float) $item->total, 2) }}
                                                            </p>
                                                        </div>
                                                        <span class="shrink-0 inline-flex rounded-md px-2 py-1 text-[9px] font-black uppercase tracking-wider {{ $statusClasses[$item->status ?: $order->order_status] ?? 'bg-card-tint text-muted border border-border' }}">
                                                            {{ $statuses[$item->status ?: $order->order_status] ?? ucfirst($item->status ?: $order->order_status) }}
                                                        </span>
                                                    </div>
                                                    @if($item->special_instructions)
                                                        <p class="mt-1 text-[10px] font-semibold italic text-muted">
                                                            Note: {{ $item->special_instructions }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                            <div class="rounded-lg border border-border bg-card px-3 py-2">
                                                <span class="block text-[9px] font-black uppercase tracking-wider text-muted">Customer</span>
                                                <p class="mt-1 text-xs font-black text-ink">{{ $order->customer_name ?: 'Walk-in Customer' }}</p>
                                                <p class="mt-0.5 text-[10px] font-semibold text-muted">{{ $order->customer_phone ?: 'N/A' }}</p>
                                                @if($order->customer_email)
                                                    <p class="mt-0.5 break-all text-[10px] font-semibold text-muted">{{ $order->customer_email }}</p>
                                                @endif
                                            </div>
                                            <div class="rounded-lg border border-border bg-card px-3 py-2">
                                                <span class="block text-[9px] font-black uppercase tracking-wider text-muted">Bill Summary</span>
                                                <div class="mt-1 space-y-1 text-[10px] font-semibold text-muted">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span>Subtotal</span>
                                                        <span class="font-black text-ink">Rs. {{ number_format((float) $order->subtotal, 2) }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span>Tax</span>
                                                        <span class="font-black text-ink">Rs. {{ number_format((float) $order->tax, 2) }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span>Discount</span>
                                                        <span class="font-black text-ink">Rs. {{ number_format((float) $order->discount, 2) }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-2 border-t border-border pt-1 text-xs">
                                                        <span class="font-black text-muted">Total</span>
                                                        <span class="font-black text-ink">Rs. {{ number_format((float) $order->total, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($order->notes)
                                            <div class="rounded-lg border border-orange/10 bg-orange/5 px-3 py-2">
                                                <span class="block text-[9px] font-black uppercase tracking-wider text-orange">Order Note</span>
                                                <p class="mt-1 text-[10px] font-semibold italic text-slate-700">{{ $order->notes }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex rounded-md px-2 py-1 text-[9px] font-black uppercase tracking-wider {{ $statusClasses[$order->order_status] ?? 'bg-card-tint text-muted border border-border' }}">
                                    {{ $statuses[$order->order_status] ?? ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex rounded-md px-2 py-1 text-[9px] font-black uppercase tracking-wider {{ $paymentClasses[$order->payment_status] ?? 'bg-card-tint text-muted border border-border' }}">
                                    {{ ucfirst($order->payment_status) }}{{ $latestPayment?->payment_method ? ' via '.ucfirst($latestPayment->payment_method) : '' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right align-top text-sm font-black text-ink">
                                Rs. {{ number_format((float) $order->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <p class="text-sm font-black text-ink">No order history found</p>
                                <p class="mt-1 text-xs font-semibold text-muted">Clear filters or change the date range to see more orders.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if($orders->hasPages())
        <div>
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
