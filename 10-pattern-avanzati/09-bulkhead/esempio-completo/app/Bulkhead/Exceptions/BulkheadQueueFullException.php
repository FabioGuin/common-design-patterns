<?php

namespace App\Bulkhead\Exceptions;

class BulkheadQueueFullException extends \Exception
{
    public function __construct(string $message = "Bulkhead queue is full", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
