<?php

namespace App\Services\States;

class PendingState implements StateInterface
{
    public function handle(object $context): void
    {
        echo "Order is pending. Waiting for confirmation.\n";
    }
    
    public function canTransitionTo(string $state): bool
    {
        return in_array($state, ['confirmed', 'cancelled']);
    }
    
    public function getStateName(): string
    {
        return 'pending';
    }
}
