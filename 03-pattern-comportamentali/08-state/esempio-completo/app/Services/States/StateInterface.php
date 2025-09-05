<?php

namespace App\Services\States;

interface StateInterface
{
    public function handle(object $context): void;
    public function canTransitionTo(string $state): bool;
    public function getStateName(): string;
}
