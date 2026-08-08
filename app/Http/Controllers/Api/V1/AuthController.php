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
use App\Models\Business;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

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

        if (! $user || ! Hash::check($data['password'], $user->password) || $user->status !== 'active') {
            return $this->error('Invalid credentials', 422, [
                'email' => ['The provided credentials are incorrect.'],
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
        Password::sendResetLink($request->validated());

        return $this->success(null, 'If the email exists, a password reset link has been sent.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->validated(),
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
}
