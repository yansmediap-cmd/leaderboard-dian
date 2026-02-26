<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('api-login', fn (Request $request) => [
            Limit::perMinute(20)->by($request->ip()),
        ]);

        RateLimiter::for('api-ingest', fn (Request $request) => [
            Limit::perMinute(120)->by((string) ($request->user()?->id ?? $request->ip())),
        ]);

        RateLimiter::for('api-read', fn (Request $request) => [
            Limit::perMinute(240)->by((string) ($request->user()?->id ?? $request->ip())),
        ]);
    }
}
