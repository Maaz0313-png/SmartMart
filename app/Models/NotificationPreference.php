<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the notification preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the enabled channels for this preference.
     */
    public function getEnabledChannelsAttribute(): array
    {
        $channels = [];

        if ($this->email_enabled) {
            $channels[] = 'mail';
        }

        if ($this->sms_enabled) {
            $channels[] = 'sms';
        }

        if ($this->push_enabled) {
            $channels[] = 'broadcast';
        }

        // Always include database notifications
        $channels[] = 'database';

        return $channels;
    }

    /**
     * Check if any notification channel is enabled.
     */
    public function hasEnabledChannels(): bool
    {
        return $this->email_enabled || $this->sms_enabled || $this->push_enabled;
    }

    /**
     * Default notification types and their settings.
     */
    public static function getDefaultTypes(): array
    {
        return [
            'order_updates' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => true,
            ],
            'subscription_updates' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => true,
            ],
            'payment_notifications' => [
                'email_enabled' => true,
                'sms_enabled' => true,
                'push_enabled' => true,
            ],
            'promotions' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
            ],
            'recommendations' => [
                'email_enabled' => false,
                'sms_enabled' => false,
                'push_enabled' => true,
            ],
            'security_alerts' => [
                'email_enabled' => true,
                'sms_enabled' => true,
                'push_enabled' => true,
            ],
        ];
    }
}