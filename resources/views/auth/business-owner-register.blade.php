<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Admin Account - EverythingEasy ServiceOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full bg-bg text-ink font-sans antialiased">
<main class="min-h-screen" x-data="registrationWizard()">
    <section class="relative flex min-h-screen items-center justify-center overflow-hidden px-5 py-8 sm:px-8">
        <div class="pointer-events-none absolute inset-0 opacity-[0.35]" style="background-image: radial-gradient(circle at 1px 1px, #cbd5e1 1px, transparent 0); background-size: 22px 22px;"></div>
        <div class="relative w-full max-w-xl">
            <div class="mb-5 flex items-center justify-between">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-black text-muted transition hover:text-ink">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    Back to login
                </a>
                <span class="rounded-full border border-orange/20 bg-orange/10 px-3 py-1.5 text-xs font-black text-orange">Step <span x-text="step"></span> of 6</span>
            </div>

            <div class="mb-5 flex gap-2" aria-label="Registration progress">
                <template x-for="number in 6" :key="number"><span class="h-1.5 flex-1 rounded-full transition-colors" :class="number <= step ? 'bg-orange' : 'bg-border'"></span></template>
            </div>

            <div class="rounded-3xl border border-border bg-card p-6 shadow-xl shadow-slate-200/70 sm:p-8">
                @if($errors->any())
                    <div class="mb-5 rounded-xl border border-danger/20 bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">{{ $errors->first() }}</div>
                @endif
                <div x-show="message" x-cloak class="mb-5 rounded-xl px-4 py-3 text-sm font-semibold" :class="messageType === 'error' ? 'border border-danger/20 bg-danger/10 text-danger' : 'border border-success/20 bg-success/10 text-success'" x-text="message"></div>

                <form method="POST" action="{{ route('register.store') }}" @submit="submitting = true">
                    @csrf
                    <input type="hidden" name="business_type" :value="industry">

                    <section x-show="step === 1" x-cloak>
                        <p class="text-xs font-black uppercase tracking-wider text-orange">Your business</p>
                        <h2 class="mt-2 text-2xl font-black">What type of business do you run?</h2>
                        <p class="mt-2 text-sm text-muted">Choose the option that best describes your business.</p>
                        <div class="mt-7 grid grid-cols-2 gap-3">
                            <template x-for="item in industries" :key="item.id">
                                <button type="button" @click="industry = item.id" class="rounded-2xl border-2 p-5 text-left transition" :class="industry === item.id ? 'border-orange bg-orange/5 text-orange' : 'border-border bg-card-tint text-ink hover:border-orange/40'">
                                    <span class="text-2xl" x-text="item.icon"></span><span class="mt-3 block text-sm font-black" x-text="item.name"></span>
                                </button>
                            </template>
                        </div>
                    </section>

                    <section x-show="step === 2" x-cloak>
                        <p class="text-xs font-black uppercase tracking-wider text-orange">About you</p><h2 class="mt-2 text-2xl font-black">What's your name?</h2><p class="mt-2 text-sm text-muted">This will be the name on your admin profile.</p>
                        <label for="name" class="mt-7 block text-xs font-black uppercase tracking-wider">Full name</label>
                        <input id="name" name="name" x-model.trim="name" value="{{ old('name') }}" autocomplete="name" class="mt-2 w-full rounded-xl border border-border bg-card-tint px-4 py-3.5 text-base outline-none transition focus:border-orange focus:bg-white focus:ring-2 focus:ring-orange/15" placeholder="Enter your full name">
                    </section>

                    <section x-show="step === 3" x-cloak>
                        <p class="text-xs font-black uppercase tracking-wider text-orange">Contact number</p><h2 class="mt-2 text-2xl font-black">Your mobile number</h2><p class="mt-2 text-sm text-muted">Use a 10-digit number for account communication.</p>
                        <label for="phone" class="mt-7 block text-xs font-black uppercase tracking-wider">Mobile number</label>
                        <div class="mt-2 flex rounded-xl border border-border bg-card-tint focus-within:border-orange focus-within:ring-2 focus-within:ring-orange/15">
                            <select name="country_code" x-model="countryCode" aria-label="Country calling code" class="max-w-[48%] rounded-l-xl border-0 border-r border-border bg-transparent px-3 py-3.5 text-sm font-black outline-none sm:max-w-[44%]">
                                @foreach(config('country_codes') as $code => $country)
                                    <option value="{{ $code }}">{{ $country }} ({{ $code }})</option>
                                @endforeach
                            </select>
                            <input id="phone" name="phone" x-model="phone" @input="phone = phone.replace(/\D/g, '').slice(0, 15)" inputmode="numeric" autocomplete="tel-national" class="min-w-0 flex-1 bg-transparent px-4 py-3.5 text-base outline-none" placeholder="9876543210">
                        </div>
                    </section>

                    <section x-show="step === 4" x-cloak>
                        <p class="text-xs font-black uppercase tracking-wider text-orange">Email address</p><h2 class="mt-2 text-2xl font-black">Where can we reach you?</h2><p class="mt-2 text-sm text-muted">We'll send your verification code to this email.</p>
                        <label for="email" class="mt-7 block text-xs font-black uppercase tracking-wider">Business email</label>
                        <input id="email" name="email" type="email" x-model.trim="email" value="{{ old('email') }}" autocomplete="email" class="mt-2 w-full rounded-xl border border-border bg-card-tint px-4 py-3.5 text-base outline-none transition focus:border-orange focus:bg-white focus:ring-2 focus:ring-orange/15" placeholder="owner@example.com">
                    </section>

                    <section x-show="step === 5" x-cloak>
                        <p class="text-xs font-black uppercase tracking-wider text-orange">Secure your account</p><h2 class="mt-2 text-2xl font-black">Create a password</h2><p class="mt-2 text-sm text-muted">Use at least 8 characters.</p>
                        <div class="mt-7 grid gap-4 sm:grid-cols-2">
                            <div><label for="password" class="block text-xs font-black uppercase tracking-wider">Password</label><input id="password" name="password" :type="showPassword ? 'text' : 'password'" x-model="password" autocomplete="new-password" class="mt-2 w-full rounded-xl border border-border bg-card-tint px-4 py-3.5 outline-none focus:border-orange focus:ring-2 focus:ring-orange/15" placeholder="Minimum 8 characters"></div>
                            <div><label for="password_confirmation" class="block text-xs font-black uppercase tracking-wider">Confirm password</label><input id="password_confirmation" name="password_confirmation" :type="showPassword ? 'text' : 'password'" x-model="confirmation" autocomplete="new-password" class="mt-2 w-full rounded-xl border border-border bg-card-tint px-4 py-3.5 outline-none focus:border-orange focus:ring-2 focus:ring-orange/15" placeholder="Repeat password"></div>
                        </div>
                        <label class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-muted"><input type="checkbox" x-model="showPassword" class="rounded border-border text-orange"> Show passwords</label>
                    </section>

                    <section x-show="step === 6" x-cloak>
                        <p class="text-xs font-black uppercase tracking-wider text-orange">Email verification</p><h2 class="mt-2 text-2xl font-black">Enter your OTP</h2><p class="mt-2 text-sm text-muted">We sent a 4-digit code to <strong class="text-ink" x-text="email"></strong>.</p>
                        <label for="otp" class="mt-7 block text-xs font-black uppercase tracking-wider">Verification code</label>
                        <input id="otp" name="otp" x-model="otp" @input="otp = otp.replace(/\D/g, '').slice(0, 4)" inputmode="numeric" autocomplete="one-time-code" maxlength="4" class="mt-2 w-full rounded-xl border border-border bg-card-tint px-4 py-4 text-center text-2xl font-black tracking-[0.7em] outline-none focus:border-orange focus:ring-2 focus:ring-orange/15" placeholder="0000">
                        <button type="button" @click="sendOtp" :disabled="sendingOtp || timer > 0" class="mt-4 text-sm font-black text-orange disabled:text-muted"><span x-show="timer > 0">Resend code in <span x-text="timer"></span>s</span><span x-show="timer === 0">Resend OTP</span></button>
                    </section>

                    <div class="mt-8 flex items-center gap-3">
                        <button x-show="step > 1" type="button" @click="back" class="rounded-xl border border-border px-5 py-3 text-sm font-black text-muted transition hover:bg-card-tint">Back</button>
                        <button x-show="step < 6" type="button" @click="next" :disabled="!valid || sendingOtp" class="flex flex-1 items-center justify-center rounded-xl bg-orange px-5 py-3 text-sm font-black text-white shadow-md shadow-orange/20 transition disabled:cursor-not-allowed disabled:opacity-40"><span x-text="sendingOtp ? 'Sending OTP...' : (step === 5 ? 'Send OTP' : 'Continue')"></span></button>
                        <button x-show="step === 6" type="submit" :disabled="otp.length !== 4 || submitting" class="flex flex-1 items-center justify-center rounded-xl bg-orange px-5 py-3 text-sm font-black text-white shadow-md shadow-orange/20 disabled:opacity-40"><span x-text="submitting ? 'Creating account...' : 'Create admin account'"></span></button>
                    </div>
                </form>
            </div>
            <p class="mt-5 text-center text-sm font-semibold text-muted">Already have an account? <a href="{{ route('login') }}" class="font-black text-orange">Sign in</a></p>
        </div>
    </section>
