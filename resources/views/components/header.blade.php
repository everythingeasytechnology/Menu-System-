<header class="flex h-20 items-center justify-between border-b border-border bg-card px-4 md:px-6 shadow-sm shrink-0">
    <!-- Left Section: Mobile Menu & Branch Location Selector -->
    <div class="flex items-center gap-4">
        <!-- Toggle Sidebar (Mobile) -->
        <button 
            @click="sidebarOpen = true" 
            class="rounded-xl p-2 text-muted hover:bg-card-tint hover:text-ink lg:hidden border border-border transition-colors cursor-pointer"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
 
        <!-- Location Dropdown switcher -->
        <div x-data="{ open: false }" class="relative">
            <button 
                @click="open = !open" 
                class="flex items-center gap-2.5 rounded-xl border border-transparent px-2 py-1.5 text-xs font-semibold text-ink hover:bg-card-tint transition-all focus:outline-none cursor-pointer"
            >
                <span class="text-orange text-sm">📍</span>
                <span class="font-bold text-ink truncate max-w-[110px] sm:max-w-none" x-text="activeBranch">KFC Connaught Place</span>
                <svg class="h-3 w-3 text-muted transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Options -->
            <div 
                x-show="open" 
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute left-0 mt-2 w-56 origin-top-left rounded-2xl bg-card border border-border p-1.5 shadow-xl z-30" 
                style="display: none;"
            >
                <button 
                    @click="activeBranch = 'KFC Connaught Place'; open = false" 
                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium hover:bg-card-tint transition-all"
                    :class="activeBranch === 'KFC Connaught Place' ? 'text-orange bg-orange/5 font-bold' : 'text-ink'"
                >
                    <span>📍</span>
                    <span>KFC Connaught Place</span>
                </button>
                <button 
                    @click="activeBranch = 'KFC Saket Mall'; open = false" 
                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium hover:bg-card-tint transition-all"
                    :class="activeBranch === 'KFC Saket Mall' ? 'text-orange bg-orange/5 font-bold' : 'text-ink'"
                >
                    <span>📍</span>
                    <span>KFC Saket Mall</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Center Section: Command Search Bar with Ctrl+K shortcut -->
    <div class="hidden md:flex max-w-lg w-full mx-6 relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
            <svg class="h-4 w-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input 
            type="text" 
            placeholder="Search orders, menu, customers..." 
            class="w-full rounded-xl border border-border bg-card-tint py-2.5 pl-10 pr-16 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
            @keyup.keydown.meta.k.window="$el.focus()"
            @keyup.keydown.ctrl.k.window="$el.focus()"
        >
        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5">
            <kbd class="hidden sm:inline-block rounded-lg border border-border bg-card px-1.5 py-0.5 text-[9px] font-bold text-muted shadow-xs">Ctrl + K</kbd>
        </div>
    </div>

    <!-- Right Section: Alerts, Date Box & User profile metadata -->
    <div class="flex items-center gap-4">
        <!-- Notifications Bell -->
        <div x-data="{ open: false }" class="relative">
            <button 
                @click="open = !open" 
                class="relative rounded-xl border border-border p-2.5 text-muted hover:bg-card-tint hover:text-ink transition-colors focus:outline-none cursor-pointer"
            >
                <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange"></span>
                </span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>

            <!-- Notification list overlay -->
            <div 
                x-show="open" 
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 mt-2 w-72 origin-top-right rounded-2xl bg-card border border-border p-4 shadow-xl z-30" 
                style="display: none;"
            >
                <div class="flex items-center justify-between pb-2.5 border-b border-border mb-2.5">
                    <span class="text-xs font-black text-ink">Notifications</span>
                    <button class="text-[10px] font-bold text-orange hover:underline">Clear all</button>
                </div>
                <div class="space-y-2 text-[11px] text-muted">
                    <div class="p-1.5 rounded-lg hover:bg-card-tint cursor-pointer">🍔 Order #KFC1256 received</div>
                    <div class="p-1.5 rounded-lg hover:bg-card-tint cursor-pointer">🍳 Order #KFC1252 is ready</div>
                </div>
            </div>
        </div>

        <!-- Date Picker Card -->
        <div class="hidden lg:flex items-center gap-2 rounded-xl border border-border bg-card-tint px-3 py-1.5">
            <span class="text-xs">📅</span>
            <div class="flex flex-col text-left">
                <span class="text-[10px] font-bold text-ink leading-tight">07 May 2025</span>
                <span class="text-[8px] text-muted font-bold tracking-wider uppercase mt-0.5">Wednesday</span>
            </div>
        </div>

        <!-- Profile Avatar Metadata selector -->
        <div x-data="{ open: false }" class="relative">
            <button 
                @click="open = !open" 
                class="flex items-center gap-2.5 rounded-xl border border-border p-1 pr-3 hover:bg-card-tint transition-all focus:outline-none cursor-pointer"
            >
                <!-- Initials Orange Icon -->
                <span class="h-8 w-8 rounded-lg bg-orange text-white text-xs font-black flex items-center justify-center shadow-sm">
                    AK
                </span>
                <div class="hidden xl:flex flex-col text-left">
                    <span class="text-[10px] font-extrabold text-ink leading-tight">Arjun Kumar</span>
                    <span class="text-[8px] text-muted font-bold uppercase tracking-wider mt-0.5">Manager</span>
                </div>
                <svg class="h-3 w-3 text-muted transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Options -->
            <div 
                x-show="open" 
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 mt-2 w-48 origin-top-right rounded-2xl bg-card border border-border p-1.5 shadow-xl z-30" 
                style="display: none;"
            >
                <a href="/settings" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-semibold text-ink hover:bg-card-tint transition-all">Settings</a>
                <button class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-semibold text-danger hover:bg-danger/5">Logout</button>
            </div>
        </div>
    </div>
</header>
