<?php

namespace App\Handlers;

use App\Commands\CreateOrderCommand;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use App\Services\EventBus;

class CreateOrderHandler
{
    public function __construct(
        private EventBus $eventBus
    ) {}

    public function handle(CreateOrderCommand $command): Order
    {
        // Validazione disponibilità prodotti
        $totalAmount = 0;
        $orderItems = [];

        foreach ($command->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            if ($product->stock < $item['quantity']) {
                throw new \InvalidArgumentException(
                    "Prodotto {$product->name} non disponibile in quantità richiesta"
                );
            }

            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $product->price * $item['quantity'],
            ];

            $totalAmount += $product->price * $item['quantity'];
        }

        // Creazione ordine
        $order = Order::create([
            'user_id' => $command->userId,
            'items' => json_encode($orderItems),
            'total_amount' => $totalAmount,
            'shipping_address' => $command->shippingAddress,
            'billing_address' => $command->billingAddress,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Aggiornamento stock prodotti
        foreach ($command->items as $item) {
            $product = Product::find($item['product_id']);
            $product->decrement('stock', $item['quantity']);
        }

        // Pubblicazione evento per sincronizzazione
        $this->eventBus->publish(new OrderCreated(
            $order->id,
            $command->userId,
            $orderItems,
            $totalAmount,
            $command->shippingAddress,
            $command->billingAddress
        ));

        return $order;
    }
}
