<?php

namespace App\Sharding;

use App\Models\ShardingMetric;
use App\Models\ShardingEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShardingManager
{
    private array $shardingConfigs = [];
    private array $shardConnections = [];

    public function __construct()
    {
        $this->initializeShardingConfigs();
        $this->initializeShardConnections();
    }

    public function getShardForKey(string $entity, mixed $key): string
    {
        $config = $this->getShardingConfig($entity);
        
        switch ($config['strategy']) {
            case 'key_based':
                return $this->getShardByKey($key, $config);
            case 'range_based':
                return $this->getShardByRange($key, $config);
            case 'hash_based':
                return $this->getShardByHash($key, $config);
            case 'directory_based':
                return $this->getShardByDirectory($key, $config);
            default:
                throw new \InvalidArgumentException("Unknown sharding strategy: {$config['strategy']}");
        }
    }

    public function executeQuery(string $entity, mixed $key, string $query, array $bindings = []): mixed
    {
        $shard = $this->getShardForKey($entity, $key);
        $connection = $this->getConnection($shard);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $result = $connection->select($query, $bindings);
            $this->recordSuccess($entity, $shard, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory);
            return $result;
        } catch (\Exception $e) {
            $this->recordError($entity, $shard, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory, $e);
            throw $e;
        }
    }

    public function executeQueryOnAllShards(string $entity, string $query, array $bindings = []): array
    {
        $results = [];
        $config = $this->getShardingConfig($entity);
        
        foreach ($config['shards'] as $shardName => $shardConfig) {
            try {
                $connection = $this->getConnection($shardName);
                $result = $connection->select($query, $bindings);
                $results[$shardName] = $result;
            } catch (\Exception $e) {
                Log::error("Error executing query on shard {$shardName}: " . $e->getMessage());
                $results[$shardName] = [];
            }
        }

        return $this->mergeResults($results);
    }

    public function getShardingStatus(string $entity): array
    {
        $config = $this->getShardingConfig($entity);
        $status = [];

        foreach ($config['shards'] as $shardName => $shardConfig) {
            try {
                $connection = $this->getConnection($shardName);
                $status[$shardName] = [
                    'name' => $shardName,
                    'host' => $shardConfig['host'],
                    'database' => $shardConfig['database'],
                    'status' => 'connected',
                    'record_count' => $this->getRecordCount($connection, $entity),
                    'last_updated' => now(),
                ];
            } catch (\Exception $e) {
                $status[$shardName] = [
                    'name' => $shardName,
                    'host' => $shardConfig['host'],
                    'database' => $shardConfig['database'],
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'last_updated' => now(),
                ];
            }
        }

        return $status;
    }

    public function getAllShardingConfigs(): array
    {
        return $this->shardingConfigs;
    }

    private function getShardByKey(mixed $key, array $config): string
    {
        $shardIndex = $key % $config['shard_count'];
        return $config['shards'][$shardIndex];
    }

    private function getShardByRange(mixed $key, array $config): string
    {
        foreach ($config['ranges'] as $shardName => $range) {
            if ($key >= $range['min'] && $key <= $range['max']) {
                return $shardName;
            }
        }
        throw new \InvalidArgumentException("No shard found for key: {$key}");
    }

    private function getShardByHash(mixed $key, array $config): string
    {
        $hash = hash($config['hash_function'], (string) $key);
        $shardIndex = hexdec(substr($hash, 0, 8)) % $config['shard_count'];
        return $config['shards'][$shardIndex];
    }

    private function getShardByDirectory(mixed $key, array $config): string
    {
        return $config['directory'][$key] ?? $config['default_shard'];
    }

    private function getConnection(string $shard): \Illuminate\Database\Connection
    {
        if (!isset($this->shardConnections[$shard])) {
            $config = $this->getShardConfig($shard);
            $this->shardConnections[$shard] = DB::connection($config['connection']);
        }

        return $this->shardConnections[$shard];
    }

    private function getShardConfig(string $shard): array
    {
        foreach ($this->shardingConfigs as $entityConfig) {
            if (isset($entityConfig['shards'][$shard])) {
                return $entityConfig['shards'][$shard];
            }
        }
        throw new \InvalidArgumentException("Shard not found: {$shard}");
    }

    private function getRecordCount(\Illuminate\Database\Connection $connection, string $entity): int
    {
        try {
            $table = $this->getTableName($entity);
            return $connection->table($table)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTableName(string $entity): string
    {
        return match ($entity) {
            'users' => 'users',
            'products' => 'products',
            'orders' => 'orders',
            'categories' => 'categories',
            default => $entity
        };
    }

    private function mergeResults(array $results): array
    {
        $merged = [];
        foreach ($results as $shardResults) {
            $merged = array_merge($merged, $shardResults);
        }
        return $merged;
    }

    private function recordSuccess(string $entity, string $shard, mixed $key, float $executionTime, int $memoryUsed): void
    {
        if (!config('sharding.monitoring.enabled', true)) {
            return;
        }

        ShardingMetric::create([
            'entity' => $entity,
            'shard' => $shard,
            'key' => (string) $key,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_queries' => 1,
            'successful_queries' => 1,
            'failed_queries' => 0,
            'created_at' => now(),
        ]);
    }

    private function recordError(string $entity, string $shard, mixed $key, float $executionTime, int $memoryUsed, \Exception $e): void
    {
        if (!config('sharding.monitoring.enabled', true)) {
            return;
        }

        ShardingMetric::create([
            'entity' => $entity,
            'shard' => $shard,
            'key' => (string) $key,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_queries' => 1,
            'successful_queries' => 0,
            'failed_queries' => 1,
            'created_at' => now(),
        ]);

        ShardingEvent::create([
            'entity' => $entity,
            'shard' => $shard,
            'key' => (string) $key,
            'event_type' => 'error',
            'error_message' => $e->getMessage(),
            'created_at' => now(),
        ]);

        Log::error("Sharding error for {$entity} on {$shard}: " . $e->getMessage());
    }

    private function initializeShardingConfigs(): void
    {
        $configs = config('sharding.entities', []);

        foreach ($configs as $entity => $config) {
            $this->shardingConfigs[$entity] = $config;
        }
    }

    private function initializeShardConnections(): void
    {
        // Le connessioni vengono inizializzate lazy quando necessario
    }
}
