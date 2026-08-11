@extends('layouts.app')

@section('title', 'Settings Management')

@section('content')
<div class="space-y-8" x-data="{
    activeTab: '{{ session('active_tab', 'business') }}',
    toastOpen: {{ session('success') ? 'true' : 'false' }},
    init() {
        if (this.toastOpen) {
            setTimeout(() => { this.toastOpen = false }, 4000);
        }
    }
}">
    <!-- Floating Notification Toast -->
    <div 
        x-show="toastOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2 lg:translate-y-0 lg:translate-x-2"
        x-transition:enter-end="opacity-100 translate-y-0 lg:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-navy-deep border border-orange/20 text-white px-5 py-4 rounded-xl shadow-xl max-w-sm"
        style="display: none;"
    >
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange/20 text-orange">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-xs font-bold text-white">System Notification</p>
            <p class="text-[11px] text-slate-300 mt-0.5">{{ session('success') }}</p>
        </div>
        <button @click="toastOpen = false" class="text-muted hover:text-white transition-colors cursor-pointer">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">System Settings</h1>
            <p class="text-sm text-muted mt-1">Configure company profiles, update security keys, manage payment processing gateways, and change passwords.</p>
        </div>
    </div>

    <!-- Tab Selection / Navigation -->
    <div class="flex items-center gap-1 bg-card-tint border border-border p-1 rounded-xl w-full md:w-max overflow-x-auto scrollbar-none">
        <button 
            @click="activeTab = 'business'"
            :class="activeTab === 'business' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
            class="rounded-lg px-5 py-2.5 text-xs transition-all cursor-pointer whitespace-nowrap flex items-center gap-2"
        >
            <span>🏢</span>
            <span>Business Profile</span>
        </button>
        <button 
            @click="activeTab = 'payments'"
            :class="activeTab === 'payments' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
            class="rounded-lg px-5 py-2.5 text-xs transition-all cursor-pointer whitespace-nowrap flex items-center gap-2"
        >
            <span>💳</span>
            <span>Payment Gateways</span>
        </button>
        <button 
            @click="activeTab = 'gst'"
            :class="activeTab === 'gst' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
            class="rounded-lg px-5 py-2.5 text-xs transition-all cursor-pointer whitespace-nowrap flex items-center gap-2"
        >
            <span>📜</span>
            <span>GST Settings</span>
        </button>
        <button 
            @click="activeTab = 'security'"
            :class="activeTab === 'security' ? 'bg-white text-ink shadow-sm font-bold border border-border/30' : 'text-muted hover:text-ink font-semibold'"
            class="rounded-lg px-5 py-2.5 text-xs transition-all cursor-pointer whitespace-nowrap flex items-center gap-2"
        >
            <span>🔒</span>
            <span>Security & Password</span>
        </button>
    </div>

    <!-- Main Settings Panel Grid -->
    <div class="grid grid-cols-1 gap-6">

        <!-- 1. BUSINESS PROFILE TAB -->
        <div x-show="activeTab === 'business'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
            <x-card>
                <div class="pb-5 border-b border-border mb-6">
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        <span>🏢</span> Business Information Settings
                    </h2>
                    <p class="text-xs text-muted mt-1">Details configured here will reflect on customer receipts, invoices, and your public brand page.</p>
                </div>

                @if ($errors->any() && session('active_tab') === 'business')
                    <div class="mb-6 p-4 bg-danger/10 border border-danger/25 rounded-xl text-danger text-xs font-semibold space-y-1">
                        <p class="font-bold">Please correct the following errors:</p>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('settings.business') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Company Logo Upload Card -->
                    <div class="p-5 rounded-2xl bg-card-tint border border-border/60 flex flex-col md:flex-row items-center gap-6" x-data="{
                        logoPreview: '{{ $business->logo_path ? asset('storage/' . $business->logo_path) : '' }}',
                        handleFileChange(e) {
                            const file = e.target.files[0];
                            if (file) {
                                this.logoPreview = URL.createObjectURL(file);
                            }
                        }
                    }">
                        <div class="relative shrink-0 flex items-center justify-center h-24 w-24 rounded-2xl bg-white border border-border/80 shadow-inner overflow-hidden group">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="h-full w-full object-contain p-2">
                            </template>
                            <template x-if="!logoPreview">
                                <div class="flex flex-col items-center justify-center text-muted">
                                    <span class="text-3xl">🏢</span>
                                </div>
                            </template>
                        </div>
                        
                        <div class="flex-1 space-y-2 text-center md:text-left">
                            <h3 class="text-xs font-bold text-ink uppercase tracking-wider">Company Logo</h3>
                            <p class="text-[11px] text-muted max-w-sm">Upload a square logo. Accepted formats: JPG, PNG, GIF, SVG. Max file size: 2MB.</p>
                            
                            <div class="pt-2">
                                <label class="inline-flex items-center gap-2 rounded-xl bg-orange hover:bg-orange/95 px-4 py-2 text-[11px] font-bold text-white shadow-md shadow-orange/15 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer select-none">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                    </svg>
                                    <span>Upload Brand Logo</span>
                                    <input type="file" name="logo" @change="handleFileChange" class="hidden" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Brand Name -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">Brand / Company Name</label>
                            <input 
                                type="text" 
                                name="brand_name" 
                                value="{{ old('brand_name', $business->brand_name) }}"
                                placeholder="Enter Brand Name"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>

                        <!-- Business Email -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">Business Email Address</label>
                            <input 
                                type="email" 
                                name="business_email" 
                                value="{{ old('business_email', $business->business_email) }}"
                                placeholder="billing@yourbrand.com"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>

                        <!-- Shop / Suite Number -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">Shop / Suite / Office No.</label>
                            <input 
                                type="text" 
                                name="shop_no" 
                                value="{{ old('shop_no', $business->shop_no) }}"
                                placeholder="Suite 404, Ground Floor"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>

                        <!-- GST Registration Number -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">GST Registration Number</label>
                            <input 
                                type="text" 
                                name="gst_no" 
                                value="{{ old('gst_no', $business->gst_no) }}"
                                placeholder="07AAAAA1111A1Z1"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>
                    </div>

                    <!-- Complete Address (Full Width) -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-ink uppercase tracking-wider">Street Address / Landmark</label>
                        <textarea 
                            name="address" 
                            rows="3"
                            placeholder="Enter complete company street address..."
                            class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none resize-none"
                        >{{ old('address', $business->address) }}</textarea>
                    </div>

                    <!-- Country, State, District, and Pincode Fields with API Integration -->
                    <div x-data="locationSelector()" x-init="initLocation()" class="space-y-6">
                        <!-- Toggle Manual Mode -->
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-card-tint border border-border/60">
                            <div class="flex items-center gap-2">
                                <span class="text-xs">🌐</span>
                                <span class="text-[11px] font-bold text-ink uppercase tracking-wider">Smart Location Selector (CountriesNow API)</span>
                            </div>
                            <button 
                                type="button" 
                                @click="manualMode = !manualMode" 
                                class="text-[10px] font-black text-orange uppercase tracking-wider hover:underline focus:outline-none cursor-pointer"
                                x-text="manualMode ? 'Use Dropdowns' : 'Enter Manually'"
                            ></button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Country Select or Input -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-ink uppercase tracking-wider flex justify-between items-center">
                                    <span>Country</span>
                                    <span x-show="loadingCountries" class="text-[10px] text-orange animate-pulse font-bold">loading...</span>
                                </label>
                                
                                <template x-if="!manualMode">
                                    <select 
                                        name="country" 
                                        x-model="selectedCountry" 
                                        @change="fetchStates()"
                                        class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none cursor-pointer"
                                    >
                                        <option value="">Select Country</option>
                                        <template x-for="c in countries" :key="c">
                                            <option :value="c" x-text="c" :selected="c === selectedCountry"></option>
                                        </template>
                                    </select>
                                </template>
                                
                                <template x-if="manualMode">
                                    <input 
                                        type="text" 
                                        name="country" 
                                        x-model="selectedCountry"
                                        placeholder="India"
                                        class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                                    >
                                </template>
                            </div>

                            <!-- State Select or Input -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-ink uppercase tracking-wider flex justify-between items-center">
                                    <span>State</span>
                                    <span x-show="loadingStates" class="text-[10px] text-orange animate-pulse font-bold">loading...</span>
                                </label>
                                
                                <template x-if="!manualMode">
                                    <select 
                                        name="state" 
                                        x-model="selectedState" 
                                        @change="fetchCities()"
                                        class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none cursor-pointer"
                                        :disabled="!selectedCountry"
                                    >
                                        <option value="">Select State</option>
                                        <template x-for="s in states" :key="s">
                                            <option :value="s" x-text="s" :selected="s === selectedState"></option>
                                        </template>
                                    </select>
                                </template>
                                
                                <template x-if="manualMode">
                                    <input 
                                        type="text" 
                                        name="state" 
                                        x-model="selectedState"
                                        placeholder="Delhi"
                                        class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                                    >
                                </template>
                            </div>

                            <!-- District/City Select or Input -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-ink uppercase tracking-wider flex justify-between items-center">
                                    <span>District / City</span>
                                    <span x-show="loadingCities" class="text-[10px] text-orange animate-pulse font-bold">loading...</span>
                                </label>
                                
                                <template x-if="!manualMode">
                                    <select 
                                        name="district" 
                                        x-model="selectedDistrict" 
                                        class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none cursor-pointer"
                                        :disabled="!selectedState"
                                    >
                                        <option value="">Select District</option>
                                        <template x-for="ct in cities" :key="ct">
                                            <option :value="ct" x-text="ct" :selected="ct === selectedDistrict"></option>
                                        </template>
                                    </select>
                                </template>
                                
                                <template x-if="manualMode">
                                    <input 
                                        type="text" 
                                        name="district" 
                                        x-model="selectedDistrict"
                                        placeholder="New Delhi"
                                        class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                                    >
                                </template>
                            </div>

                            <!-- Pincode -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-ink uppercase tracking-wider">Pincode / Postal Code</label>
                                <input 
                                    type="text" 
                                    name="pincode" 
                                    value="{{ old('pincode', $business->pincode) }}"
                                    placeholder="110001"
                                    class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Latitude & Longitude Coordinates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-border/40">
                        <!-- Latitude -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">Latitude</label>
                            <input 
                                type="text" 
                                name="latitude" 
                                value="{{ old('latitude', $business->latitude) }}"
                                placeholder="28.6304"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>

                        <!-- Longitude -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">Longitude</label>
                            <input 
                                type="text" 
                                name="longitude" 
                                value="{{ old('longitude', $business->longitude) }}"
                                placeholder="77.2177"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 border-t border-border">
                        <button 
                            type="submit"
                            class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-3 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
                        >
                            Save Business Details
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- 2. PAYMENT GATEWAYS TAB -->
        <div x-show="activeTab === 'payments'" x-transition:enter="transition ease-out duration-200" class="space-y-6" style="display: none;">
            
            <!-- Cash Payments Config -->
            <x-card>
                <div class="pb-5 border-b border-border mb-6">
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        <span>💵</span> Physical Cash Payment Options
                    </h2>
                    <p class="text-xs text-muted mt-1">Enable or disable cash checkout on counter order points or server apps.</p>
                </div>

                <form action="{{ route('settings.cash') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="flex items-center justify-between p-4 rounded-xl bg-card-tint border border-border/60">
                        <div class="space-y-1">
                            <h3 class="text-xs font-bold text-ink">Enable Cash Payments (Pay Cash)</h3>
                            <p class="text-[11px] text-muted">Allow customers and staff to settle bills using physical currency.</p>
                        </div>
                        
                        <!-- Toggle Switch -->
                        <div x-data="{ enabled: {{ $cash->enabled ? 'true' : 'false' }} }" class="flex items-center gap-3">
                            <input type="hidden" name="enabled" :value="enabled ? '1' : '0'">
                            <button 
                                type="button"
                                @click="enabled = !enabled"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="enabled ? 'bg-orange' : 'bg-slate-300'"
                            >
                                <span 
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                    :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                                ></span>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-border">
                        <button 
                            type="submit"
                            class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-3 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
                        >
                            Save Cash Settings
                        </button>
                    </div>
                </form>
            </x-card>

            <!-- Razorpay Gateway Config -->
            <x-card>
                <div class="pb-5 border-b border-border mb-6">
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        <span>💳</span> Razorpay Payment Gateway
                    </h2>
                    <p class="text-xs text-muted mt-1">Setup your online payment gateway credentials to receive credit/debit cards, UPI, and net banking payments instantly.</p>
                </div>

                @if ($errors->any() && session('active_tab') === 'payments')
                    <div class="mb-6 p-4 bg-danger/10 border border-danger/25 rounded-xl text-danger text-xs font-semibold space-y-1">
                        <p class="font-bold">Please correct the following errors:</p>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('settings.razorpay') }}" method="POST" class="space-y-6" x-data="{
                    enabled: {{ $razorpay->enabled ? 'true' : 'false' }}
                }">
                    @csrf
                    
                    <div class="flex items-center justify-between p-4 rounded-xl bg-card-tint border border-border/60 mb-6">
                        <div class="space-y-1">
                            <h3 class="text-xs font-bold text-ink">Enable Razorpay Processing</h3>
                            <p class="text-[11px] text-muted">Route online payments directly into your Razorpay account.</p>
                        </div>
                        
                        <!-- Toggle Switch -->
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="enabled" :value="enabled ? '1' : '0'">
                            <button 
                                type="button"
                                @click="enabled = !enabled"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="enabled ? 'bg-orange' : 'bg-slate-300'"
                            >
                                <span 
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                    :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                                ></span>
                            </button>
                        </div>
                    </div>

                    <!-- Credential fields wrapper that shows/hides based on toggle -->
                    <div class="space-y-6" x-show="enabled" x-collapse>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Key ID -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-ink uppercase tracking-wider">Razorpay Key ID</label>
                                <input 
                                    type="text" 
                                    name="key_id" 
                                    value="{{ old('key_id', $razorpay->key_id) }}"
                                    placeholder="rzp_live_..."
                                    class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                                >
                            </div>

                            <!-- Key Secret -->
                            <div class="space-y-2" x-data="{ showSecret: false }">
                                <label class="block text-xs font-bold text-ink uppercase tracking-wider flex justify-between items-center">
                                    <span>Razorpay Key Secret</span>
                                    <button type="button" @click="showSecret = !showSecret" class="text-[10px] text-orange hover:underline lowercase font-bold focus:outline-none cursor-pointer">
                                        <span x-text="showSecret ? 'hide secret' : 'show secret'"></span>
                                    </button>
                                </label>
                                <input 
                                    :type="showSecret ? 'text' : 'password'" 
                                    name="key_secret" 
                                    value="{{ old('key_secret') }}"
                                    placeholder="••••••••••••••••••••••••"
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-border">
                        <button 
                            type="submit"
                            class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-3 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
                        >
                            Save Razorpay Credentials
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- 3. SECURITY & PASSWORD TAB -->
        <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-200" class="space-y-6" style="display: none;">
            <x-card>
                <div class="pb-5 border-b border-border mb-6">
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        <span>🔒</span> Update Security Password
                    </h2>
                    <p class="text-xs text-muted mt-1">Ensure your executive panel remains secure. Change the system password regularly.</p>
                </div>

                @if ($errors->any() && session('active_tab') === 'security')
                    <div class="mb-6 p-4 bg-danger/10 border border-danger/25 rounded-xl text-danger text-xs font-semibold space-y-1">
                        <p class="font-bold">Please correct the following errors:</p>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('settings.password') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-6 max-w-xl">
                        <!-- Current Password -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">Current Password</label>
                            <input 
                                type="password" 
                                name="current_password" 
                                placeholder="••••••••••••"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>

                        <!-- New Password -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">New Password</label>
                            <input 
                                type="password" 
                                name="new_password" 
                                placeholder="•••••••••••• (Minimum 8 characters)"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">Confirm New Password</label>
                            <input 
                                type="password" 
                                name="new_password_confirmation" 
                                placeholder="••••••••••••"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 border-t border-border">
                        <button 
                            type="submit"
                            class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-3 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
                        >
                            Change Password
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- 4. GST BILLING TAB -->
        <div x-show="activeTab === 'gst'" x-transition:enter="transition ease-out duration-200" class="space-y-6" style="display: none;">
            <x-card>
                <div class="pb-5 border-b border-border mb-6">
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        <span>📜</span> GST & Tax Configuration
                    </h2>
                    <p class="text-xs text-muted mt-1">Configure your GST identification number and enable tax breakdowns for customer billing.</p>
                </div>

                @if ($errors->any() && session('active_tab') === 'gst')
                    <div class="mb-6 p-4 bg-danger/10 border border-danger/25 rounded-xl text-danger text-xs font-semibold space-y-1">
                        <p class="font-bold">Please correct the following errors:</p>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('settings.gst') }}" method="POST" class="space-y-6" x-data="{ gstNo: '{{ old('gst_no', $business->gst_no) }}', gstEnabled: {{ old('gst_enabled', $business->gst_enabled) ? 'true' : 'false' }}, cgstInput: {{ old('cgst', $business->cgst ?? 2.50) }}, sgstInput: {{ old('sgst', $business->sgst ?? 2.50) }} }">
                    @csrf
                    
                    <!-- Warning message if GST is blank -->
                    <div 
                        x-show="gstNo.trim() === ''" 
                        x-transition:enter="transition ease-out duration-200"
                        class="p-4 rounded-xl bg-orange/5 border border-orange/15 text-orange flex items-start gap-3"
                    >
                        <span class="text-base shrink-0">⚠️</span>
                        <div class="space-y-1">
                            <p class="text-xs font-bold">Please enter your GST number</p>
                            <p class="text-[10px] text-orange/90">A valid GST Identification Number (GSTIN) is required to enable automated tax calculations and activate tax billing on order invoices.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- GST Number Input -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">GST Identification Number (GSTIN)</label>
                            <input 
                                type="text" 
                                name="gst_no" 
                                x-model="gstNo"
                                placeholder="e.g. 07AAAAA1111A1Z1"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                            <p class="text-[10px] text-muted">A valid 15-digit GSTIN is required to enable automated tax calculations.</p>
                        </div>

                        <!-- Enable GST Toggle Switch -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">GST Billing Status</label>
                            <div class="flex items-center justify-between p-3.5 rounded-xl bg-card-tint border border-border/60">
                                <div class="space-y-0.5">
                                    <span class="block text-xs font-bold text-ink">Enable GST Billing</span>
                                    <span class="block text-[10px] text-muted">Apply tax rate to all order invoices</span>
                                </div>
                                <button 
                                    type="button"
                                    @click="if (gstEnabled) { gstEnabled = false } else if (gstNo.trim() !== '') { gstEnabled = true } else { alert('Please enter a GSTIN first to enable tax billing.') }"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                    :class="gstEnabled && gstNo.trim() !== '' ? 'bg-orange' : 'bg-slate-300'"
                                >
                                    <input type="hidden" name="gst_enabled" :value="gstEnabled && gstNo.trim() !== '' ? '1' : '0'">
                                    <span 
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                        :class="gstEnabled && gstNo.trim() !== '' ? 'translate-x-5' : 'translate-x-0'"
                                    ></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- CGST & SGST Input Box Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-border/40">
                        <!-- CGST Input -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">Central GST (CGST %)</label>
                            <input 
                                type="number" 
                                name="cgst" 
                                step="0.01"
                                x-model="cgstInput"
                                required
                                placeholder="2.50"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                            <p class="text-[10px] text-muted">Enter CGST rate (usually 2.5% for general F&B tax structures).</p>
                        </div>

                        <!-- SGST Input -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink uppercase tracking-wider">State GST (SGST %)</label>
                            <input 
                                type="number" 
                                name="sgst" 
                                step="0.01"
                                x-model="sgstInput"
                                required
                                placeholder="2.50"
                                class="w-full rounded-xl border border-border bg-card-tint py-2.5 px-4 text-xs text-ink placeholder-muted focus:bg-card focus:border-orange focus:ring-2 focus:ring-orange/15 transition-all outline-none"
                            >
                            <p class="text-[10px] text-muted">Enter SGST rate (usually 2.5% for general F&B tax structures).</p>
                        </div>
                    </div>

                    <!-- GST Rates Breakup Box (Shows when enabled) -->
                    <div 
                        x-show="gstEnabled && gstNo.trim() !== ''" 
                        x-transition:enter="transition ease-out duration-200" 
                        class="p-4 rounded-xl bg-orange/5 border border-orange/10 space-y-3"
                    >
                        <span class="block text-[10px] font-extrabold text-orange uppercase tracking-wider">📊 Active GST Rate Breakdown</span>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-card border border-border/60 p-3 rounded-lg shadow-xs">
                                <span class="block text-[8px] font-bold text-muted uppercase">Central GST (CGST)</span>
                                <span class="block text-base font-black text-ink mt-0.5" x-text="parseFloat(cgstInput || 0).toFixed(2) + '%'"></span>
                            </div>
                            <div class="bg-card border border-border/60 p-3 rounded-lg shadow-xs">
                                <span class="block text-[8px] font-bold text-muted uppercase">State GST (SGST)</span>
                                <span class="block text-base font-black text-ink mt-0.5" x-text="parseFloat(sgstInput || 0).toFixed(2) + '%'"></span>
                            </div>
                            <div class="bg-card border border-orange/20 p-3 rounded-lg shadow-xs">
                                <span class="block text-[8px] font-bold text-orange uppercase">Total GST Rate</span>
                                <span class="block text-base font-black text-orange mt-0.5" x-text="(parseFloat(cgstInput || 0) + parseFloat(sgstInput || 0)).toFixed(2) + '%'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 border-t border-border">
                        <button 
                            type="submit"
                            class="rounded-xl bg-orange hover:bg-orange/95 px-5 py-3 text-xs font-bold text-white shadow-md shadow-orange/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
                        >
                            Save Tax Configurations
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

    </div>
