<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentServiceController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Ottiene tutti i pagamenti
     */
    public function index(): JsonResponse
    {
        $payments = $this->paymentService->getAllPayments();

        return response()->json([
            'success' => true,
            'data' => $payments,
            'service' => 'PaymentService',
            'database' => 'payment_service'
        ]);
    }

    /**
     * Crea un nuovo pagamento
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer',
            'user_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string|in:credit_card,paypal,apple_pay,google_pay'
        ]);

        $payment = $this->paymentService->createPayment($request->all());

        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment created successfully'
        ], 201);
    }

    /**
     * Ottiene un pagamento specifico
     */
    public function show(int $id): JsonResponse
    {
        $payment = $this->paymentService->getPayment($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Processa un pagamento
     */
    public function process(int $id): JsonResponse
    {
        $payment = $this->paymentService->processPayment($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment processed successfully'
        ]);
    }

    /**
     * Rimborsa un pagamento
     */
    public function refund(int $id): JsonResponse
    {
        $payment = $this->paymentService->refundPayment($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment refunded successfully'
        ]);
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function stats(): JsonResponse
    {
        $stats = $this->paymentService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
