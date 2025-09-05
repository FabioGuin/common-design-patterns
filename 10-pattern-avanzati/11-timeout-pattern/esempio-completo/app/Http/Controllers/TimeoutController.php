<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Timeout\TimeoutManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TimeoutController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private InventoryService $inventoryService,
        private NotificationService $notificationService,
        private TimeoutManager $timeoutManager
    ) {}

    public function index()
    {
        return view('timeout.index');
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
            $metrics = \App\Models\TimeoutMetric::orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->groupBy('service_name')
                ->map(function ($serviceMetrics) {
                    return [
                        'total_operations' => $serviceMetrics->sum('total_operations'),
                        'successful_operations' => $serviceMetrics->sum('successful_operations'),
                        'timeout_operations' => $serviceMetrics->sum('timeout_operations'),
                        'error_operations' => $serviceMetrics->sum('error_operations'),
                        'success_rate' => $this->calculateSuccessRate($serviceMetrics),
                        'timeout_rate' => $this->calculateTimeoutRate($serviceMetrics),
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

    public function getTimeoutEvents(): JsonResponse
    {
        try {
            $events = \App\Models\TimeoutEvent::orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->groupBy('service_name')
                ->map(function ($serviceEvents) {
                    return [
                        'total_events' => $serviceEvents->count(),
                        'timeout_events' => $serviceEvents->where('event_type', 'timeout')->count(),
                        'error_events' => $serviceEvents->where('event_type', 'error')->count(),
                        'avg_execution_time' => $serviceEvents->avg('execution_time'),
                        'last_event' => $serviceEvents->first()->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'events' => $events
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
        $totalOperations = $metrics->sum('total_operations');
        $successfulOperations = $metrics->sum('successful_operations');

        return $totalOperations > 0 ? ($successfulOperations / $totalOperations) * 100 : 0;
    }

    private function calculateTimeoutRate($metrics): float
    {
        $totalOperations = $metrics->sum('total_operations');
        $timeoutOperations = $metrics->sum('timeout_operations');

        return $totalOperations > 0 ? ($timeoutOperations / $totalOperations) * 100 : 0;
    }
}
