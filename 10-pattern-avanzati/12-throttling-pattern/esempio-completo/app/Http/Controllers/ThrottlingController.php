<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Throttling\ThrottlingManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ThrottlingController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private InventoryService $inventoryService,
        private NotificationService $notificationService,
        private ThrottlingManager $throttlingManager
    ) {}

    public function index()
    {
        return view('throttling.index');
    }

    public function processPayment(Request $request): JsonResponse
    {
        try {
            $paymentData = [
                'amount' => (float) $request->input('amount'),
                'payment_method' => $request->input('payment_method', 'credit_card'),
                'currency' => $request->input('currency', 'EUR'),
            ];

            $userId = $request->input('user_id', 'anonymous');

            $result = $this->paymentService->processPayment($paymentData, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() === 429 ? 429 : 400);
        }
    }

    public function checkInventory(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('product_id');
            $quantity = (int) $request->input('quantity');
            $userId = $request->input('user_id', 'anonymous');

            $result = $this->inventoryService->checkAvailability($productId, $quantity, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Inventory checked successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() === 429 ? 429 : 400);
        }
    }

    public function sendNotification(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'email');
            $to = $request->input('to');
            $subject = $request->input('subject', 'Notification');
            $body = $request->input('body', 'This is a test notification');
            $userId = $request->input('user_id', 'anonymous');

            $result = match ($type) {
                'email' => $this->notificationService->sendEmail($to, $subject, $body, $userId),
                'sms' => $this->notificationService->sendSms($to, $body, $userId),
                'push' => $this->notificationService->sendPushNotification($userId, $subject, $body),
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
            ], $e->getCode() === 429 ? 429 : 400);
        }
    }

    public function getServiceStatus(Request $request): JsonResponse
    {
        try {
            $serviceName = $request->input('service_name');
            $userId = $request->input('user_id', 'anonymous');
            $endpoint = $request->input('endpoint');

            $status = match ($serviceName) {
                'payment' => $this->paymentService->getServiceStatus($userId),
                'inventory' => $this->inventoryService->getServiceStatus($userId),
                'notification' => $this->notificationService->getServiceStatus($userId),
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

    public function getAllServicesStatus(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id', 'anonymous');

            $services = [
                'payment' => $this->paymentService->getServiceStatus($userId),
                'inventory' => $this->inventoryService->getServiceStatus($userId),
                'notification' => $this->notificationService->getServiceStatus($userId),
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
            $metrics = \App\Models\ThrottlingMetric::orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->groupBy('service_name')
                ->map(function ($serviceMetrics) {
                    return [
                        'total_requests' => $serviceMetrics->sum('total_requests'),
                        'successful_requests' => $serviceMetrics->sum('successful_requests'),
                        'throttled_requests' => $serviceMetrics->sum('throttled_requests'),
                        'error_requests' => $serviceMetrics->sum('error_requests'),
                        'success_rate' => $this->calculateSuccessRate($serviceMetrics),
                        'throttling_rate' => $this->calculateThrottlingRate($serviceMetrics),
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

    public function getThrottlingEvents(): JsonResponse
    {
        try {
            $events = \App\Models\ThrottlingEvent::orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->groupBy('service_name')
                ->map(function ($serviceEvents) {
                    return [
                        'total_events' => $serviceEvents->count(),
                        'throttled_events' => $serviceEvents->where('event_type', 'throttled')->count(),
                        'error_events' => $serviceEvents->where('event_type', 'error')->count(),
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
        $totalRequests = $metrics->sum('total_requests');
        $successfulRequests = $metrics->sum('successful_requests');

        return $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0;
    }

    private function calculateThrottlingRate($metrics): float
    {
        $totalRequests = $metrics->sum('total_requests');
        $throttledRequests = $metrics->sum('throttled_requests');

        return $totalRequests > 0 ? ($throttledRequests / $totalRequests) * 100 : 0;
    }
}
