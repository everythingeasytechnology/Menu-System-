@extends('layouts.app')

@section('title', 'KFC Executive Dashboard')

@section('content')
<div 
    x-data="{
        activeTab: 'all',
        orders: [
            { id: 'KFC1256', customer: 'Rahul Sharma', phone: '9876543210', email: 'rahul@example.com', type: 'packed', items: ['1 x 8 Pc Hot & Crispy', '1 x Pepsi (1.25 L)'], extra: 2, amount: '₹ 485', paymentStatus: 'Online Paid', status: 'preparing', time: '11:42 AM', elapsed: '2 mins ago' },
            { id: 'KFC1255', customer: 'Table 4', phone: '2 People', email: '', type: 'dine-in', items: ['1 x Zinger Burger', '1 x Chicken Popcorn (Large)'], extra: 1, amount: '₹ 395', paymentStatus: 'Paid', method: 'Cash', status: 'preparing', time: '11:39 AM', elapsed: '3 mins ago' },
            { id: 'KFC1254', customer: 'Walk-in Customer', phone: 'N/A', email: '', type: 'packed', items: ['1 x 6 Pc Hot Wings', '1 x Pepsi (600ml)'], extra: 0, amount: '₹ 325', paymentStatus: 'Paid', method: 'UPI', status: 'ready', time: '11:37 AM', elapsed: '5 mins ago' },
            { id: 'KFC1253', customer: 'Priya Verma', phone: '9876543290', email: 'priya@example.com', type: 'packed', items: ['1 x 5 in 1 Rice Bowl', '1 x Pepsi (1.25 L)'], extra: 1, amount: '₹ 450', paymentStatus: 'Online Paid', status: 'ready', time: '11:33 AM', elapsed: '8 mins ago' },
            { id: 'KFC1252', customer: 'Table 7', phone: '4 People', email: '', type: 'dine-in', items: ['1 x Smoky Red Bucket', '2 x Garlic Bread'], extra: 0, amount: '₹ 760', paymentStatus: 'Paid', method: 'Card', status: 'ready', time: '11:30 AM', elapsed: '10 mins ago' }
        ],
        changeStatus(id, nextStatus) {
            let o = this.orders.find(x => x.id === id);
            if (o) o.status = nextStatus;
        }
    }"
    class="space-y-6"
