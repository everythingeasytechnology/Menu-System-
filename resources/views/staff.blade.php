@extends('layouts.app')

@section('title', 'Staff Members')

@section('content')
<div class="space-y-8" x-data="staffManager({
    initialStaff: {{ Illuminate\Support\Js::from($staffPayload) }},
    roles: {{ Illuminate\Support\Js::from($roles) }},
    statuses: {{ Illuminate\Support\Js::from($statuses) }},
    csrf: '{{ csrf_token() }}'
})">
    <!-- Floating Toast -->
    <div
        x-show="toast"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-navy-deep border border-orange/20 text-white px-5 py-4 rounded-xl shadow-xl max-w-sm"
        style="display: none;"
    >
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange/20 text-orange">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-xs font-bold text-white">Staff Directory</p>
            <p class="text-[11px] text-slate-300 mt-0.5" x-text="toast"></p>
        </div>
        <button @click="toast = ''" class="text-muted hover:text-white transition-colors cursor-pointer">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Staff Directory</h1>
            <p class="text-sm text-muted mt-1">Manage restaurant roles, profiles, and account status.</p>
        </div>

        <div class="flex items-center gap-3">
            <button
                @click="openCreateModal()"
                class="rounded-xl bg-orange hover:bg-orange/95 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Add Staff Member</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <x-card class="p-4" variant="default">
        <div class="flex flex-col lg:flex-row gap-4 justify-between items-center">
            <!-- Tabs -->
            <div class="flex items-center gap-1 bg-card-tint border border-border p-1 rounded-xl w-full lg:w-auto overflow-x-auto scrollbar-none">
                <button
                    @click="selectedRole = 'all'"
                    :class="selectedRole === 'all' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    All Roles
                </button>
                <template x-for="[value, label] in Object.entries(roles)" :key="value">
                    <button
                        @click="selectedRole = value"
                        :class="selectedRole === value ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                        class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                        x-text="label"
                    ></button>
                </template>
            </div>

            <!-- Search input -->
            <div class="relative w-full lg:w-72">
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Search by name, email, phone..."
                    class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 pl-9 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted transition-all"
                />
                <div class="absolute left-3 top-2.5 text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Staff Directory Table -->
    <x-card class="overflow-x-auto p-0" variant="default">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                    <th class="py-3.5 px-5">Name</th>
                    <th class="py-3.5 px-5">Role</th>
                    <th class="py-3.5 px-5">Phone</th>
                    <th class="py-3.5 px-5">Email ID</th>
                    <th class="py-3.5 px-5">Status</th>
                    <th class="py-3.5 px-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-xs text-ink">
                <template x-for="staff in filteredStaff" :key="staff.id">
                    <tr class="hover:bg-card-tint transition-all">
                        <!-- Name with Avatar -->
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xs font-bold w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#161615] flex items-center justify-center overflow-hidden">
                                    <template x-if="staff.profileImageUrl">
                                        <img :src="staff.profileImageUrl" :alt="staff.name" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!staff.profileImageUrl">
                                        <span x-text="staff.initials"></span>
                                    </template>
                                </span>
                                <span class="font-bold text-ink" x-text="staff.name"></span>
                            </div>
                        </td>

                        <!-- Role -->
                        <td class="py-3.5 px-5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-700 dark:bg-[#161615] dark:text-gray-300" x-text="staff.roleLabel"></span>
                        </td>

                        <!-- Phone -->
                        <td class="py-3.5 px-5 font-medium text-ink" x-text="staff.phone || 'N/A'"></td>

                        <!-- Email -->
                        <td class="py-3.5 px-5 text-muted" x-text="staff.email"></td>

                        <!-- Status badge -->
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full"
                                      :class="{
                                          'bg-teal': staff.status === 'active',
                                          'bg-orange': staff.status === 'suspended',
                                          'bg-danger': staff.status === 'inactive'
                                      }"></span>
                                <span class="font-semibold text-ink" x-text="staff.statusLabel"></span>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-5 text-right">
                            <div class="inline-flex items-center gap-1">
                                <button
                                    @click="openEditModal(staff)"
                                    class="p-1.5 rounded-lg text-blue hover:bg-blue/5 transition-all cursor-pointer inline-flex items-center"
                                    title="Edit Staff Details"
                                >
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button
                                    x-show="staff.status === 'active'"
                                    @click="deactivateStaff(staff)"
                                    class="p-1.5 rounded-lg text-danger hover:bg-danger/5 transition-all cursor-pointer inline-flex items-center"
                                    title="Deactivate Staff"
                                >
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr x-show="filteredStaff.length === 0">
                    <td colspan="6" class="py-10 text-center text-muted text-xs font-semibold">No staff members match your filters.</td>
                </tr>
            </tbody>
        </table>
    </x-card>

    <!-- Modal Form (Add/Edit Staff) -->
    <div
        x-show="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        style="display: none;"
    >
        <x-card
            class="w-full max-w-lg p-6 relative overflow-hidden"
            variant="default"
            @click.outside="isModalOpen = false"
        >
            <!-- Close Button -->
            <button
                @click="isModalOpen = false"
                class="absolute right-4 top-4 p-1.5 rounded-lg text-muted hover:bg-card-tint hover:text-ink cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <h3 class="text-lg font-bold text-ink mb-2" x-text="editingStaff ? 'Edit Staff Profile' : 'Add New Staff Member'"></h3>
            <p class="text-xs text-muted mb-6" x-text="editingStaff ? 'Update photo, role, contact info, or credentials.' : 'Create a login for a manager, waiter, kitchen staff, or cashier.'"></p>

            <div class="space-y-4">
                <p x-show="formError" x-text="formError" class="text-xs font-semibold text-danger bg-danger/5 border border-danger/20 rounded-lg px-3 py-2"></p>

                <div class="rounded-xl border border-border bg-card-tint p-4 flex items-center gap-4">
                    <div class="h-16 w-16 rounded-xl border border-border bg-white dark:bg-[#161615] overflow-hidden flex items-center justify-center text-sm font-bold text-ink shrink-0">
                        <template x-if="profileImagePreview()">
                            <img :src="profileImagePreview()" alt="Profile preview" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!profileImagePreview()">
                            <span x-text="formInitials()"></span>
                        </template>
                    </div>
                    <div class="min-w-0 flex-1">
                        <label class="block text-xs font-bold text-ink mb-1.5">Profile Image</label>
                        <p class="text-[11px] text-muted">JPG, PNG, GIF, or WEBP up to 2MB.</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <label class="inline-flex items-center gap-1.5 rounded-xl bg-orange hover:bg-orange/95 px-3 py-2 text-[11px] font-bold text-white shadow-md shadow-orange/15 transition-all cursor-pointer select-none">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                </svg>
                                <span x-text="profileImagePreview() ? 'Change Photo' : 'Upload Photo'"></span>
                                <input
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @click="$event.target.value = null"
                                    @change="handleProfileImageChange($event)"
                                >
                            </label>
                            <button
                                type="button"
                                x-show="profileImagePreview()"
                                @click="removeProfileImage()"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-border px-3 py-2 text-[11px] font-bold text-muted hover:text-danger hover:border-danger/30 transition-all cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                <span>Remove</span>
                            </button>
                        </div>
                        <p x-show="profileImageName" x-text="profileImageName" class="mt-2 truncate text-[10px] font-semibold text-muted"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-ink mb-1.5">Staff Name *</label>
                    <input
                        type="text"
                        x-model="form.name"
                        placeholder="Enter full name"
                        class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Role *</label>
                        <select
                            x-model="form.role"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink"
                        >
                            <template x-for="[value, label] in Object.entries(roles)" :key="value">
                                <option :value="value" x-text="label"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Status *</label>
                        <select
                            x-model="form.status"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink"
                        >
                            <template x-for="status in statuses" :key="status">
                                <option :value="status" x-text="status.charAt(0).toUpperCase() + status.slice(1)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Phone Number</label>
                        <input
                            type="text"
                            x-model="form.phone"
                            placeholder="Mobile number"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Email ID *</label>
                        <input
                            type="email"
                            x-model="form.email"
                            placeholder="Email address"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5" x-text="editingStaff ? 'New Password' : 'Password *'"></label>
                        <input
                            type="password"
                            x-model="form.password"
                            placeholder="Min. 8 characters"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Confirm Password</label>
                        <input
                            type="password"
                            x-model="form.password_confirmation"
                            placeholder="Repeat password"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                        />
                    </div>
                </div>
                <p x-show="editingStaff" class="text-[10px] text-muted">Leave password blank to keep the current password.</p>
            </div>

            <!-- Submit actions -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-border">
                <button
                    @click="isModalOpen = false"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-muted hover:text-ink cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    @click="saveStaff()"
                    :disabled="isSaving"
                    class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-2 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer disabled:opacity-60"
                >
                    <span x-text="isSaving ? 'Saving...' : (editingStaff ? 'Update Profile' : 'Save Profile')"></span>
                </button>
            </div>
        </x-card>
    </div>