</main>

<script>
function registrationWizard() {
    return {
        step: {{ $errors->any() ? 6 : 1 }}, industry: @js(old('business_type', '')), name: @js(old('name', '')), countryCode: @js(old('country_code', '+91')), phone: @js(old('phone', '')), email: @js(old('email', '')),
        password: '', confirmation: '', otp: '', showPassword: false, sendingOtp: false, submitting: false, timer: 0, message: '', messageType: 'success', timerId: null,
        industries: [{id:'hotels',name:'Hotels',icon:'🏨'},{id:'cafe',name:'Cafe',icon:'☕'},{id:'restaurant',name:'Restaurant',icon:'🍽️'},{id:'bakery',name:'Bakery',icon:'🥐'}],
        get valid() { if(this.step===1)return !!this.industry; if(this.step===2)return this.name.length>=3; if(this.step===3)return /^\d{6,15}$/.test(this.phone); if(this.step===4)return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email); if(this.step===5)return this.password.length>=8&&this.password===this.confirmation; return false; },
        back(){ this.message=''; if(this.step>1)this.step--; },
        async next(){ this.message=''; if(!this.valid)return; if(this.step===5){ await this.sendOtp(true); } else { this.step++; window.scrollTo({top:0,behavior:'smooth'}); } },
        startTimer(seconds){ this.timer=seconds; clearInterval(this.timerId); this.timerId=setInterval(()=>{ if(this.timer>0)this.timer--; else clearInterval(this.timerId); },1000); },
        async sendOtp(advance=false){ this.sendingOtp=true; this.message=''; try { const response=await fetch(@js(route('register.otp')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({email:this.email})}); const data=await response.json(); if(!response.ok)throw new Error(data.message || Object.values(data.errors||{})[0]?.[0] || 'Unable to send OTP.'); this.message=data.message; this.messageType='success'; this.startTimer(60); if(advance)this.step=6; } catch(error){ this.message=error.message; this.messageType='error'; } finally { this.sendingOtp=false; } }
    }
}
</script>
</body>
</html>
