<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor that owns the cart.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get all cart items for this cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Scope a query to only include carts for a specific customer.
     */
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('user_id', $customerId);
    }

    /**
     * Scope a query to only include carts for a specific vendor.
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Get total quantity of items in cart.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Calculate total price of cart items.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->items->reduce(function ($total, $item) {
            $addonsTotal = collect($item->selected_addons)->sum('price') ?? 0;
            return $total + (($item->unit_price + $addonsTotal) * $item->quantity);
        }, 0);
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Clear all items from cart.
     */
    public function clear(): bool
    {
        return $this->items()->delete();
    }

    /**
     * Find cart by customer and vendor ID with error handling.
     */
    public static function findByCustomerAndVendor(int $customerId, int $vendorId): ?static
    {
        try {
            return static::where('user_id', $customerId)
                ->where('vendor_id', $vendorId)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /**
     * Get or create cart for customer and vendor.
     */
    public static function getOrCreate(int $customerId, int $vendorId): static
    {
        return static::firstOrCreate(
            [
                'user_id' => $customerId,
                'vendor_id' => $vendorId,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Get cart summary for customer.
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $this->vendor->brand_name,
            'total_quantity' => $this->total_quantity,
            'total_price' => $this->total_price,
            'item_count' => $this->items->count(),
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($cart) {
            // Delete all cart items when cart is deleted
            $cart->items()->delete();
        });
    }
}
