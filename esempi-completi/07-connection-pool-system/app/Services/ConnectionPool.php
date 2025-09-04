<?php

namespace App\Services;

use App\Models\DatabaseConnection;
use Illuminate\Support\Facades\Log;

class ConnectionPool
{
    private array $available = [];
    private array $inUse = [];
    private int $maxSize;
    private string $connectionName;
    private int $created = 0;
    private int $acquired = 0;
    private int $released = 0;
    private int $failed = 0;

    public function __construct(string $connectionName = 'mysql', int $maxSize = 10)
    {
        $this->connectionName = $connectionName;
        $this->maxSize = $maxSize;
    }

    public function acquire(string $acquiredBy = null): DatabaseConnection
    {
        if (!empty($this->available)) {
            $connection = array_pop($this->available);
            $connection->acquire($acquiredBy);
            $this->inUse[] = $connection;
            $this->acquired++;
            
            Log::debug("Connection acquired from pool", [
                'connection_name' => $this->connectionName,
                'acquired_by' => $acquiredBy,
                'available' => count($this->available),
                'in_use' => count($this->inUse)
            ]);
            
            return $connection;
        }

        if (count($this->inUse) < $this->maxSize) {
            try {
                $connection = new DatabaseConnection($this->connectionName);
                $connection->acquire($acquiredBy);
                $this->inUse[] = $connection;
                $this->created++;
                $this->acquired++;
                
                Log::info("New connection created for pool", [
                    'connection_name' => $this->connectionName,
                    'acquired_by' => $acquiredBy,
                    'total_created' => $this->created
                ]);
                
                return $connection;
            } catch (\Exception $e) {
                $this->failed++;
                Log::error("Failed to create new connection", [
                    'connection_name' => $this->connectionName,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception("Connection pool esaurito: " . $e->getMessage());
            }
        }

        $this->failed++;
        throw new \Exception("Connection pool esaurito. Max size: {$this->maxSize}");
    }

    public function release(DatabaseConnection $connection): void
    {
        $key = array_search($connection, $this->inUse, true);
        if ($key !== false) {
            unset($this->inUse[$key]);
            $this->inUse = array_values($this->inUse); // Reindex array
            
            $connection->release();
            $this->available[] = $connection;
            $this->released++;
            
            Log::debug("Connection released to pool", [
                'connection_name' => $this->connectionName,
                'available' => count($this->available),
                'in_use' => count($this->inUse)
            ]);
        }
    }

    public function getStats(): array
    {
        return [
            'connection_name' => $this->connectionName,
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

        foreach ($this->available as $connection) {
            if ($connection->ping()) {
                $healthy++;
            } else {
                $unhealthy++;
            }
        }

        foreach ($this->inUse as $connection) {
            if ($connection->ping()) {
                $healthy++;
            } else {
                $unhealthy++;
            }
        }

        return [
            'total_connections' => $total,
            'healthy' => $healthy,
            'unhealthy' => $unhealthy,
            'health_percentage' => $total > 0 ? ($healthy / $total) * 100 : 0,
            'status' => $unhealthy === 0 ? 'healthy' : ($healthy > $unhealthy ? 'degraded' : 'unhealthy')
        ];
    }

    public function cleanup(): int
    {
        $removed = 0;
        
        // Rimuovi connessioni non sane dalla piscina disponibile
        foreach ($this->available as $key => $connection) {
            if (!$connection->ping()) {
                unset($this->available[$key]);
                $removed++;
            }
        }
        
        $this->available = array_values($this->available); // Reindex array
        
        Log::info("Pool cleanup completed", [
            'connection_name' => $this->connectionName,
            'removed_connections' => $removed,
            'remaining_available' => count($this->available)
        ]);
        
        return $removed;
    }

    public function reset(): void
    {
        // Reset di tutte le connessioni disponibili
        foreach ($this->available as $connection) {
            $connection->reset();
        }
        
        Log::info("Pool reset completed", [
            'connection_name' => $this->connectionName,
            'reset_connections' => count($this->available)
        ]);
    }

    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    public function setMaxSize(int $maxSize): void
    {
        $this->maxSize = $maxSize;
        
        Log::info("Pool max size updated", [
            'connection_name' => $this->connectionName,
            'new_max_size' => $maxSize
        ]);
    }

    public function __destruct()
    {
        // Chiudi tutte le connessioni
        foreach ($this->available as $connection) {
            unset($connection);
        }
        
        foreach ($this->inUse as $connection) {
            unset($connection);
        }
    }
}
