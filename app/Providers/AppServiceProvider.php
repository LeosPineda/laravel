<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when behind ngrok or other HTTPS proxies
        // This fixes "Mixed Content" errors
        if (request()->header('X-Forwarded-Proto') === 'https' ||
            str_contains(config('app.url', ''), 'https://') ||
            str_contains(request()->header('Host', ''), 'ngrok')) {
            URL::forceScheme('https');
        }

        // Load broadcasting channels
        Broadcast::routes();
        require base_path('routes/channels.php');
    }
}
