<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Forgot Password - EverythingEasy ServiceOS</title>

    @php $brandLogoUrl = app(\App\Services\MailBrandingService::class)->logoUrl(); @endphp
    @if($brandLogoUrl)
        <link rel="icon" type="image/png" href="{{ $brandLogoUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-bg font-sans text-ink antialiased">
    <main class="flex min-h-screen items-center justify-center px-5 py-8">
        <section class="w-full max-w-md rounded-3xl border border-border bg-card p-5 shadow-xl shadow-slate-200/70 sm:p-7">
            <div class="mb-7">
                <p class="text-xs font-black uppercase tracking-wider text-orange">Password Reset</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-ink">Reset your password</h1>
                <p class="mt-2 text-sm leading-6 text-muted">Enter your active owner or admin email address.</p>
            </div>

            @if(session('status'))
                <div class="mb-5 flex gap-3 rounded-xl border border-success/20 bg-success/10 px-4 py-3 text-xs font-semibold text-success">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 flex gap-3 rounded-xl border border-danger/20 bg-danger/10 px-4 py-3 text-xs font-semibold text-danger">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-xs font-black uppercase tracking-wider text-ink">Email address</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full rounded-xl border border-border bg-card-tint py-3 pl-11 pr-4 text-sm text-ink placeholder-muted outline-none transition-all focus:border-orange focus:bg-card focus:ring-2 focus:ring-orange/15"
                            placeholder="owner@example.com"
                        >
                    </div>
                </div>

                <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-orange px-5 py-3 text-sm font-black text-white shadow-md shadow-orange/20 transition-all hover:bg-orange/95 active:scale-[0.99]">
                    Send reset link
                </button>
            </form>

            <a href="{{ route('login') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-border bg-card px-5 py-3 text-sm font-black text-ink transition hover:border-orange hover:text-orange">
                Back to login
            </a>
        </section>
    </main>
</body>
</html>
