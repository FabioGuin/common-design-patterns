<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Payment\Factories\PaymentFactory;
use App\Services\Payment\PaymentResult;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentFactory $paymentFactory
    ) {}
    
    /**
     * Processa un pagamento
     */
    public function processPayment(Request $request): JsonResponse
    {
        $validator = $this->paymentFactory->createValidator();
        $gateway = $this->paymentFactory->createGateway();
        $logger = $this->paymentFactory->createLogger();
        
        // Valida i dati
        $validationResult = $validator->validate($request->all());
        if (!$validationResult->valid) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationResult->errors
            ], 400);
        }
        
        // Logga l'inizio del pagamento
        $logger->logPaymentStart($request->all());
        
        try {
            // Processa il pagamento
            $result = $gateway->processPayment(
                $request->input('amount'),
                $request->all()
            );
            
            // Logga il risultato
            $logger->logPaymentComplete($result->transactionId, $result->success);
            
            return response()->json([
                'success' => $result->success,
                'message' => $result->message,
                'transaction_id' => $result->transactionId,
                'provider' => $this->paymentFactory->getProviderName(),
                'data' => $result->data
            ], $result->success ? 200 : 400);
            
        } catch (\Exception $e) {
            $logger->logPaymentError($e->getMessage(), [
                'amount' => $request->input('amount'),
                'provider' => $this->paymentFactory->getProviderName()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Rimborsa un pagamento
     */
    public function refundPayment(Request $request): JsonResponse
    {
        $gateway = $this->paymentFactory->createGateway();
        $logger = $this->paymentFactory->createLogger();
        
        $request->validate([
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01'
        ]);
        
        try {
            $result = $gateway->refundPayment(
                $request->input('transaction_id'),
                $request->input('amount')
            );
            
            $logger->log('info', 'Refund processed', [
                'transaction_id' => $request->input('transaction_id'),
                'amount' => $request->input('amount'),
                'success' => $result->success
            ]);
            
            return response()->json([
                'success' => $result->success,
                'message' => $result->message,
                'transaction_id' => $result->transactionId,
                'provider' => $this->paymentFactory->getProviderName()
            ], $result->success ? 200 : 400);
            
        } catch (\Exception $e) {
            $logger->logPaymentError($e->getMessage(), [
                'transaction_id' => $request->input('transaction_id'),
                'amount' => $request->input('amount')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verifica lo stato di un pagamento
     */
    public function getPaymentStatus(string $transactionId): JsonResponse
    {
        $gateway = $this->paymentFactory->createGateway();
        $logger = $this->paymentFactory->createLogger();
        
        try {
            $status = $gateway->getPaymentStatus($transactionId);
            
            $logger->log('info', 'Payment status checked', [
                'transaction_id' => $transactionId,
                'status' => $status->value
            ]);
            
            return response()->json([
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => $status->value,
                'provider' => $this->paymentFactory->getProviderName()
            ]);
            
        } catch (\Exception $e) {
            $logger->logPaymentError($e->getMessage(), [
                'transaction_id' => $transactionId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

