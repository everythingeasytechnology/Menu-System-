@extends('layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
<div class="space-y-8" x-data="{
    activeTab: 'sales',
    timePeriod: '7days',
    
    // Mock Sales Data
    salesSummary: {
        totalRevenue: '₹ 1,48,560',
        totalOrders: 382,
        avgOrderValue: '₹ 388',
        growthRate: '+14.2% from last week'
    },
    
    paymentBreakdown: [
        { method: 'UPI (GPay/PhonePe)', amount: '₹ 74,280', percentage: 50, color: 'bg-teal' },
        { method: 'Cash', amount: '₹ 44,568', percentage: 30, color: 'bg-orange' },
        { method: 'Credit/Debit Card', amount: '₹ 29,712', percentage: 20, color: 'bg-blue-500' }
    ],

    salesTrends: [
        { day: 'Mon', revenue: 18500, label: '₹ 18.5k' },
        { day: 'Tue', revenue: 21200, label: '₹ 21.2k' },
        { day: 'Wed', revenue: 19800, label: '₹ 19.8k' },
        { day: 'Thu', revenue: 24500, label: '₹ 24.5k' },
        { day: 'Fri', revenue: 28900, label: '₹ 28.9k' },
        { day: 'Sat', revenue: 35600, label: '₹ 35.6k' },
        { day: 'Sun', revenue: 32000, label: '₹ 32.0k' }
    ],

    // Deep Detail: Recent Sales Ledger
    detailedTransactions: [
        { id: 'TXN-901', date: '2026-08-03 15:42', orderId: 'KFC1256', point: 'Table 4', items: 'Steak, Truffle Fries', amount: '₹ 485', method: 'UPI', status: 'Settled' },
        { id: 'TXN-902', date: '2026-08-03 15:15', orderId: 'KFC1255', point: 'Table 2', items: 'Lava Cake, Cappuccino', amount: '₹ 395', method: 'Cash', status: 'Settled' },
        { id: 'TXN-903', date: '2026-08-03 14:58', orderId: 'KFC1254', point: 'Room 302', items: 'Salmon, Truffle Fries', amount: '₹ 760', method: 'Card', status: 'Settled' },
        { id: 'TXN-904', date: '2026-08-03 14:20', orderId: 'KFC1253', point: 'Table 8', items: 'Double Burger, Coke', amount: '₹ 325', method: 'UPI', status: 'Settled' },
        { id: 'TXN-905', date: '2026-08-03 13:45', orderId: 'KFC1252', point: 'Takeaway', items: 'Paneer Wrap, Fanta', amount: '₹ 450', method: 'UPI', status: 'Settled' },
        { id: 'TXN-906', date: '2026-08-03 13:10', orderId: 'KFC1251', point: 'Room 105', items: 'Aged Ribeye, Lime Soda', amount: '₹ 820', method: 'Card', status: 'Settled' },
        { id: 'TXN-907', date: '2026-08-03 12:30', orderId: 'KFC1250', point: 'Table 12', items: 'Garlic Bread, Mocktail', amount: '₹ 280', method: 'Cash', status: 'Settled' }
    ],

    // Mock Order Data
    ordersSummary: {
        total: 382,
        completed: 356,
        cancelled: 26,
        cancelRate: '6.8%'
    },

    channelBreakdown: [
        { channel: 'Dine-In', orders: 198, revenue: '₹ 85,140', icon: '🍽️', percentage: 52 },
        { channel: 'Takeaway', orders: 114, revenue: '₹ 39,900', icon: '🛍️', percentage: 30 },
        { channel: 'Delivery', orders: 70, revenue: '₹ 23,520', icon: '🛵', percentage: 18 }
    ],

    // Deep Detail: Cancelled Orders Log
    cancelledOrdersList: [
        { orderId: 'KFC1210', date: '2026-08-03 10:15', point: 'Table 1', items: '2 x Zinger Burger', amount: '₹ 340', reason: 'Customer changed mind', staff: 'Vikram Singh' },
        { orderId: 'KFC1194', date: '2026-08-02 19:42', point: 'Room 104', items: '1 x Atlantic Salmon', amount: '₹ 690', reason: 'Out of stock items', staff: 'Amit Kumar' },
        { orderId: 'KFC1188', date: '2026-08-02 18:10', point: 'Takeaway', items: '4 x Cappuccino', amount: '₹ 440', reason: 'Delay in preparation', staff: 'Pooja Verma' },
        { orderId: 'KFC1152', date: '2026-08-01 21:05', point: 'Table 9', items: '1 x Truffle Fries', amount: '₹ 150', reason: 'Order placed by mistake', staff: 'Rohit Sen' }
    ],

    // Mock Item Performance Data
    topItems: [
        { code: 'ITM01', name: 'Dry Aged Ribeye Steak', category: 'Mains', qty: 142, price: '₹ 300', revenue: '₹ 42,600', stock: '54 units', margin: '42%' },
        { code: 'ITM02', name: 'Parmesan Truffle Fries', category: 'Sides', qty: 98, price: '₹ 150', revenue: '₹ 14,700', stock: '22 units', margin: '68%' },
        { code: 'ITM03', name: 'Warm Chocolate Lava Cake', category: 'Desserts', qty: 84, price: '₹ 120', revenue: '₹ 10,080', stock: '18 units', margin: '55%' },
        { code: 'ITM04', name: 'Pan-Seared Atlantic Salmon', category: 'Mains', qty: 76, price: '₹ 300', revenue: '₹ 22,800', stock: '12 units', margin: '48%' },
        { code: 'ITM05', name: 'Double Shot Cappuccino', category: 'Beverages', qty: 64, price: '₹ 70', revenue: '₹ 4,480', stock: '110 units', margin: '74%' }
    ],

    // Mock Staff Performance Data
    staffPerformance: [
        { code: 'EMP-001', name: 'Amit Kumar', role: 'Chef', shift: 'Morning Shift', hours: 42, ordersServed: 184, errorRate: '0.5%', rating: '4.8★', status: 'Active' },
        { code: 'EMP-003', name: 'Neha Sharma', role: 'Manager', shift: 'Morning Shift', hours: 45, ordersServed: 120, errorRate: '0.0%', rating: '4.9★', status: 'Active' },
        { code: 'EMP-002', name: 'Vikram Singh', role: 'Waiter', shift: 'Evening Shift', hours: 38, ordersServed: 96, errorRate: '1.2%', rating: '4.6★', status: 'Active' },
        { code: 'EMP-006', name: 'Pooja Verma', role: 'Waiter', shift: 'Evening Shift', hours: 40, ordersServed: 84, errorRate: '0.8%', rating: '4.7★', status: 'Active' },
        { code: 'EMP-004', name: 'Rohit Sen', role: 'Cashier', shift: 'Morning Shift', hours: 35, ordersServed: 78, errorRate: '0.2%', rating: '4.5★', status: 'On Break' }
    ],

    // Update charts/metrics on period change
    changePeriod(period) {
        this.timePeriod = period;
        if (period === 'today') {
            this.salesSummary.totalRevenue = '₹ 24,560';
            this.salesSummary.totalOrders = 68;
            this.salesSummary.avgOrderValue = '₹ 361';
            this.salesSummary.growthRate = 'Live stats updated just now';
        } else if (period === 'yesterday') {
            this.salesSummary.totalRevenue = '₹ 28,900';
            this.salesSummary.totalOrders = 78;
            this.salesSummary.avgOrderValue = '₹ 370';
            this.salesSummary.growthRate = '-2% from daily average';
        } else {
            this.salesSummary.totalRevenue = '₹ 1,48,560';
            this.salesSummary.totalOrders = 382;
            this.salesSummary.avgOrderValue = '₹ 388';
            this.salesSummary.growthRate = '+14.2% from last week';
        }
    }
}">
    <!-- Page Header & Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Reports Dashboard</h1>
            <p class="text-sm text-muted mt-1">Analyze revenue statistics, order splits, item performance, and staff logs.</p>
        </div>

        <!-- Period selector -->
        <div class="flex items-center gap-1.5 bg-card-tint border border-border p-1 rounded-xl">
            <button 
                @click="changePeriod('today')"
                :class="timePeriod === 'today' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                class="rounded-lg px-3 py-1.5 text-xs transition-all cursor-pointer"
            >
                Today
            </button>
            <button 
                @click="changePeriod('yesterday')"
                :class="timePeriod === 'yesterday' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                class="rounded-lg px-3 py-1.5 text-xs transition-all cursor-pointer"
            >
                Yesterday
            </button>
            <button 
                @click="changePeriod('7days')"
                :class="timePeriod === '7days' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                class="rounded-lg px-3 py-1.5 text-xs transition-all cursor-pointer"
            >
                Last 7 Days
            </button>
        </div>
    </div>

    <!-- Reports Menu Navigation Tabs -->
    <x-card class="p-1 rounded-2xl flex items-center gap-1.5 w-full bg-card border border-border overflow-x-auto scrollbar-none" variant="default">
        <button 
            @click="activeTab = 'sales'"
            :class="activeTab === 'sales' ? 'bg-orange text-white shadow-sm font-bold' : 'text-muted hover:text-ink font-semibold hover:bg-card-tint'"
            class="flex-1 py-3 rounded-xl text-xs transition-all cursor-pointer text-center"
        >
            📊 Sales & Revenue
        </button>
        <button 
            @click="activeTab = 'orders'"
            :class="activeTab === 'orders' ? 'bg-orange text-white shadow-sm font-bold' : 'text-muted hover:text-ink font-semibold hover:bg-card-tint'"
            class="flex-1 py-3 rounded-xl text-xs transition-all cursor-pointer text-center"
        >
            🛍️ Orders split
        </button>
        <button 
            @click="activeTab = 'items'"
            :class="activeTab === 'items' ? 'bg-orange text-white shadow-sm font-bold' : 'text-muted hover:text-ink font-semibold hover:bg-card-tint'"
            class="flex-1 py-3 rounded-xl text-xs transition-all cursor-pointer text-center"
        >
            🍔 Item Performance
        </button>
        <button 
            @click="activeTab = 'staff'"
            :class="activeTab === 'staff' ? 'bg-orange text-white shadow-sm font-bold' : 'text-muted hover:text-ink font-semibold hover:bg-card-tint'"
            class="flex-1 py-3 rounded-xl text-xs transition-all cursor-pointer text-center"
        >
            🤵 Staff Report
        </button>
    </x-card>

    <!-- Content Sections based on Active Tab -->

    <!-- 1. SALES REPORT -->
    <div x-show="activeTab === 'sales'" class="space-y-6" x-transition>
        <!-- Metric summaries -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Gross Revenue</p>
                <h3 class="text-3xl font-extrabold text-ink mt-2" x-text="salesSummary.totalRevenue">₹ 1,48,560</h3>
                <span class="text-[11px] text-teal font-bold block mt-1" x-text="salesSummary.growthRate">+14.2% from last week</span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-teal"></div>
            </x-card>
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Sales Count</p>
                <h3 class="text-3xl font-extrabold text-ink mt-2" x-text="salesSummary.totalOrders">382</h3>
                <span class="text-[11px] text-muted font-bold block mt-1">Orders processed</span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-orange"></div>
            </x-card>
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Average Order Ticket</p>
                <h3 class="text-3xl font-extrabold text-ink mt-2" x-text="salesSummary.avgOrderValue">₹ 388</h3>
                <span class="text-[11px] text-muted font-bold block mt-1">Spend per consumer</span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500"></div>
            </x-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sales trends graph -->
            <x-card class="p-6 lg:col-span-2 flex flex-col justify-between" variant="default">
                <div>
                    <h3 class="text-sm font-bold text-ink mb-1">Weekly Sales Trends</h3>
                    <p class="text-xs text-muted">Daily distribution of revenue generated across current week.</p>
                </div>

                <div class="flex items-end justify-between h-48 mt-8 border-b border-border pb-2">
                    <template x-for="trend in salesTrends" :key="trend.day">
                        <div class="flex flex-col items-center gap-2 flex-1">
                            <span class="text-[9px] font-bold text-ink" x-text="trend.label"></span>
                            <div 
                                class="w-8 bg-orange hover:bg-orange/95 rounded-t-lg transition-all"
                                :style="`height: ${(trend.revenue / 38000) * 120}px`"
                            ></div>
                            <span class="text-xs font-semibold text-muted" x-text="trend.day"></span>
                        </div>
                    </template>
                </div>
            </x-card>

            <!-- Payments ledger breakdown -->
            <x-card class="p-6 flex flex-col justify-between" variant="default">
                <div>
                    <h3 class="text-sm font-bold text-ink mb-1">Payment Modes</h3>
                    <p class="text-xs text-muted">A Breakdown of customer payment modes and UPI vs cash ratio.</p>
                </div>

                <div class="space-y-5 mt-6">
                    <template x-for="pm in paymentBreakdown" :key="pm.method">
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-muted" x-text="pm.method">UPI</span>
                                <span class="text-ink" x-text="`${pm.amount} (${pm.percentage}%)`"></span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-[#161615] h-2 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" :class="pm.color" :style="`width: ${pm.percentage}%`"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </x-card>
        </div>

        <!-- Deep Detail: Sales Transactions List -->
        <x-card class="p-0 overflow-hidden" variant="default">
            <div class="p-6 border-b border-border">
                <h3 class="text-sm font-bold text-ink mb-1">Detailed Sales Ledger (Transactions History)</h3>
                <p class="text-xs text-muted">A detailed log of recent settled invoices, order locations, items breakdown, and payment methods.</p>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                        <th class="py-3.5 px-5">Transaction ID</th>
                        <th class="py-3.5 px-5">Date/Time</th>
                        <th class="py-3.5 px-5">Ticket No</th>
                        <th class="py-3.5 px-5">Location</th>
                        <th class="py-3.5 px-5">Items Served</th>
                        <th class="py-3.5 px-5">Pay Method</th>
                        <th class="py-3.5 px-5 text-right">Settled Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border text-xs text-ink">
                    <template x-for="txn in detailedTransactions" :key="txn.id">
                        <tr class="hover:bg-card-tint transition-all">
                            <td class="py-3.5 px-5 font-bold uppercase text-ink" x-text="txn.id">TXN-901</td>
                            <td class="py-3.5 px-5 text-muted" x-text="txn.date">2026-08-03 14:24</td>
                            <td class="py-3.5 px-5 font-bold text-ink" x-text="txn.orderId">KFC1256</td>
                            <td class="py-3.5 px-5 font-medium text-ink" x-text="txn.point">Table 4</td>
                            <td class="py-3.5 px-5 text-muted" x-text="txn.items">Steak, Truffle Fries</td>
                            <td class="py-3.5 px-5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-[#161615] dark:text-gray-300" x-text="txn.method">UPI</span>
                            </td>
                            <td class="py-3.5 px-5 text-right font-bold text-teal" x-text="txn.amount">₹ 485</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </x-card>
    </div>

    <!-- 2. ORDERS REPORT -->
    <div x-show="activeTab === 'orders'" class="space-y-6" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Total Received</p>
                <h3 class="text-3xl font-extrabold text-ink mt-2" x-text="ordersSummary.total">382</h3>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500"></div>
            </x-card>
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Completed Orders</p>
                <h3 class="text-3xl font-extrabold text-teal mt-2" x-text="ordersSummary.completed">356</h3>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-teal"></div>
            </x-card>
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Cancelled Orders</p>
                <h3 class="text-3xl font-extrabold text-danger mt-2" x-text="ordersSummary.cancelled">26</h3>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-danger"></div>
            </x-card>
        </div>

        <!-- Channel Splitting rows -->
        <x-card class="p-6" variant="default">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-ink mb-1">Orders Splitting Channels</h3>
                <p class="text-xs text-muted">Distribution of orders placed via Dine-in QR scans, Takeaway, and Delivery riders.</p>
            </div>

            <div class="space-y-6 mt-6">
                <template x-for="ch in channelBreakdown" :key="ch.channel">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-[#161615] flex items-center justify-center text-xl" x-text="ch.icon"></div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1.5">
                                <h4 class="text-xs font-bold text-ink" x-text="ch.channel">Dine-In</h4>
                                <span class="text-xs text-muted" x-text="`${ch.orders} Orders (${ch.percentage}%) - ${ch.revenue}`"></span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-[#161615] h-2.5 rounded-full overflow-hidden">
                                <div class="h-full bg-orange rounded-full" :style="`width: ${ch.percentage}%`"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </x-card>

        <!-- Deep Detail: Cancelled Orders Detailed Table -->
        <x-card class="p-0 overflow-hidden" variant="default">
            <div class="p-6 border-b border-border">
                <h3 class="text-sm font-bold text-ink mb-1">Cancelled Orders Audit Log</h3>
                <p class="text-xs text-muted">Detailed overview of order cancellations, reasons, and responsible staff profiles.</p>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                        <th class="py-3.5 px-5">Order ID</th>
                        <th class="py-3.5 px-5">Date/Time</th>
                        <th class="py-3.5 px-5">Location</th>
                        <th class="py-3.5 px-5">Cancelled Items</th>
                        <th class="py-3.5 px-5">Loss Value</th>
                        <th class="py-3.5 px-5">Cancellation Reason</th>
                        <th class="py-3.5 px-5 text-right">Staff Associated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border text-xs text-ink">
                    <template x-for="co in cancelledOrdersList" :key="co.orderId">
                        <tr class="hover:bg-card-tint transition-all">
                            <td class="py-3.5 px-5 font-bold text-danger" x-text="co.orderId">KFC1210</td>
                            <td class="py-3.5 px-5 text-muted" x-text="co.date">2026-08-03 10:15</td>
                            <td class="py-3.5 px-5 font-medium text-ink" x-text="co.point">Table 1</td>
                            <td class="py-3.5 px-5 text-muted" x-text="co.items">2 x Zinger Burger</td>
                            <td class="py-3.5 px-5 font-bold text-danger" x-text="co.amount">₹ 340</td>
                            <td class="py-3.5 px-5 font-semibold text-ink" x-text="co.reason">Customer changed mind</td>
                            <td class="py-3.5 px-5 text-right text-muted" x-text="co.staff">Vikram Singh</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </x-card>
    </div>

    <!-- 3. ITEM PERFORMANCE -->
    <div x-show="activeTab === 'items'" class="space-y-6" x-transition>
        <x-card class="p-0 overflow-hidden" variant="default">
            <div class="p-6 border-b border-border">
                <h3 class="text-sm font-bold text-ink mb-1">Detailed Item Sales Roster</h3>
                <p class="text-xs text-muted">Complete breakdown of quantities sold, item catalog prices, live stock statuses, and profit margins.</p>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                        <th class="py-3.5 px-5">Item Code</th>
                        <th class="py-3.5 px-5">Item Name</th>
                        <th class="py-3.5 px-5">Category</th>
                        <th class="py-3.5 px-5">Unit Price</th>
                        <th class="py-3.5 px-5">Quantity Sold</th>
                        <th class="py-3.5 px-5">Current Stock</th>
                        <th class="py-3.5 px-5">Profit Margin</th>
                        <th class="py-3.5 px-5 text-right">Revenue Generated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border text-xs text-ink">
                    <template x-for="item in topItems" :key="item.code">
                        <tr class="hover:bg-card-tint transition-all">
                            <td class="py-3.5 px-5 font-bold uppercase text-ink" x-text="item.code">ITM01</td>
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-lg w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#161615] flex items-center justify-center" x-text="item.icon"></span>
                                    <span class="font-bold text-ink" x-text="item.name">Dry Aged Steak</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 text-muted" x-text="item.category">Mains</td>
                            <td class="py-3.5 px-5 font-medium text-ink" x-text="item.price">₹ 300</td>
                            <td class="py-3.5 px-5 font-bold text-ink" x-text="item.qty">142</td>
                            <td class="py-3.5 px-5 text-muted font-medium" x-text="item.stock">54 units</td>
                            <td class="py-3.5 px-5 text-teal font-bold" x-text="item.margin">42%</td>
                            <td class="py-3.5 px-5 text-right font-bold text-teal" x-text="item.revenue">₹ 42,600</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </x-card>
    </div>

    <!-- 4. STAFF PERFORMANCE -->
    <div x-show="activeTab === 'staff'" class="space-y-6" x-transition>
        <x-card class="p-0 overflow-hidden" variant="default">
            <div class="p-6 border-b border-border">
                <h3 class="text-sm font-bold text-ink mb-1">Detailed Staff Productivity & Rating Ledger</h3>
                <p class="text-xs text-muted">Review worked hours log, served order tickets volume, order accuracy ratings, and overall feedback index.</p>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                        <th class="py-3.5 px-5">Code</th>
                        <th class="py-3.5 px-5">Employee Name</th>
                        <th class="py-3.5 px-5">Role</th>
                        <th class="py-3.5 px-5">Shift</th>
                        <th class="py-3.5 px-5">Hours Logged</th>
                        <th class="py-3.5 px-5">Orders Served</th>
                        <th class="py-3.5 px-5">Accuracy Error</th>
                        <th class="py-3.5 px-5">Customer Rating</th>
                        <th class="py-3.5 px-5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border text-xs text-ink">
                    <template x-for="st in staffPerformance" :key="st.code">
                        <tr class="hover:bg-card-tint transition-all">
                            <td class="py-3.5 px-5 font-bold text-ink" x-text="st.code">EMP-001</td>
                            <td class="py-3.5 px-5 font-bold text-ink" x-text="st.name">Amit Kumar</td>
                            <td class="py-3.5 px-5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-700 dark:bg-[#161615] dark:text-gray-300" x-text="st.role">Chef</span>
                            </td>
                            <td class="py-3.5 px-5 text-muted" x-text="st.shift">Morning Shift</td>
                            <td class="py-3.5 px-5 font-medium text-ink" x-text="`${st.hours} hrs`">42 hrs</td>
                            <td class="py-3.5 px-5 font-bold text-ink" x-text="st.ordersServed">184</td>
                            <td class="py-3.5 px-5 font-semibold text-danger" x-text="st.errorRate">0.5%</td>
                            <td class="py-3.5 px-5 font-bold text-orange" x-text="st.rating">4.8★</td>
                            <td class="py-3.5 px-5 text-right">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="st.status === 'Active' ? 'bg-teal' : 'bg-orange'"></span>
                                    <span class="font-semibold text-ink" x-text="st.status === 'Active' ? 'Active' : 'On Leave'"></span>
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </x-card>
    </div>
</div>
@endsection
