<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'price',
        'category',
        'image_url',
        'is_active',
        // ✅ CHANGED: stock_quantity removed from fillable (required but not user-set)
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the vendor for this product.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the addons for this product.
     */
    public function addons(): HasMany
    {
        return $this->hasMany(Addon::class);
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if product is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Decrement stock quantity.
     */
    public function decrementStock(int $quantity): bool
    {
        if ($this->stock_quantity >= $quantity) {
            $this->decrement('stock_quantity', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Increment stock quantity.
     */
    public function incrementStock(int $quantity): void
    {
        $this->increment('stock_quantity', $quantity);
    }

    /**
     * Get price formatted for display.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Get image URL with fallback.
     */
    public function getImageUrlAttribute($value): ?string
    {
        return $value ? asset('storage/' . $value) : null;
    }

    /**
     * Scope to get products by vendor.
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope to get active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get products by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', 'like', '%' . $category . '%');
    }

    /**
     * Scope to get products with stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope to search products.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }
}
