@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $metricCards = [
        [
            'label' => "Today's Orders",
            'value' => number_format($stats['orders']),
            'box' => 'bg-orange/10 text-orange',
            'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7.25h10M7 11.75h10M7 16.25h6M5.75 4.75h12.5v14.5H5.75V4.75Z" /></svg>',
        ],
        [
            'label' => "Today's Sales",
            'value' => 'Rs. '.number_format($stats['sales'], 2),
            'box' => 'bg-teal/10 text-teal',
            'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.75v14.5M8.5 15.25a3.5 3.5 0 0 0 3.5 2.25c2.1 0 3.5-1.05 3.5-2.6 0-1.7-1.35-2.3-3.5-2.9-2.2-.6-3.5-1.25-3.5-3 0-1.45 1.4-2.5 3.5-2.5 1.55 0 2.75.62 3.35 1.75" /></svg>',
        ],
        [
            'label' => 'Avg Order Value',
            'value' => 'Rs. '.number_format($stats['average_order'], 2),
            'box' => 'bg-violet-100 text-violet-600',
            'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h13.5v9.5H5.25v-9.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 6h7M8.5 12h4.5" /></svg>',
        ],
        [
            'label' => 'Completed Orders',
            'value' => number_format($stats['completed']),
            'box' => 'bg-blue-100 text-blue-600',
            'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M7.75 12.5 10.5 15.25 16.75 8.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25a8.25 8.25 0 1 0 0-16.5 8.25 8.25 0 0 0 0 16.5Z" /></svg>',
        ],
        [
            'label' => 'Open Orders',
            'value' => number_format($stats['pending']),
            'box' => 'bg-warning/10 text-warning',
            'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.25v5l3 2" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25a8.25 8.25 0 1 0 0-16.5 8.25 8.25 0 0 0 0 16.5Z" /></svg>',
        ],
    ];

    $statusTones = [
        'pending' => ['dot' => 'bg-slate-300 text-slate-500', 'bar' => 'bg-slate-400'],
        'confirmed' => ['dot' => 'bg-blue-100 text-blue-600', 'bar' => 'bg-blue-500'],
        'preparing' => ['dot' => 'bg-orange/10 text-orange', 'bar' => 'bg-orange'],
        'ready' => ['dot' => 'bg-cyan-100 text-cyan-700', 'bar' => 'bg-cyan-500'],
        'served' => ['dot' => 'bg-teal/10 text-teal', 'bar' => 'bg-teal'],
        'completed' => ['dot' => 'bg-success/10 text-success', 'bar' => 'bg-success'],
        'cancelled' => ['dot' => 'bg-danger/10 text-danger', 'bar' => 'bg-danger'],
    ];
@endphp

