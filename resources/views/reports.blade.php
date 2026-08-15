@extends('layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'sales' }">
    <!-- Page Header & Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Reports Dashboard</h1>
            <p class="text-sm text-muted mt-1">Analyze revenue statistics, order splits, and item performance.</p>
        </div>

        <!-- Period selector -->
        <div class="flex items-center gap-1.5 bg-card-tint border border-border p-1 rounded-xl">
            <a
                href="{{ route('reports.index', ['period' => 'today']) }}"
                class="rounded-lg px-3 py-1.5 text-xs transition-all {{ $period === 'today' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold' }}"
            >
                Today
            </a>
            <a
                href="{{ route('reports.index', ['period' => 'yesterday']) }}"
                class="rounded-lg px-3 py-1.5 text-xs transition-all {{ $period === 'yesterday' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold' }}"
            >
                Yesterday
            </a>
            <a
                href="{{ route('reports.index', ['period' => '7days']) }}"
                class="rounded-lg px-3 py-1.5 text-xs transition-all {{ $period === '7days' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold' }}"
            >
                Last 7 Days
            </a>
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
    </x-card>

    <!-- Content Sections based on Active Tab -->

    <!-- 1. SALES REPORT -->
    <div x-show="activeTab === 'sales'" class="space-y-6" x-transition>
        <!-- Metric summaries -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Gross Revenue</p>
                <h3 class="text-3xl font-extrabold text-ink mt-2">₹ {{ number_format($salesSummary['total_revenue'], 2) }}</h3>
                <span class="text-[11px] text-muted font-bold block mt-1">Excludes cancelled orders</span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-teal"></div>
            </x-card>
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Sales Count</p>
                <h3 class="text-3xl font-extrabold text-ink mt-2">{{ number_format($salesSummary['total_orders']) }}</h3>
                <span class="text-[11px] text-muted font-bold block mt-1">Orders processed</span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-orange"></div>
            </x-card>
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Average Order Ticket</p>
                <h3 class="text-3xl font-extrabold text-ink mt-2">₹ {{ number_format($salesSummary['avg_order_value'], 2) }}</h3>
                <span class="text-[11px] text-muted font-bold block mt-1">Spend per consumer</span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500"></div>
            </x-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sales trends graph -->
            <x-card class="p-6 lg:col-span-2 flex flex-col justify-between" variant="default">
                <div>
                    <h3 class="text-sm font-bold text-ink mb-1">Sales Trends</h3>
                    <p class="text-xs text-muted">Daily distribution of revenue generated in the selected period.</p>
                </div>

                @if($salesTrends->isEmpty())
                    <div class="flex items-center justify-center h-48 mt-8 text-xs text-muted font-semibold">
                        No sales recorded in this period yet.
                    </div>
                @else
                    @php $maxRevenue = max(1, $salesTrends->max('revenue')); @endphp
                    <div class="flex items-end justify-between h-48 mt-8 border-b border-border pb-2 gap-2">
                        @foreach($salesTrends as $trend)
                            <div class="flex flex-col items-center gap-2 flex-1">
                                <span class="text-[9px] font-bold text-ink">₹{{ number_format($trend['revenue'], 0) }}</span>
                                <div
                                    class="w-8 bg-orange hover:bg-orange/95 rounded-t-lg transition-all"
                                    style="height: {{ max(4, (int) round(($trend['revenue'] / $maxRevenue) * 150)) }}px"
                                ></div>
                                <span class="text-xs font-semibold text-muted">{{ $trend['day'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            <!-- Payments ledger breakdown -->
            <x-card class="p-6 flex flex-col justify-between" variant="default">
                <div>
                    <h3 class="text-sm font-bold text-ink mb-1">Payment Modes</h3>
                    <p class="text-xs text-muted">Breakdown of settled payments by method.</p>
                </div>

                @if($paymentBreakdown->isEmpty())
                    <p class="mt-6 text-xs text-muted font-semibold">No paid transactions in this period yet.</p>
                @else
                    <div class="space-y-5 mt-6">
                        @foreach($paymentBreakdown as $pm)
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-muted">{{ $pm['method'] }}</span>
                                    <span class="text-ink">₹{{ number_format($pm['amount'], 2) }} ({{ $pm['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-[#161615] h-2 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-teal" style="width: {{ $pm['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Deep Detail: Sales Transactions List -->
        <x-card class="p-0 overflow-hidden" variant="default">
            <div class="p-6 border-b border-border">
                <h3 class="text-sm font-bold text-ink mb-1">Detailed Sales Ledger</h3>
                <p class="text-xs text-muted">Recent orders in the selected period, their locations, items, and payment methods.</p>
            </div>

            @if($detailedTransactions->isEmpty())
                <p class="p-6 text-xs text-muted font-semibold">No orders in this period yet.</p>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                            <th class="py-3.5 px-5">Order</th>
                            <th class="py-3.5 px-5">Date/Time</th>
                            <th class="py-3.5 px-5">Location</th>
                            <th class="py-3.5 px-5">Items</th>
                            <th class="py-3.5 px-5">Pay Method</th>
                            <th class="py-3.5 px-5">Status</th>
                            <th class="py-3.5 px-5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-xs text-ink">
                        @foreach($detailedTransactions as $txn)
                            <tr class="hover:bg-card-tint transition-all">
                                <td class="py-3.5 px-5 font-bold uppercase text-ink">{{ $txn['display_id'] }}</td>
                                <td class="py-3.5 px-5 text-muted">{{ $txn['date'] }}</td>
                                <td class="py-3.5 px-5 font-medium text-ink">{{ $txn['location'] }}</td>
                                <td class="py-3.5 px-5 text-muted">{{ $txn['items'] ?: '—' }}</td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-[#161615] dark:text-gray-300">{{ $txn['method'] }}</span>
                                </td>
                                <td class="py-3.5 px-5 text-muted">{{ $txn['status'] }}</td>
                                <td class="py-3.5 px-5 text-right font-bold text-teal">₹{{ number_format($txn['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </x-card>
    </div>

    <!-- 2. ORDERS REPORT -->
    <div x-show="activeTab === 'orders'" class="space-y-6" x-transition style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Total Received</p>
                <h3 class="text-3xl font-extrabold text-ink mt-2">{{ number_format($ordersSummary['total']) }}</h3>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500"></div>
            </x-card>
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Completed Orders</p>
                <h3 class="text-3xl font-extrabold text-teal mt-2">{{ number_format($ordersSummary['completed']) }}</h3>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-teal"></div>
            </x-card>
            <x-card class="p-6 relative overflow-hidden" variant="default">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Cancelled Orders</p>
                <h3 class="text-3xl font-extrabold text-danger mt-2">{{ number_format($ordersSummary['cancelled']) }}</h3>
                <span class="text-[11px] text-muted font-bold block mt-1">{{ $ordersSummary['cancel_rate'] }}% cancel rate</span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-danger"></div>
            </x-card>
        </div>

        <!-- Channel Splitting rows -->
        <x-card class="p-6" variant="default">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-ink mb-1">Orders Splitting Channels</h3>
                <p class="text-xs text-muted">Distribution of orders by dine-in, room service, and takeaway.</p>
            </div>

            @if($channelBreakdown->isEmpty())
                <p class="text-xs text-muted font-semibold">No orders in this period yet.</p>
            @else
                <div class="space-y-6 mt-6">
                    @foreach($channelBreakdown as $ch)
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-[#161615] flex items-center justify-center text-xl">
                                {{ $ch['channel'] === 'Takeaway' ? '🛍️' : ($ch['channel'] === 'Room Service' ? '🛎️' : '🍽️') }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1.5">
                                    <h4 class="text-xs font-bold text-ink">{{ $ch['channel'] }}</h4>
                                    <span class="text-xs text-muted">{{ $ch['orders'] }} Orders ({{ $ch['percentage'] }}%) - ₹{{ number_format($ch['revenue'], 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-[#161615] h-2.5 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange rounded-full" style="width: {{ $ch['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Deep Detail: Cancelled Orders Detailed Table -->
        <x-card class="p-0 overflow-hidden" variant="default">
            <div class="p-6 border-b border-border">
                <h3 class="text-sm font-bold text-ink mb-1">Cancelled Orders Log</h3>
                <p class="text-xs text-muted">Orders cancelled in the selected period.</p>
            </div>

            @if($cancelledOrdersList->isEmpty())
                <p class="p-6 text-xs text-muted font-semibold">No cancelled orders in this period.</p>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                            <th class="py-3.5 px-5">Order</th>
                            <th class="py-3.5 px-5">Date/Time</th>
                            <th class="py-3.5 px-5">Location</th>
                            <th class="py-3.5 px-5">Items</th>
                            <th class="py-3.5 px-5">Notes</th>
                            <th class="py-3.5 px-5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-xs text-ink">
                        @foreach($cancelledOrdersList as $co)
                            <tr class="hover:bg-card-tint transition-all">
                                <td class="py-3.5 px-5 font-bold text-danger">{{ $co['display_id'] }}</td>
                                <td class="py-3.5 px-5 text-muted">{{ $co['date'] }}</td>
                                <td class="py-3.5 px-5 font-medium text-ink">{{ $co['location'] }}</td>
                                <td class="py-3.5 px-5 text-muted">{{ $co['items'] ?: '—' }}</td>
                                <td class="py-3.5 px-5 font-semibold text-ink">{{ $co['notes'] ?: '—' }}</td>
                                <td class="py-3.5 px-5 text-right font-bold text-danger">₹{{ number_format($co['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </x-card>
    </div>

    <!-- 3. ITEM PERFORMANCE -->
    <div x-show="activeTab === 'items'" class="space-y-6" x-transition style="display: none;">
        <x-card class="p-0 overflow-hidden" variant="default">
            <div class="p-6 border-b border-border">
                <h3 class="text-sm font-bold text-ink mb-1">Item Sales Roster</h3>
                <p class="text-xs text-muted">Quantities sold and revenue generated per item in the selected period.</p>
            </div>

            @if($topItems->isEmpty())
                <p class="p-6 text-xs text-muted font-semibold">No item sales in this period yet.</p>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                            <th class="py-3.5 px-5">Item Name</th>
                            <th class="py-3.5 px-5">Quantity Sold</th>
                            <th class="py-3.5 px-5 text-right">Revenue Generated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-xs text-ink">
                        @foreach($topItems as $item)
                            <tr class="hover:bg-card-tint transition-all">
                                <td class="py-3.5 px-5 font-bold text-ink">{{ $item['name'] }}</td>
                                <td class="py-3.5 px-5 font-bold text-ink">{{ $item['quantity'] }}</td>
                                <td class="py-3.5 px-5 text-right font-bold text-teal">₹{{ number_format($item['revenue'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
