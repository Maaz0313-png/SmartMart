<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\NotificationPreference;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDefaultNotificationPreferences implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;
        
        if ($user instanceof User) {
            $defaultTypes = NotificationPreference::getDefaultTypes();
            
            foreach ($defaultTypes as $type => $preferences) {
                $user->notificationPreferences()->create([
                    'type' => $type,
                    'email_enabled' => $preferences['email_enabled'],
                    'sms_enabled' => $preferences['sms_enabled'], 
                    'push_enabled' => $preferences['push_enabled'],
                ]);
            }
        }
    }
}