<?php

namespace App\Services;

use App\Aggregates\Order;
use App\EventStore\EventStore;
use App\Projections\OrderProjectionHandler;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private EventStore $eventStore,
        private OrderProjectionHandler $projectionHandler
    ) {}

    public function createOrder(
        string $customerId,
        array $items,
        float $totalAmount,
        string $shippingAddress
    ): Order {
        $orderId = Str::uuid()->toString();
        $order = new Order($orderId, $customerId);
        
        $order->createOrder($customerId, $items, $totalAmount, $shippingAddress);
        
        $this->saveOrder($order);
        
        return $order;
    }

    public function payOrder(string $orderId, string $paymentMethod, string $transactionId): Order
    {
        $order = $this->getOrder($orderId);
        $order->payOrder($paymentMethod, $transactionId);
        
        $this->saveOrder($order);
        
        return $order;
    }

    public function shipOrder(string $orderId, string $trackingNumber, string $carrier): Order
    {
        $order = $this->getOrder($orderId);
        $order->shipOrder($trackingNumber, $carrier);
        
        $this->saveOrder($order);
        
        return $order;
    }

    public function deliverOrder(string $orderId, string $deliveryConfirmation): Order
    {
        $order = $this->getOrder($orderId);
        $order->deliverOrder($deliveryConfirmation);
        
        $this->saveOrder($order);
        
        return $order;
    }

    public function cancelOrder(string $orderId, string $reason): Order
    {
        $order = $this->getOrder($orderId);
        $order->cancelOrder($reason);
        
        $this->saveOrder($order);
        
        return $order;
    }

    public function refundOrder(string $orderId, float $refundAmount, string $reason): Order
    {
        $order = $this->getOrder($orderId);
        $order->refundOrder($refundAmount, $reason);
        
        $this->saveOrder($order);
        
        return $order;
    }

    public function getOrder(string $orderId): Order
    {
        $events = $this->eventStore->getEvents($orderId);
        
        if (empty($events)) {
            throw new \InvalidArgumentException("Order {$orderId} not found");
        }
        
        return Order::fromHistory($events);
    }

    public function getOrderProjection(string $orderId): ?\App\Models\OrderProjection
    {
        return \App\Models\OrderProjection::where('order_id', $orderId)->first();
    }

    public function getAllOrderProjections(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\OrderProjection::orderBy('created_at', 'desc')->get();
    }

    public function getOrderEvents(string $orderId): array
    {
        return $this->eventStore->getEvents($orderId);
    }

    public function getAllEvents(): array
    {
        return $this->eventStore->getAllEvents();
    }

    private function saveOrder(Order $order): void
    {
        $events = $order->getUncommittedEvents();
        
        if (empty($events)) {
            return;
        }
        
        $expectedVersion = $order->getVersion() - count($events);
        $this->eventStore->saveEvents($order->getOrderId(), $events, $expectedVersion);
        
        // Aggiorna proiezioni
        foreach ($events as $event) {
            $this->projectionHandler->handle($event);
        }
        
        $order->clearUncommittedEvents();
    }
}
