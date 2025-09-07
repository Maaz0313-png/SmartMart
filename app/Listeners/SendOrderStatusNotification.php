<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Notifications\OrderStatusNotification;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdated $event): void
    {
        $notification = new OrderStatusNotification($event->order, $event->previousStatus);
        
        $this->notificationService->sendNotification(
            $event->order->user,
            $notification,
            'order_updates'
        );
    }
}