@extends('admin.layout')

@section('title', 'Mail Settings')
@section('eyebrow', 'Platform')
@section('page-title', 'Mail Settings')
@section('page-subtitle', 'SMTP configuration for platform emails.')

@section('content')
    @include('admin.partials.alerts')

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <form method="POST" action="{{ route('admin.mail-settings.update') }}" class="rounded-lg border border-border bg-card p-4 shadow-sm">
            @csrf

            <div class="flex flex-col gap-3 border-b border-border pb-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-black text-ink">SMTP Details</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">Used by password reset, OTP, registration, and future transactional emails.</p>
                </div>

                <label class="inline-flex items-center gap-2 rounded-lg border border-border bg-card-tint px-3 py-2">
                    <input type="hidden" name="enabled" value="0">
                    <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $setting?->enabled ?? false)) class="h-4 w-4 rounded border-border text-orange focus:ring-orange">
                    <span class="text-xs font-black text-ink">Enable SMTP</span>
                </label>
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <label class="block md:col-span-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">SMTP Host</span>
                    <input name="host" value="{{ old('host', $setting?->host) }}" placeholder="smtp.gmail.com" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    <span class="mt-1 block text-[11px] font-semibold text-muted">Use only the mail server name, for example smtp.gmail.com or mail.everythingeasy.in.</span>
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Port</span>
                    <input type="number" min="1" max="65535" name="port" value="{{ old('port', $setting?->port ?? 587) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Encryption</span>
                    <select name="encryption" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                        @foreach($encryptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('encryption', $setting ? ($setting->encryption ?? 'none') : 'tls') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Username</span>
                    <input name="username" value="{{ old('username', $setting?->username) }}" autocomplete="off" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    <span class="mt-1 block text-[11px] font-semibold text-muted">This can be your mailbox email, for example noreply@everythingeasy.in.</span>
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Password</span>
                    <input type="password" name="password" value="" autocomplete="new-password" placeholder="{{ $setting?->getRawOriginal('password') ? 'Saved. Leave blank to keep' : '' }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">From Email</span>
                    <input type="email" name="from_address" value="{{ old('from_address', $setting?->from_address) }}" placeholder="noreply@example.com" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">From Name</span>
                    <input name="from_name" value="{{ old('from_name', $setting?->from_name ?? config('app.name')) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Timeout Seconds</span>
                    <input type="number" min="1" max="120" name="timeout" value="{{ old('timeout', $setting?->timeout ?? 30) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>
            </div>

            <div class="mt-4 flex justify-end border-t border-border pt-4">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-orange px-5 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                    Save SMTP Settings
                </button>
            </div>
        </form>

        <aside class="space-y-5">
            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <div class="border-b border-border pb-3">
                    <h2 class="text-base font-black text-ink">Current Status</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">SMTP runtime state.</p>
                </div>

                <dl class="mt-4 space-y-3 text-xs font-bold">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Status</dt>
                        <dd @class([
                            'rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-wider',
                            'bg-success/10 text-success' => $setting?->enabled,
                            'bg-warning/10 text-warning' => ! $setting?->enabled,
                        ])>
                            {{ $setting?->enabled ? 'Enabled' : 'Disabled' }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Host</dt>
                        <dd class="truncate text-ink">{{ $setting?->host ?: 'Not set' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">From</dt>
                        <dd class="truncate text-ink">{{ $setting?->from_address ?: 'Not set' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Last Test</dt>
                        <dd class="truncate text-ink">{{ $setting?->last_tested_at?->format('d M, h:i A') ?: 'Not tested' }}</dd>
                    </div>
                    @if($setting?->last_test_message)
                        <div>
                            <dt class="text-muted">Test Message</dt>
                            <dd class="mt-1 rounded-lg bg-card-tint p-2 text-[11px] font-semibold text-ink">{{ $setting->last_test_message }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <form method="POST" action="{{ route('admin.mail-settings.test') }}" class="rounded-lg border border-border bg-card p-4 shadow-sm">
                @csrf
                <div class="border-b border-border pb-3">
                    <h2 class="text-base font-black text-ink">Send Test</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">Requires saved and enabled SMTP settings.</p>
                </div>

                <label class="mt-4 block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Test Email</span>
                    <input type="email" name="test_email" value="{{ old('test_email', 'info@everythingeasy.in') }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                </label>

                <button type="submit" class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-navy px-4 text-xs font-black text-white transition hover:bg-navy/90">
                    Send Test Email
                </button>
            </form>
        </aside>
    </section>
@endsection
