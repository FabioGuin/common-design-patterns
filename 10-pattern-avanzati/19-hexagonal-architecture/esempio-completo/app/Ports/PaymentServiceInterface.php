<?php

namespace App\Ports;

use App\Domain\Order;

interface PaymentServiceInterface
{
    /**
     * Processa un pagamento per un ordine
     */
    public function processPayment(Order $order): array;

    /**
     * Rimborsa un pagamento
     */
    public function refundPayment(string $paymentId, float $amount = null): array;

    /**
     * Verifica lo status di un pagamento
     */
    public function getPaymentStatus(string $paymentId): array;

    /**
     * Valida i dati di pagamento
     */
    public function validatePaymentData(array $paymentData): bool;

    /**
     * Ottiene i metodi di pagamento disponibili
     */
    public function getAvailablePaymentMethods(): array;

    /**
     * Calcola le commissioni per un pagamento
     */
    public function calculateFees(float $amount): array;
}
