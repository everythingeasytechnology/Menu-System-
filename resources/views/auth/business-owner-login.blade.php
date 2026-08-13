<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Business Owner Login - EverythingEasy ServiceOS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-bg text-ink font-sans antialiased">
    <main class="min-h-screen grid lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.72fr)]">
        <section class="relative hidden overflow-hidden bg-navy-deep text-white lg:flex">
            <div class="absolute inset-0 opacity-[0.08]" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 38px 38px;"></div>

            <div class="relative z-10 flex min-h-screen w-full flex-col justify-between p-10 xl:p-14">
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

                <div class="grid gap-10 xl:grid-cols-[0.82fr_1fr] xl:items-center">
                    <div>
                        <p class="mb-4 text-xs font-black uppercase tracking-[0.22em] text-orange">Business Owner Login</p>
                        <h1 class="text-4xl font-black leading-tight tracking-tight xl:text-5xl">Control every service point from one desk.</h1>
                        <p class="mt-5 max-w-lg text-sm leading-6 text-slate-300">Manage menus, staff access, QR ordering, payments, and reports with a secure owner account.</p>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-4 shadow-2xl shadow-black/25 backdrop-blur">
                        <div class="rounded-2xl bg-white p-4 text-ink">
                            <div class="flex items-center justify-between border-b border-border pb-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-muted">Live Summary</p>
                                    <p class="mt-1 text-sm font-black">Connaught Place</p>
                                </div>
                                <span class="rounded-full bg-success/10 px-2.5 py-1 text-[10px] font-black text-success">Online</span>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="rounded-xl bg-card-tint p-3">
                                    <p class="text-[10px] font-bold text-muted">Orders</p>
                                    <p class="mt-1 text-xl font-black">124</p>
                                </div>
                                <div class="rounded-xl bg-card-tint p-3">
                                    <p class="text-[10px] font-bold text-muted">Sales</p>
                                    <p class="mt-1 text-xl font-black">45k</p>
                                </div>
                                <div class="rounded-xl bg-card-tint p-3">
                                    <p class="text-[10px] font-bold text-muted">Tables</p>
                                    <p class="mt-1 text-xl font-black">18</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-2">
                                <div class="flex items-center justify-between rounded-xl border border-border px-3 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-2.5 w-2.5 rounded-full bg-orange"></span>
                                        <span class="text-xs font-bold">Order #1256 preparing</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-muted">2 min</span>
                                </div>
                                <div class="flex items-center justify-between rounded-xl border border-border px-3 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-2.5 w-2.5 rounded-full bg-teal"></span>
                                        <span class="text-xs font-bold">QR table scan received</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-muted">Now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 text-xs">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="font-black text-white">Menu Control</p>
                        <p class="mt-1 text-slate-400">Items and categories</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="font-black text-white">QR Service</p>
                        <p class="mt-1 text-slate-400">Tables and rooms</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="font-black text-white">Staff Access</p>
                        <p class="mt-1 text-slate-400">Roles and security</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center px-5 py-8 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-between lg:hidden">
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

                <div class="rounded-3xl border border-border bg-card p-5 shadow-xl shadow-slate-200/70 sm:p-7">
                    <div class="mb-7">
                        <p class="text-xs font-black uppercase tracking-wider text-orange">Business Owner Login</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-ink">Sign in to your dashboard</h2>
                        <p class="mt-2 text-sm leading-6 text-muted">Use your active owner or admin account to continue.</p>
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
                </div>
            </div>
        </section>
    </main>
</body>
</html>
