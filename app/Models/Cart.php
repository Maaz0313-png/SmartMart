<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'total_amount',
        'item_count',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'item_count' => 'integer',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->item_count === 0;
    }

    /**
     * Update cart totals.
     */
    public function updateTotals()
    {
        $this->total_amount = $this->items()->sum('total_price');
        $this->item_count = $this->items()->sum('quantity');
        $this->save();
    }
}