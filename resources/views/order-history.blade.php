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
    $orderDetails = $orders->getCollection()->map(function ($order) use ($typeOptions, $statuses) {
        $latestPayment = $order->payments->sortByDesc('created_at')->first();
        $location = $order->servicePoint?->name
            ?? $order->restaurantTable?->name
            ?? $order->room?->name
            ?? ($order->order_type === 'takeaway' ? 'Counter / Takeaway' : 'Direct Order');
        $typeLabel = $typeOptions[$order->order_type] ?? ucwords(str_replace('_', ' ', $order->order_type));
        $paymentMethod = $latestPayment?->payment_method;
        $paymentLabel = ucfirst($order->payment_status).($paymentMethod ? ' via '.ucfirst($paymentMethod) : '');

        return [
            'id' => $order->id,
            'orderNumber' => $order->order_number,
            'displayId' => $order->compactNumber(),
            'date' => $order->created_at?->format('d M Y') ?? '',
            'time' => $order->created_at?->format('h:i A') ?? '',
            'customerName' => $order->customer_name ?: 'Walk-in Customer',
            'customerPhone' => $order->customer_phone ?: 'N/A',
            'customerEmail' => $order->customer_email,
            'location' => $location,
            'typeLabel' => $typeLabel,
            'status' => $order->order_status,
            'statusLabel' => $statuses[$order->order_status] ?? ucfirst($order->order_status),
            'paymentStatus' => $order->payment_status,
            'paymentMethod' => $paymentMethod,
            'paymentLabel' => $paymentLabel,
            'subtotal' => (float) $order->subtotal,
            'tax' => (float) $order->tax,
            'discount' => (float) $order->discount,
            'total' => (float) $order->total,
            'note' => $order->notes,
            'itemsCount' => $order->items->count(),
            'items' => $order->items->map(function ($item) use ($order, $statuses) {
                $status = $item->status ?: $order->order_status;
                $lineSubtotal = round((float) $item->price * (int) $item->quantity, 2);

                return [
                    'id' => $item->id,
                    'name' => $item->item_name,
                    'displayName' => trim(($item->variant_label ? $item->variant_label.' ' : '').$item->item_name),
                    'variantLabel' => $item->variant_label,
                    'qty' => (int) $item->quantity,
                    'unitPrice' => (float) $item->price,
                    'lineSubtotal' => $lineSubtotal,
                    'tax' => (float) $item->tax,
                    'discount' => (float) $item->discount,
                    'total' => (float) $item->total,
                    'status' => $status,
                    'statusLabel' => $statuses[$status] ?? ucfirst($status),
                    'specialInstructions' => $item->special_instructions,
                ];
            })->values()->all(),
        ];
    })->values();
@endphp

<div
    x-data="window.orderHistoryPage ? window.orderHistoryPage({
        orders: @js($orderDetails),
        gstSettings: @js([
            'enabled' => (bool) $business->gst_enabled,
            'cgstRate' => (float) ($business->cgst ?? 2.5),
            'sgstRate' => (float) ($business->sgst ?? 2.5),
            'brandName' => $business->name,
            'gstNo' => $business->gst_number ?? '',
            'address' => $business->address ?? '',
            'pincode' => $business->pincode ?? '',
        ]),
    }) : { activeOrderId: null, closeDetail() {} }"
    x-on:keydown.escape.window="closeDetail()"
    class="space-y-4"
