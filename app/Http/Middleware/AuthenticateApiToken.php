<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken();

        if (! $plainTextToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $accessToken = PersonalAccessToken::findByPlainTextToken($plainTextToken);

        $user = $accessToken?->user;

        if (! $accessToken || ! $accessToken->isUsable() || ! $user || $user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $business = $user->business_id ? $user->business()->first() : null;
        if ($user->business_id && (! $business || $business->status !== 'active')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        Auth::setUser($user);
        $request->attributes->set('access_token', $accessToken);

        return $next($request);
    }
}
