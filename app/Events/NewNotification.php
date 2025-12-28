<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\Vendor;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewNotification implements ShouldBroadcast
{
    use Dispatchable;

    public $vendor;
    public $notification;

    public function __construct(Vendor $vendor, Notification $notification)
    {
        $this->vendor = $vendor;
        $this->notification = $notification->load('order');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor-notifications.' . $this->vendor->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewNotification';
    }

    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id' => $this->notification->id,
                'type' => $this->notification->type,
                'title' => $this->notification->title,
                'message' => $this->notification->message,
                'order_id' => $this->notification->order_id,
                'is_read' => $this->notification->is_read,
                'created_at' => $this->notification->created_at->toISOString(),
                'formatted_time' => $this->notification->formatted_time,
                'order' => $this->notification->order ? [
                    'id' => $this->notification->order->id,
                    'order_number' => $this->notification->order->order_number,
                    'status' => $this->notification->order->status,
                ] : null,
            ]
        ];
    }
}
