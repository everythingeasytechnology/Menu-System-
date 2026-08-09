@extends('layouts.app')

@section('title', 'Service Points Map')

@section('content')
<div 
    x-data="servicePointManager({
        initialPoints: @js($points),
        initialCategories: @js($categories),
        businessName: @js($business->name),
        csrf: '{{ csrf_token() }}'
    })"
    class="space-y-6"
>
    <!-- Floating Success Toast -->
    @if(session('success'))
    <div 
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 4000)"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-navy-deep border border-orange/20 text-white px-5 py-4 rounded-xl shadow-xl max-w-sm"
    >
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange/20 text-orange">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-xs font-bold text-white">Service Points Notification</p>
            <p class="text-[11px] text-slate-300 mt-0.5">{{ session('success') }}</p>
        </div>
        <button @click="show = false" class="text-muted hover:text-white transition-colors cursor-pointer">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    <!-- Page Header & Zone switcher -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-ink">Service Points Map</h1>
            <p class="text-xs text-muted mt-0.5">Live status, room layouts, and direct order links.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Zone Switcher (Dynamic Categories) -->
            <template x-if="categories.length > 0">
                <div class="flex items-center gap-1 bg-card-tint border border-border p-1 rounded-xl">
                    <template x-for="cat in categories" :key="cat">
                        <button 
                            @click="activeFloor = cat"
                            :class="activeFloor === cat ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'"
                            class="rounded-lg px-3.5 py-1.5 text-[10px] cursor-pointer transition-all"
                            x-text="cat"
                        ></button>
                    </template>
                </div>
            </template>

            <!-- Status Filter -->
            <select x-model="statusFilter" class="rounded-xl border border-border bg-card px-3 py-1.5 text-[10px] font-bold text-ink outline-none cursor-pointer">
                <option value="all">All Statuses</option>
                <option value="available">Only Vacant</option>
                <option value="occupied">Only Occupied</option>
            </select>

            <!-- Trigger Button -->
            <button 
                @click="openCreateModal()"
                class="rounded-xl bg-orange hover:bg-orange/95 px-4.5 py-2 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer active:scale-95 transition-all"
            >
                + Add Point / Room
            </button>
        </div>
    </div>

    <!-- Stats roster -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[9px] font-bold text-muted uppercase">Total Points</span>
                <h4 class="text-base font-black text-ink mt-0.5" x-text="stats.total">0</h4>
            </div>
            <span class="text-xs">🗺️</span>
        </div>
        <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[9px] font-bold text-muted uppercase">Available</span>
                <h4 class="text-base font-black text-teal mt-0.5" x-text="stats.available">0</h4>
            </div>
            <span class="text-xs">🟢</span>
        </div>
        <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[9px] font-bold text-muted uppercase">Occupied</span>
                <h4 class="text-base font-black text-orange mt-0.5" x-text="stats.occupied">0</h4>
            </div>
            <span class="text-xs">🟠</span>
        </div>
        <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-xs col-span-2 lg:col-span-1">
            <div>
                <span class="text-[9px] font-bold text-muted uppercase">Total Capacity</span>
                <h4 class="text-base font-black text-ink mt-0.5" x-text="`${stats.capacity} Pax`">0 Pax</h4>
            </div>
            <span class="text-xs">🪑</span>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-3.5 text-[9px] font-bold text-ink uppercase tracking-wider">
        <div class="flex items-center gap-1.5 rounded-lg bg-card border border-border px-2.5 py-1.5 shadow-xs">
            <span class="w-2.5 h-2.5 rounded-full bg-border border border-slate-300"></span>
            <span>Available</span>
        </div>
        <div class="flex items-center gap-1.5 rounded-lg bg-card border border-border px-2.5 py-1.5 shadow-xs">
            <span class="w-2.5 h-2.5 rounded-full bg-orange animate-pulse"></span>
            <span>Occupied</span>
        </div>
        <div class="flex items-center gap-1.5 rounded-lg bg-card border border-border px-2.5 py-1.5 shadow-xs">
            <span class="w-2.5 h-2.5 rounded-full bg-warning"></span>
            <span>Bill Pending</span>
        </div>
    </div>

    <!-- Empty Categories Placeholder -->
    <template x-if="categories.length === 0">
        <div class="bg-card border border-border rounded-card p-12 text-center text-xs text-muted flex flex-col items-center justify-center gap-3">
            <span class="text-3xl">🗺️</span>
            <span class="font-bold text-sm text-ink">No Service Points Found</span>
            <span>Please create your first service point / room to populate custom zones.</span>
            <button @click="openCreateModal()" class="rounded-xl bg-orange text-white px-4 py-2 font-bold mt-2">Create Point</button>
        </div>
    </template>

    <!-- Grid of Tables/Rooms -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <template x-for="t in tables" :key="t.id">
            <div 
                x-show="
                    t.category === activeFloor && 
                    (statusFilter === 'all' || 
                     (statusFilter === 'available' && t.status === 'available') || 
                     (statusFilter === 'occupied' && (t.status === 'occupied' || t.status === 'bill-pending')))
                "
                class="rounded-xl border bg-card p-4.5 cursor-pointer hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 transition-all flex flex-col justify-between h-48 select-none relative overflow-hidden group"
                :class="{
                    'border-border hover:border-slate-400': t.status === 'available',
                    'border-orange/20 bg-orange/[0.01] hover:border-orange/40': t.status === 'occupied',
                    'border-warning/30 bg-warning/[0.01] hover:border-warning/50': t.status === 'bill-pending'
                }"
                @click="selectTable(t.id)"
            >
                <!-- Card Header -->
                <div class="flex justify-between items-start">
                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-black text-ink block truncate" x-text="t.name"></span>
                        <span class="block text-[8px] text-slate-400 font-bold uppercase mt-0.5" x-text="t.code"></span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <span class="text-[8px] font-bold text-muted bg-card-tint border border-border px-1.5 py-0.5 rounded-md" x-text="`${t.seats} Pax`"></span>
                        <!-- Delete Button -->
                        <button 
                            @click.stop="deletePoint(t)"
                            class="p-1 rounded text-slate-300 hover:text-danger hover:bg-danger/5 transition-all opacity-0 group-hover:opacity-100 cursor-pointer"
                            title="Delete Service Point"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Generic Visual Center Indicator (Hides table shape / bed layout) -->
                <div class="my-3 flex items-center justify-center h-16">
                    <div 
                        :class="t.status === 'available' ? 'bg-card-tint border-border text-slate-400' : 'bg-orange/10 border-orange text-orange'"
                        class="h-12 w-12 rounded-xl border-2 flex items-center justify-center text-xl font-bold shadow-xs relative"
                    >
                        <!-- Conditional icon representation -->
                        <span x-text="t.category.toLowerCase().includes('room') ? '🏨' : '🪑'"></span>
                        
                        <!-- Mini occupied count badge -->
                        <template x-if="t.status !== 'available'">
                            <span class="absolute -bottom-1 -right-1 h-4 w-4 bg-orange text-[8px] text-white font-extrabold rounded-full flex items-center justify-center border border-white">✓</span>
                        </template>
                    </div>
                </div>

                <!-- Card Footer status indicator -->
                <div class="pt-2 border-t border-border/40 flex justify-between items-center shrink-0">
                    <span 
                        class="text-[8px] font-extrabold uppercase tracking-wider"
                        :class="{
                            'text-slate-400': t.status === 'available',
                            'text-orange': t.status === 'occupied',
                            'text-warning animate-pulse': t.status === 'bill-pending'
                        }"
                        x-text="t.status.replace('-', ' ')"
                    ></span>
                    
                    <span class="text-[9px] font-bold text-ink" x-text="t.amount > 0 ? `₹ ${parseInt(t.amount)}` : ''"></span>
                </div>
            </div>
        </template>
    </div>

    <!-- Sliding Sidebar Checkout Drawer -->
    <div 
        x-show="drawerOpen" 
        class="fixed inset-y-0 right-0 w-full max-w-md bg-card border-l border-border shadow-2xl z-50 flex flex-col justify-between"
        style="display: none;"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
    >
        <template x-if="selectedTable">
            <div class="flex flex-col h-full justify-between">
                <!-- Drawer Header -->
                <div class="p-6 border-b border-border bg-card-tint flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-ink" x-text="`${selectedTable.name} (${selectedTable.code})`"></h3>
                        <span class="text-xs text-muted" x-text="`Category: ${selectedTable.category}`"></span>
                    </div>
                    <button 
                        @click="drawerOpen = false" 
                        class="rounded-xl border border-border bg-card p-1.5 text-muted hover:text-ink cursor-pointer font-bold"
                    >
                        ✕
                    </button>
                </div>

                <!-- Drawer Content -->
                <div class="p-6 flex-1 overflow-y-auto space-y-6">
                    <!-- Scan-to-Order Actions -->
                    <div class="bg-card border border-border/80 rounded-xl p-3 space-y-3 shadow-xs">
                        <div class="flex justify-between items-center pb-2 border-b border-border/60">
                            <span class="text-[9px] font-extrabold text-muted uppercase tracking-wider">Scan to Order</span>
                            <span class="text-[8px] font-bold text-teal bg-teal/5 px-2 py-0.5 rounded-lg border border-teal/10">Active Link</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                @click="copyScanLink()"
                                class="rounded-lg border border-orange/10 bg-orange/5 text-orange py-1.5 text-[9px] font-bold hover:bg-orange/10 cursor-pointer active:scale-95 transition-all"
                            >
                                Copy Link
                            </button>
                            <button 
                                @click="openScannerModal()"
                                class="rounded-lg border border-border bg-card py-1.5 text-[9px] font-bold text-ink hover:bg-card-tint cursor-pointer active:scale-95 transition-all"
                            >
                                View
                            </button>
                            <a
                                :href="`${selectedTable.scanner_url}?download=1`"
                                class="rounded-lg border border-border bg-card py-1.5 text-center text-[9px] font-bold text-ink hover:bg-card-tint active:scale-95 transition-all"
                            >
                                Download
                            </a>
                        </div>
                        <span x-show="copyMessage" x-text="copyMessage" class="block text-[8px] font-bold text-teal"></span>
                    </div>

                    <!-- Active Bill Log -->
                    <div class="space-y-3.5">
                        <div class="flex justify-between items-center">
                            <span class="text-[9px] font-extrabold text-muted uppercase tracking-wider">Items on Bill</span>
                            <span class="text-[9px] font-extrabold text-orange" x-text="selectedTable.order_number"></span>
                        </div>

                        <div class="rounded-xl border border-border p-3.5 bg-card-tint space-y-2.5">
                            <div class="space-y-2 max-h-56 overflow-y-auto">
                                <template x-for="order in selectedTable.active_orders" :key="order.id">
                                    <div class="rounded-lg border border-border bg-card p-2.5">
                                        <div class="flex items-center justify-between gap-2 border-b border-border/50 pb-1.5">
                                            <div class="min-w-0">
                                                <span class="block truncate text-[10px] font-black text-ink" x-text="order.display_id"></span>
                                                <span class="block truncate text-[8px] font-bold text-muted" x-text="order.customer"></span>
                                            </div>
                                            <div class="text-right">
                                                <span class="block text-[10px] font-black text-ink" x-text="order.amount_label"></span>
                                                <span class="block text-[8px] font-bold uppercase text-muted" x-text="`${order.status_label} / ${order.payment_status}`"></span>
                                            </div>
                                        </div>
                                        <div class="mt-2 space-y-1">
                                            <template x-for="item in order.items" :key="item.id">
                                                <div class="flex items-center justify-between gap-2 text-[10px] font-semibold text-muted">
                                                    <span class="min-w-0 truncate" x-text="item.label"></span>
                                                    <span class="shrink-0 rounded bg-card-tint px-1.5 py-0.5 text-[7px] font-bold uppercase tracking-wider text-slate-500" x-text="item.status_label"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="(!selectedTable.active_orders || selectedTable.active_orders.length === 0) && (!selectedTable.items || selectedTable.items.length === 0)">
                                    <span class="text-xs text-slate-400 block text-center py-2">No active orders added.</span>
                                </template>

                                <template x-if="(!selectedTable.active_orders || selectedTable.active_orders.length === 0) && selectedTable.items && selectedTable.items.length > 0">
                                    <div class="space-y-1.5">
                                        <template x-for="item in selectedTable.items" :key="item.label">
                                            <div class="flex justify-between items-center text-xs text-muted font-semibold">
                                                <span x-text="item.label"></span>
                                                <span class="text-[9px] text-teal" x-text="item.status_label || 'Added'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <div class="h-px bg-border my-2"></div>
                            <div class="flex justify-between text-xs font-bold text-ink">
                                <span>Running Balance</span>
                                <span x-text="`Rs. ${Number(selectedTable.amount || 0).toFixed(2)}`"></span>
                            </div>
                        </div>

                        <!-- Table Status Advancer -->
                        <div class="pt-3 border-t border-border" x-show="selectedTable.status !== 'available'">
                            <button 
                                @click="advanceStatus('bill-pending')"
                                class="w-full rounded-lg border border-warning/30 bg-warning/5 text-warning py-2 text-[10px] font-bold hover:bg-warning/10 cursor-pointer active:scale-95 transition-all"
                            >
                                Request Bill
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer Actions -->
                <div class="p-6 border-t border-border bg-card-tint space-y-2.5 shrink-0">
                    <!-- Quick Activation Trigger Button (If vacant) -->
                    <button 
                        x-show="selectedTable.status === 'available'"
                        @click="advanceStatus('occupied')"
                        class="w-full rounded-xl bg-orange hover:bg-orange/95 py-3 text-xs font-bold text-white shadow-md shadow-orange/15 cursor-pointer"
                    >
                        ⚡ Occupy & Activate
                    </button>

                    <!-- Checkout Trigger Button (If active) -->
                    <button 
                        x-show="selectedTable.status !== 'available'"
                        @click="settleSelectedTable()"
                        :disabled="settling"
                        class="w-full rounded-xl bg-teal hover:bg-teal/95 py-3 text-xs font-bold text-white shadow-md shadow-teal/15 cursor-pointer disabled:cursor-not-allowed disabled:opacity-60"
                        x-text="settling ? 'Settling...' : `Checkout & Settle (Rs. ${Number(selectedTable.amount || 0).toFixed(2)})`"
                    >
                    </button>
                    <button 
                        @click="drawerOpen = false"
                        class="w-full rounded-xl border border-border bg-card py-3 text-xs font-bold text-ink hover:bg-card-tint cursor-pointer"
                    >
                        Close Panel
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Scanner Preview Modal -->
    <div
        x-show="scannerModalOpen"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-navy-deep/70 p-4 backdrop-blur-xs"
        style="display: none;"
        x-transition.opacity
        @click.self="scannerModalOpen = false"
    >
        <template x-if="selectedTable">
            <div class="w-full max-w-sm rounded-card border border-border bg-card shadow-2xl">
                <div class="flex items-center justify-between border-b border-border bg-card-tint px-5 py-4">
                    <div>
                        <span class="block text-[9px] font-black uppercase tracking-wider text-orange">Scan to Order</span>
                        <h3 class="mt-1 text-sm font-black text-ink" x-text="businessName"></h3>
                        <p class="mt-0.5 text-[10px] font-semibold text-muted" x-text="`${selectedTable.name} (${selectedTable.code})`"></p>
                    </div>
                    <button
                        type="button"
                        @click="scannerModalOpen = false"
                        class="rounded-xl border border-border bg-card px-2.5 py-1.5 text-xs font-black text-muted hover:text-ink"
                    >
                        X
                    </button>
                </div>

                <div class="space-y-4 p-5">
                    <div class="mx-auto flex h-56 w-56 items-center justify-center rounded-2xl border border-border bg-white p-4 shadow-xs">
                        <img :src="selectedTable.scanner_url" :alt="`${selectedTable.name} QR scanner`" class="h-full w-full object-contain">
                    </div>

                    <div class="space-y-1">
                        <span class="block text-[8px] font-bold uppercase tracking-wider text-muted">Scan Link</span>
                        <input
                            type="text"
                            readonly
                            :value="selectedTable.scan_url"
                            class="w-full rounded-lg border border-border bg-card-tint px-2 py-1.5 text-[9px] font-mono text-ink outline-none select-all"
                        >
                        <span x-show="copyMessage" x-text="copyMessage" class="block text-[8px] font-bold text-teal"></span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button
                            type="button"
                            @click="copyScanLink()"
                            class="rounded-lg border border-orange/10 bg-orange/5 py-2 text-[9px] font-bold text-orange hover:bg-orange/10"
                        >
                            Copy Link
                        </button>
                        <button
                            type="button"
                            @click="printScanner()"
                            class="rounded-lg border border-border bg-card py-2 text-[9px] font-bold text-ink hover:bg-card-tint"
                        >
                            Print
                        </button>
                        <a
                            :href="`${selectedTable.scanner_url}?download=1`"
                            class="rounded-lg border border-border bg-card py-2 text-center text-[9px] font-bold text-ink hover:bg-card-tint"
                        >
                            Download
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Create Service Point Modal Wizard Overlay -->
    <div 
        x-show="createModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" 
        style="display: none;"
    >
        <!-- Backdrop background click triggers close -->
        <div class="fixed inset-0 bg-navy-deep/60 backdrop-blur-xs transition-opacity animate-fade-in" @click="createModalOpen = false"></div>

        <!-- Form Box -->
        <div class="bg-card border border-border rounded-card p-6 shadow-2xl max-w-sm w-full relative z-10 space-y-4">
            <div class="flex justify-between items-center pb-2.5 border-b border-border">
                <h3 class="text-xs font-black text-ink uppercase tracking-wider">➕ Add Point / Room</h3>
                <button @click="createModalOpen = false" class="text-muted hover:text-ink cursor-pointer font-bold">✕</button>
            </div>

            <!-- Form Fields -->
            <form action="/service-points" method="POST" class="space-y-4">
                @csrf
                <!-- Display Name -->
                <div class="space-y-1.5">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Display Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        required
                        placeholder="e.g. Table Room 5, Hall Chair 2" 
                        class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-xs text-ink outline-none focus:border-orange focus:ring-1 focus:ring-orange/20"
                    >
                </div>

                <!-- Seating Size -->
                <div class="space-y-1.5">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Capacity / Seating Size</label>
                    <select 
                        name="seats" 
                        required
                        class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-xs text-ink outline-none cursor-pointer focus:border-orange"
                    >
                        <option value="2">2 Pax</option>
                        <option value="4">4 Pax</option>
                        <option value="6">6 Pax</option>
                        <option value="8">8 Pax</option>
                        <option value="12">12 Pax</option>
                    </select>
                </div>

                <!-- Custom Category Input / Selection -->
                <div class="space-y-2">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Category / Zone</label>
                    <input 
                        type="text" 
                        name="category" 
                        x-model="categoryInput"
                        required
                        list="zone-suggestions"
                        placeholder="e.g. Dining Hall, 1st Floor Rooms" 
                        class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-xs text-ink outline-none focus:border-orange focus:ring-1 focus:ring-orange/20"
                    >
                    <datalist id="zone-suggestions">
                        <template x-for="cat in categories" :key="cat">
                            <option :value="cat"></option>
                        </template>
                    </datalist>

                    <!-- Pre-created Categories Badges underneath the input -->
                    <template x-if="categories.length > 0">
                        <div class="space-y-1.5 pt-1">
                            <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Or select existing:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="cat in categories" :key="cat">
                                    <button 
                                        type="button"
                                        @click="categoryInput = cat"
                                        class="rounded-lg bg-card-tint border border-border px-2 py-1 text-[9px] font-semibold text-ink hover:border-orange hover:text-orange transition-colors cursor-pointer"
                                        x-text="cat"
                                    ></button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Modal footer actions -->
                <div class="flex gap-2 pt-2.5 border-t border-border mt-4">
                    <button 
                        type="submit" 
                        class="flex-1 rounded-xl bg-orange hover:bg-orange/95 py-2.5 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer active:scale-95 transition-all"
                    >
                        Create Point
                    </button>
                    <button 
                        type="button"
                        @click="createModalOpen = false" 
                        class="rounded-xl border border-border bg-card px-4 py-2.5 text-xs font-bold text-ink hover:bg-card-tint cursor-pointer active:scale-95 transition-all"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function servicePointManager(config) {
    return {
        tables: config.initialPoints,
        categories: config.initialCategories,
        businessName: config.businessName || 'Hotel',
        activeFloor: config.initialCategories.length > 0 ? config.initialCategories[0] : '',
        drawerOpen: false,
        selectedTableId: null,
        createModalOpen: false,
        scannerModalOpen: false,
        statusFilter: 'all',
        copyMessage: '',
        settling: false,

        // Form Fields
        categoryInput: '',

        get selectedTable() {
            return this.tables.find(t => t.id === this.selectedTableId);
        },

        get stats() {
            let activeList = this.tables.filter(t => t.category === this.activeFloor);
            return {
                total: activeList.length,
                available: activeList.filter(t => t.status === 'available').length,
                occupied: activeList.filter(t => t.status === 'occupied' || t.status === 'bill-pending').length,
                capacity: activeList.reduce((acc, t) => acc + t.seats, 0)
            };
        },

        selectTable(id) {
            this.selectedTableId = id;
            this.drawerOpen = true;
        },

        openCreateModal() {
            this.categoryInput = '';
            this.createModalOpen = true;
        },

        openScannerModal() {
            this.copyMessage = '';
            this.scannerModalOpen = true;
        },

        replacePoint(updatedPoint) {
            let index = this.tables.findIndex(t => t.id === updatedPoint.id);
            if (index >= 0) {
                Object.assign(this.tables[index], updatedPoint);
            }
        },

        async copyScanLink() {
            let t = this.selectedTable;
            if (!t) return;

            await navigator.clipboard.writeText(t.scan_url);
            this.copyMessage = 'Link copied';
            setTimeout(() => this.copyMessage = '', 1800);
        },

        escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character]));
        },

        printScanner() {
            let t = this.selectedTable;
            if (!t) return;

            let printWindow = window.open('', '_blank', 'width=420,height=560');
            if (!printWindow) return;
            let doc = printWindow.document;

            doc.title = String(t.name || 'Service Point') + ' QR Tag';
            doc.body.innerHTML = '';

            let style = doc.createElement('style');
            style.textContent = 'body{font-family:Arial,sans-serif;margin:0;padding:24px;color:#111827}.tag{width:280px;border:1px solid #d1d5db;border-radius:12px;padding:18px;text-align:center}img{width:190px;height:190px;object-fit:contain}.eyebrow{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#f97316;font-weight:800;margin-bottom:6px}h1{font-size:18px;margin:0 0 4px}p{font-size:12px;margin:4px 0;color:#64748b;word-break:break-all}';
            doc.head.appendChild(style);

            let tag = doc.createElement('div');
            tag.className = 'tag';
            doc.body.appendChild(tag);

            let eyebrow = doc.createElement('div');
            eyebrow.className = 'eyebrow';
            eyebrow.textContent = 'Scan to Order';
            tag.appendChild(eyebrow);

            let title = doc.createElement('h1');
            title.textContent = String(this.businessName || '');
            tag.appendChild(title);

            let point = doc.createElement('p');
            point.textContent = String(t.name || '') + ' (' + String(t.code || '') + ')';
            tag.appendChild(point);

            let image = doc.createElement('img');
            image.src = t.scanner_url;
            image.alt = 'QR scanner';
            tag.appendChild(image);

            let link = doc.createElement('p');
            link.textContent = String(t.scan_url || '');
            tag.appendChild(link);

            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                setTimeout(() => printWindow.close(), 500);
            }, 250);
        },

        // Trigger PUT updates to backend
        async syncPoint(point) {
            try {
                let response = await fetch(`/service-points/${point.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf
                    },
                    body: JSON.stringify({
                        status: point.status,
                        amount: point.amount,
                        items: point.items
                    })
                });
                let data = await response.json();
                if (data.success) {
                    this.replacePoint(data.point);
                } else if (data.message) {
                    alert(data.message);
                }

                return Boolean(data.success);
            } catch (e) {
                console.error('Failed to sync service point data', e);
                return false;
            }
        },

        // Advance Point State
        async advanceStatus(status) {
            let t = this.selectedTable;
            if (t) {
                t.status = status;
                let synced = await this.syncPoint(t);
                if (synced && status === 'available') {
                    this.drawerOpen = false;
                }
            }
        },

        async settleSelectedTable() {
            let t = this.selectedTable;
            if (!t || this.settling) return;

            if (!t.active_order_count) {
                await this.advanceStatus('available');
                return;
            }

            if (!confirm(`Settle ${t.active_order_count} active order(s) and free ${t.name}?`)) {
                return;
            }

            this.settling = true;
            try {
                let response = await fetch(`/service-points/${t.id}/settle`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrf
                    }
                });
                let data = await response.json();

                if (!response.ok || !data.success) {
                    alert(data.message || 'Could not settle service point.');
                    return;
                }

                this.replacePoint(data.point);
                this.drawerOpen = false;
            } catch (e) {
                console.error('Failed to settle service point', e);
                alert('Could not settle service point.');
            } finally {
                this.settling = false;
            }
        },

        // Delete Point Action
        async deletePoint(point) {
            if (!confirm(`Are you sure you want to delete service point "${point.name}" (${point.code})?`)) {
                return;
            }

            let backupTables = [...this.tables];
            this.tables = this.tables.filter(t => t.id !== point.id);

            try {
                let response = await fetch(`/service-points/${point.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': config.csrf
                    }
                });
                let data = await response.json();
                if (!data.success) {
                    this.tables = backupTables;
                    alert('Could not delete service point.');
                } else {
                    // Re-calculate categories list if it was the last point in that category
                    let stillHasCategory = this.tables.some(t => t.category === point.category);
                    if (!stillHasCategory) {
                        this.categories = this.categories.filter(c => c !== point.category);
                        if (this.activeFloor === point.category) {
                            this.activeFloor = this.categories.length > 0 ? this.categories[0] : '';
                        }
                    }
                }
            } catch (e) {
                console.error('Failed to delete service point', e);
                this.tables = backupTables;
                alert('Could not delete service point.');
            }
        }
    }
}
</script>
@endsection