</div>

<script>
    function staffManager({ initialStaff, roles, statuses, csrf }) {
        return {
            staffMembers: initialStaff,
            roles,
            statuses,
            searchQuery: '',
            selectedRole: 'all',
            isModalOpen: false,
            isSaving: false,
            editingStaff: null,
            formError: '',
            toast: '',
            profileImageFile: null,
            profileImageName: '',
            profileImageObjectUrl: '',
            form: {
                name: '',
                role: 'waiter',
                phone: '',
                email: '',
                status: 'active',
                password: '',
                password_confirmation: '',
                profileImageUrl: '',
                remove_profile_image: false,
            },

            get filteredStaff() {
                const query = this.searchQuery.trim().toLowerCase();

                return this.staffMembers.filter((staff) => {
                    const matchesRole = this.selectedRole === 'all' || staff.role === this.selectedRole;
                    const matchesQuery = !query
                        || staff.name.toLowerCase().includes(query)
                        || staff.email.toLowerCase().includes(query)
                        || (staff.phone || '').toLowerCase().includes(query);

                    return matchesRole && matchesQuery;
                });
            },

            openCreateModal() {
                this.editingStaff = null;
                this.formError = '';
                this.resetProfileImageState();
                this.form = {
                    name: '',
                    role: 'waiter',
                    phone: '',
                    email: '',
                    status: 'active',
                    password: '',
                    password_confirmation: '',
                    profileImageUrl: '',
                    remove_profile_image: false,
                };
                this.isModalOpen = true;
            },

            openEditModal(staff) {
                this.editingStaff = staff;
                this.formError = '';
                this.resetProfileImageState();
                this.form = {
                    name: staff.name,
                    role: staff.role,
                    phone: staff.phone || '',
                    email: staff.email,
                    status: staff.status,
                    password: '',
                    password_confirmation: '',
                    profileImageUrl: staff.profileImageUrl || '',
                    remove_profile_image: false,
                };
                this.isModalOpen = true;
            },

            showToast(message) {
                this.toast = message;
                setTimeout(() => { this.toast = ''; }, 4000);
            },

            profileImagePreview() {
                if (this.form.remove_profile_image) {
                    return '';
                }

                return this.profileImageObjectUrl || this.form.profileImageUrl;
            },

            formInitials() {
                return this.initialsFor(this.form.name);
            },

            initialsFor(name) {
                const initials = (name || '')
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean)
                    .slice(0, 2)
                    .map((part) => part.charAt(0))
                    .join('')
                    .toUpperCase();

                return initials || 'S';
            },

            handleProfileImageChange(event) {
                const file = event.target.files[0];

                if (!file) {
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    this.formError = 'Please choose a valid image file.';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    this.formError = 'Profile image must be 2MB or smaller.';
                    return;
                }

                if (this.profileImageObjectUrl) {
                    URL.revokeObjectURL(this.profileImageObjectUrl);
                }

                this.profileImageFile = file;
                this.profileImageName = file.name;
                this.profileImageObjectUrl = URL.createObjectURL(file);
                this.form.remove_profile_image = false;
                this.formError = '';
            },

            removeProfileImage() {
                if (this.profileImageObjectUrl) {
                    URL.revokeObjectURL(this.profileImageObjectUrl);
                }

                this.profileImageFile = null;
                this.profileImageName = '';
                this.profileImageObjectUrl = '';
                this.form.profileImageUrl = '';
                this.form.remove_profile_image = true;
            },

            resetProfileImageState() {
                if (this.profileImageObjectUrl) {
                    URL.revokeObjectURL(this.profileImageObjectUrl);
                }

                this.profileImageFile = null;
                this.profileImageName = '';
                this.profileImageObjectUrl = '';
            },

            buildStaffFormData() {
                const formData = new FormData();

                formData.append('name', this.form.name);
                formData.append('role', this.form.role);
                formData.append('phone', this.form.phone);
                formData.append('email', this.form.email);
                formData.append('status', this.form.status);
                formData.append('password', this.form.password);
                formData.append('password_confirmation', this.form.password_confirmation);

                if (this.profileImageFile) {
                    formData.append('profile_image', this.profileImageFile);
                }

                if (this.editingStaff) {
                    formData.append('_method', 'PUT');

                    if (this.form.remove_profile_image) {
                        formData.append('remove_profile_image', '1');
                    }
                }

                return formData;
            },

            async saveStaff() {
                if (!this.form.name.trim() || !this.form.email.trim()) {
                    this.formError = 'Please enter Name and Email.';
                    return;
                }

                if (!this.editingStaff && !this.form.password) {
                    this.formError = 'Password is required for new staff members.';
                    return;
                }

                if (this.form.password && this.form.password !== this.form.password_confirmation) {
                    this.formError = 'Password confirmation does not match.';
                    return;
                }

                this.isSaving = true;
                this.formError = '';

                const url = this.editingStaff ? `/staff/${this.editingStaff.id}` : '/staff';
                const method = 'POST';

                try {
                    const response = await fetch(url, {
                        method,
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: this.buildStaffFormData(),
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        this.formError = payload.message || 'Unable to save staff member.';
                        this.isSaving = false;
                        return;
                    }

                    if (this.editingStaff) {
                        const index = this.staffMembers.findIndex((s) => s.id === payload.staff.id);
                        if (index !== -1) {
                            this.staffMembers.splice(index, 1, payload.staff);
                        }
                    } else {
                        this.staffMembers.push(payload.staff);
                    }

                    this.isModalOpen = false;
                    this.editingStaff = null;
                    this.showToast(payload.message);
                } catch (e) {
                    this.formError = 'Something went wrong. Please try again.';
                } finally {
                    this.isSaving = false;
                }
            },

            async deactivateStaff(staff) {
                if (!confirm(`Deactivate ${staff.name}? They will lose dashboard/app access.`)) {
                    return;
                }

                try {
                    const response = await fetch(`/staff/${staff.id}`, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        alert(payload.message || 'Unable to deactivate staff member.');
                        return;
                    }

                    const index = this.staffMembers.findIndex((s) => s.id === payload.staff.id);
                    if (index !== -1) {
                        this.staffMembers.splice(index, 1, payload.staff);
                    }

                    this.showToast(payload.message);
                } catch (e) {
                    alert('Something went wrong. Please try again.');
                }
            },
        };
    }
</script>
@endsection
