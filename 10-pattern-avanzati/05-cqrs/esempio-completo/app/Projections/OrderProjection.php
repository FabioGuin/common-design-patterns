<?php

namespace App\Projections;

use App\Events\OrderCreated;
use App\QueryModels\OrderView;
use Illuminate\Support\Facades\DB;

class OrderProjection
{
    public function handle(object $event): void
    {
        if ($event instanceof OrderCreated) {
            $this->handleOrderCreated($event);
        }
    }

    private function handleOrderCreated(OrderCreated $event): void
    {
        // Inserimento nel read database
        DB::connection('mysql_read')->table('order_views')->insert([
            'id' => $event->id,
            'user_id' => $event->userId,
            'items' => json_encode($event->items),
            'total_amount' => $event->totalAmount,
            'shipping_address' => $event->shippingAddress,
            'billing_address' => $event->billingAddress,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
