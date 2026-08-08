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
        gstSettings: {
            enabled: {{ $settings->gst_enabled ? 'true' : 'false' }},
            cgstRate: {{ $settings->cgst ?? 2.5 }},
            sgstRate: {{ $settings->sgst ?? 2.5 }},
            brandName: '{{ addslashes($settings->brand_name ?? 'KFC') }}',
            gstNo: '{{ addslashes($settings->gst_no ?? '') }}',
            address: '{{ addslashes($settings->address ?? 'Connaught Place') }}',
            pincode: '{{ addslashes($settings->pincode ?? '110001') }}'
        },

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
                paymentStatus: 'paid',
                status: 'preparing',
                note: 'Please make the chicken extra crispy, and pack dips separately.',
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
                paymentStatus: 'paid',
                status: 'kitchen',
                note: 'No onions in the sandwich, please.',
                items: [
                    { name: 'Turkey Club Sandwich', qty: 1, status: 'kitchen' },
                    { name: 'Fresh Orange Juice', qty: 1, status: 'pending' }
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
                paymentStatus: 'paid',
                status: 'ready',
                note: '',
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
                paymentStatus: 'unpaid',
                status: 'pending',
                note: 'Need 4 extra ketchup packets and plastic straws.',
                items: [
                    { name: 'Smoky Red Bucket', qty: 1, status: 'pending' },
                    { name: 'Garlic Bread (2 Pc)', qty: 2, status: 'pending' },
                    { name: 'Lava Cake', qty: 1, status: 'pending' }
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
                if (statuses.every(s => s === 'served' || s === 'cancelled')) {
                    if (statuses.every(s => s === 'cancelled')) {
                        order.status = 'cancelled';
                    } else {
                        order.status = 'served';
                    }
                } else if (statuses.every(s => s === 'ready' || s === 'served' || s === 'cancelled')) {
                    order.status = 'ready';
                } else if (statuses.includes('preparing')) {
                    order.status = 'preparing';
                } else if (statuses.includes('kitchen')) {
                    order.status = 'kitchen';
                } else {
                    order.status = 'pending';
                }
            }
        },

        // Cancel the entire order ticket
        cancelOrder(orderId) {
            let order = this.orders.find(o => o.id === orderId);
            if (order) {
                order.status = 'cancelled';
                order.items.forEach(i => i.status = 'cancelled');
            }
        },

        // Print POS style bill receipt
        printReceipt(order) {
            if (window.printOrderReceipt) {
                window.printOrderReceipt(order, this.gstSettings);
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
            <x-card class="p-3" variant="default">
                <div class="max-h-[580px] overflow-y-auto overflow-x-auto pr-1">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-card z-10">
                            <tr class="border-b border-border text-[9px] font-bold text-muted uppercase tracking-wider bg-card">
                                <th class="pb-2.5 pl-2">Order ID</th>
                                <th class="pb-2.5 hidden sm:table-cell">Channel</th>
                                <th class="pb-2.5">Location</th>
                                <th class="pb-2.5 hidden md:table-cell">Customer</th>
                                <th class="pb-2.5 text-center hidden sm:table-cell">Items</th>
                                <th class="pb-2.5">Total</th>
                                <th class="pb-2.5 hidden sm:table-cell">Payment</th>
                                <th class="pb-2.5">Status</th>
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
                                    class="cursor-pointer transition-all border-b border-border/40"
                                >
                                    <td class="py-2.5 pl-2 font-bold text-orange text-xs" x-text="o.id"></td>
                                    <td class="py-2.5 text-xs font-semibold text-muted hidden sm:table-cell" x-text="o.channel"></td>
                                    <td class="py-2.5 text-xs text-ink" x-text="o.location"></td>
                                    <td class="py-2.5 text-xs text-muted hidden md:table-cell">
                                        <div class="flex items-center gap-1 font-bold text-ink">
                                            <span x-text="o.customer"></span>
                                            <span class="text-[9px] font-normal text-muted" x-text="`(${o.phone})`"></span>
                                        </div>
                                        <template x-if="o.email">
                                            <div class="text-[9px] text-orange font-medium mt-0.5" x-text="o.email"></div>
                                        </template>
                                    </td>
                                    <td class="py-2.5 text-xs text-muted text-center hidden sm:table-cell" x-text="o.items.length"></td>
                                    <td class="py-2.5 font-bold text-ink text-xs" x-text="o.amount"></td>
                                    <!-- Payment status column -->
                                    <td class="py-2.5 text-xs hidden sm:table-cell">
                                        <span 
                                            class="inline-flex items-center rounded-lg px-2 py-0.5 text-[8px] font-extrabold uppercase tracking-wider"
                                            :class="{
                                                'bg-success/5 text-success border border-success/10': o.paymentStatus === 'paid',
                                                'bg-danger/5 text-danger border border-danger/10': o.paymentStatus === 'unpaid',
                                                'bg-orange/5 text-orange border border-orange/10': o.paymentStatus === 'pending'
                                            }"
                                            x-text="o.paymentStatus"
                                        ></span>
                                    </td>
                                    <td class="py-2.5">
                                        <span 
                                            class="inline-flex items-center rounded-lg px-2 py-0.5 text-[8px] font-extrabold uppercase tracking-wider"
                                            :class="{
                                                'bg-slate-100 text-slate-600 border border-slate-200': o.status === 'pending',
                                                'bg-blue-50 text-blue-500 border border-blue-100': o.status === 'kitchen',
                                                'bg-orange/5 text-orange border border-orange/10': o.status === 'preparing',
                                                'bg-teal/5 text-teal border border-teal/10': o.status === 'ready',
                                                'bg-success/5 text-success border border-success/10': o.status === 'served',
                                                'bg-danger/5 text-danger border border-danger/10': o.status === 'cancelled'
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
                            <div class="mt-1.5 text-[10px] text-slate-500 font-semibold text-left">
                                <span class="text-ink font-extrabold" x-text="selectedOrder.customer"></span>
                                <span x-show="selectedOrder.phone && selectedOrder.phone !== 'N/A'" x-text="` (${selectedOrder.phone})`"></span>
                                <template x-if="selectedOrder.email">
                                    <span x-text="` • ${selectedOrder.email}`" class="text-orange font-bold"></span>
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
                    <div class="space-y-3">
                        <span class="block text-[10px] font-bold text-muted uppercase tracking-wider">Item Progress Control</span>
                        
                        <div class="divide-y divide-border/60">
                            <template x-for="item in selectedOrder.items" :key="item.name">
                                <div class="py-2 flex items-center justify-between gap-3 text-xs">
                                    <div class="min-w-0">
                                        <span class="font-extrabold text-ink truncate block max-w-[200px]" :title="item.name" x-text="item.name"></span>
                                        <span class="block text-[9px] text-muted mt-0.5" x-text="`Qty: ${item.qty}`"></span>
                                    </div>
                                    <div class="flex items-center shrink-0">
                                        <select 
                                            @change="advanceItem(selectedOrder.id, item.name, $event.target.value)"
                                            class="rounded-lg border border-border/85 bg-card-tint py-1 px-1.5 text-[9px] font-extrabold text-ink outline-none cursor-pointer focus:border-orange focus:ring-1 focus:ring-orange/20 transition-all"
                                        >
                                            <option value="pending" :selected="item.status === 'pending'">📋 Pending</option>
                                            <option value="kitchen" :selected="item.status === 'kitchen'">🍳 Kitchen</option>
                                            <option value="preparing" :selected="item.status === 'preparing'">🔥 Preparing</option>
                                            <option value="ready" :selected="item.status === 'ready'">🔔 Ready</option>
                                            <option value="served" :selected="item.status === 'served'">🍽️ Served</option>
                                            <option value="cancelled" :selected="item.status === 'cancelled'">🚫 Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Customer Note / Kitchen instructions -->
                    <template x-if="selectedOrder.note">
                        <div class="bg-orange/5 border border-orange/10 p-2.5 rounded-xl text-left">
                            <span class="block text-[8px] font-bold text-orange uppercase tracking-wider">Kitchen Note / Instructions</span>
                            <p class="text-[10px] italic text-slate-700 leading-normal mt-0.5" x-text="selectedOrder.note"></p>
                        </div>
                    </template>

                    <!-- Footer Action details -->
                    <div class="border-t border-border pt-4 flex flex-col gap-3">
                        <div class="flex justify-between items-center text-[10px] text-muted font-bold">
                            <span x-text="`Payment: ${selectedOrder.payment}`"></span>
                            <span x-text="`Total: ${selectedOrder.amount}`" class="text-ink"></span>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="printReceipt(selectedOrder)"
                                class="flex-1 rounded-xl bg-orange hover:bg-orange/95 text-white py-2.5 text-xs font-bold shadow-md shadow-orange/20 cursor-pointer transition-all text-center flex items-center justify-center gap-1.5"
                            >
                                🖨️ Print Bill
                            </button>
                            <template x-if="selectedOrder.status !== 'served' && selectedOrder.status !== 'cancelled'">
                                <button 
                                    @click="cancelOrder(selectedOrder.id)"
                                    class="flex-1 rounded-xl bg-danger/10 hover:bg-danger/20 text-danger py-2.5 text-xs font-bold transition-all cursor-pointer text-center"
                                >
                                    🚫 Cancel Ticket
                                </button>
                            </template>
                        </div>
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

<script>
    window.printOrderReceipt = function(order, gstSettings) {
        if (!order) return;
        
        let subtotal = order.amount ? parseFloat(order.amount.replace(/[^\d\.]/g, '')) : 0;
        let total = subtotal;
        let cgstAmount = 0;
        let sgstAmount = 0;
        let taxDetailsHtml = '';
        
        if (gstSettings && gstSettings.enabled) {
            let totalTaxRate = gstSettings.cgstRate + gstSettings.sgstRate;
            subtotal = total / (1 + totalTaxRate / 100);
            cgstAmount = subtotal * (gstSettings.cgstRate / 100);
            sgstAmount = subtotal * (gstSettings.sgstRate / 100);
            
            taxDetailsHtml = `
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>CGST (${gstSettings.cgstRate}%):</span>
                    <span>₹ ${cgstAmount.toFixed(2)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px; border-bottom: 1px dashed #000; padding-bottom: 4px;">
                    <span>SGST (${gstSettings.sgstRate}%):</span>
                    <span>₹ ${sgstAmount.toFixed(2)}</span>
                </div>
            `;
        }
        
        let itemsHtml = order.items.map(item => `
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <div style="max-width: 75%; text-align: left;">
                    <span style="font-weight: bold;">${item.name}</span>
                    <span style="display: block; font-size: 8px; color: #555;">Status: ${item.status}</span>
                </div>
                <span>x${item.qty}</span>
            </div>
        `).join('');
        
        let brandName = gstSettings ? gstSettings.brandName : 'KFC';
        let address = gstSettings ? gstSettings.address : 'Connaught Place';
        let pincode = gstSettings ? gstSettings.pincode : '110001';
        let gstNo = gstSettings ? gstSettings.gstNo : '';
        
        let printWindow = window.open('', '_blank', 'width=380,height=600');
        printWindow.document.write(`
            <html>
            <head>
                <title>Bill Receipt - ${order.id}</title>
                <style>
                    @media print {
                        body {
                            width: 74mm;
                            margin: 0;
                            padding: 5px;
                        }
                    }
                    body {
                        font-family: 'Courier New', Courier, monospace;
                        width: 74mm;
                        margin: 0 auto;
                        padding: 10px 5px;
                        font-size: 10px;
                        line-height: 1.4;
                        color: #000;
                    }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .bold { font-weight: bold; }
                    .divider { border-top: 1px dashed #000; margin: 6px 0; }
                    .brand-title { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
                    .receipt-header { margin-bottom: 8px; }
                    .receipt-footer { margin-top: 12px; font-size: 8px; text-align: center; }
                </style>
            </head>
            <body>
                <div class="text-center receipt-header">
                    <div class="brand-title">${brandName}</div>
                    <div>${address}</div>
                    <div>PIN: ${pincode}</div>
                    ${gstNo ? `<div>GSTIN: ${gstNo}</div>` : ''}
                </div>
                
                <div class="divider"></div>
                
                <div style="display: flex; justify-content: space-between; font-size: 9px;">
                    <span>Order ID: <b>${order.id}</b></span>
                    <span>Date: ${new Date().toLocaleDateString()}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 9px; margin-top: 2px;">
                    <span>Channel: ${order.channel}</span>
                    <span>Loc: ${order.location}</span>
                </div>
                <div style="font-size: 9px; margin-top: 2px; text-align: left;">
                    <span>Cust: ${order.customer} ${order.phone !== 'N/A' ? '(' + order.phone + ')' : ''}</span>
                </div>
                
                <div class="divider"></div>
                
                <div style="font-weight: bold; display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>Item Description</span>
                    <span>Qty</span>
                </div>
                
                <div class="divider"></div>
                
                <div>${itemsHtml}</div>
                
                <div class="divider"></div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Subtotal:</span>
                    <span>₹ ${subtotal.toFixed(2)}</span>
                </div>
                
                ${taxDetailsHtml}
                
                <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: bold; margin-top: 4px; border-bottom: 1px dashed #000; padding-bottom: 4px;">
                    <span>TOTAL BILL:</span>
                    <span>${order.amount}</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; font-size: 8px; margin-top: 4px; color: #333;">
                    <span>Payment Status:</span>
                    <span style="text-transform: uppercase; font-weight: bold;">${order.paymentStatus} (${order.payment})</span>
                </div>
                
                <div class="divider"></div>
                
                <div class="receipt-footer">
                    <p class="bold" style="margin: 2px 0;">Thank you for dining with us!</p>
                    <p style="margin: 2px 0;">Powered by EverythingEasy ServiceOS</p>
                </div>
                
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() { window.close(); }, 500);
                    }
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    };
</script>
@endsection
