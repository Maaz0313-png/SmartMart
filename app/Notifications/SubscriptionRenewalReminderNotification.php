<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalReminderNotification extends Notification implements ShouldQueue
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
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your SmartMart Subscription Renews Soon')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your SmartMart subscription will renew in 3 days.')
            ->line('Subscription: ' . $this->subscription->plan->name)
            ->line('Next Billing Date: ' . $this->subscription->next_billing_date->format('M j, Y'))
            ->line('Amount: ' . $this->subscription->formatted_price)
            ->action('Manage Subscription', route('subscriptions.manage'))
            ->line('Thank you for being a valued SmartMart subscriber!');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Subscription Renewal Reminder',
            'message' => 'Your subscription renews in 3 days',
            'type' => 'subscription_reminder',
            'subscription_id' => $this->subscription->id,
            'renewal_date' => $this->subscription->next_billing_date->format('M j, Y'),
            'action_url' => route('subscriptions.manage'),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_reminder',
            'title' => 'Subscription Renewal Reminder',
            'message' => 'Your subscription renews in 3 days',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'renewal_date' => $this->subscription->next_billing_date->format('M j, Y'),
            'amount' => $this->subscription->formatted_price,
            'action_url' => route('subscriptions.manage'),
        ];
    }
}