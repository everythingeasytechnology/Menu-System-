@extends('layouts.app')

@section('title', 'Menu Categories')

@section('content')
<div class="space-y-8" x-data="categoryManager({
    initialCategories: {{ $categories->toJson() }}
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
            <p class="text-xs font-bold text-white">Categories Notification</p>
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
            <h1 class="text-2xl font-bold tracking-tight text-ink">Menu Categories</h1>
            <p class="text-sm text-muted mt-1">Structure your menu hierarchy, toggle section visibility, and reorder departments.</p>
        </div>
        <button 
            @click="openAddModal()"
            class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-3 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer flex items-center gap-1.5"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            <span>Create Category</span>
        </button>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <template x-for="cat in categories" :key="cat.id">
            <x-card variant="default" class="flex flex-col justify-between h-44 p-5 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <a :href="'/menu?category=' + encodeURIComponent(cat.name)" class="flex items-center gap-3 hover:text-orange group transition-colors cursor-pointer">
                        <span class="text-slate-300 text-lg group-hover:text-orange transition-colors">
                            📂
                        </span>
                        <div>
                            <span class="block text-sm font-black text-ink group-hover:text-orange transition-colors" x-text="cat.name"></span>
                        </div>
                    </a>

                    <div class="flex items-center gap-1.5">
                        <!-- Edit Button -->
                        <button 
                            @click="openEditModal(cat)"
                            class="p-1 rounded-lg text-slate-400 hover:text-orange hover:bg-orange/5 transition-all cursor-pointer"
                            title="Edit Category"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        
                        <!-- Delete Button -->
                        <button 
                            @click="deleteCategory(cat)"
                            class="p-1 rounded-lg text-slate-400 hover:text-danger hover:bg-danger/5 transition-all cursor-pointer"
                            title="Delete Category"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-between items-center bg-card-tint border border-border/40 p-2.5 rounded-xl mt-3">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Associated Items</span>
                    <span class="text-xs font-black text-ink" x-text="`${cat.count} Items`"></span>
                </div>

                <div class="pt-3 border-t border-border/60 mt-3 flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider" :class="cat.active ? 'text-teal font-extrabold' : 'text-slate-400'" x-text="cat.active ? 'Visible on Menu' : 'Hidden'"></span>
                    
                    <button 
                        @click="toggleActive(cat)"
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

    <!-- Category Add/Edit Modal -->
    <div 
        x-show="modalOpen" 
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
            class="bg-card w-full max-w-md rounded-card shadow-2xl border border-border p-6 flex flex-col max-h-[85vh]"
        >
            <div class="flex items-center justify-between pb-4 border-b border-border mb-4 shrink-0">
                <h3 class="text-base font-black text-ink flex items-center gap-2">
                    <span>📂</span> <span x-text="isEditing ? 'Edit Category' : 'Create Category'"></span>
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

            <!-- Form Content -->
            <form :action="isEditing ? '/categories/' + editingId : '/categories'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Category Name -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-ink uppercase tracking-wider">Category Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="nameInput"
                        required
                        placeholder="e.g. Desserts"
                        class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                    >
                </div>



                <!-- Submit Action Buttons -->
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
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function categoryManager(config) {
    return {
        categories: config.initialCategories,
        modalOpen: false,
        isEditing: false,
        editingId: null,

        // Form Fields
        nameInput: '',
        codeInput: '',

        openAddModal() {
            this.isEditing = false;
            this.editingId = null;
            this.nameInput = '';
            this.codeInput = '';
            this.modalOpen = true;
        },

        openEditModal(cat) {
            this.isEditing = true;
            this.editingId = cat.id;
            this.nameInput = cat.name;
            this.codeInput = cat.code;
            this.modalOpen = true;
        },

        // Toggle category visibility
        async toggleActive(cat) {
            let oldActive = cat.active;
            cat.active = !cat.active; // Optimistic update
            try {
                let response = await fetch(`/categories/${cat.id}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                let data = await response.json();
                if (!data.success) {
                    cat.active = oldActive; // Rollback
                }
            } catch (e) {
                console.error('Failed to toggle category active status', e);
                cat.active = oldActive; // Rollback
            }
        },

        // Delete category
        async deleteCategory(cat) {
            if (cat.count > 0) {
                if (!confirm(`Warning: This category contains ${cat.count} menu items. Deleting it will dissociate these items. Are you sure you want to proceed?`)) {
                    return;
                }
            } else {
                if (!confirm(`Are you sure you want to delete category "${cat.name}"?`)) {
                    return;
                }
            }

            let backupCategories = [...this.categories];
            this.categories = this.categories.filter(c => c.id !== cat.id);

            try {
                let response = await fetch(`/categories/${cat.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                let data = await response.json();
                if (!data.success) {
                    this.categories = backupCategories;
                    alert('Could not delete category.');
                }
            } catch (e) {
                console.error('Failed to delete category', e);
                this.categories = backupCategories;
                alert('Could not delete category.');
            }
        }
    }
}
</script>
@endsection
