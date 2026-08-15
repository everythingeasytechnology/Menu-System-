@extends('layouts.app')

@section('title', 'Menu Catalog')

@section('content')
<div class="space-y-8" x-data="menuManager({
    initialItems: {{ $items->toJson() }},
    categories: {{ json_encode($categories) }}
})">
    <!-- Floating Success Toast -->
    @if(session('success'))
    <div 
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 4000)"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-navy-deep border border-orange/20 text-white px-5 py-4 rounded-xl shadow-xl max-w-sm"
    >
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange/20 text-orange">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-xs font-bold text-white">Catalog Notification</p>
            <p class="text-[11px] text-slate-300 mt-0.5">{{ session('success') }}</p>
        </div>
        <button @click="show = false" class="text-muted hover:text-white transition-colors cursor-pointer">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Menu Catalog</h1>
            <p class="text-sm text-muted mt-1">Configure portions, categories, and inventory availability. Synced with the waiter and customer ordering apps.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button 
                @click="openAddModal()" 
                class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-3 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Add Menu Item</span>
            </button>
        </div>
    </div>

    <!-- Filters Bar -->
    <x-card class="p-4">
        <div class="flex flex-col lg:flex-row gap-4 justify-between items-center">
            <!-- Dynamic Categories Tabs -->
            <div class="flex items-center gap-1 bg-card-tint border border-border p-1 rounded-xl w-full lg:w-auto overflow-x-auto scrollbar-none">
                <button 
                    @click="selectCategory('all')"
                    :class="selectedCategory === 'all' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    All Items
                </button>
                <template x-for="cat in categories" :key="cat">
                    <button 
                        @click="selectCategory(cat)"
                        :class="selectedCategory === cat ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                        class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                        x-text="cat"
                    ></button>
                </template>
            </div>

            <!-- Search box -->
            <div class="relative w-full lg:w-72">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @input="scheduleItemsFetch()"
                    placeholder="Search menu items..."
                    class="w-full rounded-xl border border-border bg-card-tint py-2.5 pl-9 pr-3 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="h-4 w-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div x-show="isLoadingItems" class="absolute inset-y-0 right-0 flex items-center pr-3 text-orange" style="display: none;">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Catalog Item Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        <template x-for="item in items" :key="item.id">
            <div 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="rounded-card border border-border bg-card p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-h-[340px]"
            >
                <div>
                    <!-- Menu Item Image -->
                    <div class="h-32 w-full rounded-xl overflow-hidden bg-slate-100 border border-border/60 mb-4 relative shrink-0">
                        <!-- Dietary Type Badge in corner of image -->
                        <div class="absolute top-2.5 left-2.5 z-10 bg-white/95 backdrop-blur-xs px-2 py-0.5 rounded-lg border border-border/80 flex items-center gap-1 shadow-xs">
                            <div class="h-1.5 w-1.5 rounded-full" :class="item.type === 'veg' ? 'bg-green-600' : 'bg-red-600'"></div>
                            <span class="text-[8px] font-black uppercase tracking-wider text-ink" x-text="item.type"></span>
                        </div>

                        <!-- actual Image element -->
                        <template x-if="item.preset_image">
                            <img :src="'/' + item.preset_image.image_path" class="h-full w-full object-cover" alt="Food item">
                        </template>
                        <template x-if="!item.preset_image">
                            <div class="h-full w-full flex items-center justify-center text-slate-300 bg-slate-50">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                                </svg>
                            </div>
                        </template>
                    </div>

                    <div class="space-y-4">
                        <!-- Heading: Name, Category -->
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <span class="text-xs font-black text-ink block" x-text="item.name"></span>
                                <span class="block text-[9px] text-muted font-bold uppercase mt-0.5 tracking-wider" x-text="item.category"></span>
                            </div>
                            
                            <div class="flex items-center gap-1 shrink-0">
                                <!-- Edit Action Button -->
                                <button 
                                    @click="openEditModal(item)"
                                    class="p-1 rounded-lg text-slate-400 hover:text-orange hover:bg-orange/5 transition-all cursor-pointer"
                                    title="Edit Item"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                <!-- Delete Action Button -->
                                <button 
                                    @click="deleteItem(item)"
                                    class="p-1 rounded-lg text-slate-400 hover:text-danger hover:bg-danger/5 transition-all cursor-pointer"
                                    title="Delete Item"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Portions & Sizing list -->
                        <div class="space-y-1.5 p-3 rounded-xl bg-card-tint border border-border/40">
                            <template x-for="v in item.variants" :key="v.id">
                                <div class="flex justify-between text-[11px] font-bold text-ink">
                                    <span class="text-slate-400 font-semibold" x-text="v.label"></span>
                                    <span x-text="`₹${v.price}`"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-border mt-4 flex items-center justify-between">
                    <div>
                        <!-- Optional Cooking Time -->
                        <template x-if="item.cooking_time">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-muted">
                                <span>⏱️</span>
                                <span x-text="item.cooking_time"></span>
                            </span>
                        </template>
                    </div>

                    <!-- Stock Toggle Switch -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase" :class="item.stock ? 'text-teal font-extrabold' : 'text-slate-400'" x-text="item.stock ? 'In Stock' : 'Out'"></span>
                        <button 
                            @click="toggleStock(item)"
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
    <div x-show="!isLoadingItems && items.length === 0" class="rounded-card border border-dashed border-border bg-card-tint px-5 py-10 text-center text-xs font-bold text-muted" style="display: none;">
        No menu items found.
    </div>

    <!-- Add Menu Item Modal (React Native Parity) -->
    <div
        x-show="modalOpen"
        x-init="@if($errors->any()) modalOpen = true @endif"
        class="fixed inset-0 z-50 flex items-center justify-center bg-navy-deep/60 backdrop-blur-xs p-4"
        style="display: none;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            @click.outside="modalOpen = false"
            class="bg-card w-full max-w-lg rounded-card shadow-2xl border border-border p-6 flex flex-col max-h-[85vh]"
        >
            <div class="flex items-center justify-between pb-4 border-b border-border mb-4 shrink-0">
                <h3 class="text-base font-black text-ink flex items-center gap-2">
                    <span>🍽️</span> <span x-text="isEditing ? 'Edit Menu Item' : 'Add Menu Item'"></span>
                </h3>
                <button
                    @click="modalOpen = false"
                    class="h-8 w-8 rounded-lg flex items-center justify-center hover:bg-card-tint text-muted hover:text-ink cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-danger/20 bg-danger/5 px-4 py-3 text-xs font-semibold text-danger shrink-0">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Form Content (Scrollable) -->
            <form :action="isEditing ? '/menu/' + editingId : '/menu'" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto pr-1 space-y-6 pb-2">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- 1. Core Item Details -->
                <div class="space-y-4">
                    <!-- Item Name -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-ink uppercase tracking-wider">Item Name</label>
                        <input 
                            type="text" 
                            name="name" 
                            x-model="newName"
                            @input.debounce.300ms="if (!isEditing) { presetSearchQuery = newName; searchPresets(); }"
                            required
                            placeholder="e.g. Matar Paneer"
                            class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                        >
                    </div>

                    <!-- Dietary Type (Veg/Non-Veg) -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-ink uppercase tracking-wider">Dietary Type</label>
                        <div class="flex gap-4">
                            <button 
                                type="button" 
                                @click="newType = 'veg'"
                                :class="newType === 'veg' ? 'bg-green-50 border-green-500 text-green-700 shadow-sm' : 'border-border bg-card-tint text-slate-400'"
                                class="flex-1 py-3 border rounded-xl font-bold flex items-center justify-center gap-2 cursor-pointer transition-all text-xs"
                            >
                                <div class="h-4 w-4 border-2 border-green-600 flex items-center justify-center rounded-sm">
                                    <div class="h-2 w-2 rounded-full bg-green-600"></div>
                                </div>
                                <span>VEG</span>
                            </button>

                            <button 
                                type="button" 
                                @click="newType = 'non-veg'"
                                :class="newType === 'non-veg' ? 'bg-red-50 border-red-500 text-red-700 shadow-sm' : 'border-border bg-card-tint text-slate-400'"
                                class="flex-1 py-3 border rounded-xl font-bold flex items-center justify-center gap-2 cursor-pointer transition-all text-xs"
                            >
                                <div class="h-4 w-4 border-2 border-red-600 flex items-center justify-center rounded-sm">
                                    <div class="h-2 w-2 rounded-full bg-red-600"></div>
                                </div>
                                <span>NON-VEG</span>
                            </button>
                        </div>
                        <input type="hidden" name="type" :value="newType">
                    </div>

                    <!-- Cooking Time -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-ink uppercase tracking-wider">Cooking Time (Optional)</label>
                        <input 
                            type="text" 
                            name="cooking_time" 
                            x-model="newCookingTime"
                            placeholder="e.g. 15-20 mins"
                            class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                        >
                    </div>
                </div>

                <!-- Image Selection Section -->
                <div class="space-y-4">
                    <label class="block text-xs font-bold text-ink uppercase tracking-wider">Menu Item Image</label>
                    
                    <!-- Toggle Tabs for Custom vs Preset -->
                    <div class="flex items-center gap-1 bg-card-tint border border-border p-1 rounded-xl w-full">
                        <button 
                            type="button"
                            @click="imageSource = 'preset'"
                            :class="imageSource === 'preset' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                            class="flex-1 rounded-lg py-2 text-xs transition-all cursor-pointer"
                        >
                            Select Preset
                        </button>
                        <button 
                            type="button"
                            @click="imageSource = 'upload'"
                            :class="imageSource === 'upload' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
                            class="flex-1 rounded-lg py-2 text-xs transition-all cursor-pointer"
                        >
                            Upload Custom
                        </button>
                    </div>

                    <!-- Preset Image Gallery -->
                    <div x-show="imageSource === 'preset'" class="space-y-4">
                        <input type="hidden" name="preset_image_id" :value="presetImageSelectedId">
                        
                        <!-- Search Box for Preset Images -->
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="presetSearchQuery" 
                                @input.debounce.250ms="searchPresets()"
                                placeholder="Search preset food library (e.g. paneer, steak, coffee)..."
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 pl-9 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                                             <!-- Dynamic Grid of Searched Presets -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 p-3 rounded-2xl bg-card-tint border border-border/60 min-h-20">
                            <!-- Preset Image Cards -->
                            <template x-for="p in presetImages" :key="p.id">
                                <button 
                                    type="button"
                                    @click="presetImageSelectedId = p.id; removeImageFlag = '0';"
                                    :class="presetImageSelectedId === p.id && removeImageFlag === '0' ? 'border-orange ring-4 ring-orange/15 scale-[1.02]' : 'border-border/60 hover:border-orange'"
                                    class="aspect-square rounded-xl overflow-hidden border-2 bg-white transition-all cursor-pointer relative"
                                    :title="p.name"
                                >
                                    <img :src="'/' + p.image_path" class="h-full w-full object-cover" alt="Preset option">
                                    <template x-if="presetImageSelectedId === p.id && removeImageFlag === '0'">
                                        <div class="absolute inset-0 bg-orange/10 flex items-center justify-center">
                                            <span class="text-white text-xs bg-orange h-5 w-5 rounded-full flex items-center justify-center font-bold">✓</span>
                                        </div>
                                    </template>
                                </button>
                            </template>

                            <!-- Placeholder when search is empty -->
                            <template x-if="presetSearchQuery.trim() === ''">
                                <div class="col-span-2 sm:col-span-3 md:col-span-4 lg:col-span-5 py-6 text-center text-xs text-muted flex flex-col items-center justify-center gap-1">
                                    <span class="text-lg">🔍</span>
                                    <span class="font-bold">Please search for images...</span>
                                    <span class="text-[10px] text-slate-400">Type in the item name or enter a term above.</span>
                                </div>
                            </template>

                            <!-- Placeholder when search query yields nothing -->
                            <template x-if="presetSearchQuery.trim() !== '' && presetImages.length === 0">
                                <div class="col-span-2 sm:col-span-3 md:col-span-4 lg:col-span-5 py-6 text-center text-xs text-muted flex flex-col items-center justify-center gap-1.5">
                                    <span class="text-lg">😕</span>
                                    <span class="font-bold">No preset images found.</span>
                                    <span class="text-[10px] text-slate-400">Please upload a custom image under the "Upload Custom" tab.</span>
                                </div>
                            </template>
                        </div>
                        </div>
                    </div>

                    <!-- Custom Upload Field -->
                    <div x-show="imageSource === 'upload'" class="space-y-3">
                        <div class="p-4 rounded-2xl bg-card-tint border border-border/60 flex flex-col items-center justify-center gap-3">
                            <!-- Show preview of current image if editing -->
                            <template x-if="isEditing && currentImagePath && removeImageFlag === '0'">
                                <div class="relative h-20 w-20 rounded-xl overflow-hidden border border-border">
                                    <img :src="'/' + currentImagePath" class="h-full w-full object-cover" alt="Current image">
                                </div>
                            </template>

                            <input 
                                type="file" 
                                name="image" 
                                @change="removeImageFlag = '0'"
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange/10 file:text-orange hover:file:bg-orange/15 transition-all cursor-pointer"
                            >
                        </div>
                    </div>

                    <!-- Remove Image option if there is an image (Preset or Custom) -->
                    <template x-if="isEditing && currentImagePath && removeImageFlag === '0'">
                        <div class="flex items-center justify-end">
                            <button 
                                type="button" 
                                @click="removeImageFlag = '1'; presetImageSelected = '';"
                                class="text-[10px] font-bold text-danger hover:underline flex items-center gap-1 cursor-pointer"
                            >
                                🗑️ Remove Image
                            </button>
                        </div>
                    </template>
                    <input type="hidden" name="remove_image" :value="removeImageFlag">
                </div>

                <!-- 2. Category Selection -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-ink uppercase tracking-wider">Category</label>
                    <input type="hidden" name="category" :value="newCategory" required>
                    
                    <!-- Quick Select Tags -->
                    <div class="flex flex-wrap gap-2 p-3 rounded-2xl bg-card-tint border border-border/60">
                        <template x-for="cat in commonCategories" :key="cat">
                            <button 
                                type="button" 
                                @click="newCategory = cat"
                                :class="newCategory === cat ? 'bg-orange text-white border-orange shadow-md shadow-orange/15 font-bold scale-[1.02]' : 'bg-white border-border text-slate-500 hover:border-orange hover:text-ink'"
                                class="px-3.5 py-2 rounded-xl border text-xs transition-all cursor-pointer select-none"
                                x-text="cat"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- 3. Base Price & Sort Order -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-ink uppercase tracking-wider">Base Price (₹) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="price"
                            x-model="newPrice"
                            required
                            placeholder="e.g. 150.00"
                            class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-3.5 text-xs text-ink focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                        >
                        <p class="text-[9px] text-slate-400">Used when no portion/size is selected on the menu.</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-ink uppercase tracking-wider">Sort Order</label>
                        <input
                            type="number"
                            step="1"
                            min="0"
                            name="sort_order"
                            x-model="newSortOrder"
                            placeholder="0"
                            class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-3.5 text-xs text-ink focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                        >
                        <p class="text-[9px] text-slate-400">Lower numbers appear first in the menu.</p>
                    </div>
                </div>

                <!-- 4. Dynamic Sizes & Pricing -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xs font-bold text-ink uppercase tracking-wider">Sizes & Pricing <span class="text-slate-400 normal-case font-semibold">(optional)</span></h4>
                        <button
                            type="button"
                            @click="addVariant"
                            class="bg-orange/10 text-orange border border-orange/20 px-3 py-1.5 rounded-lg text-[10px] font-bold hover:bg-orange/15 transition-all cursor-pointer flex items-center gap-1"
                        >
                            <span>+</span> Add Portion
                        </button>
                    </div>

                    <template x-if="newVariants.length === 0">
                        <p class="text-xs text-slate-400 italic">No portions added — the base price above will be used.</p>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(v, index) in newVariants" :key="index">
                            <div class="flex items-end gap-3">
                                <!-- Sizing / Portion Label -->
                                <div class="flex-1 space-y-1">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Portion Label</label>
                                    <input 
                                        type="text" 
                                        :name="`variants[${index}][label]`" 
                                        x-model="v.label"
                                        required
                                        placeholder="e.g. Regular, Half, Full, 300ml"
                                        class="w-full rounded-xl border border-border bg-card-tint py-2 px-3.5 text-xs text-ink focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                                    >
                                </div>

                                <!-- Portion Price -->
                                <div class="w-32 space-y-1">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Price (₹)</label>
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        :name="`variants[${index}][price]`" 
                                        x-model="v.price"
                                        required
                                        placeholder="0.00"
                                        class="w-full rounded-xl border border-border bg-card-tint py-2 px-3.5 text-xs text-ink focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                                    >
                                </div>

                                <!-- Delete Row Button -->
                                <button
                                    type="button"
                                    @click="removeVariant(index)"
                                    class="h-9 w-9 rounded-xl flex items-center justify-center text-danger hover:bg-danger/5 transition-all cursor-pointer border border-transparent hover:border-danger/10"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="flex justify-end gap-3 pt-4 border-t border-border shrink-0">
                    <button 
                        type="button" 
                        @click="modalOpen = false"
                        class="rounded-xl bg-card-tint border border-border hover:bg-slate-200 px-5 py-3 text-xs font-bold text-ink cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-3 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
                    >
                        Save Menu Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function menuManager(config) {
    return {
        searchQuery: '',
        selectedCategory: 'all',
        items: config.initialItems,
        categories: config.categories,
        isLoadingItems: false,
        searchDebounceTimer: null,
        itemsFetchController: null,
        modalOpen: false,
        isEditing: false,
        editingId: null,

        // Modal Form fields
        newName: '',
        newCategory: '',
        newType: 'veg',
        newCookingTime: '',
        newPrice: '',
        newSortOrder: '',
        newVariants: [{ label: 'Regular', price: '' }],
        commonCategories: config.categories,

        // Image Selection states
        imageSource: 'preset',
        presetSearchQuery: '',
        presetImageSelectedId: '',
        removeImageFlag: '0',
        currentImagePath: '',
        presetImages: [],

        selectCategory(category) {
            if (this.selectedCategory === category) {
                return;
            }

            this.selectedCategory = category;
            this.fetchItems();
        },

        scheduleItemsFetch() {
            clearTimeout(this.searchDebounceTimer);
            this.searchDebounceTimer = setTimeout(() => this.fetchItems(), 350);
        },

        async fetchItems() {
            if (this.itemsFetchController) {
                this.itemsFetchController.abort();
            }

            const controller = new AbortController();
            this.itemsFetchController = controller;
            this.isLoadingItems = true;

            const params = new URLSearchParams();
            const search = this.searchQuery.trim();

            if (search !== '') {
                params.set('search', search);
            }

            if (this.selectedCategory !== 'all') {
                params.set('category', this.selectedCategory);
            }

            try {
                const queryString = params.toString();
                const response = await fetch(`/menu/items${queryString ? '?' + queryString : ''}`, {
                    headers: {
                        'Accept': 'application/json'
                    },
                    signal: controller.signal
                });

                if (!response.ok) {
                    throw new Error('Unable to load menu items.');
                }

                const payload = await response.json();
                this.items = payload.items || [];
            } catch (e) {
                if (e.name !== 'AbortError') {
                    console.error('Failed to load menu items', e);
                }
            } finally {
                if (this.itemsFetchController === controller) {
                    this.isLoadingItems = false;
                    this.itemsFetchController = null;
                }
            }
        },

        async searchPresets() {
            if (this.presetSearchQuery.trim() === '') {
                this.presetImages = [];
                return;
            }
            try {
                let res = await fetch('/preset-images?search=' + encodeURIComponent(this.presetSearchQuery));
                this.presetImages = await res.json();
            } catch (e) {
                console.error('Failed to search presets', e);
            }
        },

        openAddModal() {
            this.isEditing = false;
            this.editingId = null;
            this.newName = '';
            this.newCategory = this.commonCategories.length > 0 ? this.commonCategories[0] : '';
            this.newType = 'veg';
            this.newCookingTime = '';
            this.newPrice = '';
            this.newSortOrder = '';
            this.newVariants = [{ label: 'Regular', price: '' }];

            this.imageSource = 'preset';
            this.presetSearchQuery = '';
            this.presetImageSelectedId = '';
            this.removeImageFlag = '0';
            this.currentImagePath = '';
            this.presetImages = [];
            
            this.modalOpen = true;
        },

        openEditModal(item) {
            this.isEditing = true;
            this.editingId = item.id;
            this.newName = item.name;
            this.newCategory = item.category;
            this.newType = item.type;
            this.newCookingTime = item.cooking_time || '';
            this.newPrice = item.price > 0 ? item.price : '';
            this.newSortOrder = item.sort_order ?? '';
            this.newVariants = item.variants.map(v => ({ label: v.label, price: v.price }));

            this.currentImagePath = item.preset_image ? item.preset_image.image_path : '';
            this.removeImageFlag = '0';
            this.presetImageSelectedId = item.preset_food_image_id || '';
            
            if (this.currentImagePath) {
                if (this.currentImagePath.startsWith('images/defaults/')) {
                    this.imageSource = 'preset';
                    this.presetSearchQuery = item.name;
                    this.searchPresets();
                } else {
                    this.imageSource = 'upload';
                    this.presetSearchQuery = '';
                    this.presetImages = [];
                }
            } else {
                this.imageSource = 'preset';
                this.presetSearchQuery = '';
                this.presetImages = [];
            }
            
            this.modalOpen = true;
        },

        // Toggle stock via AJAX fetch
        async toggleStock(item) {
            let oldStock = item.stock;
            item.stock = !item.stock; // Optimistic update in UI
            try {
                let response = await fetch(`/menu/${item.id}/toggle-stock`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                let data = await response.json();
                if (!data.success) {
                    item.stock = oldStock; // Rollback on error
                }
            } catch (e) {
                console.error('Failed to toggle stock status', e);
                item.stock = oldStock; // Rollback on connection error
            }
        },

        // Delete menu item via AJAX fetch
        async deleteItem(item) {
            if (!confirm(`Are you sure you want to delete "${item.name}"?`)) return;
            let backupItems = [...this.items];
            this.items = this.items.filter(i => i.id !== item.id); // Optimistic UI update
            
            try {
                let response = await fetch(`/menu/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                let data = await response.json();
                if (!data.success) {
                    this.items = backupItems; // Rollback
                    alert('Could not delete the menu item.');
                }
            } catch (e) {
                console.error('Failed to delete menu item', e);
                this.items = backupItems; // Rollback
                alert('Could not delete the menu item.');
            }
        },

        // Add size variant row
        addVariant() {
            this.newVariants.push({ label: '', price: '' });
        },

        // Remove size variant row
        removeVariant(index) {
            if (this.newVariants.length > 1) {
                this.newVariants.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
