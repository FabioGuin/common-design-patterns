<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Retry\RetryManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RetryController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private InventoryService $inventoryService,
        private NotificationService $notificationService,
        private RetryManager $retryManager
    ) {}

    public function index()
    {
        return view('retry.index');
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

    public function getMetrics(): JsonResponse
    {
        try {
            $metrics = \App\Models\RetryMetric::orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->groupBy('service_name')
                ->map(function ($serviceMetrics) {
                    return [
                        'total_attempts' => $serviceMetrics->sum('total_attempts'),
                        'successful_attempts' => $serviceMetrics->sum('successful_attempts'),
                        'failed_attempts' => $serviceMetrics->sum('failed_attempts'),
                        'success_rate' => $this->calculateSuccessRate($serviceMetrics),
                        'avg_execution_time' => $serviceMetrics->avg('execution_time'),
                        'avg_memory_used' => $serviceMetrics->avg('memory_used'),
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

    public function getRetryAttempts(): JsonResponse
    {
        try {
            $attempts = \App\Models\RetryAttempt::orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->groupBy('service_name')
                ->map(function ($serviceAttempts) {
                    return [
                        'total_attempts' => $serviceAttempts->count(),
                        'error_codes' => $serviceAttempts->pluck('error_code')->unique()->values(),
                        'avg_execution_time' => $serviceAttempts->avg('execution_time'),
                        'avg_memory_used' => $serviceAttempts->avg('memory_used'),
                        'last_attempt' => $serviceAttempts->first()->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'attempts' => $attempts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateSuccessRate($metrics): float
    {
        $totalAttempts = $metrics->sum('total_attempts');
        $successfulAttempts = $metrics->sum('successful_attempts');

        return $totalAttempts > 0 ? ($successfulAttempts / $totalAttempts) * 100 : 0;
    }
}
