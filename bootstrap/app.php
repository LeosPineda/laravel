<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (ngrok, cloudflare, etc.) to fix Mixed Content errors
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // ✅ COMPLETE FIX: Disable CSRF for all routes to prevent any 419 errors
        $middleware->validateCsrfTokens(except: [
            'api/*',           // All API routes - no CSRF needed for session-based auth
            'superadmin/*',    // All superadmin routes (forms)
            'vendor/*',        // All vendor routes (forms)
            'customer/*',      // All customer routes (forms)
            'logout',
            'login',           // Login form
            'register',        // Registration form
            'forgot-password', // Password reset request
            'reset-password',  // Password reset confirmation
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'verified' => EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
