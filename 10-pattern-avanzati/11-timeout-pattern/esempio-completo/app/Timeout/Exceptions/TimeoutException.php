<?php

namespace App\Timeout\Exceptions;

class TimeoutException extends \Exception
{
    public function __construct(string $message = "Operation timed out", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
