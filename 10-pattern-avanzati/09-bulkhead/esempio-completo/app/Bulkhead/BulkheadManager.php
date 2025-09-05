<?php

namespace App\Bulkhead;

use App\Models\BulkheadMetric;
use Illuminate\Support\Facades\Log;

class BulkheadManager
{
    private array $bulkheads = [];
    private array $resourcePools = [];

    public function __construct()
    {
        $this->initializeBulkheads();
    }

    public function execute(string $serviceName, callable $operation): mixed
    {
        $bulkhead = $this->getBulkhead($serviceName);
        
        if (!$bulkhead->hasCapacity()) {
            throw new BulkheadCapacityExceededException("No capacity available for service: {$serviceName}");
        }

        $startTime = microtime(true);
        $memoryStart = memory_get_usage();

        try {
            $result = $bulkhead->execute($operation);
            $this->recordMetric($serviceName, 'success', microtime(true) - $startTime, memory_get_usage() - $memoryStart);
            return $result;
        } catch (\Exception $e) {
            $this->recordMetric($serviceName, 'failure', microtime(true) - $startTime, memory_get_usage() - $memoryStart);
            Log::warning("Bulkhead execution failed for {$serviceName}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function getBulkhead(string $serviceName): Bulkhead
    {
        if (!isset($this->bulkheads[$serviceName])) {
            throw new \InvalidArgumentException("Unknown service: {$serviceName}");
        }

        return $this->bulkheads[$serviceName];
    }

    public function getAllBulkheads(): array
    {
        return array_map(fn($bulkhead) => $bulkhead->toArray(), $this->bulkheads);
    }

    public function getBulkheadStatus(string $serviceName): ?array
    {
        if (!isset($this->bulkheads[$serviceName])) {
            return null;
        }

        return $this->bulkheads[$serviceName]->toArray();
    }

    public function resetBulkhead(string $serviceName): bool
    {
        if (!isset($this->bulkheads[$serviceName])) {
            return false;
        }

        $this->bulkheads[$serviceName]->reset();
        Log::info("Bulkhead for {$serviceName} has been reset");
        return true;
    }

    public function resetAllBulkheads(): void
    {
        foreach ($this->bulkheads as $serviceName => $bulkhead) {
            $bulkhead->reset();
            Log::info("Bulkhead for {$serviceName} has been reset");
        }
    }

    private function initializeBulkheads(): void
    {
        $configs = config('bulkhead.services', []);

        foreach ($configs as $serviceName => $config) {
            $this->bulkheads[$serviceName] = new Bulkhead(
                $serviceName,
                BulkheadConfig::fromArray($config)
            );
        }
    }

    private function recordMetric(string $serviceName, string $type, float $executionTime, int $memoryUsed): void
    {
        if (!config('bulkhead.monitoring.enabled', true)) {
            return;
        }

        BulkheadMetric::create([
            'service_name' => $serviceName,
            'metric_type' => $type,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'active_threads' => $this->bulkheads[$serviceName]->getActiveThreads(),
            'active_connections' => $this->bulkheads[$serviceName]->getActiveConnections(),
            'queue_length' => $this->bulkheads[$serviceName]->getQueueLength(),
            'created_at' => now(),
        ]);
    }
}