>
    <!-- Header Greeting and Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-ink">Good Morning, John 👋</h1>
            <p class="text-xs text-muted mt-0.5">Operational summary for KFC Connaught Place today.</p>
        </div>
        <button 
            @click="alert('Opening new order wizard...')"
            class="rounded-xl bg-orange hover:bg-orange/95 px-4 py-2 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer flex items-center gap-1.5"
        >
            <span class="font-extrabold text-sm">+</span>
            <span>New Order</span>
        </button>
    </div>

    <!-- Business KPI Metric Cards (Responsive, Auto-expanding Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <!-- KPI 1: Today's Orders -->
        <x-card variant="default" class="p-4 flex flex-col justify-between min-h-[88px]">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange/10 text-orange">🛍️</span>
                    <div>
                        <span class="text-[10px] font-bold text-muted uppercase tracking-wider block">Today's Orders</span>
                        <h3 class="text-2xl font-black text-ink mt-0.5 whitespace-nowrap">124</h3>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- KPI 2: Today's Sales -->
        <x-card variant="default" class="p-4 flex flex-col justify-between min-h-[88px]">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange/10 text-orange">₹</span>
                    <div>
                        <span class="text-[10px] font-bold text-muted uppercase tracking-wider block">Today's Sales</span>
                        <h3 class="text-2xl font-black text-ink mt-0.5 whitespace-nowrap">₹ 45,680</h3>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- KPI 3: Average Order Value -->
        <x-card variant="default" class="p-4 flex flex-col justify-between min-h-[88px]">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange/10 text-orange">🛒</span>
                    <div>
                        <span class="text-[10px] font-bold text-muted uppercase tracking-wider block font-semibold leading-tight">Average Order Value</span>
                        <h3 class="text-2xl font-black text-ink mt-0.5 whitespace-nowrap">₹ 368</h3>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- KPI 4: Completed Orders -->
        <x-card variant="default" class="p-4 flex flex-col justify-between min-h-[88px]">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange/10 text-orange">📈</span>
                    <div>
                        <span class="text-[10px] font-bold text-muted uppercase tracking-wider block">Completed Orders</span>
                        <h3 class="text-2xl font-black text-ink mt-0.5 whitespace-nowrap">102</h3>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- KPI 5: Pending Orders -->
        <x-card variant="default" class="p-4 flex flex-col justify-between min-h-[88px]">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange/10 text-orange">⏳</span>
                    <div>
                        <span class="text-[10px] font-bold text-muted uppercase tracking-wider block">Pending Orders</span>
                        <h3 class="text-2xl font-black text-ink mt-0.5 whitespace-nowrap">12</h3>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Live Orders & Side Widgets Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Live Orders Monitor -->
        <div class="lg:col-span-2 space-y-4">
            <x-card variant="default" class="p-4 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-border">
                    <div class="flex items-center gap-2">
                        <span class="text-orange">⚡</span>
                        <h3 class="text-xs font-black text-ink uppercase tracking-wider">Live Orders</h3>
                    </div>
                    <a href="/orders" class="text-[10px] font-bold text-orange hover:underline">View all orders →</a>
                </div>

                <!-- Tabs Filter Row -->
                <div class="flex flex-wrap items-center gap-1.5 bg-card-tint border border-border p-1 rounded-lg">
                    <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'" class="rounded px-3 py-1.5 text-[10px] cursor-pointer">All (12)</button>
                    <button @click="activeTab = 'preparing'" :class="activeTab === 'preparing' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'" class="rounded px-3 py-1.5 text-[10px] cursor-pointer">Preparing (4)</button>
                    <button @click="activeTab = 'ready'" :class="activeTab === 'ready' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'" class="rounded px-3 py-1.5 text-[10px] cursor-pointer">Ready (3)</button>
                    <button @click="activeTab = 'delivery'" :class="activeTab === 'delivery' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'" class="rounded px-3 py-1.5 text-[10px] cursor-pointer">Out for Delivery (2)</button>
                </div>

                <!-- Order rows list (Mockup Table Style) -->
                <div class="divide-y divide-border space-y-2.5">
                    <template x-for="o in orders" :key="o.id">
                        <div 
                            x-show="activeTab === 'all' || o.status === activeTab"
                            class="flex flex-col xl:flex-row items-start xl:items-center justify-between py-3.5 gap-4 hover:bg-card-tint/40 transition-all rounded-lg px-2"
                        >
                            <!-- Col 1: Order ID & Channel -->
                            <div class="w-full xl:w-24 shrink-0 space-y-1">
                                <span class="text-xs font-black text-orange block" x-text="`#${o.id}`"></span>
                                <span class="text-[9px] text-muted block" x-text="o.time"></span>
                                
                                <span 
                                    class="inline-flex items-center gap-1 text-[8px] font-bold uppercase tracking-wider mt-1 px-1.5 py-0.5 rounded"
                                    :class="{
                                        'bg-teal/10 text-teal': o.type === 'dine-in',
                                        'bg-orange/10 text-orange': o.type === 'packed'
                                    }"
                                >
                                    <span x-text="o.type === 'dine-in' ? '🍽️ Dining' : '🥡 Packed'"></span>
                                </span>
                            </div>

                            <!-- Col 2: Customer Contacts -->
                            <div class="flex-1 min-w-0 space-y-0.5">
                                <h4 class="text-xs font-black text-ink truncate" x-text="o.customer"></h4>
                                <span class="text-[10px] text-muted block" x-text="o.phone"></span>
                                <template x-if="o.email">
                                    <span class="text-[9px] text-orange/95 font-bold block truncate" x-text="o.email"></span>
                                </template>
                            </div>

                            <!-- Col 3: Items list previews -->
                            <div class="w-full xl:w-40 shrink-0 space-y-1 text-left">
                                <div class="space-y-0.5">
                                    <template x-for="item in o.items">
                                        <span class="block text-[10px] text-slate-700 font-semibold truncate" x-text="`• ${item}`"></span>
                                    </template>
                                </div>
                                <template x-if="o.extra > 0">
                                    <span class="inline-block text-[8px] font-bold text-white bg-orange px-1.5 py-0.5 rounded-full" x-text="`+ ${o.extra} more`"></span>
                                </template>
                            </div>

                            <!-- Col 4: Amount & Payments -->
                            <div class="w-full xl:w-20 shrink-0 text-left space-y-0.5">
                                <span class="text-xs font-black text-ink block" x-text="o.amount"></span>
                                <span class="text-[8px] font-bold text-teal block" x-text="o.paymentStatus"></span>
                                <template x-if="o.method">
                                    <span class="text-[8px] text-muted block" x-text="`💸 ${o.method}`"></span>
                                </template>
                            </div>

                            <!-- Col 5: Status pill & elapsed time -->
                            <div class="w-full xl:w-32 shrink-0 flex items-center justify-between gap-2.5">
                                <div>
                                    <span 
                                        class="inline-flex items-center rounded-lg px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-wider"
                                        :class="{
                                            'bg-orange/15 text-orange': o.status === 'preparing',
                                            'bg-teal/15 text-teal': o.status === 'ready',
                                            'bg-blue-500/10 text-blue-500': o.status === 'delivery'
                                        }"
                                        x-text="o.status"
                                    ></span>
                                    <span class="block text-[8px] text-muted mt-0.5" x-text="o.elapsed"></span>
                                </div>
                                
                                <!-- Ellipsis action menu button -->
                                <button 
                                    @click="
                                        if (o.status === 'preparing') changeStatus(o.id, 'ready');
                                        else if (o.status === 'ready') changeStatus(o.id, 'delivery');
                                    "
                                    class="rounded-lg border border-border px-2.5 py-1 text-muted hover:text-ink cursor-pointer hover:bg-card-tint transition-all text-xs"
                                >
                                    ⋮
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-4 border-t border-border/60 text-center">
                    <a href="/orders" class="text-[10px] font-extrabold text-orange hover:underline uppercase tracking-wider">View all live orders →</a>
                </div>
            </x-card>
        </div>

        <!-- Right Sidebar Cards (No SVGs/Charts) -->
        <div class="space-y-6">
            <!-- Card 1: Order Status Overview -->
            <x-card variant="default" class="p-4 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-border">
                    <h3 class="text-xs font-black text-ink uppercase tracking-wider">Order Status Overview</h3>
                    <select class="rounded border border-border bg-card-tint text-[9px] font-semibold text-ink outline-none px-2 py-0.5 cursor-pointer">
                        <option>Today</option>
                        <option>Weekly</option>
                    </select>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <!-- Typography Box (No SVG) -->
                    <div class="h-20 w-20 rounded-full border-4 border-orange flex flex-col items-center justify-center shrink-0">
                        <span class="text-xl font-black text-ink leading-none">12</span>
                        <span class="text-[7px] text-muted font-bold uppercase mt-1">Total</span>
                    </div>

                    <!-- Legend -->
                    <div class="flex-1 space-y-1 text-[10px] font-semibold text-muted">
                        <div class="flex justify-between"><span>🟠 Preparing</span><span class="text-ink">4 (33%)</span></div>
                        <div class="flex justify-between"><span>🟢 Ready</span><span class="text-ink">3 (25%)</span></div>
                        <div class="flex justify-between"><span>🔵 Out for Delivery</span><span class="text-ink">2 (17%)</span></div>
                        <div class="flex justify-between"><span>🟣 Completed</span><span class="text-ink">2 (17%)</span></div>
                        <div class="flex justify-between"><span>🔴 Cancelled</span><span class="text-ink">1 (8%)</span></div>
                    </div>
                </div>
            </x-card>

            <!-- Card 2: Top Selling Items -->
            <x-card variant="default" class="p-4 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-border">
                    <h3 class="text-xs font-black text-ink uppercase tracking-wider">Top Selling Items</h3>
                    <a href="/reports" class="text-[9px] font-bold text-orange hover:underline">View report →</a>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-orange bg-orange/10 w-5 h-5 rounded-full flex items-center justify-center font-bold">1</span>
                            <span>🍗</span>
                            <div>
                                <span class="text-ink block">8 Pc Hot & Crispy Chicken</span>
                                <span class="text-[9px] text-muted block mt-0.5">245 orders</span>
                            </div>
                        </div>
                        <span class="text-ink font-bold">₹ 768</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-orange bg-orange/10 w-5 h-5 rounded-full flex items-center justify-center font-bold">2</span>
                            <span>🍔</span>
                            <div>
                                <span class="text-ink block">Zinger Burger</span>
                                <span class="text-[9px] text-muted block mt-0.5">198 orders</span>
                            </div>
                        </div>
                        <span class="text-ink font-bold">₹ 199</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-orange bg-orange/10 w-5 h-5 rounded-full flex items-center justify-center font-bold">3</span>
                            <span>🍿</span>
                            <div>
                                <span class="text-ink block">Chicken Popcorn (Large)</span>
                                <span class="text-[9px] text-muted block mt-0.5">176 orders</span>
                            </div>
                        </div>
                        <span class="text-ink font-bold">₹ 249</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-orange bg-orange/10 w-5 h-5 rounded-full flex items-center justify-center font-bold">4</span>
                            <span>🪣</span>
                            <div>
                                <span class="text-ink block">Smoky Red Bucket</span>
                                <span class="text-[9px] text-muted block mt-0.5">142 orders</span>
                            </div>
                        </div>
                        <span class="text-ink font-bold">₹ 599</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-orange bg-orange/10 w-5 h-5 rounded-full flex items-center justify-center font-bold">5</span>
                            <span>🥣</span>
                            <div>
                                <span class="text-ink block">5 in 1 Rice Bowl</span>
                                <span class="text-[9px] text-muted block mt-0.5">118 orders</span>
                            </div>
                        </div>
                        <span class="text-ink font-bold">₹ 249</span>
                    </div>
                </div>
            </x-card>
        </div>

    </div>

</div>
@endsection
