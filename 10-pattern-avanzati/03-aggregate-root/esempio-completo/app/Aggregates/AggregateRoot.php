<?php

namespace App\Aggregates;

use Illuminate\Support\Collection;

/**
 * Classe base per Aggregate Root
 * 
 * Fornisce funzionalità comuni per tutti gli aggregate:
 * - Gestione eventi di dominio
 * - Tracciamento modifiche
 * - Identificazione univoca
 */
abstract class AggregateRoot
{
    protected string $id;
    protected Collection $domainEvents;
    protected int $version = 0;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->domainEvents = collect();
    }

    /**
     * Restituisce l'ID dell'aggregate
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Restituisce la versione dell'aggregate
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Incrementa la versione
     */
    protected function incrementVersion(): void
    {
        $this->version++;
    }

    /**
     * Aggiunge un evento di dominio
     */
    protected function addDomainEvent($event): void
    {
        $this->domainEvents->push($event);
    }

    /**
     * Restituisce tutti gli eventi di dominio
     */
    public function getDomainEvents(): Collection
    {
        return $this->domainEvents;
    }

    /**
     * Pulisce gli eventi di dominio
     */
    public function clearDomainEvents(): void
    {
        $this->domainEvents = collect();
    }

    /**
     * Verifica se l'aggregate ha eventi pendenti
     */
    public function hasDomainEvents(): bool
    {
        return $this->domainEvents->isNotEmpty();
    }

    /**
     * Restituisce una rappresentazione stringa
     */
    public function __toString(): string
    {
        return static::class . '#' . $this->id;
    }
}
