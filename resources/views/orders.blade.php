@extends('layouts.app')

@section('title', 'Live Orders')

@section('content')
<div
    x-data="ordersPage({
        initialOrders: @js($ordersPayload),
        selectedOrderId: @js($selectedOrderId),
        statuses: @js($statuses),
        itemStatuses: @js($itemStatuses),
        menuItems: @js($menuItemsPayload),
        feedUrl: '{{ route('dashboard.orders.feed') }}',
        csrf: '{{ csrf_token() }}',
        gstSettings: @js([
            'enabled' => (bool) $settings->gst_enabled,
            'cgstRate' => (float) ($settings->cgst ?? 2.5),
            'sgstRate' => (float) ($settings->sgst ?? 2.5),
            'brandName' => $settings->brand_name ?? $business->name,
            'gstNo' => $settings->gst_no ?? '',
            'address' => $settings->address ?? '',
            'pincode' => $settings->pincode ?? '',
        ]),
        statusImages: @js([
            'preparing' => asset('images/order-status/preparing.png'),
            'ready' => asset('images/order-status/ready.png'),
            'served' => asset('images/order-status/served.png'),
            'completed' => asset('images/order-status/served.png'),
            'cancelled' => asset('images/order-status/cancelled.png'),
        ])
    })"
    x-init="start()"
    class="space-y-4"
