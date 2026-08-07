@extends('layouts.app')

@section('title', 'Staff Members')

@section('content')
<div class="space-y-8" x-data="{
    searchQuery: '',
    selectedRole: 'all',
    isModalOpen: false,
    editingStaff: null,
    
    // Form Inputs
    newName: '',
    newRole: 'Waiter',
    newPhone: '',
    newEmail: '',
    newEmployeeCode: '',
    newPin: '',
    newStatus: 'Active',
    newShift: 'Morning Shift',
    
    staffMembers: [
        { id: 'ST-001', name: 'Amit Kumar', role: 'Chef', phone: '9876543210', email: 'amit.k@everythingeasy.com', employeeCode: 'EMP-001', pin: '1234', status: 'Active', avatar: '👨‍🍳', shift: 'Morning Shift' },
        { id: 'ST-002', name: 'Vikram Singh', role: 'Waiter', phone: '9123456789', email: 'vikram.s@everythingeasy.com', employeeCode: 'EMP-002', pin: '2345', status: 'Active', avatar: '🤵', shift: 'Evening Shift' },
        { id: 'ST-003', name: 'Neha Sharma', role: 'Manager', phone: '9012345678', email: 'neha.s@everythingeasy.com', employeeCode: 'EMP-003', pin: '3456', status: 'Active', avatar: '💼', shift: 'Morning Shift' },
        { id: 'ST-004', name: 'Rohit Sen', role: 'Cashier', phone: '9898989898', email: 'rohit.s@everythingeasy.com', employeeCode: 'EMP-004', pin: '4567', status: 'On Break', avatar: '🧑‍💼', shift: 'Morning Shift' },
        { id: 'ST-005', name: 'Sanjay Dutt', role: 'Chef', phone: '9876500123', email: 'sanjay.d@everythingeasy.com', employeeCode: 'EMP-005', pin: '5678', status: 'Inactive', avatar: '👨‍🍳', shift: 'Night Shift' },
        { id: 'ST-006', name: 'Pooja Verma', role: 'Waiter', phone: '9654321098', email: 'pooja.v@everythingeasy.com', employeeCode: 'EMP-006', pin: '6789', status: 'Active', avatar: '🤵', shift: 'Evening Shift' }
    ],

    openCreateModal() {
        this.editingStaff = null;
        this.newName = '';
        this.newRole = 'Waiter';
        this.newPhone = '';
        this.newEmail = '';
        this.newEmployeeCode = '';
        this.newPin = '';
        this.newStatus = 'Active';
        this.newShift = 'Morning Shift';
        this.isModalOpen = true;
    },

    openEditModal(staff) {
        this.editingStaff = staff;
        this.newName = staff.name;
        this.newRole = staff.role;
        this.newPhone = staff.phone;
        this.newEmail = staff.email;
        this.newEmployeeCode = staff.employeeCode;
        this.newPin = staff.pin;
        this.newStatus = staff.status;
        this.newShift = staff.shift;
        this.isModalOpen = true;
    },

    // Add or Update staff
    saveStaff() {
        if (!this.newName.trim() || !this.newPhone.trim() || !this.newEmail.trim() || !this.newEmployeeCode.trim() || !this.newPin.trim()) {
            alert('Please enter Name, Phone Number, Email, Employee Code, and Login PIN.');
            return;
        }

        let avatar = '🧑';
        if (this.newRole === 'Chef') avatar = '👨‍🍳';
        if (this.newRole === 'Waiter') avatar = '🤵';
        if (this.newRole === 'Manager') avatar = '💼';
        if (this.newRole === 'Cashier') avatar = '🧑‍💼';

        if (this.editingStaff) {
            this.editingStaff.name = this.newName;
            this.editingStaff.role = this.newRole;
            this.editingStaff.phone = this.newPhone;
            this.editingStaff.email = this.newEmail;
            this.editingStaff.employeeCode = this.newEmployeeCode.toUpperCase();
            this.editingStaff.pin = this.newPin;
            this.editingStaff.status = this.newStatus;
            this.editingStaff.shift = this.newShift;
            this.editingStaff.avatar = avatar;
        } else {
            const newId = 'ST-' + String(this.staffMembers.length + 1).padStart(3, '0');
            this.staffMembers.push({
                id: newId,
                name: this.newName,
                role: this.newRole,
                phone: this.newPhone,
                email: this.newEmail,
                employeeCode: this.newEmployeeCode.toUpperCase(),
                pin: this.newPin,
                status: this.newStatus,
                avatar: avatar,
                shift: this.newShift
            });
        }

        // Close & Reset
        this.isModalOpen = false;
        this.editingStaff = null;
    }
}">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Staff Directory</h1>
            <p class="text-sm text-muted mt-1">Manage restaurant roles, shifts, profiles, and live attendance metrics.</p>
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
                <button 
                    @click="selectedRole = 'Chef'"
                    :class="selectedRole === 'Chef' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    Chefs
                </button>
                <button 
                    @click="selectedRole = 'Waiter'"
                    :class="selectedRole === 'Waiter' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    Waiters
                </button>
                <button 
                    @click="selectedRole = 'Manager'"
                    :class="selectedRole === 'Manager' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    Managers
                </button>
                <button 
                    @click="selectedRole = 'Cashier'"
                    :class="selectedRole === 'Cashier' ? 'bg-white text-ink shadow-sm font-bold border border-border/30 dark:bg-card-tint' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded-lg px-4 py-2 text-xs transition-all cursor-pointer whitespace-nowrap"
                >
                    Cashiers
                </button>
            </div>

            <!-- Search input -->
            <div class="relative w-full lg:w-72">
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Search by name, phone..." 
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
        <table class="w-full text-left border-collapse min-w-[900px]">
            <thead>
                <tr class="border-b border-border text-xs font-bold text-muted uppercase tracking-wider bg-card-tint">
                    <th class="py-3.5 px-5">Employee Code</th>
                    <th class="py-3.5 px-5">Name</th>
                    <th class="py-3.5 px-5">Role</th>
                    <th class="py-3.5 px-5">Phone</th>
                    <th class="py-3.5 px-5">Email ID</th>
                    <th class="py-3.5 px-5">Shift</th>
                    <th class="py-3.5 px-5">PIN</th>
                    <th class="py-3.5 px-5">Status</th>
                    <th class="py-3.5 px-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-xs text-ink">
                <template x-for="staff in staffMembers" :key="staff.id">
                    <tr 
                        x-show="(selectedRole === 'all' || staff.role === selectedRole) && 
                                (staff.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                 staff.email.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                 staff.employeeCode.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                 staff.phone.includes(searchQuery))"
                        class="hover:bg-card-tint transition-all"
                    >
                        <!-- Code -->
                        <td class="py-3.5 px-5 font-bold uppercase text-ink" x-text="staff.employeeCode">EMP-001</td>
                        
                        <!-- Name with Avatar -->
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-2.5">
                                <span class="text-lg w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#161615] flex items-center justify-center" x-text="staff.avatar"></span>
                                <span class="font-bold text-ink" x-text="staff.name">Amit Kumar</span>
                            </div>
                        </td>
                        
                        <!-- Role -->
                        <td class="py-3.5 px-5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-700 dark:bg-[#161615] dark:text-gray-300" x-text="staff.role">Chef</span>
                        </td>
                        
                        <!-- Phone -->
                        <td class="py-3.5 px-5 font-medium text-ink" x-text="staff.phone">+91 9876543210</td>
                        
                        <!-- Email -->
                        <td class="py-3.5 px-5 text-muted" x-text="staff.email">amit.k@everythingeasy.com</td>
                        
                        <!-- Shift -->
                        <td class="py-3.5 px-5 font-medium text-ink" x-text="staff.shift">Morning Shift</td>
                        
                        <!-- PIN -->
                        <td class="py-3.5 px-5 font-mono font-bold text-ink" x-text="staff.pin">1234</td>
                        
                        <!-- Status badge -->
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full" 
                                      :class="{
                                          'bg-teal': staff.status === 'Active',
                                          'bg-orange': staff.status === 'On Break',
                                          'bg-danger': staff.status === 'Inactive'
                                      }"></span>
                                <span class="font-semibold text-ink" x-text="staff.status === 'On Break' ? 'On Leave' : staff.status">Active</span>
                            </div>
                        </td>
                        
                        <!-- Actions -->
                        <!-- Actions -->
                        <td class="py-3.5 px-5 text-right">
                            <button 
                                @click="openEditModal(staff)"
                                class="p-1.5 rounded-lg text-blue hover:bg-blue/5 transition-all cursor-pointer inline-flex items-center"
                                title="Edit Staff Details"
                            >
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </x-card>

    <!-- Modal Form (Add Staff Wizard overlay) -->
    <div 
        x-show="isModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        style="display: none;"
    >
        <x-card 
            class="w-full max-w-md p-6 relative overflow-hidden" 
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

            <h3 class="text-lg font-bold text-ink mb-2" x-text="editingStaff ? 'Edit Staff Profile' : 'Add New Staff Member'">Add New Staff Member</h3>
            <p class="text-xs text-muted mb-6" x-text="editingStaff ? 'Modify profile info, credentials, and work cycles.' : 'Create a profile to map roles, shift times, and manage work cycles.'">Create a profile to map roles, shift times, and manage work cycles.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-ink mb-1.5">Staff Name *</label>
                    <input 
                        type="text" 
                        x-model="newName" 
                        placeholder="Enter full name" 
                        class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Role *</label>
                        <select 
                            x-model="newRole"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink"
                        >
                            <option value="Chef">Chef</option>
                            <option value="Waiter">Waiter</option>
                            <option value="Manager">Manager</option>
                            <option value="Cashier">Cashier</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Work Shift *</label>
                        <select 
                            x-model="newShift"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink"
                        >
                            <option value="Morning Shift">Morning Shift</option>
                            <option value="Evening Shift">Evening Shift</option>
                            <option value="Night Shift">Night Shift</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Phone Number *</label>
                        <input 
                            type="text" 
                            x-model="newPhone" 
                            placeholder="Mobile number" 
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Email ID *</label>
                        <input 
                            type="email" 
                            x-model="newEmail" 
                            placeholder="Email address" 
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Employee Code *</label>
                        <input 
                            type="text" 
                            x-model="newEmployeeCode" 
                            placeholder="Code" 
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Login PIN *</label>
                        <input 
                            type="text" 
                            x-model="newPin" 
                            maxlength="6"
                            placeholder="PIN" 
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink placeholder:text-muted"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">Status *</label>
                        <select 
                            x-model="newStatus"
                            class="w-full bg-card-tint border border-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-orange text-ink"
                        >
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="On Break">On Leave</option>
                        </select>
                    </div>
                </div>
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
                    class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-2 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer"
                >
                    <span x-text="editingStaff ? 'Update Profile' : 'Save Profile'">Save Profile</span>
                </button>
            </div>
        </x-card>
    </div>
</div>
@endsection
