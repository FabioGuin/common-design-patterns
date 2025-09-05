<?php

namespace App\UnitOfWork;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitOfWork implements UnitOfWorkInterface
{
    private array $newEntities = [];
    private array $dirtyEntities = [];
    private array $deletedEntities = [];
    private array $cleanEntities = [];
    private bool $inTransaction = false;
    private array $entityTypes = [];

    public function begin(): void
    {
        if ($this->inTransaction) {
            throw new \Exception('Transaction already started');
        }
        
        $this->inTransaction = true;
        DB::beginTransaction();
        
        Log::info('Unit of Work: Transaction started');
    }

    public function commit(): void
    {
        if (!$this->inTransaction) {
            throw new \Exception('No active transaction');
        }
        
        try {
            Log::info('Unit of Work: Committing transaction', [
                'new_entities' => count($this->newEntities),
                'dirty_entities' => count($this->dirtyEntities),
                'deleted_entities' => count($this->deletedEntities)
            ]);
            
            $this->executeInserts();
            $this->executeUpdates();
            $this->executeDeletes();
            
            DB::commit();
            $this->clear();
            
            Log::info('Unit of Work: Transaction committed successfully');
        } catch (\Exception $e) {
            Log::error('Unit of Work: Transaction failed, rolling back', [
                'error' => $e->getMessage()
            ]);
            
            $this->rollback();
            throw $e;
        }
    }

    public function rollback(): void
    {
        if ($this->inTransaction) {
            DB::rollback();
            $this->clear();
            
            Log::info('Unit of Work: Transaction rolled back');
        }
    }

    public function registerNew($entity): void
    {
        $this->validateEntity($entity);
        $this->newEntities[] = $entity;
        $this->entityTypes[spl_object_hash($entity)] = 'new';
        
        Log::debug('Unit of Work: Entity registered as new', [
            'entity_class' => get_class($entity),
            'entity_id' => $entity->id ?? 'new'
        ]);
    }

    public function registerDirty($entity): void
    {
        $this->validateEntity($entity);
        $this->dirtyEntities[] = $entity;
        $this->entityTypes[spl_object_hash($entity)] = 'dirty';
        
        Log::debug('Unit of Work: Entity registered as dirty', [
            'entity_class' => get_class($entity),
            'entity_id' => $entity->id ?? 'unknown'
        ]);
    }

    public function registerDeleted($entity): void
    {
        $this->validateEntity($entity);
        $this->deletedEntities[] = $entity;
        $this->entityTypes[spl_object_hash($entity)] = 'deleted';
        
        Log::debug('Unit of Work: Entity registered as deleted', [
            'entity_class' => get_class($entity),
            'entity_id' => $entity->id ?? 'unknown'
        ]);
    }

    public function registerClean($entity): void
    {
        $this->validateEntity($entity);
        $this->cleanEntities[] = $entity;
        $this->entityTypes[spl_object_hash($entity)] = 'clean';
        
        Log::debug('Unit of Work: Entity registered as clean', [
            'entity_class' => get_class($entity),
            'entity_id' => $entity->id ?? 'unknown'
        ]);
    }

    public function isInTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function getEntityCount(): int
    {
        return count($this->newEntities) + 
               count($this->dirtyEntities) + 
               count($this->deletedEntities) + 
               count($this->cleanEntities);
    }

    public function getEntities(string $type): array
    {
        return match ($type) {
            'new' => $this->newEntities,
            'dirty' => $this->dirtyEntities,
            'deleted' => $this->deletedEntities,
            'clean' => $this->cleanEntities,
            default => throw new \InvalidArgumentException("Invalid entity type: {$type}")
        };
    }

    public function clear(): void
    {
        $this->newEntities = [];
        $this->dirtyEntities = [];
        $this->deletedEntities = [];
        $this->cleanEntities = [];
        $this->entityTypes = [];
        $this->inTransaction = false;
        
        Log::debug('Unit of Work: All entities cleared');
    }

    public function isRegistered($entity): bool
    {
        return isset($this->entityTypes[spl_object_hash($entity)]);
    }

    public function getEntityType($entity): ?string
    {
        return $this->entityTypes[spl_object_hash($entity)] ?? null;
    }

    /**
     * Esegue tutte le operazioni di inserimento
     */
    private function executeInserts(): void
    {
        foreach ($this->newEntities as $entity) {
            try {
                $entity->save();
                Log::debug('Unit of Work: Entity inserted', [
                    'entity_class' => get_class($entity),
                    'entity_id' => $entity->id
                ]);
            } catch (\Exception $e) {
                Log::error('Unit of Work: Failed to insert entity', [
                    'entity_class' => get_class($entity),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Esegue tutte le operazioni di aggiornamento
     */
    private function executeUpdates(): void
    {
        foreach ($this->dirtyEntities as $entity) {
            try {
                $entity->save();
                Log::debug('Unit of Work: Entity updated', [
                    'entity_class' => get_class($entity),
                    'entity_id' => $entity->id
                ]);
            } catch (\Exception $e) {
                Log::error('Unit of Work: Failed to update entity', [
                    'entity_class' => get_class($entity),
                    'entity_id' => $entity->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Esegue tutte le operazioni di eliminazione
     */
    private function executeDeletes(): void
    {
        foreach ($this->deletedEntities as $entity) {
            try {
                $entity->delete();
                Log::debug('Unit of Work: Entity deleted', [
                    'entity_class' => get_class($entity),
                    'entity_id' => $entity->id
                ]);
            } catch (\Exception $e) {
                Log::error('Unit of Work: Failed to delete entity', [
                    'entity_class' => get_class($entity),
                    'entity_id' => $entity->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Valida che l'entità sia valida
     */
    private function validateEntity($entity): void
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException('Entity must be an object');
        }

        if (!method_exists($entity, 'save') && !method_exists($entity, 'delete')) {
            throw new \InvalidArgumentException('Entity must have save() or delete() methods');
        }
    }

    /**
     * Ottiene statistiche delle entità registrate
     */
    public function getStatistics(): array
    {
        return [
            'new_entities' => count($this->newEntities),
            'dirty_entities' => count($this->dirtyEntities),
            'deleted_entities' => count($this->deletedEntities),
            'clean_entities' => count($this->cleanEntities),
            'total_entities' => $this->getEntityCount(),
            'in_transaction' => $this->inTransaction
        ];
    }

    /**
     * Verifica se ci sono entità registrate
     */
    public function hasEntities(): bool
    {
        return $this->getEntityCount() > 0;
    }

    /**
     * Ottiene un riepilogo delle entità per tipo
     */
    public function getEntitySummary(): array
    {
        $summary = [];
        
        foreach ($this->newEntities as $entity) {
            $class = get_class($entity);
            $summary[$class]['new'] = ($summary[$class]['new'] ?? 0) + 1;
        }
        
        foreach ($this->dirtyEntities as $entity) {
            $class = get_class($entity);
            $summary[$class]['dirty'] = ($summary[$class]['dirty'] ?? 0) + 1;
        }
        
        foreach ($this->deletedEntities as $entity) {
            $class = get_class($entity);
            $summary[$class]['deleted'] = ($summary[$class]['deleted'] ?? 0) + 1;
        }
        
        foreach ($this->cleanEntities as $entity) {
            $class = get_class($entity);
            $summary[$class]['clean'] = ($summary[$class]['clean'] ?? 0) + 1;
        }
        
        return $summary;
    }
}
