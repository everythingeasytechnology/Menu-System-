@php
    $activeOrderCount = 0;

    try {
        if (
            auth()->check()
            && auth()->user()->business_id
            && \Illuminate\Support\Facades\Schema::hasTable('orders')
        ) {
            $activeOrderCount = \App\Models\Order::where('business_id', auth()->user()->business_id)
                ->live()
                ->count();
        }
    } catch (\Throwable $e) {
        $activeOrderCount = 0;
    }

    $menuItems = [
        ['label' => 'Dashboard', 'route' => '/', 'exact' => true, 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 5.75a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a1.5 1.5 0 0 1-1.5-1.5v-3Zm8.5 0a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a1.5 1.5 0 0 1-1.5-1.5v-3Zm-8.5 8.5a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a1.5 1.5 0 0 1-1.5-1.5v-3Zm8.5 0a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a1.5 1.5 0 0 1-1.5-1.5v-3Z" /></svg>'],
        ['label' => 'Live Orders', 'route' => 'orders', 'exact' => true, 'badge' => $activeOrderCount, 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 8.75h10.5l1 10.5H5.75l1-10.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.75v-1a3 3 0 0 1 6 0v1" /></svg>'],
        ['label' => 'Kitchen Display', 'route' => 'kitchen-display', 'exact' => true, 'badge' => $activeOrderCount, 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75h14.5v9.5H4.75v-9.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 19.25h7.5M12 16.25v3M8.25 10h3.5M8.25 13h7.5" /></svg>'],
        ['label' => 'Service Points', 'route' => 'service-points', 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 5.75A2 2 0 0 1 6.75 4h10.5a2 2 0 0 1 2 1.75v11.5A2 2 0 0 1 17.25 19H6.75a2 2 0 0 1-2-1.75V5.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 8h8M8 12h5M8 16h8" /></svg>'],
        ['label' => 'Categories', 'route' => 'categories', 'group' => 'MENU MANAGEMENT', 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8.25h14M6.75 5.25h10.5A1.75 1.75 0 0 1 19 7v11.25H5V7a1.75 1.75 0 0 1 1.75-1.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 18.25v-10m9 10v-10" /></svg>'],
        ['label' => 'Menu Items', 'route' => 'menu', 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M5.5 4.75h13v14.5h-13V4.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h4.5" /></svg>'],
        ['label' => 'Coupons', 'route' => 'coupons', 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 8.25A2.25 2.25 0 0 1 7 6h10a2.25 2.25 0 0 1 2.25 2.25v2a2 2 0 0 0 0 3.5v2A2.25 2.25 0 0 1 17 18H7a2.25 2.25 0 0 1-2.25-2.25v-2a2 2 0 0 0 0-3.5v-2Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14.5 8.5v7" /></svg>'],
        ['label' => 'Staff Members', 'route' => 'staff', 'group' => 'STAFF MANAGEMENT', 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M8.75 11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM15.75 10.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5ZM4.5 19.25a4.25 4.25 0 0 1 8.5 0M13.75 18.25a3.25 3.25 0 0 1 5.75-2" /></svg>'],
        ['label' => 'Reports Dashboard', 'route' => 'reports', 'group' => 'REPORTS', 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 18.75v-5.5h3.5v5.5h-3.5Zm5.5 0v-9.5h3.5v9.5h-3.5Zm5.5 0v-13.5h3.5v13.5h-3.5Z" /></svg>'],
        ['label' => 'Order History', 'route' => 'orders/history', 'exact' => true, 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M5.75 6.75h12.5v12.5H5.75V6.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 10h7M8.5 13h7M8.5 16h4" /></svg>'],
        ['label' => 'Settings', 'route' => 'settings', 'group' => 'SETTINGS', 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.75a3.25 3.25 0 1 1 0 6.5 3.25 3.25 0 0 1 0-6.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 12a6.5 6.5 0 0 0-.1-1.1l2.05-1.6-2-3.45-2.4.95a6.7 6.7 0 0 0-1.9-1.1L13.75 3h-3.5l-.4 2.7a6.7 6.7 0 0 0-1.9 1.1l-2.4-.95-2 3.45 2.05 1.6A6.5 6.5 0 0 0 5.5 12c0 .38.03.75.1 1.1l-2.05 1.6 2 3.45 2.4-.95c.57.47 1.2.84 1.9 1.1l.4 2.7h3.5l.4-2.7c.7-.26 1.33-.63 1.9-1.1l2.4.95 2-3.45-2.05-1.6c.07-.35.1-.72.1-1.1Z" /></svg>'],
    ];

    $business = null;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('business_settings')) {
            $business = \App\Models\BusinessSetting::first();
        }
    } catch (\Throwable $e) {
        // Keep the sidebar available during early database setup.
    }

    $brandName = $business?->brand_name ?? 'SmartMenu';
    $logoUrl = $business?->logo_path ? asset('storage/' . $business->logo_path) : null;
@endphp

<div
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-40 bg-navy-deep/80 lg:hidden"
    @click="sidebarOpen = false"
></div>

<aside
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCompact ? 'lg:w-56' : 'lg:w-64'
    ]"
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-white/5 bg-[#121c28] text-white shadow-2xl shadow-navy/20 transition-all duration-300 ease-in-out lg:static lg:z-auto"
>
    <div
        class="flex h-20 items-center justify-between border-b border-white/5 px-4 transition-all duration-300"
        :class="sidebarCompact ? 'lg:px-3' : 'lg:px-4'"
    >
        <a href="/" class="flex min-w-0 items-center gap-3">
            @if($logoUrl)
                <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-white p-1 shadow-lg">
                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-full w-full object-contain">
                </span>
            @else
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange text-white shadow-lg shadow-orange/30">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 4.5v15M6 4.5v4.75a2 2 0 0 0 4 0V4.5M16.5 4.5v15M16.5 4.5c2 1 3 2.65 3 5.25 0 2.2-1.1 3.75-3 4" />
                    </svg>
                </span>
            @endif
            <span class="min-w-0">
                <span class="block truncate text-base font-black leading-5 tracking-tight">
                    {{ $brandName === 'SmartMenu' ? 'Smart' : $brandName }}@if($brandName === 'SmartMenu')<span class="text-orange">Menu</span>@endif
                </span>
                <span class="mt-0.5 block text-[11px] font-semibold text-slate-300">System</span>
            </span>
        </a>

        <button
            type="button"
            @click="sidebarOpen = false"
            class="rounded-lg p-2 text-slate-300 transition hover:bg-white/5 hover:text-white lg:hidden"
            aria-label="Close navigation"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>

        <button
            type="button"
            @click="sidebarCompact = !sidebarCompact"
            class="hidden rounded-lg p-2 text-slate-300 transition hover:bg-white/5 hover:text-white lg:inline-flex"
            :aria-label="sidebarCompact ? 'Regular sidebar' : 'Compact sidebar'"
            :title="sidebarCompact ? 'Regular sidebar' : 'Compact sidebar'"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M5 12h14M5 17h14" />
            </svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
        @php $currentGroup = null; @endphp

        <nav class="space-y-1">
            @foreach($menuItems as $item)
                @if(isset($item['group']) && $item['group'] !== $currentGroup)
                    @php $currentGroup = $item['group']; @endphp
                    <div class="px-3 pb-1.5 pt-4 text-[9px] font-black uppercase tracking-[0.16em] text-slate-500">
                        {{ $currentGroup }}
                    </div>
                @endif

                @php
                    $isHome = $item['route'] === '/';
                    $isActive = $isHome
                        ? request()->is('/')
                        : (($item['exact'] ?? false)
                            ? request()->is($item['route'])
                            : request()->is($item['route']) || request()->is($item['route'] . '/*'));
                    $linkHref = $isHome ? '/' : '/' . $item['route'];
                @endphp

                <a
                    href="{{ $linkHref }}"
                    class="group flex items-center justify-between rounded-lg px-3 py-2.5 text-xs font-bold transition {{ $isActive ? 'bg-orange text-white shadow-lg shadow-orange/20' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                    :class="sidebarCompact ? 'lg:px-2.5' : ''"
                    title="{{ $item['label'] }}"
                >
                    <span class="flex min-w-0 items-center gap-2.5">
                        <span class="{{ $isActive ? 'text-white' : 'text-slate-400 group-hover:text-orange' }}">
                            {!! $item['icon'] !!}
                        </span>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </span>

                    @if(($item['badge'] ?? 0) > 0)
                        <span class="ml-2 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-orange px-1.5 text-[10px] font-black text-white {{ $isActive ? 'ring-2 ring-white/25' : '' }}">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <div class="p-3">
        <div class="overflow-hidden rounded-xl border border-white/5 bg-white/[0.04] p-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="relative flex h-3 w-3 shrink-0">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-60"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-success"></span>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-xs font-black text-white">System Status</span>
                        <span class="block truncate text-[11px] font-semibold text-slate-400">Operational</span>
                    </span>
                </div>
                <span class="rounded-lg bg-white/10 px-2 py-1 text-[10px] font-black text-slate-300">v1.2.0</span>
            </div>
            <div class="mt-3 h-6 rounded-lg bg-gradient-to-r from-transparent via-orange/20 to-orange/45"></div>
        </div>
    </div>
</aside>
