<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'trial_days',
        'features',
        'stripe_plan_id',
        'paypal_plan_id',
        'is_active',
        'max_products',
        'categories',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'categories' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get active subscriptions for this plan.
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('status', 'active');
    }

    /**
     * Scope to get active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the billing cycle display name.
     */
    public function getBillingCycleDisplayAttribute(): string
    {
        return match ($this->billing_cycle) {
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            default => ucfirst($this->billing_cycle),
        };
    }

    /**
     * Calculate next billing date from a given date.
     */
    public function calculateNextBillingDate(\Carbon\Carbon $from): \Carbon\Carbon
    {
        return match ($this->billing_cycle) {
            'weekly' => $from->addWeek(),
            'monthly' => $from->addMonth(),
            'quarterly' => $from->addMonths(3),
            'yearly' => $from->addYear(),
            default => $from->addMonth(),
        };
    }

    /**
     * Check if plan has feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }
}