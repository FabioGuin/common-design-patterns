<?php

namespace App\Throttling;

use App\Models\ThrottlingMetric;
use App\Models\ThrottlingEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ThrottlingManager
{
    private array $throttlingConfigs = [];

    public function __construct()
    {
        $this->initializeThrottlingConfigs();
    }

    public function checkRateLimit(string $serviceName, string $identifier, ?string $endpoint = null): bool
    {
        $config = $this->getThrottlingConfig($serviceName, $endpoint);
        $key = $this->generateKey($serviceName, $identifier, $endpoint);
        
        $currentCount = $this->getCurrentCount($key, $config);
        
        if ($currentCount >= $config['rate']) {
            $this->recordThrottling($serviceName, $identifier, $endpoint, $config);
            return false;
        }
        
        $this->incrementCount($key, $config);
        $this->recordSuccess($serviceName, $identifier, $endpoint, $config);
        return true;
    }

    public function execute(string $serviceName, string $identifier, callable $operation, ?string $endpoint = null): mixed
    {
        if (!$this->checkRateLimit($serviceName, $identifier, $endpoint)) {
            throw new ThrottlingException("Rate limit exceeded for service: {$serviceName}");
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $result = $operation();
            $this->recordSuccess($serviceName, $identifier, $endpoint, $this->getThrottlingConfig($serviceName, $endpoint), microtime(true) - $startTime, memory_get_usage() - $startMemory);
            return $result;
        } catch (\Exception $e) {
            $this->recordError($serviceName, $identifier, $endpoint, $this->getThrottlingConfig($serviceName, $endpoint), microtime(true) - $startTime, memory_get_usage() - $startMemory, $e);
            throw $e;
        }
    }

    public function getThrottlingConfig(string $serviceName, ?string $endpoint = null): array
    {
        // Controlla limite per endpoint specifico
        if ($endpoint && isset($this->throttlingConfigs['endpoints'][$endpoint])) {
            return $this->throttlingConfigs['endpoints'][$endpoint];
        }

        // Controlla limite per servizio
        if (isset($this->throttlingConfigs['services'][$serviceName])) {
            return $this->throttlingConfigs['services'][$serviceName];
        }

        // Usa limite di default
        return $this->throttlingConfigs['default'];
    }

    public function getThrottlingStatus(string $serviceName, string $identifier, ?string $endpoint = null): array
    {
        $config = $this->getThrottlingConfig($serviceName, $endpoint);
        $key = $this->generateKey($serviceName, $identifier, $endpoint);
        $currentCount = $this->getCurrentCount($key, $config);

        return [
            'service_name' => $serviceName,
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'config' => $config,
            'current_count' => $currentCount,
            'remaining_requests' => max(0, $config['rate'] - $currentCount),
            'is_throttled' => $currentCount >= $config['rate'],
            'reset_time' => $this->getResetTime($key, $config),
        ];
    }

    public function getAllThrottlingConfigs(): array
    {
        return $this->throttlingConfigs;
    }

    private function generateKey(string $serviceName, string $identifier, ?string $endpoint = null): string
    {
        $key = "throttling:{$serviceName}:{$identifier}";
        if ($endpoint) {
            $key .= ":{$endpoint}";
        }
        return $key;
    }

    private function getCurrentCount(string $key, array $config): int
    {
        $windowKey = $this->getWindowKey($key, $config);
        return Cache::get($windowKey, 0);
    }

    private function incrementCount(string $key, array $config): void
    {
        $windowKey = $this->getWindowKey($key, $config);
        $currentCount = Cache::get($windowKey, 0);
        
        Cache::put($windowKey, $currentCount + 1, $config['window']);
    }

    private function getWindowKey(string $key, array $config): string
    {
        $strategy = $config['strategy'] ?? 'fixed_window';
        
        switch ($strategy) {
            case 'sliding_window':
                $windowStart = floor(time() / $config['window']) * $config['window'];
                return "{$key}:{$windowStart}";
            case 'token_bucket':
                return "{$key}:tokens";
            case 'leaky_bucket':
                return "{$key}:queue";
            default: // fixed_window
                $windowStart = floor(time() / $config['window']) * $config['window'];
                return "{$key}:{$windowStart}";
        }
    }

    private function getResetTime(string $key, array $config): int
    {
        $strategy = $config['strategy'] ?? 'fixed_window';
        
        switch ($strategy) {
            case 'sliding_window':
                return floor(time() / $config['window']) * $config['window'] + $config['window'];
            case 'token_bucket':
                return time() + $config['refill_interval'];
            case 'leaky_bucket':
                return time() + $config['drain_interval'];
            default: // fixed_window
                return floor(time() / $config['window']) * $config['window'] + $config['window'];
        }
    }

    private function recordSuccess(string $serviceName, string $identifier, ?string $endpoint, array $config, float $executionTime = 0, int $memoryUsed = 0): void
    {
        if (!config('throttling.monitoring.enabled', true)) {
            return;
        }

        ThrottlingMetric::create([
            'service_name' => $serviceName,
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'rate_limit' => $config['rate'],
            'window_seconds' => $config['window'],
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_requests' => 1,
            'successful_requests' => 1,
            'throttled_requests' => 0,
            'error_requests' => 0,
            'created_at' => now(),
        ]);
    }

    private function recordThrottling(string $serviceName, string $identifier, ?string $endpoint, array $config): void
    {
        if (!config('throttling.monitoring.enabled', true)) {
            return;
        }

        ThrottlingMetric::create([
            'service_name' => $serviceName,
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'rate_limit' => $config['rate'],
            'window_seconds' => $config['window'],
            'execution_time' => 0,
            'memory_used' => 0,
            'total_requests' => 1,
            'successful_requests' => 0,
            'throttled_requests' => 1,
            'error_requests' => 0,
            'created_at' => now(),
        ]);

        ThrottlingEvent::create([
            'service_name' => $serviceName,
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'event_type' => 'throttled',
            'rate_limit' => $config['rate'],
            'window_seconds' => $config['window'],
            'error_message' => 'Rate limit exceeded',
            'created_at' => now(),
        ]);

        Log::warning("Throttling occurred for {$serviceName}:{$identifier} - Rate limit exceeded");
    }

    private function recordError(string $serviceName, string $identifier, ?string $endpoint, array $config, float $executionTime, int $memoryUsed, \Exception $e): void
    {
        if (!config('throttling.monitoring.enabled', true)) {
            return;
        }

        ThrottlingMetric::create([
            'service_name' => $serviceName,
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'rate_limit' => $config['rate'],
            'window_seconds' => $config['window'],
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_requests' => 1,
            'successful_requests' => 0,
            'throttled_requests' => 0,
            'error_requests' => 1,
            'created_at' => now(),
        ]);

        ThrottlingEvent::create([
            'service_name' => $serviceName,
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'event_type' => 'error',
            'rate_limit' => $config['rate'],
            'window_seconds' => $config['window'],
            'error_message' => $e->getMessage(),
            'created_at' => now(),
        ]);
    }

    private function initializeThrottlingConfigs(): void
    {
        $configs = config('throttling', []);

        $this->throttlingConfigs = [
            'default' => $configs['default'] ?? [
                'rate' => 100,
                'window' => 3600,
                'strategy' => 'fixed_window'
            ],
            'services' => $configs['services'] ?? [],
            'endpoints' => $configs['endpoints'] ?? [],
            'user_types' => $configs['user_types'] ?? [],
        ];
    }
}
