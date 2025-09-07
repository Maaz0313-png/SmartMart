<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'payment_status',
        'payment_method',
        'payment_reference',
        'billing_address',
        'shipping_address',
        'coupon_code',
        'notes',
        'shipped_at',
        'delivered_at',
        'tracking_info',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'tracking_info' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Helper methods
    public function getFormattedTotalAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return config('smartmart.orders.statuses')[$this->status] ?? ucfirst($this->status);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function canBeShipped(): bool
    {
        return $this->status === 'processing' && $this->payment_status === 'paid';
    }

    public function canBeRefunded(): bool
    {
        return $this->payment_status === 'paid' &&
            in_array($this->status, ['delivered', 'shipped']) &&
            $this->created_at->diffInDays(now()) <= config('smartmart.orders.refund_window', 30);
    }

    public function markAsShipped(array $trackingInfo = []): void
    {
        $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
            'tracking_info' => $trackingInfo,
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        if ($this->canBeCancelled()) {
            $this->update(['status' => 'cancelled']);

            // Restore stock for all items
            foreach ($this->items as $item) {
                if ($item->product && $item->product->track_quantity) {
                    $item->product->incrementStock($item->quantity);
                }
            }
        }
    }

    public static function generateOrderNumber(): string
    {
        $prefix = config('smartmart.orders.number_prefix', 'SM');
        $length = config('smartmart.orders.number_length', 8);

        do {
            $number = $prefix . str_pad(
                random_int(0, pow(10, $length - strlen($prefix)) - 1),
                $length - strlen($prefix),
                '0',
                STR_PAD_LEFT
            );
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum(\DB::raw('quantity * unit_price'));
        $taxRate = config('smartmart.shop.tax_rate', 0);
        $taxAmount = $subtotal * $taxRate;

        $shippingAmount = $this->shipping_amount ?? 0;
        if ($subtotal >= config('smartmart.shop.free_shipping_threshold', 100)) {
            $shippingAmount = 0;
        }

        $discountAmount = $this->discount_amount ?? 0;
        $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => max(0, $totalAmount),
        ]);
    }
}