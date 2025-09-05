<?php

namespace App\Bulkhead;

class BulkheadConfig
{
    public function __construct(
        private int $maxThreads = 5,
        private int $maxConnections = 10,
        private int $maxQueueLength = 100,
        private int $memoryLimit = 256, // MB
        private string $priority = 'medium',
        private int $timeout = 30, // seconds
        private bool $monitoringEnabled = true
    ) {}

    public function getMaxThreads(): int
    {
        return $this->maxThreads;
    }

    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }

    public function getMaxQueueLength(): int
    {
        return $this->maxQueueLength;
    }

    public function getMemoryLimit(): int
    {
        return $this->memoryLimit;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function isMonitoringEnabled(): bool
    {
        return $this->monitoringEnabled;
    }

    public function setMaxThreads(int $maxThreads): self
    {
        $this->maxThreads = $maxThreads;
        return $this;
    }

    public function setMaxConnections(int $maxConnections): self
    {
        $this->maxConnections = $maxConnections;
        return $this;
    }

    public function setMaxQueueLength(int $maxQueueLength): self
    {
        $this->maxQueueLength = $maxQueueLength;
        return $this;
    }

    public function setMemoryLimit(int $memoryLimit): self
    {
        $this->memoryLimit = $memoryLimit;
        return $this;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setMonitoringEnabled(bool $monitoringEnabled): self
    {
        $this->monitoringEnabled = $monitoringEnabled;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'max_threads' => $this->maxThreads,
            'max_connections' => $this->maxConnections,
            'max_queue_length' => $this->maxQueueLength,
            'memory_limit' => $this->memoryLimit,
            'priority' => $this->priority,
            'timeout' => $this->timeout,
            'monitoring_enabled' => $this->monitoringEnabled,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['max_threads'] ?? 5,
            $config['max_connections'] ?? 10,
            $config['max_queue_length'] ?? 100,
            $config['memory_limit'] ?? 256,
            $config['priority'] ?? 'medium',
            $config['timeout'] ?? 30,
            $config['monitoring_enabled'] ?? true
        );
    }
}
