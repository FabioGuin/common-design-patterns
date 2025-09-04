<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PoolManager
{
    private array $pools = [];
    private static ?PoolManager $instance = null;

    private function __construct()
    {
        // Singleton pattern per gestire un solo manager
    }

    public static function getInstance(): PoolManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createPool(
        string $name,
        string $connectionName = 'mysql',
        int $maxSize = 10,
        int $timeout = 30,
        int $retryAttempts = 3
    ): ConnectionPool {
        if (isset($this->pools[$name])) {
            throw new \Exception("Pool '{$name}' già esistente");
        }

        $pool = new ConnectionPool($connectionName, $maxSize, $timeout, $retryAttempts);
        $this->pools[$name] = $pool;
        
        Log::info("Pool '{$name}' creato con connessione '{$connectionName}'");
        
        return $pool;
    }

    public function getPool(string $name): ConnectionPool
    {
        if (!isset($this->pools[$name])) {
            throw new \Exception("Pool '{$name}' non trovato");
        }

        return $this->pools[$name];
    }

    public function removePool(string $name): void
    {
        if (isset($this->pools[$name])) {
            $this->pools[$name]->reset();
            unset($this->pools[$name]);
            Log::info("Pool '{$name}' rimosso");
        }
    }

    public function getAllPools(): array
    {
        return $this->pools;
    }

    public function getAllStats(): array
    {
        $stats = [];
        foreach ($this->pools as $name => $pool) {
            $stats[$name] = $pool->getStats();
        }
        return $stats;
    }

    public function getAllHealthStatus(): array
    {
        $health = [];
        foreach ($this->pools as $name => $pool) {
            $health[$name] = $pool->getHealthStatus();
        }
        return $health;
    }

    public function resetAllPools(): void
    {
        foreach ($this->pools as $name => $pool) {
            $pool->reset();
        }
        Log::info("Tutti i pool sono stati resettati");
    }

    public function getGlobalStats(): array
    {
        $totalAvailable = 0;
        $totalInUse = 0;
        $totalMaxSize = 0;
        $poolCount = count($this->pools);

        foreach ($this->pools as $pool) {
            $stats = $pool->getStats();
            $totalAvailable += $stats['available'];
            $totalInUse += $stats['in_use'];
            $totalMaxSize += $stats['max_size'];
        }

        return [
            'total_pools' => $poolCount,
            'total_available' => $totalAvailable,
            'total_in_use' => $totalInUse,
            'total_max_size' => $totalMaxSize,
            'global_utilization_percentage' => $totalMaxSize > 0 ? round(($totalInUse / $totalMaxSize) * 100, 2) : 0,
            'timestamp' => now()->toISOString()
        ];
    }

    public function getGlobalHealthStatus(): array
    {
        $globalStats = $this->getGlobalStats();
        $poolHealths = $this->getAllHealthStatus();
        
        $health = 'healthy';
        $criticalPools = 0;
        $warningPools = 0;
        
        foreach ($poolHealths as $poolHealth) {
            if ($poolHealth['status'] === 'critical') {
                $criticalPools++;
            } elseif ($poolHealth['status'] === 'warning') {
                $warningPools++;
            }
        }
        
        if ($criticalPools > 0) {
            $health = 'critical';
        } elseif ($warningPools > 0) {
            $health = 'warning';
        }
        
        return [
            'status' => $health,
            'global_stats' => $globalStats,
            'pool_healths' => $poolHealths,
            'critical_pools' => $criticalPools,
            'warning_pools' => $warningPools,
            'timestamp' => now()->toISOString()
        ];
    }
}