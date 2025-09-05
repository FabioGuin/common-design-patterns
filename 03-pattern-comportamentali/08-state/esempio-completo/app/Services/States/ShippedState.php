<?php

namespace App\Services\States;

class ShippedState implements StateInterface
{
    public function handle(object $context): void
    {
        echo "Order is shipped. Tracking available.\n";
    }
    
    public function canTransitionTo(string $state): bool
    {
        return in_array($state, ['delivered', 'returned']);
    }
    
    public function getStateName(): string
    {
        return 'shipped';
    }
}
