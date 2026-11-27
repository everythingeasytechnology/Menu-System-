<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\BusinessRegistrationSuccessMail;
use App\Mail\EmailOtpMail;
use App\Models\Business;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\MailSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BusinessOwnerRegistrationController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly MailSettingsService $mailSettingsService,
    ) {}

    public function create(): View
    {
        return view('auth.business-owner-register');
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        if (! $this->mailSettingsService->apply()) {
            return response()->json(['message' => 'Email service is not configured yet. Please contact support.'], 422);
        }

        $otp = (string) random_int(1000, 9999);
        $expiresInSeconds = 300;

        try {
            Mail::to($data['email'])->send(new EmailOtpMail($otp, $expiresInSeconds));
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Unable to send OTP. Please try again.'], 502);
        }

        Cache::put($this->otpCacheKey($data['email']), [
            'email' => Str::lower($data['email']),
            'otp_hash' => Hash::make($otp),
        ], $expiresInSeconds);

        return response()->json(['message' => 'OTP sent to your email.', 'expires_in_seconds' => $expiresInSeconds]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_type' => ['required', 'in:hotels,cafe,restaurant,bakery'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'country_code' => ['required', Rule::in(array_keys(config('country_codes')))],
            'phone' => ['required', 'digits_between:6,15'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'otp' => ['required', 'digits:4'],
        ]);

        if (User::where('phone', $this->internationalPhone($data['country_code'], $data['phone']))->exists()) {
            return back()->withErrors(['phone' => 'This phone number is already registered.'])->withInput($request->except(['password', 'password_confirmation', 'otp']));
        }

        $cacheKey = $this->otpCacheKey($data['email']);
        $cachedOtp = Cache::get($cacheKey);

        if (! is_array($cachedOtp) || ! Hash::check($data['otp'], $cachedOtp['otp_hash'] ?? '')) {
            return back()->withErrors(['otp' => 'The OTP is invalid or has expired.'])->withInput($request->except(['password', 'password_confirmation', 'otp']));
        }

        [$user, $business] = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'phone' => $this->internationalPhone($data['country_code'], $data['phone']),
                'password' => $data['password'],
                'role' => 'owner',
                'status' => 'active',
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            $business = Business::create([
                'owner_user_id' => $user->id,
                'name' => $data['name']."'s Business",
                'type' => $data['business_type'],
                'email' => $user->email,
                'phone' => $user->phone,
            ]);

            $user->update(['business_id' => $business->id]);
            $this->auditLogService->record($user, $business->id, 'auth.registered', $business);

            return [$user->fresh(), $business];
        });

        Cache::forget($cacheKey);
        Auth::login($user);
        $request->session()->regenerate();

        try {
            if ($this->mailSettingsService->apply()) {
                Mail::to($user->email)->send(new BusinessRegistrationSuccessMail($user, $business));
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return redirect()->route('dashboard')->with('status', 'Your admin account has been created successfully.');
    }

    private function otpCacheKey(string $email): string
    {
        return 'auth:email-otp:'.sha1(Str::lower($email));
    }

    private function internationalPhone(string $countryCode, string $phone): string
    {
        return str_replace('-', '', $countryCode).ltrim($phone, '0');
    }
}
