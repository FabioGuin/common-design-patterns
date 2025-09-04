<?php

namespace App\Services\Payment\Gateways;

interface PaymentGateway
{
    /**
     * Processa un pagamento
     */
    public function processPayment(float $amount, array $data): PaymentResult;
    
    /**
     * Rimborsa un pagamento
     */
    public function refundPayment(string $transactionId, float $amount): PaymentResult;
    
    /**
     * Verifica lo stato di un pagamento
     */
    public function getPaymentStatus(string $transactionId): PaymentStatus;
}

