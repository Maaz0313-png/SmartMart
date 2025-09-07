<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            \App\Listeners\CreateDefaultNotificationPreferences::class,
        ],

        \App\Events\OrderStatusUpdated::class => [
            \App\Listeners\SendOrderStatusNotification::class,
        ],

        \App\Events\NotificationSent::class => [
            // Add any listeners for when notifications are sent
        ],

        // You can add more event-listener mappings here
        'Illuminate\Notifications\Events\NotificationSent' => [
            // Log sent notifications
        ],

        'Illuminate\Notifications\Events\NotificationFailed' => [
            // Handle failed notifications
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}