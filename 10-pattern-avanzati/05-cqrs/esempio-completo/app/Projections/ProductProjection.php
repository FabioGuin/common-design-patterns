<?php

namespace App\Projections;

use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\QueryModels\ProductView;
use Illuminate\Support\Facades\DB;

class ProductProjection
{
    public function handle(object $event): void
    {
        match (get_class($event)) {
            ProductCreated::class => $this->handleProductCreated($event),
            ProductUpdated::class => $this->handleProductUpdated($event),
            default => null,
        };
    }

    private function handleProductCreated(ProductCreated $event): void
    {
        // Inserimento nel read database
        DB::connection('mysql_read')->table('product_views')->insert([
            'id' => $event->id,
            'name' => $event->name,
            'description' => $event->description,
            'price' => $event->price,
            'stock' => $event->stock,
            'category' => $event->category,
            'attributes' => json_encode($event->attributes),
            'is_available' => $event->stock > 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function handleProductUpdated(ProductUpdated $event): void
    {
        // Aggiornamento nel read database
        DB::connection('mysql_read')->table('product_views')
            ->where('id', $event->id)
            ->update([
                'name' => $event->name,
                'description' => $event->description,
                'price' => $event->price,
                'stock' => $event->stock,
                'category' => $event->category,
                'attributes' => json_encode($event->attributes),
                'is_available' => $event->stock > 0,
                'updated_at' => now(),
            ]);
    }
}