<div
    x-data="dashboardPage({
        initialOrders: @js($liveOrders),
        feedUrl: '{{ route('dashboard.orders.feed') }}',
        csrf: '{{ csrf_token() }}'
    })"
    x-init="start()"
    class="space-y-5"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-ink">Dashboard</h1>
            <p class="mt-1 text-sm font-semibold text-muted">Overview of your restaurant operations for {{ $business->name }}.</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="refreshOrders()"
                class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 text-sm font-black text-ink shadow-sm transition hover:bg-card-tint"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.25 8.25h4v-4M20.25 8.25a8.25 8.25 0 0 0-14-2.5M7.75 15.75h-4v4M3.75 15.75a8.25 8.25 0 0 0 14 2.5" />
                </svg>
                <span>Refresh</span>
            </button>
            <a
                href="{{ route('dashboard.orders.index') }}"
                class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-orange px-6 text-sm font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                </svg>
                <span>Manage Orders</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @foreach($metricCards as $metric)
            <x-card variant="default" class="min-h-[126px] p-5">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl {{ $metric['box'] }}">
                        {!! $metric['icon'] !!}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-[11px] font-black uppercase tracking-wider text-muted">{{ $metric['label'] }}</span>
                        <span class="mt-3 block truncate text-2xl font-black tracking-tight text-ink">{{ $metric['value'] }}</span>
                    </span>
                </div>
            </x-card>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <x-card variant="default" class="p-5">
            <div class="flex flex-col gap-3 border-b border-border pb-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-orange">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7.25h10M7 11.75h10M7 16.25h6M5.75 4.75h12.5v14.5H5.75V4.75Z" />
                            </svg>
                        </span>
                        <h2 class="text-lg font-black text-ink">Live Orders</h2>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-muted"><span x-text="orders.length"></span> active orders</p>
                </div>
                <a href="{{ route('dashboard.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-black text-orange hover:underline">
                    <span>View all orders</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="mt-4 flex max-w-full items-center gap-1 overflow-x-auto rounded-xl border border-border bg-card-tint p-1">
                <button type="button" @click="activeTab = 'all'" :class="tabClass('all')" class="shrink-0 rounded-lg px-4 py-2 text-xs font-black transition">All (<span x-text="orders.length"></span>)</button>
                <button type="button" @click="activeTab = 'pending'" :class="tabClass('pending')" class="shrink-0 rounded-lg px-4 py-2 text-xs font-black transition">Pending (<span x-text="countStatus('pending')"></span>)</button>
                <button type="button" @click="activeTab = 'confirmed'" :class="tabClass('confirmed')" class="shrink-0 rounded-lg px-4 py-2 text-xs font-black transition">Kitchen (<span x-text="countStatus('confirmed')"></span>)</button>
                <button type="button" @click="activeTab = 'preparing'" :class="tabClass('preparing')" class="shrink-0 rounded-lg px-4 py-2 text-xs font-black transition">Preparing (<span x-text="countStatus('preparing')"></span>)</button>
                <button type="button" @click="activeTab = 'ready'" :class="tabClass('ready')" class="shrink-0 rounded-lg px-4 py-2 text-xs font-black transition">Ready (<span x-text="countStatus('ready')"></span>)</button>
            </div>

            <div class="mt-4 overflow-hidden rounded-xl border border-border">
                <div class="overflow-x-auto">
                    <div class="min-w-[780px]">
                        <div class="grid grid-cols-[92px_92px_minmax(220px,1fr)_150px_132px_56px] gap-4 border-b border-border bg-card-tint px-4 py-3 text-[11px] font-black uppercase tracking-wider text-muted">
                            <span>Order</span>
                            <span>Time</span>
                            <span>Guest</span>
                            <span>Payment</span>
                            <span>Status</span>
                            <span>Action</span>
                        </div>

                        <div class="divide-y divide-border/70 bg-card">
                            <template x-for="order in filteredOrders" :key="order.id">
                                <div
                                    role="button"
                                    tabindex="0"
                                    @click="openOrderDetails(order)"
                                    @keydown.enter.prevent="openOrderDetails(order)"
                                    class="grid min-h-[58px] cursor-pointer grid-cols-[92px_92px_minmax(220px,1fr)_150px_132px_56px] items-center gap-4 px-4 py-2 text-left text-sm transition hover:bg-card-tint/70"
                                >
                                    <span class="truncate font-black text-orange" x-text="order.displayId"></span>
                                    <span class="truncate text-xs font-bold text-muted" x-text="order.time || order.elapsed"></span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-black text-ink" x-text="order.customer"></span>
                                        <span class="mt-0.5 block truncate text-xs font-semibold text-muted" x-text="guestLine(order)"></span>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-black text-ink" x-text="order.amount"></span>
                                        <span class="mt-0.5 block truncate text-[10px] font-black uppercase" :class="paymentClass(order)" x-text="order.paymentLabel"></span>
                                    </span>
                                    <span class="inline-flex w-fit items-center rounded-lg px-3 py-1 text-[10px] font-black uppercase tracking-wider" :class="statusClass(order.status)" x-text="order.statusLabel"></span>
                                    <button
                                        type="button"
                                        @click.stop="openOrderDetails(order)"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-card text-muted transition hover:border-orange/30 hover:text-orange"
                                        aria-label="Open order"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75h.01M12 12h.01M12 17.25h.01" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div x-show="filteredOrders.length === 0" class="border-t border-border bg-card px-4 py-12 text-center">
                    <p class="text-sm font-black text-ink">No live orders</p>
                    <p class="mt-1 text-xs font-semibold text-muted">New database orders will appear here automatically.</p>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between text-xs font-semibold text-muted">
                <span x-text="filteredOrders.length ? `Showing 1 to ${filteredOrders.length} of ${orders.length} orders` : 'No orders to show'"></span>
                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                        </svg>
                    </button>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-orange text-xs font-black text-white">1</span>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </div>
        </x-card>

        <div class="space-y-5">
            <x-card variant="default" class="p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-orange">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 18.75v-5.5h3.5v5.5h-3.5Zm5.5 0v-9.5h3.5v9.5h-3.5Zm5.5 0v-13.5h3.5v13.5h-3.5Z" />
                            </svg>
                        </span>
                        <h2 class="text-lg font-black text-ink">Order Status Overview</h2>
                    </div>
                    <span class="rounded-lg border border-border bg-card px-3 py-1.5 text-xs font-black text-muted">Today</span>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach($statusOverview as $row)
                        @php $tone = $statusTones[$row['status']] ?? ['dot' => 'bg-card-tint text-muted', 'bar' => 'bg-muted']; @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm font-black">
                                <span class="flex min-w-0 items-center gap-3 text-muted">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $tone['dot'] }}">
                                        <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                                    </span>
                                    <span class="truncate">{{ $row['label'] }}</span>
                                </span>
                                <span class="shrink-0 text-ink">{{ $row['count'] }} ({{ $row['percentage'] }}%)</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-card-tint">
                                <div class="h-full rounded-full {{ $tone['bar'] }}" style="width: {{ $row['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card variant="default" class="p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-orange">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.75 14.1 9l4.65.68-3.37 3.28.8 4.63L12 15.4l-4.18 2.19.8-4.63-3.37-3.28L9.9 9 12 4.75Z" />
                            </svg>
                        </span>
                        <h2 class="text-lg font-black text-ink">Top Selling Items</h2>
                    </div>
                    <a href="/reports" class="text-sm font-black text-orange hover:underline">View report</a>
                </div>

                <div class="mt-6 space-y-5">
                    @forelse($topItems as $index => $item)
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-orange/20 via-warning/20 to-teal/20 text-sm font-black text-orange">
                                    {{ $index + 1 }}
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-black text-ink">{{ $item['name'] }}</span>
                                    <span class="mt-1 block text-xs font-semibold text-muted">{{ $item['quantity'] }} sold</span>
                                </span>
                            </div>
                            <span class="shrink-0 text-sm font-black text-ink">Rs. {{ number_format($item['revenue'], 2) }}</span>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-border bg-card-tint px-4 py-8 text-center">
                            <p class="text-sm font-black text-ink">No sales yet today</p>
                            <p class="mt-1 text-xs font-semibold text-muted">Completed order items will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>

    <div
        x-show="selectedOrder"
        @keydown.escape.window="closeOrderDetails()"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-navy-deep/60 p-4 backdrop-blur-sm"
        style="display: none;"
        @click.self="closeOrderDetails()"
    >
        <div class="w-full max-w-2xl overflow-hidden rounded-card border border-border bg-card shadow-2xl">
            <div class="flex items-center justify-between border-b border-border bg-card-tint px-5 py-4">
                <div>
                    <h3 class="text-lg font-black text-ink">Order <span class="text-orange" x-text="selectedOrder?.displayId"></span></h3>
                    <p class="mt-1 text-xs font-black uppercase tracking-wider text-muted" x-text="`${selectedOrder?.time || ''} - ${selectedOrder?.elapsed || ''}`"></p>
                </div>
                <button type="button" @click="closeOrderDetails()" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-card text-muted transition hover:text-ink" aria-label="Close order detail">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4 p-5">
                <div class="grid gap-3 rounded-xl border border-border bg-card-tint p-4 sm:grid-cols-[minmax(0,1fr)_140px_120px] sm:items-center">
                    <span class="min-w-0">
                        <span class="block truncate text-base font-black text-ink" x-text="selectedOrder?.customer"></span>
                        <span class="mt-1 block truncate text-xs font-semibold text-muted" x-text="guestLine(selectedOrder)"></span>
                    </span>
                    <span class="text-xs font-black uppercase" :class="paymentClass(selectedOrder)" x-text="selectedOrder?.paymentLabel"></span>
                    <span class="text-base font-black text-ink sm:text-right" x-text="selectedOrder?.amount"></span>
                </div>

                <div>
                    <span class="block text-xs font-black uppercase tracking-wider text-muted">Items</span>
                    <div class="mt-2 max-h-72 divide-y divide-border overflow-auto rounded-xl border border-border">
                        <template x-for="item in selectedOrder?.items || []" :key="item.id">
                            <div class="grid grid-cols-[minmax(0,1fr)_80px_108px] items-center gap-3 px-3 py-2.5 text-sm">
                                <span class="truncate font-black text-ink" x-text="item.name"></span>
                                <span class="text-xs font-bold text-muted" x-text="`Qty ${item.qty}`"></span>
                                <span class="inline-flex justify-center rounded-lg px-2 py-1 text-[10px] font-black uppercase tracking-wider" :class="statusClass(item.status)" x-text="item.statusLabel"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-xl border border-orange/15 bg-orange/5 p-4">
                    <span class="block text-xs font-black uppercase tracking-wider text-orange">Instructions</span>
                    <p class="mt-2 text-sm font-semibold italic text-slate-700" x-text="selectedOrder?.note || 'No special instructions provided.'"></p>
                </div>
            </div>

            <div class="flex gap-3 border-t border-border bg-card-tint px-5 py-4">
                <button type="button" @click="closeOrderDetails()" class="flex-1 rounded-xl border border-border bg-card py-3 text-sm font-black text-ink transition hover:bg-card-tint">Close</button>
                <a href="{{ route('dashboard.orders.index') }}" class="flex-1 rounded-xl bg-orange py-3 text-center text-sm font-black text-white transition hover:bg-orange/95">Manage Order</a>
            </div>
        </div>
    </div>
