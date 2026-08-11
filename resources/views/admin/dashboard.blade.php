@extends('admin.layout')

@section('title', 'Business Control Center')
@section('eyebrow', 'Super Admin')
@section('page-title', 'Business Control Center')
@section('page-subtitle', 'Manage business accounts and business owner access.')

@section('content')
    @if(session('success'))
        <div class="rounded-lg border border-success/20 bg-success/10 px-4 py-3 text-sm font-bold text-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-danger/20 bg-danger/10 px-4 py-3 text-sm font-bold text-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-danger/20 bg-danger/10 px-4 py-3 text-sm font-bold text-danger">
            {{ $errors->first() }}
        </div>
    @endif

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
            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owners</span>
            <strong class="mt-1 block text-2xl font-black text-orange">{{ number_format($stats['owners']) }}</strong>
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

    <section class="grid gap-5 xl:grid-cols-[420px_minmax(0,1fr)]">
        <div id="create-business" class="scroll-mt-20 rounded-lg border border-border bg-card p-4 shadow-sm">
            <div class="border-b border-border pb-3">
                <h2 class="text-base font-black text-ink">Create Business</h2>
                <p class="mt-0.5 text-xs font-semibold text-muted">Business owner account will use the same login system.</p>
            </div>

            <form method="POST" action="{{ route('admin.businesses.store') }}" class="mt-4 space-y-3">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Name</span>
                        <input name="business_name" value="{{ old('business_name') }}" required class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Type</span>
                        <input name="business_type" value="{{ old('business_type', 'restaurant') }}" required class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Status</span>
                        <select name="business_status" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                            @foreach($businessStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('business_status', 'active') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Email</span>
                        <input type="email" name="business_email" value="{{ old('business_email') }}" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Phone</span>
                        <input name="business_phone" value="{{ old('business_phone') }}" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">City</span>
                        <input name="city" value="{{ old('city') }}" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">State</span>
                        <input name="state" value="{{ old('state') }}" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Country</span>
                        <input name="country" value="{{ old('country', 'India') }}" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                    </label>
                </div>

                <div class="border-t border-border pt-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-ink">Owner Login</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Name</span>
                            <input name="owner_name" value="{{ old('owner_name') }}" required class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Email</span>
                            <input type="email" name="owner_email" value="{{ old('owner_email') }}" required class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Phone</span>
                            <input name="owner_phone" value="{{ old('owner_phone') }}" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Password</span>
                            <input type="password" name="owner_password" required class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Confirm Password</span>
                            <input type="password" name="owner_password_confirmation" required class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                        </label>
                    </div>
                </div>

                <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-orange px-4 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                    Create Business
                </button>
            </form>
        </div>

        <section id="businesses" class="scroll-mt-20 rounded-lg border border-border bg-card shadow-sm">
            <div class="border-b border-border p-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-black text-ink">Businesses</h2>
                        <p class="mt-0.5 text-xs font-semibold text-muted">Suspend, reactivate, and update business owner account details.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col gap-2 sm:flex-row">
                        <input name="business_search" value="{{ $filters['business_search'] ?? '' }}" placeholder="Search business" class="h-9 rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                        <select name="business_status" class="h-9 rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                            <option value="all">All status</option>
                            @foreach($businessStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['business_status'] ?? 'all') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="h-9 rounded-lg bg-navy px-3 text-xs font-black text-white">Filter</button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1050px] w-full text-left text-xs">
                    <thead class="border-b border-border bg-card-tint text-[10px] font-black uppercase tracking-wider text-muted">
                        <tr>
                            <th class="px-3 py-2">Business</th>
                            <th class="px-3 py-2">Owner</th>
                            <th class="px-3 py-2">Contact</th>
                            <th class="px-3 py-2">Location</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Orders</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($businesses as $business)
                            <tr class="align-top">
                                <td class="px-3 py-2">
                                    <input form="business-form-{{ $business->id }}" name="name" value="{{ $business->name }}" required class="h-8 w-48 rounded-lg border border-border bg-card-tint px-2 text-xs font-black text-ink outline-none focus:border-orange">
                                    <input form="business-form-{{ $business->id }}" name="type" value="{{ $business->type }}" required class="mt-1 h-8 w-48 rounded-lg border border-border bg-card-tint px-2 text-xs font-bold text-muted outline-none focus:border-orange">
                                </td>
                                <td class="px-3 py-2">
                                    <input form="business-form-{{ $business->id }}" name="owner_name" value="{{ $business->owner?->name }}" placeholder="Owner name" class="h-8 w-44 rounded-lg border border-border bg-card-tint px-2 text-xs font-black text-ink outline-none focus:border-orange" @disabled(! $business->owner)>
                                    <input form="business-form-{{ $business->id }}" type="email" name="owner_email" value="{{ $business->owner?->email }}" placeholder="Owner email" class="mt-1 h-8 w-44 rounded-lg border border-border bg-card-tint px-2 text-xs font-bold text-muted outline-none focus:border-orange" @disabled(! $business->owner)>
                                    <div class="mt-1 flex gap-1">
                                        <input form="business-form-{{ $business->id }}" name="owner_phone" value="{{ $business->owner?->phone }}" placeholder="Phone" class="h-8 w-24 rounded-lg border border-border bg-card-tint px-2 text-xs font-bold text-ink outline-none focus:border-orange" @disabled(! $business->owner)>
                                        <select form="business-form-{{ $business->id }}" name="owner_status" class="h-8 w-24 rounded-lg border border-border bg-card-tint px-2 text-xs font-black text-ink outline-none focus:border-orange" @disabled(! $business->owner)>
                                            @foreach($ownerStatuses as $value => $label)
                                                <option value="{{ $value }}" @selected($business->owner?->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input form="business-form-{{ $business->id }}" type="email" name="email" value="{{ $business->email }}" placeholder="Email" class="h-8 w-44 rounded-lg border border-border bg-card-tint px-2 text-xs font-bold text-ink outline-none focus:border-orange">
                                    <input form="business-form-{{ $business->id }}" name="phone" value="{{ $business->phone }}" placeholder="Phone" class="mt-1 h-8 w-44 rounded-lg border border-border bg-card-tint px-2 text-xs font-bold text-ink outline-none focus:border-orange">
                                </td>
                                <td class="px-3 py-2">
                                    <input form="business-form-{{ $business->id }}" name="city" value="{{ $business->city }}" placeholder="City" class="h-8 w-36 rounded-lg border border-border bg-card-tint px-2 text-xs font-bold text-ink outline-none focus:border-orange">
                                    <div class="mt-1 flex gap-1">
                                        <input form="business-form-{{ $business->id }}" name="state" value="{{ $business->state }}" placeholder="State" class="h-8 w-24 rounded-lg border border-border bg-card-tint px-2 text-xs font-bold text-ink outline-none focus:border-orange">
                                        <input form="business-form-{{ $business->id }}" name="country" value="{{ $business->country }}" placeholder="Country" class="h-8 w-24 rounded-lg border border-border bg-card-tint px-2 text-xs font-bold text-ink outline-none focus:border-orange">
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <select form="business-form-{{ $business->id }}" name="status" class="h-8 rounded-lg border border-border bg-card-tint px-2 text-xs font-black text-ink outline-none focus:border-orange">
                                        @foreach($businessStatuses as $value => $label)
                                            <option value="{{ $value }}" @selected($business->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="block font-black text-ink">{{ $business->orders_count }} orders</span>
                                    <span class="mt-0.5 block text-muted">{{ $business->created_at?->format('d M Y') }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <form id="business-form-{{ $business->id }}" method="POST" action="{{ route('admin.businesses.update', $business) }}">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <button form="business-form-{{ $business->id }}" type="submit" class="h-8 rounded-lg bg-orange px-3 text-[10px] font-black uppercase tracking-wider text-white">
                                        Save
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center font-bold text-muted">No businesses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
