<?php

namespace App\Handlers;

use App\Commands\UpdateProductCommand;
use App\Events\ProductUpdated;
use App\Models\Product;
use App\Services\EventBus;

class UpdateProductHandler
{
    public function __construct(
        private EventBus $eventBus
    ) {}

    public function handle(UpdateProductCommand $command): Product
    {
        $product = Product::findOrFail($command->id);

        // Aggiornamento solo dei campi forniti
        $updateData = [];
        
        if ($command->name !== null) {
            $updateData['name'] = $command->name;
        }
        
        if ($command->description !== null) {
            $updateData['description'] = $command->description;
        }
        
        if ($command->price !== null) {
            if ($command->price <= 0) {
                throw new \InvalidArgumentException('Il prezzo deve essere maggiore di zero');
            }
            $updateData['price'] = $command->price;
        }
        
        if ($command->stock !== null) {
            if ($command->stock < 0) {
                throw new \InvalidArgumentException('Lo stock non può essere negativo');
            }
            $updateData['stock'] = $command->stock;
        }
        
        if ($command->category !== null) {
            $updateData['category'] = $command->category;
        }
        
        if ($command->attributes !== null) {
            $updateData['attributes'] = json_encode($command->attributes);
        }

        $updateData['updated_at'] = now();

        $product->update($updateData);

        // Pubblicazione evento per sincronizzazione
        $this->eventBus->publish(new ProductUpdated(
            $product->id,
            $product->name,
            $product->description,
            $product->price,
            $product->stock,
            $product->category,
            json_decode($product->attributes, true) ?? []
        ));

        return $product;
    }
}
