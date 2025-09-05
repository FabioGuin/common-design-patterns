<?php

namespace App\Services;

use App\Models\Order;
use App\Services\EventBusService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private EventBusService $eventBus;
    private string $connection = 'order_service';

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
        // Gestisce eventi di pagamento processato
        $this->eventBus->subscribe('PaymentProcessed', function ($event) {
            $this->handlePaymentProcessed($event);
        });

        // Gestisce eventi di aggiornamento inventario
        $this->eventBus->subscribe('InventoryUpdated', function ($event) {
            $this->handleInventoryUpdated($event);
        });
    }

    /**
     * Crea un nuovo ordine
     */
    public function createOrder(array $orderData): array
    {
        return DB::connection($this->connection)->transaction(function () use ($orderData) {
            $order = new Order();
            $order->user_id = $orderData['user_id'];
            $order->items = $orderData['items'];
            $order->total = $orderData['total'];
            $order->status = 'pending';
            $order->created_at = now();
            $order->save();

            // Pubblica evento
            $this->eventBus->publish('OrderCreated', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'items' => $order->items,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at
            ]);

            Log::info("Order created", [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total' => $order->total,
                'items_count' => count($order->items)
            ]);

            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'items' => $order->items,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Ottiene un ordine per ID
     */
    public function getOrder(int $orderId): ?array
    {
        $order = Order::on($this->connection)->find($orderId);
        
        if (!$order) {
            return null;
        }

        return [
            'id' => $order->id,
            'user_id' => $order->user_id,
            'items' => $order->items,
            'total' => $order->total,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'database' => $this->connection
        ];
    }

    /**
     * Ottiene tutti gli ordini
     */
    public function getAllOrders(): array
    {
        $orders = Order::on($this->connection)->all();
        
        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'items' => $order->items,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'database' => $this->connection
            ];
        })->toArray();
    }

    /**
     * Aggiorna lo stato di un ordine
     */
    public function updateOrderStatus(int $orderId, string $status): ?array
    {
        return DB::connection($this->connection)->transaction(function () use ($orderId, $status) {
            $order = Order::on($this->connection)->find($orderId);
            
            if (!$order) {
                return null;
            }

            $oldStatus = $order->status;
            $order->status = $status;
            $order->updated_at = now();
            $order->save();

            // Pubblica evento
            $this->eventBus->publish('OrderStatusUpdated', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'updated_at' => $order->updated_at
            ]);

            Log::info("Order status updated", [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'items' => $order->items,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Elimina un ordine
     */
    public function deleteOrder(int $orderId): bool
    {
        return DB::connection($this->connection)->transaction(function () use ($orderId) {
            $order = Order::on($this->connection)->find($orderId);
            
            if (!$order) {
                return false;
            }

            $order->delete();

            // Pubblica evento
            $this->eventBus->publish('OrderDeleted', [
                'order_id' => $orderId,
                'deleted_at' => now()
            ]);

            Log::info("Order deleted", [
                'order_id' => $orderId
            ]);

            return true;
        });
    }

    /**
     * Gestisce l'evento di pagamento processato
     */
    private function handlePaymentProcessed(array $event): void
    {
        $orderId = $event['data']['order_id'];
        $paymentId = $event['data']['payment_id'];

        // Aggiorna lo stato dell'ordine
        $this->updateOrderStatus($orderId, 'paid');

        Log::info("Payment processed event received", [
            'order_id' => $orderId,
            'payment_id' => $paymentId
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

        // In un'implementazione reale, potresti aggiornare lo stato dell'ordine
        // se l'inventario non è sufficiente
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        $totalOrders = Order::on($this->connection)->count();
        $totalValue = Order::on($this->connection)->sum('total');
        $statusCounts = Order::on($this->connection)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'service' => 'OrderService',
            'database' => $this->connection,
            'total_orders' => $totalOrders,
            'total_value' => $totalValue,
            'status_counts' => $statusCounts,
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
        return 'order-service-pattern-' . uniqid();
    }
}
