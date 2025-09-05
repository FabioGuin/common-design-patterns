<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $paymentData;

    /**
     * Create a new event instance.
     */
    public function __construct(array $paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('payments'),
            new PrivateChannel('order.' . $this->paymentData['order_id'])
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event_type' => 'PaymentProcessed',
            'payment_id' => $this->paymentData['payment_id'],
            'order_id' => $this->paymentData['order_id'],
            'amount' => $this->paymentData['amount'],
            'payment_method' => $this->paymentData['payment_method'],
            'status' => $this->paymentData['status'],
            'processed_at' => $this->paymentData['processed_at']
        ];
    }

    /**
     * Get the event name to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'payment.processed';
    }
}
