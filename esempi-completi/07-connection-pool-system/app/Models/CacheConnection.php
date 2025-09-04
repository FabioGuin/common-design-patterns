<?php

namespace App\Models;

use App\Traits\Poolable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CacheConnection
{
    use Poolable;

    private string $connectionName;
    private $redis;
    private bool $isConnected = false;

    public function __construct(string $connectionName = 'default')
    {
        $this->connectionName = $connectionName;
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $this->redis = Redis::connection($this->connectionName);
            $this->isConnected = true;
            Log::info("Cache connection established for {$this->connectionName}");
            
        } catch (\Exception $e) {
            Log::error("Failed to connect to cache {$this->connectionName}: " . $e->getMessage());
            throw new \Exception("Cache connection failed: " . $e->getMessage());
        }
    }

    public function get(string $key)
    {
        if (!$this->isConnected) {
            throw new \Exception("Cache connection is not established");
        }

        try {
            return $this->redis->get($key);
        } catch (\Exception $e) {
            Log::error("Cache get failed: " . $e->getMessage());
            throw new \Exception("Cache get failed: " . $e->getMessage());
        }
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        if (!$this->isConnected) {
            throw new \Exception("Cache connection is not established");
        }

        try {
            if ($ttl) {
                return $this->redis->setex($key, $ttl, $value);
            } else {
                return $this->redis->set($key, $value);
            }
        } catch (\Exception $e) {
            Log::error("Cache set failed: " . $e->getMessage());
            throw new \Exception("Cache set failed: " . $e->getMessage());
        }
    }

    public function delete(string $key): bool
    {
        if (!$this->isConnected) {
            throw new \Exception("Cache connection is not established");
        }

        try {
            return $this->redis->del($key) > 0;
        } catch (\Exception $e) {
            Log::error("Cache delete failed: " . $e->getMessage());
            throw new \Exception("Cache delete failed: " . $e->getMessage());
        }
    }

    public function exists(string $key): bool
    {
        if (!$this->isConnected) {
            throw new \Exception("Cache connection is not established");
        }

        try {
            return $this->redis->exists($key) > 0;
        } catch (\Exception $e) {
            Log::error("Cache exists failed: " . $e->getMessage());
            throw new \Exception("Cache exists failed: " . $e->getMessage());
        }
    }

    public function increment(string $key, int $value = 1): int
    {
        if (!$this->isConnected) {
            throw new \Exception("Cache connection is not established");
        }

        try {
            return $this->redis->incrby($key, $value);
        } catch (\Exception $e) {
            Log::error("Cache increment failed: " . $e->getMessage());
            throw new \Exception("Cache increment failed: " . $e->getMessage());
        }
    }

    public function decrement(string $key, int $value = 1): int
    {
        if (!$this->isConnected) {
            throw new \Exception("Cache connection is not established");
        }

        try {
            return $this->redis->decrby($key, $value);
        } catch (\Exception $e) {
            Log::error("Cache decrement failed: " . $e->getMessage());
            throw new \Exception("Cache decrement failed: " . $e->getMessage());
        }
    }

    public function flush(): bool
    {
        if (!$this->isConnected) {
            throw new \Exception("Cache connection is not established");
        }

        try {
            return $this->redis->flushdb();
        } catch (\Exception $e) {
            Log::error("Cache flush failed: " . $e->getMessage());
            throw new \Exception("Cache flush failed: " . $e->getMessage());
        }
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    public function ping(): bool
    {
        if (!$this->isConnected) {
            return false;
        }

        try {
            $result = $this->redis->ping();
            return $result === 'PONG';
        } catch (\Exception $e) {
            Log::warning("Cache ping failed: " . $e->getMessage());
            return false;
        }
    }

    protected function onRelease(): void
        {
        // Reset di eventuali transazioni o pipeline
        try {
            if ($this->redis) {
                $this->redis->discard();
            }
        } catch (\Exception $e) {
            // Ignora errori di reset
        }
    }

    protected function onReset(): void
    {
        // Riconnetti se necessario
        if (!$this->ping()) {
            $this->connect();
        }
    }

    public function __destruct()
    {
        if ($this->isConnected && $this->redis) {
            $this->redis = null;
            $this->isConnected = false;
        }
    }
}
