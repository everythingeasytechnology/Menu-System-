@extends('admin.layout')

@section('title', 'Edit Business')
@section('eyebrow', 'Business Control')
@section('page-title', 'Edit Business Details')
@section('page-subtitle', $business->name)

@section('content')
    @include('admin.partials.alerts')

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <div class="border-b border-border pb-3">
                <h2 class="text-base font-black text-ink">Business and Owner Details</h2>
                <p class="mt-0.5 text-xs font-semibold text-muted">Edit business profile, owner login details, and access status.</p>
            </div>

            <form method="POST" action="{{ route('admin.businesses.update', $business) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid gap-3 md:grid-cols-3">
                    <label class="block md:col-span-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Name</span>
                        <input name="name" value="{{ old('name', $business->name) }}" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Type</span>
                        <input name="type" value="{{ old('type', $business->type) }}" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Status</span>
                        <select name="status" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                            @foreach($businessStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $business->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Email</span>
                        <input type="email" name="email" value="{{ old('email', $business->email) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Phone</span>
                        <input name="phone" value="{{ old('phone', $business->phone) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">City</span>
                        <input name="city" value="{{ old('city', $business->city) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">State</span>
                        <input name="state" value="{{ old('state', $business->state) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Country</span>
                        <input name="country" value="{{ old('country', $business->country) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                </div>

                <div class="border-t border-border pt-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-ink">Owner Login</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-4">
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Name</span>
                            <input name="owner_name" value="{{ old('owner_name', $business->owner?->name) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange" @disabled(! $business->owner)>
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Email</span>
                            <input type="email" name="owner_email" value="{{ old('owner_email', $business->owner?->email) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange" @disabled(! $business->owner)>
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Phone</span>
                            <input name="owner_phone" value="{{ old('owner_phone', $business->owner?->phone) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange" @disabled(! $business->owner)>
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Status</span>
                            <select name="owner_status" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange" @disabled(! $business->owner)>
                                @foreach($ownerStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('owner_status', $business->owner?->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-t border-border pt-4 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.businesses.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-border bg-card px-4 text-xs font-black text-ink transition hover:border-orange hover:text-orange">
                        Back to List
                    </a>
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-orange px-5 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                        Save Details
                    </button>
                </div>
            </form>
        </div>

        <aside class="space-y-3">
            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <span class="text-[10px] font-black uppercase tracking-wider text-muted">Current Status</span>
                <strong class="mt-1 block text-xl font-black text-ink">{{ $businessStatuses[$business->status] ?? ucfirst($business->status) }}</strong>
            </div>
            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <span class="text-[10px] font-black uppercase tracking-wider text-muted">Orders</span>
                <strong class="mt-1 block text-xl font-black text-orange">{{ number_format($business->orders_count) }}</strong>
            </div>
            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner</span>
                <strong class="mt-1 block truncate text-sm font-black text-ink">{{ $business->owner?->name ?? 'No owner assigned' }}</strong>
                <span class="mt-1 block truncate text-xs font-semibold text-muted">{{ $business->owner?->email ?? 'Owner login missing' }}</span>
            </div>
        </aside>
    </section>
@endsection
