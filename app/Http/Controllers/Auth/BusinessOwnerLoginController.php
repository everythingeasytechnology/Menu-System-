<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class BusinessOwnerLoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check() && in_array(Auth::user()->role, ['owner', 'admin'], true)) {
            return redirect()->route('dashboard');
        }

        return view('auth.business-owner-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (
            ! $user
            || ! Hash::check($credentials['password'], $user->password)
            || $user->status !== 'active'
            || ! in_array($user->role, ['owner', 'admin'], true)
        ) {
            return back()
                ->withErrors(['email' => 'These credentials do not match an active business owner account.'])
                ->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
