<?php

namespace App\Http\Controllers;

use App\Sagas\OrderSaga;
use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SagaController extends Controller
{
    public function __construct(
        private OrderSaga $orderSaga,
        private InventoryService $inventoryService,
        private PaymentService $paymentService,
        private NotificationService $notificationService,
        private OrderService $orderService
    ) {}

    public function index()
    {
        return view('saga.index');
    }

    public function executeOrderSaga(Request $request): JsonResponse
    {
        try {
            $orderData = [
                'order_id' => $request->input('order_id'),
                'customer_id' => $request->input('customer_id'),
                'customer_email' => $request->input('customer_email'),
                'product_id' => $request->input('product_id'),
                'quantity' => (int) $request->input('quantity'),
                'total_amount' => (float) $request->input('total_amount'),
                'payment_method' => $request->input('payment_method', 'credit_card'),
            ];

            $result = $this->orderSaga->execute($orderData);

            return response()->json([
                'success' => true,
                'message' => $result['success'] ? 'Saga completata con successo' : 'Saga fallita e compensata',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getSagaStatus(string $sagaId): JsonResponse
    {
        try {
            $status = $this->orderSaga->getSagaStatus($sagaId);

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saga non trovata'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllSagas(): JsonResponse
    {
        try {
            $sagas = $this->orderSaga->getAllSagas();

            return response()->json([
                'success' => true,
                'sagas' => $sagas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getInventoryReservations(): JsonResponse
    {
        try {
            $reservations = $this->inventoryService->getAllReservations();

            return response()->json([
                'success' => true,
                'reservations' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPayments(): JsonResponse
    {
        try {
            $payments = $this->paymentService->getAllPayments();

            return response()->json([
                'success' => true,
                'payments' => $payments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getNotifications(): JsonResponse
    {
        try {
            $notifications = $this->notificationService->getAllNotifications();

            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getOrders(): JsonResponse
    {
        try {
            $orders = $this->orderService->getAllOrders();

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSagaStats(): JsonResponse
    {
        try {
            $sagas = $this->orderSaga->getAllSagas();
            
            $stats = [
                'total_sagas' => count($sagas),
                'completed' => count(array_filter($sagas, fn($s) => $s['status'] === 'completed')),
                'compensated' => count(array_filter($sagas, fn($s) => $s['status'] === 'compensated')),
                'running' => count(array_filter($sagas, fn($s) => $s['status'] === 'running')),
                'compensating' => count(array_filter($sagas, fn($s) => $s['status'] === 'compensating')),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
