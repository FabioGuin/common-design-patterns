<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servizio per gestire il database condiviso
 * 
 * Questo servizio dimostra i problemi del Shared Database Anti-pattern
 * dove tutti i servizi condividono lo stesso database.
 */
class SharedDatabaseService
{
    private string $id;
    private array $connectionStats;
    private array $lockHistory;
    private array $conflictHistory;
    private int $totalOperations;
    private int $failedOperations;
    private array $performanceMetrics;

    public function __construct()
    {
        $this->id = 'shared-db-' . uniqid();
        $this->connectionStats = [
            'active_connections' => 0,
            'max_connections' => 100,
            'connection_errors' => 0,
            'last_connection_time' => null
        ];
        $this->lockHistory = [];
        $this->conflictHistory = [];
        $this->totalOperations = 0;
        $this->failedOperations = 0;
        $this->performanceMetrics = [
            'avg_query_time' => 0,
            'slow_queries' => 0,
            'lock_wait_time' => 0,
            'deadlocks' => 0
        ];
        
        Log::info('SharedDatabaseService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Simula una connessione al database condiviso
     */
    public function getConnection(): object
    {
        $this->connectionStats['active_connections']++;
        $this->connectionStats['last_connection_time'] = now()->toISOString();
        
        // Simula problemi di connessione
        if (rand(1, 100) <= 5) { // 5% di probabilità di errore
            $this->connectionStats['connection_errors']++;
            throw new Exception('Database connection failed - too many connections');
        }
        
        Log::debug('Database connection established', [
            'service' => $this->id,
            'active_connections' => $this->connectionStats['active_connections']
        ]);
        
        return DB::connection('shared_database');
    }

    /**
     * Simula l'acquisizione di un lock su una tabella
     */
    public function acquireLock(string $table, string $operation = 'read'): bool
    {
        $lockId = $table . '_' . $operation . '_' . uniqid();
        $lockTime = microtime(true);
        
        // Simula conflitti di lock
        if (rand(1, 100) <= 15) { // 15% di probabilità di conflitto
            $this->conflictHistory[] = [
                'table' => $table,
                'operation' => $operation,
                'timestamp' => now()->toISOString(),
                'error' => 'Lock timeout - table locked by another service'
            ];
            
            Log::warning('Lock conflict detected', [
                'table' => $table,
                'operation' => $operation,
                'service' => $this->id
            ]);
            
            return false;
        }
        
        $this->lockHistory[] = [
            'id' => $lockId,
            'table' => $table,
            'operation' => $operation,
            'acquired_at' => $lockTime,
            'service' => $this->id
        ];
        
        Log::debug('Lock acquired', [
            'lock_id' => $lockId,
            'table' => $table,
            'operation' => $operation,
            'service' => $this->id
        ]);
        
        return true;
    }

    /**
     * Rilascia un lock su una tabella
     */
    public function releaseLock(string $table, string $operation = 'read'): bool
    {
        $lockIndex = array_search($table . '_' . $operation, array_column($this->lockHistory, 'table'));
        
        if ($lockIndex !== false) {
            $lock = $this->lockHistory[$lockIndex];
            $lockDuration = microtime(true) - $lock['acquired_at'];
            
            unset($this->lockHistory[$lockIndex]);
            $this->lockHistory = array_values($this->lockHistory);
            
            Log::debug('Lock released', [
                'table' => $table,
                'operation' => $operation,
                'duration' => $lockDuration,
                'service' => $this->id
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Simula una transazione distribuita complessa
     */
    public function executeDistributedTransaction(array $operations): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            $results = [];
            $locks = [];
            
            // Acquisisce lock su tutte le tabelle coinvolte
            foreach ($operations as $operation) {
                $table = $operation['table'];
                $op = $operation['operation'];
                
                if (!$this->acquireLock($table, $op)) {
                    throw new Exception("Failed to acquire lock on table: $table");
                }
                
                $locks[] = ['table' => $table, 'operation' => $op];
            }
            
            // Simula l'esecuzione delle operazioni
            foreach ($operations as $operation) {
                $result = $this->executeOperation($operation);
                $results[] = $result;
            }
            
            // Rilascia tutti i lock
            foreach ($locks as $lock) {
                $this->releaseLock($lock['table'], $lock['operation']);
            }
            
            $duration = microtime(true) - $startTime;
            $this->updatePerformanceMetrics($duration);
            
            Log::info('Distributed transaction completed', [
                'service' => $this->id,
                'operations_count' => count($operations),
                'duration' => $duration
            ]);
            
            return [
                'success' => true,
                'results' => $results,
                'duration' => $duration,
                'locks_acquired' => count($locks)
            ];
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            // Rilascia tutti i lock in caso di errore
            foreach ($locks as $lock) {
                $this->releaseLock($lock['table'], $lock['operation']);
            }
            
            Log::error('Distributed transaction failed', [
                'service' => $this->id,
                'error' => $e->getMessage(),
                'operations' => $operations
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'duration' => microtime(true) - $startTime
            ];
        }
    }

    /**
     * Simula l'esecuzione di una singola operazione
     */
    private function executeOperation(array $operation): array
    {
        $table = $operation['table'];
        $op = $operation['operation'];
        $data = $operation['data'] ?? [];
        
        // Simula delay per operazioni complesse
        usleep(rand(10000, 50000)); // 10-50ms
        
        // Simula errori occasionali
        if (rand(1, 100) <= 3) { // 3% di probabilità di errore
            throw new Exception("Operation failed on table: $table");
        }
        
        return [
            'table' => $table,
            'operation' => $op,
            'success' => true,
            'affected_rows' => rand(1, 10),
            'data' => $data
        ];
    }

    /**
     * Aggiorna le metriche di performance
     */
    private function updatePerformanceMetrics(float $duration): void
    {
        $this->performanceMetrics['avg_query_time'] = 
            ($this->performanceMetrics['avg_query_time'] + $duration) / 2;
        
        if ($duration > 1.0) { // Query lenta
            $this->performanceMetrics['slow_queries']++;
        }
        
        if ($duration > 5.0) { // Possibile deadlock
            $this->performanceMetrics['deadlocks']++;
        }
    }

    /**
     * Simula un deadlock tra servizi
     */
    public function simulateDeadlock(): array
    {
        $this->performanceMetrics['deadlocks']++;
        
        $deadlock = [
            'id' => 'deadlock_' . uniqid(),
            'timestamp' => now()->toISOString(),
            'services_involved' => ['UserService', 'OrderService', 'PaymentService'],
            'tables_locked' => ['users', 'orders', 'payments'],
            'error' => 'Deadlock detected: circular wait condition'
        ];
        
        $this->conflictHistory[] = $deadlock;
        
        Log::error('Deadlock detected', $deadlock);
        
        return $deadlock;
    }

    /**
     * Ottiene le statistiche del database condiviso
     */
    public function getStats(): array
    {
        return [
            'id' => $this->id,
            'database' => 'shared_database',
            'connection_stats' => $this->connectionStats,
            'performance_metrics' => $this->performanceMetrics,
            'total_operations' => $this->totalOperations,
            'failed_operations' => $this->failedOperations,
            'success_rate' => $this->totalOperations > 0 
                ? round((($this->totalOperations - $this->failedOperations) / $this->totalOperations) * 100, 2)
                : 100,
            'active_locks' => count($this->lockHistory),
            'total_conflicts' => count($this->conflictHistory),
            'deadlocks' => $this->performanceMetrics['deadlocks']
        ];
    }

    /**
     * Ottiene la cronologia dei conflitti
     */
    public function getConflictHistory(): array
    {
        return $this->conflictHistory;
    }

    /**
     * Ottiene la cronologia dei lock
     */
    public function getLockHistory(): array
    {
        return $this->lockHistory;
    }

    /**
     * Simula la pulizia delle risorse
     */
    public function cleanup(): void
    {
        // Rilascia tutti i lock attivi
        $this->lockHistory = [];
        
        // Reset delle connessioni
        $this->connectionStats['active_connections'] = 0;
        
        Log::info('SharedDatabaseService cleanup completed', ['id' => $this->id]);
    }
}
