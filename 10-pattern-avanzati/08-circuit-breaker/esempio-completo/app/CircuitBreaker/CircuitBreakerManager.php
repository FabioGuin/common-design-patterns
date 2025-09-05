<?php

namespace App\CircuitBreaker;

use Illuminate\Support\Facades\Config;

class CircuitBreakerManager
{
    private array $circuitBreakers = [];

    public function getCircuitBreaker(string $serviceName): CircuitBreaker
    {
        if (!isset($this->circuitBreakers[$serviceName])) {
            $config = $this->getConfigForService($serviceName);
            $this->circuitBreakers[$serviceName] = new CircuitBreaker($serviceName, $config);
        }

        return $this->circuitBreakers[$serviceName];
    }

    public function call(string $serviceName, callable $serviceFunction, callable $fallbackFunction = null): mixed
    {
        $circuitBreaker = $this->getCircuitBreaker($serviceName);
        return $circuitBreaker->call($serviceFunction, $fallbackFunction);
    }

    public function getAllCircuitBreakers(): array
    {
        return array_map(fn($cb) => $cb->toArray(), $this->circuitBreakers);
    }

    public function getCircuitBreakerState(string $serviceName): ?array
    {
        if (!isset($this->circuitBreakers[$serviceName])) {
            return null;
        }

        return $this->circuitBreakers[$serviceName]->toArray();
    }

    public function resetCircuitBreaker(string $serviceName): bool
    {
        if (!isset($this->circuitBreakers[$serviceName])) {
            return false;
        }

        $this->circuitBreakers[$serviceName]->reset();
        return true;
    }

    public function resetAllCircuitBreakers(): void
    {
        foreach ($this->circuitBreakers as $circuitBreaker) {
            $circuitBreaker->reset();
        }
    }

    private function getConfigForService(string $serviceName): CircuitBreakerConfig
    {
        $config = Config::get("circuit_breaker.services.{$serviceName}", []);
        
        return CircuitBreakerConfig::fromArray([
            'failure_threshold' => $config['failure_threshold'] ?? Config::get('circuit_breaker.failure_threshold', 5),
            'timeout' => $config['timeout'] ?? Config::get('circuit_breaker.timeout', 60000),
            'success_threshold' => $config['success_threshold'] ?? Config::get('circuit_breaker.success_threshold', 3),
            'monitoring_enabled' => $config['monitoring_enabled'] ?? Config::get('circuit_breaker.monitoring_enabled', true),
        ]);
    }
}
