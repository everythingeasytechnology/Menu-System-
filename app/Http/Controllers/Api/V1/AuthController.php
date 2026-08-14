<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\V1\BusinessResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Mail\BusinessRegistrationSuccessMail;
use App\Mail\EmailOtpMail;
use App\Models\Business;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\MailSettingsService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends ApiController
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly MailSettingsService $mailSettingsService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => 'owner',
                'status' => 'active',
            ]);

            $business = Business::create([
                'owner_user_id' => $user->id,
                'name' => $data['business_name'],
                'type' => $data['business_type'] ?? 'restaurant',
                'email' => $user->email,
                'phone' => $user->phone,
            ]);

            $user->update(['business_id' => $business->id]);
            $token = $user->createApiToken($data['device_name'] ?? 'mobile');

            $this->auditLogService->record($user, $business->id, 'auth.registered', $business);

            return [$user->fresh('business'), $business, $token];
        });

        [$user, $business, $token] = $result;

        $this->sendMailIfEnabled(fn () => Mail::to($user->email)->send(new BusinessRegistrationSuccessMail($user, $business)));

        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
            'business' => new BusinessResource($business),
        ], 'Registered successfully', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->error('Invalid credentials', 422, [
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status === 'suspended') {
            return $this->error('Your account has been suspended by admin.', 403, [
                'account_status' => ['suspended'],
            ]);
        }

        if ($user->status === 'inactive') {
            return $this->error('Your account has been marked inactive by admin.', 403, [
                'account_status' => ['inactive'],
            ]);
        }

        if ($user->status !== 'active') {
            return $this->error('Your account is not active.', 403, [
                'account_status' => [$user->status],
            ]);
        }

        $token = $user->createApiToken($data['device_name'] ?? 'mobile');
        $this->auditLogService->record($user, $user->business_id, 'auth.login');

        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user->load('business')),
            'business' => $user->business ? new BusinessResource($user->business) : null,
        ], 'Logged in successfully');
    }

    public function tokenStatus(Request $request): JsonResponse
    {
        $plainTextToken = $request->bearerToken() ?: $request->input('token');

        if (! $plainTextToken) {
            return $this->error('Token is required', 422, [
                'token' => ['Please provide a bearer token or token field.'],
            ]);
        }

        $accessToken = PersonalAccessToken::findByPlainTextToken($plainTextToken);
        $user = $accessToken?->user;

        if (! $accessToken || ! $user) {
            return $this->error('Invalid token', 401);
        }

        return $this->success([
            'status' => $user->status,
        ], 'User status');
    }

    public function checkEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $exists = User::where('email', $data['email'])->exists();

        return $this->success([
            'field' => 'email',
            'value' => $data['email'],
            'exists' => $exists,
            'available' => ! $exists,
        ], $exists ? 'Email already exists' : 'Email is available');
    }

    public function checkPhone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $exists = User::where('phone', $data['phone'])->exists();

        return $this->success([
            'field' => 'phone',
            'value' => $data['phone'],
            'exists' => $exists,
            'available' => ! $exists,
        ], $exists ? 'Phone number already exists' : 'Phone number is available');
    }

    public function emailOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        if (! $this->mailSettingsService->apply()) {
            return $this->error('SMTP settings are not enabled.', 422, [
                'email' => ['Please enable SMTP settings before sending email OTP.'],
            ]);
        }

        $expiresInSeconds = 300;
        $otp = (string) random_int(1000, 9999);

        try {
            Mail::to($data['email'])->send(new EmailOtpMail($otp, $expiresInSeconds));
        } catch (Throwable $exception) {
            report($exception);

            return $this->error('Unable to send OTP email.', 502, [
                'email' => ['Please check SMTP settings and try again.'],
            ]);
        }

        Cache::put($this->emailOtpCacheKey($data['email']), [
            'email' => Str::lower($data['email']),
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addSeconds($expiresInSeconds)->toIso8601String(),
        ], $expiresInSeconds);

        return $this->success([
            'email' => $data['email'],
            'otp' => $otp,
            'expires_in_seconds' => $expiresInSeconds,
            'mode' => 'smtp',
            'mail_sent' => true,
        ], 'Email OTP sent');
    }

    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'string', 'regex:/^\d{4}$/'],
        ]);

        $cacheKey = $this->emailOtpCacheKey($data['email']);
        $cachedOtp = Cache::get($cacheKey);

        if (! is_array($cachedOtp) || ! Hash::check($data['otp'], $cachedOtp['otp_hash'] ?? '')) {
            return $this->error('Invalid or expired OTP.', 422, [
                'otp' => ['The OTP is invalid or has expired.'],
            ]);
        }

        Cache::forget($cacheKey);

        return $this->success([
            'email' => $data['email'],
            'verified' => true,
        ], 'Email OTP verified');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->attributes->get('access_token')?->update(['revoked_at' => now()]);
        $this->auditLogService->record($request->user(), $request->user()?->business_id, 'auth.logout');

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success([
            'user' => new UserResource($request->user()),
            'business' => $request->user()->business ? new BusinessResource($request->user()->business) : null,
        ], 'Current user');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->activeUserByEmail($data['email']);

        if (! $user) {
            return $this->error('We could not find an active account with that email.', 422, [
                'email' => ['We could not find an active account with that email.'],
            ]);
        }

        if (! $this->mailSettingsService->apply()) {
            return $this->error('SMTP settings are not enabled.', 422, [
                'email' => ['Please enable SMTP settings before sending password reset email.'],
            ]);
        }

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (Throwable $exception) {
            report($exception);

            return $this->error('Unable to send password reset email.', 502, [
                'email' => ['Please check SMTP settings and try again.'],
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->error('Unable to send password reset email.', 422, [
                'email' => [__($status)],
            ]);
        }

        return $this->success([
            'email' => $user->email,
            'mail_sent' => true,
        ], 'Password reset link sent to your email.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $this->activeUserByEmail($data['email'])) {
            return $this->error('We could not find an active account with that email.', 422, [
                'email' => ['We could not find an active account with that email.'],
            ]);
        }

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->error('Password reset failed', 422, ['email' => [__($status)]]);
        }

        return $this->success(null, 'Password reset successfully');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (! Hash::check($data['current_password'], $user->password)) {
            return $this->error('Validation failed', 422, [
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update(['password' => $data['password']]);
        $this->auditLogService->record($user, $user->business_id, 'auth.password_changed');

        return $this->success(null, 'Password changed successfully');
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return $this->success(new UserResource($request->user()->fresh()), 'Profile updated successfully');
    }

    private function sendMailIfEnabled(callable $callback): bool
    {
        if (! $this->mailSettingsService->apply()) {
            return false;
        }

        try {
            $callback();

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function emailOtpCacheKey(string $email): string
    {
        return 'auth:email-otp:'.sha1(Str::lower($email));
    }

    private function activeUserByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->where('status', 'active')
            ->first();
    }
}