>
    <div class="grid grid-cols-1 items-start gap-4 xl:grid-cols-[minmax(0,1fr)_430px]">
        <section class="space-y-3">
            <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-start 2xl:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-3 w-3 rounded-full bg-orange shadow-[0_0_0_4px_rgba(255,122,0,0.14)]"></span>
                        <h1 class="text-xl font-black tracking-tight text-ink">Live Orders</h1>
                    </div>
                    <p class="mt-1 flex items-center gap-2 text-xs font-bold text-muted">
                        <span class="h-2 w-2 rotate-45 rounded-[2px] bg-orange"></span>
                        <span><span x-text="orders.length"></span> active orders</span>
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <label class="relative block">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                        </span>
                        <input
                            type="search"
                            x-model="searchQuery"
                            placeholder="Search orders..."
                            class="h-9 w-full rounded-lg border border-border bg-card px-9 text-xs font-semibold text-ink shadow-sm outline-none placeholder:text-muted focus:border-orange focus:ring-4 focus:ring-orange/10 sm:w-56"
                        >
                    </label>

                    <div class="flex h-9 overflow-hidden rounded-lg border border-border bg-card shadow-sm">
                        <button type="button" @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-card-tint text-ink' : 'text-muted hover:text-ink'" class="flex w-10 items-center justify-center border-r border-border transition" aria-label="Grid view">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 5.75h5v5h-5v-5Zm9.5 0h5v5h-5v-5Zm-9.5 9.5h5v5h-5v-5Zm9.5 0h5v5h-5v-5Z" />
                            </svg>
                        </button>
                        <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-card-tint text-ink' : 'text-muted hover:text-ink'" class="flex w-10 items-center justify-center transition" aria-label="List view">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6.75h11M8 12h11M8 17.25h11M4.75 6.75h.01M4.75 12h.01M4.75 17.25h.01" />
                            </svg>
                        </button>
                    </div>

                    <label class="relative block">
                        <select x-model="sortMode" class="h-9 appearance-none rounded-lg border border-border bg-card py-0 pl-4 pr-9 text-xs font-black text-ink shadow-sm outline-none focus:border-orange focus:ring-4 focus:ring-orange/10">
                            <option value="latest">Sort: Latest</option>
                            <option value="oldest">Sort: Oldest</option>
                            <option value="amount-high">Sort: Amount High</option>
                            <option value="amount-low">Sort: Amount Low</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-muted">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex max-w-full items-center gap-1 overflow-x-auto rounded-lg border border-border bg-card-tint p-0.5 sm:w-max">
                <button @click="activeTab = 'all'" :class="tabClass('all')" class="shrink-0 rounded-md px-3 py-1.5 text-xs font-black transition">
                    All <span class="ml-1 rounded-full bg-orange/10 px-1.5 py-0.5 text-[10px] text-orange" x-text="orders.length"></span>
                </button>
                <button @click="activeTab = 'dine-in'" :class="tabClass('dine-in')" class="shrink-0 rounded-md px-3 py-1.5 text-xs font-black transition">
                    Dining <span class="ml-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px]" x-text="channelCount('dine-in')"></span>
                </button>
                <button @click="activeTab = 'takeaway'" :class="tabClass('takeaway')" class="shrink-0 rounded-md px-3 py-1.5 text-xs font-black transition">
                    Packed <span class="ml-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px]" x-text="channelCount('takeaway')"></span>
                </button>
                <button @click="activeTab = 'room-service'" :class="tabClass('room-service')" class="shrink-0 rounded-md px-3 py-1.5 text-xs font-black transition">
                    Room <span class="ml-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px]" x-text="channelCount('room-service')"></span>
                </button>
            </div>

            <div
                class="gap-2"
                :class="viewMode === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3' : 'grid grid-cols-1'"
            >
                <template x-for="order in sortedOrders" :key="order.id">
                    <button
                        type="button"
                        @click="selectOrder(order.id)"
                        class="group relative overflow-hidden rounded-card border bg-card p-2.5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-navy/10"
                        :class="[orderAccentClass(order), selectedOrderId === order.id ? 'ring-2 ring-orange/30' : '']"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-base font-black text-orange" :class="orderNumberClass(order)" x-text="order.displayId"></span>
                            <span class="text-xs font-black text-muted" x-text="order.time"></span>
                        </div>

                        <div class="mt-2 grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 text-xs font-black text-ink">
                                    <span class="text-orange" x-html="locationIcon(order)"></span>
                                    <span class="truncate" x-text="order.location"></span>
                                </div>
                            </div>
                            <div class="min-w-0 text-right">
                                <div class="truncate text-xs font-black text-ink" x-text="order.customer"></div>
                                <div class="truncate text-[10px] font-bold text-muted" x-text="order.phone"></div>
                            </div>
                        </div>

                        <div class="mt-2 flex items-end justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-black text-ink">
                                    <span x-text="`${order.itemCount} ${order.itemCount === 1 ? 'Item' : 'Items'}`"></span>
                                    <span class="mx-1 text-muted">&middot;</span>
                                    <span x-text="order.amount"></span>
                                </p>
                                <span class="mt-1 inline-flex max-w-full rounded-md px-2 py-0.5 text-[8px] font-black uppercase tracking-wider" :class="paymentClass(order.paymentStatus)" x-text="order.paymentLabel"></span>
                            </div>
                            <div class="flex shrink-0 items-center gap-1.5">
                                <span class="flex h-10 w-10 overflow-hidden rounded-lg border bg-card-tint" :class="itemStatusImageClass(order)" :title="itemStatusLabel(order)">
                                    <img :src="itemStatusImageUrl(order)" :alt="itemStatusLabel(order)" class="h-full w-full object-cover">
                                </span>
                                <span class="rounded-md px-2 py-0.5 text-[8px] font-black uppercase tracking-wider" :class="statusClass(itemStatusVisual(order))" x-text="itemStatusLabel(order)"></span>
                            </div>
                        </div>

                        <div class="mt-2">
                            <div class="relative grid grid-cols-4 gap-2">
                                <div class="absolute left-4 right-4 top-2 h-0.5 rounded-full bg-card-tint"></div>
                                <template x-for="step in statusSteps" :key="step.status">
                                    <div class="relative z-10 flex flex-col items-center">
                                        <span class="flex h-4 w-4 items-center justify-center rounded-full border text-[8px] font-black shadow-sm" :class="stepCircleClass(order, step.status)" x-html="step.icon"></span>
                                        <span class="sr-only" x-text="step.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </button>
                </template>

                <div x-show="sortedOrders.length === 0" class="col-span-full rounded-card border border-dashed border-border bg-card p-10 text-center">
                    <p class="text-base font-black text-ink">No orders found</p>
                    <p class="mt-1 text-sm font-semibold text-muted">Try clearing filters or wait for new orders.</p>
                </div>
            </div>

            <p class="text-xs font-semibold text-muted" x-text="sortedOrders.length ? `Showing 1 to ${sortedOrders.length} of ${filteredOrders.length} orders` : 'No orders to show'"></p>
        </section>

        <div
            class="xl:col-span-1"
            :class="selectedOrderId ? 'fixed inset-0 z-50 flex items-end justify-center bg-navy-deep/60 p-3 backdrop-blur-sm xl:sticky xl:top-4 xl:z-auto xl:block xl:bg-transparent xl:p-0 xl:backdrop-blur-none' : 'hidden xl:sticky xl:top-4 xl:block'"
            @click.self="selectedOrderId = null"
        >
            <template x-if="selectedOrder">
                <x-card class="w-full max-w-xl space-y-2 border border-border p-3 shadow-2xl xl:max-w-none xl:shadow-sm" variant="default">
                    <div class="flex items-start justify-between border-b border-border pb-2">
                        <div class="min-w-0">
                            <span class="text-[10px] font-black uppercase tracking-[0.18em] text-muted">Order Detail</span>
                            <h3 class="mt-1 truncate text-base font-black text-ink" x-text="selectedOrder.location"></h3>
                            <p class="mt-0.5 text-xs font-semibold text-muted">
                                <span x-text="selectedOrder.customer"></span>
                                <span x-show="selectedOrder.phone !== 'N/A'" x-text="` (${selectedOrder.phone})`"></span>
                            </p>
                            <p class="mt-0.5 flex items-center gap-2 text-[11px] font-bold text-muted">
                                <span x-text="selectedOrder.time"></span>
                                <span class="h-1 w-1 rounded-full bg-orange"></span>
                                <span x-text="selectedOrder.date || ''"></span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="block text-base font-black text-orange" x-text="selectedOrder.displayId"></span>
                            <button type="button" @click="selectedOrderId = null" class="mt-1 text-xs font-bold text-muted hover:text-ink xl:hidden">Close</button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Items</span>
                        <div class="max-h-48 divide-y divide-border/70 overflow-auto">
                            <template x-for="item in selectedOrder.items" :key="item.id">
                                <div class="grid grid-cols-[minmax(0,1fr)_108px] items-center gap-2 py-1.5">
                                    <div class="min-w-0">
                                        <span class="block truncate text-sm font-black text-ink" x-text="item.name"></span>
                                        <span class="block text-xs font-bold text-muted" x-text="`Qty: ${item.qty} x Rs. ${(item.total / item.qty).toFixed(2)}`"></span>
                                    </div>
                                    <label class="block">
                                        <span class="sr-only">Item Status</span>
                                        <select
                                            :value="item.status"
                                            @click.stop
                                            @change="updateOrderItemStatus(selectedOrder.id, item.id, $event.target.value)"
                                            :disabled="isUpdatingItemStatus || selectedOrder.status === 'completed' || selectedOrder.status === 'cancelled'"
                                            class="h-8 w-full rounded-lg border px-2 text-[11px] font-black uppercase tracking-wider outline-none disabled:cursor-not-allowed disabled:opacity-60"
                                            :class="statusClass(item.status)"
                                        >
                                            <template x-for="statusOption in itemStatuses" :key="statusOption.value">
                                                <option :value="statusOption.value" x-text="statusOption.label"></option>
                                            </template>
                                        </select>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="openAddItemModal()"
                        :disabled="menuItems.length === 0 || !selectedOrder || selectedOrder.status === 'completed' || selectedOrder.status === 'cancelled'"
                        class="w-full rounded-lg bg-orange px-4 py-2 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Add Item
                    </button>

                    <template x-if="menuItems.length === 0">
                        <p class="text-xs font-semibold text-muted">No active menu items available.</p>
                    </template>

                    <label class="block">
                        <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Order Status</span>
                        <select
                            @change="updateOrderStatus(selectedOrder.id, $event.target.value)"
                            class="h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-black text-ink outline-none focus:border-orange"
                        >
                            @foreach($statuses as $status => $label)
                                <option value="{{ $status }}" :selected="selectedOrder.status === '{{ $status }}'">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <template x-if="selectedOrder.note">
                        <div class="rounded-lg border border-orange/15 bg-orange/5 p-2">
                            <span class="block text-[10px] font-black uppercase tracking-wider text-orange">Instructions</span>
                            <p class="mt-1 text-xs font-semibold italic leading-normal text-slate-700" x-text="selectedOrder.note"></p>
                        </div>
                    </template>

                    <div class="border-t border-border pt-2">
                        <div class="flex items-center justify-between gap-3 text-sm font-black">
                            <span class="min-w-0 truncate text-muted" x-text="`Payment: ${selectedOrder.paymentLabel}`"></span>
                            <span class="shrink-0 text-ink" x-text="selectedOrder.amount"></span>
                        </div>

                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Status</span>
                                <select x-model="paymentForm.status" class="h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-black text-ink outline-none focus:border-orange">
                                    <option value="unpaid">Unpaid</option>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </label>

                            <label class="block">
                                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Method</span>
                                <select x-model="paymentForm.method" class="h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-black text-ink outline-none focus:border-orange">
                                    <option value="cash">Cash</option>
                                    <option value="online">Online</option>
                                    <option value="razorpay">Razorpay</option>
                                </select>
                            </label>
                        </div>

                        <div class="mt-2 grid grid-cols-[minmax(0,1fr)_100px] gap-2">
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-muted">Amount</span>
                                <input type="number" min="0" step="0.01" x-model.number="paymentForm.amount" class="h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-black text-ink outline-none focus:border-orange">
                            </label>

                            <button type="button" @click="markCashPaid()" :disabled="isUpdatingPayment" class="self-end rounded-lg bg-success/10 py-2 text-xs font-black text-success transition hover:bg-success/20 disabled:opacity-50">Cash Paid</button>
                        </div>

                        <template x-if="paymentError">
                            <p class="mt-2 rounded-lg border border-danger/10 bg-danger/5 px-3 py-1.5 text-xs font-bold text-danger" x-text="paymentError"></p>
                        </template>

                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <button type="button" @click="updatePayment()" :disabled="isUpdatingPayment" class="rounded-lg bg-teal/10 py-2 text-center text-xs font-black text-teal transition hover:bg-teal/20 disabled:opacity-50" x-text="isUpdatingPayment ? 'Saving...' : 'Update Payment'"></button>
                            <button type="button" @click="printReceipt(selectedOrder)" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-orange py-2 text-center text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 8.75v-4h10.5v4M6.75 17.75H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-1.75M7.5 14.75h9v5h-9v-5Z" />
                                </svg>
                                Print
                            </button>
                            <template x-if="selectedOrder.status !== 'completed' && selectedOrder.status !== 'cancelled'">
                                <button type="button" @click="cancelOrder(selectedOrder.id)" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-danger/10 py-2 text-center text-xs font-black text-danger transition hover:bg-danger/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.75h12M9.75 7.75v-2h4.5v2M10 11v6M14 11v6M7.5 7.75l.75 12h7.5l.75-12" />
                                    </svg>
                                    Cancel
                                </button>
                            </template>
                        </div>
                    </div>
                </x-card>
            </template>

            <template x-if="!selectedOrder">
                <x-card class="flex h-72 flex-col items-center justify-center border border-dashed border-border p-6 text-center" variant="default">
                    <h3 class="text-lg font-black text-ink">No Order Selected</h3>
                    <p class="mt-2 text-sm font-semibold leading-normal text-muted">Select an order from the list.</p>
                </x-card>
            </template>
        </div>
    </div>

    <div
        x-show="isAddItemModalOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-navy-deep/60 p-3 backdrop-blur-xs sm:items-center"
        @click.self="closeAddItemModal()"
        @keydown.escape.window="closeAddItemModal()"
    >
        <form
            @submit.prevent="addOrderItem()"
            class="w-full max-w-md rounded-card border border-border bg-card p-3 shadow-2xl"
        >
            <div class="flex items-start justify-between gap-3 border-b border-border pb-2">
                <div>
                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-muted">Order Detail</span>
                    <h3 class="mt-0.5 text-sm font-black text-ink">Add Item</h3>
                    <p class="mt-0.5 text-[10px] font-semibold text-muted" x-show="selectedOrder">
                        <span x-text="selectedOrder?.displayId"></span>
                        <span x-text="selectedOrder ? ` - ${selectedOrder.location}` : ''"></span>
                    </p>
                </div>
                <button type="button" @click="closeAddItemModal()" class="rounded-lg border border-border bg-card-tint px-2.5 py-1 text-[11px] font-bold text-muted hover:text-ink">Close</button>
            </div>

            <div class="mt-3 space-y-2">
                <label class="block">
                    <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-muted">Category</span>
                    <select
                        x-model="addItemForm.category"
                        @change="syncSelectedCategory()"
                        class="w-full rounded-lg border border-border bg-card-tint px-3 py-2 text-[12px] font-bold text-ink outline-none focus:border-orange"
                    >
                        <template x-for="category in menuCategories" :key="category.value">
                            <option :value="category.value" x-text="`${category.label} (${category.count})`"></option>
                        </template>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-muted">Search Items</span>
                    <input
                        type="text"
                        x-model="addItemForm.itemSearch"
                        @input.debounce.200ms="syncSelectedCategory()"
                        class="w-full rounded-lg border border-border bg-card-tint px-3 py-2 text-[12px] text-ink outline-none placeholder:text-muted focus:border-orange"
                    >
                </label>

                <label class="block">
                    <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-muted">Item</span>
                    <select
                        x-model="addItemForm.menuItemId"
                        @change="syncSelectedMenuItem()"
                        :disabled="filteredMenuItemsForAdd.length === 0"
                        class="w-full rounded-lg border border-border bg-card-tint px-3 py-2 text-[12px] font-bold text-ink outline-none focus:border-orange disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <template x-for="menuItem in filteredMenuItemsForAdd" :key="menuItem.id">
                            <option :value="String(menuItem.id)" x-text="`${menuItem.name} - ${menuItem.priceLabel}`"></option>
                        </template>
                    </select>
                    <span x-show="filteredMenuItemsForAdd.length === 0" class="mt-1 block text-[10px] font-bold text-danger">No items found in this category.</span>
                </label>

                <template x-if="selectedMenuItemVariants.length > 0">
                    <label class="block">
                        <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-muted">Variant</span>
                        <select
                            x-model="addItemForm.variantId"
                            class="w-full rounded-lg border border-border bg-card-tint px-3 py-2 text-[12px] font-bold text-ink outline-none focus:border-orange"
                        >
                            <template x-for="variant in selectedMenuItemVariants" :key="variant.id">
                                <option :value="String(variant.id)" x-text="`${variant.label} - ${variant.priceLabel}`"></option>
                            </template>
                        </select>
                    </label>
                </template>

                <div class="grid grid-cols-[90px_minmax(0,1fr)] gap-2">
                    <label class="block">
                        <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-muted">Qty</span>
                        <input
                            type="number"
                            min="1"
                            max="99"
                            x-model.number="addItemForm.quantity"
                            class="w-full rounded-lg border border-border bg-card-tint px-3 py-2 text-[12px] font-bold text-ink outline-none focus:border-orange"
                        >
                    </label>

                    <label class="block">
                        <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-muted">Instructions</span>
                        <input
                            type="text"
                            x-model="addItemForm.specialInstructions"
                            class="w-full rounded-lg border border-border bg-card-tint px-3 py-2 text-[12px] text-ink outline-none placeholder:text-muted focus:border-orange"
                        >
                    </label>
                </div>

                <div class="flex items-center justify-between rounded-lg border border-orange/10 bg-orange/5 px-3 py-2 text-[11px] font-bold">
                    <span class="text-muted" x-text="selectedMenuItem?.name || 'Item'"></span>
                    <span class="text-orange" x-text="selectedAddItemPriceLabel"></span>
                </div>

                <template x-if="addItemError">
                    <p class="rounded-lg border border-danger/10 bg-danger/5 px-3 py-2 text-[11px] font-bold text-danger" x-text="addItemError"></p>
                </template>
            </div>

            <div class="mt-3 flex gap-2 border-t border-border pt-3">
                <button type="button" @click="closeAddItemModal()" class="flex-1 rounded-lg border border-border bg-card-tint py-2 text-[11px] font-bold text-ink hover:bg-card">Cancel</button>
                <button
                    type="submit"
                    :disabled="isAddingItem || !selectedMenuItem || !selectedMenuItemIsVisible"
                    class="flex-1 rounded-lg bg-orange py-2 text-[11px] font-bold text-white shadow-md shadow-orange/20 transition-all hover:bg-orange/95 disabled:cursor-not-allowed disabled:opacity-50"
                    x-text="isAddingItem ? 'Adding...' : 'Add Item'"
                ></button>
            </div>
        </form>
    </div>
