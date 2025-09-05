<?php

namespace App\UnitOfWork;

interface UnitOfWorkInterface
{
    /**
     * Inizia una nuova transazione
     */
    public function begin(): void;

    /**
     * Conferma la transazione corrente
     */
    public function commit(): void;

    /**
     * Annulla la transazione corrente
     */
    public function rollback(): void;

    /**
     * Registra una nuova entità per l'inserimento
     */
    public function registerNew($entity): void;

    /**
     * Registra un'entità modificata per l'aggiornamento
     */
    public function registerDirty($entity): void;

    /**
     * Registra un'entità per l'eliminazione
     */
    public function registerDeleted($entity): void;

    /**
     * Registra un'entità pulita (non modificata)
     */
    public function registerClean($entity): void;

    /**
     * Verifica se è attiva una transazione
     */
    public function isInTransaction(): bool;

    /**
     * Ottiene il numero di entità registrate
     */
    public function getEntityCount(): int;

    /**
     * Ottiene le entità per tipo
     */
    public function getEntities(string $type): array;

    /**
     * Pulisce tutte le entità registrate
     */
    public function clear(): void;

    /**
     * Verifica se un'entità è registrata
     */
    public function isRegistered($entity): bool;

    /**
     * Ottiene il tipo di registrazione di un'entità
     */
    public function getEntityType($entity): ?string;
}
