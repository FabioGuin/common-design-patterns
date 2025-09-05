<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PaymentService
{
    protected $serviceId = 'payment-service';
    protected $version = '1.0.0';

    /**
     * Processa un pagamento
     */
    public function processPayment(array $paymentData): array
    {
        try {
            // Valida i dati del pagamento
            $this->validatePaymentData($paymentData);

            // Simula chiamata a gateway di pagamento
            $gatewayResult = $this->callPaymentGateway($paymentData);

            if ($gatewayResult['success']) {
                // Crea il record di pagamento
                $payment = new Payment([
                    'order_id' => $paymentData['order_id'],
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency'] ?? 'EUR',
                    'payment_method' => $paymentData['payment_method'] ?? 'card',
                    'status' => 'completed',
                    'gateway_transaction_id' => $gatewayResult['transaction_id'],
                    'gateway_response' => $gatewayResult['response']
                ]);

                $payment->save();

                // Cache del pagamento
                Cache::put("payment:{$payment->id}", $payment, 3600);

                Log::info("Payment Service: Pagamento processato", [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'amount' => $payment->amount,
                    'service' => $this->serviceId
                ]);

                return [
                    'success' => true,
                    'data' => $payment->toArray(),
                    'service' => $this->serviceId
                ];
            } else {
                // Crea record di pagamento fallito
                $payment = new Payment([
                    'order_id' => $paymentData['order_id'],
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency'] ?? 'EUR',
                    'payment_method' => $paymentData['payment_method'] ?? 'card',
                    'status' => 'failed',
                    'gateway_response' => $gatewayResult['error']
                ]);

                $payment->save();

                return [
                    'success' => false,
                    'error' => $gatewayResult['error'],
                    'data' => $payment->toArray(),
                    'service' => $this->serviceId
                ];
            }

        } catch (\Exception $e) {
            Log::error("Payment Service: Errore nel processing pagamento", [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene un pagamento per ID
     */
    public function getPayment(string $paymentId): array
    {
        try {
            // Prova prima la cache
            $cachedPayment = Cache::get("payment:{$paymentId}");
            if ($cachedPayment) {
                return [
                    'success' => true,
                    'data' => $cachedPayment->toArray(),
                    'service' => $this->serviceId,
                    'cached' => true
                ];
            }

            // Recupera dal database
            $payment = Payment::find($paymentId);
            if (!$payment) {
                return [
                    'success' => false,
                    'error' => 'Pagamento non trovato',
                    'service' => $this->serviceId
                ];
            }

            // Cache del pagamento
            Cache::put("payment:{$paymentId}", $payment, 3600);

            return [
                'success' => true,
                'data' => $payment->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Payment Service: Errore nel recupero pagamento", [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene pagamenti per ordine
     */
    public function getPaymentsByOrder(string $orderId): array
    {
        try {
            $payments = Payment::where('order_id', $orderId)->get();
            $paymentsArray = $payments->map(function($payment) {
                return $payment->toArray();
            })->toArray();

            return [
                'success' => true,
                'data' => $paymentsArray,
                'count' => count($paymentsArray),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Payment Service: Errore nel recupero pagamenti per ordine", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Rimborsa un pagamento
     */
    public function refundPayment(string $paymentId, float $amount = null): array
    {
        try {
            $payment = Payment::find($paymentId);
            if (!$payment) {
                return [
                    'success' => false,
                    'error' => 'Pagamento non trovato',
                    'service' => $this->serviceId
                ];
            }

            if ($payment->status !== 'completed') {
                return [
                    'success' => false,
                    'error' => 'Impossibile rimborsare pagamento con status: ' . $payment->status,
                    'service' => $this->serviceId
                ];
            }

            $refundAmount = $amount ?? $payment->amount;

            // Simula chiamata a gateway per rimborso
            $gatewayResult = $this->callRefundGateway($payment->gateway_transaction_id, $refundAmount);

            if ($gatewayResult['success']) {
                // Crea record di rimborso
                $refund = new Payment([
                    'order_id' => $payment->order_id,
                    'amount' => -$refundAmount, // Negativo per rimborso
                    'currency' => $payment->currency,
                    'payment_method' => $payment->payment_method,
                    'status' => 'refunded',
                    'gateway_transaction_id' => $gatewayResult['refund_id'],
                    'gateway_response' => $gatewayResult['response']
                ]);

                $refund->save();

                // Aggiorna status del pagamento originale
                $payment->status = 'refunded';
                $payment->save();

                // Aggiorna la cache
                Cache::put("payment:{$paymentId}", $payment, 3600);

                Log::info("Payment Service: Rimborso processato", [
                    'payment_id' => $paymentId,
                    'refund_amount' => $refundAmount,
                    'service' => $this->serviceId
                ]);

                return [
                    'success' => true,
                    'data' => $refund->toArray(),
                    'service' => $this->serviceId
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Rimborso fallito: ' . $gatewayResult['error'],
                    'service' => $this->serviceId
                ];
            }

        } catch (\Exception $e) {
            Log::error("Payment Service: Errore nel rimborso", [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Verifica lo status di un pagamento
     */
    public function checkPaymentStatus(string $paymentId): array
    {
        try {
            $payment = Payment::find($paymentId);
            if (!$payment) {
                return [
                    'success' => false,
                    'error' => 'Pagamento non trovato',
                    'service' => $this->serviceId
                ];
            }

            // Simula chiamata a gateway per verificare status
            $gatewayResult = $this->callStatusCheckGateway($payment->gateway_transaction_id);

            if ($gatewayResult['success']) {
                // Aggiorna status se necessario
                if ($gatewayResult['status'] !== $payment->status) {
                    $payment->status = $gatewayResult['status'];
                    $payment->save();
                    Cache::put("payment:{$paymentId}", $payment, 3600);
                }

                return [
                    'success' => true,
                    'data' => $payment->toArray(),
                    'service' => $this->serviceId
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Errore nella verifica status: ' . $gatewayResult['error'],
                    'service' => $this->serviceId
                ];
            }

        } catch (\Exception $e) {
            Log::error("Payment Service: Errore nella verifica status", [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Lista tutti i pagamenti
     */
    public function listPayments(int $limit = 100, int $offset = 0, array $filters = []): array
    {
        try {
            $query = Payment::query();

            // Applica filtri
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['payment_method'])) {
                $query->where('payment_method', $filters['payment_method']);
            }

            if (isset($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }

            $payments = $query->limit($limit)->offset($offset)->get();
            $paymentsArray = $payments->map(function($payment) {
                return $payment->toArray();
            })->toArray();

            return [
                'success' => true,
                'data' => $paymentsArray,
                'count' => count($paymentsArray),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Payment Service: Errore nel recupero lista pagamenti", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene statistiche dei pagamenti
     */
    public function getPaymentStats(): array
    {
        try {
            $totalPayments = Payment::count();
            $completedPayments = Payment::where('status', 'completed')->count();
            $failedPayments = Payment::where('status', 'failed')->count();
            $refundedPayments = Payment::where('status', 'refunded')->count();
            $totalAmount = Payment::where('status', 'completed')->sum('amount');
            $refundedAmount = Payment::where('status', 'refunded')->sum('amount');

            return [
                'success' => true,
                'data' => [
                    'total_payments' => $totalPayments,
                    'completed_payments' => $completedPayments,
                    'failed_payments' => $failedPayments,
                    'refunded_payments' => $refundedPayments,
                    'total_amount' => $totalAmount,
                    'refunded_amount' => abs($refundedAmount),
                    'net_amount' => $totalAmount + $refundedAmount
                ],
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Payment Service: Errore nel recupero statistiche", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del servizio
     */
    public function healthCheck(): array
    {
        try {
            // Verifica connessione database
            Payment::count();

            return [
                'success' => true,
                'status' => 'healthy',
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Valida i dati del pagamento
     */
    private function validatePaymentData(array $paymentData): void
    {
        $required = ['order_id', 'amount'];
        
        foreach ($required as $field) {
            if (!isset($paymentData[$field]) || empty($paymentData[$field])) {
                throw new \InvalidArgumentException("Campo obbligatorio mancante: {$field}");
            }
        }

        // Valida amount
        if (!is_numeric($paymentData['amount']) || $paymentData['amount'] <= 0) {
            throw new \InvalidArgumentException("Amount non valido");
        }

        // Valida currency
        $validCurrencies = ['EUR', 'USD', 'GBP'];
        if (isset($paymentData['currency']) && !in_array($paymentData['currency'], $validCurrencies)) {
            throw new \InvalidArgumentException("Currency non valida");
        }
    }

    /**
     * Simula chiamata a gateway di pagamento
     */
    private function callPaymentGateway(array $paymentData): array
    {
        // Simula latenza di rete
        usleep(200000); // 200ms

        // Simula successo/failure basato su amount
        if ($paymentData['amount'] > 0 && $paymentData['amount'] < 1000) {
            return [
                'success' => true,
                'transaction_id' => 'txn_' . uniqid(),
                'response' => ['status' => 'approved', 'code' => '00']
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Amount not valid or too high'
            ];
        }
    }

    /**
     * Simula chiamata a gateway per rimborso
     */
    private function callRefundGateway(string $transactionId, float $amount): array
    {
        // Simula latenza di rete
        usleep(300000); // 300ms

        return [
            'success' => true,
            'refund_id' => 'ref_' . uniqid(),
            'response' => ['status' => 'refunded', 'amount' => $amount]
        ];
    }

    /**
     * Simula chiamata a gateway per verifica status
     */
    private function callStatusCheckGateway(string $transactionId): array
    {
        // Simula latenza di rete
        usleep(100000); // 100ms

        return [
            'success' => true,
            'status' => 'completed'
        ];
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del servizio
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
