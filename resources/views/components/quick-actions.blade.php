<div 
    x-show="quickActionsOpen" 
    @keydown.escape.window="quickActionsOpen = false"
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;"
    role="dialog" 
    aria-modal="true"
>
    <!-- Modal Backdrop -->
    <div 
        x-show="quickActionsOpen"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-navy-deep/80 transition-opacity" 
        @click="quickActionsOpen = false"
    ></div>

    <!-- Modal Content Alignment Frame -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div 
            x-show="quickActionsOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-card bg-card text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-border"
        >
            <!-- Header -->
            <div class="border-b border-border bg-card-tint px-6 py-4.5 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-bold text-ink leading-tight">ServiceOS Command Palette</h3>
                    <p class="text-xs text-muted mt-0.5">Quickly trigger events in your active branch</p>
                </div>
                <button 
                    @click="quickActionsOpen = false" 
                    class="rounded-xl border border-border bg-card p-1.5 text-muted hover:text-ink hover:bg-card-tint transition-all cursor-pointer"
                >
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Spotlight List -->
            <div class="p-4 space-y-2">
                <!-- Action Item 1 -->
                <button 
                    @click="quickActionsOpen = false; alert('Demo Event: New Order initialized')" 
                    class="flex w-full items-center gap-4 rounded-xl p-3 text-left hover:bg-orange/5 border border-transparent hover:border-orange/15 transition-all group"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange/10 text-orange group-hover:scale-105 transition-transform">
                        🛍️
                    </span>
                    <div class="flex-1">
                        <span class="block text-xs font-bold text-ink group-hover:text-orange transition-colors">Create New Dine-in Order</span>
                        <span class="block text-[11px] text-muted mt-0.5">Initialize ticket checkout process for tables/rooms</span>
                    </div>
                    <kbd class="hidden sm:inline-block rounded-lg border border-border bg-card-tint px-1.5 py-0.5 text-[9px] font-semibold text-muted">⌥ N</kbd>
                </button>

                <!-- Action Item 2 -->
                <button 
                    @click="quickActionsOpen = false; alert('Demo Event: Staff assignment drawer opened')" 
                    class="flex w-full items-center gap-4 rounded-xl p-3 text-left hover:bg-teal/5 border border-transparent hover:border-teal/15 transition-all group"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal/10 text-teal group-hover:scale-105 transition-transform">
                        🤵
                    </span>
                    <div class="flex-1">
                        <span class="block text-xs font-bold text-ink group-hover:text-teal transition-colors">Assign Waiter to Table</span>
                        <span class="block text-[11px] text-muted mt-0.5">Assign servers to live client service points</span>
                    </div>
                    <kbd class="hidden sm:inline-block rounded-lg border border-border bg-card-tint px-1.5 py-0.5 text-[9px] font-semibold text-muted">⌥ W</kbd>
                </button>

                <!-- Action Item 3 -->
                <button 
                    @click="quickActionsOpen = false; alert('Demo Event: Billing payout drawer opened')" 
                    class="flex w-full items-center gap-4 rounded-xl p-3 text-left hover:bg-navy/5 border border-transparent hover:border-navy/15 transition-all group"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-navy/10 text-navy group-hover:scale-105 transition-transform">
                        💳
                    </span>
                    <div class="flex-1">
                        <span class="block text-xs font-bold text-ink group-hover:text-navy transition-colors">Settle Bill & Process Payout</span>
                        <span class="block text-[11px] text-muted mt-0.5">Checkout active tables or rooms and process cash/card/stripe</span>
                    </div>
                    <kbd class="hidden sm:inline-block rounded-lg border border-border bg-card-tint px-1.5 py-0.5 text-[9px] font-semibold text-muted">⌥ P</kbd>
                </button>

                <!-- Action Item 4 -->
                <button 
                    @click="quickActionsOpen = false; alert('Demo Event: KDS alarm alert updated')" 
                    class="flex w-full items-center gap-4 rounded-xl p-3 text-left hover:bg-danger/5 border border-transparent hover:border-danger/15 transition-all group"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger/10 text-danger group-hover:scale-105 transition-transform">
                        🚨
                    </span>
                    <div class="flex-1">
                        <span class="block text-xs font-bold text-ink group-hover:text-danger transition-colors">Broadcast Kitchen Alert</span>
                        <span class="block text-[11px] text-muted mt-0.5">Send a high-priority warning to the KDS screens</span>
                    </div>
                    <kbd class="hidden sm:inline-block rounded-lg border border-border bg-card-tint px-1.5 py-0.5 text-[9px] font-semibold text-muted">⌥ K</kbd>
                </button>
            </div>
            
            <!-- Footer -->
            <div class="border-t border-border bg-card-tint px-6 py-3.5 flex items-center justify-between text-[11px] text-muted font-medium">
                <span>Use arrows to navigate, <kbd class="font-sans font-bold">enter</kbd> to select</span>
                <span>ESC to close</span>
            </div>
        </div>
    </div>
</div>
