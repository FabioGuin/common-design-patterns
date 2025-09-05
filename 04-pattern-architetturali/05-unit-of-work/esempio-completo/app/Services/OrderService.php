<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Inventory;
use App\UnitOfWork\UnitOfWorkInterface;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private UnitOfWorkInterface $unitOfWork
    ) {}

    /**
     * Crea un nuovo ordine con gestione transazionale
     */
    public function createOrder(array $orderData, array $products): Order
    {
        $this->unitOfWork->begin();
        
        try {
            Log::info('OrderService: Creating order with Unit of Work', [
                'order_data' => $orderData,
                'products_count' => count($products)
            ]);

            // Crea l'ordine
            $order = new Order($orderData);
            $this->unitOfWork->registerNew($order);

            // Processa i prodotti
            $this->processOrderProducts($order, $products);

            // Conferma la transazione
            $this->unitOfWork->commit();

            Log::info('OrderService: Order created successfully', [
                'order_id' => $order->id
            ]);

            return $order;
        } catch (\Exception $e) {
            Log::error('OrderService: Failed to create order', [
                'error' => $e->getMessage(),
                'order_data' => $orderData
            ]);
            
            $this->unitOfWork->rollback();
            throw $e;
        }
    }

    /**
     * Aggiorna un ordine esistente
     */
    public function updateOrder(int $orderId, array $orderData, array $products = []): Order
    {
        $this->unitOfWork->begin();
        
        try {
            $order = Order::findOrFail($orderId);
            
            // Aggiorna i dati dell'ordine
            $order->fill($orderData);
            $this->unitOfWork->registerDirty($order);

            // Se ci sono prodotti, aggiorna anche quelli
            if (!empty($products)) {
                $this->processOrderProducts($order, $products);
            }

            $this->unitOfWork->commit();

            return $order->fresh();
        } catch (\Exception $e) {
            $this->unitOfWork->rollback();
            throw $e;
        }
    }

    /**
     * Cancella un ordine e ripristina l'inventario
     */
    public function cancelOrder(int $orderId): bool
    {
        $this->unitOfWork->begin();
        
        try {
            $order = Order::with('products')->findOrFail($orderId);
            
            // Ripristina l'inventario per ogni prodotto
            foreach ($order->products as $product) {
                $inventory = Inventory::where('product_id', $product->id)->first();
                if ($inventory) {
                    $inventory->increaseQuantity($product->pivot->quantity);
                    $this->unitOfWork->registerDirty($inventory);
                }
            }

            // Cancella l'ordine
            $this->unitOfWork->registerDeleted($order);
            $this->unitOfWork->commit();

            return true;
        } catch (\Exception $e) {
            $this->unitOfWork->rollback();
            throw $e;
        }
    }

    /**
     * Processa i prodotti dell'ordine
     */
    private function processOrderProducts(Order $order, array $products): void
    {
        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['id']);
            $quantity = $productData['quantity'];

            // Verifica disponibilità
            if ($product->stock < $quantity) {
                throw new \Exception("Prodotto {$product->name} non disponibile in quantità sufficiente");
            }

            // Decrementa lo stock
            $product->decreaseStock($quantity);
            $this->unitOfWork->registerDirty($product);

            // Aggiorna l'inventario
            $inventory = Inventory::where('product_id', $product->id)->first();
            if ($inventory) {
                $inventory->decreaseQuantity($quantity);
                $this->unitOfWork->registerDirty($inventory);
            }

            // Aggiungi il prodotto all'ordine
            $order->products()->attach($product->id, [
                'quantity' => $quantity,
                'price' => $product->price
            ]);
        }
    }

    /**
     * Completa un ordine (cambia stato)
     */
    public function completeOrder(int $orderId): Order
    {
        $this->unitOfWork->begin();
        
        try {
            $order = Order::findOrFail($orderId);
            $order->status = 'completed';
            $order->completed_at = now();
            
            $this->unitOfWork->registerDirty($order);
            $this->unitOfWork->commit();

            return $order->fresh();
        } catch (\Exception $e) {
            $this->unitOfWork->rollback();
            throw $e;
        }
    }

    /**
     * Processa un pagamento per un ordine
     */
    public function processPayment(int $orderId, array $paymentData): Order
    {
        $this->unitOfWork->begin();
        
        try {
            $order = Order::findOrFail($orderId);
            
            // Aggiorna l'ordine con i dati del pagamento
            $order->payment_method = $paymentData['method'];
            $order->payment_status = 'paid';
            $order->paid_at = now();
            
            $this->unitOfWork->registerDirty($order);

            // Se il pagamento è andato a buon fine, completa l'ordine
            if ($paymentData['status'] === 'success') {
                $order->status = 'processing';
                $this->unitOfWork->registerDirty($order);
            }

            $this->unitOfWork->commit();

            return $order->fresh();
        } catch (\Exception $e) {
            $this->unitOfWork->rollback();
            throw $e;
        }
    }

    /**
     * Ottiene statistiche degli ordini
     */
    public function getOrderStatistics(): array
    {
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'average_order_value' => Order::where('status', 'completed')->avg('total_amount'),
        ];
    }

    /**
     * Ottiene ordini con filtri
     */
    public function getOrders(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Order::with('products');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Ottiene un ordine per ID
     */
    public function getOrder(int $orderId): Order
    {
        return Order::with('products')->findOrFail($orderId);
    }

    /**
     * Verifica se un ordine può essere cancellato
     */
    public function canCancelOrder(int $orderId): bool
    {
        $order = Order::findOrFail($orderId);
        
        return in_array($order->status, ['pending', 'processing']);
    }

    /**
     * Verifica se un ordine può essere completato
     */
    public function canCompleteOrder(int $orderId): bool
    {
        $order = Order::findOrFail($orderId);
        
        return $order->status === 'processing' && $order->payment_status === 'paid';
    }
}
