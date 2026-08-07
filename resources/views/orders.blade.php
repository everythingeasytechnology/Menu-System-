@extends('layouts.app')

@section('title', 'System Orders')

@section('content')
<div 
    x-data="{ 
        activeTab: 'all',
        searchQuery: '',
        statusFilter: 'all',
        selectedOrderId: '#43291',
        isLoading: false,

        orders: [
            {
                id: '#43291',
                channel: '🍽️ Dining',
                customer: 'Rahul Sharma',
                phone: '9876543210',
                email: 'rahul@example.com',
                location: 'Table 12 (Ground Floor)',
                amount: '₹ 1,145.50',
                payment: 'Paid via Stripe',
                status: 'preparing',
                items: [
                    { name: '8 Pc Hot & Crispy Chicken', qty: 1, status: 'preparing' },
                    { name: 'Pepsi (1.25 L)', qty: 2, status: 'ready' },
                    { name: 'Parmesan Truffle Fries', qty: 1, status: 'served' }
                ]
            },
            {
                id: '#43290',
                channel: '🥡 Packed',
                customer: 'Arjun Kumar',
                phone: '9876543211',
                email: 'arjun@example.com',
                location: 'Counter 2',
                amount: '₹ 420.80',
                payment: 'Paid',
                status: 'preparing',
                items: [
                    { name: 'Turkey Club Sandwich', qty: 1, status: 'preparing' },
                    { name: 'Fresh Orange Juice', qty: 1, status: 'new' }
                ]
            },
            {
                id: '#43289',
                channel: '🥡 Packed',
                customer: 'Walk-in Customer',
                phone: 'N/A',
                email: '',
                location: 'Counter 1',
                amount: '₹ 152.20',
                payment: 'Paid (Cash)',
                status: 'ready',
                items: [
                    { name: 'Zinger Burger', qty: 1, status: 'ready' },
                    { name: 'Pepsi (600ml)', qty: 1, status: 'served' }
                ]
            },
            {
                id: '#43288',
                channel: '🍽️ Dining',
                customer: 'Priya Verma',
                phone: '9876543290',
                email: 'priya@example.com',
                location: 'Table 4 (Cafe Floor)',
                amount: '₹ 884.40',
                payment: 'Unpaid',
                status: 'new',
                items: [
                    { name: 'Smoky Red Bucket', qty: 1, status: 'new' },
                    { name: 'Garlic Bread (2 Pc)', qty: 2, status: 'new' },
                    { name: 'Lava Cake', qty: 1, status: 'new' }
                ]
            }
        ],

        get selectedOrder() {
            return this.orders.find(o => o.id === this.selectedOrderId);
        },

        triggerSearch() {
            this.isLoading = true;
            setTimeout(() => { this.isLoading = false }, 300);
        },

        // Advance individual item status
        advanceItem(orderId, itemName, nextStatus) {
            let order = this.orders.find(o => o.id === orderId);
            if (order) {
                let item = order.items.find(i => i.name === itemName);
                if (item) {
                    item.status = nextStatus;
                }
                
                // Recalculate global order status based on item states
                let statuses = order.items.map(i => i.status);
                if (statuses.every(s => s === 'served')) {
                    order.status = 'served';
                } else if (statuses.every(s => s === 'ready' || s === 'served')) {
                    order.status = 'ready';
                } else if (statuses.includes('preparing') || statuses.includes('ready')) {
                    order.status = 'preparing';
                } else {
                    order.status = 'new';
                }
            }
        }
    }"
    class="space-y-6"
