<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Business Owner Login - EverythingEasy ServiceOS</title>

    @php $brandLogoUrl = app(\App\Services\MailBrandingService::class)->logoUrl(); @endphp
    @if($brandLogoUrl)
        <link rel="icon" type="image/png" href="{{ $brandLogoUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-bg text-ink font-sans antialiased">
    <main class="min-h-screen grid lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.72fr)]">
        <section class="relative hidden overflow-hidden bg-navy-deep text-white lg:flex">
            <div class="absolute inset-0 opacity-[0.08]" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 38px 38px;"></div>

            <div class="relative z-10 flex min-h-screen w-full flex-col p-10 xl:p-14">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <x-super-admin-logo
                            image-box-class="inline-flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-white p-1 shadow-lg shadow-orange/30"
                            fallback-box-class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange text-white shadow-lg shadow-orange/30"
                            icon-class="h-6 w-6"
                        />
                        <div>
                            <p class="text-lg font-black leading-tight">EverythingEasy</p>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-orange">ServiceOS</p>
                        </div>
                    </div>

                    <div class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[11px] font-bold text-slate-200">
                        Owner Portal
                    </div>
                </div>

                <div class="flex flex-1 flex-col justify-center gap-8 py-10">
                <div>
                    <p class="mb-4 text-xs font-black uppercase tracking-[0.22em] text-orange">Smart Menu &middot; Order &middot; Billing</p>
                    <h1 class="text-4xl font-black leading-tight tracking-tight xl:text-5xl">Manage your restaurant <span class="text-orange">smarter, faster.</span></h1>
                    <p class="mt-5 max-w-lg text-sm leading-6 text-slate-300">Smart menu management, QR ordering, order tracking, billing, payments, staff access and detailed reports — all from one powerful dashboard.</p>
                </div>

                <div class="relative">
                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-4 shadow-2xl shadow-black/25 backdrop-blur">
                        <div class="rounded-2xl bg-white p-4 text-ink">
                            <div class="flex items-center justify-between border-b border-border pb-3">
                                <p class="text-sm font-black">Dashboard Overview</p>
                                <span class="rounded-full bg-card-tint px-2.5 py-1 text-[10px] font-black text-muted">Today</span>
                            </div>

                            <div class="mt-4 grid grid-cols-4 gap-2">
                                <div class="rounded-xl border border-border p-3">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-orange/10 text-orange">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75h14.5v9.5H4.75v-9.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 19.25h7.5" /></svg>
                                    </span>
                                    <p class="mt-2 text-[9px] font-bold text-muted">Total Orders</p>
                                    <p class="text-base font-black">242</p>
                                    <p class="text-[9px] font-bold text-success">+18.2%</p>
                                </div>
                                <div class="rounded-xl border border-border p-3">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-teal/10 text-teal">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M17 7.5c0-1.66-2.24-3-5-3s-5 1.34-5 3 2.24 3 5 3 5 1.34 5 3-2.24 3-5 3-5-1.34-5-3" /></svg>
                                    </span>
                                    <p class="mt-2 text-[9px] font-bold text-muted">Revenue</p>
                                    <p class="text-base font-black">&#8377;45,320</p>
                                    <p class="text-[9px] font-bold text-success">+22.4%</p>
                                </div>
                                <div class="rounded-xl border border-border p-3">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-navy/10 text-navy">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 21V9a1 1 0 011-1h14a1 1 0 011 1v12M4 21h16M4 21v-6a1 1 0 011-1h3M20 21v-6a1 1 0 00-1-1h-3" /></svg>
                                    </span>
                                    <p class="mt-2 text-[9px] font-bold text-muted">Active Tables</p>
                                    <p class="text-base font-black">18</p>
                                    <p class="text-[9px] font-bold text-success">+3</p>
                                </div>
                                <div class="rounded-xl border border-border p-3">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-orange/10 text-orange">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </span>
                                    <p class="mt-2 text-[9px] font-bold text-muted">Pending</p>
                                    <p class="text-base font-black">32</p>
                                    <p class="text-[9px] font-bold text-danger">-6</p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-[10px] font-black uppercase tracking-wider text-muted">Live Orders</p>
                                <span class="text-[10px] font-bold text-orange">View all</span>
                            </div>
                            <div class="mt-2 space-y-1.5">
                                <div class="flex items-center justify-between rounded-xl border border-border px-3 py-2">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-2 w-2 rounded-full bg-orange"></span>
                                        <span class="text-xs font-bold">#1256 &middot; Table 5</span>
                                    </div>
                                    <span class="rounded-full bg-orange/10 px-2 py-0.5 text-[9px] font-black text-orange">Preparing</span>
                                </div>
                                <div class="flex items-center justify-between rounded-xl border border-border px-3 py-2">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-2 w-2 rounded-full bg-teal"></span>
                                        <span class="text-xs font-bold">#1255 &middot; Table 2</span>
                                    </div>
                                    <span class="rounded-full bg-teal/10 px-2 py-0.5 text-[9px] font-black text-teal">New</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phone mockup overlay -->
                    <div class="absolute bottom-2 right-2 hidden h-40 w-24 overflow-hidden rounded-2xl border-4 border-navy-deep bg-navy-deep shadow-2xl shadow-black/40 xl:block">
                        <div class="flex h-full w-full flex-col overflow-hidden rounded-xl bg-white text-ink" style="font-size: 7px; line-height: 1.3;">
                            <div class="shrink-0 bg-navy-deep px-2 py-1.5 text-white">
                                <p class="font-bold" style="font-size: 7px;">Order #1256</p>
                                <span class="mt-1 inline-block rounded-full bg-orange/25 px-1.5 py-0.5 font-black text-orange" style="font-size: 6px;">Preparing</span>
                            </div>
                            <div class="flex-1 space-y-1.5 overflow-hidden p-2">
                                <p class="border-b border-border pb-1 font-bold text-muted" style="font-size: 6px;">Items (4)</p>
                                <div class="flex items-center justify-between font-semibold">
                                    <span class="truncate">Margherita</span><span class="shrink-0 text-muted">x2</span>
                                </div>
                                <div class="flex items-center justify-between font-semibold">
                                    <span class="truncate">Veg Burger</span><span class="shrink-0 text-muted">x1</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-border pt-1 font-black" style="font-size: 8px;">
                                    <span>Total</span>
                                    <span>&#8377;946</span>
                                </div>
                            </div>
                            <div class="shrink-0 bg-orange py-1.5 text-center font-black text-white" style="font-size: 6px;">View Details</div>
                        </div>
                    </div>
                </div>
                </div>

                <div class="grid grid-cols-5 gap-2 text-center text-[10px]">
                    <div class="flex flex-col items-center gap-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-orange">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h6M17 14v6" /></svg>
                        </span>
                        <p class="font-bold text-slate-300">QR Ordering</p>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-orange">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </span>
                        <p class="font-bold text-slate-300">Live Orders</p>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-orange">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6M9 11h6M9 15h4M5 3h14a1 1 0 011 1v16l-4-2-3 2-3-2-3 2-3-2V4a1 1 0 011-1z" /></svg>
                        </span>
                        <p class="font-bold text-slate-300">Smart Billing</p>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-orange">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M6 15h3M3 6h18a1 1 0 011 1v10a1 1 0 01-1 1H3a1 1 0 01-1-1V7a1 1 0 011-1z" /></svg>
                        </span>
                        <p class="font-bold text-slate-300">Payments</p>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-orange">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 21V9M12 21V3M19 21v-7" /></svg>
                        </span>
                        <p class="font-bold text-slate-300">Reports</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative flex items-center justify-center overflow-hidden bg-bg px-5 py-8 sm:px-8">
            <div class="pointer-events-none absolute inset-0 opacity-[0.35]" style="background-image: radial-gradient(circle at 1px 1px, #cbd5e1 1px, transparent 0); background-size: 22px 22px;"></div>

            <div class="relative w-full max-w-md">
                <div class="mb-6 flex items-center justify-between lg:hidden">
                    <div class="flex items-center gap-3">
                        <x-super-admin-logo
                            image-box-class="inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border bg-white p-1 shadow-md shadow-orange/20"
                            fallback-box-class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange text-white shadow-md shadow-orange/20"
                            icon-class="h-6 w-6"
                        />
                        <div>
                            <p class="text-base font-black leading-tight">EverythingEasy</p>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-orange">ServiceOS</p>
                        </div>
                    </div>
                    <span class="rounded-full border border-border bg-card px-3 py-1.5 text-[10px] font-black text-muted">Owner</span>
                </div>

                <div class="rounded-3xl border border-border bg-card p-5 shadow-xl shadow-slate-200/70 sm:p-8">
                    <div class="mb-7 flex flex-col items-center text-center">
                        <span class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-orange text-white shadow-lg shadow-orange/30">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12.5C4 9 7 6.5 12 6.5s8 2.5 8 6M4 12.5h16M4 12.5c0 1 .5 1.5 1.5 1.5h13c1 0 1.5-.5 1.5-1.5M12 6.5V4" />
                                <circle cx="12" cy="3.5" r="1" fill="currentColor" stroke="none" />
                            </svg>
                        </span>
                        <p class="text-xs font-black uppercase tracking-wider text-orange">Business Owner Login</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-ink">Welcome back!</h2>
                        <p class="mt-2 max-w-xs text-sm leading-6 text-muted">Sign in to your dashboard and manage your restaurant easily.</p>
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

                    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" x-data="{ showPassword: false }">
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

                        <div class="space-y-2">
                            <label for="password" class="block text-xs font-black uppercase tracking-wider text-ink">Password</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <input
                                    id="password"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    autocomplete="current-password"
                                    class="w-full rounded-xl border border-border bg-card-tint py-3 pl-11 pr-12 text-sm text-ink placeholder-muted outline-none transition-all focus:border-orange focus:bg-card focus:ring-2 focus:ring-orange/15"
                                    placeholder="Your password"
                                >
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-muted transition-colors hover:bg-card hover:text-ink"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                >
                                    <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58M9.88 5.35A9.7 9.7 0 0112 5.12c6 0 9.75 6.88 9.75 6.88a17.27 17.27 0 01-2.13 2.95M6.35 6.35C3.8 8.1 2.25 12 2.25 12s3.75 6.88 9.75 6.88a9.77 9.77 0 004.08-.9" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-muted">
                                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-border text-orange focus:ring-orange/20">
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}" class="text-xs font-black text-orange transition hover:text-orange/80">
                                Forgot password?
                            </a>
                        </div>

                        <button
                            type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-orange px-5 py-3 text-sm font-black text-white shadow-md shadow-orange/20 transition-all hover:bg-orange/95 active:scale-[0.99]"
                        >
                            <span>Sign in to dashboard</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </form>

                    <p class="mt-6 flex items-center justify-center gap-1.5 text-[11px] font-semibold text-muted">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Secure login &middot; Your data is protected
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
