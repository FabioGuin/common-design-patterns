<?php

namespace App\Retry;

use App\Models\RetryMetric;
use App\Models\RetryAttempt;
use Illuminate\Support\Facades\Log;

class RetryManager
{
    private array $retryConfigs = [];

    public function __construct()
    {
        $this->initializeRetryConfigs();
    }

    public function execute(string $serviceName, callable $operation, ?RetryConfig $config = null): mixed
    {
        $retryConfig = $config ?? $this->getRetryConfig($serviceName);
        $lastError = null;

        for ($attempt = 1; $attempt <= $retryConfig->getMaxAttempts(); $attempt++) {
            $startTime = microtime(true);
            $startMemory = memory_get_usage();

            try {
                $result = $operation();
                $this->recordSuccess($serviceName, $attempt, microtime(true) - $startTime, memory_get_usage() - $startMemory);
                return $result;
            } catch (\Exception $e) {
                $lastError = $e;
                $this->recordAttempt($serviceName, $attempt, $e, microtime(true) - $startTime, memory_get_usage() - $startMemory);

                if (!$this->shouldRetry($e, $attempt, $retryConfig)) {
                    $this->recordFailure($serviceName, $attempt, $e, microtime(true) - $startTime, memory_get_usage() - $startMemory);
                    throw $e;
                }

                if ($attempt < $retryConfig->getMaxAttempts()) {
                    $delay = $this->calculateDelay($attempt, $retryConfig);
                    Log::info("Retrying {$serviceName} in {$delay}ms (attempt {$attempt}/{$retryConfig->getMaxAttempts()})");
                    $this->sleep($delay);
                }
            }
        }

        $this->recordFailure($serviceName, $retryConfig->getMaxAttempts(), $lastError, 0, 0);
        throw $lastError;
    }

    public function getRetryConfig(string $serviceName): RetryConfig
    {
        if (!isset($this->retryConfigs[$serviceName])) {
            throw new \InvalidArgumentException("Unknown service: {$serviceName}");
        }

        return $this->retryConfigs[$serviceName];
    }

    public function getAllRetryConfigs(): array
    {
        return array_map(fn($config) => $config->toArray(), $this->retryConfigs);
    }

    public function getRetryStatus(string $serviceName): ?array
    {
        if (!isset($this->retryConfigs[$serviceName])) {
            return null;
        }

        $config = $this->retryConfigs[$serviceName];
        $metrics = RetryMetric::where('service_name', $serviceName)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return [
            'service_name' => $serviceName,
            'config' => $config->toArray(),
            'total_attempts' => $metrics->sum('total_attempts'),
            'successful_attempts' => $metrics->sum('successful_attempts'),
            'failed_attempts' => $metrics->sum('failed_attempts'),
            'success_rate' => $this->calculateSuccessRate($metrics),
            'avg_attempts_per_operation' => $this->calculateAvgAttempts($metrics),
        ];
    }

    private function shouldRetry(\Exception $error, int $attempt, RetryConfig $config): bool
    {
        if ($attempt >= $config->getMaxAttempts()) {
            return false;
        }

        if ($config->getRetryableErrors() && !in_array($error->getCode(), $config->getRetryableErrors())) {
            return false;
        }

        if ($config->getNonRetryableErrors() && in_array($error->getCode(), $config->getNonRetryableErrors())) {
            return false;
        }

        return true;
    }

    private function calculateDelay(int $attempt, RetryConfig $config): int
    {
        $strategy = $config->getBackoffStrategy();
        $baseDelay = $config->getBaseDelay();
        $maxDelay = $config->getMaxDelay();
        $multiplier = $config->getMultiplier();

        $delay = match ($strategy) {
            'exponential' => $baseDelay * pow($multiplier, $attempt - 1),
            'linear' => $baseDelay * $attempt,
            'jitter' => $baseDelay * pow($multiplier, $attempt - 1) + rand(0, 1000),
            default => $baseDelay
        };

        return min($delay, $maxDelay);
    }

    private function sleep(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }

    private function recordSuccess(string $serviceName, int $attempt, float $executionTime, int $memoryUsed): void
    {
        if (!config('retry.monitoring.enabled', true)) {
            return;
        }

        RetryMetric::create([
            'service_name' => $serviceName,
            'total_attempts' => $attempt,
            'successful_attempts' => 1,
            'failed_attempts' => 0,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'created_at' => now(),
        ]);
    }

    private function recordAttempt(string $serviceName, int $attempt, \Exception $error, float $executionTime, int $memoryUsed): void
    {
        if (!config('retry.monitoring.enabled', true)) {
            return;
        }

        RetryAttempt::create([
            'service_name' => $serviceName,
            'attempt_number' => $attempt,
            'error_code' => $error->getCode(),
            'error_message' => $error->getMessage(),
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'created_at' => now(),
        ]);
    }

    private function recordFailure(string $serviceName, int $attempt, \Exception $error, float $executionTime, int $memoryUsed): void
    {
        if (!config('retry.monitoring.enabled', true)) {
            return;
        }

        RetryMetric::create([
            'service_name' => $serviceName,
            'total_attempts' => $attempt,
            'successful_attempts' => 0,
            'failed_attempts' => 1,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'created_at' => now(),
        ]);
    }

    private function calculateSuccessRate($metrics): float
    {
        $totalAttempts = $metrics->sum('total_attempts');
        $successfulAttempts = $metrics->sum('successful_attempts');

        return $totalAttempts > 0 ? ($successfulAttempts / $totalAttempts) * 100 : 0;
    }

    private function calculateAvgAttempts($metrics): float
    {
        $totalOperations = $metrics->count();
        $totalAttempts = $metrics->sum('total_attempts');

        return $totalOperations > 0 ? $totalAttempts / $totalOperations : 0;
    }

    private function initializeRetryConfigs(): void
    {
        $configs = config('retry.services', []);

        foreach ($configs as $serviceName => $config) {
            $this->retryConfigs[$serviceName] = RetryConfig::fromArray($config);
        }
    }
}
