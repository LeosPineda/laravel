<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip verification for superadmin (they don't need to verify)
        if ($user && $user->role === 'superadmin') {
            return $next($request);
        }

        // Check if user has verified their email
        if ($user && !$user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                abort(403, 'Your email address is not verified.');
            }

            return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
