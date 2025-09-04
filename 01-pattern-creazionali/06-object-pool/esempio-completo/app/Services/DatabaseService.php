<?php

namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Illuminate\Support\Facades\Log;

class DatabaseService
{
    private ConnectionPool $pool;
    private string $poolName;

    public function __construct(string $poolName = 'default')
    {
        $this->poolName = $poolName;
        $this->pool = PoolManager::getInstance()->getPool($poolName);
    }

    public function processUsers(array $userIds): array
    {
        $results = [];
        $errors = [];
        
        foreach ($userIds as $userId) {
            try {
                $user = $this->getUserById($userId);
                if ($user) {
                    $results[] = $user;
                }
            } catch (Exception $e) {
                $errors[] = [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ];
                Log::error("Errore processando utente {$userId}: " . $e->getMessage());
            }
        }
        
        return [
            'users' => $results,
            'errors' => $errors,
            'total_processed' => count($userIds),
            'successful' => count($results),
            'failed' => count($errors)
        ];
    }

    public function getUserById(int $userId): ?array
    {
        $connection = $this->pool->acquire();
        
        try {
            $stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            return $user ?: null;
            
        } catch (PDOException $e) {
            Log::error("Errore query utente {$userId}: " . $e->getMessage());
            throw new Exception("Errore database: " . $e->getMessage());
        } finally {
            $this->pool->release($connection);
        }
    }

    public function getUsersByRole(string $role, int $limit = 100): array
    {
        $connection = $this->pool->acquire();
        
        try {
            $stmt = $connection->prepare("SELECT * FROM users WHERE role = ? LIMIT ?");
            $stmt->execute([$role, $limit]);
            $users = $stmt->fetchAll();
            
            return $users;
            
        } catch (PDOException $e) {
            Log::error("Errore query utenti per ruolo {$role}: " . $e->getMessage());
            throw new Exception("Errore database: " . $e->getMessage());
        } finally {
            $this->pool->release($connection);
        }
    }

    public function createUser(array $userData): int
    {
        $connection = $this->pool->acquire();
        
        try {
            $connection->beginTransaction();
            
            $stmt = $connection->prepare("
                INSERT INTO users (name, email, role, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $userData['name'],
                $userData['email'],
                $userData['role'] ?? 'user'
            ]);
            
            $userId = $connection->lastInsertId();
            $connection->commit();
            
            Log::info("Utente creato con ID: {$userId}");
            return (int) $userId;
            
        } catch (PDOException $e) {
            $connection->rollBack();
            Log::error("Errore creazione utente: " . $e->getMessage());
            throw new Exception("Errore creazione utente: " . $e->getMessage());
        } finally {
            $this->pool->release($connection);
        }
    }

    public function updateUser(int $userId, array $userData): bool
    {
        $connection = $this->pool->acquire();
        
        try {
            $connection->beginTransaction();
            
            $fields = [];
            $values = [];
            
            foreach ($userData as $field => $value) {
                $fields[] = "{$field} = ?";
                $values[] = $value;
            }
            
            $values[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $connection->prepare($sql);
            $result = $stmt->execute($values);
            
            $connection->commit();
            
            Log::info("Utente {$userId} aggiornato");
            return $result;
            
        } catch (PDOException $e) {
            $connection->rollBack();
            Log::error("Errore aggiornamento utente {$userId}: " . $e->getMessage());
            throw new Exception("Errore aggiornamento utente: " . $e->getMessage());
        } finally {
            $this->pool->release($connection);
        }
    }

    public function deleteUser(int $userId): bool
    {
        $connection = $this->pool->acquire();
        
        try {
            $connection->beginTransaction();
            
            $stmt = $connection->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$userId]);
            
            $connection->commit();
            
            Log::info("Utente {$userId} eliminato");
            return $result;
            
        } catch (PDOException $e) {
            $connection->rollBack();
            Log::error("Errore eliminazione utente {$userId}: " . $e->getMessage());
            throw new Exception("Errore eliminazione utente: " . $e->getMessage());
        } finally {
            $this->pool->release($connection);
        }
    }

    public function executeBatchQueries(array $queries): array
    {
        $results = [];
        $errors = [];
        
        foreach ($queries as $index => $query) {
            $connection = $this->pool->acquire();
            
            try {
                $stmt = $connection->prepare($query['sql']);
                $stmt->execute($query['params'] ?? []);
                
                $results[] = [
                    'index' => $index,
                    'result' => $stmt->fetchAll()
                ];
                
            } catch (PDOException $e) {
                $errors[] = [
                    'index' => $index,
                    'error' => $e->getMessage()
                ];
                Log::error("Errore query batch {$index}: " . $e->getMessage());
            } finally {
                $this->pool->release($connection);
            }
        }
        
        return [
            'results' => $results,
            'errors' => $errors,
            'total_queries' => count($queries),
            'successful' => count($results),
            'failed' => count($errors)
        ];
    }

    public function getPoolStats(): array
    {
        return $this->pool->getStats();
    }

    public function getPoolHealth(): array
    {
        return $this->pool->getHealthStatus();
    }
}
