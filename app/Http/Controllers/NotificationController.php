<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Display user notifications.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getUserNotifications($user);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, int $id)
    {
        $user = Auth::user();
        $success = $this->notificationService->markAsRead($user, $id);

        if ($success) {
            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['error' => 'Notification not found'], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'message' => "Marked {$count} notifications as read",
            'count' => $count,
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json(['count' => $count]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, int $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->delete();
            return response()->json(['message' => 'Notification deleted']);
        }

        return response()->json(['error' => 'Notification not found'], 404);
    }

    /**
     * Display notification preferences.
     */
    public function preferences(Request $request): Response
    {
        $user = Auth::user();
        $preferences = $user->notificationPreferences()
                           ->get()
                           ->keyBy('type');

        // Ensure all default types exist
        $defaultTypes = NotificationPreference::getDefaultTypes();
        foreach ($defaultTypes as $type => $defaults) {
            if (!isset($preferences[$type])) {
                $preferences[$type] = $user->notificationPreferences()->create([
                    'type' => $type,
                    'email_enabled' => $defaults['email_enabled'],
                    'sms_enabled' => $defaults['sms_enabled'],
                    'push_enabled' => $defaults['push_enabled'],
                ]);
            }
        }

        return Inertia::render('Notifications/Preferences', [
            'preferences' => $preferences->values(),
            'types' => array_keys($defaultTypes),
        ]);
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*.type' => 'required|string',
            'preferences.*.email_enabled' => 'boolean',
            'preferences.*.sms_enabled' => 'boolean',
            'preferences.*.push_enabled' => 'boolean',
        ]);

        foreach ($request->preferences as $preferenceData) {
            $user->notificationPreferences()->updateOrCreate(
                ['type' => $preferenceData['type']],
                [
                    'email_enabled' => $preferenceData['email_enabled'] ?? false,
                    'sms_enabled' => $preferenceData['sms_enabled'] ?? false,
                    'push_enabled' => $preferenceData['push_enabled'] ?? false,
                ]
            );
        }

        return back()->with('success', 'Notification preferences updated successfully!');
    }

    /**
     * Get recent notifications for dropdown/widget.
     */
    public function recent(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications()
                             ->latest()
                             ->limit(10)
                             ->get()
                             ->map(function ($notification) {
                                 return [
                                     'id' => $notification->id,
                                     'title' => $notification->title,
                                     'message' => $notification->message,
                                     'type' => $notification->type,
                                     'read' => $notification->read(),
                                     'time_ago' => $notification->time_ago,
                                     'data' => $notification->data,
                                 ];
                             });

        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}