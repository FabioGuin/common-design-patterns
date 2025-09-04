<?php

namespace App\Services\Payment\Loggers;

interface PaymentLogger
{
    /**
     * Logga un evento di pagamento
     */
    public function log(string $level, string $message, array $context = []): void;
    
    /**
     * Logga l'inizio di un pagamento
     */
    public function logPaymentStart(array $data): void;
    
    /**
     * Logga il completamento di un pagamento
     */
    public function logPaymentComplete(string $transactionId, bool $success): void;
    
    /**
     * Logga un errore di pagamento
     */
    public function logPaymentError(string $error, array $context = []): void;
}

