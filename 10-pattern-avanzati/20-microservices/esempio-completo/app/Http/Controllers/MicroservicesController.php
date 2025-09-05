<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ApiGatewayService;
use App\Services\ServiceDiscoveryService;
use Illuminate\Support\Facades\Log;

class MicroservicesController extends Controller
{
    protected $userService;
    protected $productService;
    protected $orderService;
    protected $paymentService;
    protected $apiGateway;
    protected $serviceDiscovery;

    public function __construct(
        UserService $userService,
        ProductService $productService,
        OrderService $orderService,
        PaymentService $paymentService,
        ApiGatewayService $apiGateway,
        ServiceDiscoveryService $serviceDiscovery
    ) {
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->apiGateway = $apiGateway;
        $this->serviceDiscovery = $serviceDiscovery;
    }

    /**
     * Mostra l'interfaccia per testare il pattern
     */
    public function index()
    {
        return view('microservices.example');
    }

    /**
     * Test del pattern Microservices
     */
    public function test()
    {
        try {
            // Test di tutti i servizi
            $results = $this->testAllServices();
            
            return response()->json([
                'success' => true,
                'message' => 'Test Microservices completato',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test: ' . $e->getMessage()
            ], 500);
        }
    }

    // User Service Endpoints
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500'
        ]);

        $result = $this->userService->createUser($request->all());
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function getUser($id)
    {
        $result = $this->userService->getUser($id);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    // Product Service Endpoints
    public function createProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'sometimes|string|max:100',
            'category' => 'sometimes|string|max:100',
            'stock_quantity' => 'sometimes|integer|min:0'
        ]);

        $result = $this->productService->createProduct($request->all());
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function getProduct($id)
    {
        $result = $this->productService->getProduct($id);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    public function listProducts(Request $request)
    {
        $result = $this->productService->listProducts(
            $request->get('limit', 100),
            $request->get('offset', 0),
            $request->get('filters', [])
        );
        return response()->json($result);
    }

    // Order Service Endpoints
    public function createOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'sometimes|string|max:500',
            'notes' => 'sometimes|string|max:1000'
        ]);

        $result = $this->orderService->createOrder($request->all());
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function getOrder($id)
    {
        $result = $this->orderService->getOrder($id);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    public function listOrders(Request $request)
    {
        $result = $this->orderService->listOrders(
            $request->get('limit', 100),
            $request->get('offset', 0),
            $request->get('filters', [])
        );
        return response()->json($result);
    }

    // Payment Service Endpoints
    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'sometimes|string|in:EUR,USD,GBP',
            'payment_method' => 'sometimes|string|in:card,paypal,bank_transfer'
        ]);

        $result = $this->paymentService->processPayment($request->all());
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function getPayment($id)
    {
        $result = $this->paymentService->getPayment($id);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    // Service Discovery Endpoints
    public function listServices()
    {
        $result = $this->serviceDiscovery->listServices();
        return response()->json($result);
    }

    public function healthCheck()
    {
        $result = $this->serviceDiscovery->healthCheckAllServices();
        return response()->json($result);
    }

    /**
     * Test di tutti i servizi
     */
    private function testAllServices()
    {
        $results = [];
        
        try {
            // Test User Service
            $userResult = $this->testUserService();
            $results['user_service'] = $userResult;
            
            // Test Product Service
            $productResult = $this->testProductService();
            $results['product_service'] = $productResult;
            
            // Test Order Service
            $orderResult = $this->testOrderService();
            $results['order_service'] = $orderResult;
            
            // Test Payment Service
            $paymentResult = $this->testPaymentService();
            $results['payment_service'] = $paymentResult;
            
            // Test API Gateway
            $gatewayResult = $this->testApiGateway();
            $results['api_gateway'] = $gatewayResult;
            
            // Test Service Discovery
            $discoveryResult = $this->testServiceDiscovery();
            $results['service_discovery'] = $discoveryResult;
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test User Service
     */
    private function testUserService()
    {
        $results = [];
        
        try {
            // Test creazione utente
            $userData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123'
            ];
            
            $createResult = $this->userService->createUser($userData);
            $results['create_user'] = $createResult['success'] ? 'success' : 'failed';
            
            if ($createResult['success']) {
                $userId = $createResult['data']['id'];
                
                // Test recupero utente
                $getResult = $this->userService->getUser($userId);
                $results['get_user'] = $getResult['success'] ? 'success' : 'failed';
                
                // Test lista utenti
                $listResult = $this->userService->listUsers();
                $results['list_users'] = $listResult['success'] ? 'success' : 'failed';
                
                // Test statistiche
                $statsResult = $this->userService->getUserStats();
                $results['get_stats'] = $statsResult['success'] ? 'success' : 'failed';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Product Service
     */
    private function testProductService()
    {
        $results = [];
        
        try {
            // Test creazione prodotto
            $productData = [
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 99.99,
                'stock_quantity' => 10
            ];
            
            $createResult = $this->productService->createProduct($productData);
            $results['create_product'] = $createResult['success'] ? 'success' : 'failed';
            
            if ($createResult['success']) {
                $productId = $createResult['data']['id'];
                
                // Test recupero prodotto
                $getResult = $this->productService->getProduct($productId);
                $results['get_product'] = $getResult['success'] ? 'success' : 'failed';
                
                // Test lista prodotti
                $listResult = $this->productService->listProducts();
                $results['list_products'] = $listResult['success'] ? 'success' : 'failed';
                
                // Test verifica disponibilità
                $availabilityResult = $this->productService->checkAvailability($productId, 5);
                $results['check_availability'] = $availabilityResult['success'] ? 'success' : 'failed';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Order Service
     */
    private function testOrderService()
    {
        $results = [];
        
        try {
            // Test creazione ordine
            $orderData = [
                'user_id' => 'test_user_123',
                'items' => [
                    ['product_id' => 'test_product_123', 'quantity' => 2]
                ]
            ];
            
            $createResult = $this->orderService->createOrder($orderData);
            $results['create_order'] = $createResult['success'] ? 'success' : 'failed';
            
            if ($createResult['success']) {
                $orderId = $createResult['data']['id'];
                
                // Test recupero ordine
                $getResult = $this->orderService->getOrder($orderId);
                $results['get_order'] = $getResult['success'] ? 'success' : 'failed';
                
                // Test lista ordini
                $listResult = $this->orderService->listOrders();
                $results['list_orders'] = $listResult['success'] ? 'success' : 'failed';
                
                // Test aggiornamento status
                $updateResult = $this->orderService->updateOrderStatus($orderId, 'paid');
                $results['update_status'] = $updateResult['success'] ? 'success' : 'failed';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Payment Service
     */
    private function testPaymentService()
    {
        $results = [];
        
        try {
            // Test processing pagamento
            $paymentData = [
                'order_id' => 'test_order_123',
                'amount' => 199.99,
                'currency' => 'EUR',
                'payment_method' => 'card'
            ];
            
            $processResult = $this->paymentService->processPayment($paymentData);
            $results['process_payment'] = $processResult['success'] ? 'success' : 'failed';
            
            if ($processResult['success']) {
                $paymentId = $processResult['data']['id'];
                
                // Test recupero pagamento
                $getResult = $this->paymentService->getPayment($paymentId);
                $results['get_payment'] = $getResult['success'] ? 'success' : 'failed';
                
                // Test lista pagamenti
                $listResult = $this->paymentService->listPayments();
                $results['list_payments'] = $listResult['success'] ? 'success' : 'failed';
                
                // Test verifica status
                $statusResult = $this->paymentService->checkPaymentStatus($paymentId);
                $results['check_status'] = $statusResult['success'] ? 'success' : 'failed';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test API Gateway
     */
    private function testApiGateway()
    {
        $results = [];
        
        try {
            // Test routing
            $routeResult = $this->apiGateway->routeRequest('/users', 'GET');
            $results['route_request'] = $routeResult['success'] ? 'success' : 'failed';
            
            // Test status servizi
            $statusResult = $this->apiGateway->getServicesStatus();
            $results['get_services_status'] = $statusResult['success'] ? 'success' : 'failed';
            
            // Test statistiche aggregate
            $statsResult = $this->apiGateway->getAggregatedStats();
            $results['get_aggregated_stats'] = $statsResult['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Service Discovery
     */
    private function testServiceDiscovery()
    {
        $results = [];
        
        try {
            // Test lista servizi
            $listResult = $this->serviceDiscovery->listServices();
            $results['list_services'] = $listResult['success'] ? 'success' : 'failed';
            
            // Test health check
            $healthResult = $this->serviceDiscovery->healthCheckAllServices();
            $results['health_check_all'] = $healthResult['success'] ? 'success' : 'failed';
            
            // Test statistiche
            $statsResult = $this->serviceDiscovery->getDiscoveryStats();
            $results['get_stats'] = $statsResult['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
