@extends('layouts.app')

@section('title', 'Service Points Map')

@section('content')
<div 
    x-data="{
        activeFloor: 'dining-hall',
        drawerOpen: false,
        selectedTableId: 'T01',
        createModalOpen: false,
        statusFilter: 'all',

        // Create Service Point Form Model
        newId: '',
        newName: '',
        newFloor: 'dining-hall',
        newSeats: 4,
        newShape: 'rect',
        newWaiter: 'Marcus V.',
        
        tables: [
            // Dining Hall
            { id: 'T01', name: 'Table 1', floor: 'dining-hall', seats: 2, occupiedSeats: 0, shape: 'round', status: 'available', waiter: 'Marcus V.', order: null, amount: '₹ 0', items: [] },
            { id: 'T02', name: 'Table 2', floor: 'dining-hall', seats: 4, occupiedSeats: 3, shape: 'rect', status: 'occupied', waiter: 'Marcus V.', order: '#KFC1255', amount: '₹ 395', items: ['1x Zinger Burger', '1x Popcorn (L)'] },
            { id: 'T03', name: 'Table 3', floor: 'dining-hall', seats: 4, occupiedSeats: 4, shape: 'rect', status: 'occupied', waiter: 'Clarissa G.', order: '#KFC1252', amount: '₹ 760', items: ['1x Smoky Red Bucket', '2x Garlic Bread'] },
            { id: 'T04', name: 'Table 4', floor: 'dining-hall', seats: 6, occupiedSeats: 5, shape: 'rect', status: 'bill-pending', waiter: 'Marcus V.', order: '#KFC1250', amount: '₹ 1,230', items: ['2x Hot & Crispy Bucket', '3x Pepsi'] },
            { id: 'T05', name: 'Table 5', floor: 'dining-hall', seats: 2, occupiedSeats: 0, shape: 'round', status: 'available', waiter: 'Hostess Kelly', order: null, amount: '₹ 0', items: [] },
            { id: 'T06', name: 'Table 6', floor: 'dining-hall', seats: 8, occupiedSeats: 0, shape: 'rect', status: 'available', waiter: 'Clarissa G.', order: null, amount: '₹ 0', items: [] },

            // Terrace Cafe
            { id: 'TC1', name: 'Terrace 1', floor: 'cafe-terrace', seats: 2, occupiedSeats: 2, shape: 'round', status: 'occupied', waiter: 'Elena R.', order: null, amount: '₹ 410', items: ['2x Frappuccino', '1x Butter Croissant'] },
            { id: 'TC2', name: 'Terrace 2', floor: 'cafe-terrace', seats: 2, occupiedSeats: 0, shape: 'round', status: 'available', waiter: 'Elena R.', order: null, amount: '₹ 0', items: [] },
            { id: 'TC3', name: 'Terrace 3', floor: 'cafe-terrace', seats: 4, occupiedSeats: 0, shape: 'rect', status: 'available', waiter: 'Hostess Kelly', order: null, amount: '₹ 0', items: [] },
            
            // Lounge Cabanas
            { id: 'C01', name: 'Cabana 1', floor: 'lounge-cabanas', seats: 6, occupiedSeats: 6, shape: 'rect', status: 'occupied', waiter: 'Vikram S.', order: null, amount: '₹ 2,450', items: ['3x Pitcher Mojito', '2x Platter Wings'] },
            { id: 'C02', name: 'Cabana 2', floor: 'lounge-cabanas', seats: 6, occupiedSeats: 0, shape: 'rect', status: 'available', waiter: 'Vikram S.', order: null, amount: '₹ 0', items: [] },

            // Hotel Wing A Rooms
            { id: 'R301', name: 'Room 301', floor: 'hotel-wing-a', seats: 2, occupiedSeats: 0, shape: 'bed', status: 'available', waiter: 'Housekeeping', order: null, amount: '₹ 0', items: [] },
            { id: 'R302', name: 'Room 302', floor: 'hotel-wing-a', seats: 4, occupiedSeats: 4, shape: 'bed', status: 'occupied', waiter: 'Room Service', order: '#KFC1238', amount: '₹ 740', items: ['2x Seafood Fettuccine', '1x Garlic Bread'] },
            { id: 'R303', name: 'Room 303', floor: 'hotel-wing-a', seats: 2, occupiedSeats: 0, shape: 'bed', status: 'available', waiter: 'Front Desk', order: null, amount: '₹ 0', items: [] }
        ],

        get selectedTable() {
            return this.tables.find(t => t.id === this.selectedTableId);
        },

        get stats() {
            let activeList = this.tables.filter(t => t.floor === this.activeFloor);
            return {
                total: activeList.length,
                available: activeList.filter(t => t.status === 'available').length,
                occupied: activeList.filter(t => t.status === 'occupied' || t.status === 'bill-pending').length,
                seatingCapacity: activeList.reduce((acc, t) => acc + t.seats, 0)
            };
        },

        selectTable(id) {
            this.selectedTableId = id;
            this.drawerOpen = true;
        },

        addMockItem(itemName, price) {
            let t = this.selectedTable;
            if (t) {
                if (t.status === 'available') {
                    t.status = 'occupied';
                    t.order = '#KFC' + Math.floor(Math.random() * 8000 + 1000);
                }
                t.items.push('1x ' + itemName);
                let currentNum = parseInt(t.amount.replace(/[^0-9]/g, '')) || 0;
                t.amount = '₹ ' + (currentNum + price);
            }
        },

        createServicePoint() {
            if (!this.newId) {
                alert('Please enter a unique Room / Service Point ID.');
                return;
            }
            // Check uniqueness
            if (this.tables.find(t => t.id === this.newId)) {
                alert('ID already exists. Please use a unique Code.');
                return;
            }

            // Auto-detect shape/type if hotel zone is chosen
            let shape = this.newShape;
            if (this.newFloor === 'hotel-wing-a') {
                shape = 'bed';
            }

            this.tables.push({
                id: this.newId,
                name: this.newName || ((this.newFloor === 'hotel-wing-a' ? 'Room ' : 'Table ') + this.newId),
                floor: this.newFloor,
                seats: parseInt(this.newSeats),
                occupiedSeats: 0,
                shape: shape,
                status: 'available',
                waiter: this.newFloor === 'hotel-wing-a' ? 'Room Service' : this.newWaiter,
                order: null,
                amount: '₹ 0',
                items: []
            });
            
            // Switch view automatically to show the created zone
            this.activeFloor = this.newFloor;

            // Reset form
            this.newId = '';
            this.newName = '';
            this.createModalOpen = false;
        }
    }"
    class="space-y-6"
