<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servizio per la gestione dei prodotti
 * 
 * Questo servizio dimostra i problemi del Shared Database Anti-pattern
 * dove il servizio è fortemente accoppiato al database condiviso.
 */
class ProductService
{
    private string $id;
    private SharedDatabaseService $sharedDb;
    private array $operationHistory;
    private int $totalOperations;
    private int $failedOperations;

    public function __construct(SharedDatabaseService $sharedDb)
    {
        $this->id = 'product-service-' . uniqid();
        $this->sharedDb = $sharedDb;
        $this->operationHistory = [];
        $this->totalOperations = 0;
        $this->failedOperations = 0;
        
        Log::info('ProductService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Crea un nuovo prodotto
     * 
     * Problema: Utilizza il database condiviso, causando accoppiamento forte
     */
    public function createProduct(array $data): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock sulla tabella products
            if (!$this->sharedDb->acquireLock('products', 'write')) {
                throw new Exception('Failed to acquire lock on products table');
            }
            
            // Simula la creazione del prodotto
            $product = new Product($data);
            $product->save();
            
            $this->sharedDb->releaseLock('products', 'write');
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'inventory' => $product->inventory,
                'database' => 'shared_database',
                'table' => 'products',
                'created_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'create_product',
                'product_id' => $product->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Product created successfully', [
                'service' => $this->id,
                'product_id' => $product->id,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('products', 'write');
            
            $this->operationHistory[] = [
                'operation' => 'create_product',
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to create product', [
                'service' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Aggiorna un prodotto esistente
     * 
     * Problema: Modifiche al schema products impattano altri servizi
     */
    public function updateProduct(int $productId, array $data): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock sulla tabella products
            if (!$this->sharedDb->acquireLock('products', 'write')) {
                throw new Exception('Failed to acquire lock on products table');
            }
            
            // Simula l'aggiornamento del prodotto
            $product = Product::find($productId);
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            foreach ($data as $key => $value) {
                $product->$key = $value;
            }
            $product->save();
            
            $this->sharedDb->releaseLock('products', 'write');
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'inventory' => $product->inventory,
                'database' => 'shared_database',
                'table' => 'products',
                'updated_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'update_product',
                'product_id' => $product->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Product updated successfully', [
                'service' => $this->id,
                'product_id' => $product->id,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('products', 'write');
            
            $this->operationHistory[] = [
                'operation' => 'update_product',
                'product_id' => $productId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to update product', [
                'service' => $this->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Aggiorna l'inventario di un prodotto
     * 
     * Problema: Modifiche all'inventario impattano ordini e pagamenti
     */
    public function updateInventory(int $productId, int $quantityChange): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock su multiple tabelle
            $tables = ['products', 'orders', 'order_items'];
            foreach ($tables as $table) {
                if (!$this->sharedDb->acquireLock($table, 'write')) {
                    throw new Exception("Failed to acquire lock on $table table");
                }
            }
            
            // Simula l'aggiornamento dell'inventario
            $product = Product::find($productId);
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            $newInventory = $product->inventory + $quantityChange;
            if ($newInventory < 0) {
                throw new Exception('Insufficient inventory');
            }
            
            $product->inventory = $newInventory;
            $product->save();
            
            // Rilascia tutti i lock
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $product->id,
                'name' => $product->name,
                'inventory' => $product->inventory,
                'quantity_change' => $quantityChange,
                'database' => 'shared_database',
                'table' => 'products',
                'updated_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'update_inventory',
                'product_id' => $product->id,
                'quantity_change' => $quantityChange,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Inventory updated successfully', [
                'service' => $this->id,
                'product_id' => $product->id,
                'quantity_change' => $quantityChange,
                'new_inventory' => $product->inventory,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            // Rilascia tutti i lock in caso di errore
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $this->operationHistory[] = [
                'operation' => 'update_inventory',
                'product_id' => $productId,
                'quantity_change' => $quantityChange,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to update inventory', [
                'service' => $this->id,
                'product_id' => $productId,
                'quantity_change' => $quantityChange,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene un prodotto per ID
     * 
     * Problema: Query su database condiviso con possibili conflitti
     */
    public function getProduct(int $productId): ?array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock di lettura
            if (!$this->sharedDb->acquireLock('products', 'read')) {
                throw new Exception('Failed to acquire read lock on products table');
            }
            
            // Simula la query
            $product = Product::find($productId);
            
            $this->sharedDb->releaseLock('products', 'read');
            
            $duration = microtime(true) - $startTime;
            
            if (!$product) {
                return null;
            }
            
            $result = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
                'inventory' => $product->inventory,
                'database' => 'shared_database',
                'table' => 'products',
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'get_product',
                'product_id' => $product->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('products', 'read');
            
            $this->operationHistory[] = [
                'operation' => 'get_product',
                'product_id' => $productId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to get product', [
                'service' => $this->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene tutti i prodotti
     * 
     * Problema: Query su database condiviso con possibili conflitti
     */
    public function getAllProducts(): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock di lettura
            if (!$this->sharedDb->acquireLock('products', 'read')) {
                throw new Exception('Failed to acquire read lock on products table');
            }
            
            // Simula la query
            $products = Product::all();
            
            $this->sharedDb->releaseLock('products', 'read');
            
            $duration = microtime(true) - $startTime;
            
            $result = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'category' => $product->category,
                    'inventory' => $product->inventory,
                    'database' => 'shared_database',
                    'table' => 'products'
                ];
            })->toArray();
            
            $this->operationHistory[] = [
                'operation' => 'get_all_products',
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true,
                'count' => count($result)
            ];
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('products', 'read');
            
            $this->operationHistory[] = [
                'operation' => 'get_all_products',
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to get all products', [
                'service' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        return [
            'id' => $this->id,
            'service' => 'ProductService',
            'database' => 'shared_database',
            'table' => 'products',
            'total_operations' => $this->totalOperations,
            'failed_operations' => $this->failedOperations,
            'success_rate' => $this->totalOperations > 0 
                ? round((($this->totalOperations - $this->failedOperations) / $this->totalOperations) * 100, 2)
                : 100,
            'operation_history' => $this->operationHistory,
            'coupling_level' => 'high', // Alto accoppiamento con database condiviso
            'scalability_issues' => [
                'shared_database' => true,
                'table_locks' => true,
                'schema_dependencies' => true,
                'inventory_conflicts' => true
            ]
        ];
    }

    /**
     * Ottiene la cronologia delle operazioni
     */
    public function getOperationHistory(): array
    {
        return $this->operationHistory;
    }
}
