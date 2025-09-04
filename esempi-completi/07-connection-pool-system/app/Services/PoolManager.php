<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PoolManager
{
    private array $pools = [];
    private array $poolStats = [];

    public function addPool(string $name, $pool): void
    {
        $this->pools[$name] = $pool;
        $this->poolStats[$name] = [
            'created_at' => time(),
            'last_used' => null,
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
        ];
        
        Log::info("Pool added to manager", [
            'pool_name' => $name,
            'pool_type' => get_class($pool)
        ]);
    }

    public function removePool(string $name): void
    {
        if (isset($this->pools[$name])) {
            unset($this->pools[$name]);
            unset($this->poolStats[$name]);
            
            Log::info("Pool removed from manager", [
                'pool_name' => $name
            ]);
        }
    }

    public function acquire(string $poolName, string $acquiredBy = null)
    {
        if (!isset($this->pools[$poolName])) {
            throw new \Exception("Pool '{$poolName}' not found");
        }

        $this->poolStats[$poolName]['total_requests']++;
        $this->poolStats[$poolName]['last_used'] = time();

        try {
            $resource = $this->pools[$poolName]->acquire($acquiredBy);
            $this->poolStats[$poolName]['successful_requests']++;
            
            Log::debug("Resource acquired from pool manager", [
                'pool_name' => $poolName,
                'acquired_by' => $acquiredBy
            ]);
            
            return $resource;
        } catch (\Exception $e) {
            $this->poolStats[$poolName]['failed_requests']++;
            
            Log::error("Failed to acquire resource from pool", [
                'pool_name' => $poolName,
                'acquired_by' => $acquiredBy,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function release(string $poolName, $resource): void
    {
        if (!isset($this->pools[$poolName])) {
            throw new \Exception("Pool '{$poolName}' not found");
        }

        try {
            $this->pools[$poolName]->release($resource);
            
            Log::debug("Resource released to pool manager", [
                'pool_name' => $poolName
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to release resource to pool", [
                'pool_name' => $poolName,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function getPoolStats(string $poolName): array
    {
        if (!isset($this->pools[$poolName])) {
            throw new \Exception("Pool '{$poolName}' not found");
        }

        $poolStats = $this->pools[$poolName]->getStats();
        $managerStats = $this->poolStats[$poolName];
        
        return array_merge($poolStats, [
            'manager_stats' => $managerStats,
            'success_rate' => $this->getSuccessRate($poolName),
            'uptime' => time() - $managerStats['created_at'],
        ]);
    }

    public function getAllStats(): array
    {
        $stats = [];
        
        foreach (array_keys($this->pools) as $poolName) {
            $stats[$poolName] = $this->getPoolStats($poolName);
        }
        
        return $stats;
    }

    public function getSuccessRate(string $poolName): float
    {
        if (!isset($this->poolStats[$poolName])) {
            return 0.0;
        }
        
        $stats = $this->poolStats[$poolName];
        $total = $stats['total_requests'];
        
        if ($total === 0) {
            return 0.0;
        }
        
        return ($stats['successful_requests'] / $total) * 100;
    }

    public function healthCheck(string $poolName = null): array
    {
        if ($poolName) {
            if (!isset($this->pools[$poolName])) {
                throw new \Exception("Pool '{$poolName}' not found");
            }
            
            return [
                $poolName => $this->pools[$poolName]->healthCheck()
            ];
        }
        
        $health = [];
        foreach ($this->pools as $name => $pool) {
            $health[$name] = $pool->healthCheck();
        }
        
        return $health;
    }

    public function cleanup(string $poolName = null): array
    {
        if ($poolName) {
            if (!isset($this->pools[$poolName])) {
                throw new \Exception("Pool '{$poolName}' not found");
            }
            
            $removed = $this->pools[$poolName]->cleanup();
            
            Log::info("Pool cleanup completed", [
                'pool_name' => $poolName,
                'removed_resources' => $removed
            ]);
            
            return [$poolName => $removed];
        }
        
        $results = [];
        foreach ($this->pools as $name => $pool) {
            $results[$name] = $pool->cleanup();
        }
        
        Log::info("All pools cleanup completed", [
            'results' => $results
        ]);
        
        return $results;
    }

    public function reset(string $poolName = null): void
    {
        if ($poolName) {
            if (!isset($this->pools[$poolName])) {
                throw new \Exception("Pool '{$poolName}' not found");
            }
            
            $this->pools[$poolName]->reset();
            
            Log::info("Pool reset completed", [
                'pool_name' => $poolName
            ]);
        } else {
            foreach ($this->pools as $name => $pool) {
                $pool->reset();
            }
            
            Log::info("All pools reset completed");
        }
    }

    public function getPoolNames(): array
    {
        return array_keys($this->pools);
    }

    public function hasPool(string $poolName): bool
    {
        return isset($this->pools[$poolName]);
    }

    public function getPool(string $poolName)
    {
        if (!isset($this->pools[$poolName])) {
            throw new \Exception("Pool '{$poolName}' not found");
        }
        
        return $this->pools[$poolName];
    }

    public function getTotalPools(): int
    {
        return count($this->pools);
    }

    public function getTotalResources(): int
    {
        $total = 0;
        foreach ($this->pools as $pool) {
            $stats = $pool->getStats();
            $total += $stats['total'];
        }
        
        return $total;
    }

    public function getTotalInUse(): int
    {
        $total = 0;
        foreach ($this->pools as $pool) {
            $stats = $pool->getStats();
            $total += $stats['in_use'];
        }
        
        return $total;
    }

    public function getTotalAvailable(): int
    {
        $total = 0;
        foreach ($this->pools as $pool) {
            $stats = $pool->getStats();
            $total += $stats['available'];
        }
        
        return $total;
    }
}
