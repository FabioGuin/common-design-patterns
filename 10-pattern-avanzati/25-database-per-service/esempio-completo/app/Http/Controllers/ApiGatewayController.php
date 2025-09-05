<?php

namespace App\Http\Controllers;

use App\Services\EventBusService;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApiGatewayController extends Controller
{
    private EventBusService $eventBus;
    private UserService $userService;
    private ProductService $productService;
    private OrderService $orderService;
    private PaymentService $paymentService;

    public function __construct(
        EventBusService $eventBus,
        UserService $userService,
        ProductService $productService,
        OrderService $orderService,
        PaymentService $paymentService
    ) {
        $this->eventBus = $eventBus;
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Mostra la dashboard del Database Per Service
     */
    public function index(): View
    {
        $services = $this->getServicesInfo();
        $eventStats = $this->eventBus->getEventStats();

        return view('database-per-service.example', compact(
            'services', 
            'eventStats'
        ));
    }

    /**
     * Testa il sistema completo
     */
    public function test(Request $request): JsonResponse
    {
        $testData = $this->runCompleteTest();

        return response()->json([
            'success' => true,
            'data' => $testData,
            'pattern_id' => 'database-per-service-pattern-' . uniqid(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Ottiene le informazioni sui servizi
     */
    public function services(): JsonResponse
    {
        $services = $this->getServicesInfo();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Ottiene le statistiche complete
     */
    public function stats(): JsonResponse
    {
        $services = $this->getServicesInfo();
        $eventStats = $this->eventBus->getEventStats();

        return response()->json([
            'success' => true,
            'data' => [
                'services' => $services,
                'events' => $eventStats,
                'pattern_id' => 'database-per-service-pattern-' . uniqid()
            ]
        ]);
    }

    /**
     * Esegue un test completo del sistema
     */
    private function runCompleteTest(): array
    {
        $results = [];
        $startTime = microtime(true);

        // Test 1: Creazione utente
        $user = $this->userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        $results['user_created'] = $user;

        // Test 2: Creazione prodotto
        $product = $this->productService->createProduct([
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 29.99,
            'category' => 'Electronics',
            'inventory' => 10
        ]);
        $results['product_created'] = $product;

        // Test 3: Creazione ordine
        $order = $this->orderService->createOrder([
            'user_id' => $user['id'],
            'items' => [
                [
                    'product_id' => $product['id'],
                    'quantity' => 2,
                    'price' => $product['price']
                ]
            ],
            'total' => $product['price'] * 2
        ]);
        $results['order_created'] = $order;

        // Test 4: Processamento pagamento
        $payments = $this->paymentService->getAllPayments();
        $payment = $this->paymentService->processPayment($payments[0]['id']);
        $results['payment_processed'] = $payment;

        // Test 5: Verifica sincronizzazione
        $updatedOrder = $this->orderService->getOrder($order['id']);
        $results['order_synchronized'] = $updatedOrder;

        $endTime = microtime(true);
        $results['execution_time'] = ($endTime - $startTime) * 1000; // in millisecondi

        return $results;
    }

    /**
     * Ottiene le informazioni sui servizi
     */
    private function getServicesInfo(): array
    {
        return [
            'user_service' => $this->userService->getStats(),
            'product_service' => $this->productService->getStats(),
            'order_service' => $this->orderService->getStats(),
            'payment_service' => $this->paymentService->getStats()
        ];
    }
}
