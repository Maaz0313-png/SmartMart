<?php

namespace App\Notifications;

use App\Models\DataRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GdprRequestProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DataRequest $dataRequest)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Your Data Request Has Been Processed')
            ->greeting('Hello ' . $notifiable->name);

        return match($this->dataRequest->type) {
            DataRequest::TYPE_EXPORT => $mail
                ->line('Your data export request has been completed.')
                ->line('You can download your data using the link below.')
                ->action('Download My Data', route('gdpr.download', $this->dataRequest))
                ->line('This download link will expire in 30 days for security reasons.'),
                
            DataRequest::TYPE_DELETE => $mail
                ->line('Your data deletion request has been completed.')
                ->line('Your personal information has been removed from our systems.')
                ->line('Some anonymized data may remain for legal and business purposes.'),
                
            default => $mail->line('Your data request has been processed.')
        };
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'gdpr_request_processed',
            'request_type' => $this->dataRequest->type,
            'request_id' => $this->dataRequest->getKey(),
            'message' => "Your {$this->dataRequest->type} request has been completed.",
        ];
    }
}