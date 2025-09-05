<?php

namespace App\Http\Controllers;

use App\Services\SagaOrchestratorService;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Controller per il Saga Orchestration Pattern
 * 
 * Questo controller gestisce le operazioni delle saga e fornisce
 * un'interfaccia per monitorare e controllare le transazioni distribuite.
 */
class SagaOrchestratorController extends Controller
{
    private SagaOrchestratorService $orchestrator;
    private UserService $userService;
    private ProductService $productService;
    private OrderService $orderService;
    private PaymentService $paymentService;
    private InventoryService $inventoryService;
    private NotificationService $notificationService;

    public function __construct(
        SagaOrchestratorService $orchestrator,
        UserService $userService,
        ProductService $productService,
        OrderService $orderService,
        PaymentService $paymentService,
        InventoryService $inventoryService,
        NotificationService $notificationService
    ) {
        $this->orchestrator = $orchestrator;
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
        $this->notificationService = $notificationService;
    }

    /**
     * Mostra la pagina di esempio del Saga Orchestration Pattern
     */
    public function example()
    {
        return view('saga-orchestration.example', [
            'orchestrator' => $this->orchestrator,
            'userService' => $this->userService,
            'productService' => $this->productService,
            'orderService' => $this->orderService,
            'paymentService' => $this->paymentService,
            'inventoryService' => $this->inventoryService,
            'notificationService' => $this->notificationService
        ]);
    }

    /**
     * Avvia una saga per creare un ordine
     */
    public function startCreateOrderSaga(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer',
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
                'total' => 'required|numeric|min:0'
            ]);

            $saga = $this->orchestrator->startSaga('create_order', $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Create order saga started successfully',
                'data' => $saga
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start create order saga',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Avvia una saga per cancellare un ordine
     */
    public function startCancelOrderSaga(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'order_id' => 'required|integer',
                'user_id' => 'required|integer'
            ]);

            $saga = $this->orchestrator->startSaga('cancel_order', $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Cancel order saga started successfully',
                'data' => $saga
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start cancel order saga',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene lo stato di una saga
     */
    public function getSagaStatus(int $sagaId): JsonResponse
    {
        try {
            $status = $this->orchestrator->getSagaStatus($sagaId);
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get saga status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene la cronologia delle saga
     */
    public function getSagaHistory(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 50);
            $history = $this->orchestrator->getSagaHistory($limit);
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get saga history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche del sistema
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'orchestrator' => $this->orchestrator->getStats(),
                'user_service' => $this->userService->getStats(),
                'product_service' => $this->productService->getStats(),
                'order_service' => $this->orderService->getStats(),
                'payment_service' => $this->paymentService->getStats(),
                'inventory_service' => $this->inventoryService->getStats(),
                'notification_service' => $this->notificationService->getStats()
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
     * Pulisce le saga vecchie
     */
    public function cleanupOldSagas(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);
            $deletedCount = $this->orchestrator->cleanupOldSagas($days);
            
            return response()->json([
                'success' => true,
                'message' => 'Old sagas cleaned up successfully',
                'data' => [
                    'deleted_count' => $deletedCount,
                    'days' => $days
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup old sagas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa la resilienza del sistema
     */
    public function testResilience(): JsonResponse
    {
        try {
            $results = [];
            $startTime = microtime(true);
            
            // Simula multiple saga simultanee
            for ($i = 0; $i < 5; $i++) {
                try {
                    $saga = $this->orchestrator->startSaga('create_order', [
                        'user_id' => $i + 1,
                        'product_id' => $i + 1,
                        'quantity' => 1,
                        'total' => 29.99
                    ]);
                    $results[] = ['saga_id' => $saga['saga_id'], 'status' => 'started'];
                } catch (Exception $e) {
                    $results[] = ['error' => $e->getMessage()];
                }
            }
            
            $duration = microtime(true) - $startTime;
            
            return response()->json([
                'success' => true,
                'message' => 'Resilience test completed',
                'data' => [
                    'duration' => $duration,
                    'sagas' => $results,
                    'total_sagas' => count($results),
                    'successful_sagas' => count(array_filter($results, fn($r) => isset($r['saga_id']))),
                    'failed_sagas' => count(array_filter($results, fn($r) => isset($r['error'])))
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to test resilience',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simula un fallimento di un passaggio
     */
    public function simulateStepFailure(Request $request): JsonResponse
    {
        try {
            $sagaId = $request->input('saga_id');
            $stepId = $request->input('step_id');
            $error = $request->input('error', 'Simulated failure');
            
            $this->orchestrator->failStep($sagaId, $stepId, $error);
            
            return response()->json([
                'success' => true,
                'message' => 'Step failure simulated successfully',
                'data' => [
                    'saga_id' => $sagaId,
                    'step_id' => $stepId,
                    'error' => $error
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to simulate step failure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene i dettagli di una saga
     */
    public function getSagaDetails(int $sagaId): JsonResponse
    {
        try {
            $status = $this->orchestrator->getSagaStatus($sagaId);
            
            // Aggiunge dettagli aggiuntivi
            $details = array_merge($status, [
                'duration' => $status['completed_at'] ? 
                    now()->parse($status['started_at'])->diffInSeconds(now()->parse($status['completed_at'])) : 
                    now()->parse($status['started_at'])->diffInSeconds(now()),
                'is_expired' => $status['timeout_at'] ? 
                    now()->parse($status['timeout_at'])->isPast() : false,
                'step_details' => array_map(function ($step) {
                    return array_merge($step, [
                        'duration' => $step['completed_at'] ? 
                            now()->parse($step['started_at'])->diffInSeconds(now()->parse($step['completed_at'])) : 
                            now()->parse($step['started_at'])->diffInSeconds(now()),
                        'is_expired' => $step['timeout_at'] ? 
                            now()->parse($step['timeout_at'])->isPast() : false
                    ]);
                }, $status['steps'])
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get saga details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
