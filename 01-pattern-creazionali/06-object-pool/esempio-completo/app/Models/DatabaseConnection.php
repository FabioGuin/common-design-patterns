<?php

namespace App\Models;

interface PoolableInterface
{
    public function reset(): void;
    public function isInUse(): bool;
    public function setInUse(bool $inUse): void;
}

class DatabaseConnection implements PoolableInterface
{
    public string $id;
    public string $host;
    public string $database;
    public bool $inUse;
    public int $createdAt;

    public function __construct(string $host = 'localhost', string $database = 'default')
    {
        $this->id = uniqid('conn_', true);
        $this->host = $host;
        $this->database = $database;
        $this->inUse = false;
        $this->createdAt = time();
    }

    public function reset(): void
    {
        $this->inUse = false;
    }

    public function isInUse(): bool
    {
        return $this->inUse;
    }

    public function setInUse(bool $inUse): void
    {
        $this->inUse = $inUse;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'host' => $this->host,
            'database' => $this->database,
            'in_use' => $this->inUse,
            'created_at' => now()->toDateTimeString()
        ];
    }
}

class ConnectionPool
{
    private array $pool = [];
    private int $maxSize;
    private int $currentSize = 0;

    public function __construct(int $maxSize = 5)
    {
        $this->maxSize = $maxSize;
    }

    public function acquire(): ?DatabaseConnection
    {
        // Cerca una connessione disponibile nel pool
        foreach ($this->pool as $connection) {
            if (!$connection->isInUse()) {
                $connection->setInUse(true);
                return $connection;
            }
        }

        // Se non ci sono connessioni disponibili e possiamo crearne una nuova
        if ($this->currentSize < $this->maxSize) {
            $connection = new DatabaseConnection();
            $connection->setInUse(true);
            $this->pool[] = $connection;
            $this->currentSize++;
            return $connection;
        }

        return null; // Pool pieno
    }

    public function release(DatabaseConnection $connection): void
    {
        $connection->reset();
    }

    public function getPoolStatus(): array
    {
        $available = 0;
        $inUse = 0;

        foreach ($this->pool as $connection) {
            if ($connection->isInUse()) {
                $inUse++;
            } else {
                $available++;
            }
        }

        return [
            'total' => $this->currentSize,
            'available' => $available,
            'in_use' => $inUse,
            'max_size' => $this->maxSize
        ];
    }

    public function getPoolConnections(): array
    {
        return array_map(fn($conn) => $conn->toArray(), $this->pool);
    }
}
