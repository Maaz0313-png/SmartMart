<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Subscription $subscription;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed - Action Required')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We were unable to process the payment for your SmartMart subscription.')
            ->line('Subscription: ' . $this->subscription->plan->name)
            ->line('Amount: ' . $this->subscription->formatted_price)
            ->line('Please update your payment method to continue receiving your subscription boxes.')
            ->action('Update Payment Method', route('subscriptions.manage'))
            ->line('If you don\'t update your payment method within 7 days, your subscription will be cancelled.')
            ->line('Thank you for being a valued SmartMart subscriber!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_failed',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'amount' => $this->subscription->formatted_price,
            'message' => 'Your subscription payment has failed. Please update your payment method.',
        ];
    }
}