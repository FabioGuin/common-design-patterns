<?php

namespace App\Services;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeAdapter implements PaymentProcessorInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Processa un pagamento tramite Stripe
     */
    public function processPayment(float $amount, string $currency = 'USD', array $metadata = []): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $this->convertToCents($amount),
                'currency' => strtolower($currency),
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'success' => true,
                'payment_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $amount,
                'currency' => $currency,
                'provider' => $this->getProviderName(),
                'client_secret' => $paymentIntent->client_secret,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->getProviderName(),
            ];
        }
    }

    /**
     * Verifica lo stato di un pagamento Stripe
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentId);

            return [
                'success' => true,
                'payment_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $this->convertFromCents($paymentIntent->amount),
                'currency' => strtoupper($paymentIntent->currency),
                'provider' => $this->getProviderName(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->getProviderName(),
            ];
        }
    }

    /**
     * Rimborsa un pagamento Stripe
     */
    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        try {
            $refundData = ['payment_intent' => $paymentId];
            
            if ($amount !== null) {
                $refundData['amount'] = $this->convertToCents($amount);
            }

            $refund = $this->stripe->refunds->create($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $this->convertFromCents($refund->amount),
                'provider' => $this->getProviderName(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->getProviderName(),
            ];
        }
    }

    /**
     * Ottiene il nome del provider
     */
    public function getProviderName(): string
    {
        return 'Stripe';
    }

    /**
     * Converte dollari in centesimi per Stripe
     */
    private function convertToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Converte centesimi in dollari da Stripe
     */
    private function convertFromCents(int $amount): float
    {
        return $amount / 100;
    }
}
