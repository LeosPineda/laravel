<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vendor;
    public $order;
    public $customer;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Vendor $vendor, Order $order, User $customer, string $oldStatus, string $newStatus)
    {
        $this->vendor = $vendor;
        $this->order = $order->load('items.product');
        $this->customer = $customer;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('vendor-orders.' . $this->vendor->id),
            new PrivateChannel('customer-orders.' . $this->customer->id),
        ];

        // Add vendor-toasts channel for customer cancellations
        if ($this->newStatus === 'cancelled') {
            $channels[] = new PrivateChannel('vendor-toasts.' . $this->vendor->id);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     * FIXED: Use different names for different channels to avoid conflicts
     */
    public function broadcastAs(): string
    {
        // For vendor-toasts channel, use a different event name
        return 'VendorOrderCancelled';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'customer_id' => $this->customer->id,
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'total_amount' => $this->order->total_amount,
                'payment_method' => $this->order->payment_method,
                'table_number' => $this->order->table_number,
                'updated_at' => $this->order->updated_at->toISOString(),
            ],
            'message' => $this->getStatusMessage(),
        ];
    }

    /**
     * Determine if this event should broadcast.
     * FIXED: Removed 'completed' - vendor work ends at 'ready_for_pickup'
     */
    public function broadcastWhen(): bool
    {
        return in_array($this->newStatus, ['pending', 'accepted', 'ready_for_pickup', 'cancelled']);
    }

    /**
     * Get status change message.
     * UPDATED: Include decline reason for cancelled orders
     */
    private function getStatusMessage(): string
    {
        if ($this->newStatus === 'cancelled' && $this->order->decline_reason) {
            $declineReasonDisplay = $this->getDeclineReasonDisplay($this->order->decline_reason);
            return "Order #{$this->order->order_number} has been declined. Reason: {$declineReasonDisplay}";
        }

        return match ($this->newStatus) {
            'pending' => "Order #{$this->order->order_number} has been placed and is awaiting vendor response.",
            'accepted' => "Order #{$this->order->order_number} has been accepted and is being prepared.",
            'ready_for_pickup' => "Order #{$this->order->order_number} is ready for pickup!",
            'cancelled' => "Order #{$this->order->order_number} has been cancelled.",
            default => "Order #{$this->order->order_number} status updated to {$this->newStatus}.",
        };
    }

    /**
     * Get decline reason display for customers.
     * UPDATED: Handle single pre-written reason
     */
    private function getDeclineReasonDisplay(string $reason): string
    {
        // Check if it's the pre-written reason
        if ($reason === 'cannot_prepare') {
            return 'Cannot prepare the order at the moment';
        }

        // Custom reason (user entered text)
        return $reason;
    }
}
