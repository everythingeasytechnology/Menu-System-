<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Super Admin') - EverythingEasy ServiceOS</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-bg font-sans text-ink antialiased">
    <div
        x-data="{ adminSidebarOpen: false, adminSidebarCompact: false }"
        class="min-h-screen bg-bg lg:flex"
    >
        <div
            x-cloak
            x-show="adminSidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-navy-deep/60 lg:hidden"
            @click="adminSidebarOpen = false"
        ></div>

        <aside
            :class="[
                adminSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                adminSidebarCompact ? 'lg:w-24' : 'lg:w-72'
            ]"
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-white/10 bg-navy-deep text-white shadow-2xl transition-all duration-200 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:shadow-none"
        >
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-4">
                <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 flex-1 items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-orange text-sm font-black text-white shadow-lg shadow-orange/25">
                        SA
                    </span>
                    <span x-show="!adminSidebarCompact" x-transition class="min-w-0">
                        <span class="block truncate text-sm font-black leading-tight">ServiceOS Admin</span>
                        <span class="mt-0.5 block truncate text-[10px] font-bold uppercase tracking-[0.18em] text-white/50">Super Admin</span>
                    </span>
                </a>

                <button
                    type="button"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white/15 lg:hidden"
                    @click="adminSidebarOpen = false"
                    aria-label="Close admin menu"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" />
                    </svg>
                </button>

                <button
                    type="button"
                    class="hidden h-9 w-9 shrink-0 place-items-center rounded-lg bg-white/10 text-white transition hover:bg-white/15 lg:grid"
                    @click="adminSidebarCompact = !adminSidebarCompact"
                    aria-label="Toggle compact admin sidebar"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
                <div>
                    <p x-show="!adminSidebarCompact" class="px-3 text-[10px] font-black uppercase tracking-[0.18em] text-white/40">Control</p>
                    <div class="mt-2 space-y-1">
                        <a
                            href="{{ route('admin.dashboard') }}"
                            @class([
                                'group flex h-11 items-center gap-3 rounded-lg px-3 text-sm transition',
                                'bg-orange font-black text-white shadow-lg shadow-orange/20' => request()->routeIs('admin.dashboard'),
                                'font-bold text-white/75 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.dashboard'),
                            ])
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path d="M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-3H4v3Z" stroke-linejoin="round" />
                            </svg>
                            <span x-show="!adminSidebarCompact" x-transition class="truncate">Overview</span>
                        </a>
                        <a
                            href="{{ route('admin.businesses.index') }}"
                            @class([
                                'group flex h-11 items-center gap-3 rounded-lg px-3 text-sm transition',
                                'bg-orange font-black text-white shadow-lg shadow-orange/20' => request()->routeIs('admin.businesses.index') || request()->routeIs('admin.businesses.edit'),
                                'font-bold text-white/75 hover:bg-white/10 hover:text-white' => ! (request()->routeIs('admin.businesses.index') || request()->routeIs('admin.businesses.edit')),
                            ])
                        >
                            <svg class="h-5 w-5 shrink-0 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path d="M8 21V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v14M4 21V11a2 2 0 0 1 2-2h2m4 12v-4h4v4M3 21h18" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span x-show="!adminSidebarCompact" x-transition class="truncate">View Businesses</span>
                        </a>
                        <a
                            href="{{ route('admin.businesses.create') }}"
                            @class([
                                'group flex h-11 items-center gap-3 rounded-lg px-3 text-sm transition',
                                'bg-orange font-black text-white shadow-lg shadow-orange/20' => request()->routeIs('admin.businesses.create'),
                                'font-bold text-white/75 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.businesses.create'),
                            ])
                        >
                            <svg class="h-5 w-5 shrink-0 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                            </svg>
                            <span x-show="!adminSidebarCompact" x-transition class="truncate">Create Business</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p x-show="!adminSidebarCompact" class="px-3 text-[10px] font-black uppercase tracking-[0.18em] text-white/40">Platform</p>
                    <div class="mt-2 space-y-1">
                        @php
                            $futureNav = [
                                ['label' => 'Subscriptions', 'icon' => 'card'],
                                ['label' => 'Billing', 'icon' => 'receipt'],
                                ['label' => 'Reports', 'icon' => 'chart'],
                                ['label' => 'Settings', 'icon' => 'settings'],
                            ];
                        @endphp

                        @foreach($futureNav as $item)
                            <div class="flex h-11 items-center gap-3 rounded-lg px-3 text-sm font-bold text-white/40">
                                @if($item['icon'] === 'card')
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                        <path d="M4 7h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Zm-1 4h18" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                @elseif($item['icon'] === 'receipt')
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                        <path d="M6 3h12v18l-2-1-2 1-2-1-2 1-2-1-2 1V3Zm4 6h7M10 13h7M10 17h4" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                @elseif($item['icon'] === 'chart')
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                        <path d="M4 19V5M4 19h16M8 16v-5M12 16V8M16 16v-9" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                        <path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z" />
                                        <path d="M19.4 15a1.8 1.8 0 0 0 .36 1.98l.04.04a2 2 0 0 1-2.83 2.83l-.04-.04A1.8 1.8 0 0 0 15 19.45a1.8 1.8 0 0 0-1 .57 1.8 1.8 0 0 0-.5 1.25V21a2 2 0 0 1-4 0v-.06a1.8 1.8 0 0 0-.5-1.25 1.8 1.8 0 0 0-1-.57 1.8 1.8 0 0 0-1.98.36l-.04.04a2 2 0 0 1-2.83-2.83l.04-.04A1.8 1.8 0 0 0 4.55 15a1.8 1.8 0 0 0-.57-1 1.8 1.8 0 0 0-1.25-.5H3a2 2 0 0 1 0-4h.06a1.8 1.8 0 0 0 1.25-.5 1.8 1.8 0 0 0 .57-1 1.8 1.8 0 0 0-.36-1.98l-.04-.04a2 2 0 0 1 2.83-2.83l.04.04A1.8 1.8 0 0 0 9 4.55c.38-.1.72-.3 1-.57.33-.34.51-.78.5-1.25V3a2 2 0 0 1 4 0v.06c0 .47.17.91.5 1.25.28.27.62.47 1 .57a1.8 1.8 0 0 0 1.98-.36l.04-.04a2 2 0 0 1 2.83 2.83l-.04.04A1.8 1.8 0 0 0 19.45 9c.1.38.3.72.57 1 .34.33.78.51 1.25.5H21a2 2 0 0 1 0 4h-.06c-.47 0-.91.17-1.25.5-.27.28-.47.62-.57 1Z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                @endif
                                <span x-show="!adminSidebarCompact" x-transition class="truncate">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </nav>

            <div class="border-t border-white/10 p-3">
                <div class="space-y-3 rounded-lg bg-white/10 p-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-success"></span>
                        <span x-show="!adminSidebarCompact" x-transition class="truncate text-xs font-black text-white">Super admin active</span>
                    </div>
                    <p x-show="!adminSidebarCompact" x-transition class="mt-2 truncate text-[11px] font-semibold text-white/50">{{ auth()->user()?->email }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-white/10 text-xs font-black text-white transition hover:bg-white/15">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                                <path d="M10 17 15 12l-5-5M15 12H3M21 4v16" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span x-show="!adminSidebarCompact" x-transition>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-border bg-card/95 shadow-sm backdrop-blur">
                <div class="flex min-h-16 items-center gap-3 px-4 py-2 md:px-5">
                    <button
                        type="button"
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-border bg-card-tint text-ink transition hover:border-orange hover:text-orange lg:hidden"
                        @click="adminSidebarOpen = true"
                        aria-label="Open admin menu"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange">@yield('eyebrow', 'Super Admin')</p>
                        <div class="flex flex-col gap-1 lg:flex-row lg:items-end lg:gap-3">
                            <h1 class="truncate text-xl font-black tracking-tight text-ink md:text-2xl">@yield('page-title', 'Business Control Center')</h1>
                            @hasSection('page-subtitle')
                                <p class="truncate text-xs font-semibold text-muted">@yield('page-subtitle')</p>
                            @endif
                        </div>
                    </div>

                    <div class="hidden min-w-0 items-center gap-3 md:flex">
                        <div class="min-w-0 text-right">
                            <p class="truncate text-xs font-black text-ink">{{ auth()->user()?->name ?? 'Super Admin' }}</p>
                            <p class="truncate text-[11px] font-semibold text-muted">{{ auth()->user()?->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-navy px-3 text-xs font-black text-white transition hover:bg-navy/90">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                                    <path d="M10 17 15 12l-5-5M15 12H3M21 4v16" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-5 md:px-5">
                <div class="mx-auto max-w-[1600px] space-y-5">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