>
    <!-- Page Title & Control Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-ink">Order Operations</h1>
            <p class="text-xs text-muted mt-0.5">Control individual items and operational status from the kitchen deck.</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                @click="alert('New ticket wizard...')"
                class="rounded-xl bg-orange hover:bg-orange/95 px-4.5 py-2 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer flex items-center gap-1.5"
            >
                <span>+ New Ticket</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Control Grid -->
    <x-card class="p-4" variant="default">
        <div class="flex flex-col lg:flex-row gap-4 justify-between items-center">
            <!-- Tabs (Dine-in, Takeaway, Room Service) -->
            <div class="flex items-center gap-1 bg-card-tint border border-border p-1 rounded-lg w-full lg:w-auto">
                <button 
                    @click="activeTab = 'all'; triggerSearch()"
                    :class="activeTab === 'all' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'"
                    class="flex-1 lg:flex-none rounded px-3 py-1.5 text-[10px] cursor-pointer"
                >
                    All Channels
                </button>
                <button 
                    @click="activeTab = 'dine-in'; triggerSearch()"
                    :class="activeTab === 'dine-in' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'"
                    class="flex-1 lg:flex-none rounded px-3 py-1.5 text-[10px] cursor-pointer"
                >
                    Dining
                </button>
                <button 
                    @click="activeTab = 'takeaway'; triggerSearch()"
                    :class="activeTab === 'takeaway' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'"
                    class="flex-1 lg:flex-none rounded px-3 py-1.5 text-[10px] cursor-pointer"
                >
                    Packed
                </button>
            </div>

            <!-- Search and Status Filters -->
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @input.debounce.300ms="triggerSearch()"
                    placeholder="Search by order ID or table..."
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange outline-none"
                >

                <select 
                    x-model="statusFilter"
                    @change="triggerSearch()"
                    class="rounded-xl border border-border bg-card-tint px-3 py-2 text-xs font-semibold text-ink outline-none cursor-pointer"
                >
                    <option value="all">All Statuses</option>
                    <option value="new">New</option>
                    <option value="preparing">Preparing</option>
                    <option value="ready">Ready</option>
                    <option value="served">Served</option>
                </select>
            </div>
        </div>
    </x-card>

    <!-- Splits Grid: Orders List & Item status control center -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start relative">
        <!-- Skeleton loader overlay -->
        <div x-show="isLoading" class="absolute inset-0 bg-bg/65 backdrop-blur-[1px] z-10 flex justify-center items-center rounded-card" style="display: none;">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-orange border-t-transparent"></div>
        </div>

        <!-- Left: Orders Table list (Col-span 2) -->
        <div class="lg:col-span-2 space-y-4">
            <x-card class="p-4" variant="default">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border text-[10px] font-bold text-muted uppercase tracking-wider">
                                <th class="pb-3.5 pl-2">Order ID</th>
                                <th class="pb-3.5 hidden sm:table-cell">Channel</th>
                                <th class="pb-3.5">Location</th>
                                <th class="pb-3.5 hidden md:table-cell">Customer</th>
                                <th class="pb-3.5 text-center hidden sm:table-cell">Items</th>
                                <th class="pb-3.5">Total</th>
                                <th class="pb-3.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <template x-for="o in orders" :key="o.id">
                                <tr 
                                    x-show="
                                        (activeTab === 'all' || 
                                         (activeTab === 'dine-in' && o.channel.includes('Dining')) || 
                                         (activeTab === 'takeaway' && o.channel.includes('Packed'))) &&
                                        (statusFilter === 'all' || o.status === statusFilter) &&
                                        (searchQuery === '' || o.id.toLowerCase().includes(searchQuery.toLowerCase()) || o.location.toLowerCase().includes(searchQuery.toLowerCase()) || o.customer.toLowerCase().includes(searchQuery.toLowerCase()))
                                    "
                                    @click="selectedOrderId = o.id"
                                    :class="selectedOrderId === o.id ? 'bg-orange/5 font-semibold' : 'hover:bg-card-tint/30'"
                                    class="cursor-pointer transition-all"
                                >
                                    <td class="py-4 pl-2 font-bold text-orange text-xs" x-text="o.id"></td>
                                    <td class="py-4 text-xs font-semibold text-muted hidden sm:table-cell" x-text="o.channel"></td>
                                    <td class="py-4 text-xs text-ink" x-text="o.location"></td>
                                    <td class="py-4 text-xs text-muted hidden md:table-cell">
                                        <div class="font-extrabold text-ink" x-text="o.customer"></div>
                                        <div class="text-[10px] text-muted mt-0.5" x-text="o.phone"></div>
                                        <template x-if="o.email">
                                            <div class="text-[9px] text-orange font-bold mt-0.5" x-text="o.email"></div>
                                        </template>
                                    </td>
                                    <td class="py-4 text-xs text-muted text-center hidden sm:table-cell" x-text="o.items.length"></td>
                                    <td class="py-4 font-bold text-ink text-xs" x-text="o.amount"></td>
                                    <td class="py-4">
                                        <span 
                                            class="inline-flex items-center rounded-lg px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider"
                                            :class="{
                                                'bg-blue-50 text-blue-500 border border-blue-100': o.status === 'new',
                                                'bg-orange/5 text-orange border border-orange/10': o.status === 'preparing',
                                                'bg-teal/5 text-teal border border-teal/10': o.status === 'ready',
                                                'bg-success/5 text-success border border-success/10': o.status === 'served'
                                            }"
                                            x-text="o.status"
                                        ></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Right: Particular Item status Control Panel (Col-span 1) -->
        <div 
            class="lg:col-span-1"
            :class="selectedOrderId ? 'fixed inset-0 z-50 flex items-end justify-center lg:static lg:z-auto lg:flex lg:items-start lg:justify-start bg-navy-deep/60 backdrop-blur-xs lg:bg-transparent lg:backdrop-blur-none p-4' : 'hidden lg:block'"
            @click.self="selectedOrderId = null"
        >
            <template x-if="selectedOrder">
                <x-card class="p-5 space-y-5 w-full max-w-md lg:max-w-none shadow-2xl lg:shadow-none border border-border lg:border-none" variant="default">
                    <!-- Heading info -->
                    <div class="pb-3.5 border-b border-border flex justify-between items-start">
                        <div>
                            <span class="text-[9px] font-extrabold text-muted uppercase tracking-wider">Active Ticket Detail</span>
                            <h3 class="text-sm font-extrabold text-ink block mt-0.5" x-text="selectedOrder.location"></h3>
                            <!-- Customer Details inside details drawer -->
                            <div class="mt-2.5 p-3 rounded-xl bg-card-tint border border-border/60 text-left">
                                <span class="block text-[8px] font-bold text-muted uppercase tracking-wider">Customer Details</span>
                                <h4 class="text-xs font-black text-ink mt-0.5" x-text="selectedOrder.customer"></h4>
                                <span class="block text-[10px] text-muted mt-0.5" x-text="selectedOrder.phone"></span>
                                <template x-if="selectedOrder.email">
                                    <span class="block text-[9px] text-orange font-bold mt-0.5" x-text="selectedOrder.email"></span>
                                </template>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-orange" x-text="selectedOrder.id"></span>
                            <button 
                                type="button"
                                @click="selectedOrderId = null" 
                                class="lg:hidden text-muted hover:text-ink font-bold text-sm cursor-pointer p-1"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- Items list progress tracker -->
                    <div class="space-y-4">
                        <span class="block text-[10px] font-bold text-muted uppercase tracking-wider">Item Progress Control</span>
                        
                        <div class="space-y-3">
                            <template x-for="item in selectedOrder.items" :key="item.name">
                                <div class="bg-card-tint border border-border/80 rounded-xl p-3.5 flex flex-col justify-between gap-3 shadow-xs">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-xs font-extrabold text-ink" x-text="item.name"></span>
                                            <span class="block text-[9px] text-muted mt-0.5" x-text="`Qty: ${item.qty}`"></span>
                                        </div>
                                        
                                        <!-- Item Status Badge -->
                                        <span 
                                            class="inline-flex items-center rounded-lg px-2 py-0.5 text-[8px] font-extrabold uppercase tracking-wider"
                                            :class="{
                                                'bg-blue-50 text-blue-500 border border-blue-100': item.status === 'new',
                                                'bg-orange/5 text-orange border border-orange/10': item.status === 'preparing',
                                                'bg-teal/5 text-teal border border-teal/10': item.status === 'ready',
                                                'bg-success/5 text-success border border-success/10': item.status === 'served'
                                            }"
                                            x-text="item.status"
                                        ></span>
                                    </div>

                                    <!-- Quick actions for this specific item -->
                                    <div class="flex gap-1.5 border-t border-border/40 pt-2.5 justify-end">
                                        <template x-if="item.status === 'new'">
                                            <button 
                                                @click="advanceItem(selectedOrder.id, item.name, 'preparing')"
                                                class="rounded-lg bg-orange/10 hover:bg-orange/15 text-orange px-2.5 py-1 text-[9px] font-bold transition-all cursor-pointer"
                                            >
                                                🍳 Cook
                                            </button>
                                        </template>

                                        <template x-if="item.status === 'preparing'">
                                            <button 
                                                @click="advanceItem(selectedOrder.id, item.name, 'ready')"
                                                class="rounded-lg bg-teal/10 hover:bg-teal/15 text-teal px-2.5 py-1 text-[9px] font-bold transition-all cursor-pointer"
                                            >
                                                🔔 Ready
                                            </button>
                                        </template>

                                        <template x-if="item.status === 'ready'">
                                            <button 
                                                @click="advanceItem(selectedOrder.id, item.name, 'served')"
                                                class="rounded-lg bg-success/10 hover:bg-success/15 text-success px-2.5 py-1 text-[9px] font-bold transition-all cursor-pointer"
                                            >
                                                🍽️ Serve
                                            </button>
                                        </template>

                                        <template x-if="item.status === 'served'">
                                            <span class="text-[9px] font-bold text-success flex items-center gap-1">
                                                ✓ Completed
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer Action details -->
                    <div class="border-t border-border pt-4 flex justify-between items-center text-[10px] text-muted font-bold">
                        <span x-text="`Server: ${selectedOrder.server}`"></span>
                        <span x-text="`Total: ${selectedOrder.amount}`" class="text-ink"></span>
                    </div>
                </x-card>
            </template>

            <template x-if="!selectedOrder">
                <x-card class="p-5 text-center flex flex-col justify-center items-center h-48 border border-dashed border-border" variant="default">
                    <span class="text-xl">🛎️</span>
                    <h3 class="text-xs font-bold text-ink mt-2">No Ticket Selected</h3>
                    <p class="text-[10px] text-muted mt-1 leading-normal">Select an active order ticket on the left ledger to manage item progress.</p>
                </x-card>
            </template>
        </div>
    </div>
</div>
@endsection