</div>

<script>
    function dashboardPage(config) {
        return {
            orders: config.initialOrders || [],
            activeTab: 'all',
            selectedOrder: null,
            refreshTimer: null,
            liveStatuses: ['pending', 'confirmed', 'preparing', 'ready', 'served'],

            start() {
                this.refreshTimer = setInterval(() => this.refreshOrders(), 20000);
            },

            get filteredOrders() {
                if (this.activeTab === 'all') return this.orders;

                return this.orders.filter((order) => order.status === this.activeTab);
            },

            countStatus(status) {
                return this.orders.filter((order) => order.status === status).length;
            },

            guestLine(order) {
                if (!order) return '';

                return [order.location, order.phone].filter(Boolean).join(' - ');
            },

            tabClass(tab) {
                return this.activeTab === tab
                    ? 'bg-white text-orange shadow-sm'
                    : 'text-muted hover:bg-white hover:text-ink';
            },

            statusClass(status) {
                return {
                    pending: 'bg-slate-100 text-slate-600 border border-slate-200',
                    confirmed: 'bg-blue-50 text-blue-600 border border-blue-100',
                    preparing: 'bg-orange/10 text-orange border border-orange/20',
                    ready: 'bg-cyan-50 text-cyan-700 border border-cyan-100',
                    served: 'bg-teal/10 text-teal border border-teal/20',
                    completed: 'bg-success/10 text-success border border-success/20',
                    cancelled: 'bg-danger/10 text-danger border border-danger/20',
                }[status] || 'bg-card-tint text-muted border border-border';
            },

            paymentClass(order) {
                if (!order) return 'text-muted';

                if (order.paymentStatus === 'paid') {
                    return 'text-success';
                }

                if (order.paymentStatus === 'pending') {
                    return 'text-orange';
                }

                return 'text-teal';
            },

            openOrderDetails(order) {
                this.selectedOrder = order;
            },

            closeOrderDetails() {
                this.selectedOrder = null;
            },

            isLiveOrder(order) {
                return this.liveStatuses.includes(order.status)
                    || (order.status === 'completed' && order.paymentStatus !== 'paid');
            },

            replaceOrder(updatedOrder) {
                const index = this.orders.findIndex((order) => order.id === updatedOrder.id);

                if (!this.isLiveOrder(updatedOrder)) {
                    if (index >= 0) {
                        this.orders.splice(index, 1);
                    }

                    if (this.selectedOrder?.id === updatedOrder.id) {
                        this.selectedOrder = null;
                    }

                    return;
                }

                if (index >= 0) {
                    this.orders.splice(index, 1, updatedOrder);
                } else {
                    this.orders.unshift(updatedOrder);
                }

                if (this.selectedOrder?.id === updatedOrder.id) {
                    this.selectedOrder = updatedOrder;
                }
            },

            async refreshOrders() {
                try {
                    const response = await fetch(`${config.feedUrl}?limit=100&active_only=1`, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) return;

                    const payload = await response.json();
                    if (payload.success) {
                        this.orders = payload.orders || [];
                        if (this.selectedOrder) {
                            this.selectedOrder = this.orders.find((order) => order.id === this.selectedOrder.id) || null;
                        }
                    }
                } catch (error) {
                    // Keep the last successful snapshot on transient network failures.
                }
            },
        };
    }
</script>
@endsection
