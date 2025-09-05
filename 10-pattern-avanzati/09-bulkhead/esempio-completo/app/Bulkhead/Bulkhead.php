<?php

namespace App\Bulkhead;

use Illuminate\Support\Facades\Log;

class Bulkhead
{
    private int $activeThreads = 0;
    private int $activeConnections = 0;
    private array $queue = [];
    private int $totalExecutions = 0;
    private int $successfulExecutions = 0;
    private int $failedExecutions = 0;

    public function __construct(
        private string $serviceName,
        private BulkheadConfig $config
    ) {}

    public function execute(callable $operation): mixed
    {
        if (!$this->hasCapacity()) {
            throw new BulkheadCapacityExceededException("No capacity available for service: {$this->serviceName}");
        }

        $this->activeThreads++;
        $this->activeConnections++;
        $this->totalExecutions++;

        try {
            $result = $operation();
            $this->successfulExecutions++;
            return $result;
        } catch (\Exception $e) {
            $this->failedExecutions++;
            throw $e;
        } finally {
            $this->activeThreads--;
            $this->activeConnections--;
        }
    }

    public function hasCapacity(): bool
    {
        return $this->activeThreads < $this->config->getMaxThreads() &&
               $this->activeConnections < $this->config->getMaxConnections() &&
               $this->getQueueLength() < $this->config->getMaxQueueLength();
    }

    public function queueOperation(callable $operation): void
    {
        if (count($this->queue) >= $this->config->getMaxQueueLength()) {
            throw new BulkheadQueueFullException("Queue is full for service: {$this->serviceName}");
        }

        $this->queue[] = $operation;
        Log::info("Operation queued for {$this->serviceName}. Queue length: " . count($this->queue));
    }

    public function processQueue(): void
    {
        while (!empty($this->queue) && $this->hasCapacity()) {
            $operation = array_shift($this->queue);
            try {
                $this->execute($operation);
            } catch (\Exception $e) {
                Log::error("Queued operation failed for {$this->serviceName}: {$e->getMessage()}");
            }
        }
    }

    public function getActiveThreads(): int
    {
        return $this->activeThreads;
    }

    public function getActiveConnections(): int
    {
        return $this->activeConnections;
    }

    public function getQueueLength(): int
    {
        return count($this->queue);
    }

    public function getTotalExecutions(): int
    {
        return $this->totalExecutions;
    }

    public function getSuccessfulExecutions(): int
    {
        return $this->successfulExecutions;
    }

    public function getFailedExecutions(): int
    {
        return $this->failedExecutions;
    }

    public function getSuccessRate(): float
    {
        return $this->totalExecutions > 0 ? ($this->successfulExecutions / $this->totalExecutions) * 100 : 0;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getConfig(): BulkheadConfig
    {
        return $this->config;
    }

    public function reset(): void
    {
        $this->activeThreads = 0;
        $this->activeConnections = 0;
        $this->queue = [];
        $this->totalExecutions = 0;
        $this->successfulExecutions = 0;
        $this->failedExecutions = 0;
        Log::info("Bulkhead for {$this->serviceName} has been reset");
    }

    public function toArray(): array
    {
        return [
            'service_name' => $this->serviceName,
            'active_threads' => $this->activeThreads,
            'active_connections' => $this->activeConnections,
            'queue_length' => $this->getQueueLength(),
            'total_executions' => $this->totalExecutions,
            'successful_executions' => $this->successfulExecutions,
            'failed_executions' => $this->failedExecutions,
            'success_rate' => $this->getSuccessRate(),
            'has_capacity' => $this->hasCapacity(),
            'config' => $this->config->toArray(),
        ];
    }
}