>
    <!-- Page Header & Floor Tabs Switcher -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-ink">Service Points Map</h1>
            <p class="text-xs text-muted mt-0.5">Live status, geometric table & room layouts, and direct order links.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Section / Floor Switcher -->
            <div class="flex items-center gap-1 bg-card-tint border border-border p-1 rounded-xl">
                <button 
                    @click="activeFloor = 'dining-hall'"
                    :class="activeFloor === 'dining-hall' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded px-3 py-1.5 text-[10px] cursor-pointer transition-all"
                >
                    Main Dining Hall
                </button>
                <button 
                    @click="activeFloor = 'cafe-terrace'"
                    :class="activeFloor === 'cafe-terrace' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded px-3 py-1.5 text-[10px] cursor-pointer transition-all"
                >
                    Terrace Cafe
                </button>
                <button 
                    @click="activeFloor = 'lounge-cabanas'"
                    :class="activeFloor === 'lounge-cabanas' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded px-3 py-1.5 text-[10px] cursor-pointer transition-all"
                >
                    Lounge Cabanas
                </button>
                <button 
                    @click="activeFloor = 'hotel-wing-a'"
                    :class="activeFloor === 'hotel-wing-a' ? 'bg-white text-ink shadow-xs font-bold' : 'text-muted hover:text-ink font-semibold'"
                    class="rounded px-3 py-1.5 text-[10px] cursor-pointer transition-all"
                >
                    🏨 Hotel Wing A
                </button>
            </div>

            <!-- Status Filter Dropdown -->
            <select x-model="statusFilter" class="rounded-xl border border-border bg-card px-3 py-1.5 text-[10px] font-bold text-ink outline-none cursor-pointer">
                <option value="all">All Statuses</option>
                <option value="available">Only Vacant</option>
                <option value="occupied">Only Occupied</option>
            </select>

            <!-- Create Service Point Trigger Button -->
            <button 
                @click="createModalOpen = true"
                class="rounded-xl bg-orange hover:bg-orange/95 px-4.5 py-2 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer active:scale-95 transition-all"
            >
                + Add Point / Room
            </button>
        </div>
    </div>

    <!-- Active Zone Statistics Header Roster -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[9px] font-bold text-muted uppercase" x-text="activeFloor === 'hotel-wing-a' ? 'Total Rooms' : 'Total Tables'">Total Service Points</span>
                <h4 class="text-base font-black text-ink mt-0.5" x-text="stats.total">0</h4>
            </div>
            <span class="text-xs">🗺️</span>
        </div>
        <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[9px] font-bold text-muted uppercase" x-text="activeFloor === 'hotel-wing-a' ? 'Vacant' : 'Available'">Available</span>
                <h4 class="text-base font-black text-teal mt-0.5" x-text="stats.available">0</h4>
            </div>
            <span class="text-xs">🟢</span>
        </div>
        <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[9px] font-bold text-muted uppercase" x-text="activeFloor === 'hotel-wing-a' ? 'Checked In' : 'Occupied'">Occupied</span>
                <h4 class="text-base font-black text-orange mt-0.5" x-text="stats.occupied">0</h4>
            </div>
            <span class="text-xs">🟠</span>
        </div>
        <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-xs col-span-2 lg:col-span-1">
            <div>
                <span class="text-[9px] font-bold text-muted uppercase" x-text="activeFloor === 'hotel-wing-a' ? 'Room Capacity' : 'Seating Capacity'">Seating Capacity</span>
                <h4 class="text-base font-black text-ink mt-0.5" x-text="`${stats.seatingCapacity} Pax`">0 Pax</h4>
            </div>
            <span class="text-xs">🪑</span>
        </div>
    </div>

    <!-- Status Legend -->
    <div class="flex flex-wrap gap-3.5 text-[9px] font-bold text-ink uppercase tracking-wider">
        <div class="flex items-center gap-1.5 rounded-lg bg-card border border-border px-2.5 py-1.5 shadow-xs">
            <span class="w-2.5 h-2.5 rounded-full bg-border border border-slate-300"></span>
            <span x-text="activeFloor === 'hotel-wing-a' ? 'Vacant / Available' : 'Available'">Available</span>
        </div>
        <div class="flex items-center gap-1.5 rounded-lg bg-card border border-border px-2.5 py-1.5 shadow-xs">
            <span class="w-2.5 h-2.5 rounded-full bg-orange animate-pulse"></span>
            <span>Occupied</span>
        </div>
        <div class="flex items-center gap-1.5 rounded-lg bg-card border border-border px-2.5 py-1.5 shadow-xs">
            <span class="w-2.5 h-2.5 rounded-full bg-warning"></span>
            <span x-text="activeFloor === 'hotel-wing-a' ? 'Service Pending' : 'Bill Pending'">Bill Pending</span>
        </div>
    </div>

    <!-- Visual Interactive Roster floor layout of Tables & Rooms -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <template x-for="t in tables" :key="t.id">
            <div 
                x-show="
                    t.floor === activeFloor && 
                    (statusFilter === 'all' || 
                     (statusFilter === 'available' && t.status === 'available') || 
                     (statusFilter === 'occupied' && (t.status === 'occupied' || t.status === 'bill-pending')))
                "
                @click="selectTable(t.id)"
                class="rounded-xl border bg-card p-4.5 cursor-pointer hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 transition-all flex flex-col justify-between h-48 select-none relative overflow-hidden"
                :class="{
                    'border-border hover:border-slate-400': t.status === 'available',
                    'border-orange/20 bg-orange/[0.01] hover:border-orange/40': t.status === 'occupied',
                    'border-warning/30 bg-warning/[0.01] hover:border-warning/50': t.status === 'bill-pending'
                }"
            >
                <!-- Card Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-black text-ink" x-text="t.name"></span>
                        <span class="block text-[8px] text-muted mt-0.5" x-text="`Staff: ${t.waiter}`"></span>
                    </div>
                    <span class="text-[8px] font-bold text-muted bg-card-tint border border-border px-1.5 py-0.5 rounded-md" x-text="`${t.seats} Pax`"></span>
                </div>

                <!-- Geometric Seating or Bed layout -->
                <div class="my-3 flex items-center justify-center h-16">
                    <div class="relative flex items-center justify-center">
                        
                        <!-- OPTION A: Round/Rect Table layout -->
                        <template x-if="t.shape !== 'bed'">
                            <div class="relative flex items-center justify-center">
                                <!-- Seating Chairs -->
                                <div class="absolute -top-3.5 w-2.5 h-2.5 rounded-full border border-border" :class="t.status === 'available' ? 'bg-slate-200' : 'bg-orange'"></div>
                                <div class="absolute -bottom-3.5 w-2.5 h-2.5 rounded-full border border-border" :class="t.status === 'available' ? 'bg-slate-200' : 'bg-orange'"></div>
                                <div class="absolute -left-3.5 w-2.5 h-2.5 rounded-full border border-border" :class="t.status === 'available' ? 'bg-slate-200' : 'bg-orange'"></div>
                                <div class="absolute -right-3.5 w-2.5 h-2.5 rounded-full border border-border" :class="t.status === 'available' ? 'bg-slate-200' : 'bg-orange'"></div>
                                
                                <div 
                                    :class="[
                                        t.shape === 'round' ? 'rounded-full' : 'rounded-md',
                                        t.status === 'available' ? 'bg-card border-2 border-border' : 'bg-orange/10 border-2 border-orange'
                                    ]"
                                    class="w-10 h-10 flex items-center justify-center text-[10px] font-black text-ink shadow-xs"
                                    x-text="t.id"
                                ></div>
                            </div>
                        </template>

                        <!-- OPTION B: Room Bed Layout -->
                        <template x-if="t.shape === 'bed'">
                            <div class="relative w-12 h-14 border-2 rounded-lg flex flex-col items-center justify-between p-1 bg-card shadow-xs"
                                 :class="t.status === 'available' ? 'border-border' : 'border-orange bg-orange/[0.03]'"
                            >
                                <div class="w-8 h-3.5 rounded border border-border bg-slate-100 flex justify-center items-center text-[7px] text-muted font-bold"
                                     :class="t.status === 'available' ? 'bg-slate-100' : 'bg-orange/15 text-orange'">
                                    PILLOW
                                </div>
                                <div class="w-10 h-6 border-t border-dashed border-border/80 text-[8px] font-black text-ink flex items-center justify-center"
                                     :class="t.status === 'available' ? 'text-slate-400' : 'text-orange'"
                                     x-text="t.id"
                                ></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Card Footer Status Pill -->
                <div class="pt-2 border-t border-border/40 flex justify-between items-center">
                    <span 
                        class="text-[8px] font-extrabold uppercase tracking-wider"
                        :class="{
                            'text-slate-400': t.status === 'available',
                            'text-orange': t.status === 'occupied',
                            'text-warning animate-pulse': t.status === 'bill-pending'
                        }"
                        x-text="t.status === 'available' && t.floor === 'hotel-wing-a' ? 'vacant' : t.status.replace('-', ' ')"
                    ></span>
                    
                    <span class="text-[9px] font-bold text-ink" x-text="t.amount !== '₹ 0' ? t.amount : ''"></span>
                </div>
            </div>
        </template>
    </div>

    <!-- Sliding Sidebar Checkout Drawer (Alpine control) -->
    <div 
        x-show="drawerOpen" 
        class="fixed inset-y-0 right-0 w-96 bg-card border-l border-border shadow-2xl z-50 flex flex-col justify-between"
        style="display: none;"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
    >
        <template x-if="selectedTable">
            <div class="flex flex-col h-full justify-between">
                <!-- Drawer Header -->
                <div class="p-6 border-b border-border bg-card-tint flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-ink" x-text="`${selectedTable.floor === 'hotel-wing-a' ? 'Hotel Room' : 'Table'} ${selectedTable.id} (${selectedTable.name})`"></h3>
                        <span class="text-xs text-muted" x-text="`Assigned Roster: ${selectedTable.waiter}`"></span>
                    </div>
                    <button 
                        @click="drawerOpen = false" 
                        class="rounded-xl border border-border bg-card p-1.5 text-muted hover:text-ink cursor-pointer font-bold"
                    >
                        ✕
                    </button>
                </div>

                <!-- Drawer Content -->
                <div class="p-6 flex-1 overflow-y-auto space-y-6">
                    
                    <!-- Table-Specific QR Order Scanner Card -->
                    <div class="bg-card border border-border/80 rounded-xl p-4 space-y-3 shadow-xs">
                        <div class="flex justify-between items-center pb-2 border-b border-border/60">
                            <span class="text-[9px] font-extrabold text-muted uppercase tracking-wider">📱 QR Order Scanner</span>
                            <span class="text-[8px] font-bold text-teal bg-teal/5 px-2 py-0.5 rounded-lg border border-teal/10">Active Scan</span>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <!-- QR Visual Mockup -->
                            <div class="w-18 h-18 bg-card-tint border border-border rounded-lg flex flex-col items-center justify-center p-1.5 shrink-0 relative">
                                <div class="w-full h-full border border-ink/40 rounded flex flex-wrap gap-0.5 p-0.5 bg-white">
                                    <div class="w-3.5 h-3.5 bg-ink rounded-xs"></div>
                                    <div class="w-3.5 h-3.5 bg-ink rounded-xs opacity-20"></div>
                                    <div class="w-3.5 h-3.5 bg-ink rounded-xs opacity-50"></div>
                                    <div class="w-3.5 h-3.5 bg-ink rounded-xs"></div>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0 space-y-1">
                                <span class="block text-[8px] font-bold text-muted uppercase tracking-wider">Scan-to-Order Link</span>
                                <input 
                                    type="text" 
                                    readonly
                                    :value="`http://127.0.0.1:8004/menu?${selectedTable.floor === 'hotel-wing-a' ? 'room' : 'table'}=${selectedTable.id}`"
                                    class="w-full bg-card-tint border border-border/65 rounded-lg px-2 py-1 text-[8px] font-mono text-ink outline-none select-all"
                                >
                            </div>
                        </div>

                        <!-- Printing and sharing triggers -->
                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-border/40">
                            <button 
                                @click="alert('Printing QR code sticker card for ' + selectedTable.name)"
                                class="rounded-lg border border-border bg-card py-1.5 text-[9px] font-bold text-ink hover:bg-card-tint cursor-pointer active:scale-95 transition-all"
                            >
                                🖨️ Print QR Tag
                            </button>
                            <button 
                                @click="
                                    navigator.clipboard.writeText(`http://127.0.0.1:8004/menu?${selectedTable.floor === 'hotel-wing-a' ? 'room' : 'table'}=${selectedTable.id}`);
                                    alert('Link copied to clipboard!');
                                "
                                class="rounded-lg border border-orange/10 bg-orange/5 text-orange py-1.5 text-[9px] font-bold hover:bg-orange/10 cursor-pointer active:scale-95 transition-all"
                            >
                                🔗 Copy Link
                            </button>
                        </div>
                    </div>

                    <!-- Active Bill Log and Food items adding -->
                    <div class="space-y-3.5" x-show="selectedTable.status !== 'available'">
                        <div class="flex justify-between items-center">
                            <span class="text-[9px] font-extrabold text-muted uppercase tracking-wider" x-text="selectedTable.floor === 'hotel-wing-a' ? 'Room Service Food Orders' : 'Items on Table'">Orders Detail</span>
                            <span class="text-[9px] font-extrabold text-orange" x-text="selectedTable.order"></span>
                        </div>

                        <div class="rounded-xl border border-border p-3.5 bg-card-tint space-y-2.5">
                            <div class="space-y-1.5 max-h-36 overflow-y-auto">
                                <template x-for="item in selectedTable.items">
                                    <div class="flex justify-between items-center text-xs text-muted font-semibold">
                                        <span x-text="item"></span>
                                        <span class="text-[9px] text-teal">✓</span>
                                    </div>
                                </template>
                                <template x-if="selectedTable.items.length === 0">
                                    <span class="text-xs text-slate-400 block text-center py-2">No active orders added.</span>
                                </template>
                            </div>
                            <div class="h-px bg-border my-2"></div>
                            <div class="flex justify-between text-xs font-bold text-ink">
                                <span>Running Balance</span>
                                <span x-text="selectedTable.amount"></span>
                            </div>
                        </div>

                        <!-- Menu Quick Adds Roster -->
                        <div class="space-y-2">
                            <span class="text-[9px] font-extrabold text-muted uppercase tracking-wider block" x-text="selectedTable.floor === 'hotel-wing-a' ? 'Order Room Service' : 'Quick Add Items'">Quick Add Items</span>
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="addMockItem('Zinger Burger', 199)" class="rounded-lg border border-border bg-card px-2.5 py-1.5 text-[10px] font-bold text-ink hover:border-orange hover:text-orange cursor-pointer">🍔 Zinger Burger (₹199)</button>
                                <button @click="addMockItem('8 Pc Bucket', 768)" class="rounded-lg border border-border bg-card px-2.5 py-1.5 text-[10px] font-bold text-ink hover:border-orange hover:text-orange cursor-pointer">🍗 Fried Bucket (₹768)</button>
                                <button @click="addMockItem('Seafood Fettuccine', 620)" class="rounded-lg border border-border bg-card px-2.5 py-1.5 text-[10px] font-bold text-ink hover:border-orange hover:text-orange cursor-pointer">🍝 Fettuccine (₹620)</button>
                                <button @click="addMockItem('Garlic Bread', 120)" class="rounded-lg border border-border bg-card px-2.5 py-1.5 text-[10px] font-bold text-ink hover:border-orange hover:text-orange cursor-pointer">🍞 Garlic Bread (₹120)</button>
                            </div>
                        </div>

                        <!-- Table Status Advancer -->
                        <div class="pt-4.5 border-t border-border">
                            <button 
                                @click="selectedTable.status = 'bill-pending'"
                                class="w-full rounded-lg border border-warning/30 bg-warning/5 text-warning py-2 text-[10px] font-bold hover:bg-warning/10 cursor-pointer active:scale-95 transition-all"
                                x-text="selectedTable.floor === 'hotel-wing-a' ? 'Request Room Bill' : 'Request Table Bill'"
                            >
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer Actions -->
                <div class="p-6 border-t border-border bg-card-tint space-y-2.5">
                    <!-- Quick Activation Trigger Button (If vacant) -->
                    <button 
                        x-show="selectedTable.status === 'available'"
                        @click="selectedTable.status = 'occupied'; selectedTable.order = '#KFC' + Math.floor(Math.random() * 8000 + 1000); selectedTable.items = ['1x Zinger Burger']; selectedTable.amount = '₹ 199'; drawerOpen = false"
                        class="w-full rounded-xl bg-orange hover:bg-orange/95 py-3 text-xs font-bold text-white shadow-md shadow-orange/15 cursor-pointer"
                        x-text="selectedTable.floor === 'hotel-wing-a' ? '⚡ Activate Room (Check In)' : '⚡ Activate Table (Occupy)'"
                    >
                    </button>

                    <!-- Checkout Trigger Button (If active) -->
                    <button 
                        x-show="selectedTable.status !== 'available'"
                        @click="alert('Settling checkout payment...'); selectedTable.status = 'available'; selectedTable.items = []; selectedTable.amount = '₹ 0'; selectedTable.order = null; drawerOpen = false"
                        class="w-full rounded-xl bg-teal hover:bg-teal/95 py-3 text-xs font-bold text-white shadow-md shadow-teal/15 cursor-pointer"
                        x-text="selectedTable.floor === 'hotel-wing-a' ? `Check Out Room & Settle Bill (${selectedTable.amount})` : `Checkout Table & Settle Bill (${selectedTable.amount})`"
                    >
                    </button>
                    <button 
                        @click="drawerOpen = false"
                        class="w-full rounded-xl border border-border bg-card py-3 text-xs font-bold text-ink hover:bg-card-tint cursor-pointer"
                        x-text="selectedTable.floor === 'hotel-wing-a' ? 'Dismiss Room' : 'Dismiss Table'"
                    >
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Create Service Point Modal Wizard Overlay -->
    <div 
        x-show="createModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" 
        style="display: none;"
    >
        <!-- Backdrop background click triggers close -->
        <div class="fixed inset-0 bg-ink/40 transition-opacity" @click="createModalOpen = false"></div>

        <!-- Form Box -->
        <div class="bg-card border border-border rounded-xl p-6 shadow-2xl max-w-sm w-full relative z-10 space-y-4">
            <div class="flex justify-between items-center pb-2.5 border-b border-border">
                <h3 class="text-xs font-black text-ink uppercase tracking-wider">➕ Create Service Point / Room</h3>
                <button @click="createModalOpen = false" class="text-muted hover:text-ink cursor-pointer font-bold">✕</button>
            </div>

            <!-- Form Fields -->
            <div class="space-y-3">
                <!-- ID/Code -->
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Unique Code (e.g. T07, R304)</label>
                    <input type="text" x-model="newId" placeholder="e.g. T07, R304" class="w-full rounded-xl border border-border bg-card px-3 py-2.5 text-xs text-ink outline-none">
                </div>

                <!-- Display Name -->
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Display Name (e.g. Table 7, Room 304)</label>
                    <input type="text" x-model="newName" placeholder="e.g. Table 7, Room 304" class="w-full rounded-xl border border-border bg-card px-3 py-2.5 text-xs text-ink outline-none">
                </div>

                <!-- Zone Selection -->
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Zone / Floor Section</label>
                    <select x-model="newFloor" class="w-full rounded-xl border border-border bg-card px-3 py-2.5 text-xs text-ink outline-none cursor-pointer">
                        <option value="dining-hall">Main Dining Hall</option>
                        <option value="cafe-terrace">Terrace Cafe</option>
                        <option value="lounge-cabanas">Lounge Cabanas</option>
                        <option value="hotel-wing-a">Hotel Wing A (Rooms)</option>
                    </select>
                </div>

                <!-- Seating Capacity & Shape -->
                <div class="grid grid-cols-2 gap-3" x-show="newFloor !== 'hotel-wing-a'">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pax Size</label>
                        <select x-model="newSeats" class="w-full rounded-xl border border-border bg-card px-3 py-2.5 text-xs text-ink outline-none cursor-pointer">
                            <option value="2">2 Pax</option>
                            <option value="4">4 Pax</option>
                            <option value="6">6 Pax</option>
                            <option value="8">8 Pax</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Table Shape</label>
                        <select x-model="newShape" class="w-full rounded-xl border border-border bg-card px-3 py-2.5 text-xs text-ink outline-none cursor-pointer">
                            <option value="round">Round</option>
                            <option value="rect">Rectangle</option>
                        </select>
                    </div>
                </div>

                <!-- Room Occupancy (Only visible if Hotel selected) -->
                <div class="grid grid-cols-1 gap-3" x-show="newFloor === 'hotel-wing-a'">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Room Occupancy / Bed Size</label>
                        <select x-model="newSeats" class="w-full rounded-xl border border-border bg-card px-3 py-2.5 text-xs text-ink outline-none cursor-pointer">
                            <option value="2">Single Bed (2 Guests)</option>
                            <option value="4">Double Bed (4 Guests)</option>
                            <option value="6">Family Suite (6 Guests)</option>
                        </select>
                    </div>
                </div>

                <!-- Server Waiter -->
                <div x-show="newFloor !== 'hotel-wing-a'">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Assigned Waiter</label>
                    <input type="text" x-model="newWaiter" placeholder="e.g. Marcus V." class="w-full rounded-xl border border-border bg-card px-3 py-2.5 text-xs text-ink outline-none">
                </div>
            </div>

            <!-- Modal footer actions -->
            <div class="flex gap-2 pt-2">
                <button 
                    @click="createServicePoint()" 
                    class="flex-1 rounded-xl bg-orange hover:bg-orange/95 py-2.5 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer active:scale-95 transition-all"
                >
                    Create Point
                </button>
                <button 
                    @click="createModalOpen = false" 
                    class="rounded-xl border border-border bg-card px-4 py-2.5 text-xs font-bold text-ink hover:bg-card-tint cursor-pointer active:scale-95 transition-all"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
