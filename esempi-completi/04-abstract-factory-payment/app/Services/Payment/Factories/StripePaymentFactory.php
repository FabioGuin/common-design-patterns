<?php

namespace App\Services\Payment\Factories;

use App\Services\Payment\Gateways\PaymentGateway;
use App\Services\Payment\Gateways\StripeGateway;
use App\Services\Payment\Validators\PaymentValidator;
use App\Services\Payment\Validators\StripeValidator;
use App\Services\Payment\Loggers\PaymentLogger;
use App\Services\Payment\Loggers\StripeLogger;

class StripePaymentFactory implements PaymentFactory
{
    public function __construct(
        private string $apiKey,
        private string $webhookSecret
    ) {}
    
    public function createGateway(): PaymentGateway
    {
        return new StripeGateway($this->apiKey, $this->webhookSecret);
    }
    
    public function createValidator(): PaymentValidator
    {
        return new StripeValidator();
    }
    
    public function createLogger(): PaymentLogger
    {
        return new StripeLogger();
    }
    
    public function getProviderName(): string
    {
        return 'stripe';
    }
}

