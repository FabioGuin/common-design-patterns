<?php

namespace App\Services\Expressions;

interface ExpressionInterface
{
    public function interpret(array $context): mixed;
}
