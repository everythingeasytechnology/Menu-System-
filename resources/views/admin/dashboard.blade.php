@extends('admin.layout')

@section('title', 'Business Overview')
@section('eyebrow', 'Super Admin')
@section('page-title', 'Business Overview')
@section('page-subtitle', 'Platform overview and charts only.')

@section('content')
    @include('admin.partials.alerts')

    <section id="overview" class="scroll-mt-20 grid grid-cols-2 gap-3 lg:grid-cols-7">
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Businesses</span>
            <strong class="mt-1 block text-2xl font-black text-ink">{{ number_format($stats['businesses']) }}</strong>
        </div>
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Active</span>
            <strong class="mt-1 block text-2xl font-black text-success">{{ number_format($stats['active_businesses']) }}</strong>
        </div>
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Suspended</span>
            <strong class="mt-1 block text-2xl font-black text-danger">{{ number_format($stats['suspended_businesses']) }}</strong>
        </div>
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Active Owners</span>
            <strong class="mt-1 block text-2xl font-black text-success">{{ number_format($stats['active_owners']) }}</strong>
        </div>
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Live Orders</span>
            <strong class="mt-1 block text-2xl font-black text-orange">{{ number_format($stats['live_orders']) }}</strong>
        </div>
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Orders</span>
            <strong class="mt-1 block text-2xl font-black text-ink">{{ number_format($stats['orders']) }}</strong>
        </div>
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Gross Sales</span>
            <strong class="mt-1 block text-xl font-black text-teal">Rs. {{ number_format($stats['gross_sales'], 2) }}</strong>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,0.8fr)]">
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <div class="flex flex-col gap-2 border-b border-border pb-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-black text-ink">Orders and Sales</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">Last 6 months platform movement.</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] font-black text-muted">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-orange"></span>Orders</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-teal"></span>Sales</span>
                </div>
            </div>

            <div class="mt-5 flex h-64 items-end gap-3 border-b border-border px-2 pb-3">
                @foreach($charts['monthly'] as $month)
                    <div class="flex h-full min-w-0 flex-1 flex-col justify-end gap-2">
                        <div class="flex min-h-0 flex-1 items-end justify-center gap-1 rounded-t-lg bg-card-tint px-1.5 pt-2">
                            <span
                                class="block w-3 rounded-t bg-orange shadow-sm sm:w-4"
                                style="height: {{ $month['order_height'] }}%"
                                title="{{ $month['orders'] }} orders"
                            ></span>
                            <span
                                class="block w-3 rounded-t bg-teal shadow-sm sm:w-4"
                                style="height: {{ $month['sales_height'] }}%"
                                title="Rs. {{ number_format($month['sales'], 2) }}"
                            ></span>
                        </div>
                        <div class="text-center">
                            <span class="block text-[10px] font-black uppercase tracking-wider text-muted">{{ $month['label'] }}</span>
                            <span class="mt-0.5 block truncate text-[10px] font-bold text-ink">Rs. {{ number_format($month['sales'], 0) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid gap-5">
            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <div class="border-b border-border pb-3">
                    <h2 class="text-base font-black text-ink">Business Status</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">Current business account distribution.</p>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach($charts['business_statuses'] as $status)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-xs font-black">
                                <span class="text-ink">{{ $status['label'] }}</span>
                                <span class="text-muted">{{ number_format($status['count']) }} ({{ $status['percent'] }}%)</span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-card-tint">
                                <div
                                    @class([
                                        'h-2 rounded-full',
                                        'bg-success' => $status['label'] === 'Active',
                                        'bg-warning' => $status['label'] === 'Inactive',
                                        'bg-danger' => $status['label'] === 'Suspended',
                                    ])
                                    style="width: {{ $status['percent'] }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <div class="border-b border-border pb-3">
                    <h2 class="text-base font-black text-ink">Owner Access</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">Owner login status overview.</p>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach($charts['owner_statuses'] as $status)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-xs font-black">
                                <span class="text-ink">{{ $status['label'] }}</span>
                                <span class="text-muted">{{ number_format($status['count']) }} ({{ $status['percent'] }}%)</span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-card-tint">
                                <div
                                    @class([
                                        'h-2 rounded-full',
                                        'bg-success' => $status['label'] === 'Active',
                                        'bg-warning' => $status['label'] === 'Inactive',
                                        'bg-danger' => $status['label'] === 'Suspended',
                                    ])
                                    style="width: {{ $status['percent'] }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
