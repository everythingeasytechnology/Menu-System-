@php
    $menuItems = [
        ['label' => 'Dashboard', 'route' => '/', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/></svg>'],
        ['label' => 'Live Orders', 'route' => 'orders', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>', 'badge' => 12],
        ['label' => 'Dine-in Orders', 'route' => 'service-points', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'],
        
        ['label' => 'Menu Items', 'route' => 'menu', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>', 'group' => 'MENU MANAGEMENT'],
        ['label' => 'Categories', 'route' => 'categories', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'],
        
        ['label' => 'Staff Members', 'route' => 'staff', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', 'group' => 'STAFF MANAGEMENT'],
        ['label' => 'Reports Dashboard', 'route' => 'reports', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>', 'group' => 'REPORTS'],
    ];
@endphp

<!-- Mobile Sidebar Backdrop (locks layout view when open) -->
<div 
    x-show="sidebarOpen" 
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-40 bg-navy-deep/80 lg:hidden" 
    @click="sidebarOpen = false">
</div>

<!-- Sidebar Main Panel (Premium Dark Theme) -->
<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-navy-deep border-r border-navy/20 transition-transform duration-300 ease-in-out lg:static lg:z-auto">
    
    <!-- Branding Header -->
    <div class="flex h-20 items-center justify-between px-6 border-b border-navy/40">
        <a href="/" class="flex items-center gap-3 group">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange text-white shadow-lg shadow-orange/30 group-hover:scale-105 transition-all">
                <!-- Lighting bolt icon -->
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-base font-bold tracking-tight text-white leading-tight">EverythingEasy</span>
                <span class="text-[11px] font-semibold tracking-wider text-orange uppercase">ServiceOS</span>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="rounded-lg p-1.5 text-muted hover:text-white lg:hidden cursor-pointer">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation List (Scrollable) -->
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6">
        @php
            $currentGroup = null;
        @endphp

        <nav class="space-y-1">
            @foreach($menuItems as $item)
                @if(isset($item['group']) && $item['group'] !== $currentGroup)
                    @php $currentGroup = $item['group']; @endphp
                    <div class="pt-6 pb-2 px-3 text-[10px] font-bold tracking-wider text-muted uppercase">
                        {{ $currentGroup }}
                    </div>
                @endif

                @php
                    $isHome = $item['route'] === '/';
                    $isActive = $isHome 
                        ? request()->is('/') 
                        : request()->is($item['route']) || request()->is($item['route'] . '/*');
                    
                    $linkHref = $isHome ? '/' : '/' . $item['route'];
                @endphp

                <a 
                    href="{{ $linkHref }}" 
                    class="flex items-center justify-between px-3.5 py-3 text-xs font-semibold rounded-xl transition-all duration-200 group {{ $isActive ? 'bg-orange text-white shadow-md shadow-orange/20 font-bold' : 'text-slate-400 hover:text-white hover:bg-navy/40' }}">
                    <div class="flex items-center gap-3.5">
                        <span class="{{ $isActive ? 'text-white' : 'text-slate-400 group-hover:text-orange transition-colors' }}">
                            {!! $item['icon'] !!}
                        </span>
                        <span>{{ $item['label'] }}</span>
                    </div>
                    
                    <!-- Optional Badge -->
                    @if(isset($item['badge']))
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-orange text-[9px] font-bold text-white" x-text="{{ $item['badge'] }}"></span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <!-- Footer System Switcher / SaaS Status -->
    <div class="border-t border-navy/40 p-4 bg-navy-deep/40">
        <div class="flex items-center justify-between rounded-xl bg-navy/30 p-3 border border-navy/20">
            <div class="flex items-center gap-2.5">
                <div class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-success"></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-white">System Status</span>
                    <span class="text-[10px] text-muted">All Nodes Operational</span>
                </div>
            </div>
            <span class="inline-flex items-center rounded-md bg-teal/20 px-2 py-0.5 text-[9px] font-bold text-teal">v1.2.0</span>
        </div>
    </div>
</aside>
