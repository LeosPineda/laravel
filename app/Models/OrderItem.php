<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'selected_addons',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'selected_addons' => 'array',
    ];

    /**
     * Get the order this item belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for this order item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the addons for this order item.
     */
    public function getSelectedAddonsAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set the addons for this order item.
     */
    public function setSelectedAddonsAttribute($value): void
    {
        $this->attributes['selected_addons'] = $value ? json_encode($value) : null;
    }

    /**
     * Calculate total price including addons.
     */
    public function calculateTotal(): float
    {
        $basePrice = $this->quantity * $this->unit_price;
        $addonsPrice = collect($this->selected_addons)->sum('price') * $this->quantity;

        return $basePrice + $addonsPrice;
    }

    /**
     * Get addon total price.
     */
    public function getAddonTotal(): float
    {
        return collect($this->selected_addons)->sum('price') * $this->quantity;
    }

    /**
     * Scope to get items by product.
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to get items by order.
     */
    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}
