<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailSettingsService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    public function __construct(private readonly MailSettingsService $mailSettingsService) {}

    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = $this->activePortalUser($data['email']);

        if (! $user) {
            return back()
                ->withErrors(['email' => 'We could not find an active owner or admin account with that email.'])
                ->onlyInput('email');
        }

        if (! $this->mailSettingsService->apply()) {
            return back()
                ->withErrors(['email' => 'SMTP settings are not enabled.'])
                ->onlyInput('email');
        }

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['email' => 'Unable to send password reset email. Please check SMTP settings.'])
                ->onlyInput('email');
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withErrors(['email' => __($status)])
                ->onlyInput('email');
        }

        return back()->with('status', 'Password reset link sent to your email.');
    }

    public function reset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $this->activePortalUser($data['email'])) {
            return back()
                ->withErrors(['email' => 'We could not find an active owner or admin account with that email.'])
                ->withInput($request->only('email'));
        }

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => __($status)])
                ->withInput($request->only('email'));
        }

        return redirect()
            ->route('login')
            ->with('status', 'Password updated. You can sign in now.');
    }

    private function activePortalUser(string $email): ?User
    {
        return User::where('email', $email)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'admin', 'superadmin'])
            ->first();
    }
}
