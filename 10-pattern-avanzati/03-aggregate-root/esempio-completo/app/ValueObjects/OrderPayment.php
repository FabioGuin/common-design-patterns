<?php

namespace App\ValueObjects;

/**
 * Value Object per pagamenti degli ordini
 * 
 * Immutabile, validato e type-safe per informazioni
 * di pagamento.
 */
class OrderPayment
{
    private readonly string $method;
    private readonly string $status;
    private readonly ?string $transactionId;
    private readonly ?string $cardLastFour;
    private readonly ?string $cardBrand;

    private const SUPPORTED_METHODS = ['CREDIT_CARD', 'PAYPAL', 'BANK_TRANSFER', 'CASH'];
    private const SUPPORTED_STATUSES = ['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'REFUNDED'];

    public function __construct(
        string $method,
        string $status = 'PENDING',
        ?string $transactionId = null,
        ?string $cardLastFour = null,
        ?string $cardBrand = null
    ) {
        $this->validateInput($method, $status, $transactionId, $cardLastFour, $cardBrand);

        $this->method = $method;
        $this->status = $status;
        $this->transactionId = $transactionId;
        $this->cardLastFour = $cardLastFour;
        $this->cardBrand = $cardBrand;
    }

    /**
     * Restituisce il metodo di pagamento
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Restituisce lo status del pagamento
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Restituisce l'ID della transazione
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * Restituisce le ultime 4 cifre della carta
     */
    public function getCardLastFour(): ?string
    {
        return $this->cardLastFour;
    }

    /**
     * Restituisce il brand della carta
     */
    public function getCardBrand(): ?string
    {
        return $this->cardBrand;
    }

    /**
     * Verifica se il pagamento è completato
     */
    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    /**
     * Verifica se il pagamento è fallito
     */
    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    /**
     * Verifica se il pagamento è in corso
     */
    public function isProcessing(): bool
    {
        return $this->status === 'PROCESSING';
    }

    /**
     * Verifica se il pagamento è in attesa
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Restituisce una descrizione del metodo di pagamento
     */
    public function getMethodDescription(): string
    {
        return match($this->method) {
            'CREDIT_CARD' => 'Credit Card',
            'PAYPAL' => 'PayPal',
            'BANK_TRANSFER' => 'Bank Transfer',
            'CASH' => 'Cash on Delivery',
            default => $this->method
        };
    }

    /**
     * Restituisce una descrizione dello status
     */
    public function getStatusDescription(): string
    {
        return match($this->status) {
            'PENDING' => 'Pending',
            'PROCESSING' => 'Processing',
            'COMPLETED' => 'Completed',
            'FAILED' => 'Failed',
            'REFUNDED' => 'Refunded',
            default => $this->status
        };
    }

    /**
     * Restituisce le informazioni della carta mascherate
     */
    public function getMaskedCardInfo(): ?string
    {
        if (!$this->cardLastFour || !$this->cardBrand) {
            return null;
        }

        return $this->cardBrand . ' **** **** **** ' . $this->cardLastFour;
    }

    /**
     * Confronta due pagamenti
     */
    public function equals(OrderPayment $other): bool
    {
        return $this->method === $other->method &&
               $this->status === $other->status &&
               $this->transactionId === $other->transactionId &&
               $this->cardLastFour === $other->cardLastFour &&
               $this->cardBrand === $other->cardBrand;
    }

    /**
     * Restituisce una rappresentazione array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'methodDescription' => $this->getMethodDescription(),
            'status' => $this->status,
            'statusDescription' => $this->getStatusDescription(),
            'transactionId' => $this->transactionId,
            'cardLastFour' => $this->cardLastFour,
            'cardBrand' => $this->cardBrand,
            'maskedCardInfo' => $this->getMaskedCardInfo(),
            'isCompleted' => $this->isCompleted(),
            'isFailed' => $this->isFailed(),
            'isProcessing' => $this->isProcessing(),
            'isPending' => $this->isPending()
        ];
    }

    /**
     * Valida l'input
     */
    private function validateInput(
        string $method,
        string $status,
        ?string $transactionId,
        ?string $cardLastFour,
        ?string $cardBrand
    ): void {
        if (!in_array($method, self::SUPPORTED_METHODS)) {
            throw new \InvalidArgumentException("Unsupported payment method: {$method}");
        }

        if (!in_array($status, self::SUPPORTED_STATUSES)) {
            throw new \InvalidArgumentException("Unsupported payment status: {$status}");
        }

        if ($method === 'CREDIT_CARD' && (!$cardLastFour || !$cardBrand)) {
            throw new \InvalidArgumentException('Card information is required for credit card payments');
        }

        if ($cardLastFour && !preg_match('/^\d{4}$/', $cardLastFour)) {
            throw new \InvalidArgumentException('Card last four digits must be exactly 4 digits');
        }

        if ($transactionId && strlen($transactionId) > 100) {
            throw new \InvalidArgumentException('Transaction ID is too long');
        }
    }
}
