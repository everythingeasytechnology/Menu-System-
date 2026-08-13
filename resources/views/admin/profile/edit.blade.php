@extends('admin.layout')

@section('title', 'Admin Profile')
@section('eyebrow', 'Account')
@section('page-title', 'Admin Profile')
@section('page-subtitle', 'Manage your superadmin details and password.')

@section('content')
    @include('admin.partials.alerts')

    <section class="space-y-5">
        <div class="overflow-hidden rounded-lg border border-border bg-card shadow-sm">
            <div class="flex flex-col gap-4 border-b border-border bg-card-tint p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-lg border border-border bg-card shadow-sm">
                        @if($admin->profile_image_path)
                            <img src="{{ asset('storage/'.$admin->profile_image_path) }}" alt="{{ $admin->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-2xl font-black text-orange">{{ str($admin->name)->substr(0, 1)->upper() }}</span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-wider text-orange">Superadmin Account</p>
                        <h2 class="mt-1 truncate text-xl font-black text-ink">{{ $admin->name }}</h2>
                        <p class="mt-0.5 truncate text-xs font-semibold text-muted">{{ $admin->email }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex h-8 items-center rounded-lg bg-success/10 px-3 text-[10px] font-black uppercase tracking-wider text-success">
                        {{ ucfirst($admin->status) }}
                    </span>
                    <span class="inline-flex h-8 items-center rounded-lg bg-orange/10 px-3 text-[10px] font-black uppercase tracking-wider text-orange">
                        {{ $admin->role }}
                    </span>
                </div>
            </div>

            <div class="grid gap-0 divide-y divide-border md:grid-cols-3 md:divide-x md:divide-y-0">
                <div class="p-4">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Phone</span>
                    <strong class="mt-1 block truncate text-sm font-black text-ink">{{ $admin->phone ?: 'Not set' }}</strong>
                </div>
                <div class="p-4">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Email Logo Source</span>
                    <strong class="mt-1 block truncate text-sm font-black text-ink">{{ $admin->profile_image_path ? 'Profile image active' : 'Text fallback active' }}</strong>
                </div>
                <div class="p-4">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Last Updated</span>
                    <strong class="mt-1 block truncate text-sm font-black text-ink">{{ $admin->updated_at?->format('d M Y, h:i A') }}</strong>
                </div>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_400px]">
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="rounded-lg border border-border bg-card shadow-sm">
            @csrf
            @method('PUT')

                <div class="border-b border-border p-4">
                    <h2 class="text-base font-black text-ink">Profile Details</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">This image becomes the product logo in platform emails.</p>
                </div>

                <div class="grid gap-5 p-4 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <div class="rounded-lg border border-border bg-card-tint p-3">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Brand Logo</span>
                        <div class="mt-3 grid aspect-square w-full place-items-center overflow-hidden rounded-lg border border-border bg-card">
                            @if($admin->profile_image_path)
                                <img src="{{ asset('storage/'.$admin->profile_image_path) }}" alt="{{ $admin->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-5xl font-black text-orange">{{ str($admin->name)->substr(0, 1)->upper() }}</span>
                            @endif
                        </div>
                        <p class="mt-3 text-[11px] font-semibold leading-5 text-muted">Square PNG/JPG image works best. This is used in reset-password and platform emails.</p>
                        @if($admin->profile_image_path)
                            <label class="mt-3 flex items-center gap-2 rounded-lg border border-border bg-card px-3 py-2 text-xs font-bold text-muted">
                                <input type="checkbox" name="remove_profile_image" value="1" class="h-4 w-4 rounded border-border text-orange focus:ring-orange/20">
                                Remove current image
                            </label>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="grid gap-3 md:grid-cols-2">
                            <label class="block md:col-span-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-muted">Name</span>
                                <input name="name" value="{{ old('name', $admin->name) }}" required class="mt-1 h-11 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none transition focus:border-orange focus:bg-card">
                            </label>

                            <label class="block">
                                <span class="text-[10px] font-black uppercase tracking-wider text-muted">Email</span>
                                <input type="email" name="email" value="{{ old('email', $admin->email) }}" required class="mt-1 h-11 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none transition focus:border-orange focus:bg-card">
                            </label>

                            <label class="block">
                                <span class="text-[10px] font-black uppercase tracking-wider text-muted">Phone</span>
                                <input name="phone" value="{{ old('phone', $admin->phone) }}" class="mt-1 h-11 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none transition focus:border-orange focus:bg-card">
                            </label>
                        </div>

                        <label class="block rounded-lg border border-dashed border-border bg-card-tint p-4">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted">Upload New Image</span>
                            <input type="file" name="profile_image" accept="image/*" class="mt-2 block w-full rounded-lg border border-border bg-card px-3 py-2 text-sm font-bold text-ink file:mr-3 file:rounded-md file:border-0 file:bg-orange file:px-3 file:py-1.5 file:text-xs file:font-black file:text-white">
                            <span class="mt-2 block text-[11px] font-semibold text-muted">Accepted: JPG, PNG, GIF, WEBP up to 2 MB.</span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-t border-border p-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-semibold text-muted">Changes apply immediately across the admin panel.</p>
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-orange px-5 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                        Save Profile
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.profile.password') }}" class="rounded-lg border border-border bg-card shadow-sm" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
                @csrf
                @method('PUT')

                <div class="border-b border-border p-4">
                    <h2 class="text-base font-black text-ink">Password</h2>
                    <p class="mt-0.5 text-xs font-semibold text-muted">Change your superadmin password.</p>
                </div>

                <div class="space-y-4 p-4">
                    <div class="rounded-lg border border-border bg-card-tint p-3">
                        <p class="text-xs font-black text-ink">Security check</p>
                        <p class="mt-1 text-[11px] font-semibold leading-5 text-muted">Use at least 8 characters. After update, use the new password for the next login.</p>
                    </div>

                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Current Password</span>
                        <div class="relative mt-1">
                            <input name="current_password" :type="showCurrent ? 'text' : 'password'" required class="h-11 w-full rounded-lg border border-border bg-card-tint px-3 pr-16 text-sm font-bold text-ink outline-none transition focus:border-orange focus:bg-card">
                            <button type="button" @click="showCurrent = !showCurrent" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-[10px] font-black text-muted transition hover:bg-card hover:text-ink">Show</button>
                        </div>
                    </label>

                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">New Password</span>
                        <div class="relative mt-1">
                            <input name="password" :type="showNew ? 'text' : 'password'" required class="h-11 w-full rounded-lg border border-border bg-card-tint px-3 pr-16 text-sm font-bold text-ink outline-none transition focus:border-orange focus:bg-card">
                            <button type="button" @click="showNew = !showNew" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-[10px] font-black text-muted transition hover:bg-card hover:text-ink">Show</button>
                        </div>
                    </label>

                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Confirm Password</span>
                        <div class="relative mt-1">
                            <input name="password_confirmation" :type="showConfirm ? 'text' : 'password'" required class="h-11 w-full rounded-lg border border-border bg-card-tint px-3 pr-16 text-sm font-bold text-ink outline-none transition focus:border-orange focus:bg-card">
                            <button type="button" @click="showConfirm = !showConfirm" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-[10px] font-black text-muted transition hover:bg-card hover:text-ink">Show</button>
                        </div>
                    </label>
                </div>

                <div class="border-t border-border p-4">
                    <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-navy px-5 text-xs font-black text-white transition hover:bg-navy/90">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
