@extends('layouts.app')

@section('title', 'Menu Catalog')

@section('content')
<div class="space-y-8" x-data="{
    searchQuery: '',
    selectedCategory: 'all',
    items: [
        { id: 'M101', name: 'Dry Aged Ribeye Steak', category: 'mains', price: 42.00, stock: true, icon: '🥩' },
        { id: 'M102', name: 'Pan-Seared Atlantic Salmon', category: 'mains', price: 34.50, stock: true, icon: '🐟' },
        { id: 'M103', name: 'Parmesan Truffle Fries', category: 'sides', price: 12.50, stock: true, icon: '🍟' },
        { id: 'M104', name: 'Caesar Salad with Crispy Bacon', category: 'mains', price: 18.00, stock: false, icon: '🥗' },
        { id: 'M105', name: 'Fresh Squeezed Orange Juice', category: 'beverages', price: 6.50, stock: true, icon: '🍊' },
        { id: 'M106', name: 'Double Shot Cappuccino', category: 'beverages', price: 5.50, stock: true, icon: '☕' },
        { id: 'M107', name: 'Warm Chocolate Lava Cake', category: 'desserts', price: 14.00, stock: true, icon: '🍰' }
    ]
}">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Menu Catalog</h1>
            <p class="text-sm text-muted mt-1">Configure and manage prices, categories, and inventory stock across all branches.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button 
                @click="alert('Opening menu creation form...')" 
                class="rounded-xl bg-orange hover:bg-orange/95 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Add Menu Item</span>
            </button>
        </div>
    </div>

    <!-- Filters Bar -->
    <x-card class="p-4" variant="default">
        <div class="flex flex-col lg:flex-row gap-4 justify-between items-center">
            <!-- Tabs -->
            <div class="flex items-center gap-1 bg-card-tint border border-border p-1 rounded-xl w-full lg:w-auto overflow-x-auto scrollbar-none">
                <button 
                    @click="selectedCategory = 'all'"
                    :class="selectedCategory === 'all' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    All Items
                </button>
                <button 
                    @click="selectedCategory = 'mains'"
                    :class="selectedCategory === 'mains' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    Mains
                </button>
                <button 
                    @click="selectedCategory = 'sides'"
                    :class="selectedCategory === 'sides' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    Sides
                </button>
                <button 
                    @click="selectedCategory = 'beverages'"
                    :class="selectedCategory === 'beverages' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    Beverages
                </button>
                <button 
                    @click="selectedCategory = 'desserts'"
                    :class="selectedCategory === 'desserts' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    Desserts
                </button>
            </div>

            <!-- Search box -->
            <div class="relative w-full lg:w-72">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    placeholder="Search catalog... (10,000+ items ready)"
                    class="w-full rounded-xl border border-border bg-card-tint py-2 pl-9 pr-3 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="h-4 w-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Catalog Item Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        <template x-for="item in items" :key="item.id">
            <div 
                x-show="
                    (selectedCategory === 'all' || item.category === selectedCategory) &&
                    (searchQuery === '' || item.name.toLowerCase().includes(searchQuery.toLowerCase()))
                "
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="rounded-card border border-border bg-card p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 transition-all flex flex-col justify-between h-48 select-none"
            >
                <div class="flex justify-between items-start">
                    <div class="flex gap-3 items-center">
                        <span class="text-3xl" x-text="item.icon"></span>
                        <div>
                            <span class="block text-xs font-bold text-ink" x-text="item.name"></span>
                            <span class="block text-[10px] text-muted font-semibold uppercase mt-0.5" x-text="item.category"></span>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-ink" x-text="`$${item.price.toFixed(2)}`"></span>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] text-muted font-bold uppercase">SKU</span>
                        <span class="block text-xs font-semibold text-ink" x-text="item.id"></span>
                    </div>

                    <!-- Stock Status Switcher (Apple style) -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-muted uppercase" x-text="item.stock ? 'In Stock' : 'Out of Stock'"></span>
                        <button 
                            @click="item.stock = !item.stock"
                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="item.stock ? 'bg-teal' : 'bg-slate-300'"
                        >
                            <span 
                                class="pointer-events-none inline-block h-4.5 w-4.5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                :class="item.stock ? 'translate-x-4' : 'translate-x-0'"
                            ></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection
