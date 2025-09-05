<?php

namespace App\Services\OrderApproval;

class OrderRequest
{
    public function __construct(
        public int $orderId,
        public string $customerName,
        public string $customerEmail,
        public float $totalAmount,
        public array $items,
        public string $customerId,
        public float $customerCredit = 0.0,
        public array $metadata = []
    ) {}
    
    /**
     * Verifica se l'ordine supera una certa soglia
     */
    public function exceedsThreshold(float $threshold): bool
    {
        return $this->totalAmount > $threshold;
    }
    
    /**
     * Verifica se il cliente ha credito sufficiente
     */
    public function hasSufficientCredit(): bool
    {
        return $this->customerCredit >= $this->totalAmount;
    }
    
    /**
     * Verifica se tutti gli item sono disponibili
     */
    public function allItemsAvailable(): bool
    {
        foreach ($this->items as $item) {
            if (!isset($item['available']) || !$item['available']) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Aggiunge metadati all'ordine
     */
    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
    
    /**
     * Ottiene metadati dall'ordine
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }
}
