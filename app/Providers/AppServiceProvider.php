<?php

namespace App\Providers;

use App\Mail\Transport\ZeptoMailTransport;
use App\Services\MailBrandingService;
use App\Services\MailSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
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
        Mail::extend('zeptomail', fn (array $config) => new ZeptoMailTransport(
            app(HttpFactory::class),
            $config['host'],
            $config['api_key'],
            (int) ($config['timeout'] ?? 30),
        ));

        app(MailSettingsService::class)->apply();
        app(MailBrandingService::class)->apply();

        RateLimiter::for('api', function (Request $request) {
            $key = optional($request->user())->id ?: $request->ip();

            return Limit::perMinute(120)->by((string) $key);
        });
    }
}
