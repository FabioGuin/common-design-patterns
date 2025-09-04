<?php

namespace App\Traits;

trait Poolable
{
    protected bool $isInUse = false;
    protected ?int $acquiredAt = null;
    protected ?string $acquiredBy = null;

    public function acquire(string $acquiredBy = null): self
    {
        $this->isInUse = true;
        $this->acquiredAt = time();
        $this->acquiredBy = $acquiredBy;
        
        $this->onAcquire();
        
        return $this;
    }

    public function release(): self
    {
        $this->isInUse = false;
        $this->acquiredAt = null;
        $this->acquiredBy = null;
        
        $this->onRelease();
        
        return $this;
    }

    public function isInUse(): bool
    {
        return $this->isInUse;
    }

    public function getAcquiredAt(): ?int
    {
        return $this->acquiredAt;
    }

    public function getAcquiredBy(): ?string
    {
        return $this->acquiredBy;
    }

    public function getUsageDuration(): ?int
    {
        if (!$this->isInUse || !$this->acquiredAt) {
            return null;
        }
        
        return time() - $this->acquiredAt;
    }

    public function reset(): self
    {
        $this->isInUse = false;
        $this->acquiredAt = null;
        $this->acquiredBy = null;
        
        $this->onReset();
        
        return $this;
    }

    protected function onAcquire(): void
    {
        // Override in classi concrete se necessario
    }

    protected function onRelease(): void
    {
        // Override in classi concrete se necessario
    }

    protected function onReset(): void
    {
        // Override in classi concrete se necessario
    }
}
