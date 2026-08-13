@extends('admin.layout')

@section('title', 'App Version')
@section('eyebrow', 'Platform')
@section('page-title', 'App Version')
@section('page-subtitle', 'Manage the mobile app version returned by the API.')

@section('content')
    @include('admin.partials.alerts')

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
        <form method="POST" action="{{ route('admin.app-version.update') }}" class="rounded-lg border border-border bg-card p-4 shadow-sm">
            @csrf

            <div class="border-b border-border pb-3">
                <h2 class="text-base font-black text-ink">Version Details</h2>
                <p class="mt-0.5 text-xs font-semibold text-muted">This value is returned from the public app-version API.</p>
            </div>

            <label class="mt-4 block">
                <span class="text-[10px] font-black uppercase tracking-wider text-muted">App Version</span>
                <input
                    name="version"
                    value="{{ old('version', $appVersion->version) }}"
                    required
                    maxlength="50"
                    placeholder="1.0.0"
                    class="mt-1 h-11 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none transition focus:border-orange focus:bg-card"
                >
            </label>

            <div class="mt-4 flex justify-end border-t border-border pt-4">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-orange px-5 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                    Save Version
                </button>
            </div>
        </form>

        <aside class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <div class="border-b border-border pb-3">
                <h2 class="text-base font-black text-ink">Current Version</h2>
                <p class="mt-0.5 text-xs font-semibold text-muted">Visible to mobile apps.</p>
            </div>

            <dl class="mt-4 space-y-3 text-xs font-bold">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-muted">Version</dt>
                    <dd class="rounded-full bg-orange/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-orange">{{ $appVersion->version }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-muted">Updated</dt>
                    <dd class="truncate text-ink">{{ $appVersion->updated_at?->format('d M Y, h:i A') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-muted">API</dt>
                    <dd class="truncate text-ink">GET /api/v1/app-version</dd>
                </div>
            </dl>
        </aside>
    </section>
@endsection
