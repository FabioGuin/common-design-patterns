<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Services\ReportService;
use App\Bulkhead\BulkheadManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BulkheadController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private InventoryService $inventoryService,
        private NotificationService $notificationService,
        private ReportService $reportService,
        private BulkheadManager $bulkheadManager
    ) {}

    public function index()
    {
        return view('bulkhead.index');
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

    public function generateReport(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'sales');
            $parameter = $request->input('parameter', 'monthly');

            $result = match ($type) {
                'sales' => $this->reportService->generateSalesReport($parameter),
                'inventory' => $this->reportService->generateInventoryReport($parameter),
                'user' => $this->reportService->generateUserReport($parameter),
                default => throw new \InvalidArgumentException("Unsupported report type: {$type}")
            };

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
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
                'report' => $this->reportService->getServiceStatus(),
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
                'report' => $this->reportService->getServiceStatus(),
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

    public function resetBulkhead(string $serviceName): JsonResponse
    {
        try {
            $success = $this->bulkheadManager->resetBulkhead($serviceName . '_service');

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => "Service {$serviceName} not found"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Bulkhead for {$serviceName} has been reset"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetAllBulkheads(): JsonResponse
    {
        try {
            $this->bulkheadManager->resetAllBulkheads();

            return response()->json([
                'success' => true,
                'message' => 'All bulkheads have been reset'
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
            $metrics = \App\Models\BulkheadMetric::orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->groupBy('service_name')
                ->map(function ($serviceMetrics) {
                    return [
                        'total_executions' => $serviceMetrics->sum('total_executions'),
                        'successful_executions' => $serviceMetrics->sum('successful_executions'),
                        'failed_executions' => $serviceMetrics->sum('failed_executions'),
                        'avg_execution_time' => $serviceMetrics->avg('execution_time'),
                        'avg_memory_used' => $serviceMetrics->avg('memory_used'),
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
