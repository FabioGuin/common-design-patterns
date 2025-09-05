<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servizio per la gestione degli ordini
 * 
 * Questo servizio dimostra i problemi del Shared Database Anti-pattern
 * dove il servizio è fortemente accoppiato al database condiviso.
 */
class OrderService
{
    private string $id;
    private SharedDatabaseService $sharedDb;
    private array $operationHistory;
    private int $totalOperations;
    private int $failedOperations;

    public function __construct(SharedDatabaseService $sharedDb)
    {
        $this->id = 'order-service-' . uniqid();
        $this->sharedDb = $sharedDb;
        $this->operationHistory = [];
        $this->totalOperations = 0;
        $this->failedOperations = 0;
        
        Log::info('OrderService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Crea un nuovo ordine
     * 
     * Problema: Utilizza il database condiviso, causando accoppiamento forte
     */
    public function createOrder(array $data): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock su multiple tabelle
            $tables = ['orders', 'order_items', 'users', 'products'];
            foreach ($tables as $table) {
                if (!$this->sharedDb->acquireLock($table, 'write')) {
                    throw new Exception("Failed to acquire lock on $table table");
                }
            }
            
            // Simula la creazione dell'ordine
            $order = new Order([
                'user_id' => $data['user_id'],
                'total' => $data['total'],
                'status' => 'pending'
            ]);
            $order->save();
            
            // Simula la creazione degli item dell'ordine
            $orderItems = [];
            foreach ($data['items'] as $item) {
                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
                $orderItem->save();
                $orderItems[] = $orderItem;
            }
            
            // Rilascia tutti i lock
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'total' => $order->total,
                'status' => $order->status,
                'items' => $orderItems,
                'database' => 'shared_database',
                'table' => 'orders',
                'created_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'create_order',
                'order_id' => $order->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Order created successfully', [
                'service' => $this->id,
                'order_id' => $order->id,
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
                'operation' => 'create_order',
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to create order', [
                'service' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Aggiorna lo stato di un ordine
     * 
     * Problema: Modifiche al schema orders impattano altri servizi
     */
    public function updateOrderStatus(int $orderId, string $status): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock su multiple tabelle
            $tables = ['orders', 'payments', 'users'];
            foreach ($tables as $table) {
                if (!$this->sharedDb->acquireLock($table, 'write')) {
                    throw new Exception("Failed to acquire lock on $table table");
                }
            }
            
            // Simula l'aggiornamento dell'ordine
            $order = Order::find($orderId);
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            $order->status = $status;
            $order->save();
            
            // Rilascia tutti i lock
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'total' => $order->total,
                'status' => $order->status,
                'database' => 'shared_database',
                'table' => 'orders',
                'updated_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'update_order_status',
                'order_id' => $order->id,
                'status' => $status,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Order status updated successfully', [
                'service' => $this->id,
                'order_id' => $order->id,
                'status' => $status,
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
                'operation' => 'update_order_status',
                'order_id' => $orderId,
                'status' => $status,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to update order status', [
                'service' => $this->id,
                'order_id' => $orderId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene un ordine per ID
     * 
     * Problema: Query su database condiviso con possibili conflitti
     */
    public function getOrder(int $orderId): ?array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock di lettura
            if (!$this->sharedDb->acquireLock('orders', 'read')) {
                throw new Exception('Failed to acquire read lock on orders table');
            }
            
            // Simula la query
            $order = Order::find($orderId);
            
            $this->sharedDb->releaseLock('orders', 'read');
            
            $duration = microtime(true) - $startTime;
            
            if (!$order) {
                return null;
            }
            
            $result = [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'total' => $order->total,
                'status' => $order->status,
                'database' => 'shared_database',
                'table' => 'orders',
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'get_order',
                'order_id' => $order->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('orders', 'read');
            
            $this->operationHistory[] = [
                'operation' => 'get_order',
                'order_id' => $orderId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to get order', [
                'service' => $this->id,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene tutti gli ordini
     * 
     * Problema: Query su database condiviso con possibili conflitti
     */
    public function getAllOrders(): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock di lettura
            if (!$this->sharedDb->acquireLock('orders', 'read')) {
                throw new Exception('Failed to acquire read lock on orders table');
            }
            
            // Simula la query
            $orders = Order::all();
            
            $this->sharedDb->releaseLock('orders', 'read');
            
            $duration = microtime(true) - $startTime;
            
            $result = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'total' => $order->total,
                    'status' => $order->status,
                    'database' => 'shared_database',
                    'table' => 'orders'
                ];
            })->toArray();
            
            $this->operationHistory[] = [
                'operation' => 'get_all_orders',
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true,
                'count' => count($result)
            ];
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('orders', 'read');
            
            $this->operationHistory[] = [
                'operation' => 'get_all_orders',
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to get all orders', [
                'service' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Simula una transazione distribuita complessa
     * 
     * Problema: Transazioni che coinvolgono multiple tabelle condivise
     */
    public function processOrderWithInventory(int $orderId): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock su tutte le tabelle coinvolte
            $tables = ['orders', 'order_items', 'products', 'users', 'payments'];
            foreach ($tables as $table) {
                if (!$this->sharedDb->acquireLock($table, 'write')) {
                    throw new Exception("Failed to acquire lock on $table table");
                }
            }
            
            // Simula la verifica dell'ordine
            $order = Order::find($orderId);
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            // Simula la verifica dell'inventario
            $hasInsufficientInventory = rand(1, 100) <= 20; // 20% di probabilità
            if ($hasInsufficientInventory) {
                throw new Exception('Insufficient inventory for order items');
            }
            
            // Simula l'aggiornamento dell'ordine
            $order->status = 'processing';
            $order->save();
            
            // Rilascia tutti i lock
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $order->id,
                'status' => $order->status,
                'database' => 'shared_database',
                'table' => 'orders',
                'processed_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'process_order_with_inventory',
                'order_id' => $order->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Order processed with inventory successfully', [
                'service' => $this->id,
                'order_id' => $order->id,
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
                'operation' => 'process_order_with_inventory',
                'order_id' => $orderId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to process order with inventory', [
                'service' => $this->id,
                'order_id' => $orderId,
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
            'service' => 'OrderService',
            'database' => 'shared_database',
            'table' => 'orders',
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
                'complex_transactions' => true,
                'cross_service_dependencies' => true
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
