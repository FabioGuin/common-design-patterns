<?php

namespace App\CircuitBreaker\Exceptions;

class CircuitBreakerOpenException extends \Exception
{
    public function __construct(string $message = "Circuit breaker is OPEN", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
