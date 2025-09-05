<?php

namespace App\Services\Expressions;

class VariableExpression implements ExpressionInterface
{
    private string $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public function interpret(array $context): int
    {
        return $context[$this->name] ?? 0;
    }
}
