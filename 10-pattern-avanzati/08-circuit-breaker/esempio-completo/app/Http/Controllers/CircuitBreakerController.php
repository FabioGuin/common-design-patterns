<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\CircuitBreaker\CircuitBreakerManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CircuitBreakerController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private InventoryService $inventoryService,
        private NotificationService $notificationService,
        private CircuitBreakerManager $circuitBreakerManager
    ) {}

    public function index()
    {
        return view('circuit-breaker.index');
    }

    public function processPayment(Request $request): JsonResponse
    {
        try {
            $paymentData = [
                'amount' => (float) $request->input('amount'),
                'payment_method' => $request->input('payment_method', 'credit_card'),
                'currency' => $request->input('currency', 'EUR'),
            ];

            $result = $this->paymentService->processPayment($paymentData);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function checkInventory(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('product_id');
            $quantity = (int) $request->input('quantity');

            $result = $this->inventoryService->checkAvailability($productId, $quantity);

            return response()->json([
                'success' => true,
                'message' => 'Inventory checked successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function sendNotification(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'email');
            $to = $request->input('to');
            $subject = $request->input('subject', 'Notification');
            $body = $request->input('body', 'This is a test notification');

            $result = match ($type) {
                'email' => $this->notificationService->sendEmail($to, $subject, $body),
                'sms' => $this->notificationService->sendSms($to, $body),
                'push' => $this->notificationService->sendPushNotification($to, $subject, $body),
                default => throw new \InvalidArgumentException("Unsupported notification type: {$type}")
            };

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getServiceStatus(string $serviceName): JsonResponse
    {
        try {
            $status = match ($serviceName) {
                'payment' => $this->paymentService->getServiceStatus(),
                'inventory' => $this->inventoryService->getServiceStatus(),
                'notification' => $this->notificationService->getServiceStatus(),
                default => throw new \InvalidArgumentException("Unknown service: {$serviceName}")
            };

            return response()->json([
                'success' => true,
                'service' => $serviceName,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllServicesStatus(): JsonResponse
    {
        try {
            $services = [
                'payment' => $this->paymentService->getServiceStatus(),
                'inventory' => $this->inventoryService->getServiceStatus(),
                'notification' => $this->notificationService->getServiceStatus(),
            ];

            return response()->json([
                'success' => true,
                'services' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetCircuitBreaker(string $serviceName): JsonResponse
    {
        try {
            $success = $this->circuitBreakerManager->resetCircuitBreaker($serviceName . '_service');

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => "Service {$serviceName} not found"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Circuit breaker for {$serviceName} has been reset"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetAllCircuitBreakers(): JsonResponse
    {
        try {
            $this->circuitBreakerManager->resetAllCircuitBreakers();

            return response()->json([
                'success' => true,
                'message' => 'All circuit breakers have been reset'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getMetrics(): JsonResponse
    {
        try {
            $metrics = \App\Models\CircuitBreakerMetric::orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->groupBy('service_name')
                ->map(function ($serviceMetrics) {
                    return [
                        'total_calls' => $serviceMetrics->sum('total_calls'),
                        'total_failures' => $serviceMetrics->sum('total_failures'),
                        'success_count' => $serviceMetrics->sum('success_count'),
                        'failure_count' => $serviceMetrics->sum('failure_count'),
                        'last_state' => $serviceMetrics->first()->state,
                        'last_metric' => $serviceMetrics->first()->metric_type,
                        'last_updated' => $serviceMetrics->first()->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
