<?php

namespace App\Models;

use App\Traits\Poolable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseConnection
{
    use Poolable;

    private \PDO $connection;
    private string $connectionName;
    private array $config;
    private bool $isConnected = false;

    public function __construct(string $connectionName = 'mysql')
    {
        $this->connectionName = $connectionName;
        $this->config = config("database.connections.{$connectionName}");
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']}";
            
            $this->connection = new \PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_PERSISTENT => false,
                ]
            );
            
            $this->isConnected = true;
            Log::info("Database connection established for {$this->connectionName}");
            
        } catch (\PDOException $e) {
            Log::error("Failed to connect to database {$this->connectionName}: " . $e->getMessage());
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): array
    {
        if (!$this->isConnected) {
            throw new \Exception("Database connection is not established");
        }

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            Log::error("Database query failed: " . $e->getMessage());
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    public function execute(string $sql, array $params = []): int
    {
        if (!$this->isConnected) {
            throw new \Exception("Database connection is not established");
        }

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            Log::error("Database execute failed: " . $e->getMessage());
            throw new \Exception("Execute failed: " . $e->getMessage());
        }
    }

    public function beginTransaction(): bool
    {
        if (!$this->isConnected) {
            throw new \Exception("Database connection is not established");
        }

        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        if (!$this->isConnected) {
            throw new \Exception("Database connection is not established");
        }

        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        if (!$this->isConnected) {
            throw new \Exception("Database connection is not established");
        }

        return $this->connection->rollback();
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
            $this->connection->query('SELECT 1');
            return true;
        } catch (\PDOException $e) {
            Log::warning("Database ping failed: " . $e->getMessage());
            return false;
        }
    }

    protected function onRelease(): void
    {
        // Chiudi eventuali transazioni aperte
        if ($this->connection->inTransaction()) {
            $this->connection->rollback();
        }
        
        // Reset di eventuali prepared statements
        $this->connection->exec("SET SESSION sql_mode = ''");
    }

    protected function onReset(): void
    {
        // Reset completo della connessione
        $this->onRelease();
        
        // Riconnetti se necessario
        if (!$this->ping()) {
            $this->connect();
        }
    }

    public function __destruct()
    {
        if ($this->isConnected) {
            $this->connection = null;
            $this->isConnected = false;
        }
    }
}
