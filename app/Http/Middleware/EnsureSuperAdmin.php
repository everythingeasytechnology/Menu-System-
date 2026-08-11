<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if ($user->status !== 'active') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Only active super admin accounts can access admin.']);
        }

        if ($user->role !== 'superadmin') {
            return redirect()
                ->route(in_array($user->role, ['owner', 'admin'], true) ? 'dashboard' : 'login')
                ->with('error', 'Only super admins can access this section.');
        }

        return $next($request);
    }
}
