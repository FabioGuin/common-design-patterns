<?php

namespace App\Aggregates;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Events\OrderShipped;
use App\Events\OrderDelivered;
use App\Events\OrderCancelled;
use App\Events\OrderRefunded;

class Order
{
    private array $uncommittedEvents = [];
    private int $version = 0;
    
    public function __construct(
        private string $orderId,
        private string $customerId,
        private array $items = [],
        private float $totalAmount = 0.0,
        private string $shippingAddress = '',
        private string $status = 'created',
        private ?string $paymentMethod = null,
        private ?string $transactionId = null,
        private ?string $trackingNumber = null,
        private ?string $carrier = null,
        private ?string $deliveryConfirmation = null,
        private ?string $cancellationReason = null,
        private ?float $refundAmount = null,
        private ?string $refundReason = null
    ) {}

    public static function fromHistory(array $events): self
    {
        $order = new self('', '');
        
        foreach ($events as $event) {
            $order->apply($event);
        }
        
        return $order;
    }

    public function createOrder(
        string $customerId,
        array $items,
        float $totalAmount,
        string $shippingAddress
    ): void {
        if ($this->status !== 'created') {
            throw new \InvalidArgumentException('Order already exists');
        }

        $this->apply(new OrderCreated(
            $this->orderId,
            $customerId,
            $items,
            $totalAmount,
            $shippingAddress,
            new \DateTimeImmutable()
        ));
    }

    public function payOrder(string $paymentMethod, string $transactionId): void
    {
        if ($this->status !== 'created') {
            throw new \InvalidArgumentException('Order cannot be paid in current status: ' . $this->status);
        }

        $this->apply(new OrderPaid(
            $this->orderId,
            $paymentMethod,
            $transactionId,
            new \DateTimeImmutable()
        ));
    }

    public function shipOrder(string $trackingNumber, string $carrier): void
    {
        if ($this->status !== 'paid') {
            throw new \InvalidArgumentException('Order must be paid before shipping');
        }

        $this->apply(new OrderShipped(
            $this->orderId,
            $trackingNumber,
            $carrier,
            new \DateTimeImmutable()
        ));
    }

    public function deliverOrder(string $deliveryConfirmation): void
    {
        if ($this->status !== 'shipped') {
            throw new \InvalidArgumentException('Order must be shipped before delivery');
        }

        $this->apply(new OrderDelivered(
            $this->orderId,
            $deliveryConfirmation,
            new \DateTimeImmutable()
        ));
    }

    public function cancelOrder(string $reason): void
    {
        if (in_array($this->status, ['delivered', 'cancelled', 'refunded'])) {
            throw new \InvalidArgumentException('Order cannot be cancelled in current status: ' . $this->status);
        }

        $this->apply(new OrderCancelled(
            $this->orderId,
            $reason,
            new \DateTimeImmutable()
        ));
    }

    public function refundOrder(float $refundAmount, string $reason): void
    {
        if (!in_array($this->status, ['delivered', 'cancelled'])) {
            throw new \InvalidArgumentException('Order must be delivered or cancelled before refund');
        }

        if ($refundAmount > $this->totalAmount) {
            throw new \InvalidArgumentException('Refund amount cannot exceed order total');
        }

        $this->apply(new OrderRefunded(
            $this->orderId,
            $refundAmount,
            $reason,
            new \DateTimeImmutable()
        ));
    }

    private function apply(object $event): void
    {
        $this->uncommittedEvents[] = $event;
        $this->version++;

        match (get_class($event)) {
            OrderCreated::class => $this->applyOrderCreated($event),
            OrderPaid::class => $this->applyOrderPaid($event),
            OrderShipped::class => $this->applyOrderShipped($event),
            OrderDelivered::class => $this->applyOrderDelivered($event),
            OrderCancelled::class => $this->applyOrderCancelled($event),
            OrderRefunded::class => $this->applyOrderRefunded($event),
            default => null,
        };
    }

    private function applyOrderCreated(OrderCreated $event): void
    {
        $this->orderId = $event->orderId;
        $this->customerId = $event->customerId;
        $this->items = $event->items;
        $this->totalAmount = $event->totalAmount;
        $this->shippingAddress = $event->shippingAddress;
        $this->status = 'created';
    }

    private function applyOrderPaid(OrderPaid $event): void
    {
        $this->paymentMethod = $event->paymentMethod;
        $this->transactionId = $event->transactionId;
        $this->status = 'paid';
    }

    private function applyOrderShipped(OrderShipped $event): void
    {
        $this->trackingNumber = $event->trackingNumber;
        $this->carrier = $event->carrier;
        $this->status = 'shipped';
    }

    private function applyOrderDelivered(OrderDelivered $event): void
    {
        $this->deliveryConfirmation = $event->deliveryConfirmation;
        $this->status = 'delivered';
    }

    private function applyOrderCancelled(OrderCancelled $event): void
    {
        $this->cancellationReason = $event->reason;
        $this->status = 'cancelled';
    }

    private function applyOrderRefunded(OrderRefunded $event): void
    {
        $this->refundAmount = $event->refundAmount;
        $this->refundReason = $event->refundReason;
        $this->status = 'refunded';
    }

    // Getters
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getUncommittedEvents(): array
    {
        return $this->uncommittedEvents;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function clearUncommittedEvents(): void
    {
        $this->uncommittedEvents = [];
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'customer_id' => $this->customerId,
            'items' => $this->items,
            'total_amount' => $this->totalAmount,
            'shipping_address' => $this->shippingAddress,
            'status' => $this->status,
            'payment_method' => $this->paymentMethod,
            'transaction_id' => $this->transactionId,
            'tracking_number' => $this->trackingNumber,
            'carrier' => $this->carrier,
            'delivery_confirmation' => $this->deliveryConfirmation,
            'cancellation_reason' => $this->cancellationReason,
            'refund_amount' => $this->refundAmount,
            'refund_reason' => $this->refundReason,
            'version' => $this->version,
        ];
    }
}
