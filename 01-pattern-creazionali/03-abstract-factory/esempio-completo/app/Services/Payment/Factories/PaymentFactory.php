<?php

namespace App\Services\Payment\Factories;

use App\Services\Payment\Gateways\PaymentGateway;
use App\Services\Payment\Validators\PaymentValidator;
use App\Services\Payment\Loggers\PaymentLogger;

interface PaymentFactory
{
    /**
     * Crea un gateway di pagamento
     */
    public function createGateway(): PaymentGateway;
    
    /**
     * Crea un validatore di pagamento
     */
    public function createValidator(): PaymentValidator;
    
    /**
     * Crea un logger di pagamento
     */
    public function createLogger(): PaymentLogger;
    
    /**
     * Restituisce il nome del provider
     */
    public function getProviderName(): string;
}