</div>

<script>
function locationSelector() {
    return {
        countries: [],
        states: [],
        cities: [],
        selectedCountry: '{{ old('country', $business->country) }}',
        selectedState: '{{ old('state', $business->state) }}',
        selectedDistrict: '{{ old('district', $business->district) }}',
        manualMode: false,
        loadingCountries: false,
        loadingStates: false,
        loadingCities: false,

        async initLocation() {
            this.loadingCountries = true;
            try {
                let res = await fetch('https://countriesnow.space/api/v0.1/countries');
                let data = await res.json();
                if (!data.error) {
                    this.countries = data.data.map(c => c.country).sort();
                }
            } catch (e) {
                console.error('Failed to load countries', e);
                this.manualMode = true;
            } finally {
                this.loadingCountries = false;
            }

            if (this.selectedCountry) {
                await this.fetchStates(false);
                if (this.selectedState) {
                    await this.fetchCities(false);
                }
            }
        },

        async fetchStates(resetNext = true) {
            if (resetNext) {
                this.selectedState = '';
                this.selectedDistrict = '';
                this.states = [];
                this.cities = [];
            }
            if (!this.selectedCountry) return;
            this.loadingStates = true;
            try {
                let res = await fetch('https://countriesnow.space/api/v0.1/countries/states', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ country: this.selectedCountry })
                });
                let data = await res.json();
                if (!data.error) {
                    this.states = data.data.states.map(s => s.name).sort();
                }
            } catch (e) {
                console.error('Failed to load states', e);
            } finally {
                this.loadingStates = false;
            }
        },

        async fetchCities(resetNext = true) {
            if (resetNext) {
                this.selectedDistrict = '';
                this.cities = [];
            }
            if (!this.selectedCountry || !this.selectedState) return;
            this.loadingCities = true;
            try {
                let res = await fetch('https://countriesnow.space/api/v0.1/countries/state/cities', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ country: this.selectedCountry, state: this.selectedState })
                });
                let data = await res.json();
                if (!data.error) {
                    this.cities = data.data.sort();
                }
            } catch (e) {
                console.error('Failed to load cities', e);
            } finally {
                this.loadingCities = false;
            }
        }
    }
}
</script>
@endsection
