<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'selected_addons',
        'special_instructions',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'selected_addons' => 'array',
        'special_instructions' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the cart that owns the cart item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product for this cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get total price for this cart item (including addons).
     */
    public function getTotalPriceAttribute(): float
    {
        $addonsTotal = collect($this->selected_addons)->sum('price') ?? 0;
        return ($this->unit_price + $addonsTotal) * $this->quantity;
    }

    /**
     * Get addons total price for this item.
     */
    public function getAddonsTotalAttribute(): float
    {
        return collect($this->selected_addons)->sum('price') ?? 0;
    }

    /**
     * Get formatted addon names.
     */
    public function getAddonNamesAttribute(): string
    {
        if (!$this->selected_addons) {
            return '';
        }

        return collect($this->selected_addons)->pluck('name')->join(', ');
    }

    /**
     * Scope to filter by cart ID.
     */
    public function scopeForCart($query, int $cartId)
    {
        $query->where('cart_id', $cartId);
    }

    /**
     * Scope to filter by product ID.
     */
    public function scopeForProduct($query, int $productId)
    {
        $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by quantity range.
     */
    public function scopeWithQuantity($query, int $minQuantity = 1, int $maxQuantity = null)
    {
        $query->where('quantity', '>=', $minQuantity);
        if ($maxQuantity) {
            $query->where('quantity', '<=', $maxQuantity);
        }
        return $query;
    }

    /**
     * Check if item has addons.
     */
    public function hasAddons(): bool
    {
        return !empty($this->selected_addons);
    }

    /**
     * Check if item has special instructions.
     */
    public function hasSpecialInstructions(): bool
    {
        return !empty($this->special_instructions);
    }

    /**
     * Get addon by ID.
     */
    public function getAddon(int $addonId): ?array
    {
        if (!$this->selected_addons) {
            return null;
        }

        return collect($this->selected_addons)->firstWhere('id', $addonId);
    }

    /**
     * Check if specific addon is selected.
     */
    public function hasAddon(int $addonId): bool
    {
        return $this->getAddon($addonId) !== null;
    }

    /**
     * Update selected addons.
     */
    public function updateAddons(array $addons): bool
    {
        return $this->update(['selected_addons' => $addons]);
    }

    /**
     * Add addon to item.
     */
    public function addAddon(array $addon): bool
    {
        $addons = $this->selected_addons ?? [];
        $addons[] = $addon;
        return $this->update(['selected_addons' => $addons]);
    }

    /**
     * Remove addon from item.
     */
    public function removeAddon(int $addonId): bool
    {
        if (!$this->selected_addons) {
            return false;
        }

        $addons = collect($this->selected_addons)->reject(function ($addon) use ($addonId) {
            return $addon['id'] === $addonId;
        })->values()->all();

        return $this->update(['selected_addons' => $addons]);
    }

    /**
     * Clear all addons from item.
     */
    public function clearAddons(): bool
    {
        return $this->update(['selected_addons' => []]);
    }

    /**
     * Get item summary for display.
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'addons_total' => $this->addons_total,
            'addon_names' => $this->addon_names,
            'has_addons' => $this->hasAddons(),
            'special_instructions' => $this->special_instructions,
            'has_special_instructions' => $this->hasSpecialInstructions(),
            'selected_addons' => $this->selected_addons,
        ];
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($cartItem) {
            // Ensure selected_addons is always an array
            if (is_string($cartItem->selected_addons)) {
                $cartItem->selected_addons = json_decode($cartItem->selected_addons, true);
            }
        });

        static::retrieved(function ($cartItem) {
            // Ensure selected_addons is always an array when retrieved
            if (is_string($cartItem->selected_addons)) {
                $cartItem->selected_addons = json_decode($cartItem->selected_addons, true);
            }
        });
    }
}
