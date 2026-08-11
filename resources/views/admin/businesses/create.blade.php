@extends('admin.layout')

@section('title', 'Create Business')
@section('eyebrow', 'Business Setup')
@section('page-title', 'Create Business')
@section('page-subtitle', 'Add a new business owner account.')

@section('content')
    @include('admin.partials.alerts')

    <section class="max-w-5xl rounded-lg border border-border bg-card p-4 shadow-sm">
        <div class="border-b border-border pb-3">
            <h2 class="text-base font-black text-ink">Business Details</h2>
            <p class="mt-0.5 text-xs font-semibold text-muted">The owner will use the same login page after creation.</p>
        </div>

        <form method="POST" action="{{ route('admin.businesses.store') }}" class="mt-4 space-y-4">
            @csrf

            <div class="grid gap-3 md:grid-cols-3">
                <label class="block md:col-span-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Name</span>
                    <input name="business_name" value="{{ old('business_name') }}" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Type</span>
                    <input name="business_type" value="{{ old('business_type', 'restaurant') }}" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Status</span>
                    <select name="business_status" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                        @foreach($businessStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('business_status', 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Email</span>
                    <input type="email" name="business_email" value="{{ old('business_email') }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Business Phone</span>
                    <input name="business_phone" value="{{ old('business_phone') }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">City</span>
                    <input name="city" value="{{ old('city') }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">State</span>
                    <input name="state" value="{{ old('state') }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Country</span>
                    <input name="country" value="{{ old('country', 'India') }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>
            </div>

            <div class="border-t border-border pt-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-ink">Owner Login</h3>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Name</span>
                        <input name="owner_name" value="{{ old('owner_name') }}" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Email</span>
                        <input type="email" name="owner_email" value="{{ old('owner_email') }}" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Owner Phone</span>
                        <input name="owner_phone" value="{{ old('owner_phone') }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Password</span>
                        <input type="password" name="owner_password" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Confirm Password</span>
                        <input type="password" name="owner_password_confirmation" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>
                </div>
            </div>

            <div class="flex flex-col gap-2 border-t border-border pt-4 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.businesses.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-border bg-card px-4 text-xs font-black text-ink transition hover:border-orange hover:text-orange">
                    Cancel
                </a>
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-orange px-5 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                    Create Business
                </button>
            </div>
        </form>
    </section>
@endsection
