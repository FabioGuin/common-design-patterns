<?php

namespace App\Http\Controllers;

use App\Services\PaymentApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    private PaymentApiService $paymentApiService;

    public function __construct(PaymentApiService $paymentApiService)
    {
        $this->paymentApiService = $paymentApiService;
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'payment_method' => 'required|string',
            'customer_id' => 'required|string'
        ]);

        try {
            $paymentData = $request->only([
                'amount', 'currency', 'payment_method', 'customer_id'
            ]);

            $result = $this->paymentApiService->processPayment($paymentData);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): JsonResponse
    {
        try {
            $result = $this->paymentApiService->getPaymentStatus($paymentId);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(Request $request, string $paymentId): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        try {
            $result = $this->paymentApiService->refundPayment(
                $paymentId,
                $request->input('amount')
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods(): JsonResponse
    {
        try {
            $result = $this->paymentApiService->getPaymentMethods();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create customer
     */
    public function createCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20'
        ]);

        try {
            $customerData = $request->only(['name', 'email', 'phone']);
            $result = $this->paymentApiService->createCustomer($customerData);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer
     */
    public function getCustomer(string $customerId): JsonResponse
    {
        try {
            $result = $this->paymentApiService->getCustomer($customerId);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear API cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            $pattern = $request->input('pattern');
            $this->paymentApiService->clearCache($pattern);

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get API statistics
     */
    public function getApiStats(): JsonResponse
    {
        try {
            $stats = $this->paymentApiService->getApiStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get API statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
