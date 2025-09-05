<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Create a new order.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:' . implode(',', [
                Order::PAYMENT_METHOD_CREDIT_CARD,
                Order::PAYMENT_METHOD_PAYPAL,
                Order::PAYMENT_METHOD_BANK_TRANSFER
            ]),
            'shipping_address' => 'required|array',
            'shipping_address.street' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:100',
            'billing_address' => 'required|array',
            'billing_address.street' => 'required|string|max:255',
            'billing_address.city' => 'required|string|max:100',
            'billing_address.postal_code' => 'required|string|max:20',
            'billing_address.country' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'shipping_cost' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_amount' => $request->total_amount,
                'status' => Order::STATUS_PENDING,
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'coupon_code' => $request->coupon_code
            ]);

            // L'evento OrderCreated viene automaticamente scatenato dal model booted method

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->getSummary()
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process order payment.
     */
    public function processPayment(Request $request, int $id): JsonResponse
    {
        $order = Order::where('id', $id)
                     ->where('user_id', $request->user()->id)
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if (!$order->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Order is not in pending status'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
            'transaction_id' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Simula processamento pagamento
            $paymentSuccess = $this->simulatePaymentProcessing($order, $request->payment_method);

            if ($paymentSuccess) {
                $order->markAsPaid($request->payment_method, $request->transaction_id);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'data' => $order->getSummary()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment processing failed'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
                      ->orderBy('created_at', 'desc')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                return $order->getSummary();
            })
        ]);
    }

    /**
     * Get specific order.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::where('id', $id)
                     ->where('user_id', $request->user()->id)
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order->getSummary()
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::where('id', $id)
                     ->where('user_id', $request->user()->id)
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', [
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED
            ]),
            'tracking_number' => 'required_if:status,shipped|nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            switch ($request->status) {
                case Order::STATUS_SHIPPED:
                    $order->markAsShipped($request->tracking_number);
                    break;
                case Order::STATUS_DELIVERED:
                    $order->markAsDelivered();
                    break;
                case Order::STATUS_CANCELLED:
                    $order->cancel();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order->getSummary()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simulate payment processing.
     */
    private function simulatePaymentProcessing(Order $order, string $paymentMethod): bool
    {
        // Simula processamento pagamento
        // In un'applicazione reale, integreresti con un gateway di pagamento reale
        
        // Simula 90% success rate
        return rand(1, 10) <= 9;
    }
}