</div>

<script>
    function ordersPage(config) {
        return {
            orders: config.initialOrders || [],
            selectedOrderId: config.selectedOrderId,
            statuses: config.statuses || {},
            itemStatuses: config.itemStatuses || [],
            menuItems: config.menuItems || [],
            statusImages: config.statusImages || {},
            liveStatuses: ['preparing', 'ready', 'served'],
            activeTab: 'all',
            searchQuery: '',
            statusFilter: 'all',
            viewMode: 'grid',
            sortMode: 'latest',
            statusSteps: [
                {
                    status: 'preparing',
                    label: 'Preparing',
                    icon: '<svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8.5h10M8.5 5.5h7M6.5 11.5h11L16.75 19h-9.5L6.5 11.5Z" /></svg>'
                },
                {
                    status: 'ready',
                    label: 'Ready',
                    icon: '<svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="M8 8.25V6.5a4.5 4.5 0 0 1 9 0v1.75M6.25 8.25h12.5l.8 10.5H5.45l.8-10.5Z" /></svg>'
                },
                {
                    status: 'served',
                    label: 'Served',
                    icon: '<svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="m5.75 12.75 4 4 8.5-9.5" /></svg>'
                }
            ],
            refreshTimer: null,
            isUpdatingItemStatus: false,
            isUpdatingPayment: false,
            isAddingItem: false,
            isAddItemModalOpen: false,
            addItemError: '',
            paymentError: '',
            paymentForm: {
                status: 'unpaid',
                method: 'cash',
                amount: 0,
                transactionId: ''
            },
            addItemForm: {
                category: config.menuItems?.[0]?.category || '',
                menuItemId: config.menuItems?.[0]?.id ? String(config.menuItems[0].id) : '',
                variantId: '',
                quantity: 1,
                specialInstructions: '',
                itemSearch: ''
            },

            start() {
                this.syncSelectedCategory();
                this.syncPaymentForm();
                this.refreshTimer = setInterval(() => this.refreshOrders(), 20000);
            },

            get selectedOrder() {
                return this.orders.find((order) => order.id === this.selectedOrderId) || null;
            },

            selectOrder(orderId) {
                this.selectedOrderId = orderId;
                this.syncPaymentForm();
            },

            get menuCategories() {
                const counts = new Map();

                this.menuItems.forEach((menuItem) => {
                    const category = menuItem.category || '';
                    counts.set(category, (counts.get(category) || 0) + 1);
                });

                return Array.from(counts.entries()).map(([value, count]) => ({
                    value,
                    label: value || 'Uncategorized',
                    count
                }));
            },

            get filteredMenuItemsForAdd() {
                const category = this.addItemForm.category || '';
                const query = (this.addItemForm.itemSearch || '').toLowerCase().trim();

                return this.menuItems.filter((menuItem) => {
                    const categoryMatches = String(menuItem.category || '') === String(category);
                    const searchMatches = query === ''
                        || menuItem.name.toLowerCase().includes(query)
                        || (menuItem.category || '').toLowerCase().includes(query);

                    return categoryMatches && searchMatches;
                });
            },

            get selectedMenuItem() {
                return this.menuItems.find((menuItem) => String(menuItem.id) === String(this.addItemForm.menuItemId)) || null;
            },

            get selectedMenuItemIsVisible() {
                return this.filteredMenuItemsForAdd.some((menuItem) => String(menuItem.id) === String(this.addItemForm.menuItemId));
            },

            get selectedMenuItemVariants() {
                return this.selectedMenuItem?.variants || [];
            },

            get selectedAddItemPriceLabel() {
                const variant = this.selectedMenuItemVariants.find((itemVariant) => String(itemVariant.id) === String(this.addItemForm.variantId));

                return variant?.priceLabel || this.selectedMenuItem?.priceLabel || '';
            },

            get filteredOrders() {
                const query = this.searchQuery.toLowerCase();

                return this.orders.filter((order) => {
                    const channelMatches = this.activeTab === 'all' || order.channelType === this.activeTab;
                    const statusMatches = this.statusFilter === 'all' || order.status === this.statusFilter;
                    const searchMatches = query === ''
                        || order.displayId.toLowerCase().includes(query)
                        || order.orderNumber.toLowerCase().includes(query)
                        || order.location.toLowerCase().includes(query)
                        || order.customer.toLowerCase().includes(query)
                        || order.phone.toLowerCase().includes(query);

                    return channelMatches && statusMatches && searchMatches;
                });
            },

            get sortedOrders() {
                const list = [...this.filteredOrders];

                return list.sort((a, b) => {
                    if (this.sortMode === 'oldest') {
                        return Number(a.sortKey || a.id || 0) - Number(b.sortKey || b.id || 0);
                    }

                    if (this.sortMode === 'amount-high') {
                        return Number(b.rawTotal || 0) - Number(a.rawTotal || 0);
                    }

                    if (this.sortMode === 'amount-low') {
                        return Number(a.rawTotal || 0) - Number(b.rawTotal || 0);
                    }

                    return Number(b.sortKey || b.id || 0) - Number(a.sortKey || a.id || 0);
                });
            },

            channelCount(channel) {
                return this.orders.filter((order) => order.channelType === channel).length;
            },

            tabClass(tab) {
                return this.activeTab === tab
                    ? 'bg-white text-orange shadow-sm'
                    : 'text-muted hover:bg-white/70 hover:text-ink';
            },

            orderStageIndex(status) {
                if (status === 'preparing') return 0;
                if (status === 'ready') return 1;
                if (status === 'served' || status === 'completed') return 2;

                return 0;
            },

            stepCircleClass(order, status) {
                if (order.status === 'cancelled') {
                    return status === 'preparing'
                        ? 'border-danger bg-danger text-white'
                        : 'border-border bg-card-tint text-slate-300';
                }

                return this.orderStageIndex(order.status) >= this.orderStageIndex(status)
                    ? 'border-orange bg-orange text-white'
                    : 'border-border bg-card-tint text-slate-400';
            },

            stepTextClass(order, status) {
                if (order.status === 'cancelled' && status === 'preparing') {
                    return 'text-danger';
                }

                return this.orderStageIndex(order.status) >= this.orderStageIndex(status)
                    ? 'text-orange'
                    : 'text-muted';
            },

            orderAccentClass(order) {
                if (order.status === 'preparing') return 'border-orange/30 shadow-orange/10';
                if (order.status === 'ready') return 'border-blue-200 shadow-blue-100/50';
                if (order.status === 'served') return 'border-teal/30 shadow-teal/10';
                if (order.channelType === 'takeaway') return 'border-blue-100 shadow-blue-50';

                return 'border-orange/20 shadow-orange/5';
            },

            orderNumberClass(order) {
                if (order.status === 'ready' || order.channelType === 'takeaway') return 'text-blue-500';
                if (order.status === 'served') return 'text-teal';

                return 'text-orange';
            },

            locationIcon(order) {
                if (order.channelType === 'takeaway') {
                    return '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.5V7a4.5 4.5 0 0 1 9 0v1.5M5.75 8.5h12.5l.8 10.75H4.95L5.75 8.5Z" /></svg>';
                }

                return '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10.75h14M6.25 10.75v7.5m11.5-7.5v7.5M8 7h8l1.75 3.75H6.25L8 7Z" /></svg>';
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

            itemStatusImageClass(order) {
                return {
                    preparing: 'border-orange/20',
                    ready: 'border-teal/20',
                    served: 'border-success/20',
                    cancelled: 'border-danger/20'
                }[this.itemStatusVisual(order)] || 'border-border';
            },

            itemStatusImageUrl(order) {
                const status = this.itemStatusVisual(order);

                return this.statusImages[status] || this.statusImages.preparing || '';
            },

            statusClass(status) {
                return {
                    preparing: 'bg-orange/10 text-orange border border-orange/10',
                    ready: 'bg-teal/10 text-teal border border-teal/10',
                    served: 'bg-success/10 text-success border border-success/10',
                    completed: 'bg-success/10 text-success border border-success/10',
                    cancelled: 'bg-danger/10 text-danger border border-danger/10'
                }[status] || 'bg-card-tint text-muted border border-border';
            },

            paymentClass(status) {
                return {
                    paid: 'bg-success/10 text-success border border-success/10',
                    unpaid: 'bg-danger/10 text-danger border border-danger/10',
                    pending: 'bg-orange/10 text-orange border border-orange/10'
                }[status] || 'bg-card-tint text-muted border border-border';
            },

            syncSelectedCategory() {
                if (!this.addItemForm.category && this.menuCategories.length > 0) {
                    this.addItemForm.category = this.menuCategories[0].value;
                }

                const currentItemVisible = this.filteredMenuItemsForAdd.some((menuItem) => String(menuItem.id) === String(this.addItemForm.menuItemId));

                if (!currentItemVisible) {
                    this.addItemForm.menuItemId = this.filteredMenuItemsForAdd[0]?.id
                        ? String(this.filteredMenuItemsForAdd[0].id)
                        : '';
                }

                this.syncSelectedMenuItem();
            },

            syncSelectedMenuItem() {
                if (this.selectedMenuItemVariants.length > 0) {
                    const hasSelectedVariant = this.selectedMenuItemVariants.some((variant) => String(variant.id) === String(this.addItemForm.variantId));
                    this.addItemForm.variantId = hasSelectedVariant ? String(this.addItemForm.variantId) : String(this.selectedMenuItemVariants[0].id);
                    return;
                }

                this.addItemForm.variantId = '';
            },

            openAddItemModal() {
                if (this.menuItems.length === 0 || !this.selectedOrder) return;

                if (!this.addItemForm.menuItemId && this.menuItems[0]?.id) {
                    this.addItemForm.menuItemId = String(this.menuItems[0].id);
                }

                this.addItemError = '';
                this.addItemForm.itemSearch = '';
                this.addItemForm.quantity = Math.max(1, Number(this.addItemForm.quantity || 1));
                this.syncSelectedCategory();
                this.isAddItemModalOpen = true;
            },

            closeAddItemModal() {
                if (this.isAddingItem) return;

                this.isAddItemModalOpen = false;
                this.addItemError = '';
            },

            validationMessage(payload) {
                if (payload?.message && payload.message !== 'Validation failed') {
                    return payload.message;
                }

                const firstErrorGroup = Object.values(payload?.errors || {})[0];
                if (Array.isArray(firstErrorGroup) && firstErrorGroup.length > 0) {
                    return firstErrorGroup[0];
                }

                return 'Unable to add item.';
            },

            syncPaymentForm() {
                const order = this.selectedOrder;
                if (!order) return;

                this.paymentForm.status = order.paymentStatus || 'unpaid';
                this.paymentForm.method = order.paymentMethod || 'cash';
                this.paymentForm.amount = Number(order.rawTotal || 0);
                this.paymentForm.transactionId = '';
                this.paymentError = '';
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

                    if (this.selectedOrderId === updatedOrder.id) {
                        this.selectedOrderId = this.orders[0]?.id || null;
                        this.syncPaymentForm();
                    }

                    return;
                }

                if (index >= 0) {
                    this.orders.splice(index, 1, updatedOrder);
                } else {
                    this.orders.unshift(updatedOrder);
                }

                if (!this.selectedOrderId) {
                    this.selectedOrderId = updatedOrder.id;
                }

                if (this.selectedOrderId === updatedOrder.id) {
                    this.syncPaymentForm();
                }
            },

            async updateOrderStatus(orderId, status) {
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
                if (!response.ok || !payload.success) {
                    alert(payload.message || 'Unable to update order status.');
                    return;
                }

                this.replaceOrder(payload.order);
            },

            async updateOrderItemStatus(orderId, itemId, status) {
                this.isUpdatingItemStatus = true;

                const response = await fetch(`/orders/${orderId}/items/${itemId}/status`, {
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
                this.isUpdatingItemStatus = false;

                if (!response.ok || !payload.success) {
                    alert(payload.message || 'Unable to update item status.');
                    await this.refreshOrders();
                    return;
                }

                this.replaceOrder(payload.order);
            },

            async markCashPaid() {
                this.paymentForm.status = 'paid';
                this.paymentForm.method = 'cash';
                this.paymentForm.amount = Number(this.selectedOrder?.rawTotal || this.paymentForm.amount || 0);
                await this.updatePayment();
            },

            async updatePayment() {
                if (!this.selectedOrder) return;

                this.paymentError = '';
                const amount = Number(this.paymentForm.amount || 0);
                if (amount < 0) {
                    this.paymentError = 'Amount cannot be negative.';
                    return;
                }

                this.isUpdatingPayment = true;

                const response = await fetch(`/orders/${this.selectedOrder.id}/payment`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf
                    },
                    body: JSON.stringify({
                        payment_status: this.paymentForm.status,
                        payment_method: this.paymentForm.status === 'unpaid' ? null : this.paymentForm.method,
                        amount,
                        transaction_id: this.paymentForm.transactionId || null
                    })
                });

                const payload = await response.json();
                this.isUpdatingPayment = false;

                if (!response.ok || !payload.success) {
                    this.paymentError = this.validationMessage(payload);
                    return;
                }

                this.replaceOrder(payload.order);
            },

            async addOrderItem() {
                this.addItemError = '';

                if (!this.selectedOrder || !this.selectedMenuItem || !this.selectedMenuItemIsVisible) {
                    this.addItemError = 'Select an item first.';
                    return;
                }

                const quantity = Number(this.addItemForm.quantity || 1);
                if (!Number.isInteger(quantity) || quantity < 1 || quantity > 99) {
                    this.addItemError = 'Quantity must be between 1 and 99.';
                    return;
                }

                this.isAddingItem = true;

                const response = await fetch(`/orders/${this.selectedOrder.id}/items`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf
                    },
                    body: JSON.stringify({
                        menu_item_id: Number(this.addItemForm.menuItemId),
                        variant_id: this.addItemForm.variantId ? Number(this.addItemForm.variantId) : null,
                        quantity,
                        special_instructions: this.addItemForm.specialInstructions || null
                    })
                });

                const payload = await response.json();
                this.isAddingItem = false;

                if (!response.ok || !payload.success) {
                    this.addItemError = this.validationMessage(payload);
                    return;
                }

                this.replaceOrder(payload.order);
                this.addItemForm.quantity = 1;
                this.addItemForm.specialInstructions = '';
                this.closeAddItemModal();
            },

            async cancelOrder(orderId) {
                const response = await fetch(`/orders/${orderId}/cancel`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf
                    }
                });

                const payload = await response.json();
                if (!response.ok || !payload.success) {
                    alert(payload.message || 'Unable to cancel order.');
                    return;
                }

                this.replaceOrder(payload.order);
            },

            async refreshOrders() {
                const response = await fetch(`${config.feedUrl}?limit=100&active_only=1`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) return;
                const payload = await response.json();
                if (payload.success) {
                    this.orders = payload.orders || [];
                    if (this.selectedOrderId && !this.orders.some((order) => order.id === this.selectedOrderId)) {
                        this.selectedOrderId = this.orders[0]?.id || null;
                    }
                }
            },

            printReceipt(order) {
                if (!order) return;

                const gst = config.gstSettings || {};
                const total = Number(order.rawTotal || 0);
                const totalTaxRate = gst.enabled ? Number(gst.cgstRate || 0) + Number(gst.sgstRate || 0) : 0;
                const subtotal = totalTaxRate > 0 ? total / (1 + totalTaxRate / 100) : total;
                const cgstAmount = totalTaxRate > 0 ? subtotal * (Number(gst.cgstRate || 0) / 100) : 0;
                const sgstAmount = totalTaxRate > 0 ? subtotal * (Number(gst.sgstRate || 0) / 100) : 0;
                const printWindow = window.open('', '_blank', 'width=380,height=600');
                if (!printWindow) return;
                const doc = printWindow.document;
                doc.title = 'Bill Receipt - ' + String(order.displayId || '');
                doc.body.innerHTML = '';

                const style = doc.createElement('style');
                style.textContent = 'body{font-family:Courier,monospace;width:74mm;margin:0 auto;padding:10px 5px;font-size:10px;color:#000}.center{text-align:center}.divider{border-top:1px dashed #000;margin:6px 0}.bold{font-weight:bold}.row{display:flex;justify-content:space-between;margin-bottom:5px}.total{font-size:12px}';
                doc.head.appendChild(style);

                const addDiv = (text, className = '') => {
                    const div = doc.createElement('div');
                    if (className) div.className = className;
                    div.textContent = String(text || '');
                    doc.body.appendChild(div);
                    return div;
                };

                const addDivider = () => addDiv('', 'divider');
                const addRow = (label, value, className = '') => {
                    const row = doc.createElement('div');
                    row.className = 'row' + (className ? ' ' + className : '');

                    const labelEl = doc.createElement('span');
                    labelEl.textContent = String(label || '');
                    row.appendChild(labelEl);

                    const valueEl = doc.createElement('span');
                    valueEl.textContent = String(value || '');
                    row.appendChild(valueEl);

                    doc.body.appendChild(row);
                    return row;
                };

                const header = doc.createElement('div');
                header.className = 'center';
                doc.body.appendChild(header);

                const brand = doc.createElement('div');
                brand.className = 'bold';
                brand.textContent = String(gst.brandName || 'Business');
                header.appendChild(brand);

                [gst.address || '', gst.pincode ? 'PIN: ' + gst.pincode : '', gst.gstNo ? 'GSTIN: ' + gst.gstNo : '']
                    .filter(Boolean)
                    .forEach((line) => {
                        const lineEl = doc.createElement('div');
                        lineEl.textContent = String(line);
                        header.appendChild(lineEl);
                    });

                addDivider();
                addRow('Order:', order.displayId);
                addRow('Channel:', order.channel);
                addRow('Location:', order.location);
                addDivider();

                order.items.forEach((item) => {
                    addRow(item.name, 'x' + String(item.qty || 0));
                });

                addDivider();
                if (gst.enabled) {
                    addRow('CGST (' + String(gst.cgstRate || 0) + '%):', 'Rs. ' + cgstAmount.toFixed(2));
                    addRow('SGST (' + String(gst.sgstRate || 0) + '%):', 'Rs. ' + sgstAmount.toFixed(2));
                }
                addRow('Total:', order.amount, 'bold total');
                addDivider();
                addDiv('Thank you', 'center');

                printWindow.focus();

                setTimeout(() => {
                    printWindow.print();
                    setTimeout(() => printWindow.close(), 500);
                }, 250);
            }
        };
    }
</script>
@endsection
