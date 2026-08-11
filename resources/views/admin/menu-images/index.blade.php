@extends('admin.layout')

@section('title', 'Menu Images')
@section('eyebrow', 'Menu Library')
@section('page-title', 'Menu Item Images')
@section('page-subtitle', 'Upload names and images for the preset food library.')

@section('content')
    @include('admin.partials.alerts')

    <section class="space-y-4">
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm">
            <form method="POST" action="{{ route('admin.menu-images.store') }}" enctype="multipart/form-data" class="grid gap-2 lg:grid-cols-[1.2fr_1.4fr_1.5fr_auto] lg:items-end">
                @csrf
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Item Name</span>
                    <input name="name" value="{{ old('name') }}" required placeholder="Paneer Tikka" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Search Tags</span>
                    <input name="tags" value="{{ old('tags') }}" placeholder="paneer, starter, veg" class="mt-1 h-9 w-full rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Image</span>
                    <input type="file" name="image" required accept="image/*" class="mt-1 block h-9 w-full rounded-lg border border-border bg-card-tint px-2 py-1.5 text-[11px] font-bold text-ink file:mr-2 file:rounded-md file:border-0 file:bg-orange file:px-2 file:py-1 file:text-[10px] file:font-black file:text-white">
                </label>
                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg bg-orange px-4 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                    Upload
                </button>
            </form>
        </div>

        <div class="rounded-lg border border-border bg-card shadow-sm">
            <div class="border-b border-border p-3">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-black text-ink">Image Library</h2>
                        <p class="mt-0.5 text-xs font-semibold text-muted">Compact view for quick upload and editing.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.menu-images.index') }}" class="flex gap-2">
                        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search images" class="h-8 rounded-lg border border-border bg-card-tint px-3 text-xs font-bold text-ink outline-none focus:border-orange">
                        @if(($filters['search'] ?? '') !== '')
                            <a href="{{ route('admin.menu-images.index') }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-border px-3 text-xs font-black text-ink">Clear</a>
                        @endif
                        <button type="submit" class="h-8 rounded-lg bg-navy px-3 text-xs font-black text-white">Search</button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 p-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-8">
                @forelse($images as $image)
                    <article class="rounded-lg border border-border bg-card-tint p-2">
                        <div class="h-20 overflow-hidden rounded-md border border-border bg-card sm:h-24">
                            <img src="{{ asset($image->image_path) }}" alt="{{ $image->name }}" class="h-full w-full object-cover">
                        </div>

                        <form method="POST" action="{{ route('admin.menu-images.update', $image) }}" enctype="multipart/form-data" class="mt-2 space-y-1.5">
                            @csrf
                            @method('PUT')
                            <input name="name" value="{{ old('name', $image->name) }}" required class="h-7 w-full rounded-md border border-border bg-card px-2 text-[11px] font-black text-ink outline-none focus:border-orange">
                            <input name="tags" value="{{ old('tags', $image->tags) }}" placeholder="Tags" class="h-7 w-full rounded-md border border-border bg-card px-2 text-[11px] font-bold text-muted outline-none focus:border-orange">
                            <input type="file" name="image" accept="image/*" class="block w-full rounded-md border border-border bg-card px-2 py-1 text-[10px] font-bold text-ink file:mr-1 file:rounded file:border-0 file:bg-navy file:px-1.5 file:py-0.5 file:text-[9px] file:font-black file:text-white">
                            <div class="flex gap-2">
                                <button type="submit" class="h-7 flex-1 rounded-md bg-orange px-2 text-[9px] font-black uppercase tracking-wider text-white">
                                    Save
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.menu-images.destroy', $image) }}" class="mt-1.5" onsubmit="return confirm('Delete this menu image?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="h-7 w-full rounded-md border border-danger/30 bg-danger/10 px-2 text-[9px] font-black uppercase tracking-wider text-danger">
                                Delete
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="col-span-full rounded-lg border border-dashed border-border bg-card-tint px-4 py-10 text-center text-sm font-bold text-muted">
                        No menu images found.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-border px-4 py-3">
                {{ $images->links() }}
            </div>
        </div>
    </section>
@endsection
