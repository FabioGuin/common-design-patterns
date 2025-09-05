<?php

namespace App\Http\Controllers;

use App\Services\SharedDatabaseService;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Controller per dimostrare il Shared Database Anti-pattern
 * 
 * Questo controller mostra i problemi dell'utilizzo di un database condiviso
 * tra multiple servizi in un'applicazione Laravel.
 */
class SharedDatabaseController extends Controller
{
    private SharedDatabaseService $sharedDb;
    private UserService $userService;
    private ProductService $productService;
    private OrderService $orderService;
    private PaymentService $paymentService;

    public function __construct(
        SharedDatabaseService $sharedDb,
        UserService $userService,
        ProductService $productService,
        OrderService $orderService,
        PaymentService $paymentService
    ) {
        $this->sharedDb = $sharedDb;
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Mostra la pagina di esempio del Shared Database Anti-pattern
     */
    public function example()
    {
        return view('shared-database.example', [
            'sharedDb' => $this->sharedDb,
            'userService' => $this->userService,
            'productService' => $this->productService,
            'orderService' => $this->orderService,
            'paymentService' => $this->paymentService
        ]);
    }

    /**
     * Crea un utente
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un prodotto
     */
    public function createProduct(Request $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un ordine
     */
    public function createOrder(Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un pagamento
     */
    public function createPayment(Request $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->createPayment($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => $payment
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processa un pagamento
     */
    public function processPayment(Request $request): JsonResponse
    {
        try {
            $paymentId = $request->input('payment_id');
            $payment = $this->paymentService->processPayment($paymentId);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $payment
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simula un deadlock
     */
    public function simulateDeadlock(): JsonResponse
    {
        try {
            $deadlock = $this->sharedDb->simulateDeadlock();
            
            return response()->json([
                'success' => true,
                'message' => 'Deadlock simulated successfully',
                'data' => $deadlock
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to simulate deadlock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche del database condiviso
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'shared_database' => $this->sharedDb->getStats(),
                'user_service' => $this->userService->getStats(),
                'product_service' => $this->productService->getStats(),
                'order_service' => $this->orderService->getStats(),
                'payment_service' => $this->paymentService->getStats()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene la cronologia dei conflitti
     */
    public function getConflictHistory(): JsonResponse
    {
        try {
            $conflicts = $this->sharedDb->getConflictHistory();
            
            return response()->json([
                'success' => true,
                'data' => $conflicts
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get conflict history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene la cronologia dei lock
     */
    public function getLockHistory(): JsonResponse
    {
        try {
            $locks = $this->sharedDb->getLockHistory();
            
            return response()->json([
                'success' => true,
                'data' => $locks
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get lock history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simula una transazione distribuita complessa
     */
    public function simulateComplexTransaction(): JsonResponse
    {
        try {
            $operations = [
                [
                    'table' => 'users',
                    'operation' => 'read',
                    'data' => ['id' => 1]
                ],
                [
                    'table' => 'products',
                    'operation' => 'read',
                    'data' => ['id' => 1]
                ],
                [
                    'table' => 'orders',
                    'operation' => 'write',
                    'data' => ['user_id' => 1, 'total' => 100.00]
                ],
                [
                    'table' => 'payments',
                    'operation' => 'write',
                    'data' => ['order_id' => 1, 'amount' => 100.00]
                ]
            ];
            
            $result = $this->sharedDb->executeDistributedTransaction($operations);
            
            return response()->json([
                'success' => true,
                'message' => 'Complex transaction executed',
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute complex transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa la scalabilità del sistema
     */
    public function testScalability(): JsonResponse
    {
        try {
            $results = [];
            $startTime = microtime(true);
            
            // Simula operazioni simultanee
            for ($i = 0; $i < 10; $i++) {
                try {
                    $user = $this->userService->createUser([
                        'name' => "Test User $i",
                        'email' => "test$i@example.com"
                    ]);
                    $results[] = ['operation' => 'create_user', 'success' => true, 'data' => $user];
                } catch (Exception $e) {
                    $results[] = ['operation' => 'create_user', 'success' => false, 'error' => $e->getMessage()];
                }
            }
            
            $duration = microtime(true) - $startTime;
            
            return response()->json([
                'success' => true,
                'message' => 'Scalability test completed',
                'data' => [
                    'duration' => $duration,
                    'operations' => $results,
                    'total_operations' => count($results),
                    'successful_operations' => count(array_filter($results, fn($r) => $r['success'])),
                    'failed_operations' => count(array_filter($results, fn($r) => !$r['success']))
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to test scalability',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
