<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ResourcePool
{
    private array $available = [];
    private array $inUse = [];
    private int $maxSize;
    private string $resourceClass;
    private array $constructorArgs;
    private int $created = 0;
    private int $acquired = 0;
    private int $released = 0;
    private int $failed = 0;

    public function __construct(string $resourceClass, int $maxSize = 10, array $constructorArgs = [])
    {
        $this->resourceClass = $resourceClass;
        $this->maxSize = $maxSize;
        $this->constructorArgs = $constructorArgs;
    }

    public function acquire(string $acquiredBy = null)
    {
        if (!empty($this->available)) {
            $resource = array_pop($this->available);
            $resource->acquire($acquiredBy);
            $this->inUse[] = $resource;
            $this->acquired++;
            
            Log::debug("Resource acquired from pool", [
                'resource_class' => $this->resourceClass,
                'acquired_by' => $acquiredBy,
                'available' => count($this->available),
                'in_use' => count($this->inUse)
            ]);
            
            return $resource;
        }

        if (count($this->inUse) < $this->maxSize) {
            try {
                $resource = new $this->resourceClass(...$this->constructorArgs);
                $resource->acquire($acquiredBy);
                $this->inUse[] = $resource;
                $this->created++;
                $this->acquired++;
                
                Log::info("New resource created for pool", [
                    'resource_class' => $this->resourceClass,
                    'acquired_by' => $acquiredBy,
                    'total_created' => $this->created
                ]);
                
                return $resource;
            } catch (\Exception $e) {
                $this->failed++;
                Log::error("Failed to create new resource", [
                    'resource_class' => $this->resourceClass,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception("Resource pool esaurito: " . $e->getMessage());
            }
        }

        $this->failed++;
        throw new \Exception("Resource pool esaurito. Max size: {$this->maxSize}");
    }

    public function release($resource): void
    {
        $key = array_search($resource, $this->inUse, true);
        if ($key !== false) {
            unset($this->inUse[$key]);
            $this->inUse = array_values($this->inUse); // Reindex array
            
            $resource->release();
            $this->available[] = $resource;
            $this->released++;
            
            Log::debug("Resource released to pool", [
                'resource_class' => $this->resourceClass,
                'available' => count($this->available),
                'in_use' => count($this->inUse)
            ]);
        }
    }

    public function getStats(): array
    {
        return [
            'resource_class' => $this->resourceClass,
            'max_size' => $this->maxSize,
            'available' => count($this->available),
            'in_use' => count($this->inUse),
            'total' => count($this->available) + count($this->inUse),
            'created' => $this->created,
            'acquired' => $this->acquired,
            'released' => $this->released,
            'failed' => $this->failed,
            'utilization' => $this->getUtilization(),
        ];
    }

    public function getUtilization(): float
    {
        $total = count($this->available) + count($this->inUse);
        if ($total === 0) {
            return 0.0;
        }
        
        return (count($this->inUse) / $total) * 100;
    }

    public function healthCheck(): array
    {
        $healthy = 0;
        $unhealthy = 0;
        $total = count($this->available) + count($this->inUse);

        foreach ($this->available as $resource) {
            if (method_exists($resource, 'ping') && $resource->ping()) {
                $healthy++;
            } else {
                $unhealthy++;
            }
        }

        foreach ($this->inUse as $resource) {
            if (method_exists($resource, 'ping') && $resource->ping()) {
                $healthy++;
            } else {
                $unhealthy++;
            }
        }

        return [
            'total_resources' => $total,
            'healthy' => $healthy,
            'unhealthy' => $unhealthy,
            'health_percentage' => $total > 0 ? ($healthy / $total) * 100 : 0,
            'status' => $unhealthy === 0 ? 'healthy' : ($healthy > $unhealthy ? 'degraded' : 'unhealthy')
        ];
    }

    public function cleanup(): int
    {
        $removed = 0;
        
        // Rimuovi risorse non sane dalla piscina disponibile
        foreach ($this->available as $key => $resource) {
            if (method_exists($resource, 'ping') && !$resource->ping()) {
                unset($this->available[$key]);
                $removed++;
            }
        }
        
        $this->available = array_values($this->available); // Reindex array
        
        Log::info("Resource pool cleanup completed", [
            'resource_class' => $this->resourceClass,
            'removed_resources' => $removed,
            'remaining_available' => count($this->available)
        ]);
        
        return $removed;
    }

    public function reset(): void
    {
        // Reset di tutte le risorse disponibili
        foreach ($this->available as $resource) {
            if (method_exists($resource, 'reset')) {
                $resource->reset();
            }
        }
        
        Log::info("Resource pool reset completed", [
            'resource_class' => $this->resourceClass,
            'reset_resources' => count($this->available)
        ]);
    }

    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    public function setMaxSize(int $maxSize): void
    {
        $this->maxSize = $maxSize;
        
        Log::info("Resource pool max size updated", [
            'resource_class' => $this->resourceClass,
            'new_max_size' => $maxSize
        ]);
    }

    public function __destruct()
    {
        // Distruggi tutte le risorse
        foreach ($this->available as $resource) {
            unset($resource);
        }
        
        foreach ($this->inUse as $resource) {
            unset($resource);
        }
    }
}
