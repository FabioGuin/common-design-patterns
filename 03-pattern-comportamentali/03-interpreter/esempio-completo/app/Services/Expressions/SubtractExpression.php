<?php

namespace App\Services\Expressions;

class SubtractExpression implements ExpressionInterface
{
    private ExpressionInterface $left;
    private ExpressionInterface $right;
    
    public function __construct(ExpressionInterface $left, ExpressionInterface $right)
    {
        $this->left = $left;
        $this->right = $right;
    }
    
    public function interpret(array $context): int
    {
        return $this->left->interpret($context) - $this->right->interpret($context);
    }
}
