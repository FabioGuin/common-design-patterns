<?php

namespace App\Services;

interface PaymentProcessorInterface
{
    /**
     * Processa un pagamento
     *
     * @param float $amount Importo del pagamento
     * @param string $currency Valuta (default: USD)
     * @param array $metadata Metadati aggiuntivi
     * @return array Risultato del pagamento
     */
    public function processPayment(float $amount, string $currency = 'USD', array $metadata = []): array;

    /**
     * Verifica lo stato di un pagamento
     *
     * @param string $paymentId ID del pagamento
     * @return array Stato del pagamento
     */
    public function getPaymentStatus(string $paymentId): array;

    /**
     * Rimborsa un pagamento
     *
     * @param string $paymentId ID del pagamento
     * @param float|null $amount Importo del rimborso (null = rimborso completo)
     * @return array Risultato del rimborso
     */
    public function refundPayment(string $paymentId, ?float $amount = null): array;

    /**
     * Ottiene il nome del provider
     *
     * @return string Nome del provider
     */
    public function getProviderName(): string;
}
