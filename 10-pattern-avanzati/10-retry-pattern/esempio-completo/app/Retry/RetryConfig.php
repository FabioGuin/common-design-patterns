<?php

namespace App\Retry;

class RetryConfig
{
    public function __construct(
        private int $maxAttempts = 3,
        private int $baseDelay = 1000, // milliseconds
        private int $maxDelay = 10000, // milliseconds
        private float $multiplier = 2.0,
        private string $backoffStrategy = 'exponential',
        private ?array $retryableErrors = null,
        private ?array $nonRetryableErrors = null,
        private bool $monitoringEnabled = true
    ) {}

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getBaseDelay(): int
    {
        return $this->baseDelay;
    }

    public function getMaxDelay(): int
    {
        return $this->maxDelay;
    }

    public function getMultiplier(): float
    {
        return $this->multiplier;
    }

    public function getBackoffStrategy(): string
    {
        return $this->backoffStrategy;
    }

    public function getRetryableErrors(): ?array
    {
        return $this->retryableErrors;
    }

    public function getNonRetryableErrors(): ?array
    {
        return $this->nonRetryableErrors;
    }

    public function isMonitoringEnabled(): bool
    {
        return $this->monitoringEnabled;
    }

    public function setMaxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    public function setBaseDelay(int $baseDelay): self
    {
        $this->baseDelay = $baseDelay;
        return $this;
    }

    public function setMaxDelay(int $maxDelay): self
    {
        $this->maxDelay = $maxDelay;
        return $this;
    }

    public function setMultiplier(float $multiplier): self
    {
        $this->multiplier = $multiplier;
        return $this;
    }

    public function setBackoffStrategy(string $backoffStrategy): self
    {
        $this->backoffStrategy = $backoffStrategy;
        return $this;
    }

    public function setRetryableErrors(?array $retryableErrors): self
    {
        $this->retryableErrors = $retryableErrors;
        return $this;
    }

    public function setNonRetryableErrors(?array $nonRetryableErrors): self
    {
        $this->nonRetryableErrors = $nonRetryableErrors;
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
            'max_attempts' => $this->maxAttempts,
            'base_delay' => $this->baseDelay,
            'max_delay' => $this->maxDelay,
            'multiplier' => $this->multiplier,
            'backoff_strategy' => $this->backoffStrategy,
            'retryable_errors' => $this->retryableErrors,
            'non_retryable_errors' => $this->nonRetryableErrors,
            'monitoring_enabled' => $this->monitoringEnabled,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['max_attempts'] ?? 3,
            $config['base_delay'] ?? 1000,
            $config['max_delay'] ?? 10000,
            $config['multiplier'] ?? 2.0,
            $config['backoff_strategy'] ?? 'exponential',
            $config['retryable_errors'] ?? null,
            $config['non_retryable_errors'] ?? null,
            $config['monitoring_enabled'] ?? true
        );
    }
}
