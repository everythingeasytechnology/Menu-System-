@extends('layouts.app')

@section('title', 'Coupons')

@section('content')
@php
    $editing = $editingCoupon !== null;
    $formAction = $editing ? route('dashboard.coupons.update', $editingCoupon) : route('dashboard.coupons.store');
    $formTitle = $editing ? 'Edit Coupon' : 'Create Coupon';
    $formDescription = $editing
        ? 'Update discount rules, limits, and availability for this code.'
        : 'Create a coupon code customers can apply at checkout.';
    $cancelQuery = request()->except(['edit', 'page']);

    $codeValue = old('code', $editingCoupon?->code);
    $typeValue = old('type', $editingCoupon?->type ?? 'percentage');
    $valueValue = old('value', $editingCoupon?->value);
    $minimumOrderValue = old('minimum_order', $editingCoupon?->minimum_order ?? 0);
    $maximumDiscountValue = old('maximum_discount', $editingCoupon?->maximum_discount);
    $usageLimitValue = old('usage_limit', $editingCoupon?->usage_limit);
    $perUserLimitValue = old('per_user_limit', $editingCoupon?->per_user_limit);
    $startsAtValue = old('starts_at', $editingCoupon?->starts_at?->format('Y-m-d\TH:i'));
    $expiresAtValue = old('expires_at', $editingCoupon?->expires_at?->format('Y-m-d\TH:i'));
    $isActiveValue = (bool) old('is_active', $editingCoupon?->is_active ?? true);
@endphp

