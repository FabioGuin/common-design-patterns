<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Jobs\ProcessOrderJob;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Queue;

class QueueController extends Controller
{
    private QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Send email
     */
    public function sendEmail(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string|in:text,html'
        ]);

        try {
            $job = new SendEmailJob(
                $request->input('to'),
                $request->input('subject'),
                $request->input('body'),
                $request->input('type', 'text')
            );

            $this->queueService->dispatchJob($job);

            return response()->json([
                'success' => true,
                'message' => 'Email queued successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to queue email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process order
     */
    public function processOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id'
        ]);

        try {
            $job = new ProcessOrderJob($request->input('order_id'));
            $this->queueService->dispatchJob($job);

            return response()->json([
                'success' => true,
                'message' => 'Order processing queued successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to queue order processing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): JsonResponse
    {
        try {
            $stats = $this->queueService->getQueueStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get queue statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get failed jobs
     */
    public function getFailedJobs(): JsonResponse
    {
        try {
            $failedJobs = $this->queueService->getFailedJobs();

            return response()->json([
                'success' => true,
                'data' => $failedJobs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get failed jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry failed job
     */
    public function retryFailedJob(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|string'
        ]);

        try {
            $this->queueService->retryFailedJob($request->input('job_id'));

            return response()->json([
                'success' => true,
                'message' => 'Job retried successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear failed jobs
     */
    public function clearFailedJobs(): JsonResponse
    {
        try {
            $this->queueService->clearFailedJobs();

            return response()->json([
                'success' => true,
                'message' => 'Failed jobs cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear failed jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get queue status
     */
    public function getQueueStatus(): JsonResponse
    {
        try {
            $status = $this->queueService->getQueueStatus();

            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get queue status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
