<?php

namespace App\Services;

use App\Models\Product;
use App\Services\EventBusService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private EventBusService $eventBus;
    private string $connection = 'product_service';

    public function __construct(EventBusService $eventBus)
    {
        $this->eventBus = $eventBus;
        $this->initializeEventHandlers();
    }

    /**
     * Inizializza i gestori di eventi
     */
    private function initializeEventHandlers(): void
    {
        // Gestisce eventi di creazione ordine
        $this->eventBus->subscribe('OrderCreated', function ($event) {
            $this->handleOrderCreated($event);
        });

        // Gestisce eventi di aggiornamento inventario
        $this->eventBus->subscribe('InventoryUpdated', function ($event) {
            $this->handleInventoryUpdated($event);
        });
    }

    /**
     * Crea un nuovo prodotto
     */
    public function createProduct(array $productData): array
    {
        return DB::connection($this->connection)->transaction(function () use ($productData) {
            $product = new Product();
            $product->name = $productData['name'];
            $product->description = $productData['description'];
            $product->price = $productData['price'];
            $product->category = $productData['category'];
            $product->inventory = $productData['inventory'] ?? 0;
            $product->created_at = now();
            $product->save();

            // Pubblica evento
            $this->eventBus->publish('ProductCreated', [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'inventory' => $product->inventory,
                'created_at' => $product->created_at
            ]);

            Log::info("Product created", [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category
            ]);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'inventory' => $product->inventory,
                'created_at' => $product->created_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Ottiene un prodotto per ID
     */
    public function getProduct(int $productId): ?array
    {
        $product = Product::on($this->connection)->find($productId);
        
        if (!$product) {
            return null;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category' => $product->category,
            'inventory' => $product->inventory,
            'created_at' => $product->created_at,
            'database' => $this->connection
        ];
    }

    /**
     * Ottiene tutti i prodotti
     */
    public function getAllProducts(): array
    {
        $products = Product::on($this->connection)->all();
        
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'inventory' => $product->inventory,
                'created_at' => $product->created_at,
                'database' => $this->connection
            ];
        })->toArray();
    }

    /**
     * Aggiorna un prodotto
     */
    public function updateProduct(int $productId, array $productData): ?array
    {
        return DB::connection($this->connection)->transaction(function () use ($productId, $productData) {
            $product = Product::on($this->connection)->find($productId);
            
            if (!$product) {
                return null;
            }

            $product->name = $productData['name'] ?? $product->name;
            $product->description = $productData['description'] ?? $product->description;
            $product->price = $productData['price'] ?? $product->price;
            $product->category = $productData['category'] ?? $product->category;
            $product->inventory = $productData['inventory'] ?? $product->inventory;
            $product->updated_at = now();
            $product->save();

            // Pubblica evento
            $this->eventBus->publish('ProductUpdated', [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'inventory' => $product->inventory,
                'updated_at' => $product->updated_at
            ]);

            Log::info("Product updated", [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category
            ]);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'inventory' => $product->inventory,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Aggiorna l'inventario di un prodotto
     */
    public function updateInventory(int $productId, int $quantity): ?array
    {
        return DB::connection($this->connection)->transaction(function () use ($productId, $quantity) {
            $product = Product::on($this->connection)->find($productId);
            
            if (!$product) {
                return null;
            }

            $oldInventory = $product->inventory;
            $product->inventory = max(0, $product->inventory + $quantity);
            $product->updated_at = now();
            $product->save();

            // Pubblica evento
            $this->eventBus->publish('InventoryUpdated', [
                'product_id' => $product->id,
                'old_inventory' => $oldInventory,
                'new_inventory' => $product->inventory,
                'quantity_change' => $quantity,
                'updated_at' => $product->updated_at
            ]);

            Log::info("Inventory updated", [
                'product_id' => $product->id,
                'old_inventory' => $oldInventory,
                'new_inventory' => $product->inventory,
                'quantity_change' => $quantity
            ]);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'inventory' => $product->inventory,
                'quantity_change' => $quantity,
                'updated_at' => $product->updated_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Elimina un prodotto
     */
    public function deleteProduct(int $productId): bool
    {
        return DB::connection($this->connection)->transaction(function () use ($productId) {
            $product = Product::on($this->connection)->find($productId);
            
            if (!$product) {
                return false;
            }

            $product->delete();

            // Pubblica evento
            $this->eventBus->publish('ProductDeleted', [
                'product_id' => $productId,
                'deleted_at' => now()
            ]);

            Log::info("Product deleted", [
                'product_id' => $productId
            ]);

            return true;
        });
    }

    /**
     * Gestisce l'evento di creazione ordine
     */
    private function handleOrderCreated(array $event): void
    {
        $orderItems = $event['data']['items'] ?? [];
        
        foreach ($orderItems as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Riduci l'inventario
            $this->updateInventory($productId, -$quantity);
        }

        Log::info("Order created event processed", [
            'order_id' => $event['data']['order_id'],
            'items_processed' => count($orderItems)
        ]);
    }

    /**
     * Gestisce l'evento di aggiornamento inventario
     */
    private function handleInventoryUpdated(array $event): void
    {
        Log::info("Inventory updated event received", [
            'product_id' => $event['data']['product_id'],
            'old_inventory' => $event['data']['old_inventory'],
            'new_inventory' => $event['data']['new_inventory']
        ]);

        // In un'implementazione reale, potresti aggiornare cache o notificare altri servizi
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        $totalProducts = Product::on($this->connection)->count();
        $totalInventory = Product::on($this->connection)->sum('inventory');
        $categories = Product::on($this->connection)
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        return [
            'service' => 'ProductService',
            'database' => $this->connection,
            'total_products' => $totalProducts,
            'total_inventory' => $totalInventory,
            'categories' => $categories,
            'connection_status' => $this->testConnection()
        ];
    }

    /**
     * Testa la connessione al database
     */
    private function testConnection(): bool
    {
        try {
            DB::connection($this->connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Ottiene l'ID del pattern per identificazione
     */
    public function getId(): string
    {
        return 'product-service-pattern-' . uniqid();
    }
}
