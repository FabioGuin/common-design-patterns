<?php

namespace App\Services\Strategies;

interface StrategyInterface
{
    public function execute(array $data): mixed;
    public function getStrategyName(): string;
}