>
    <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h1 class="text-[30px] font-black tracking-tight text-ink">Order History</h1>
            <p class="mt-1 text-[13px] font-semibold text-muted">Search old bills, open a clean receipt preview, and print or download it quickly.</p>
        </div>
        <a href="{{ route('dashboard.orders.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-border bg-card px-4 text-[13px] font-black text-ink transition hover:bg-card-tint">
            Live Orders
        </a>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-card class="rounded-[20px] p-3.5">
            <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Filtered Orders</span>
            <strong class="mt-2.5 block text-[28px] font-black text-ink">{{ number_format($summary['orders']) }}</strong>
        </x-card>
        <x-card class="rounded-[20px] p-3.5">
            <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Gross Sales</span>
            <strong class="mt-2.5 block text-[28px] font-black text-ink">Rs. {{ number_format($summary['gross_sales'], 2) }}</strong>
        </x-card>
        <x-card class="rounded-[20px] p-3.5">
            <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Paid Collection</span>
            <strong class="mt-2.5 block text-[28px] font-black text-success">Rs. {{ number_format($summary['paid_sales'], 2) }}</strong>
        </x-card>
        <x-card class="rounded-[20px] p-3.5">
            <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Pending Collection</span>
            <strong class="mt-2.5 block text-[28px] font-black text-orange">Rs. {{ number_format($summary['pending_collection'], 2) }}</strong>
        </x-card>
    </div>

    <x-card class="rounded-[24px] p-3.5">
        <form
            method="GET"
            action="{{ route('dashboard.orders.history') }}"
            class="grid gap-2.5 md:grid-cols-2 xl:grid-cols-12 xl:items-end"
        >
            <label class="block xl:col-span-3">
                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Order, customer, phone" class="h-10 w-full rounded-xl border border-border bg-card-tint px-3.5 text-[13px] font-semibold text-ink outline-none placeholder:text-muted focus:border-orange">
            </label>

            <label class="block xl:col-span-2">
                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">From</span>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="h-10 w-full rounded-xl border border-border bg-card-tint px-3.5 text-[13px] font-semibold text-ink outline-none focus:border-orange">
            </label>

            <label class="block xl:col-span-2">
                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">To</span>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="h-10 w-full rounded-xl border border-border bg-card-tint px-3.5 text-[13px] font-semibold text-ink outline-none focus:border-orange">
            </label>

            <label class="block xl:col-span-2">
                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Status</span>
                <select name="status" class="h-10 w-full rounded-xl border border-border bg-card-tint px-3.5 text-[13px] font-semibold text-ink outline-none focus:border-orange">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            {{--
            <label class="block xl:col-span-2">
                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Service Point</span>
                <select name="service_point_id" class="h-10 w-full rounded-xl border border-border bg-card-tint px-3.5 text-[13px] font-semibold text-ink outline-none focus:border-orange">
                    <option value="">All Locations</option>
                    @foreach($servicePoints as $point)
                        <option value="{{ $point->id }}" @selected((string) ($filters['service_point_id'] ?? '') === (string) $point->id)>{{ $point->name }} ({{ $point->code }})</option>
                    @endforeach
                </select>
            </label>
            --}}

            <label class="block xl:col-span-3">
                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Payment</span>
                <select name="payment_status" class="h-10 w-full rounded-xl border border-border bg-card-tint px-3.5 text-[13px] font-semibold text-ink outline-none focus:border-orange">
                    <option value="">All Payments</option>
                    @foreach($paymentStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            {{--
            <label class="block xl:col-span-3">
                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Order Type</span>
                <select name="order_type" class="h-10 w-full rounded-xl border border-border bg-card-tint px-3.5 text-[13px] font-semibold text-ink outline-none focus:border-orange">
                    <option value="">All Types</option>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['order_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            --}}

            <label class="block xl:col-span-2">
                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Per Page</span>
                <select name="per_page" class="h-10 w-full rounded-xl border border-border bg-card-tint px-3.5 text-[13px] font-semibold text-ink outline-none focus:border-orange">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 25) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </label>

            <div class="flex items-end gap-2 xl:col-span-2">
                <button type="submit" class="h-10 rounded-xl bg-orange px-4 text-[13px] font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                    Apply
                </button>
                <a href="{{ route('dashboard.orders.history') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-border bg-card px-4 text-[13px] font-black text-ink transition hover:bg-card-tint">
                    Reset
                </a>
            </div>
        </form>
    </x-card>

    <x-card class="overflow-hidden rounded-[26px] p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border text-left">
                <thead class="bg-card-tint/70">
                    <tr>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-muted">Order</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-muted">Date</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-muted">Customer</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-muted">Location</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-muted">Status</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-muted">Payment</th>
                        <th class="px-5 py-3 text-right text-[10px] font-black uppercase tracking-[0.18em] text-muted">Total</th>
                        <th class="px-5 py-3 text-right text-[10px] font-black uppercase tracking-[0.18em] text-muted">Action</th>
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
                            $paymentLabel = ucfirst($order->payment_status).($latestPayment?->payment_method ? ' via '.ucfirst($latestPayment->payment_method) : '');
                        @endphp
                        <tr class="transition hover:bg-card-tint/30">
                            <td class="px-4 py-3.5 align-middle">
                                <span class="block text-lg font-black text-orange">{{ $order->compactNumber() }}</span>
                                <span class="mt-1 block text-xs font-semibold text-muted">{{ $order->order_number }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 align-middle">
                                <span class="block text-sm font-black text-ink">{{ $order->created_at?->format('d M Y') }}</span>
                                <span class="mt-1 block text-xs font-semibold text-muted">{{ $order->created_at?->format('h:i A') }}</span>
                            </td>
                            <td class="px-4 py-3.5 align-middle">
                                <span class="block max-w-[180px] truncate text-sm font-black text-ink">{{ $order->customer_name ?: 'Walk-in Customer' }}</span>
                                <span class="mt-1 block text-xs font-semibold text-muted">{{ $order->customer_phone ?: 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-3.5 align-middle">
                                <span class="block max-w-[180px] truncate text-sm font-black text-ink">{{ $location }}</span>
                                <span class="mt-1 block text-xs font-semibold text-muted">{{ $typeLabel }}</span>
                            </td>
                            <td class="px-4 py-3.5 align-middle">
                                <span class="inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] {{ $statusClasses[$order->order_status] ?? 'bg-card-tint text-muted border border-border' }}">
                                    {{ $statuses[$order->order_status] ?? ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 align-middle">
                                <span class="inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] {{ $paymentClasses[$order->payment_status] ?? 'bg-card-tint text-muted border border-border' }}">
                                    {{ $paymentLabel }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-right align-middle text-lg font-black text-ink">
                                Rs. {{ number_format((float) $order->total, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-right align-middle">
                                <button
                                    type="button"
                                    x-on:click="openDetail({{ $order->id }})"
                                    class="inline-flex h-9 items-center justify-center gap-2 rounded-xl border border-border bg-card px-3.5 text-[11px] font-black uppercase tracking-[0.18em] text-ink transition hover:border-orange/30 hover:bg-card-tint"
                                >
                                    <svg class="h-4 w-4 text-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 4.75h7.5l3 3v11.5h-10.5ZM14.25 4.75v3.5h3.5M8.75 11h6.5M8.75 14.25h6.5M8.75 17.5h4.5" />
                                    </svg>
                                    <span>View Bill</span>
                                </button>
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

    <div
        x-show="selectedOrder"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-navy-deep/60 p-4 backdrop-blur-sm"
        x-on:click.self="closeDetail()"
    >
        <div class="w-full max-w-[1180px] overflow-hidden rounded-[28px] border border-border bg-card shadow-2xl shadow-navy/20">
            <div class="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                <div class="min-w-0">
                    <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-orange">Bill Preview</span>
                    <h2 class="mt-1 text-2xl font-black text-ink" x-text="selectedOrder ? selectedOrder.displayId : ''"></h2>
                    <p class="mt-1 text-sm font-semibold text-muted">
                        <span x-text="selectedOrder ? selectedOrder.orderNumber : ''"></span>
                        <span class="mx-2 text-border">&middot;</span>
                        <span x-text="selectedOrder ? selectedOrder.date : ''"></span>
                        <span class="mx-2 text-border">&middot;</span>
                        <span x-text="selectedOrder ? selectedOrder.time : ''"></span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        x-on:click="downloadReceipt()"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-border bg-card px-4 text-xs font-black uppercase tracking-[0.18em] text-ink transition hover:bg-card-tint"
                    >
                        <svg class="h-4 w-4 text-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.75v9.5m0 0 3.25-3.25M12 14.25l-3.25-3.25M5.75 17.75v1.5h12.5v-1.5" />
                        </svg>
                        <span>Download Bill</span>
                    </button>
                    <button
                        type="button"
                        x-on:click="printReceipt()"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-orange px-4 text-xs font-black uppercase tracking-[0.18em] text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 8.75v-4h10.5v4M6.75 17.75H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-1.75M7.5 14.75h9v5h-9v-5Z" />
                        </svg>
                        <span>Print Bill</span>
                    </button>
                    <button
                        type="button"
                        x-on:click="closeDetail()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-card-tint text-lg font-black text-muted transition hover:text-ink"
                        aria-label="Close detail modal"
                    >
                        &times;
                    </button>
                </div>
            </div>

            <div class="max-h-[85vh] overflow-y-auto px-5 py-5">
                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_360px]">
                    <section class="space-y-4">
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl border border-border bg-card-tint/60 px-4 py-3">
                                <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Customer</span>
                                <p class="mt-2 text-sm font-black text-ink" x-text="selectedOrder ? selectedOrder.customerName : ''"></p>
                                <p class="mt-1 text-xs font-semibold text-muted" x-text="selectedOrder ? selectedOrder.customerPhone : ''"></p>
                                <p x-show="selectedOrder && selectedOrder.customerEmail" class="mt-1 text-xs font-semibold text-muted" x-text="selectedOrder ? selectedOrder.customerEmail : ''"></p>
                            </div>
                            <div class="rounded-2xl border border-border bg-card-tint/60 px-4 py-3">
                                <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Location</span>
                                <p class="mt-2 text-sm font-black text-ink" x-text="selectedOrder ? selectedOrder.location : ''"></p>
                                <p class="mt-1 text-xs font-semibold text-muted" x-text="selectedOrder ? selectedOrder.typeLabel : ''"></p>
                            </div>
                            <div class="rounded-2xl border border-border bg-card-tint/60 px-4 py-3">
                                <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Status</span>
                                <span class="mt-2 inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em]" :class="statusClass(selectedOrder ? selectedOrder.status : '')" x-text="selectedOrder ? selectedOrder.statusLabel : ''"></span>
                            </div>
                            <div class="rounded-2xl border border-border bg-card-tint/60 px-4 py-3">
                                <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Payment</span>
                                <span class="mt-2 inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em]" :class="paymentClass(selectedOrder ? selectedOrder.paymentStatus : '')" x-text="selectedOrder ? selectedOrder.paymentLabel : ''"></span>
                            </div>
                        </div>

                        <div class="rounded-[26px] border border-border bg-card-tint/35 p-4">
                            <div class="flex items-center justify-between gap-3 border-b border-border pb-3">
                                <div>
                                    <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Full Detail</span>
                                    <h3 class="mt-1 text-lg font-black text-ink">All Items</h3>
                                </div>
                                <span class="rounded-xl bg-card px-3 py-1 text-[10px] font-black text-orange" x-text="selectedOrder ? `${selectedOrder.itemsCount} ${selectedOrder.itemsCount === 1 ? 'item' : 'items'}` : ''"></span>
                            </div>

                            <div class="mt-4 space-y-3">
                                <template x-if="selectedOrder">
                                    <template x-for="item in selectedOrder.items" :key="item.id">
                                        <div class="rounded-[22px] border border-border bg-card px-4 py-4 shadow-sm">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-sm font-black text-ink" x-text="`${item.qty}x ${item.displayName}`"></p>
                                                    <p class="mt-1 text-xs font-semibold text-muted">
                                                        <span x-text="`${money(item.unitPrice)} each`"></span>
                                                        <span class="mx-1">&middot;</span>
                                                        <span x-text="`Line total ${money(item.total)}`"></span>
                                                    </p>
                                                </div>
                                                <span class="shrink-0 inline-flex rounded-xl px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em]" :class="statusClass(item.status)" x-text="item.statusLabel"></span>
                                            </div>
                                            <p x-show="item.specialInstructions" class="mt-3 text-xs font-semibold italic text-muted">
                                                Note: <span x-text="item.specialInstructions"></span>
                                            </p>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <div x-show="selectedOrder && selectedOrder.note" class="rounded-[24px] border border-orange/15 bg-orange/5 p-4 shadow-sm">
                            <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-orange">Order Note</span>
                            <p class="mt-3 text-sm font-semibold italic leading-relaxed text-slate-700" x-text="selectedOrder ? selectedOrder.note : ''"></p>
                        </div>
                    </section>

                    <aside>
                        <div class="rounded-[26px] border border-border bg-card p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3 border-b border-border pb-3">
                                <div>
                                    <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Receipt Preview</span>
                                    <h3 class="mt-1 text-lg font-black text-ink">Small Printer Format</h3>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-center">
                                <div class="w-full max-w-[320px] rounded-[22px] border border-border bg-white px-4 py-4 font-mono text-[11px] text-slate-800 shadow-inner">
                                    <div class="text-center">
                                        <div class="text-sm font-black" x-text="gstSettings.brandName || 'Business'"></div>
                                        <template x-if="gstSettings.address">
                                            <div class="mt-1 text-[10px]" x-text="gstSettings.address"></div>
                                        </template>
                                        <template x-if="gstSettings.pincode">
                                            <div class="text-[10px]" x-text="`PIN: ${gstSettings.pincode}`"></div>
                                        </template>
                                        <template x-if="gstSettings.enabled && gstSettings.gstNo">
                                            <div class="text-[10px]" x-text="`GSTIN: ${gstSettings.gstNo}`"></div>
                                        </template>
                                    </div>

                                    <div class="my-3 border-t border-dashed border-slate-300"></div>

                                    <div class="space-y-1">
                                        <div class="flex justify-between gap-3"><span>Bill No:</span><span x-text="selectedOrder ? selectedOrder.displayId : ''"></span></div>
                                        <div class="flex justify-between gap-3"><span>Order No:</span><span x-text="selectedOrder ? selectedOrder.orderNumber : ''"></span></div>
                                        <div class="flex justify-between gap-3"><span>Date:</span><span x-text="selectedOrder ? `${selectedOrder.date} ${selectedOrder.time}` : ''"></span></div>
                                        <div class="flex justify-between gap-3"><span>Location:</span><span x-text="selectedOrder ? selectedOrder.location : ''"></span></div>
                                        <div class="flex justify-between gap-3"><span>Customer:</span><span x-text="selectedOrder ? selectedOrder.customerName : ''"></span></div>
                                    </div>

                                    <div class="my-3 border-t border-dashed border-slate-300"></div>
                                    <div class="text-center text-[10px] font-black">ITEM DETAILS</div>

                                    <div class="mt-3 space-y-2">
                                        <template x-if="selectedOrder">
                                            <template x-for="item in receiptItems(selectedOrder)" :key="`receipt-${item.id}`">
                                                <div>
                                                    <div class="font-black" x-text="`${item.qty}x ${item.displayName}`"></div>
                                                    <div class="mt-1 flex justify-between gap-3">
                                                        <span x-text="`${money(item.unitPrice)} x ${item.qty}`"></span>
                                                        <span x-text="money(item.lineSubtotal)"></span>
                                                    </div>
                                                    <template x-if="Number(item.tax || 0) > 0 && gstSettings.enabled">
                                                        <div class="mt-1 flex justify-between gap-3 text-[10px]">
                                                            <span>GST</span>
                                                            <span x-text="money(item.tax)"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="item.specialInstructions">
                                                        <div class="mt-1 text-[10px] italic text-slate-500" x-text="`Note: ${item.specialInstructions}`"></div>
                                                    </template>
                                                </div>
                                            </template>
                                        </template>
                                    </div>

                                    <div class="my-3 border-t border-dashed border-slate-300"></div>
                                    <div class="text-center text-[10px] font-black" x-text="receiptTotals(selectedOrder).hasGst ? 'GST BILL SUMMARY' : 'BILL SUMMARY'"></div>

                                    <div class="mt-3 space-y-1">
                                        <div class="flex justify-between gap-3"><span>Subtotal:</span><span x-text="money(receiptTotals(selectedOrder).subtotal)"></span></div>
                                        <template x-if="Number(receiptTotals(selectedOrder).discount || 0) > 0">
                                            <div class="flex justify-between gap-3"><span>Discount:</span><span x-text="`- ${money(receiptTotals(selectedOrder).discount)}`"></span></div>
                                        </template>
                                        <template x-if="receiptTotals(selectedOrder).hasGst">
                                            <div class="flex justify-between gap-3"><span x-text="`CGST (${receiptTotals(selectedOrder).cgstRate.toFixed(2)}%)`"></span><span x-text="money(receiptTotals(selectedOrder).cgstAmount)"></span></div>
                                        </template>
                                        <template x-if="receiptTotals(selectedOrder).hasGst">
                                            <div class="flex justify-between gap-3"><span x-text="`SGST (${receiptTotals(selectedOrder).sgstRate.toFixed(2)}%)`"></span><span x-text="money(receiptTotals(selectedOrder).sgstAmount)"></span></div>
                                        </template>
                                        <template x-if="receiptTotals(selectedOrder).hasGst">
                                            <div class="flex justify-between gap-3"><span>Total GST:</span><span x-text="money(receiptTotals(selectedOrder).tax)"></span></div>
                                        </template>
                                        <div class="mt-2 flex justify-between gap-3 border-t border-slate-300 pt-2 text-sm font-black">
                                            <span>Total</span>
                                            <span x-text="money(receiptTotals(selectedOrder).total)"></span>
                                        </div>
                                    </div>

                                    <div class="my-3 border-t border-dashed border-slate-300"></div>
                                    <div class="flex justify-between gap-3"><span>Payment:</span><span x-text="selectedOrder ? selectedOrder.paymentLabel : ''"></span></div>
                                    <div class="flex justify-between gap-3"><span>Status:</span><span x-text="selectedOrder ? selectedOrder.statusLabel : ''"></span></div>

                                    <template x-if="selectedOrder && selectedOrder.note">
                                        <div>
                                            <div class="my-3 border-t border-dashed border-slate-300"></div>
                                            <div class="text-[10px] italic text-slate-500" x-text="`Instructions: ${selectedOrder.note}`"></div>
                                        </div>
                                    </template>

                                    <div class="my-3 border-t border-dashed border-slate-300"></div>
                                    <div class="text-center font-black">Thank you</div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
