<?php

namespace App\Throttling\Exceptions;

class ThrottlingException extends \Exception
{
    public function __construct(string $message = "Rate limit exceeded", int $code = 429, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
