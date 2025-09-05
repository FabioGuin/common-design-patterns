<?php

namespace App\Services;

class PaymentService
{
    private array $payments = [];
    private float $taxRate;

    public function __construct()
    {
        $this->taxRate = config('ecommerce.tax_rate', 0.22);
    }

    /**
     * Processa un pagamento
     */
    public function processPayment(array $paymentData): array
    {
        \Log::info('Processing payment', $paymentData);

        $paymentId = 'PAY_' . uniqid();
        $amount = $paymentData['amount'];
        $tax = $amount * $this->taxRate;
        $total = $amount + $tax;

        // Simula la validazione della carta
        if (!$this->validateCard($paymentData['card_number'], $paymentData['cvv'])) {
            return [
                'success' => false,
                'payment_id' => null,
                'message' => 'Invalid card details',
                'amount' => $amount,
                'tax' => $tax,
                'total' => $total,
            ];
        }

        // Simula l'elaborazione del pagamento
        $this->payments[$paymentId] = [
            'id' => $paymentId,
            'amount' => $amount,
            'tax' => $tax,
            'total' => $total,
            'card_number' => $this->maskCardNumber($paymentData['card_number']),
            'status' => 'completed',
            'timestamp' => now()->toISOString(),
        ];

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'message' => 'Payment processed successfully',
            'amount' => $amount,
            'tax' => $tax,
            'total' => $total,
            'card_number' => $this->maskCardNumber($paymentData['card_number']),
        ];
    }

    /**
     * Rimborsa un pagamento
     */
    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        \Log::info('Processing refund', ['payment_id' => $paymentId, 'amount' => $amount]);

        if (!isset($this->payments[$paymentId])) {
            return [
                'success' => false,
                'message' => 'Payment not found',
            ];
        }

        $payment = $this->payments[$paymentId];
        $refundAmount = $amount ?? $payment['total'];

        if ($refundAmount > $payment['total']) {
            return [
                'success' => false,
                'message' => 'Refund amount exceeds payment total',
                'max_refund' => $payment['total'],
            ];
        }

        $this->payments[$paymentId]['refunded'] = $refundAmount;
        $this->payments[$paymentId]['status'] = 'refunded';

        return [
            'success' => true,
            'refund_id' => 'REF_' . uniqid(),
            'message' => 'Refund processed successfully',
            'refund_amount' => $refundAmount,
            'remaining_balance' => $payment['total'] - $refundAmount,
        ];
    }

    /**
     * Ottiene i dettagli di un pagamento
     */
    public function getPayment(string $paymentId): ?array
    {
        return $this->payments[$paymentId] ?? null;
    }

    /**
     * Ottiene tutti i pagamenti
     */
    public function getAllPayments(): array
    {
        return $this->payments;
    }

    /**
     * Calcola le tasse per un importo
     */
    public function calculateTax(float $amount): array
    {
        $tax = $amount * $this->taxRate;
        $total = $amount + $tax;

        return [
            'subtotal' => $amount,
            'tax_rate' => $this->taxRate,
            'tax_amount' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Valida i dettagli della carta
     */
    private function validateCard(string $cardNumber, string $cvv): bool
    {
        // Simula la validazione della carta
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        $cvv = preg_replace('/\D/', '', $cvv);

        return strlen($cardNumber) >= 13 && strlen($cvv) >= 3;
    }

    /**
     * Maschera il numero della carta
     */
    private function maskCardNumber(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        $length = strlen($cardNumber);
        
        if ($length < 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($cardNumber, -4);
    }
}
