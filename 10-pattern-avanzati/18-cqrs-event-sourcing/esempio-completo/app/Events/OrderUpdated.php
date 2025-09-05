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

    protected $eventId;
    protected $data;
    protected $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(array $data)
    {
        $this->eventId = uniqid('event_', true);
        $this->data = $data;
        $this->timestamp = now();
    }

    /**
     * Get the event ID
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Get the event data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the event timestamp
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Get the event type
     */
    public function getEventType()
    {
        return 'OrderUpdated';
    }

    /**
     * Get the aggregate ID
     */
    public function getAggregateId()
    {
        return $this->data['order_id'] ?? null;
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('orders'),
            new PrivateChannel('orders.' . $this->getAggregateId())
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => $this->getEventType(),
            'aggregate_id' => $this->getAggregateId(),
            'data' => $this->data,
            'timestamp' => $this->timestamp->toISOString()
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs()
    {
        return 'order.updated';
    }

    /**
     * Convert the event to array
     */
    public function toArray()
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => $this->getEventType(),
            'aggregate_id' => $this->getAggregateId(),
            'data' => $this->data,
            'timestamp' => $this->timestamp->toISOString(),
            'version' => 1
        ];
    }
}
