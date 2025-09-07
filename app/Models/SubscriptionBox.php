<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'box_number',
        'status',
        'products',
        'value',
        'ship_date',
        'tracking_info',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'products' => 'array',
        'value' => 'decimal:2',
        'ship_date' => 'date',
        'tracking_info' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the subscription that owns this box.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the formatted value.
     */
    public function getFormattedValueAttribute(): string
    {
        return '$' . number_format($this->value, 2);
    }

    /**
     * Check if box is shipped.
     */
    public function isShipped(): bool
    {
        return $this->status === 'shipped' || $this->status === 'delivered';
    }

    /**
     * Check if box is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'packed' => 'blue',
            'shipped' => 'purple',
            'delivered' => 'green',
            default => 'gray',
        };
    }

    /**
     * Scope to get boxes by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get shipped boxes.
     */
    public function scopeShipped($query)
    {
        return $query->whereIn('status', ['shipped', 'delivered']);
    }
}