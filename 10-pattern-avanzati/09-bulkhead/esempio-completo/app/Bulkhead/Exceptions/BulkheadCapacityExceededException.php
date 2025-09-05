<?php

namespace App\Bulkhead\Exceptions;

class BulkheadCapacityExceededException extends \Exception
{
    public function __construct(string $message = "Bulkhead capacity exceeded", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
