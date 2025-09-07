<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Events\NotificationSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification as LaravelNotification;

class NotificationService
{
    /**
     * Send a notification to a user with preference checking.
     */
    public function sendNotification(User $user, LaravelNotification $notification, string $type = 'general'): bool
    {
        try {
            // Get user's notification preferences for this type
            $preference = $user->notificationPreferences()
                ->where('type', $type)
                ->first();

            if (!$preference) {
                // Use default preferences if none exist
                $defaultPrefs = NotificationPreference::getDefaultTypes()[$type] ?? [
                    'email_enabled' => true,
                    'sms_enabled' => false,
                    'push_enabled' => true,
                ];

                $preference = $user->notificationPreferences()->create([
                    'type' => $type,
                    'email_enabled' => $defaultPrefs['email_enabled'],
                    'sms_enabled' => $defaultPrefs['sms_enabled'],
                    'push_enabled' => $defaultPrefs['push_enabled'],
                ]);
            }

            // Filter notification channels based on preferences
            // Ensure $notification is a custom notification, not base class
            if (!method_exists($notification, 'via')) {
                Log::error('Notification object does not implement via() method.', ['notification' => get_class($notification)]);
                return false;
            }
            $originalChannels = $notification->via($user);
            $enabledChannels = $this->filterChannelsByPreference($originalChannels, $preference);

            if (empty($enabledChannels)) {
                Log::info("No enabled channels for notification type: {$type} for user: {$user->id}");
                return false;
            }

            // Override the via method temporarily
            $notification = $this->overrideNotificationChannels($notification, $enabledChannels);

            // Send the notification
            $user->notify($notification);

            // Create database record
            $notificationRecord = $this->createNotificationRecord($user, $notification, $type);

            // Broadcast the notification event
            if (in_array('broadcast', $enabledChannels) || in_array('database', $enabledChannels)) {
                event(new NotificationSent($user, $notificationRecord));
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Filter notification channels based on user preferences.
     */
    private function filterChannelsByPreference(array $channels, NotificationPreference $preference): array
    {
        $enabledChannels = [];

        foreach ($channels as $channel) {
            switch ($channel) {
                case 'mail':
                    if ($preference->email_enabled) {
                        $enabledChannels[] = $channel;
                    }
                    break;
                case 'sms':
                case 'vonage':
                case 'twilio':
                    if ($preference->sms_enabled) {
                        $enabledChannels[] = $channel;
                    }
                    break;
                case 'broadcast':
                    if ($preference->push_enabled) {
                        $enabledChannels[] = $channel;
                    }
                    break;
                case 'database':
                    // Always allow database notifications
                    $enabledChannels[] = $channel;
                    break;
                default:
                    $enabledChannels[] = $channel;
            }
        }

        return $enabledChannels;
    }

    /**
     * Override notification channels temporarily.
     */
    private function overrideNotificationChannels(LaravelNotification $notification, array $channels): LaravelNotification
    {
        // Create a wrapper class that overrides the via method
        return new class ($notification, $channels) extends LaravelNotification {
            private $originalNotification;
            private $channels;

            public function __construct($originalNotification, $channels)
            {
                $this->originalNotification = $originalNotification;
                $this->channels = $channels;
            }

            public function via($notifiable)
            {
                return $this->channels;
            }

            public function __call($method, $arguments)
            {
                return $this->originalNotification->$method(...$arguments);
            }
        };
    }

    /**
     * Create a notification record in the database.
     */
    private function createNotificationRecord(User $user, LaravelNotification $notification, string $type): Notification
    {
        $data = method_exists($notification, 'toArray')
            ? $notification->toArray($user)
            : [];

        return Notification::create([
            'type' => $type,
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'data' => $data,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Get unread notifications count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(User $user, int $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            return $notification->markAsRead();
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->notifications()->unread()->update(['read_at' => now()]);
    }

    /**
     * Get paginated notifications for a user.
     */
    public function getUserNotifications(User $user, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $user->notifications()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Send bulk notifications to multiple users.
     */
    public function sendBulkNotification(array $userIds, LaravelNotification $notification, string $type = 'general'): array
    {
        $results = [];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $results[$userId] = $this->sendNotification($user, $notification, $type);
            } else {
                $results[$userId] = false;
            }
        }

        return $results;
    }
}