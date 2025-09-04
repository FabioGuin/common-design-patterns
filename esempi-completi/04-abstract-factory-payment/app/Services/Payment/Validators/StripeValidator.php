<?php

namespace App\Services\Payment\Validators;

use App\Services\Payment\ValidationResult;

class StripeValidator implements PaymentValidator
{
    public function validate(array $data): ValidationResult
    {
        $errors = [];
        
        // Validazioni specifiche per Stripe
        if (!isset($data['card_token']) || empty($data['card_token'])) {
            $errors[] = 'Stripe card token is required';
        }
        
        if (!isset($data['currency']) || !in_array($data['currency'], ['USD', 'EUR', 'GBP'])) {
            $errors[] = 'Currency must be USD, EUR, or GBP for Stripe';
        }
        
        if (isset($data['amount']) && $data['amount'] < 0.50) {
            $errors[] = 'Minimum amount for Stripe is $0.50';
        }
        
        return empty($errors) ? ValidationResult::valid() : ValidationResult::invalid($errors);
    }
    
    public function validateAmount(float $amount): bool
    {
        return $amount >= 0.50 && $amount <= 999999.99;
    }
    
    public function validateCustomerData(array $customerData): bool
    {
        $required = ['email', 'name'];
        
        foreach ($required as $field) {
            if (!isset($customerData[$field]) || empty($customerData[$field])) {
                return false;
            }
        }
        
        return filter_var($customerData['email'], FILTER_VALIDATE_EMAIL) !== false;
    }
}

