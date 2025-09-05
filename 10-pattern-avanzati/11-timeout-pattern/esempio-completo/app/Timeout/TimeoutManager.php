<?php

namespace App\Timeout;

use App\Models\TimeoutMetric;
use App\Models\TimeoutEvent;
use Illuminate\Support\Facades\Log;

class TimeoutManager
{
    private array $timeoutConfigs = [];

    public function __construct()
    {
        $this->initializeTimeoutConfigs();
    }

    public function execute(string $serviceName, callable $operation, ?int $customTimeout = null): mixed
    {
        $timeout = $customTimeout ?? $this->getTimeoutForService($serviceName);
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $result = $this->executeWithTimeout($operation, $timeout);
            $this->recordSuccess($serviceName, $timeout, microtime(true) - $startTime, memory_get_usage() - $startMemory);
            return $result;
        } catch (TimeoutException $e) {
            $this->recordTimeout($serviceName, $timeout, microtime(true) - $startTime, memory_get_usage() - $startMemory, $e);
            throw $e;
        } catch (\Exception $e) {
            $this->recordError($serviceName, $timeout, microtime(true) - $startTime, memory_get_usage() - $startMemory, $e);
            throw $e;
        }
    }

    public function executeWithRetry(string $serviceName, callable $operation, ?int $customTimeout = null): mixed
    {
        $timeout = $customTimeout ?? $this->getTimeoutForService($serviceName);
        $maxTotalTimeout = $this->getMaxTotalTimeoutForService($serviceName);
        $startTime = microtime(true);
        $lastError = null;

        for ($attempt = 1; $attempt <= $this->getMaxAttemptsForService($serviceName); $attempt++) {
            $remainingTime = $maxTotalTimeout - (microtime(true) - $startTime) * 1000;
            
            if ($remainingTime <= 0) {
                throw new TimeoutException("Total timeout exceeded for service: {$serviceName}");
            }

            try {
                $result = $this->executeWithTimeout($operation, min($timeout, $remainingTime));
                $this->recordSuccess($serviceName, $timeout, microtime(true) - $startTime, memory_get_usage() - memory_get_usage());
                return $result;
            } catch (TimeoutException $e) {
                $lastError = $e;
                $this->recordTimeout($serviceName, $timeout, microtime(true) - $startTime, memory_get_usage() - memory_get_usage(), $e);
                
                if ($attempt < $this->getMaxAttemptsForService($serviceName)) {
                    $delay = $this->getRetryDelayForService($serviceName);
                    usleep($delay * 1000);
                    continue;
                }
            } catch (\Exception $e) {
                $lastError = $e;
                $this->recordError($serviceName, $timeout, microtime(true) - $startTime, memory_get_usage() - memory_get_usage(), $e);
                throw $e;
            }
        }

        throw $lastError;
    }

    private function executeWithTimeout(callable $operation, int $timeoutMs): mixed
    {
        $startTime = microtime(true);
        
        // Simula timeout usando un loop con controllo del tempo
        // In un'implementazione reale, useresti Promise.race() o simili
        $result = null;
        $exception = null;
        
        try {
            $result = $operation();
        } catch (\Exception $e) {
            $exception = $e;
        }
        
        $elapsedTime = (microtime(true) - $startTime) * 1000;
        
        if ($elapsedTime > $timeoutMs) {
            throw new TimeoutException("Operation timed out after {$timeoutMs}ms");
        }
        
        if ($exception) {
            throw $exception;
        }
        
        return $result;
    }

    public function getTimeoutForService(string $serviceName): int
    {
        return $this->timeoutConfigs[$serviceName]['timeout'] ?? $this->timeoutConfigs['default']['timeout'];
    }

    public function getMaxTotalTimeoutForService(string $serviceName): int
    {
        return $this->timeoutConfigs[$serviceName]['max_total_timeout'] ?? $this->timeoutConfigs['default']['max_total_timeout'];
    }

    public function getMaxAttemptsForService(string $serviceName): int
    {
        return $this->timeoutConfigs[$serviceName]['max_attempts'] ?? $this->timeoutConfigs['default']['max_attempts'];
    }

    public function getRetryDelayForService(string $serviceName): int
    {
        return $this->timeoutConfigs[$serviceName]['retry_delay'] ?? $this->timeoutConfigs['default']['retry_delay'];
    }

    public function getAllTimeoutConfigs(): array
    {
        return $this->timeoutConfigs;
    }

    public function getTimeoutStatus(string $serviceName): ?array
    {
        if (!isset($this->timeoutConfigs[$serviceName])) {
            return null;
        }

        $config = $this->timeoutConfigs[$serviceName];
        $metrics = TimeoutMetric::where('service_name', $serviceName)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return [
            'service_name' => $serviceName,
            'config' => $config,
            'total_operations' => $metrics->sum('total_operations'),
            'successful_operations' => $metrics->sum('successful_operations'),
            'timeout_operations' => $metrics->sum('timeout_operations'),
            'error_operations' => $metrics->sum('error_operations'),
            'success_rate' => $this->calculateSuccessRate($metrics),
            'timeout_rate' => $this->calculateTimeoutRate($metrics),
            'avg_execution_time' => $this->calculateAvgExecutionTime($metrics),
        ];
    }

    private function recordSuccess(string $serviceName, int $timeout, float $executionTime, int $memoryUsed): void
    {
        if (!config('timeout.monitoring.enabled', true)) {
            return;
        }

        TimeoutMetric::create([
            'service_name' => $serviceName,
            'timeout_ms' => $timeout,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_operations' => 1,
            'successful_operations' => 1,
            'timeout_operations' => 0,
            'error_operations' => 0,
            'created_at' => now(),
        ]);
    }

    private function recordTimeout(string $serviceName, int $timeout, float $executionTime, int $memoryUsed, TimeoutException $e): void
    {
        if (!config('timeout.monitoring.enabled', true)) {
            return;
        }

        TimeoutMetric::create([
            'service_name' => $serviceName,
            'timeout_ms' => $timeout,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_operations' => 1,
            'successful_operations' => 0,
            'timeout_operations' => 1,
            'error_operations' => 0,
            'created_at' => now(),
        ]);

        TimeoutEvent::create([
            'service_name' => $serviceName,
            'timeout_ms' => $timeout,
            'execution_time' => $executionTime,
            'event_type' => 'timeout',
            'error_message' => $e->getMessage(),
            'created_at' => now(),
        ]);

        Log::warning("Timeout occurred for {$serviceName}: {$e->getMessage()}");
    }

    private function recordError(string $serviceName, int $timeout, float $executionTime, int $memoryUsed, \Exception $e): void
    {
        if (!config('timeout.monitoring.enabled', true)) {
            return;
        }

        TimeoutMetric::create([
            'service_name' => $serviceName,
            'timeout_ms' => $timeout,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_operations' => 1,
            'successful_operations' => 0,
            'timeout_operations' => 0,
            'error_operations' => 1,
            'created_at' => now(),
        ]);

        TimeoutEvent::create([
            'service_name' => $serviceName,
            'timeout_ms' => $timeout,
            'execution_time' => $executionTime,
            'event_type' => 'error',
            'error_message' => $e->getMessage(),
            'created_at' => now(),
        ]);
    }

    private function calculateSuccessRate($metrics): float
    {
        $totalOperations = $metrics->sum('total_operations');
        $successfulOperations = $metrics->sum('successful_operations');

        return $totalOperations > 0 ? ($successfulOperations / $totalOperations) * 100 : 0;
    }

    private function calculateTimeoutRate($metrics): float
    {
        $totalOperations = $metrics->sum('total_operations');
        $timeoutOperations = $metrics->sum('timeout_operations');

        return $totalOperations > 0 ? ($timeoutOperations / $totalOperations) * 100 : 0;
    }

    private function calculateAvgExecutionTime($metrics): float
    {
        return $metrics->avg('execution_time') ?? 0;
    }

    private function initializeTimeoutConfigs(): void
    {
        $configs = config('timeout.services', []);

        foreach ($configs as $serviceName => $config) {
            $this->timeoutConfigs[$serviceName] = $config;
        }
    }
}
