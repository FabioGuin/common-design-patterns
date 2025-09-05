<?php

namespace App\Services\AI\Providers;

interface AIProviderInterface
{
    public function chat(string $prompt, array $options = []): array;
    public function isAvailable(): bool;
    public function getName(): string;
    public function getCostPerToken(): float;
}
