<?php

namespace App\Services\Strategies;

class BankTransferStrategy implements StrategyInterface
{
    private string $iban;
    private string $bic;
    
    public function __construct(string $iban, string $bic)
    {
        $this->iban = $iban;
        $this->bic = $bic;
    }
    
    public function execute(array $data): mixed
    {
        $amount = $data['amount'] ?? 0;
        echo "Processing bank transfer of €{$amount}\n";
        
        // Simula validazione e pagamento
        $success = $this->validateBankDetails() && $this->processTransfer($amount);
        
        return [
            'success' => $success,
            'message' => $success ? 'Bank transfer successful' : 'Bank transfer failed',
            'method' => 'bank_transfer'
        ];
    }
    
    public function getStrategyName(): string
    {
        return 'bank_transfer';
    }
    
    private function validateBankDetails(): bool
    {
        return strlen($this->iban) >= 20 && strlen($this->bic) >= 8;
    }
    
    private function processTransfer(float $amount): bool
    {
        // Simula chiamata API bancaria
        return rand(0, 1) === 1;
    }
}
