<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Searchable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'compare_price',
        'cost_price',
        'quantity',
        'min_quantity',
        'low_stock_threshold',
        'track_quantity',
        'status',
        'images',
        'image',
        'weight',
        'weight_unit',
        'dimensions',
        'category_id',
        'user_id',
        'tags',
        'meta_data',
        'is_featured',
        'is_digital',
        'seo_data',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'dimensions' => 'array',
            'tags' => 'array',
            'meta_data' => 'array',
            'seo_data' => 'array',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'track_quantity' => 'boolean',
            'published_at' => 'datetime',
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price', 'quantity', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'category' => $this->category->name ?? '',
            'tags' => $this->tags ?? [],
            'status' => $this->status,
            'is_featured' => $this->is_featured,
        ];
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    public function viewedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_product_views')
            ->withPivot('view_count', 'viewed_at')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('short_description', 'LIKE', "%{$term}%");
        });
    }

    // Helper methods
    public function getMainImageAttribute(): ?string
    {
        $images = $this->images ?? [];
        return !empty($images) ? asset('storage/' . $images[0]) : null;
    }

    public function getImageGalleryAttribute(): array
    {
        $images = $this->images ?? [];
        return array_map(fn($image) => asset('storage/' . $image), $images);
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function isInStock(): bool
    {
        if (!$this->track_quantity) {
            return true;
        }

        return $this->quantity > 0;
    }

    public function isLowStock(): bool
    {
        if (!$this->track_quantity) {
            return false;
        }

        return $this->quantity <= $this->low_stock_threshold;
    }

    public function canPurchase(): bool
    {
        return $this->status === 'active' && $this->isInStock();
    }

    public function decrementStock(int $quantity): bool
    {
        return $this->reduceStock($quantity);
    }

    public function incrementStock(int $quantity): void
    {
        if ($this->track_quantity) {
            $this->increment('quantity', $quantity);
        }
    }

    public function reduceStock(int $quantity): bool
    {
        if (!$this->track_quantity) {
            return true;
        }

        if ($this->quantity < $quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        return true;
    }

    public function increaseStock(int $quantity): void
    {
        $this->incrementStock($quantity);
    }

    // Accessors for testing compatibility
    public function getStockQuantityAttribute(): int
    {
        return $this->quantity;
    }

    public function setStockQuantityAttribute($value): void
    {
        $this->attributes['quantity'] = $value;
    }

    public function getLowStockThresholdAttribute(): int
    {
        return config('smartmart.shop.low_stock_threshold', 5);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getStockStatusAttribute(): string
    {
        if (!$this->track_quantity) {
            return 'in_stock';
        }

        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->quantity <= $this->low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getImageUrlAttribute(): string
    {
        // Handle both 'image' and 'images' attributes
        if (isset($this->attributes['image'])) {
            return asset('storage/' . $this->attributes['image']);
        }
        
        $images = $this->images ?? [];
        if (!empty($images)) {
            return asset('storage/' . $images[0]);
        }
        
        return asset('images/placeholder-product.jpg');
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function recordView(?User $user = null, ?string $sessionId = null): void
    {
        if ($user) {
            $existingView = $this->viewedByUsers()->where('user_id', $user->id)->first();

            if ($existingView) {
                $existingView->pivot->increment('view_count');
                $existingView->pivot->update(['viewed_at' => now()]);
            } else {
                $this->viewedByUsers()->attach($user->id, [
                    'view_count' => 1,
                    'viewed_at' => now(),
                ]);
            }
        } elseif ($sessionId) {
            // Handle guest views
            UserProductView::updateOrCreate(
                ['product_id' => $this->id, 'session_id' => $sessionId],
                ['viewed_at' => now()],
                ['view_count' => \DB::raw('view_count + 1')]
            );
        }
    }
}