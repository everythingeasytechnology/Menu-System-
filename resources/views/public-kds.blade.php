<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Operator Console - EverythingEasy ServiceOS</title>
    <!-- Meta Tags for TV displays -->
    <meta name="description" content="Standalone public lobby screen showing preparing and ready orders.">
    <meta name="theme-color" content="#141C27">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-navy-deep text-white font-sans antialiased overflow-hidden">
    <!-- Standalone Alpine App Container with Login State Check -->
    <div 
        x-data="{
            loggedIn: localStorage.getItem('kds_logged_in') === 'true',
            businessId: '',
            pin: '',
            errorMessage: '',
            selectedOrderId: 242,
            activeTab: 'all',
            selectedTable: 'all',
            time: '',
            orders: [
                { id: 242, type: 'dine-in', location: 'Table 12', status: 'new', items: ['1x Dry Aged Ribeye Steak', '2x Parmesan Truffle Fries'], instructions: 'Steak medium rare, fries extra crispy.', time: '5m ago', amount: '$67.00' },
                { id: 243, type: 'takeaway', location: 'Counter 1', status: 'new', items: ['2x Cappuccino', '1x Chocolate Lava Cake'], instructions: 'Warm up the cake.', time: '2m ago', amount: '$25.00' },
                { id: 240, type: 'dine-in', location: 'Table 3', status: 'preparing', items: ['1x Caesar Salad with Salmon', '1x Iced Lemon Tea'], instructions: 'Dressing on the side.', time: '14m ago', amount: '$23.50' },
                { id: 239, type: 'takeaway', location: 'Counter 2', status: 'ready', items: ['1x Turkey Club Sandwich', '1x Fresh Orange Juice'], instructions: 'No mayo.', time: '22m ago', amount: '$19.30' },
                { id: 238, type: 'dine-in', location: 'Room 302', status: 'served', items: ['2x Seafood Fettuccine', '1x Warm Garlic Bread'], instructions: 'Extra parmesan.', time: '1h ago', amount: '$74.00' }
            ],
            
            get selectedOrder() {
                return this.orders.find(o => o.id === this.selectedOrderId);
            },
            
            get totalOrders() { return this.orders.length; },
            get newOrdersCount() { return this.orders.filter(o => o.status === 'new').length; },
            get inKitchenCount() { return this.orders.filter(o => o.status === 'preparing').length; },
            get readyCount() { return this.orders.filter(o => o.status === 'ready').length; },
            get servedCount() { return this.orders.filter(o => o.status === 'served').length; },
            
            // Login Authorization logic
            authorizeTerminal() {
                this.errorMessage = '';
                if (!this.businessId) {
                    this.errorMessage = 'Please enter a valid Business ID.';
                    return;
                }
                // Demo PIN validation (1234)
                if (this.pin === '1234') {
                    localStorage.setItem('kds_logged_in', 'true');
                    this.loggedIn = true;
                } else {
                    this.errorMessage = 'Invalid Business ID or 4-digit PIN.';
                }
            },
            
            // Logout logic
            deauthorizeTerminal() {
                localStorage.removeItem('kds_logged_in');
                this.loggedIn = false;
                this.businessId = '';
                this.pin = '';
                this.errorMessage = '';
            },

            moveStatus(id, newStatus) {
                let order = this.orders.find(o => o.id === id);
                if (order) {
                    order.status = newStatus;
                    if (newStatus === 'preparing') order.time = 'Just now';
                    if (newStatus === 'ready') order.time = 'Ready now';
                }
            },
            
            clearOrder(id) {
                this.orders = this.orders.filter(o => o.id !== id);
                if (this.selectedOrderId === id) {
                    let first = this.orders[0];
                    this.selectedOrderId = first ? first.id : null;
                }
            },
            
            addNewMockOrder() {
                let nextId = Math.max(...this.orders.map(o => o.id), 200) + 1;
                let types = ['dine-in', 'takeaway'];
                let locations = ['Table 5', 'Table 8', 'Counter 3', 'Room 101'];
                let mockItems = [
                    ['1x Pepperoni Pizza', '1x Coca Cola'],
                    ['1x Grilled Chicken breast', '1x Roasted Asparagus'],
                    ['2x Double Shot Espresso', '2x Butter Croissants']
                ];
                let selectIndex = Math.floor(Math.random() * mockItems.length);
                
                this.orders.push({
                    id: nextId,
                    type: types[Math.floor(Math.random() * 2)],
                    location: locations[Math.floor(Math.random() * locations.length)],
                    status: 'new',
                    items: mockItems[selectIndex],
                    instructions: 'Prepared fresh.',
                    time: 'Just now',
                    amount: '$' + (Math.floor(Math.random() * 40) + 15).toFixed(2)
                });
                this.selectedOrderId = nextId;
            }
        }"
        x-init="
            time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            setInterval(() => {
                time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }, 1000);
        "
        class="h-full flex flex-col justify-center items-center p-4 md:p-6"
    >
        <!-- SCREEN 1: TERMINAL AUTHORIZATION LOGIN -->
        <template x-if="!loggedIn">
            <div class="w-full max-w-sm space-y-4">
                <!-- Branding Header -->
                <div class="text-center space-y-2">
                    <x-super-admin-logo
                        image-box-class="inline-flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-white p-1 shadow-lg shadow-orange/30"
                        fallback-box-class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange text-white shadow-lg shadow-orange/30"
                        icon-class="h-7 w-7"
                    />
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-white leading-tight">Authorize Display Kiosk</h2>
                        <p class="text-[10px] text-slate-400 mt-0.5">Connect this terminal screen to your EverythingEasy ServiceOS business branch.</p>
                    </div>
                </div>

                <!-- Login Card -->
                <div class="rounded-card bg-navy border border-navy/40 p-6 shadow-2xl space-y-4">
                    <!-- Error Message Banner -->
                    <div 
                        x-show="errorMessage" 
                        class="bg-danger/10 border border-danger/20 rounded-xl p-2.5 text-[11px] font-semibold text-danger text-center transition-all"
                        x-text="errorMessage"
                        style="display: none;"
                    ></div>

                    <!-- Business ID Input -->
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Business ID</label>
                        <input 
                            type="text" 
                            x-model="businessId"
                            placeholder="e.g. EASY-786"
                            class="w-full rounded-xl border border-navy/55 bg-navy-deep px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                        >
                    </div>

                    <!-- 4-Digit Security PIN Input -->
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Terminal Access PIN (4-Digits)</label>
                        <input 
                            type="password" 
                            x-model="pin"
                            maxlength="4"
                            placeholder="● ● ● ●"
                            class="w-full rounded-xl border border-navy/55 bg-navy-deep px-3.5 py-2.5 text-xs text-white placeholder-slate-500 text-center tracking-widest font-bold focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            @keyup.enter="authorizeTerminal()"
                        >
                    </div>

                    <!-- Submit Button -->
                    <button 
                        @click="authorizeTerminal()" 
                        class="w-full rounded-xl bg-orange hover:bg-orange/95 py-2.5 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer active:scale-95 transition-all mt-1"
                    >
                        Authorize & Start Display Board
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-[9px] text-slate-500">
                        Demo Account Pin is <span class="font-bold text-slate-400">1234</span>. Any Business ID works.
                    </p>
                </div>
            </div>
        </template>

        <!-- SCREEN 2: STANDALONE OPERATIONAL KDS BOARD (DARK THEME) -->
        <template x-if="loggedIn">
            <div class="w-full h-full flex flex-col justify-between">
                <!-- Header (Dark Theme) -->
                <header class="flex items-center justify-between border-b border-navy/40 pb-3.5 shrink-0">
                    <div class="flex items-center gap-3">
                        <x-super-admin-logo
                            image-box-class="inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-white p-1 shadow-lg shadow-orange/30"
                            fallback-box-class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange text-white shadow-lg shadow-orange/30"
                            icon-class="h-5 w-5"
                        />
                        <div>
                            <h1 class="text-base font-bold tracking-tight text-white leading-tight">EverythingEasy ServiceOS</h1>
                            <span class="inline-flex items-center gap-1 text-[9px] text-teal font-semibold mt-0.5">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-teal"></span>
                                </span>
                                Standalone Kitchen Console
                            </span>
                        </div>
                    </div>

                    <!-- Header Actions: Clock & Fullscreen -->
                    <div class="flex items-center gap-4">
                        <button 
                            @click="
                                if (!document.fullscreenElement) {
                                    document.documentElement.requestFullscreen();
                                } else {
                                    document.exitFullscreen();
                                }
                            "
                            class="rounded-lg border border-navy/40 bg-navy hover:bg-navy-deep px-3 py-1.5 text-[9px] font-bold text-slate-300 transition-all cursor-pointer flex items-center gap-1"
                        >
                            🖥️ Fullscreen
                        </button>
                        <div class="text-right">
                            <span class="text-lg font-bold font-mono tracking-wider text-slate-300" x-text="time"></span>
                            <span class="block text-[8px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Kitchen Terminal</span>
                        </div>
                    </div>
                </header>

                <!-- Stats Row (Dark theme cards) -->
                <div class="grid grid-cols-5 gap-3 my-3 shrink-0">
                    <div class="bg-navy border border-navy/40 rounded-xl p-2 px-3 flex items-center justify-between shadow-sm">
                        <div>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Total</span>
                            <h3 class="text-sm font-black text-white mt-0.5" x-text="totalOrders">0</h3>
                        </div>
                        <span class="text-xs">📁</span>
                    </div>
                    <div class="bg-navy border border-navy/40 rounded-xl p-2 px-3 flex items-center justify-between shadow-sm">
                        <div>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">New</span>
                            <h3 class="text-sm font-black text-white mt-0.5" x-text="newOrdersCount">0</h3>
                        </div>
                        <span class="text-xs">🛎️</span>
                    </div>
                    <div class="bg-navy border border-navy/40 rounded-xl p-2 px-3 flex items-center justify-between shadow-sm">
                        <div>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Kitchen</span>
                            <h3 class="text-sm font-black text-white mt-0.5" x-text="inKitchenCount">0</h3>
                        </div>
                        <span class="text-xs">🍳</span>
                    </div>
                    <div class="bg-navy border border-navy/40 rounded-xl p-2 px-3 flex items-center justify-between shadow-sm">
                        <div>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Ready</span>
                            <h3 class="text-sm font-black text-white mt-0.5" x-text="readyCount">0</h3>
                        </div>
                        <span class="text-xs">✅</span>
                    </div>
                    <div class="bg-navy border border-navy/40 rounded-xl p-2 px-3 flex items-center justify-between shadow-sm">
                        <div>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Served</span>
                            <h3 class="text-sm font-black text-white mt-0.5" x-text="servedCount">0</h3>
                        </div>
                        <span class="text-xs">🍽️</span>
                    </div>
                </div>

                <!-- Filters & Table Row (Dark Theme) -->
                <div class="flex justify-between items-center bg-navy border border-navy/40 p-1.5 rounded-xl mb-3 shrink-0">
                    <div class="flex items-center gap-1 bg-navy-deep border border-navy/55 p-0.5 rounded-lg">
                        <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-navy border border-navy/40 text-white font-bold' : 'text-slate-400 hover:text-white font-semibold'" class="rounded-md px-3 py-1 text-[9px] cursor-pointer">All Orders</button>
                        <button @click="activeTab = 'new'" :class="activeTab === 'new' ? 'bg-navy border border-navy/40 text-white font-bold' : 'text-slate-400 hover:text-white font-semibold'" class="rounded-md px-3 py-1 text-[9px] cursor-pointer">New</button>
                        <button @click="activeTab = 'preparing'" :class="activeTab === 'preparing' ? 'bg-navy border border-navy/40 text-white font-bold' : 'text-slate-400 hover:text-white font-semibold'" class="rounded-md px-3 py-1 text-[9px] cursor-pointer">In Kitchen</button>
                        <button @click="activeTab = 'ready'" :class="activeTab === 'ready' ? 'bg-navy border border-navy/40 text-white font-bold' : 'text-slate-400 hover:text-white font-semibold'" class="rounded-md px-3 py-1 text-[9px] cursor-pointer">Ready</button>
                        <button @click="activeTab = 'served'" :class="activeTab === 'served' ? 'bg-navy border border-navy/40 text-white font-bold' : 'text-slate-400 hover:text-white font-semibold'" class="rounded-md px-3 py-1 text-[9px] cursor-pointer">Served</button>
                    </div>

                    <div>
                        <select x-model="selectedTable" class="rounded-lg border border-navy/55 bg-navy-deep px-2.5 py-1 text-[9px] font-semibold text-white outline-none cursor-pointer">
                            <option value="all">All Service Points</option>
                            <option value="Table 12">Table 12</option>
                            <option value="Table 3">Table 3</option>
                            <option value="Room 302">Room 302</option>
                        </select>
                    </div>
                </div>

                <!-- 4 Column operational grid (Dark Theme) -->
                <main class="flex-1 grid grid-cols-1 lg:grid-cols-4 gap-4 my-2 overflow-hidden min-h-0">
                    
                    <!-- Kanban Columns (Left group) -->
                    <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-4 gap-3 items-start h-full overflow-hidden">
                        
                        <!-- COLUMN 1: NEW -->
                        <div class="flex flex-col bg-navy/20 border border-navy/30 rounded-xl p-2 space-y-2.5 h-full min-h-0">
                            <div class="flex items-center gap-1.5 pb-2 border-b border-navy/30 shrink-0">
                                <span class="inline-block w-2 h-2 rounded-full bg-orange animate-pulse"></span>
                                <span class="text-[9px] font-extrabold text-slate-300 uppercase tracking-wider">NEW</span>
                                <span class="ml-auto text-[9px] font-bold text-slate-400 bg-navy px-1.5 py-0.5 rounded-md" x-text="orders.filter(o => o.status === 'new').length"></span>
                            </div>
                            <div class="space-y-2 overflow-y-auto flex-1 pr-0.5">
                                <template x-for="o in orders.filter(x => x.status === 'new')" :key="o.id">
                                    <div 
                                        x-show="(activeTab === 'all' || activeTab === 'new') && (selectedTable === 'all' || o.location === selectedTable)"
                                        @click="selectedOrderId = o.id"
                                        :class="selectedOrderId === o.id ? 'border-orange ring-1 ring-orange/15 shadow-sm bg-navy' : 'border-navy/40 bg-navy-deep/80 hover:border-slate-500'"
                                        class="border rounded-lg p-2.5 cursor-pointer transition-all flex flex-col justify-between select-none"
                                    >
                                        <div class="flex justify-between items-start">
                                            <span class="text-[10px] font-extrabold text-white" x-text="`${o.location}`"></span>
                                            <span class="text-[8px] font-medium text-slate-400" x-text="o.time"></span>
                                        </div>
                                        <span class="text-xs font-black text-white mt-1" x-text="`Order #${o.id}`"></span>
                                        <p class="text-[10px] text-slate-400 truncate mt-1 border-t border-navy/30 pt-1" x-text="o.items.join(', ')"></p>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 rounded-md bg-navy text-slate-300" x-text="o.type === 'dine-in' ? 'Dine-in' : 'Takeaway'"></span>
                                            <span class="text-[10px] font-extrabold text-white" x-text="o.amount"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- COLUMN 2: IN KITCHEN -->
                        <div class="flex flex-col bg-navy/20 border border-navy/30 rounded-xl p-2 space-y-2.5 h-full min-h-0">
                            <div class="flex items-center gap-1.5 pb-2 border-b border-navy/30 shrink-0">
                                <span class="inline-block w-2 h-2 rounded-full bg-warning animate-pulse"></span>
                                <span class="text-[9px] font-extrabold text-slate-300 uppercase tracking-wider">KITCHEN</span>
                                <span class="ml-auto text-[9px] font-bold text-slate-400 bg-navy px-1.5 py-0.5 rounded-md" x-text="orders.filter(o => o.status === 'preparing').length"></span>
                            </div>
                            <div class="space-y-2 overflow-y-auto flex-1 pr-0.5">
                                <template x-for="o in orders.filter(x => x.status === 'preparing')" :key="o.id">
                                    <div 
                                        x-show="(activeTab === 'all' || activeTab === 'preparing') && (selectedTable === 'all' || o.location === selectedTable)"
                                        @click="selectedOrderId = o.id"
                                        :class="selectedOrderId === o.id ? 'border-orange ring-1 ring-orange/15 shadow-sm bg-navy' : 'border-navy/40 bg-navy-deep/80 hover:border-slate-500'"
                                        class="border rounded-lg p-2.5 cursor-pointer transition-all flex flex-col justify-between select-none"
                                    >
                                        <div class="flex justify-between items-start">
                                            <span class="text-[10px] font-extrabold text-white" x-text="`${o.location}`"></span>
                                            <span class="text-[8px] font-medium text-slate-400" x-text="o.time"></span>
                                        </div>
                                        <span class="text-xs font-black text-white mt-1" x-text="`Order #${o.id}`"></span>
                                        <p class="text-[10px] text-slate-400 truncate mt-1 border-t border-navy/30 pt-1" x-text="o.items.join(', ')"></p>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 rounded-md bg-navy text-slate-300" x-text="o.type === 'dine-in' ? 'Dine-in' : 'Takeaway'"></span>
                                            <span class="text-[10px] font-extrabold text-white" x-text="o.amount"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- COLUMN 3: READY -->
                        <div class="flex flex-col bg-navy/20 border border-navy/30 rounded-xl p-2 space-y-2.5 h-full min-h-0">
                            <div class="flex items-center gap-1.5 pb-2 border-b border-navy/30 shrink-0">
                                <span class="inline-block w-2 h-2 rounded-full bg-teal"></span>
                                <span class="text-[9px] font-extrabold text-slate-300 uppercase tracking-wider">READY</span>
                                <span class="ml-auto text-[9px] font-bold text-slate-400 bg-navy px-1.5 py-0.5 rounded-md" x-text="orders.filter(o => o.status === 'ready').length"></span>
                            </div>
                            <div class="space-y-2 overflow-y-auto flex-1 pr-0.5">
                                <template x-for="o in orders.filter(x => x.status === 'ready')" :key="o.id">
                                    <div 
                                        x-show="(activeTab === 'all' || activeTab === 'ready') && (selectedTable === 'all' || o.location === selectedTable)"
                                        @click="selectedOrderId = o.id"
                                        :class="selectedOrderId === o.id ? 'border-orange ring-1 ring-orange/15 shadow-sm bg-navy' : 'border-navy/40 bg-navy-deep/80 hover:border-slate-500'"
                                        class="border rounded-lg p-2.5 cursor-pointer transition-all flex flex-col justify-between select-none"
                                    >
                                        <div class="flex justify-between items-start">
                                            <span class="text-[10px] font-extrabold text-white" x-text="`${o.location}`"></span>
                                            <span class="text-[8px] font-medium text-slate-400" x-text="o.time"></span>
                                        </div>
                                        <span class="text-xs font-black text-white mt-1" x-text="`Order #${o.id}`"></span>
                                        <p class="text-[10px] text-slate-400 truncate mt-1 border-t border-navy/30 pt-1" x-text="o.items.join(', ')"></p>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 rounded-md bg-navy text-slate-300" x-text="o.type === 'dine-in' ? 'Dine-in' : 'Takeaway'"></span>
                                            <span class="text-[10px] font-extrabold text-white" x-text="o.amount"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- COLUMN 4: SERVED -->
                        <div class="flex flex-col bg-navy/20 border border-navy/30 rounded-xl p-2 space-y-2.5 h-full min-h-0">
                            <div class="flex items-center gap-1.5 pb-2 border-b border-navy/30 shrink-0">
                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-success"></span>
                                <span class="text-[9px] font-extrabold text-slate-300 uppercase tracking-wider">SERVED</span>
                                <span class="ml-auto text-[9px] font-bold text-slate-400 bg-navy px-1.5 py-0.5 rounded-md" x-text="orders.filter(o => o.status === 'served').length"></span>
                            </div>
                            <div class="space-y-2 overflow-y-auto flex-1 pr-0.5">
                                <template x-for="o in orders.filter(x => x.status === 'served')" :key="o.id">
                                    <div 
                                        x-show="(activeTab === 'all' || activeTab === 'served') && (selectedTable === 'all' || o.location === selectedTable)"
                                        @click="selectedOrderId = o.id"
                                        :class="selectedOrderId === o.id ? 'border-orange ring-1 ring-orange/15 shadow-sm bg-navy' : 'border-navy/40 bg-navy-deep/80 hover:border-slate-500'"
                                        class="border rounded-lg p-2.5 cursor-pointer transition-all flex flex-col justify-between select-none"
                                    >
                                        <div class="flex justify-between items-start">
                                            <span class="text-[10px] font-extrabold text-white" x-text="`${o.location}`"></span>
                                            <span class="text-[8px] font-medium text-slate-400" x-text="o.time"></span>
                                        </div>
                                        <span class="text-xs font-black text-white mt-1" x-text="`Order #${o.id}`"></span>
                                        <p class="text-[10px] text-slate-400 truncate mt-1 border-t border-navy/30 pt-1" x-text="o.items.join(', ')"></p>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 rounded-md bg-navy text-slate-300" x-text="o.type === 'dine-in' ? 'Dine-in' : 'Takeaway'"></span>
                                            <span class="text-[10px] font-extrabold text-white" x-text="o.amount"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Right operational details panel (Dark Theme) -->
                    <div class="lg:col-span-1 h-full flex flex-col justify-between">
                        <template x-if="selectedOrder">
                            <div class="bg-navy border border-navy/40 rounded-xl p-4 shadow-md space-y-4 flex flex-col justify-between h-full min-h-0">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between pb-2 border-b border-navy/30">
                                        <div>
                                            <h3 class="text-xs font-extrabold text-white" x-text="selectedOrder.location"></h3>
                                            <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider mt-0.5" x-text="`Status: ${selectedOrder.status}`"></span>
                                        </div>
                                        <button @click="clearOrder(selectedOrder.id)" class="rounded-lg border border-navy/30 bg-navy-deep px-2 py-1 text-[9px] font-bold text-danger hover:bg-danger/10 transition-all cursor-pointer">CLEAR</button>
                                    </div>

                                    <div class="space-y-2">
                                        <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">ORDER ITEMS</span>
                                        <div class="space-y-1.5">
                                            <template x-for="item in selectedOrder.items">
                                                <div class="flex justify-between items-center text-xs font-semibold text-white bg-navy-deep p-2 rounded-lg border border-navy/30">
                                                    <span x-text="item"></span>
                                                    <span class="text-[9px] text-teal">✓</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">INSTRUCTIONS</span>
                                        <p class="text-xs text-slate-300 bg-navy-deep p-2.5 rounded-lg leading-relaxed border border-navy/30" x-text="selectedOrder.instructions || 'No special requests.'"></p>
                                    </div>
                                </div>

                                <div class="space-y-1.5 pt-3 border-t border-navy/30">
                                    <template x-if="selectedOrder.status === 'new'">
                                        <button @click="moveStatus(selectedOrder.id, 'preparing')" class="w-full rounded-xl bg-orange hover:bg-orange/95 py-2 text-xs font-bold text-white shadow-md shadow-orange/20 cursor-pointer active:scale-95 transition-all">Send to Kitchen</button>
                                    </template>
                                    <template x-if="selectedOrder.status === 'preparing'">
                                        <button @click="moveStatus(selectedOrder.id, 'ready')" class="w-full rounded-xl bg-teal hover:bg-teal/95 py-2 text-xs font-bold text-white shadow-md shadow-teal/20 cursor-pointer active:scale-95 transition-all">Mark Ready for Pickup</button>
                                    </template>
                                    <template x-if="selectedOrder.status === 'ready'">
                                        <button @click="moveStatus(selectedOrder.id, 'served')" class="w-full rounded-xl bg-success hover:bg-success/95 py-2 text-xs font-bold text-white shadow-md shadow-success/20 cursor-pointer active:scale-95 transition-all">Mark Served</button>
                                    </template>
                                    <button @click="alert('Printing ticket for ' + selectedOrder.location)" class="w-full rounded-xl border border-navy/30 bg-navy-deep hover:bg-navy py-2 text-xs font-bold text-white cursor-pointer active:scale-95 transition-all">🖨️ Print Ticket</button>
                                </div>
                            </div>
                        </template>

                        <template x-if="!selectedOrder">
                            <div class="bg-navy border border-navy/40 rounded-xl p-4 shadow-md text-center flex flex-col justify-center items-center h-full">
                                <span class="text-2xl">📁</span>
                                <h3 class="text-xs font-bold text-white mt-2">No Active Order</h3>
                                <p class="text-[10px] text-slate-400 mt-1 leading-normal">Click any card to load details.</p>
                            </div>
                        </template>
                    </div>

                </main>

                <!-- Footer (Dark Theme with De-authorize and KDS Console helpers) -->
                <footer class="flex flex-col sm:flex-row items-center justify-between border-t border-navy/40 pt-3 gap-3 shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] text-slate-500 font-medium">Display Engine v1.2</span>
                        <button @click="deauthorizeTerminal()" class="text-[9px] font-bold text-danger hover:underline cursor-pointer">De-authorize Screen</button>
                    </div>

                    <!-- Interactive Demo Controls -->
                    <div class="flex flex-wrap gap-2 items-center bg-navy/40 border border-navy/20 p-1 rounded-lg">
                        <span class="text-[9px] font-bold text-muted uppercase px-1.5">Demo Console</span>
                        <button @click="addNewMockOrder()" class="rounded bg-orange/10 text-orange border border-orange/15 px-2 py-1 text-[9px] font-bold hover:bg-orange/15 active:scale-95 transition-all cursor-pointer">+ Add Preparing</button>
                        <button @click="moveStatus(selectedOrderId, 'ready')" class="rounded bg-teal/10 text-teal border border-teal/15 px-2 py-1 text-[9px] font-bold hover:bg-teal/15 active:scale-95 transition-all cursor-pointer">➔ Move to Ready</button>
                        <button @click="clearOrder(selectedOrderId)" class="rounded bg-slate-300/10 text-slate-300 border border-slate-300/15 px-2 py-1 text-[9px] font-bold hover:bg-slate-300/15 active:scale-95 transition-all cursor-pointer">✕ Serve/Clear</button>
                    </div>
                </footer>
            </div>
        </template>
    </div>
</body>
</html>
