<?php

namespace App\Adapters;

use App\Ports\PaymentServiceInterface;
use App\Domain\Order;
use Illuminate\Support\Facades\Log;

class StripePaymentService implements PaymentServiceInterface
{
    protected $apiKey;
    protected $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('services.stripe.key', 'sk_test_...');
        $this->webhookSecret = config('services.stripe.webhook_secret', 'whsec_...');
    }

    public function processPayment(Order $order): array
    {
        try {
            // Simula chiamata API a Stripe
            $paymentData = [
                'amount' => (int)($order->getTotalAmount() * 100), // Stripe usa centesimi
                'currency' => 'eur',
                'customer_email' => $order->getCustomerEmail(),
                'description' => "Ordine #{$order->getId()}",
                'metadata' => [
                    'order_id' => $order->getId(),
                    'customer_name' => $order->getCustomerName()
                ]
            ];

            // Simula la risposta di Stripe
            $stripeResponse = $this->simulateStripePayment($paymentData);

            if ($stripeResponse['success']) {
                Log::info("Stripe Payment Service: Pagamento processato", [
                    'order_id' => $order->getId(),
                    'payment_id' => $stripeResponse['payment_id'],
                    'amount' => $order->getTotalAmount()
                ]);

                return [
                    'success' => true,
                    'payment_id' => $stripeResponse['payment_id'],
                    'amount' => $order->getTotalAmount(),
                    'currency' => 'eur',
                    'status' => 'succeeded',
                    'provider' => 'stripe'
                ];
            } else {
                Log::error("Stripe Payment Service: Pagamento fallito", [
                    'order_id' => $order->getId(),
                    'error' => $stripeResponse['error']
                ]);

                return [
                    'success' => false,
                    'error' => $stripeResponse['error'],
                    'provider' => 'stripe'
                ];
            }

        } catch (\Exception $e) {
            Log::error("Stripe Payment Service: Errore nel processing", [
                'order_id' => $order->getId(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'stripe'
            ];
        }
    }

    public function refundPayment(string $paymentId, float $amount = null): array
    {
        try {
            // Simula chiamata API a Stripe per rimborso
            $refundData = [
                'payment_id' => $paymentId,
                'amount' => $amount
            ];

            $stripeResponse = $this->simulateStripeRefund($refundData);

            if ($stripeResponse['success']) {
                Log::info("Stripe Payment Service: Rimborso processato", [
                    'payment_id' => $paymentId,
                    'refund_id' => $stripeResponse['refund_id']
                ]);

                return [
                    'success' => true,
                    'refund_id' => $stripeResponse['refund_id'],
                    'amount' => $stripeResponse['amount'],
                    'status' => 'succeeded',
                    'provider' => 'stripe'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $stripeResponse['error'],
                    'provider' => 'stripe'
                ];
            }

        } catch (\Exception $e) {
            Log::error("Stripe Payment Service: Errore nel rimborso", [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'stripe'
            ];
        }
    }

    public function getPaymentStatus(string $paymentId): array
    {
        try {
            // Simula chiamata API a Stripe per status
            $stripeResponse = $this->simulateStripeStatusCheck($paymentId);

            return [
                'success' => true,
                'payment_id' => $paymentId,
                'status' => $stripeResponse['status'],
                'amount' => $stripeResponse['amount'],
                'currency' => $stripeResponse['currency'],
                'provider' => 'stripe'
            ];

        } catch (\Exception $e) {
            Log::error("Stripe Payment Service: Errore nel controllo status", [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'stripe'
            ];
        }
    }

    public function validatePaymentData(array $paymentData): bool
    {
        try {
            $required = ['amount', 'currency', 'customer_email'];
            
            foreach ($required as $field) {
                if (!isset($paymentData[$field]) || empty($paymentData[$field])) {
                    return false;
                }
            }

            // Valida email
            if (!filter_var($paymentData['customer_email'], FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            // Valida amount
            if (!is_numeric($paymentData['amount']) || $paymentData['amount'] <= 0) {
                return false;
            }

            // Valida currency
            $validCurrencies = ['eur', 'usd', 'gbp'];
            if (!in_array($paymentData['currency'], $validCurrencies)) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Stripe Payment Service: Errore nella validazione", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getAvailablePaymentMethods(): array
    {
        return [
            'card' => 'Carta di credito/debito',
            'sepa_debit' => 'Addebito SEPA',
            'ideal' => 'iDEAL',
            'bancontact' => 'Bancontact',
            'sofort' => 'SOFORT'
        ];
    }

    public function calculateFees(float $amount): array
    {
        // Stripe: 1.4% + 0.25€ per transazioni europee
        $percentageFee = $amount * 0.014;
        $fixedFee = 0.25;
        $totalFee = $percentageFee + $fixedFee;

        return [
            'percentage_fee' => $percentageFee,
            'fixed_fee' => $fixedFee,
            'total_fee' => $totalFee,
            'net_amount' => $amount - $totalFee,
            'provider' => 'stripe'
        ];
    }

    /**
     * Simula una chiamata API a Stripe per il pagamento
     */
    private function simulateStripePayment(array $paymentData): array
    {
        // Simula latenza di rete
        usleep(100000); // 100ms

        // Simula successo/failure basato su amount
        if ($paymentData['amount'] > 0 && $paymentData['amount'] < 100000) { // Max 1000€
            return [
                'success' => true,
                'payment_id' => 'pi_' . uniqid(),
                'status' => 'succeeded'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Amount not valid or too high'
            ];
        }
    }

    /**
     * Simula una chiamata API a Stripe per il rimborso
     */
    private function simulateStripeRefund(array $refundData): array
    {
        // Simula latenza di rete
        usleep(150000); // 150ms

        return [
            'success' => true,
            'refund_id' => 're_' . uniqid(),
            'amount' => $refundData['amount'] ?? 0
        ];
    }

    /**
     * Simula una chiamata API a Stripe per il controllo status
     */
    private function simulateStripeStatusCheck(string $paymentId): array
    {
        // Simula latenza di rete
        usleep(50000); // 50ms

        return [
            'status' => 'succeeded',
            'amount' => 10000, // 100€ in centesimi
            'currency' => 'eur'
        ];
    }
}
