<?php

namespace App\Projections;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Events\OrderShipped;
use App\Events\OrderDelivered;
use App\Events\OrderCancelled;
use App\Events\OrderRefunded;
use App\Models\OrderProjection;
use Illuminate\Support\Facades\DB;

class OrderProjectionHandler
{
    public function handle(object $event): void
    {
        match (get_class($event)) {
            OrderCreated::class => $this->handleOrderCreated($event),
            OrderPaid::class => $this->handleOrderPaid($event),
            OrderShipped::class => $this->handleOrderShipped($event),
            OrderDelivered::class => $this->handleOrderDelivered($event),
            OrderCancelled::class => $this->handleOrderCancelled($event),
            OrderRefunded::class => $this->handleOrderRefunded($event),
            default => null,
        };
    }

    private function handleOrderCreated(OrderCreated $event): void
    {
        OrderProjection::create([
            'order_id' => $event->orderId,
            'customer_id' => $event->customerId,
            'items' => $event->items,
            'total_amount' => $event->totalAmount,
            'shipping_address' => $event->shippingAddress,
            'status' => 'created',
            'version' => 1,
        ]);
    }

    private function handleOrderPaid(OrderPaid $event): void
    {
        OrderProjection::where('order_id', $event->orderId)
            ->update([
                'status' => 'paid',
                'payment_method' => $event->paymentMethod,
                'transaction_id' => $event->transactionId,
                'version' => DB::raw('version + 1'),
            ]);
    }

    private function handleOrderShipped(OrderShipped $event): void
    {
        OrderProjection::where('order_id', $event->orderId)
            ->update([
                'status' => 'shipped',
                'tracking_number' => $event->trackingNumber,
                'carrier' => $event->carrier,
                'version' => DB::raw('version + 1'),
            ]);
    }

    private function handleOrderDelivered(OrderDelivered $event): void
    {
        OrderProjection::where('order_id', $event->orderId)
            ->update([
                'status' => 'delivered',
                'delivery_confirmation' => $event->deliveryConfirmation,
                'version' => DB::raw('version + 1'),
            ]);
    }

    private function handleOrderCancelled(OrderCancelled $event): void
    {
        OrderProjection::where('order_id', $event->orderId)
            ->update([
                'status' => 'cancelled',
                'cancellation_reason' => $event->reason,
                'version' => DB::raw('version + 1'),
            ]);
    }

    private function handleOrderRefunded(OrderRefunded $event): void
    {
        OrderProjection::where('order_id', $event->orderId)
            ->update([
                'status' => 'refunded',
                'refund_amount' => $event->refundAmount,
                'refund_reason' => $event->refundReason,
                'version' => DB::raw('version + 1'),
            ]);
    }
}
