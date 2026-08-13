@extends('admin.layout')

@section('title', 'Admin Profile')
@section('eyebrow', 'Account')
@section('page-title', 'Admin Profile')
@section('page-subtitle', 'Manage your superadmin details and password.')

@section('content')
    @include('admin.partials.alerts')

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="rounded-lg border border-border bg-card p-4 shadow-sm">
            @csrf
            @method('PUT')

            <div class="border-b border-border pb-3">
                <h2 class="text-base font-black text-ink">Profile Details</h2>
                <p class="mt-0.5 text-xs font-semibold text-muted">This profile image is also used as the product logo in platform emails.</p>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-[180px_minmax(0,1fr)]">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Profile Logo</span>
                    <div class="mt-2 grid aspect-square w-36 place-items-center overflow-hidden rounded-lg border border-border bg-card-tint">
                        @if($admin->profile_image_path)
                            <img src="{{ asset('storage/'.$admin->profile_image_path) }}" alt="{{ $admin->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-3xl font-black text-orange">{{ str($admin->name)->substr(0, 1)->upper() }}</span>
                        @endif
                    </div>
                    @if($admin->profile_image_path)
                        <label class="mt-3 inline-flex items-center gap-2 text-xs font-bold text-muted">
                            <input type="checkbox" name="remove_profile_image" value="1" class="h-4 w-4 rounded border-border text-orange focus:ring-orange/20">
                            Remove image
                        </label>
                    @endif
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block md:col-span-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Name</span>
                        <input name="name" value="{{ old('name', $admin->name) }}" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>

                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Email</span>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}" required class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>

                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Phone</span>
                        <input name="phone" value="{{ old('phone', $admin->phone) }}" class="mt-1 h-10 w-full rounded-lg border border-border bg-card-tint px-3 text-sm font-bold text-ink outline-none focus:border-orange">
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted">Upload New Image</span>
                        <input type="file" name="profile_image" accept="image/*" class="mt-1 block w-full rounded-lg border border-border bg-card-tint px-3 py-2 text-sm font-bold text-ink file:mr-3 file:rounded-md file:border-0 file:bg-orange file:px-3 file:py-1.5 file:text-xs file:font-black file:text-white">
                    </label>
                </div>
            </div>

            <div class="mt-4 flex justify-end border-t border-border pt-4">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-orange px-5 text-xs font-black text-white shadow-lg shadow-orange/20 transition hover:bg-orange/95">
                    Save Profile
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="rounded-lg border border-border bg-card p-4 shadow-sm" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
            @csrf
            @method('PUT')

            <div class="border-b border-border pb-3">
                <h2 class="text-base font-black text-ink">Password</h2>
                <p class="mt-0.5 text-xs font-semibold text-muted">Change the password for your superadmin account.</p>
            </div>

            <div class="mt-4 space-y-3">
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Current Password</span>
                    <div class="relative mt-1">
                        <input name="current_password" :type="showCurrent ? 'text' : 'password'" required class="h-10 w-full rounded-lg border border-border bg-card-tint px-3 pr-14 text-sm font-bold text-ink outline-none focus:border-orange">
                        <button type="button" @click="showCurrent = !showCurrent" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-[10px] font-black text-muted transition hover:bg-card hover:text-ink">Show</button>
                    </div>
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">New Password</span>
                    <div class="relative mt-1">
                        <input name="password" :type="showNew ? 'text' : 'password'" required class="h-10 w-full rounded-lg border border-border bg-card-tint px-3 pr-14 text-sm font-bold text-ink outline-none focus:border-orange">
                        <button type="button" @click="showNew = !showNew" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-[10px] font-black text-muted transition hover:bg-card hover:text-ink">Show</button>
                    </div>
                </label>

                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-wider text-muted">Confirm Password</span>
                    <div class="relative mt-1">
                        <input name="password_confirmation" :type="showConfirm ? 'text' : 'password'" required class="h-10 w-full rounded-lg border border-border bg-card-tint px-3 pr-14 text-sm font-bold text-ink outline-none focus:border-orange">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-[10px] font-black text-muted transition hover:bg-card hover:text-ink">Show</button>
                    </div>
                </label>
            </div>

            <div class="mt-4 flex justify-end border-t border-border pt-4">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-navy px-5 text-xs font-black text-white transition hover:bg-navy/90">
                    Update Password
                </button>
            </div>
        </form>
    </section>
@endsection
