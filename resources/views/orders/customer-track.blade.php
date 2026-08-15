<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $payload['order']['displayId'] ?? 'Order' }} - Track Order</title>

    @php $brandLogoUrl = app(\App\Services\MailBrandingService::class)->logoUrl(); @endphp
    @if($brandLogoUrl)
        <link rel="icon" type="image/png" href="{{ $brandLogoUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bg text-ink antialiased">
    <main
        x-data="customerOrderTracker(@js($payload))"
        x-init="start()"
        class="mx-auto flex min-h-screen w-full max-w-5xl flex-col px-4 py-6 sm:px-6"
    >
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orange">Order Tracking</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-ink" x-text="data.business.name"></h1>
                <p class="mt-2 text-sm font-semibold text-muted">Track live order status and view your bill.</p>
            </div>

            <div class="flex gap-2">
                <a
                    :href="data.links.bill_url"
                    class="inline-flex items-center justify-center rounded-lg border border-border bg-card px-4 py-2 text-xs font-black text-ink shadow-sm transition hover:bg-card-tint"
                >
                    View Bill
                </a>
                <a
                    :href="data.links.bill_print_url"
                    class="inline-flex items-center justify-center rounded-lg bg-orange px-4 py-2 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95"
                >
                    Download Bill
                </a>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px]">
            <section class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-border pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-muted">Order</p>
                        <h2 class="mt-1 text-2xl font-black text-orange" x-text="data.order.displayId"></h2>
                        <p class="mt-1 text-sm font-semibold text-muted" x-text="data.order.orderNumber"></p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider"
                            :class="statusClass(data.order.status)"
                            x-text="data.order.statusLabel"
                        ></span>
                        <p class="mt-2 text-xs font-bold text-muted" x-text="data.receipt.date"></p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-card-tint p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-muted">Customer</p>
                        <p class="mt-1 text-sm font-black text-ink" x-text="data.receipt.customer"></p>
                        <p class="mt-1 text-xs font-semibold text-muted" x-text="data.receipt.phone"></p>
                        <template x-if="data.receipt.email">
                            <p class="mt-1 break-all text-xs font-semibold text-muted" x-text="data.receipt.email"></p>
                        </template>
                    </div>
                    <div class="rounded-lg bg-card-tint p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-muted">Location</p>
                        <p class="mt-1 text-sm font-black text-ink" x-text="data.receipt.location"></p>
                        <p class="mt-1 text-xs font-semibold text-muted" x-text="data.receipt.channel"></p>
                    </div>
                    <div class="rounded-lg bg-card-tint p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-muted">Payment</p>
                        <p class="mt-1 text-sm font-black text-ink" x-text="data.receipt.payment_label"></p>
                        <p class="mt-1 text-xs font-semibold text-muted" x-text="money(data.receipt.total)"></p>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="grid grid-cols-4 gap-2">
                        <template x-for="step in steps" :key="step.status">
                            <div class="flex flex-col items-center gap-2">
                                <span
                                    class="flex h-10 w-10 items-center justify-center rounded-full border text-[11px] font-black"
                                    :class="stepClass(step.status)"
                                    x-text="step.short"
                                ></span>
                                <span
                                    class="text-[11px] font-black uppercase tracking-wider"
                                    :class="stepTextClass(step.status)"
                                    x-text="step.label"
                                ></span>
                            </div>
                        </template>
                    </div>
                </div>

                <template x-if="data.receipt.note">
                    <div class="mt-5 rounded-lg border border-orange/15 bg-orange/5 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-orange">Instructions</p>
                        <p class="mt-1 text-sm font-semibold text-ink" x-text="data.receipt.note"></p>
                    </div>
                </template>

                <div class="mt-5 rounded-lg border border-border">
                    <div class="grid grid-cols-[minmax(0,1fr)_92px_96px] gap-2 border-b border-border bg-card-tint px-3 py-2 text-[10px] font-black uppercase tracking-wider text-muted">
                        <span>Item</span>
                        <span class="text-center">Qty</span>
                        <span class="text-right">Total</span>
                    </div>
                    <div class="divide-y divide-border">
                        <template x-for="item in data.receipt.items" :key="`${item.name}-${item.quantity}`">
                            <div class="grid grid-cols-[minmax(0,1fr)_92px_96px] gap-2 px-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-ink" x-text="item.name"></p>
                                    <p class="mt-1 text-xs font-semibold text-muted" x-text="money(item.unit_price)"></p>
                                    <template x-if="item.special_instructions">
                                        <p class="mt-1 text-xs font-semibold text-orange" x-text="item.special_instructions"></p>
                                    </template>
                                </div>
                                <div class="text-center text-sm font-black text-ink" x-text="item.quantity"></div>
                                <div class="text-right text-sm font-black text-ink" x-text="money(item.line_total)"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <aside class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-muted">Bill Summary</p>
                <div class="mt-4 space-y-2 text-sm font-semibold text-ink">
                    <div class="flex items-center justify-between">
                        <span>Subtotal</span>
                        <span x-text="money(data.receipt.subtotal)"></span>
                    </div>
                    <template x-if="data.receipt.discount > 0">
                        <div class="flex items-center justify-between text-danger">
                            <span x-text="data.receipt.coupon_code ? `Coupon (${data.receipt.coupon_code})` : 'Discount'"></span>
                            <span x-text="`- ${money(data.receipt.discount)}`"></span>
                        </div>
                    </template>
                    <template x-if="data.receipt.has_gst">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span>Before GST</span>
                                <span x-text="money(data.receipt.taxable_after_discount)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span x-text="`CGST (${Number(data.receipt.cgst_rate || 0).toFixed(2)}%)`"></span>
                                <span x-text="money(data.receipt.cgst_amount)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span x-text="`SGST (${Number(data.receipt.sgst_rate || 0).toFixed(2)}%)`"></span>
                                <span x-text="money(data.receipt.sgst_amount)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Total GST</span>
                                <span x-text="money(data.receipt.tax)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-4 rounded-lg bg-card-tint px-4 py-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-black text-muted">Total</span>
                        <span class="text-lg font-black text-orange" x-text="money(data.receipt.total)"></span>
                    </div>
                </div>

                <div class="mt-5 rounded-lg border border-border bg-card-tint p-3 text-xs font-semibold text-muted">
                    <p>Auto refreshes every 20 seconds so the latest order status stays visible.</p>
                </div>
            </aside>
        </div>
    </main>

    <script>
        function customerOrderTracker(initialData) {
            return {
                data: initialData,
                timer: null,
                steps: [
                    { status: 'preparing', label: 'Preparing', short: '1' },
                    { status: 'ready', label: 'Ready', short: '2' },
                    { status: 'served', label: 'Served', short: '3' },
                    { status: 'completed', label: 'Completed', short: '4' }
                ],

                start() {
                    this.timer = setInterval(() => this.refresh(), 20000);
                },

                refresh() {
                    fetch(this.data.links.status_url, {
                        headers: { Accept: 'application/json' }
                    })
                        .then((response) => response.ok ? response.json() : null)
                        .then((payload) => {
                            if (payload?.success && payload.data) {
                                this.data = payload.data;
                            }
                        })
                        .catch(() => {});
                },

                money(value) {
                    return 'Rs. ' + Number(value || 0).toFixed(2);
                },

                stepIndex(status) {
                    if (status === 'preparing') return 0;
                    if (status === 'ready') return 1;
                    if (status === 'served') return 2;
                    if (status === 'completed') return 3;
                    return 0;
                },

                stepClass(status) {
                    if (this.data.order.status === 'cancelled') {
                        return status === 'preparing'
                            ? 'border-danger bg-danger text-white'
                            : 'border-border bg-card text-muted';
                    }

                    return this.stepIndex(this.data.order.status) >= this.stepIndex(status)
                        ? 'border-orange bg-orange text-white'
                        : 'border-border bg-card text-muted';
                },

                stepTextClass(status) {
                    if (this.data.order.status === 'cancelled') {
                        return status === 'preparing' ? 'text-danger' : 'text-muted';
                    }

                    return this.stepIndex(this.data.order.status) >= this.stepIndex(status)
                        ? 'text-orange'
                        : 'text-muted';
                },

                statusClass(status) {
                    return {
                        preparing: 'bg-orange/10 text-orange',
                        ready: 'bg-teal/10 text-teal',
                        served: 'bg-success/10 text-success',
                        completed: 'bg-success/10 text-success',
                        cancelled: 'bg-danger/10 text-danger'
                    }[status] || 'bg-card-tint text-muted';
                }
            };
        }
    </script>
</body>
</html>
