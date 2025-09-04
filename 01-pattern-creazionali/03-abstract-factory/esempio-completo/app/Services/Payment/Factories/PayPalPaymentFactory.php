<?php

namespace App\Services\Payment\Factories;

use App\Services\Payment\Gateways\PaymentGateway;
use App\Services\Payment\Gateways\PayPalGateway;
use App\Services\Payment\Validators\PaymentValidator;
use App\Services\Payment\Validators\PayPalValidator;
use App\Services\Payment\Loggers\PaymentLogger;
use App\Services\Payment\Loggers\PayPalLogger;

class PayPalPaymentFactory implements PaymentFactory
{
    public function __construct(
        private string $clientId,
        private string $clientSecret
    ) {}
    
    public function createGateway(): PaymentGateway
    {
        return new PayPalGateway($this->clientId, $this->clientSecret);
    }
    
    public function createValidator(): PaymentValidator
    {
        return new PayPalValidator();
    }
    
    public function createLogger(): PaymentLogger
    {
        return new PayPalLogger();
    }
    
    public function getProviderName(): string
    {
        return 'paypal';
    }
}

