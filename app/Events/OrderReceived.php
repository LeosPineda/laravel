<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vendor;
    public $order;

    /**
     * Create a new event instance.
     */
    public function __construct(Vendor $vendor, Order $order)
    {
        $this->vendor = $vendor;
        $this->order = $order->load('items.product', 'customer');
    }

    /**
     * Get the channels the event should broadcast on.
     * FIXED: Only broadcast to one channel to avoid duplicate notifications
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor-orders.' . $this->vendor->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'VendorNewOrder';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
                'total_amount' => $this->order->total_amount,
                'payment_method' => $this->order->payment_method,
                'table_number' => $this->order->table_number,
                'special_instructions' => $this->order->special_instructions,
                'created_at' => $this->order->created_at->toISOString(),
                'items' => $this->order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'selected_addons' => $item->selected_addons,
                        'product' => [
                            'name' => $item->product->name,
                        ],
                    ];
                }),
            ],
            'message' => "New order #{$this->order->order_number} received from Table {$this->order->table_number}",
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return $this->order->status === 'pending';
    }
}
