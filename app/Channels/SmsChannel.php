<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    protected Client $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable->phone) {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (!$message) {
            return;
        }

        try {
            $this->twilio->messages->create(
                $notifiable->phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message,
                ]
            );

            Log::info('SMS notification sent', [
                'to' => $notifiable->phone,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'to' => $notifiable->phone,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}