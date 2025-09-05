<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle incoming webhook
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            // Verify webhook signature
            if (!$this->webhookService->verifySignature($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ], 401);
            }

            // Check idempotency
            if ($this->webhookService->isDuplicate($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook already processed'
                ]);
            }

            // Process webhook
            $result = $this->webhookService->processWebhook($request);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'headers' => $request->headers->all(),
                'body' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment webhook
     */
    public function handlePaymentWebhook(Request $request): JsonResponse
    {
        try {
            // Verify payment webhook signature
            if (!$this->webhookService->verifyPaymentSignature($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment webhook signature'
                ], 401);
            }

            // Check idempotency
            if ($this->webhookService->isDuplicate($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment webhook already processed'
                ]);
            }

            // Process payment webhook
            $result = $this->webhookService->processPaymentWebhook($request);

            return response()->json([
                'success' => true,
                'message' => 'Payment webhook processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Payment webhook processing failed', [
                'error' => $e->getMessage(),
                'headers' => $request->headers->all(),
                'body' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment webhook processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats(): JsonResponse
    {
        try {
            $stats = $this->webhookService->getWebhookStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get webhook statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get webhook logs
     */
    public function getWebhookLogs(Request $request): JsonResponse
    {
        try {
            $logs = $this->webhookService->getWebhookLogs(
                $request->input('limit', 50),
                $request->input('offset', 0)
            );

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get webhook logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry failed webhook
     */
    public function retryWebhook(Request $request): JsonResponse
    {
        $request->validate([
            'webhook_id' => 'required|string'
        ]);

        try {
            $result = $this->webhookService->retryWebhook($request->input('webhook_id'));

            return response()->json([
                'success' => true,
                'message' => 'Webhook retried successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test webhook endpoint
     */
    public function testWebhook(Request $request): JsonResponse
    {
        try {
            $testData = $request->input('data', [
                'event' => 'test',
                'timestamp' => now()->toISOString(),
                'data' => [
                    'message' => 'This is a test webhook'
                ]
            ]);

            $result = $this->webhookService->processTestWebhook($testData);

            return response()->json([
                'success' => true,
                'message' => 'Test webhook processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test webhook failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
