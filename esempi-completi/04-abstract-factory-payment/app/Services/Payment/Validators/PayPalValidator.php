<?php

namespace App\Services\Payment\Validators;

use App\Services\Payment\ValidationResult;

class PayPalValidator implements PaymentValidator
{
    public function validate(array $data): ValidationResult
    {
        $errors = [];
        
        // Validazioni specifiche per PayPal
        if (!isset($data['paypal_order_id']) || empty($data['paypal_order_id'])) {
            $errors[] = 'PayPal order ID is required';
        }
        
        if (!isset($data['currency']) || !in_array($data['currency'], ['USD', 'EUR', 'CAD', 'AUD'])) {
            $errors[] = 'Currency must be USD, EUR, CAD, or AUD for PayPal';
        }
        
        if (isset($data['amount']) && $data['amount'] < 1.00) {
            $errors[] = 'Minimum amount for PayPal is $1.00';
        }
        
        return empty($errors) ? ValidationResult::valid() : ValidationResult::invalid($errors);
    }
    
    public function validateAmount(float $amount): bool
    {
        return $amount >= 1.00 && $amount <= 10000.00;
    }
    
    public function validateCustomerData(array $customerData): bool
    {
        $required = ['email'];
        
        foreach ($required as $field) {
            if (!isset($customerData[$field]) || empty($customerData[$field])) {
                return false;
            }
        }
        
        return filter_var($customerData['email'], FILTER_VALIDATE_EMAIL) !== false;
    }
}

