<?php

namespace App\Services;

use App\Models\Order;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OrderService
{
    protected $serviceId = 'order-service';
    protected $version = '1.0.0';
    protected $userService;
    protected $productService;
    protected $paymentService;

    public function __construct(
        UserService $userService,
        ProductService $productService,
        PaymentService $paymentService
    ) {
        $this->userService = $userService;
        $this->productService = $productService;
        $this->paymentService = $paymentService;
    }

    /**
     * Crea un nuovo ordine
     */
    public function createOrder(array $orderData): array
    {
        try {
            // Valida i dati dell'ordine
            $this->validateOrderData($orderData);

            // Verifica che l'utente esista
            $userResult = $this->userService->getUser($orderData['user_id']);
            if (!$userResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Utente non trovato: ' . $userResult['error'],
                    'service' => $this->serviceId
                ];
            }

            // Verifica disponibilità prodotti
            $productsResult = $this->validateProducts($orderData['items']);
            if (!$productsResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non disponibile: ' . $productsResult['error'],
                    'service' => $this->serviceId
                ];
            }

            // Calcola il totale
            $total = $this->calculateTotal($orderData['items'], $productsResult['data']);

            // Crea l'ordine
            $order = new Order([
                'user_id' => $orderData['user_id'],
                'items' => $orderData['items'],
                'total_amount' => $total,
                'status' => 'pending',
                'shipping_address' => $orderData['shipping_address'] ?? null,
                'notes' => $orderData['notes'] ?? null
            ]);

            $order->save();

            // Riserva lo stock
            $this->reserveStock($orderData['items']);

            // Cache dell'ordine
            Cache::put("order:{$order->id}", $order, 3600);

            Log::info("Order Service: Ordine creato", [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total_amount' => $order->total_amount,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $order->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Order Service: Errore nella creazione ordine", [
                'error' => $e->getMessage(),
                'order_data' => $orderData,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene un ordine per ID
     */
    public function getOrder(string $orderId): array
    {
        try {
            // Prova prima la cache
            $cachedOrder = Cache::get("order:{$orderId}");
            if ($cachedOrder) {
                return [
                    'success' => true,
                    'data' => $cachedOrder->toArray(),
                    'service' => $this->serviceId,
                    'cached' => true
                ];
            }

            // Recupera dal database
            $order = Order::find($orderId);
            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Ordine non trovato',
                    'service' => $this->serviceId
                ];
            }

            // Cache dell'ordine
            Cache::put("order:{$orderId}", $order, 3600);

            return [
                'success' => true,
                'data' => $order->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Order Service: Errore nel recupero ordine", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Lista tutti gli ordini
     */
    public function listOrders(int $limit = 100, int $offset = 0, array $filters = []): array
    {
        try {
            $query = Order::query();

            // Applica filtri
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }

            $orders = $query->limit($limit)->offset($offset)->get();
            $ordersArray = $orders->map(function($order) {
                return $order->toArray();
            })->toArray();

            return [
                'success' => true,
                'data' => $ordersArray,
                'count' => count($ordersArray),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Order Service: Errore nel recupero lista ordini", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Aggiorna lo status di un ordine
     */
    public function updateOrderStatus(string $orderId, string $status): array
    {
        try {
            $order = Order::find($orderId);
            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Ordine non trovato',
                    'service' => $this->serviceId
                ];
            }

            $order->status = $status;
            $order->save();

            // Aggiorna la cache
            Cache::put("order:{$orderId}", $order, 3600);

            Log::info("Order Service: Status ordine aggiornato", [
                'order_id' => $orderId,
                'new_status' => $status,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $order->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Order Service: Errore nell'aggiornamento status", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Processa il pagamento di un ordine
     */
    public function processPayment(string $orderId, array $paymentData): array
    {
        try {
            $order = Order::find($orderId);
            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Ordine non trovato',
                    'service' => $this->serviceId
                ];
            }

            if ($order->status !== 'pending') {
                return [
                    'success' => false,
                    'error' => 'Ordine non in stato pending',
                    'service' => $this->serviceId
                ];
            }

            // Processa il pagamento
            $paymentResult = $this->paymentService->processPayment([
                'order_id' => $orderId,
                'amount' => $order->total_amount,
                'currency' => 'EUR',
                'payment_method' => $paymentData['payment_method'] ?? 'card'
            ]);

            if ($paymentResult['success']) {
                $order->status = 'paid';
                $order->payment_id = $paymentResult['data']['payment_id'];
                $order->save();

                // Aggiorna la cache
                Cache::put("order:{$orderId}", $order, 3600);

                Log::info("Order Service: Pagamento processato", [
                    'order_id' => $orderId,
                    'payment_id' => $paymentResult['data']['payment_id'],
                    'service' => $this->serviceId
                ]);

                return [
                    'success' => true,
                    'data' => $order->toArray(),
                    'payment' => $paymentResult['data'],
                    'service' => $this->serviceId
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Pagamento fallito: ' . $paymentResult['error'],
                    'service' => $this->serviceId
                ];
            }

        } catch (\Exception $e) {
            Log::error("Order Service: Errore nel processing pagamento", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Cancella un ordine
     */
    public function cancelOrder(string $orderId, string $reason = null): array
    {
        try {
            $order = Order::find($orderId);
            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Ordine non trovato',
                    'service' => $this->serviceId
                ];
            }

            if (!in_array($order->status, ['pending', 'paid'])) {
                return [
                    'success' => false,
                    'error' => 'Impossibile cancellare ordine con status: ' . $order->status,
                    'service' => $this->serviceId
                ];
            }

            // Rilascia lo stock
            $this->releaseStock($order->items);

            // Aggiorna lo status
            $order->status = 'cancelled';
            $order->cancellation_reason = $reason;
            $order->save();

            // Aggiorna la cache
            Cache::put("order:{$orderId}", $order, 3600);

            Log::info("Order Service: Ordine cancellato", [
                'order_id' => $orderId,
                'reason' => $reason,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $order->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Order Service: Errore nella cancellazione ordine", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene statistiche degli ordini
     */
    public function getOrderStats(): array
    {
        try {
            $totalOrders = Order::count();
            $pendingOrders = Order::where('status', 'pending')->count();
            $paidOrders = Order::where('status', 'paid')->count();
            $cancelledOrders = Order::where('status', 'cancelled')->count();
            $totalRevenue = Order::where('status', 'paid')->sum('total_amount');

            return [
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'pending_orders' => $pendingOrders,
                    'paid_orders' => $paidOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'total_revenue' => $totalRevenue
                ],
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Order Service: Errore nel recupero statistiche", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del servizio
     */
    public function healthCheck(): array
    {
        try {
            // Verifica connessione database
            Order::count();

            // Verifica dipendenze
            $userHealth = $this->userService->healthCheck();
            $productHealth = $this->productService->healthCheck();
            $paymentHealth = $this->paymentService->healthCheck();

            $dependenciesHealthy = $userHealth['success'] && $productHealth['success'] && $paymentHealth['success'];

            return [
                'success' => true,
                'status' => $dependenciesHealthy ? 'healthy' : 'degraded',
                'service' => $this->serviceId,
                'version' => $this->version,
                'dependencies' => [
                    'user_service' => $userHealth['status'],
                    'product_service' => $productHealth['status'],
                    'payment_service' => $paymentHealth['status']
                ],
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Valida i dati dell'ordine
     */
    private function validateOrderData(array $orderData): void
    {
        $required = ['user_id', 'items'];
        
        foreach ($required as $field) {
            if (!isset($orderData[$field]) || empty($orderData[$field])) {
                throw new \InvalidArgumentException("Campo obbligatorio mancante: {$field}");
            }
        }

        if (!is_array($orderData['items']) || count($orderData['items']) === 0) {
            throw new \InvalidArgumentException("Ordine deve contenere almeno un item");
        }
    }

    /**
     * Valida i prodotti dell'ordine
     */
    private function validateProducts(array $items): array
    {
        $validatedProducts = [];

        foreach ($items as $item) {
            $productResult = $this->productService->getProduct($item['product_id']);
            if (!$productResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non trovato: ' . $item['product_id']
                ];
            }

            $product = $productResult['data'];
            if ($product['stock_quantity'] < $item['quantity']) {
                return [
                    'success' => false,
                    'error' => 'Stock insufficiente per prodotto: ' . $product['name']
                ];
            }

            $validatedProducts[] = $product;
        }

        return [
            'success' => true,
            'data' => $validatedProducts
        ];
    }

    /**
     * Calcola il totale dell'ordine
     */
    private function calculateTotal(array $items, array $products): float
    {
        $total = 0;

        foreach ($items as $item) {
            $product = collect($products)->firstWhere('id', $item['product_id']);
            if ($product) {
                $total += $product['price'] * $item['quantity'];
            }
        }

        return $total;
    }

    /**
     * Riserva lo stock per gli item dell'ordine
     */
    private function reserveStock(array $items): void
    {
        foreach ($items as $item) {
            $this->productService->reserveStock($item['product_id'], $item['quantity']);
        }
    }

    /**
     * Rilascia lo stock per gli item dell'ordine
     */
    private function releaseStock(array $items): void
    {
        foreach ($items as $item) {
            $this->productService->releaseStock($item['product_id'], $item['quantity']);
        }
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del servizio
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
