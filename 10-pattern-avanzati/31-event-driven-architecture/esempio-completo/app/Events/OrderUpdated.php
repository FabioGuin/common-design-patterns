<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderData;
    public $changes;

    /**
     * Create a new event instance.
     */
    public function __construct(array $orderData, array $changes = [])
    {
        $this->orderData = $orderData;
        $this->changes = $changes;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
            new PrivateChannel('order.' . $this->orderData['id'])
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event_type' => 'OrderUpdated',
            'order_id' => $this->orderData['id'],
            'customer_name' => $this->orderData['customer_name'],
            'amount' => $this->orderData['amount'],
            'status' => $this->orderData['status'],
            'changes' => $this->changes,
            'updated_at' => $this->orderData['updated_at']
        ];
    }

    /**
     * Get the event name to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'order.updated';
    }
}
