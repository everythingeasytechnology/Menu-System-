@props([
    'type' => 'revenue', // 'revenue' or 'orders'
    'height' => 240,
])

<div 
    x-data="{ 
        activePoint: null, 
        tooltipX: 0, 
        tooltipY: 0, 
        tooltipLabel: '', 
        tooltipValue: '',
        points: [
            { x: 30, y: 150, label: 'Mon', val: '$12,420', ord: '143 Orders' },
            { x: 100, y: 120, label: 'Tue', val: '$15,850', ord: '167 Orders' },
            { x: 170, y: 140, label: 'Wed', val: '$14,200', ord: '150 Orders' },
            { x: 240, y: 80,  label: 'Thu', val: '$24,900', ord: '232 Orders' },
            { x: 310, y: 100, label: 'Fri', val: '$21,400', ord: '210 Orders' },
            { x: 380, y: 40,  label: 'Sat', val: '$32,600', ord: '315 Orders' },
            { x: 450, y: 60,  label: 'Sun', val: '$28,150', ord: '280 Orders' }
        ]
    }"
    class="relative w-full"
    style="height: {{ $height }}px;"
>
    <!-- Background grid lines -->
    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-40">
        <div class="border-b border-dashed border-border w-full h-0"></div>
        <div class="border-b border-dashed border-border w-full h-0"></div>
        <div class="border-b border-dashed border-border w-full h-0"></div>
        <div class="border-b border-dashed border-border w-full h-0"></div>
        <div class="w-full h-0"></div> <!-- Baseline -->
    </div>

    <!-- Chart Canvas -->
    <svg class="w-full h-full overflow-visible" viewBox="0 0 480 180" preserveAspectRatio="none">
        <defs>
            <!-- Revenue Teal Gradient -->
            <linearGradient id="tealGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="var(--color-teal)" stop-opacity="0.25" />
                <stop offset="100%" stop-color="var(--color-teal)" stop-opacity="0.0" />
            </linearGradient>
            
            <!-- Orders Orange Gradient -->
            <linearGradient id="orangeGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="var(--color-orange)" stop-opacity="0.25" />
                <stop offset="100%" stop-color="var(--color-orange)" stop-opacity="0.0" />
            </linearGradient>
        </defs>

        @if($type === 'revenue')
            <!-- Area under line -->
            <path 
                d="M 30 180 L 30 150 Q 65 135 100 120 Q 135 130 170 140 Q 205 110 240 80 Q 275 90 310 100 Q 345 70 380 40 Q 415 50 450 60 L 450 180 Z" 
                fill="url(#tealGrad)"
            />
            
            <!-- Smooth line -->
            <path 
                d="M 30 150 Q 65 135 100 120 Q 135 130 170 140 Q 205 110 240 80 Q 275 90 310 100 Q 345 70 380 40 Q 415 50 450 60" 
                fill="none" 
                stroke="var(--color-teal)" 
                stroke-width="3" 
                stroke-linecap="round"
            />
        @else
            <!-- Area under line -->
            <path 
                d="M 30 180 L 30 140 Q 65 150 100 155 Q 135 125 170 110 Q 205 90 240 70 Q 275 105 310 120 Q 345 80 380 50 Q 415 65 450 80 L 450 180 Z" 
                fill="url(#orangeGrad)"
            />
            
            <!-- Smooth line -->
            <path 
                d="M 30 140 Q 65 150 100 155 Q 135 125 170 110 Q 205 90 240 70 Q 275 105 310 120 Q 345 80 380 50 Q 415 65 450 80" 
                fill="none" 
                stroke="var(--color-orange)" 
                stroke-width="3" 
                stroke-linecap="round"
            />
        @endif

        <!-- Interactive circles -->
        <g class="cursor-pointer">
            <template x-for="(pt, idx) in points" :key="idx">
                <g>
                    <!-- Trigger area -->
                    <circle 
                        :cx="pt.x" 
                        :cy="pt.y" 
                        r="12" 
                        fill="transparent" 
                        @mouseenter="
                            activePoint = idx; 
                            tooltipX = pt.x; 
                            tooltipY = pt.y; 
                            tooltipLabel = pt.label; 
                            tooltipValue = type === 'revenue' ? pt.val : pt.ord;
                        "
                        @mouseleave="activePoint = null"
                    />
                    <!-- Visual dot -->
                    <circle 
                        :cx="pt.x" 
                        :cy="pt.y" 
                        :r="activePoint === idx ? '6' : '4'" 
                        :fill="type === 'revenue' ? 'var(--color-teal)' : 'var(--color-orange)'" 
                        :stroke="activePoint === idx ? '#FFFFFF' : 'transparent'"
                        stroke-width="2"
                        class="transition-all duration-150 pointer-events-none"
                    />
                </g>
            </template>
        </g>
    </svg>

    <!-- Overlay Tooltip -->
    <div 
        x-show="activePoint !== null"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="absolute z-20 pointer-events-none rounded-xl bg-navy-deep text-white px-3 py-2 text-xs shadow-xl border border-navy/40"
        :style="`left: calc(${(tooltipX / 480) * 100}% - 45px); top: calc(${(tooltipY / 180) * 100}% - 55px);`"
        style="display: none;"
    >
        <span x-text="tooltipLabel" class="block font-medium text-slate-400 text-[10px] uppercase"></span>
        <span x-text="tooltipValue" class="block font-bold text-white"></span>
    </div>

    <!-- X-Axis Labels -->
    <div class="absolute bottom-[-22px] inset-x-0 flex justify-between px-3.5 text-[11px] font-bold text-muted">
        <span>Mon</span>
        <span>Tue</span>
        <span>Wed</span>
        <span>Thu</span>
        <span>Fri</span>
        <span>Sat</span>
        <span>Sun</span>
    </div>
</div>
