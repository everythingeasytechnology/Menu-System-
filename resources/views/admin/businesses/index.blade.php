@extends('admin.layout')

@section('title', 'Business List')
@section('eyebrow', 'Business Control')
@section('page-title', 'View Businesses')
@section('page-subtitle', 'View business owners and open details for editing.')

@section('content')
    @include('admin.partials.alerts')

    <section class="rounded-lg border border-border bg-card shadow-sm">
        <div class="border-b border-border p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-black text-ink">Business List</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">Only business owner accounts are shown here.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <form method="GET" action="{{ route('admin.businesses.index') }}" class="flex flex-col gap-2 sm:flex-row">
                        <input name="business_search" value="{{ $filters['business_search'] ?? '' }}" placeholder="Search business" class="h-9 rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                        <select name="business_status" class="h-9 rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                            <option value="all">All status</option>
                            @foreach($businessStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['business_status'] ?? 'all') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="h-9 rounded-lg bg-navy px-3 text-xs font-black text-white">Filter</button>
                    </form>
                    <a href="{{ route('admin.businesses.create') }}" class="inline-flex h-9 items-center justify-center rounded-lg bg-orange px-3 text-xs font-black text-white shadow-lg shadow-orange/20">
                        Create Business
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full text-left text-xs">
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
                            <td class="px-3 py-3">
                                <span class="block font-black text-ink">{{ $business->name }}</span>
                                <span class="mt-0.5 block text-muted">{{ $business->type }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="block font-black text-ink">{{ $business->owner?->name ?? 'No owner' }}</span>
                                <span class="mt-0.5 block text-muted">{{ $business->owner?->email ?? 'Not assigned' }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="block font-bold text-ink">{{ $business->email ?: 'No email' }}</span>
                                <span class="mt-0.5 block text-muted">{{ $business->phone ?: 'No phone' }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="block font-bold text-ink">{{ collect([$business->city, $business->state])->filter()->join(', ') ?: 'No city' }}</span>
                                <span class="mt-0.5 block text-muted">{{ $business->country ?: 'No country' }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span
                                    @class([
                                        'inline-flex rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-wider',
                                        'bg-success/10 text-success' => $business->status === 'active',
                                        'bg-warning/10 text-warning' => $business->status === 'inactive',
                                        'bg-danger/10 text-danger' => $business->status === 'suspended',
                                    ])
                                >
                                    {{ $businessStatuses[$business->status] ?? ucfirst($business->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="block font-black text-ink">{{ number_format($business->orders_count) }}</span>
                                <span class="mt-0.5 block text-muted">{{ $business->created_at?->format('d M Y') }}</span>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <a href="{{ route('admin.businesses.edit', $business) }}" class="inline-flex h-8 items-center justify-center rounded-lg bg-navy px-3 text-[10px] font-black uppercase tracking-wider text-white">
                                    Edit
                                </a>
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

        <div class="border-t border-border px-4 py-3">
            {{ $businesses->links() }}
        </div>
    </section>
@endsection
