<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $userIds,
        public Notification $notification,
        public string $type = 'general'
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        Log::info('Starting bulk notification job', [
            'user_count' => count($this->userIds),
            'type' => $this->type,
            'notification_class' => get_class($this->notification),
        ]);

        $results = $notificationService->sendBulkNotification(
            $this->userIds,
            $this->notification,
            $this->type
        );

        $successful = count(array_filter($results));
        $failed = count($this->userIds) - $successful;

        Log::info('Bulk notification job completed', [
            'total_users' => count($this->userIds),
            'successful' => $successful,
            'failed' => $failed,
            'type' => $this->type,
        ]);

        if ($failed > 0) {
            Log::warning('Some notifications failed to send', [
                'failed_count' => $failed,
                'failed_user_ids' => array_keys(array_filter($results, fn($result) => !$result)),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk notification job failed', [
            'user_count' => count($this->userIds),
            'type' => $this->type,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}