<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servizio per la gestione degli utenti
 * 
 * Questo servizio dimostra i problemi del Shared Database Anti-pattern
 * dove il servizio è fortemente accoppiato al database condiviso.
 */
class UserService
{
    private string $id;
    private SharedDatabaseService $sharedDb;
    private array $operationHistory;
    private int $totalOperations;
    private int $failedOperations;

    public function __construct(SharedDatabaseService $sharedDb)
    {
        $this->id = 'user-service-' . uniqid();
        $this->sharedDb = $sharedDb;
        $this->operationHistory = [];
        $this->totalOperations = 0;
        $this->failedOperations = 0;
        
        Log::info('UserService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Crea un nuovo utente
     * 
     * Problema: Utilizza il database condiviso, causando accoppiamento forte
     */
    public function createUser(array $data): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock sulla tabella users
            if (!$this->sharedDb->acquireLock('users', 'write')) {
                throw new Exception('Failed to acquire lock on users table');
            }
            
            // Simula la creazione dell'utente
            $user = new User($data);
            $user->save();
            
            $this->sharedDb->releaseLock('users', 'write');
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'database' => 'shared_database',
                'table' => 'users',
                'created_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'create_user',
                'user_id' => $user->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('User created successfully', [
                'service' => $this->id,
                'user_id' => $user->id,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('users', 'write');
            
            $this->operationHistory[] = [
                'operation' => 'create_user',
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to create user', [
                'service' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Aggiorna un utente esistente
     * 
     * Problema: Modifiche al schema users impattano altri servizi
     */
    public function updateUser(int $userId, array $data): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock sulla tabella users
            if (!$this->sharedDb->acquireLock('users', 'write')) {
                throw new Exception('Failed to acquire lock on users table');
            }
            
            // Simula l'aggiornamento dell'utente
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            foreach ($data as $key => $value) {
                $user->$key = $value;
            }
            $user->save();
            
            $this->sharedDb->releaseLock('users', 'write');
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'database' => 'shared_database',
                'table' => 'users',
                'updated_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'update_user',
                'user_id' => $user->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('User updated successfully', [
                'service' => $this->id,
                'user_id' => $user->id,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('users', 'write');
            
            $this->operationHistory[] = [
                'operation' => 'update_user',
                'user_id' => $userId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to update user', [
                'service' => $this->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Elimina un utente
     * 
     * Problema: Eliminazione di utenti impatta ordini e pagamenti
     */
    public function deleteUser(int $userId): bool
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock su multiple tabelle
            $tables = ['users', 'orders', 'payments'];
            foreach ($tables as $table) {
                if (!$this->sharedDb->acquireLock($table, 'write')) {
                    throw new Exception("Failed to acquire lock on $table table");
                }
            }
            
            // Simula la verifica delle dipendenze
            $user = User::find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Simula la verifica degli ordini esistenti
            $hasOrders = rand(1, 100) <= 30; // 30% di probabilità di avere ordini
            if ($hasOrders) {
                throw new Exception('Cannot delete user: has existing orders');
            }
            
            // Simula la verifica dei pagamenti esistenti
            $hasPayments = rand(1, 100) <= 20; // 20% di probabilità di avere pagamenti
            if ($hasPayments) {
                throw new Exception('Cannot delete user: has existing payments');
            }
            
            // Simula l'eliminazione
            $user->delete();
            
            // Rilascia tutti i lock
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $duration = microtime(true) - $startTime;
            
            $this->operationHistory[] = [
                'operation' => 'delete_user',
                'user_id' => $userId,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('User deleted successfully', [
                'service' => $this->id,
                'user_id' => $userId,
                'duration' => $duration
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            // Rilascia tutti i lock in caso di errore
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $this->operationHistory[] = [
                'operation' => 'delete_user',
                'user_id' => $userId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to delete user', [
                'service' => $this->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene un utente per ID
     * 
     * Problema: Query su database condiviso con possibili conflitti
     */
    public function getUser(int $userId): ?array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock di lettura
            if (!$this->sharedDb->acquireLock('users', 'read')) {
                throw new Exception('Failed to acquire read lock on users table');
            }
            
            // Simula la query
            $user = User::find($userId);
            
            $this->sharedDb->releaseLock('users', 'read');
            
            $duration = microtime(true) - $startTime;
            
            if (!$user) {
                return null;
            }
            
            $result = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'database' => 'shared_database',
                'table' => 'users',
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'get_user',
                'user_id' => $user->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('users', 'read');
            
            $this->operationHistory[] = [
                'operation' => 'get_user',
                'user_id' => $userId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to get user', [
                'service' => $this->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene tutti gli utenti
     * 
     * Problema: Query su database condiviso con possibili conflitti
     */
    public function getAllUsers(): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock di lettura
            if (!$this->sharedDb->acquireLock('users', 'read')) {
                throw new Exception('Failed to acquire read lock on users table');
            }
            
            // Simula la query
            $users = User::all();
            
            $this->sharedDb->releaseLock('users', 'read');
            
            $duration = microtime(true) - $startTime;
            
            $result = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'database' => 'shared_database',
                    'table' => 'users'
                ];
            })->toArray();
            
            $this->operationHistory[] = [
                'operation' => 'get_all_users',
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true,
                'count' => count($result)
            ];
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('users', 'read');
            
            $this->operationHistory[] = [
                'operation' => 'get_all_users',
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to get all users', [
                'service' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        return [
            'id' => $this->id,
            'service' => 'UserService',
            'database' => 'shared_database',
            'table' => 'users',
            'total_operations' => $this->totalOperations,
            'failed_operations' => $this->failedOperations,
            'success_rate' => $this->totalOperations > 0 
                ? round((($this->totalOperations - $this->failedOperations) / $this->totalOperations) * 100, 2)
                : 100,
            'operation_history' => $this->operationHistory,
            'coupling_level' => 'high', // Alto accoppiamento con database condiviso
            'scalability_issues' => [
                'shared_database' => true,
                'table_locks' => true,
                'schema_dependencies' => true
            ]
        ];
    }

    /**
     * Ottiene la cronologia delle operazioni
     */
    public function getOperationHistory(): array
    {
        return $this->operationHistory;
    }
}
