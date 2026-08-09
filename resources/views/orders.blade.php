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
        ])
    })"
    x-init="start()"
    class="space-y-3"
>
    <x-card class="p-2" variant="default">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex w-full items-center gap-1 overflow-x-auto rounded-lg border border-border bg-card-tint p-0.5 lg:w-auto">
                <button @click="activeTab = 'all'" :class="tabClass('all')" class="shrink-0 rounded-md px-2.5 py-1 text-[10px] font-bold">All</button>
                <button @click="activeTab = 'dine-in'" :class="tabClass('dine-in')" class="shrink-0 rounded-md px-2.5 py-1 text-[10px] font-bold">Dining</button>
                <button @click="activeTab = 'takeaway'" :class="tabClass('takeaway')" class="shrink-0 rounded-md px-2.5 py-1 text-[10px] font-bold">Packed</button>
                <button @click="activeTab = 'room-service'" :class="tabClass('room-service')" class="shrink-0 rounded-md px-2.5 py-1 text-[10px] font-bold">Room</button>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Search order, customer, phone, location..."
                    class="w-full rounded-lg border border-border bg-card-tint px-3 py-1.5 text-[11px] text-ink outline-none transition-all placeholder:text-muted focus:border-orange lg:w-72"
                >

                <select x-model="statusFilter" class="rounded-lg border border-border bg-card-tint px-3 py-1.5 text-[11px] font-semibold text-ink outline-none">
                    <option value="all">All Statuses</option>
                    @foreach($statuses as $status => $label)
                        <option value="{{ $status }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-3">
            <x-card class="p-0" variant="default">
                <div class="max-h-[calc(100vh-238px)] overflow-auto">
                    <table class="w-full min-w-[640px] border-collapse text-left">
                        <thead class="sticky top-0 z-10 bg-card">
                            <tr class="border-b border-border bg-card-tint text-[9px] font-bold uppercase tracking-wider text-muted">
                                <th class="px-3 py-2">Order</th>
                                <th class="px-3 py-2">Location</th>
                                <th class="px-3 py-2">Customer</th>
                                <th class="px-3 py-2 text-center">Items</th>
                                <th class="px-3 py-2">Payment</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <template x-for="order in filteredOrders" :key="order.id">
                                <tr
                                    @click="selectOrder(order.id)"
                                    :class="selectedOrderId === order.id ? 'bg-orange/5' : 'hover:bg-card-tint/40'"
                                    class="cursor-pointer transition-all"
                                >
                                    <td class="px-3 py-2 text-[11px] font-black text-orange" x-text="order.displayId"></td>
                                    <td class="px-3 py-2 text-[11px] font-bold text-ink" x-text="order.location"></td>
                                    <td class="px-3 py-2">
                                        <div class="text-[11px] font-bold text-ink" x-text="order.customer"></div>
                                        <div class="text-[9px] font-semibold text-muted" x-text="order.phone"></div>
                                    </td>
                                    <td class="px-3 py-2 text-center text-[11px] font-bold text-muted" x-text="order.itemCount"></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex rounded-md px-1.5 py-0.5 text-[8px] font-extrabold uppercase tracking-wider" :class="paymentClass(order.paymentStatus)" x-text="order.paymentLabel"></span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex rounded-md px-1.5 py-0.5 text-[8px] font-extrabold uppercase tracking-wider" :class="statusClass(order.status)" x-text="order.statusLabel"></span>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="filteredOrders.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center">
                                    <p class="text-xs font-black text-ink">No orders found</p>
                                    <p class="mt-1 text-xs text-muted">Try clearing filters or wait for new orders.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <div
            class="xl:col-span-1"
            :class="selectedOrderId ? 'fixed inset-0 z-50 flex items-end justify-center bg-navy-deep/60 p-3 backdrop-blur-xs xl:static xl:z-auto xl:block xl:bg-transparent xl:p-0 xl:backdrop-blur-none' : 'hidden xl:block'"
            @click.self="selectedOrderId = null"
        >
            <template x-if="selectedOrder">
                <x-card class="w-full max-w-sm space-y-3 border border-border p-3 shadow-2xl xl:max-w-none xl:shadow-sm" variant="default">
                    <div class="flex items-start justify-between border-b border-border pb-2">
                        <div>
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-muted">Order Detail</span>
                            <h3 class="mt-0.5 text-xs font-extrabold text-ink" x-text="selectedOrder.location"></h3>
                            <p class="mt-0.5 text-[10px] font-semibold text-muted">
                                <span x-text="selectedOrder.customer"></span>
                                <span x-show="selectedOrder.phone !== 'N/A'" x-text="` (${selectedOrder.phone})`"></span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs font-black text-orange" x-text="selectedOrder.displayId"></span>
                            <button type="button" @click="selectedOrderId = null" class="mt-1 text-[11px] font-bold text-muted hover:text-ink xl:hidden">Close</button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-muted">Items</span>
                        <div class="max-h-56 divide-y divide-border/60 overflow-auto">
                            <template x-for="item in selectedOrder.items" :key="item.id">
                                <div class="grid grid-cols-[minmax(0,1fr)_112px] items-center gap-2 py-1.5 text-[11px]">
                                    <div class="min-w-0">
                                        <span class="block truncate font-extrabold text-ink" x-text="item.name"></span>
                                        <span class="block text-[9px] text-muted" x-text="`Qty: ${item.qty}`"></span>
                                    </div>
                                    <label class="block">
                                        <span class="mb-0.5 block text-[8px] font-bold uppercase tracking-wider text-muted">Item Status</span>
                                        <select
                                            :value="item.status"
                                            @click.stop
                                            @change="updateOrderItemStatus(selectedOrder.id, item.id, $event.target.value)"
                                            :disabled="isUpdatingItemStatus || selectedOrder.status === 'completed' || selectedOrder.status === 'cancelled'"
                                            class="w-full rounded-md border px-2 py-1 text-[9px] font-extrabold uppercase tracking-wider outline-none disabled:cursor-not-allowed disabled:opacity-60"
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

                    <div class="border-t border-border pt-2">
                        <button
                            type="button"
                            @click="openAddItemModal()"
                            :disabled="menuItems.length === 0 || !selectedOrder || selectedOrder.status === 'completed' || selectedOrder.status === 'cancelled'"
                            class="w-full rounded-lg bg-orange px-3 py-2 text-[11px] font-bold text-white shadow-md shadow-orange/20 transition-all hover:bg-orange/95 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Add Item
                        </button>

                        <template x-if="menuItems.length === 0">
                            <p class="mt-1.5 text-[10px] font-semibold text-muted">No active menu items available.</p>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <label class="block">
                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-muted">Order Status</span>
                            <select
                                @change="updateOrderStatus(selectedOrder.id, $event.target.value)"
                                class="w-full rounded-lg border border-border bg-card-tint px-3 py-1.5 text-[11px] font-bold text-ink outline-none focus:border-orange"
                            >
                                @foreach($statuses as $status => $label)
                                    <option value="{{ $status }}" :selected="selectedOrder.status === '{{ $status }}'">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <template x-if="selectedOrder.note">
                        <div class="rounded-lg border border-orange/10 bg-orange/5 p-2">
                            <span class="block text-[8px] font-bold uppercase tracking-wider text-orange">Instructions</span>
                            <p class="mt-1 text-[10px] italic leading-normal text-slate-700" x-text="selectedOrder.note"></p>
                        </div>
                    </template>

                    <div class="border-t border-border pt-3">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-[10px] font-bold text-muted">
                                <span x-text="`Payment: ${selectedOrder.paymentLabel}`"></span>
                                <span class="text-ink" x-text="selectedOrder.amount"></span>
                            </div>

                            <div class="grid grid-cols-2 gap-1.5">
                                <label class="block">
                                    <span class="mb-1 block text-[9px] font-bold uppercase tracking-wider text-muted">Status</span>
                                    <select x-model="paymentForm.status" class="w-full rounded-lg border border-border bg-card-tint px-2 py-1.5 text-[11px] font-bold text-ink outline-none focus:border-orange">
                                        <option value="unpaid">Unpaid</option>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="mb-1 block text-[9px] font-bold uppercase tracking-wider text-muted">Method</span>
                                    <select x-model="paymentForm.method" class="w-full rounded-lg border border-border bg-card-tint px-2 py-1.5 text-[11px] font-bold text-ink outline-none focus:border-orange">
                                        <option value="cash">Cash</option>
                                        <option value="online">Online</option>
                                        <option value="razorpay">Razorpay</option>
                                    </select>
                                </label>
                            </div>

                            <div class="grid grid-cols-[minmax(0,1fr)_88px] gap-1.5">
                                <label class="block">
                                    <span class="mb-1 block text-[9px] font-bold uppercase tracking-wider text-muted">Amount</span>
                                    <input type="number" min="0" step="0.01" x-model.number="paymentForm.amount" class="w-full rounded-lg border border-border bg-card-tint px-2 py-1.5 text-[11px] font-bold text-ink outline-none focus:border-orange">
                                </label>

                                <button type="button" @click="markCashPaid()" :disabled="isUpdatingPayment" class="self-end rounded-lg bg-success/10 py-1.5 text-[10px] font-black text-success transition-all hover:bg-success/20 disabled:opacity-50">Cash Paid</button>
                            </div>

                            <template x-if="paymentError">
                                <p class="rounded-lg border border-danger/10 bg-danger/5 px-2 py-1.5 text-[10px] font-bold text-danger" x-text="paymentError"></p>
                            </template>
                        </div>

                        <div class="mt-2 flex gap-2">
                            <button type="button" @click="updatePayment()" :disabled="isUpdatingPayment" class="flex-1 rounded-lg bg-teal/10 py-2 text-center text-[11px] font-bold text-teal transition-all hover:bg-teal/20 disabled:opacity-50" x-text="isUpdatingPayment ? 'Saving...' : 'Update Payment'"></button>
                            <button type="button" @click="printReceipt(selectedOrder)" class="flex-1 rounded-lg bg-orange py-2 text-center text-[11px] font-bold text-white shadow-md shadow-orange/20 transition-all hover:bg-orange/95">Print</button>
                            <template x-if="selectedOrder.status !== 'completed' && selectedOrder.status !== 'cancelled'">
                                <button type="button" @click="cancelOrder(selectedOrder.id)" class="flex-1 rounded-lg bg-danger/10 py-2 text-center text-[11px] font-bold text-danger transition-all hover:bg-danger/20">Cancel</button>
                            </template>
                        </div>
                    </div>
                </x-card>
            </template>

            <template x-if="!selectedOrder">
                <x-card class="flex h-36 flex-col items-center justify-center border border-dashed border-border p-3 text-center" variant="default">
                    <h3 class="text-xs font-bold text-ink">No Order Selected</h3>
                    <p class="mt-1 text-[10px] leading-normal text-muted">Select an order from the live database list.</p>
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
            liveStatuses: ['pending', 'confirmed', 'preparing', 'ready', 'served'],
            activeTab: 'all',
            searchQuery: '',
            statusFilter: 'all',
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

            tabClass(tab) {
                return this.activeTab === tab
                    ? 'bg-white text-ink shadow-sm'
                    : 'text-muted hover:text-ink';
            },

            statusClass(status) {
                return {
                    pending: 'bg-slate-100 text-slate-600 border border-slate-200',
                    confirmed: 'bg-blue-50 text-blue-500 border border-blue-100',
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
