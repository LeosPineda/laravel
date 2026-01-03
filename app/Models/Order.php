<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'vendor_id',
        'vendor_group_id',
        'order_number',
        'status',
        'total_amount',
        'payment_method',
        'table_number',
        'special_instructions',
        'decline_reason',
        'payment_proof_url',
        'receipt_url',
        'completed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer who placed this order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the vendor for this order.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the items for this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if order is ready for pickup.
     */
    public function isReadyForPickup(): bool
    {
        return $this->status === 'ready_for_pickup';
    }

    /**
     * Check if order is completed.
     * Now: ready_for_pickup IS the completion status
     */
    public function isCompleted(): bool
    {
        return $this->status === 'ready_for_pickup';
    }

    /**
     * Check if order is cancelled/declined.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get order status for display.
     * Updated: ready_for_pickup shows as "Completed"
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'ready_for_pickup' => 'Completed', // Now displays as "Completed"
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get payment method display.
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match ($this->payment_method) {
            'qr_code' => 'QR Code',
            'cash' => 'Cashier',
            default => ucfirst($this->payment_method),
        };
    }

    /**
     * Get decline reason display.
     * UPDATED: Handle single pre-written reason
     */
    public function getDeclineReasonDisplayAttribute(): string
    {
        if (!$this->decline_reason) {
            return '';
        }

        // Check if it's the pre-written reason
        if ($this->decline_reason === 'cannot_prepare') {
            return 'Cannot prepare the order at the moment';
        }

        // Custom reason (user entered text)
        return $this->decline_reason;
    }

    /**
     * Scope to get orders by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get orders for a specific vendor.
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope to get orders by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
