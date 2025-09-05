<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public string $paymentMethod;
    public string $transactionId;
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, string $paymentMethod, string $transactionId, array $metadata = [])
    {
        $this->order = $order;
        $this->paymentMethod = $paymentMethod;
        $this->transactionId = $transactionId;
        $this->metadata = $metadata;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->order->user_id),
            new PrivateChannel('admin.orders'),
            new PrivateChannel('admin.finance'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'total_amount' => $this->order->total_amount,
            'payment_method' => $this->paymentMethod,
            'transaction_id' => $this->transactionId,
            'paid_at' => now(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'order.paid';
    }
}
