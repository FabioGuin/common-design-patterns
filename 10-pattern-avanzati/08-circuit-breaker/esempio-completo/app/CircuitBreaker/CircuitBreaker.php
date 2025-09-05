<?php

namespace App\CircuitBreaker;

use App\Models\CircuitBreakerMetric;
use Illuminate\Support\Facades\Log;

class CircuitBreaker
{
    public const STATE_CLOSED = 'CLOSED';
    public const STATE_OPEN = 'OPEN';
    public const STATE_HALF_OPEN = 'HALF_OPEN';

    private string $state = self::STATE_CLOSED;
    private int $failureCount = 0;
    private ?int $lastFailureTime = null;
    private int $successCount = 0;
    private int $totalCalls = 0;
    private int $totalFailures = 0;

    public function __construct(
        private string $serviceName,
        private CircuitBreakerConfig $config
    ) {}

    public function call(callable $serviceFunction, callable $fallbackFunction = null): mixed
    {
        $this->totalCalls++;

        if ($this->state === self::STATE_OPEN) {
            if ($this->shouldAttemptReset()) {
                $this->state = self::STATE_HALF_OPEN;
                Log::info("Circuit breaker for {$this->serviceName} moved to HALF_OPEN state");
            } else {
                $this->recordMetric('circuit_open');
                return $this->executeFallback($fallbackFunction);
            }
        }

        try {
            $result = $serviceFunction();
            $this->onSuccess();
            $this->recordMetric('success');
            return $result;
        } catch (\Exception $e) {
            $this->onFailure();
            $this->recordMetric('failure');
            Log::warning("Circuit breaker failure for {$this->serviceName}: {$e->getMessage()}");
            
            if ($this->state === self::STATE_OPEN) {
                return $this->executeFallback($fallbackFunction);
            }
            
            throw $e;
        }
    }

    private function onSuccess(): void
    {
        $this->failureCount = 0;
        $this->successCount++;

        if ($this->state === self::STATE_HALF_OPEN) {
            if ($this->successCount >= $this->config->getSuccessThreshold()) {
                $this->state = self::STATE_CLOSED;
                $this->successCount = 0;
                Log::info("Circuit breaker for {$this->serviceName} moved to CLOSED state");
            }
        }
    }

    private function onFailure(): void
    {
        $this->failureCount++;
        $this->totalFailures++;
        $this->lastFailureTime = time();

        if ($this->failureCount >= $this->config->getFailureThreshold()) {
            $this->state = self::STATE_OPEN;
            Log::warning("Circuit breaker for {$this->serviceName} moved to OPEN state");
        }
    }

    private function shouldAttemptReset(): bool
    {
        return $this->lastFailureTime && 
               (time() - $this->lastFailureTime) >= $this->config->getTimeout();
    }

    private function executeFallback(callable $fallbackFunction = null): mixed
    {
        if ($fallbackFunction) {
            try {
                $this->recordMetric('fallback_success');
                return $fallbackFunction();
            } catch (\Exception $e) {
                $this->recordMetric('fallback_failure');
                Log::error("Fallback failed for {$this->serviceName}: {$e->getMessage()}");
                throw $e;
            }
        }

        throw new CircuitBreakerOpenException("Circuit breaker is OPEN for {$this->serviceName}");
    }

    private function recordMetric(string $type): void
    {
        if (!$this->config->isMonitoringEnabled()) {
            return;
        }

        CircuitBreakerMetric::create([
            'service_name' => $this->serviceName,
            'metric_type' => $type,
            'state' => $this->state,
            'failure_count' => $this->failureCount,
            'success_count' => $this->successCount,
            'total_calls' => $this->totalCalls,
            'total_failures' => $this->totalFailures,
            'created_at' => now(),
        ]);
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getTotalCalls(): int
    {
        return $this->totalCalls;
    }

    public function getTotalFailures(): int
    {
        return $this->totalFailures;
    }

    public function getFailureRate(): float
    {
        return $this->totalCalls > 0 ? ($this->totalFailures / $this->totalCalls) * 100 : 0;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function reset(): void
    {
        $this->state = self::STATE_CLOSED;
        $this->failureCount = 0;
        $this->successCount = 0;
        $this->lastFailureTime = null;
        Log::info("Circuit breaker for {$this->serviceName} has been reset");
    }

    public function toArray(): array
    {
        return [
            'service_name' => $this->serviceName,
            'state' => $this->state,
            'failure_count' => $this->failureCount,
            'success_count' => $this->successCount,
            'total_calls' => $this->totalCalls,
            'total_failures' => $this->totalFailures,
            'failure_rate' => $this->getFailureRate(),
            'last_failure_time' => $this->lastFailureTime,
            'config' => $this->config->toArray(),
        ];
    }
}
