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

<header class="flex h-16 shrink-0 items-center justify-between border-b border-border bg-card px-4 shadow-sm md:px-8">
    <div class="flex min-w-0 items-center gap-4">
        <button
            type="button"
            @click="sidebarOpen = true"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-card text-muted transition hover:bg-card-tint hover:text-ink lg:hidden"
            aria-label="Open navigation"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M5 12h14M5 17h14" />
            </svg>
        </button>

    </div>

    <div class="flex shrink-0 items-center gap-3">
        <div
            x-data="notificationBell('{{ csrf_token() }}')"
            x-init="load(); poll()"
            class="relative"
        >
            <button
                type="button"
                @click="open = !open; if (open) load();"
                class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-card text-muted shadow-sm transition hover:bg-card-tint hover:text-ink"
                aria-label="Notifications"
            >
                <span x-show="unreadCount > 0" class="absolute right-1.5 top-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-orange px-1 text-[9px] font-black leading-none text-white" x-text="unreadCount > 9 ? '9+' : unreadCount" style="display: none;"></span>
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
                class="absolute right-0 z-30 mt-2 w-80 origin-top-right rounded-xl border border-border bg-card p-4 shadow-xl"
                style="display: none;"
            >
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm font-black text-ink">Notifications</span>
                    <button type="button" @click="markAllRead()" x-show="unreadCount > 0" class="text-xs font-bold text-orange">Clear all</button>
                </div>
                <div class="mt-3 max-h-80 space-y-2 overflow-y-auto text-xs font-semibold text-muted">
                    <template x-if="!loading && notifications.length === 0">
                        <div class="rounded-lg bg-card-tint px-3 py-4 text-center">No notifications yet.</div>
                    </template>
                    <template x-for="notification in notifications" :key="notification.id">
                        <button
                            type="button"
                            @click="markRead(notification)"
                            class="block w-full rounded-lg px-3 py-2 text-left transition hover:bg-card-tint"
                            :class="notification.read ? 'bg-card-tint/50' : 'bg-orange/5'"
                        >
                            <span class="flex items-start justify-between gap-2">
                                <span class="font-bold text-ink" x-text="notification.title"></span>
                                <span x-show="!notification.read" class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-orange"></span>
                            </span>
                            <span class="mt-0.5 block text-muted" x-text="notification.message"></span>
                            <span class="mt-1 block text-[10px] uppercase tracking-wider text-muted/70" x-text="notification.time"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="hidden h-10 items-center gap-2.5 rounded-xl border border-border bg-card px-3 shadow-sm lg:flex">
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
                class="flex h-10 items-center gap-2.5 rounded-xl border border-border bg-card py-1 pl-1 pr-3 shadow-sm transition hover:bg-card-tint"
            >
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange text-xs font-black text-white shadow-sm">
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

<script>
    function notificationBell(csrf) {
        return {
            open: false,
            loading: false,
            notifications: [],
            unreadCount: 0,
            pollTimer: null,
            hasLoaded: false,

            shouldPlaySound(nextNotifications, previousNotifications = this.notifications) {
                const previousIds = new Set(previousNotifications.map((notification) => notification.id));
                const newUnread = (nextNotifications || []).filter((notification) => !notification.read && !previousIds.has(notification.id));

                if (newUnread.length === 0) {
                    return true;
                }

                return !(window.orderFeedSoundActive && newUnread.every((notification) => notification.type === 'order_created'));
            },

            async load() {
                this.loading = true;
                try {
                    const response = await fetch('{{ route('notifications.index') }}', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });
                    const payload = await response.json();
                    if (payload.success) {
                        const previousUnreadCount = this.unreadCount;
                        const nextNotifications = payload.notifications;
                        const shouldPlaySound = this.shouldPlaySound(nextNotifications);
                        this.notifications = nextNotifications;
                        this.unreadCount = payload.unread_count;

                        if (this.hasLoaded && this.unreadCount > previousUnreadCount && shouldPlaySound) {
                            window.notificationSound?.play();
                        }

                        this.hasLoaded = true;
                    }
                } catch (e) {
                    // silent fail, keep previous state
                } finally {
                    this.loading = false;
                }
            },

            poll() {
                this.pollTimer = setInterval(() => this.load(), 30000);
            },

            async markRead(notification) {
                if (notification.read) {
                    return;
                }

                notification.read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);

                await fetch(`/notifications/${notification.id}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    credentials: 'same-origin',
                });
            },

            async markAllRead() {
                this.notifications.forEach((notification) => { notification.read = true; });
                this.unreadCount = 0;

                await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    credentials: 'same-origin',
                });
            },
        };
    }
</script>
