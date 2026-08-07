@props([
    'total' => 100000,
    'from' => 1,
    'to' => 15,
])

<div class="overflow-hidden bg-card border border-border rounded-card shadow-sm flex flex-col transition-all duration-300">
    <!-- Optional Filter Header Bar -->
    @if(isset($filters))
        <div class="border-b border-border bg-card p-4">
            {{ $filters }}
        </div>
    @endif

    <!-- Data Table Container -->
    <div class="overflow-x-auto scrollbar-thin">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border bg-card-tint text-xs font-bold uppercase tracking-wider text-muted select-none">
                    {{ $headerSlot }}
                </tr>
            </thead>
            
            <!-- Body slot -->
            <tbody class="divide-y divide-border text-sm text-ink leading-relaxed">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    <!-- Paginator Footer -->
    <div class="flex flex-col sm:flex-row items-center justify-between border-t border-border bg-card-tint px-6 py-4 gap-4">
        <div class="text-xs font-semibold text-muted">
            Showing <span class="text-ink font-bold">{{ $from }}</span> to <span class="text-ink font-bold">{{ $to }}</span> of <span class="text-ink font-bold">{{ number_format($total) }}</span> records
        </div>
        
        <div class="flex items-center gap-1.5">
            <button class="rounded-xl border border-border bg-card px-3.5 py-2.5 text-xs font-bold text-ink hover:bg-card-tint active:scale-[0.97] transition-all cursor-pointer">
                Previous
            </button>
            <button class="rounded-xl border border-border bg-card-tint px-3.5 py-2.5 text-xs font-bold text-ink hover:bg-card-tint active:scale-[0.97] transition-all cursor-pointer">
                1
            </button>
            <button class="rounded-xl border border-border bg-card px-3.5 py-2.5 text-xs font-bold text-white bg-orange shadow-md shadow-orange/15 hover:bg-orange/90 active:scale-[0.97] transition-all cursor-pointer">
                2
            </button>
            <button class="rounded-xl border border-border bg-card px-3.5 py-2.5 text-xs font-bold text-ink hover:bg-card-tint active:scale-[0.97] transition-all cursor-pointer">
                3
            </button>
            <span class="text-muted px-1.5 text-xs font-semibold select-none">...</span>
            <button class="rounded-xl border border-border bg-card px-3.5 py-2.5 text-xs font-bold text-ink hover:bg-card-tint active:scale-[0.97] transition-all cursor-pointer">
                Next
            </button>
        </div>
    </div>
</div>
