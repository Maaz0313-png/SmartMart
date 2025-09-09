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
        $this->app->singleton(\App\Services\NotificationService::class);
        $this->app->singleton(\App\Services\OrderService::class);
        $this->app->singleton(\App\Services\GdprService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom notification channels
        // Only register if the notification manager is available
        $this->app->afterResolving('notification', function ($notificationManager) {
            $notificationManager->extend('sms', function ($app) {
                return $app->make(\App\Channels\SmsChannel::class);
            });
        });
    }
}
