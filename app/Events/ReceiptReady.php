<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceiptReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vendor;
    public $order;
    public $customer;

    /**
     * Create a new event instance.
     */
    public function __construct(Vendor $vendor, Order $order, $customer)
    {
        $this->vendor = $vendor;
        $this->order = $order->load('items.product');
        $this->customer = $customer;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('customer-orders.'.$this->customer->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ReceiptReady';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'customer_id' => $this->customer->id,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => "Your receipt for order #{$this->order->order_number} is ready! You can download and print it.",
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return true;
    }
}
