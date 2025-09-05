<?php

namespace App\Services\Expressions;

class NumberExpression implements ExpressionInterface
{
    private int $value;
    
    public function __construct(int $value)
    {
        $this->value = $value;
    }
    
    public function interpret(array $context): int
    {
        return $this->value;
    }
}
