<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // WhatsApp Interface Binding - REMOVED (Microservice used directly via Http facade)
        // $this->app->bind(
        //    \App\Contracts\WhatsAppServiceInterface::class,
        //    \App\Services\WhatsApp\TwilioWhatsAppService::class
        // );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
