<?php

namespace App\Notifications;

use App\Models\SubscriptionBox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionBoxShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public SubscriptionBox $box;

    /**
     * Create a new notification instance.
     */
    public function __construct(SubscriptionBox $box)
    {
        $this->box = $box;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'sms'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your SmartMart Box Has Shipped!')
            ->greeting('Great news, ' . $notifiable->name . '!')
            ->line('Your SmartMart subscription box has been shipped and is on its way to you.')
            ->line('Box Number: ' . $this->box->box_number)
            ->line('Estimated Value: ' . $this->box->formatted_value)
            ->line('Ship Date: ' . $this->box->ship_date->format('M j, Y'));

        if ($this->box->tracking_info && isset($this->box->tracking_info['tracking_number'])) {
            $message->line('Tracking Number: ' . $this->box->tracking_info['tracking_number']);
            
            if (isset($this->box->tracking_info['tracking_url'])) {
                $message->action('Track Your Package', $this->box->tracking_info['tracking_url']);
            }
        }

        return $message->line('Thank you for being a SmartMart subscriber!');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        $message = "Your SmartMart box #{$this->box->box_number} has shipped!";
        
        if ($this->box->tracking_info && isset($this->box->tracking_info['tracking_number'])) {
            $message .= " Tracking: {$this->box->tracking_info['tracking_number']}";
        }
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'box_shipped',
            'box_id' => $this->box->id,
            'box_number' => $this->box->box_number,
            'value' => $this->box->formatted_value,
            'tracking_info' => $this->box->tracking_info,
            'message' => 'Your subscription box has been shipped!',
        ];
    }
}