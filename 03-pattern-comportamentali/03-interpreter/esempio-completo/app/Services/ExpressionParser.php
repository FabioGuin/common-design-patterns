<?php

namespace App\Services;

use App\Services\Expressions\ExpressionInterface;
use App\Services\Expressions\NumberExpression;
use App\Services\Expressions\AddExpression;
use App\Services\Expressions\SubtractExpression;
use App\Services\Expressions\VariableExpression;

class ExpressionParser
{
    public function parse(string $expression): ExpressionInterface
    {
        $tokens = $this->tokenize($expression);
        return $this->parseExpression($tokens);
    }
    
    private function tokenize(string $expression): array
    {
        $tokens = [];
        $expression = str_replace(' ', '', $expression);
        
        for ($i = 0; $i < strlen($expression); $i++) {
            $char = $expression[$i];
            
            if (is_numeric($char)) {
                $number = '';
                while ($i < strlen($expression) && is_numeric($expression[$i])) {
                    $number .= $expression[$i];
                    $i++;
                }
                $i--; // Backtrack
                $tokens[] = ['type' => 'number', 'value' => (int)$number];
            } elseif (ctype_alpha($char)) {
                $variable = '';
                while ($i < strlen($expression) && ctype_alnum($expression[$i])) {
                    $variable .= $expression[$i];
                    $i++;
                }
                $i--; // Backtrack
                $tokens[] = ['type' => 'variable', 'value' => $variable];
            } elseif (in_array($char, ['+', '-', '(', ')'])) {
                $tokens[] = ['type' => 'operator', 'value' => $char];
            }
        }
        
        return $tokens;
    }
    
    private function parseExpression(array $tokens): ExpressionInterface
    {
        $index = 0;
        return $this->parseAdditiveExpression($tokens, $index);
    }
    
    private function parseAdditiveExpression(array $tokens, int &$index): ExpressionInterface
    {
        $left = $this->parseMultiplicativeExpression($tokens, $index);
        
        while ($index < count($tokens) && in_array($tokens[$index]['value'], ['+', '-'])) {
            $operator = $tokens[$index]['value'];
            $index++;
            $right = $this->parseMultiplicativeExpression($tokens, $index);
            
            if ($operator === '+') {
                $left = new AddExpression($left, $right);
            } else {
                $left = new SubtractExpression($left, $right);
            }
        }
        
        return $left;
    }
    
    private function parseMultiplicativeExpression(array $tokens, int &$index): ExpressionInterface
    {
        return $this->parsePrimaryExpression($tokens, $index);
    }
    
    private function parsePrimaryExpression(array $tokens, int &$index): ExpressionInterface
    {
        if ($index >= count($tokens)) {
            throw new \InvalidArgumentException('Unexpected end of expression');
        }
        
        $token = $tokens[$index];
        
        if ($token['type'] === 'number') {
            $index++;
            return new NumberExpression($token['value']);
        }
        
        if ($token['type'] === 'variable') {
            $index++;
            return new VariableExpression($token['value']);
        }
        
        if ($token['value'] === '(') {
            $index++; // Skip '('
            $expression = $this->parseAdditiveExpression($tokens, $index);
            
            if ($index >= count($tokens) || $tokens[$index]['value'] !== ')') {
                throw new \InvalidArgumentException('Missing closing parenthesis');
            }
            
            $index++; // Skip ')'
            return $expression;
        }
        
        throw new \InvalidArgumentException('Invalid token: ' . $token['value']);
    }
}