<div class="space-y-8">
    @if(session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-5 right-5 z-50 flex max-w-sm items-center gap-3 rounded-xl border border-orange/20 bg-navy-deep px-5 py-4 text-white shadow-xl"
        >
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange/20 text-orange">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-xs font-bold text-white">Coupon Notification</p>
                <p class="mt-0.5 text-[11px] text-slate-300">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="cursor-pointer text-muted transition-colors hover:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Coupon Studio</h1>
            <p class="mt-1 text-sm text-muted">Create, edit, pause, and monitor checkout coupons for {{ $business->name }}.</p>
        </div>
        <a
            href="{{ route('dashboard.coupons.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-orange/20 transition-all hover:bg-orange/95 active:scale-[0.98]"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>New Coupon</span>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-card class="p-5" variant="default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Total Coupons</p>
                    <p class="mt-2 text-2xl font-black text-ink">{{ $stats['total'] }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange/10 text-orange">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v14M5 5a2 2 0 00-2 2v3a2 2 0 010 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 010-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card class="p-5" variant="default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Live Now</p>
                    <p class="mt-2 text-2xl font-black text-teal">{{ $stats['active'] }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal/10 text-teal">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card class="p-5" variant="default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Redemptions</p>
                    <p class="mt-2 text-2xl font-black text-ink">{{ $stats['redemptions'] }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-navy/10 text-navy">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125C16.5 3.504 17.004 3 17.625 3h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <x-card class="p-5" variant="default">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Expired</p>
                    <p class="mt-2 text-2xl font-black text-danger">{{ $stats['expired'] }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </x-card>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-danger/20 bg-danger/5 px-4 py-3 text-sm text-danger">
            <p class="font-bold">Please fix the coupon form.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-card class="p-5" variant="{{ $editing ? 'warm' : 'default' }}">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <h2 class="text-base font-black text-ink">{{ $formTitle }}</h2>
                <p class="mt-1 text-xs text-muted">{{ $formDescription }}</p>
            </div>
            @if($editing)
                <a href="{{ route('dashboard.coupons.index', $cancelQuery) }}" class="inline-flex items-center justify-center rounded-xl border border-border px-3 py-2 text-xs font-bold text-muted transition-colors hover:text-ink">
                    Cancel Edit
                </a>
            @endif
        </div>

        <form method="POST" action="{{ $formAction }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            @csrf
            @if($editing)
                @method('PUT')
            @endif

            <div class="lg:col-span-3">
                <label for="code" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Coupon Code</label>
                <input
                    id="code"
                    name="code"
                    value="{{ $codeValue }}"
                    placeholder="SAVE10"
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-black uppercase text-ink outline-none transition-all focus:border-orange"
                    required
                >
            </div>

            <div class="lg:col-span-2">
                <label for="type" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Discount Type</label>
                <select id="type" name="type" class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-bold text-ink outline-none transition-all focus:border-orange" required>
                    <option value="percentage" @selected($typeValue === 'percentage')>Percentage</option>
                    <option value="fixed" @selected($typeValue === 'fixed')>Fixed Amount</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="value" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Value</label>
                <input
                    id="value"
                    name="value"
                    type="number"
                    min="0"
                    step="0.01"
                    value="{{ $valueValue }}"
                    placeholder="10"
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-bold text-ink outline-none transition-all focus:border-orange"
                    required
                >
            </div>

            <div class="lg:col-span-2">
                <label for="minimum_order" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Minimum Order</label>
                <input
                    id="minimum_order"
                    name="minimum_order"
                    type="number"
                    min="0"
                    step="0.01"
                    value="{{ $minimumOrderValue }}"
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-bold text-ink outline-none transition-all focus:border-orange"
                >
            </div>

            <div class="lg:col-span-3">
                <label for="maximum_discount" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Maximum Discount</label>
                <input
                    id="maximum_discount"
                    name="maximum_discount"
                    type="number"
                    min="0"
                    step="0.01"
                    value="{{ $maximumDiscountValue }}"
                    placeholder="Optional"
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-bold text-ink outline-none transition-all focus:border-orange"
                >
            </div>

            <div class="lg:col-span-2">
                <label for="usage_limit" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Usage Limit</label>
                <input
                    id="usage_limit"
                    name="usage_limit"
                    type="number"
                    min="1"
                    value="{{ $usageLimitValue }}"
                    placeholder="Unlimited"
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-bold text-ink outline-none transition-all focus:border-orange"
                >
            </div>

            <div class="lg:col-span-2">
                <label for="per_user_limit" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Per User</label>
                <input
                    id="per_user_limit"
                    name="per_user_limit"
                    type="number"
                    min="1"
                    value="{{ $perUserLimitValue }}"
                    placeholder="Optional"
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-bold text-ink outline-none transition-all focus:border-orange"
                >
            </div>

            <div class="lg:col-span-3">
                <label for="starts_at" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Starts At</label>
                <input
                    id="starts_at"
                    name="starts_at"
                    type="datetime-local"
                    value="{{ $startsAtValue }}"
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-bold text-ink outline-none transition-all focus:border-orange"
                >
            </div>

            <div class="lg:col-span-3">
                <label for="expires_at" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-muted">Expires At</label>
                <input
                    id="expires_at"
                    name="expires_at"
                    type="datetime-local"
                    value="{{ $expiresAtValue }}"
                    class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 text-sm font-bold text-ink outline-none transition-all focus:border-orange"
                >
            </div>

            <div class="flex items-end lg:col-span-2">
                <label class="flex w-full cursor-pointer items-center justify-between rounded-xl border border-border bg-card-tint px-3 py-2.5">
                    <span class="text-xs font-bold text-ink">Active</span>
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-border text-orange focus:ring-orange" @checked($isActiveValue)>
                </label>
            </div>

            <div class="flex items-end lg:col-span-12">
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange px-5 py-3 text-xs font-black text-white shadow-md shadow-orange/20 transition-all hover:bg-orange/95 active:scale-[0.98] sm:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $editing ? 'M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z' : 'M12 4.5v15m7.5-7.5h-15' }}" />
                    </svg>
                    <span>{{ $editing ? 'Update Coupon' : 'Create Coupon' }}</span>
                </button>
            </div>
        </form>
    </x-card>

    <x-card class="p-4" variant="default">
        <form method="GET" action="{{ route('dashboard.coupons.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-3 md:flex-row">
                <div class="relative flex-1">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search coupon code..."
                        class="w-full rounded-xl border border-border bg-card-tint px-3 py-2.5 pl-9 text-xs font-semibold text-ink outline-none transition-all placeholder:text-muted focus:border-orange"
                    >
                    <div class="absolute left-3 top-2.5 text-muted">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <select name="status" class="rounded-xl border border-border bg-card-tint px-3 py-2.5 text-xs font-bold text-ink outline-none transition-all focus:border-orange">
                    <option value="all" @selected(request('status', 'all') === 'all')>All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                    <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>

                <select name="type" class="rounded-xl border border-border bg-card-tint px-3 py-2.5 text-xs font-bold text-ink outline-none transition-all focus:border-orange">
                    <option value="all" @selected(request('type', 'all') === 'all')>All Types</option>
                    <option value="percentage" @selected(request('type') === 'percentage')>Percentage</option>
                    <option value="fixed" @selected(request('type') === 'fixed')>Fixed Amount</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white transition-all hover:bg-navy-deep">Filter</button>
                <a href="{{ route('dashboard.coupons.index') }}" class="rounded-xl border border-border px-4 py-2.5 text-xs font-bold text-muted transition-colors hover:text-ink">Clear</a>
            </div>
        </form>
    </x-card>

    <x-card class="overflow-x-auto p-0" variant="default">
        <table class="w-full min-w-[980px] border-collapse text-left">
            <thead>
                <tr class="border-b border-border bg-card-tint text-xs font-bold uppercase tracking-wider text-muted">
                    <th class="px-5 py-3.5">Code</th>
                    <th class="px-5 py-3.5">Discount</th>
                    <th class="px-5 py-3.5">Order Rules</th>
                    <th class="px-5 py-3.5">Usage</th>
                    <th class="px-5 py-3.5">Validity</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-xs text-ink">
                @forelse($coupons as $coupon)
                    @php
                        if (! $coupon->is_active) {
                            $statusLabel = 'Inactive';
                            $statusClass = 'bg-slate-100 text-slate-500';
                        } elseif ($coupon->expires_at && $coupon->expires_at->isPast()) {
                            $statusLabel = 'Expired';
                            $statusClass = 'bg-danger/10 text-danger';
                        } elseif ($coupon->starts_at && $coupon->starts_at->isFuture()) {
                            $statusLabel = 'Scheduled';
                            $statusClass = 'bg-warning/10 text-warning';
                        } else {
                            $statusLabel = 'Active';
                            $statusClass = 'bg-teal/10 text-teal';
                        }
                    @endphp
                    <tr class="transition-all hover:bg-card-tint">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-navy-deep px-2.5 py-1.5 font-mono text-xs font-black text-white">{{ $coupon->code }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-black text-ink">
                                @if($coupon->type === 'percentage')
                                    {{ number_format((float) $coupon->value, 2) }}%
                                @else
                                    Rs. {{ number_format((float) $coupon->value, 2) }}
                                @endif
                            </p>
                            <p class="mt-1 text-[11px] font-semibold capitalize text-muted">{{ str_replace('_', ' ', $coupon->type) }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-bold text-ink">Min Rs. {{ number_format((float) $coupon->minimum_order, 2) }}</p>
                            <p class="mt-1 text-[11px] text-muted">
                                Max {{ $coupon->maximum_discount !== null ? 'Rs. ' . number_format((float) $coupon->maximum_discount, 2) : 'unlimited' }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-bold text-ink">{{ $coupon->used_count }} used</p>
                            <p class="mt-1 text-[11px] text-muted">
                                Limit {{ $coupon->usage_limit ?: 'unlimited' }} / User {{ $coupon->per_user_limit ?: 'unlimited' }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-ink">{{ $coupon->starts_at ? $coupon->starts_at->format('d M Y, h:i A') : 'Starts immediately' }}</p>
                            <p class="mt-1 text-[11px] text-muted">{{ $coupon->expires_at ? 'Ends ' . $coupon->expires_at->format('d M Y, h:i A') : 'No expiry' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a
                                    href="{{ route('dashboard.coupons.index', array_merge(request()->except('page'), ['edit' => $coupon->id])) }}"
                                    class="inline-flex rounded-lg p-1.5 text-blue transition-colors hover:bg-blue/5"
                                    title="Edit Coupon"
                                >
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('dashboard.coupons.toggle', $coupon) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-orange/5 hover:text-orange" title="{{ $coupon->is_active ? 'Pause Coupon' : 'Activate Coupon' }}">
                                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            @if($coupon->is_active)
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6M5.25 5.25h13.5v13.5H5.25V5.25z" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l10.5 5.847a1.125 1.125 0 010 1.972l-10.5 5.847A1.125 1.125 0 015.25 17.347V5.653z" />
                                            @endif
                                        </svg>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('dashboard.coupons.destroy', $coupon) }}" onsubmit="return confirm('Deactivate this coupon?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-danger/5 hover:text-danger" title="Deactivate Coupon">
                                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 105.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-card-tint text-muted">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v14M5 5a2 2 0 00-2 2v3a2 2 0 010 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 010-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                </div>
                                <p class="mt-4 text-sm font-black text-ink">No coupons found</p>
                                <p class="mt-1 text-xs text-muted">Create your first coupon or clear filters to see existing codes.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    @if($coupons->hasPages())
        <div>
            {{ $coupons->links() }}
        </div>
    @endif
</div>
@endsection
