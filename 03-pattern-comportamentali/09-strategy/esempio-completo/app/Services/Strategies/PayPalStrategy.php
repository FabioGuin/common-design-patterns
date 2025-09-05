<?php

namespace App\Services\Strategies;

class PayPalStrategy implements StrategyInterface
{
    private string $email;
    private string $password;
    
    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
    
    public function execute(array $data): mixed
    {
        $amount = $data['amount'] ?? 0;
        echo "Processing PayPal payment of €{$amount}\n";
        
        // Simula validazione e pagamento
        $success = $this->validateCredentials() && $this->processPayment($amount);
        
        return [
            'success' => $success,
            'message' => $success ? 'PayPal payment successful' : 'PayPal payment failed',
            'method' => 'paypal'
        ];
    }
    
    public function getStrategyName(): string
    {
        return 'paypal';
    }
    
    private function validateCredentials(): bool
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) && !empty($this->password);
    }
    
    private function processPayment(float $amount): bool
    {
        // Simula chiamata API PayPal
        return rand(0, 1) === 1;
    }
}
