<?php

namespace App\Handlers;

use App\Commands\CreateProductCommand;
use App\Events\ProductCreated;
use App\Models\Product;
use App\Services\EventBus;

class CreateProductHandler
{
    public function __construct(
        private EventBus $eventBus
    ) {}

    public function handle(CreateProductCommand $command): Product
    {
        // Validazione business logic
        if ($command->price <= 0) {
            throw new \InvalidArgumentException('Il prezzo deve essere maggiore di zero');
        }

        if ($command->stock < 0) {
            throw new \InvalidArgumentException('Lo stock non può essere negativo');
        }

        // Creazione del prodotto nel write database
        $product = Product::create([
            'name' => $command->name,
            'description' => $command->description,
            'price' => $command->price,
            'stock' => $command->stock,
            'category' => $command->category,
            'attributes' => json_encode($command->attributes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pubblicazione evento per sincronizzazione
        $this->eventBus->publish(new ProductCreated(
            $product->id,
            $product->name,
            $product->description,
            $product->price,
            $product->stock,
            $product->category,
            $command->attributes
        ));

        return $product;
    }
}
