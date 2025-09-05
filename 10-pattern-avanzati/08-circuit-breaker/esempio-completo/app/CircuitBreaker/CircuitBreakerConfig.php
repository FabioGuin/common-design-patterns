<?php

namespace App\CircuitBreaker;

class CircuitBreakerConfig
{
    public function __construct(
        private int $failureThreshold = 5,
        private int $timeout = 60000, // 60 secondi in millisecondi
        private int $successThreshold = 3,
        private bool $monitoringEnabled = true
    ) {}

    public function getFailureThreshold(): int
    {
        return $this->failureThreshold;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getSuccessThreshold(): int
    {
        return $this->successThreshold;
    }

    public function isMonitoringEnabled(): bool
    {
        return $this->monitoringEnabled;
    }

    public function setFailureThreshold(int $threshold): self
    {
        $this->failureThreshold = $threshold;
        return $this;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setSuccessThreshold(int $threshold): self
    {
        $this->successThreshold = $threshold;
        return $this;
    }

    public function setMonitoringEnabled(bool $enabled): self
    {
        $this->monitoringEnabled = $enabled;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'failure_threshold' => $this->failureThreshold,
            'timeout' => $this->timeout,
            'success_threshold' => $this->successThreshold,
            'monitoring_enabled' => $this->monitoringEnabled,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['failure_threshold'] ?? 5,
            $config['timeout'] ?? 60000,
            $config['success_threshold'] ?? 3,
            $config['monitoring_enabled'] ?? true
        );
    }
}
