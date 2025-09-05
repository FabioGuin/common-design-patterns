<?php

namespace App\Services;

use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ApiGatewayService
{
    protected $serviceId = 'api-gateway';
    protected $version = '1.0.0';
    protected $userService;
    protected $productService;
    protected $orderService;
    protected $paymentService;

    public function __construct(
        UserService $userService,
        ProductService $productService,
        OrderService $orderService,
        PaymentService $paymentService
    ) {
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Route una richiesta al servizio appropriato
     */
    public function routeRequest(string $path, string $method, array $data = []): array
    {
        try {
            $pathParts = explode('/', trim($path, '/'));
            $service = $pathParts[0] ?? '';

            Log::info("API Gateway: Richiesta ricevuta", [
                'path' => $path,
                'method' => $method,
                'service' => $service,
                'gateway' => $this->serviceId
            ]);

            switch ($service) {
                case 'users':
                    return $this->routeToUserService($pathParts, $method, $data);
                
                case 'products':
                    return $this->routeToProductService($pathParts, $method, $data);
                
                case 'orders':
                    return $this->routeToOrderService($pathParts, $method, $data);
                
                case 'payments':
                    return $this->routeToPaymentService($pathParts, $method, $data);
                
                default:
                    return [
                        'success' => false,
                        'error' => 'Servizio non trovato: ' . $service,
                        'gateway' => $this->serviceId
                    ];
            }

        } catch (\Exception $e) {
            Log::error("API Gateway: Errore nel routing", [
                'error' => $e->getMessage(),
                'path' => $path,
                'method' => $method,
                'gateway' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->serviceId
            ];
        }
    }

    /**
     * Route al User Service
     */
    private function routeToUserService(array $pathParts, string $method, array $data): array
    {
        $action = $pathParts[1] ?? '';
        $id = $pathParts[2] ?? null;

        switch ($method) {
            case 'POST':
                if ($action === '') {
                    return $this->userService->createUser($data);
                }
                break;
            
            case 'GET':
                if ($action === '') {
                    return $this->userService->listUsers(
                        $data['limit'] ?? 100,
                        $data['offset'] ?? 0
                    );
                } elseif ($id) {
                    return $this->userService->getUser($id);
                }
                break;
            
            case 'PUT':
                if ($id) {
                    return $this->userService->updateUser($id, $data);
                }
                break;
            
            case 'DELETE':
                if ($id) {
                    return $this->userService->deleteUser($id);
                }
                break;
        }

        return [
            'success' => false,
            'error' => 'Azione non supportata per User Service',
            'gateway' => $this->serviceId
        ];
    }

    /**
     * Route al Product Service
     */
    private function routeToProductService(array $pathParts, string $method, array $data): array
    {
        $action = $pathParts[1] ?? '';
        $id = $pathParts[2] ?? null;

        switch ($method) {
            case 'POST':
                if ($action === '') {
                    return $this->productService->createProduct($data);
                }
                break;
            
            case 'GET':
                if ($action === '') {
                    return $this->productService->listProducts(
                        $data['limit'] ?? 100,
                        $data['offset'] ?? 0,
                        $data['filters'] ?? []
                    );
                } elseif ($id) {
                    return $this->productService->getProduct($id);
                }
                break;
            
            case 'PUT':
                if ($id) {
                    return $this->productService->updateProduct($id, $data);
                }
                break;
        }

        return [
            'success' => false,
            'error' => 'Azione non supportata per Product Service',
            'gateway' => $this->serviceId
        ];
    }

    /**
     * Route al Order Service
     */
    private function routeToOrderService(array $pathParts, string $method, array $data): array
    {
        $action = $pathParts[1] ?? '';
        $id = $pathParts[2] ?? null;

        switch ($method) {
            case 'POST':
                if ($action === '') {
                    return $this->orderService->createOrder($data);
                } elseif ($action === 'payment' && $id) {
                    return $this->orderService->processPayment($id, $data);
                }
                break;
            
            case 'GET':
                if ($action === '') {
                    return $this->orderService->listOrders(
                        $data['limit'] ?? 100,
                        $data['offset'] ?? 0,
                        $data['filters'] ?? []
                    );
                } elseif ($id) {
                    return $this->orderService->getOrder($id);
                }
                break;
            
            case 'PUT':
                if ($id && $action === 'status') {
                    return $this->orderService->updateOrderStatus($id, $data['status']);
                }
                break;
            
            case 'DELETE':
                if ($id) {
                    return $this->orderService->cancelOrder($id, $data['reason'] ?? null);
                }
                break;
        }

        return [
            'success' => false,
            'error' => 'Azione non supportata per Order Service',
            'gateway' => $this->serviceId
        ];
    }

    /**
     * Route al Payment Service
     */
    private function routeToPaymentService(array $pathParts, string $method, array $data): array
    {
        $action = $pathParts[1] ?? '';
        $id = $pathParts[2] ?? null;

        switch ($method) {
            case 'POST':
                if ($action === '') {
                    return $this->paymentService->processPayment($data);
                } elseif ($action === 'refund' && $id) {
                    return $this->paymentService->refundPayment($id, $data['amount'] ?? null);
                }
                break;
            
            case 'GET':
                if ($action === '') {
                    return $this->paymentService->listPayments(
                        $data['limit'] ?? 100,
                        $data['offset'] ?? 0,
                        $data['filters'] ?? []
                    );
                } elseif ($id) {
                    if ($action === 'status') {
                        return $this->paymentService->checkPaymentStatus($id);
                    } else {
                        return $this->paymentService->getPayment($id);
                    }
                } elseif ($action === 'order' && $id) {
                    return $this->paymentService->getPaymentsByOrder($id);
                }
                break;
        }

        return [
            'success' => false,
            'error' => 'Azione non supportata per Payment Service',
            'gateway' => $this->serviceId
        ];
    }

    /**
     * Ottiene lo status di tutti i servizi
     */
    public function getServicesStatus(): array
    {
        try {
            $userHealth = $this->userService->healthCheck();
            $productHealth = $this->productService->healthCheck();
            $orderHealth = $this->orderService->healthCheck();
            $paymentHealth = $this->paymentService->healthCheck();

            $services = [
                'user_service' => [
                    'status' => $userHealth['status'],
                    'version' => $userHealth['version'] ?? 'unknown',
                    'healthy' => $userHealth['success']
                ],
                'product_service' => [
                    'status' => $productHealth['status'],
                    'version' => $productHealth['version'] ?? 'unknown',
                    'healthy' => $productHealth['success']
                ],
                'order_service' => [
                    'status' => $orderHealth['status'],
                    'version' => $orderHealth['version'] ?? 'unknown',
                    'healthy' => $orderHealth['success']
                ],
                'payment_service' => [
                    'status' => $paymentHealth['status'],
                    'version' => $paymentHealth['version'] ?? 'unknown',
                    'healthy' => $paymentHealth['success']
                ]
            ];

            $allHealthy = collect($services)->every(function($service) {
                return $service['healthy'];
            });

            return [
                'success' => true,
                'data' => [
                    'gateway' => $this->serviceId,
                    'version' => $this->version,
                    'overall_status' => $allHealthy ? 'healthy' : 'degraded',
                    'services' => $services,
                    'timestamp' => now()->toISOString()
                ],
                'gateway' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("API Gateway: Errore nel recupero status servizi", [
                'error' => $e->getMessage(),
                'gateway' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene statistiche aggregate
     */
    public function getAggregatedStats(): array
    {
        try {
            $userStats = $this->userService->getUserStats();
            $productStats = $this->productService->getProductStats();
            $orderStats = $this->orderService->getOrderStats();
            $paymentStats = $this->paymentService->getPaymentStats();

            return [
                'success' => true,
                'data' => [
                    'gateway' => $this->serviceId,
                    'version' => $this->version,
                    'users' => $userStats['success'] ? $userStats['data'] : null,
                    'products' => $productStats['success'] ? $productStats['data'] : null,
                    'orders' => $orderStats['success'] ? $orderStats['data'] : null,
                    'payments' => $paymentStats['success'] ? $paymentStats['data'] : null,
                    'timestamp' => now()->toISOString()
                ],
                'gateway' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("API Gateway: Errore nel recupero statistiche aggregate", [
                'error' => $e->getMessage(),
                'gateway' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del gateway
     */
    public function healthCheck(): array
    {
        try {
            $servicesStatus = $this->getServicesStatus();
            $allHealthy = $servicesStatus['success'] && 
                         $servicesStatus['data']['overall_status'] === 'healthy';

            return [
                'success' => true,
                'status' => $allHealthy ? 'healthy' : 'degraded',
                'gateway' => $this->serviceId,
                'version' => $this->version,
                'services_count' => count($servicesStatus['data']['services']),
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'gateway' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Ottiene l'ID del gateway
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del gateway
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
