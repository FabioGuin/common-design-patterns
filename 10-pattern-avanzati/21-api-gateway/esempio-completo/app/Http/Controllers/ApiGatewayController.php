<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ApiGatewayService;
use App\Services\AuthenticationService;
use App\Services\AuthorizationService;
use App\Services\RateLimitService;
use App\Services\LoggingService;
use App\Services\CachingService;
use App\Services\MonitoringService;
use Illuminate\Support\Facades\Log;

class ApiGatewayController extends Controller
{
    protected $apiGateway;
    protected $authService;
    protected $authorizationService;
    protected $rateLimitService;
    protected $loggingService;
    protected $cachingService;
    protected $monitoringService;

    public function __construct(
        ApiGatewayService $apiGateway,
        AuthenticationService $authService,
        AuthorizationService $authorizationService,
        RateLimitService $rateLimitService,
        LoggingService $loggingService,
        CachingService $cachingService,
        MonitoringService $monitoringService
    ) {
        $this->apiGateway = $apiGateway;
        $this->authService = $authService;
        $this->authorizationService = $authorizationService;
        $this->rateLimitService = $rateLimitService;
        $this->loggingService = $loggingService;
        $this->cachingService = $cachingService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * Mostra l'interfaccia per testare il pattern
     */
    public function index()
    {
        return view('api-gateway.example');
    }

    /**
     * Test del pattern API Gateway
     */
    public function test()
    {
        try {
            // Test di tutti i servizi
            $results = $this->testAllServices();
            
            return response()->json([
                'success' => true,
                'message' => 'Test API Gateway completato',
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
    public function listUsers(Request $request)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function createUser(Request $request)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function getUser(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function updateUser(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function deleteUser(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    // Product Service Endpoints
    public function listProducts(Request $request)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function createProduct(Request $request)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function getProduct(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function updateProduct(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    // Order Service Endpoints
    public function listOrders(Request $request)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function createOrder(Request $request)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function getOrder(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    // Payment Service Endpoints
    public function listPayments(Request $request)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function processPayment(Request $request)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function getPayment(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function refundPayment(Request $request, $id)
    {
        $result = $this->apiGateway->handleRequest($request);
        return response()->json($result, $result['status'] ?? 200);
    }

    // Gateway Management Endpoints
    public function healthCheck()
    {
        $result = $this->apiGateway->healthCheck();
        return response()->json($result);
    }

    public function getStats()
    {
        $result = $this->apiGateway->getStats();
        return response()->json($result);
    }

    public function listServices()
    {
        $result = $this->apiGateway->getServicesStatus();
        return response()->json($result);
    }

    /**
     * Test di tutti i servizi
     */
    private function testAllServices()
    {
        $results = [];
        
        try {
            // Test API Gateway
            $gatewayResult = $this->testApiGateway();
            $results['api_gateway'] = $gatewayResult;
            
            // Test Authentication Service
            $authResult = $this->testAuthenticationService();
            $results['authentication_service'] = $authResult;
            
            // Test Authorization Service
            $authorizationResult = $this->testAuthorizationService();
            $results['authorization_service'] = $authorizationResult;
            
            // Test Rate Limit Service
            $rateLimitResult = $this->testRateLimitService();
            $results['rate_limit_service'] = $rateLimitResult;
            
            // Test Logging Service
            $loggingResult = $this->testLoggingService();
            $results['logging_service'] = $loggingResult;
            
            // Test Caching Service
            $cachingResult = $this->testCachingService();
            $results['caching_service'] = $cachingResult;
            
            // Test Monitoring Service
            $monitoringResult = $this->testMonitoringService();
            $results['monitoring_service'] = $monitoringResult;
            
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
            // Test health check
            $healthResult = $this->apiGateway->healthCheck();
            $results['health_check'] = $healthResult['success'] ? 'success' : 'failed';
            
            // Test services status
            $servicesResult = $this->apiGateway->getServicesStatus();
            $results['services_status'] = $servicesResult['success'] ? 'success' : 'failed';
            
            // Test stats
            $statsResult = $this->apiGateway->getStats();
            $results['get_stats'] = $statsResult['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Authentication Service
     */
    private function testAuthenticationService()
    {
        $results = [];
        
        try {
            // Test health check
            $healthResult = $this->authService->healthCheck();
            $results['health_check'] = $healthResult['success'] ? 'success' : 'failed';
            
            // Test JWT generation
            $user = ['id' => 'test_user', 'email' => 'test@example.com'];
            $jwtResult = $this->authService->generateJwtToken($user);
            $results['jwt_generation'] = !empty($jwtResult) ? 'success' : 'failed';
            
            // Test API key generation
            $apiKeyResult = $this->authService->generateApiKey($user);
            $results['api_key_generation'] = !empty($apiKeyResult) ? 'success' : 'failed';
            
            // Test credentials validation
            $validationResult = $this->authService->validateCredentials('test@example.com', 'password123');
            $results['credentials_validation'] = $validationResult['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Authorization Service
     */
    private function testAuthorizationService()
    {
        $results = [];
        
        try {
            // Test health check
            $healthResult = $this->authorizationService->healthCheck();
            $results['health_check'] = $healthResult['success'] ? 'success' : 'failed';
            
            // Test user permissions
            $user = ['id' => 'test_user', 'role' => 'user', 'permissions' => ['read', 'write']];
            $permissionsResult = $this->authorizationService->getUserPermissions($user);
            $results['get_user_permissions'] = !empty($permissionsResult) ? 'success' : 'failed';
            
            // Test role check
            $roleResult = $this->authorizationService->hasRole($user, 'user');
            $results['role_check'] = $roleResult ? 'success' : 'failed';
            
            // Test access level
            $accessLevelResult = $this->authorizationService->getAccessLevel($user);
            $results['get_access_level'] = !empty($accessLevelResult) ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Rate Limit Service
     */
    private function testRateLimitService()
    {
        $results = [];
        
        try {
            // Test health check
            $healthResult = $this->rateLimitService->healthCheck();
            $results['health_check'] = $healthResult['success'] ? 'success' : 'failed';
            
            // Test rate limit stats
            $statsResult = $this->rateLimitService->getRateLimitStats();
            $results['get_stats'] = $statsResult['success'] ? 'success' : 'failed';
            
            // Test user rate limit config
            $configResult = $this->rateLimitService->configureUserRateLimit('test_user', 100, 60);
            $results['configure_rate_limit'] = $configResult['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Logging Service
     */
    private function testLoggingService()
    {
        $results = [];
        
        try {
            // Test health check
            $healthResult = $this->loggingService->healthCheck();
            $results['health_check'] = $healthResult['success'] ? 'success' : 'failed';
            
            // Test logging stats
            $statsResult = $this->loggingService->getLoggingStats();
            $results['get_stats'] = $statsResult['success'] ? 'success' : 'failed';
            
            // Test clean old logs
            $cleanResult = $this->loggingService->cleanOldLogs(7);
            $results['clean_old_logs'] = $cleanResult['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Caching Service
     */
    private function testCachingService()
    {
        $results = [];
        
        try {
            // Test health check
            $healthResult = $this->cachingService->healthCheck();
            $results['health_check'] = $healthResult['success'] ? 'success' : 'failed';
            
            // Test basic operations
            $putResult = $this->cachingService->put('test_key', 'test_value', 60);
            $results['put'] = $putResult ? 'success' : 'failed';
            
            $getResult = $this->cachingService->get('test_key');
            $results['get'] = $getResult === 'test_value' ? 'success' : 'failed';
            
            $hasResult = $this->cachingService->has('test_key');
            $results['has'] = $hasResult ? 'success' : 'failed';
            
            $forgetResult = $this->cachingService->forget('test_key');
            $results['forget'] = $forgetResult ? 'success' : 'failed';
            
            // Test cache stats
            $statsResult = $this->cachingService->getCacheStats();
            $results['get_stats'] = $statsResult['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test Monitoring Service
     */
    private function testMonitoringService()
    {
        $results = [];
        
        try {
            // Test health check
            $healthResult = $this->monitoringService->healthCheck();
            $results['health_check'] = $healthResult['success'] ? 'success' : 'failed';
            
            // Test aggregated stats
            $statsResult = $this->monitoringService->getAggregatedStats();
            $results['get_aggregated_stats'] = $statsResult['success'] ? 'success' : 'failed';
            
            // Test real-time metrics
            $realTimeResult = $this->monitoringService->getRealTimeMetrics();
            $results['get_real_time_metrics'] = $realTimeResult['success'] ? 'success' : 'failed';
            
            // Test clean old metrics
            $cleanResult = $this->monitoringService->cleanOldMetrics(7);
            $results['clean_old_metrics'] = $cleanResult['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
