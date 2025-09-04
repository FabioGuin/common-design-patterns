<?php

namespace App\Services\Payment\Validators;

interface PaymentValidator
{
    /**
     * Valida i dati del pagamento
     */
    public function validate(array $data): ValidationResult;
    
    /**
     * Valida l'importo del pagamento
     */
    public function validateAmount(float $amount): bool;
    
    /**
     * Valida i dati del cliente
     */
    public function validateCustomerData(array $customerData): bool;
}

