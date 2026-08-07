@extends('layouts.app')

@section('title', 'Menu Categories')

@section('content')
<div class="space-y-8" x-data="{
    categories: [
        { id: 1, name: 'Starters', code: 'STR', count: 18, active: true },
        { id: 2, name: 'Mains', code: 'MNS', count: 42, active: true },
        { id: 3, name: 'Desserts', code: 'DES', count: 12, active: true },
        { id: 4, name: 'Sides', code: 'SDE', count: 9, active: true },
        { id: 5, name: 'Beverages', code: 'BEV', count: 24, active: true },
        { id: 6, name: 'Chef Specials', code: 'SPC', count: 6, active: false }
    ]
}">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Menu Categories</h1>
            <p class="text-sm text-muted mt-1">Structure your menu hierarchy, toggle section visibility, and reorder departments.</p>
        </div>
        <button 
            @click="alert('New category dialog...')"
            class="rounded-xl bg-orange hover:bg-orange/95 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer flex items-center gap-1.5"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Create Category</span>
        </button>
    </div>

    <!-- Reorder drag indicators -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="(cat, idx) in categories" :key="cat.id">
            <x-card variant="default" class="flex flex-col justify-between h-40">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-3">
                        <!-- Drag Handle Icon -->
                        <span class="text-muted cursor-grab hover:text-ink">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/></svg>
                        </span>
                        <div>
                            <span class="block text-xs font-bold text-ink" x-text="cat.name"></span>
                            <span class="block text-[10px] text-muted font-semibold uppercase mt-0.5" x-text="`Code: ${cat.code}`"></span>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-muted bg-card-tint border border-border px-2 py-0.5 rounded-lg" x-text="`${cat.count} Items`"></span>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-between">
                    <span class="text-[10px] font-bold text-muted uppercase" x-text="cat.active ? 'Visible on Menu' : 'Hidden'"></span>
                    
                    <button 
                        @click="cat.active = !cat.active"
                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                        :class="cat.active ? 'bg-teal' : 'bg-slate-300'"
                    >
                        <span 
                            class="pointer-events-none inline-block h-4.5 w-4.5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                            :class="cat.active ? 'translate-x-4' : 'translate-x-0'"
                        ></span>
                    </button>
                </div>
            </x-card>
        </template>
    </div>
</div>
@endsection
