<?php

namespace App\Services\States;

class ConfirmedState implements StateInterface
{
    public function handle(object $context): void
    {
        echo "Order is confirmed. Processing payment.\n";
    }
    
    public function canTransitionTo(string $state): bool
    {
        return in_array($state, ['shipped', 'cancelled']);
    }
    
    public function getStateName(): string
    {
        return 'confirmed';
    }
}
