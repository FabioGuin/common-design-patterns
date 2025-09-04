<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimiter
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai.rate_limits', []);
    }

    public function checkLimit(string $providerName): bool
    {
        $limits = $this->config[$providerName] ?? [];
        
        if (empty($limits)) {
            return true; // Nessun limite configurato
        }

        // Controlla limiti per minuto
        if (isset($limits['requests_per_minute'])) {
            if (!$this->checkRequestsPerMinute($providerName, $limits['requests_per_minute'])) {
                Log::warning('Rate limit exceeded for requests per minute', [
                    'provider' => $providerName,
                    'limit' => $limits['requests_per_minute']
                ]);
                return false;
            }
        }

        // Controlla limiti per giorno
        if (isset($limits['requests_per_day'])) {
            if (!$this->checkRequestsPerDay($providerName, $limits['requests_per_day'])) {
                Log::warning('Rate limit exceeded for requests per day', [
                    'provider' => $providerName,
                    'limit' => $limits['requests_per_day']
                ]);
                return false;
            }
        }

        return true;
    }

    private function checkRequestsPerMinute(string $providerName, int $limit): bool
    {
        $key = "rate_limit:{$providerName}:minute:" . now()->format('Y-m-d-H-i');
        $current = Cache::get($key, 0);

        if ($current >= $limit) {
            return false;
        }

        Cache::increment($key);
        Cache::expire($key, 60); // Scade dopo 1 minuto

        return true;
    }

    private function checkRequestsPerDay(string $providerName, int $limit): bool
    {
        $key = "rate_limit:{$providerName}:day:" . now()->format('Y-m-d');
        $current = Cache::get($key, 0);

        if ($current >= $limit) {
            return false;
        }

        Cache::increment($key);
        Cache::expire($key, 86400); // Scade dopo 24 ore

        return true;
    }

    public function getRemainingRequests(string $providerName): array
    {
        $limits = $this->config[$providerName] ?? [];
        $remaining = [];

        if (isset($limits['requests_per_minute'])) {
            $key = "rate_limit:{$providerName}:minute:" . now()->format('Y-m-d-H-i');
            $current = Cache::get($key, 0);
            $remaining['per_minute'] = max(0, $limits['requests_per_minute'] - $current);
        }

        if (isset($limits['requests_per_day'])) {
            $key = "rate_limit:{$providerName}:day:" . now()->format('Y-m-d');
            $current = Cache::get($key, 0);
            $remaining['per_day'] = max(0, $limits['requests_per_day'] - $current);
        }

        return $remaining;
    }

    public function resetLimits(string $providerName): void
    {
        $minuteKey = "rate_limit:{$providerName}:minute:" . now()->format('Y-m-d-H-i');
        $dayKey = "rate_limit:{$providerName}:day:" . now()->format('Y-m-d');

        Cache::forget($minuteKey);
        Cache::forget($dayKey);

        Log::info('Rate limits reset', ['provider' => $providerName]);
    }

    public function getAllLimits(): array
    {
        $allLimits = [];

        foreach ($this->config as $provider => $limits) {
            $allLimits[$provider] = [
                'limits' => $limits,
                'remaining' => $this->getRemainingRequests($provider)
            ];
        }

        return $allLimits;
    }
}
