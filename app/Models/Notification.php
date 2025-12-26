<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'user_id',
        'type',
        'title',
        'message',
        'order_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [];

    /**
     * Get the vendor that owns the notification.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user (customer) that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the notification.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope a query to only include notifications for a specific vendor.
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope a query to only include notifications for a specific user (customer).
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to filter by notification type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by notification types.
     */
    public function scopeOfTypes($query, array $types)
    {
        return $query->whereIn('type', $types);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false]);
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return $this->is_read;
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread(): bool
    {
        return !$this->is_read;
    }

    /**
     * Get the notification's formatted time.
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match ($this->type) {
            'order' => 'Order Notification',
            'system' => 'System Alert',
            'payment' => 'Payment Update',
            'general' => 'General Notice',
            default => 'Notification',
        };
    }

    /**
     * Get notification type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'order' => '🛒',
            'system' => '⚙️',
            'payment' => '💳',
            'general' => '📢',
            default => '🔔',
        };
    }

    /**
     * Get notification urgency level.
     */
    public function getUrgencyLevelAttribute(): string
    {
        return match ($this->type) {
            'order' => 'high',
            'payment' => 'medium',
            'system' => 'low',
            'general' => 'low',
            default => 'low',
        };
    }

    /**
     * Get CSS class based on urgency level.
     */
    public function getUrgencyClassAttribute(): string
    {
        return match ($this->urgency_level) {
            'high' => 'bg-red-100 text-red-800 border-red-200',
            'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'low' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    /**
     * Get unread notification count for a vendor.
     */
    public static function unreadCountForVendor(int $vendorId): int
    {
        return static::forVendor($vendorId)->unread()->count();
    }

    /**
     * Get recent notifications for a vendor.
     */
    public static function recentForVendor(int $vendorId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::forVendor($vendorId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new notification.
     */
    public static function createNotification(
        int $vendorId,
        string $type,
        string $title,
        string $message,
        ?int $orderId = null
    ): static {
        return static::create([
            'vendor_id' => $vendorId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'order_id' => $orderId,
            'is_read' => false,
        ]);
    }

    /**
     * Mark multiple notifications as read for a vendor.
     */
    public static function markAllAsReadForVendor(int $vendorId): int
    {
        return static::forVendor($vendorId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Delete old notifications for a vendor.
     */
    public static function deleteOldNotificationsForVendor(int $vendorId, int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return static::forVendor($vendorId)
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Find notification by ID and vendor ID with error handling.
     */
    public static function findForVendor(int $notificationId, int $vendorId): ?static
    {
        try {
            return static::where('id', $notificationId)
                ->where('vendor_id', $vendorId)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            return null;
        }
    }
}
