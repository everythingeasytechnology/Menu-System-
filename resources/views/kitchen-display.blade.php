<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">

    <title>Kitchen Display - {{ $business->name }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden bg-[#0b1220] font-sans text-white antialiased">
    <div
        x-data="kitchenDisplay({
            initialOrders: @js($ordersPayload),
            statuses: @js($statuses),
            itemStatuses: @js($itemStatuses),
            feedUrl: '{{ route('dashboard.orders.feed') }}',
            csrf: '{{ csrf_token() }}',
            statusImages: @js([
                'preparing' => asset('images/order-status/preparing.png'),
                'ready' => asset('images/order-status/ready.png'),
                'served' => asset('images/order-status/served.png'),
                'completed' => asset('images/order-status/served.png'),
                'cancelled' => asset('images/order-status/cancelled.png'),
            ])
        })"
        x-init="start()"
        class="flex h-screen flex-col overflow-hidden"
    >
        <header class="shrink-0 border-b border-white/10 bg-[#101827] px-3 py-2 shadow-2xl shadow-black/20">
            <div class="flex flex-col gap-2 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-orange shadow-lg shadow-orange/25">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75h14.5v9.5H4.75v-9.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 19.25h7.5M12 16.25v3M8.25 10h3.5M8.25 13h7.5" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="truncate text-xl font-black tracking-tight">Kitchen Display</h1>
                        <p class="truncate text-xs font-semibold text-slate-300">{{ $business->name }} live orders</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2 sm:flex sm:items-center">
                    <template x-for="summary in summaries" :key="summary.key">
                        <div class="rounded-lg border border-white/10 bg-white/[0.04] px-2.5 py-1.5 text-center">
                            <span class="block text-[9px] font-black uppercase tracking-wider text-slate-400" x-text="summary.label"></span>
                            <strong class="block text-lg font-black leading-tight" :class="summary.textClass" x-text="summary.count"></strong>
                        </div>
                    </template>
                </div>

                <div class="flex shrink-0 items-center justify-between gap-2 xl:justify-end">
                    <div class="rounded-lg border border-white/10 bg-white/[0.04] px-3 py-1.5 text-right">
                        <span class="block font-mono text-lg font-black tracking-wide leading-tight" x-text="clock"></span>
                        <span class="block text-[9px] font-black uppercase tracking-wider text-slate-400">Auto refresh 10s</span>
                    </div>
                    <button
                        type="button"
                        @click="toggleFullscreen()"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-white/10 bg-white/[0.06] px-3 text-[10px] font-black uppercase tracking-wider text-slate-100 transition hover:bg-white/[0.1]"
                    >
                        Full Screen
                    </button>
                    <a href="{{ route('dashboard.orders.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg bg-orange px-3 text-[10px] font-black uppercase tracking-wider text-white shadow-lg shadow-orange/20">
                        Orders
                    </a>
                </div>
            </div>
        </header>

        <main class="grid min-h-0 flex-1 grid-cols-1 gap-2 overflow-hidden p-2 lg:grid-cols-4">
            <template x-for="column in columns" :key="column.key">
                <section class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-white/10 bg-white/[0.04]">
                    <div class="flex shrink-0 items-center justify-between border-b border-white/10 px-2.5 py-1.5" :class="column.headerClass">
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider" x-text="column.label"></h2>
                            <p class="text-[9px] font-bold uppercase tracking-wider text-white/70" x-text="column.subtitle"></p>
                        </div>
                        <strong class="rounded-lg bg-black/20 px-2.5 py-0.5 text-base font-black" x-text="columnOrders(column.key).length"></strong>
                    </div>

                    <div class="min-h-0 flex-1 space-y-1.5 overflow-y-auto p-1.5">
                        <template x-for="order in columnOrders(column.key)" :key="order.id">
                            <article class="rounded-lg border border-white/10 bg-[#f8fafc] p-2 text-slate-900 shadow-lg shadow-black/10">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xl font-black text-orange" x-text="order.displayId"></span>
                                            <span class="rounded-md px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider" :class="statusClass(order.status)" x-text="order.statusLabel"></span>
                                        </div>
                                        <p class="truncate text-xs font-black" x-text="order.location"></p>
                                        <p class="truncate text-[10px] font-bold text-slate-500">
                                            <span x-text="order.customer"></span>
                                            <span x-show="order.phone !== 'N/A'" x-text="` - ${order.phone}`"></span>
                                        </p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <img :src="statusImage(order)" :alt="itemStatusLabel(order)" class="mb-0.5 h-10 w-14 rounded-md border border-slate-200 object-cover">
                                        <span class="block text-xs font-black text-slate-700" x-text="order.time"></span>
                                        <span class="inline-flex rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-500" x-text="order.elapsed"></span>
                                    </div>
                                </div>

                                <template x-if="order.note">
                                    <div class="mt-1.5 rounded-md border border-orange/15 bg-orange/5 px-2 py-1">
                                        <span class="text-[9px] font-black uppercase tracking-wider text-orange">Instructions</span>
                                        <p class="text-[10px] font-semibold italic text-slate-700" x-text="order.note"></p>
                                    </div>
                                </template>

                                <div class="mt-2 space-y-1">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="rounded-md border border-slate-200 bg-white p-1.5">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <p class="truncate text-xs font-black">
                                                        <span x-text="`${item.qty}x`"></span>
                                                        <span x-text="item.name"></span>
                                                    </p>
                                                    <span class="mt-0.5 inline-flex rounded-md px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider" :class="statusClass(item.status)" x-text="item.statusLabel"></span>
                                                </div>
                                                <button
                                                    type="button"
                                                    x-show="nextItemStatus(item)"
                                                    @click="updateItem(order, item, nextItemStatus(item))"
                                                    :disabled="isUpdating"
                                                    class="shrink-0 rounded-md bg-slate-900 px-2.5 py-1.5 text-[9px] font-black uppercase tracking-wider text-white transition hover:bg-orange disabled:opacity-50"
                                                    x-text="nextItemLabel(item)"
                                                ></button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-2 grid grid-cols-3 gap-1">
                                    <button type="button" @click="bulkMove(order, 'preparing')" :disabled="isUpdating || order.status === 'preparing'" class="rounded-md bg-orange px-2 py-1.5 text-[9px] font-black uppercase tracking-wider text-white transition disabled:cursor-not-allowed disabled:opacity-40">Start</button>
                                    <button type="button" @click="bulkMove(order, 'ready')" :disabled="isUpdating || order.status === 'ready'" class="rounded-md bg-blue-500 px-2 py-1.5 text-[9px] font-black uppercase tracking-wider text-white transition disabled:cursor-not-allowed disabled:opacity-40">Ready</button>
                                    <button type="button" @click="bulkMove(order, 'served')" :disabled="isUpdating || order.status === 'served'" class="rounded-md bg-teal px-2 py-1.5 text-[9px] font-black uppercase tracking-wider text-white transition disabled:cursor-not-allowed disabled:opacity-40">Served</button>
                                </div>
                            </article>
                        </template>

                        <div x-show="columnOrders(column.key).length === 0" class="flex h-32 items-center justify-center rounded-lg border border-dashed border-white/10 text-center">
                            <p class="text-xs font-bold text-slate-400">No orders</p>
                        </div>
                    </div>
                </section>
            </template>
        </main>
    </div>

    <script>
        function kitchenDisplay(config) {
            return {
                orders: config.initialOrders || [],
                statuses: config.statuses || {},
                itemStatuses: config.itemStatuses || [],
                statusImages: config.statusImages || {},
                liveStatuses: ['preparing', 'ready', 'served'],
                clock: '',
                refreshTimer: null,
                clockTimer: null,
                isUpdating: false,
                columns: [
                    { key: 'received', label: 'Received', subtitle: 'New kitchen tickets', textClass: 'text-blue-300', headerClass: 'bg-blue-500/20 text-blue-100' },
                    { key: 'preparing', label: 'Preparing', subtitle: 'Cooking now', textClass: 'text-orange', headerClass: 'bg-orange/20 text-orange' },
                    { key: 'ready', label: 'Ready', subtitle: 'Waiting pickup', textClass: 'text-teal', headerClass: 'bg-teal/20 text-teal' },
                    { key: 'served', label: 'Served', subtitle: 'Sent to guest', textClass: 'text-success', headerClass: 'bg-success/20 text-success' }
                ],

                start() {
                    this.tickClock();
                    this.clockTimer = setInterval(() => this.tickClock(), 1000);
                    this.refreshTimer = setInterval(() => this.refreshOrders(), 10000);
                },

                tickClock() {
                    this.clock = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                },

                get summaries() {
                    return [
                        { key: 'total', label: 'Total', count: this.orders.length, textClass: 'text-white' },
                        { key: 'received', label: 'Received', count: this.columnOrders('received').length, textClass: 'text-blue-300' },
                        { key: 'preparing', label: 'Preparing', count: this.columnOrders('preparing').length, textClass: 'text-orange' },
                        { key: 'ready', label: 'Ready', count: this.columnOrders('ready').length, textClass: 'text-teal' }
                    ];
                },

                columnOrders(columnKey) {
                    return [...this.orders]
                        .filter((order) => this.orderStage(order) === columnKey)
                        .sort((a, b) => Number(a.sortKey || a.id || 0) - Number(b.sortKey || b.id || 0));
                },

                orderStage(order) {
                    const visual = this.itemStatusVisual(order);
                    if (visual === 'preparing') return 'preparing';
                    if (visual === 'ready') return 'ready';
                    if (visual === 'served') return 'served';

                    return 'received';
                },

                itemStatusVisual(order) {
                    if (order.status === 'cancelled') return 'cancelled';

                    const itemStatuses = (order.items || []).map((item) => item.status || order.status);
                    if (itemStatuses.includes('preparing')) return 'preparing';
                    if (itemStatuses.includes('ready')) return 'ready';
                    if (itemStatuses.includes('served') || order.status === 'served' || order.status === 'completed') return 'served';

                    return order.status || 'preparing';
                },

                itemStatusLabel(order) {
                    const status = this.itemStatusVisual(order);

                    return this.statuses?.[status] || {
                        preparing: 'Preparing',
                        ready: 'Ready',
                        served: 'Served',
                        cancelled: 'Cancelled'
                    }[status] || 'Preparing';
                },

                statusImage(order) {
                    const status = this.itemStatusVisual(order);

                    return this.statusImages[status] || this.statusImages.preparing || '';
                },

                nextItemStatus(item) {
                    if (item.status === 'preparing') return 'ready';
                    if (item.status === 'ready') return 'served';

                    return null;
                },

                nextItemLabel(item) {
                    return {
                        ready: 'Ready',
                        served: 'Served'
                    }[this.nextItemStatus(item)] || '';
                },

                statusClass(status) {
                    return {
                        preparing: 'bg-orange/10 text-orange border border-orange/10',
                        ready: 'bg-blue-50 text-blue-600 border border-blue-100',
                        served: 'bg-teal/10 text-teal border border-teal/10',
                        completed: 'bg-success/10 text-success border border-success/10',
                        cancelled: 'bg-danger/10 text-danger border border-danger/10'
                    }[status] || 'bg-slate-100 text-slate-500 border border-slate-200';
                },

                isLiveOrder(order) {
                    return this.liveStatuses.includes(order.status)
                        || (order.status === 'completed' && order.paymentStatus !== 'paid');
                },

                replaceOrder(updatedOrder) {
                    const index = this.orders.findIndex((order) => order.id === updatedOrder.id);

                    if (!this.isLiveOrder(updatedOrder)) {
                        if (index >= 0) this.orders.splice(index, 1);
                        return;
                    }

                    if (index >= 0) {
                        this.orders.splice(index, 1, updatedOrder);
                    } else {
                        this.orders.unshift(updatedOrder);
                    }
                },

                async bulkMove(order, status) {
                    await this.postOrderStatus(order.id, status);
                },

                async updateItem(order, item, status) {
                    if (!status) return;
                    this.isUpdating = true;

                    const response = await fetch(`/orders/${order.id}/items/${item.id}/status`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrf
                        },
                        body: JSON.stringify({ status })
                    });

                    const payload = await response.json();
                    this.isUpdating = false;

                    if (!response.ok || !payload.success) {
                        alert(payload.message || 'Unable to update item.');
                        await this.refreshOrders();
                        return;
                    }

                    this.replaceOrder(payload.order);
                },

                async postOrderStatus(orderId, status) {
                    this.isUpdating = true;

                    const response = await fetch(`/orders/${orderId}/status`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrf
                        },
                        body: JSON.stringify({ status })
                    });

                    const payload = await response.json();
                    this.isUpdating = false;

                    if (!response.ok || !payload.success) {
                        alert(payload.message || 'Unable to update order.');
                        await this.refreshOrders();
                        return;
                    }

                    this.replaceOrder(payload.order);
                },

                async refreshOrders() {
                    const response = await fetch(`${config.feedUrl}?limit=150&active_only=1`, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) return;
                    const payload = await response.json();
                    if (payload.success) {
                        this.orders = payload.orders || [];
                    }
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen?.();
                        return;
                    }

                    document.exitFullscreen?.();
                }
            };
        }
    </script>
</body>
</html>
