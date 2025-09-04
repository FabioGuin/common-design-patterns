<?php

namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Illuminate\Support\Facades\Log;

class ConnectionPool
{
    private array $available = [];
    private array $inUse = [];
    private int $maxSize;
    private string $connectionName;
    private array $config;
    private int $timeout;
    private int $retryAttempts;

    public function __construct(
        string $connectionName = 'mysql',
        int $maxSize = 10,
        int $timeout = 30,
        int $retryAttempts = 3
    ) {
        $this->connectionName = $connectionName;
        $this->maxSize = $maxSize;
        $this->timeout = $timeout;
        $this->retryAttempts = $retryAttempts;
        $this->config = config("database.connections.{$connectionName}");
        
        if (!$this->config) {
            throw new Exception("Configurazione database '{$connectionName}' non trovata");
        }
    }

    public function acquire(): PDO
    {
        // Prova a prendere una connessione disponibile
        if (!empty($this->available)) {
            $connection = array_pop($this->available);
            $this->inUse[] = $connection;
            
            // Verifica che la connessione sia ancora valida
            if ($this->isConnectionValid($connection)) {
                return $connection;
            } else {
                // Rimuovi la connessione non valida
                $this->removeFromInUse($connection);
            }
        }

        // Crea una nuova connessione se siamo sotto il limite
        if (count($this->inUse) < $this->maxSize) {
            $connection = $this->createConnection();
            $this->inUse[] = $connection;
            return $connection;
        }

        throw new Exception('Connection pool esaurito. Max size: ' . $this->maxSize);
    }

    public function release(PDO $connection): void
    {
        $key = array_search($connection, $this->inUse, true);
        if ($key !== false) {
            unset($this->inUse[$key]);
            $this->inUse = array_values($this->inUse); // Re-index array
            
            // Reset della connessione
            $this->resetConnection($connection);
            
            // Verifica che la connessione sia ancora valida
            if ($this->isConnectionValid($connection)) {
                $this->available[] = $connection;
            } else {
                Log::warning('Connessione non valida rimossa dal pool');
            }
        }
    }

    private function createConnection(): PDO
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < $this->retryAttempts) {
            try {
                $dsn = $this->buildDsn();
                
                $pdo = new PDO(
                    $dsn,
                    $this->config['username'],
                    $this->config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_TIMEOUT => $this->timeout,
                        PDO::ATTR_PERSISTENT => false, // Non persistente per il pool
                    ]
                );

                Log::info("Nuova connessione creata per pool '{$this->connectionName}'");
                return $pdo;

            } catch (PDOException $e) {
                $lastException = $e;
                $attempts++;
                
                if ($attempts < $this->retryAttempts) {
                    Log::warning("Tentativo {$attempts} fallito per connessione pool: " . $e->getMessage());
                    usleep(100000); // 100ms di attesa
                }
            }
        }

        throw new Exception("Impossibile creare connessione dopo {$this->retryAttempts} tentativi: " . $lastException->getMessage());
    }

    private function buildDsn(): string
    {
        $host = $this->config['host'];
        $port = $this->config['port'] ?? 3306;
        $database = $this->config['database'];
        $charset = $this->config['charset'] ?? 'utf8mb4';

        return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
    }

    private function resetConnection(PDO $connection): void
    {
        try {
            // Chiudi eventuali transazioni aperte
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            
            // Reset di eventuali prepared statements
            $connection->exec("SET SESSION sql_mode = ''");
            
            // Reset di eventuali variabili di sessione
            $connection->exec("SET SESSION autocommit = 1");
            
        } catch (PDOException $e) {
            Log::warning("Errore durante reset connessione: " . $e->getMessage());
        }
    }

    private function isConnectionValid(PDO $connection): bool
    {
        try {
            $connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function removeFromInUse(PDO $connection): void
    {
        $key = array_search($connection, $this->inUse, true);
        if ($key !== false) {
            unset($this->inUse[$key]);
            $this->inUse = array_values($this->inUse);
        }
    }

    public function getStats(): array
    {
        return [
            'available' => count($this->available),
            'in_use' => count($this->inUse),
            'total' => count($this->available) + count($this->inUse),
            'max_size' => $this->maxSize,
            'connection_name' => $this->connectionName,
            'utilization_percentage' => $this->maxSize > 0 ? round((count($this->inUse) / $this->maxSize) * 100, 2) : 0
        ];
    }

    public function getHealthStatus(): array
    {
        $stats = $this->getStats();
        
        $health = 'healthy';
        if ($stats['utilization_percentage'] > 90) {
            $health = 'warning';
        }
        if ($stats['utilization_percentage'] >= 100) {
            $health = 'critical';
        }

        return [
            'status' => $health,
            'stats' => $stats,
            'timestamp' => now()->toISOString()
        ];
    }

    public function reset(): void
    {
        // Chiudi tutte le connessioni
        foreach ($this->available as $connection) {
            $connection = null;
        }
        foreach ($this->inUse as $connection) {
            $connection = null;
        }
        
        $this->available = [];
        $this->inUse = [];
        
        Log::info("Pool di connessioni '{$this->connectionName}' resettato");
    }

    public function __destruct()
    {
        $this->reset();
    }
}