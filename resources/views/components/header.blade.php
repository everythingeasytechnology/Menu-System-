@php
    $profileUser = auth()->user();
    $profileName = $profileUser?->name ?? 'Business Owner';
    $profileRole = $profileUser?->role ? str_replace('_', ' ', $profileUser->role) : 'Owner';
    $profileInitials = collect(explode(' ', $profileName))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');
    $today = now('Asia/Kolkata');
@endphp

<header class="flex h-20 shrink-0 items-center justify-between border-b border-border bg-card px-4 shadow-sm md:px-8">
    <div class="flex min-w-0 items-center gap-4">
        <button
            type="button"
            @click="sidebarOpen = true"
            class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-border bg-card text-muted transition hover:bg-card-tint hover:text-ink lg:hidden"
            aria-label="Open navigation"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M5 12h14M5 17h14" />
            </svg>
        </button>

        <div x-data="{ open: false }" class="relative hidden sm:block">
            <button
                type="button"
                @click="open = !open"
                class="flex h-12 min-w-[190px] items-center justify-between gap-3 rounded-xl border border-border bg-card px-4 text-sm font-extrabold text-ink shadow-sm transition hover:bg-card-tint"
            >
                <span class="flex min-w-0 items-center gap-3">
                    <span class="text-orange">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.75a6.75 6.75 0 0 0-6.75 6.75c0 4.6 5.54 10.7 5.78 10.96a1.32 1.32 0 0 0 1.94 0c.24-.26 5.78-6.36 5.78-10.96A6.75 6.75 0 0 0 12 2.75Zm0 9.25a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z" />
                        </svg>
                    </span>
                    <span class="truncate" x-text="activeBranch">Restaurant Branch</span>
                </span>
                <svg class="h-4 w-4 shrink-0 text-muted transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
            </button>

            <div
                x-show="open"
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute left-0 z-30 mt-2 w-60 origin-top-left rounded-xl border border-border bg-card p-1.5 shadow-xl"
                style="display: none;"
            >
                @foreach(['Restaurant Branch', 'Main Dining', 'Takeaway Counter'] as $branch)
                    <button
                        type="button"
                        @click="activeBranch = '{{ $branch }}'; open = false"
                        class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs font-bold transition hover:bg-card-tint"
                        :class="activeBranch === '{{ $branch }}' ? 'text-orange bg-orange/5' : 'text-ink'"
                    >
                        {{ $branch }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mx-4 hidden w-full max-w-xl md:block">
        <label class="relative block">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-muted">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
            </span>
            <input
                type="search"
                placeholder="Search orders, menu, customers..."
                class="h-12 w-full rounded-xl border border-border bg-card px-4 pl-12 text-sm font-semibold text-ink outline-none transition placeholder:text-muted focus:border-orange focus:ring-4 focus:ring-orange/10"
            >
        </label>
    </div>

    <div class="flex shrink-0 items-center gap-3">
        <div x-data="{ open: false }" class="relative">
            <button
                type="button"
                @click="open = !open"
                class="relative inline-flex h-12 w-12 items-center justify-center rounded-xl border border-border bg-card text-muted shadow-sm transition hover:bg-card-tint hover:text-ink"
                aria-label="Notifications"
            >
                <span class="absolute right-2 top-2 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-orange px-1 text-[9px] font-black leading-none text-white">3</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25h-7.5m7.5 0H19l-1.25-2.5V11a5.75 5.75 0 0 0-11.5 0v3.75L5 17.25h3.25m7.5 0a3.75 3.75 0 0 1-7.5 0" />
                </svg>
            </button>

            <div
                x-show="open"
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 z-30 mt-2 w-72 origin-top-right rounded-xl border border-border bg-card p-4 shadow-xl"
                style="display: none;"
            >
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm font-black text-ink">Notifications</span>
                    <button type="button" class="text-xs font-bold text-orange">Clear all</button>
                </div>
                <div class="mt-3 space-y-2 text-xs font-semibold text-muted">
                    <div class="rounded-lg bg-card-tint px-3 py-2">Order received from Table 7</div>
                    <div class="rounded-lg bg-card-tint px-3 py-2">Kitchen marked one item ready</div>
                    <div class="rounded-lg bg-card-tint px-3 py-2">Cash payment needs review</div>
                </div>
            </div>
        </div>

        <div class="hidden h-12 items-center gap-3 rounded-xl border border-border bg-card px-4 shadow-sm lg:flex">
            <span class="text-muted">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75v3M17 3.75v3M4.75 9.25h14.5M6.75 5.25h10.5a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6.75a2 2 0 0 1-2-2v-10a2 2 0 0 1 2-2Z" />
                </svg>
            </span>
            <span class="text-left">
                <span class="block text-xs font-black leading-tight text-ink">{{ $today->format('d M Y') }}</span>
                <span class="mt-0.5 block text-[10px] font-black uppercase tracking-wider text-muted">{{ $today->format('l') }}</span>
            </span>
        </div>

        <div x-data="{ open: false }" class="relative">
            <button
                type="button"
                @click="open = !open"
                class="flex h-12 items-center gap-3 rounded-xl border border-border bg-card py-1 pl-1 pr-3 shadow-sm transition hover:bg-card-tint"
            >
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange text-sm font-black text-white shadow-sm">
                    {{ strtoupper($profileInitials ?: 'BO') }}
                </span>
                <span class="hidden min-w-0 text-left xl:block">
                    <span class="block max-w-28 truncate text-xs font-black leading-tight text-ink">{{ $profileName }}</span>
                    <span class="mt-0.5 block text-[10px] font-black uppercase tracking-wider text-muted">{{ $profileRole }}</span>
                </span>
                <svg class="h-4 w-4 text-muted transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
            </button>

            <div
                x-show="open"
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 z-30 mt-2 w-48 origin-top-right rounded-xl border border-border bg-card p-1.5 shadow-xl"
                style="display: none;"
            >
                <a href="/settings" class="block rounded-lg px-3 py-2 text-xs font-bold text-ink transition hover:bg-card-tint">Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-bold text-danger transition hover:bg-danger/5">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>
