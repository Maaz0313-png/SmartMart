<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\VonageMessage;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $otp;
    public string $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otp, string $type = 'email')
    {
        $this->otp = $otp;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->type === 'email') {
            $channels[] = 'mail';
        } elseif ($this->type === 'sms' && config('services.twilio.enabled')) {
            $channels[] = 'vonage'; // or 'twilio' if using Twilio
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your SmartMart Verification Code')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your verification code is: **' . $this->otp . '**')
            ->line('This code will expire in 5 minutes.')
            ->line('If you did not request this code, please ignore this email.')
            ->line('Thank you for using SmartMart!');
    }

    /**
     * Get the Vonage / SMS representation of the notification.
     */
    public function toVonage(object $notifiable): VonageMessage
    {
        return (new VonageMessage)
            ->content("Your SmartMart verification code is: {$this->otp}. Valid for 5 minutes.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'otp_verification',
            'message' => "OTP sent via {$this->type}",
            'expires_at' => now()->addMinutes(5),
        ];
    }
}