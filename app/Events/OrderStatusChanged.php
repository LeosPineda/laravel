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
        return [
            new PrivateChannel('vendor-orders.' . $this->vendor->id),
            new PrivateChannel('customer-orders.' . $this->customer->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'OrderStatusChanged';
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
     * FIXED: Added 'pending' status so new orders broadcast to customers
     */
    public function broadcastWhen(): bool
    {
        return in_array($this->newStatus, ['pending', 'accepted', 'ready_for_pickup', 'completed', 'cancelled']);
    }

    /**
     * Get status change message.
     */
    private function getStatusMessage(): string
    {
        return match ($this->newStatus) {
            'pending' => "Order #{$this->order->order_number} has been placed and is awaiting vendor response.",
            'accepted' => "Order #{$this->order->order_number} has been accepted and is being prepared.",
            'ready_for_pickup' => "Order #{$this->order->order_number} is ready for pickup!",
            'completed' => "Order #{$this->order->order_number} has been completed. Thank you!",
            'cancelled' => "Order #{$this->order->order_number} has been declined by the vendor.",
            default => "Order #{$this->order->order_number} status updated to {$this->newStatus}.",
        };
    }
}
