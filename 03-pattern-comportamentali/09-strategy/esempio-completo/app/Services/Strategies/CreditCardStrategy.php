<?php

namespace App\Services\Strategies;

class CreditCardStrategy implements StrategyInterface
{
    private string $cardNumber;
    private string $cvv;
    
    public function __construct(string $cardNumber, string $cvv)
    {
        $this->cardNumber = $cardNumber;
        $this->cvv = $cvv;
    }
    
    public function execute(array $data): mixed
    {
        $amount = $data['amount'] ?? 0;
        echo "Processing credit card payment of €{$amount}\n";
        
        // Simula validazione e pagamento
        $success = $this->validateCard() && $this->processPayment($amount);
        
        return [
            'success' => $success,
            'message' => $success ? 'Payment successful' : 'Payment failed',
            'method' => 'credit_card'
        ];
    }
    
    public function getStrategyName(): string
    {
        return 'credit_card';
    }
    
    private function validateCard(): bool
    {
        return strlen($this->cardNumber) === 16 && strlen($this->cvv) === 3;
    }
    
    private function processPayment(float $amount): bool
    {
        // Simula chiamata API
        return rand(0, 1) === 1;
    }
}
