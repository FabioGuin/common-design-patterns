<?php

namespace App\Services\AI\Strategies;

interface CacheStrategyInterface
{
    /**
     * Salva dati in cache
     */
    public function put(string $key, array $data, int $ttl): bool;

    /**
     * Recupera dati dalla cache
     */
    public function get(string $key): ?array;

    /**
     * Verifica se una chiave esiste in cache
     */
    public function has(string $key): bool;

    /**
     * Rimuove una chiave dalla cache
     */
    public function forget(string $key): bool;

    /**
     * Pulisce tutta la cache
     */
    public function flush(): bool;

    /**
     * Ottiene le chiavi della cache
     */
    public function getKeys(string $pattern = '*', int $limit = 100): array;

    /**
     * Ottiene le statistiche della cache
     */
    public function getStats(): array;

    /**
     * Ottimizza la cache
     */
    public function optimize(): array;

    /**
     * Verifica la salute della cache
     */
    public function healthCheck(): array;

    /**
     * Ottiene il nome della strategia
     */
    public function getName(): string;

    /**
     * Ottiene la configurazione della strategia
     */
    public function getConfig(): array;

    /**
     * Aggiorna la configurazione della strategia
     */
    public function updateConfig(array $config): void;

    /**
     * Ottiene la dimensione massima della cache
     */
    public function getMaxSize(): int;

    /**
     * Ottiene la dimensione attuale della cache
     */
    public function getCurrentSize(): int;

    /**
     * Verifica se la cache è piena
     */
    public function isFull(): bool;

    /**
     * Ottiene il TTL di una chiave
     */
    public function getTtl(string $key): ?int;

    /**
     * Aggiorna il TTL di una chiave
     */
    public function updateTtl(string $key, int $ttl): bool;

    /**
     * Ottiene le chiavi che scadranno presto
     */
    public function getExpiringKeys(int $minutes = 5): array;

    /**
     * Pulisce le chiavi scadute
     */
    public function cleanExpired(): int;

    /**
     * Ottiene le chiavi più utilizzate
     */
    public function getMostUsedKeys(int $limit = 10): array;

    /**
     * Ottiene le chiavi meno utilizzate
     */
    public function getLeastUsedKeys(int $limit = 10): array;

    /**
     * Ottiene le chiavi più grandi
     */
    public function getLargestKeys(int $limit = 10): array;

    /**
     * Ottiene le chiavi più piccole
     */
    public function getSmallestKeys(int $limit = 10): array;

    /**
     * Ottiene le statistiche di utilizzo
     */
    public function getUsageStats(): array;

    /**
     * Ottiene le statistiche di performance
     */
    public function getPerformanceStats(): array;

    /**
     * Ottiene le statistiche di memoria
     */
    public function getMemoryStats(): array;

    /**
     * Ottiene le statistiche di hit rate
     */
    public function getHitRateStats(): array;

    /**
     * Ottiene le statistiche di miss rate
     */
    public function getMissRateStats(): array;

    /**
     * Ottiene le statistiche di TTL
     */
    public function getTtlStats(): array;

    /**
     * Ottiene le statistiche di compressione
     */
    public function getCompressionStats(): array;

    /**
     * Ottiene le statistiche di crittografia
     */
    public function getEncryptionStats(): array;

    /**
     * Ottiene le statistiche di tag
     */
    public function getTagStats(): array;

    /**
     * Ottiene le statistiche di pattern
     */
    public function getPatternStats(): array;

    /**
     * Ottiene le statistiche di strategia
     */
    public function getStrategyStats(): array;

    /**
     * Ottiene le statistiche complete
     */
    public function getAllStats(): array;

    /**
     * Resetta le statistiche
     */
    public function resetStats(): void;

    /**
     * Ottiene le informazioni di debug
     */
    public function getDebugInfo(): array;

    /**
     * Ottiene le informazioni di configurazione
     */
    public function getConfigurationInfo(): array;

    /**
     * Ottiene le informazioni di stato
     */
    public function getStatusInfo(): array;

    /**
     * Ottiene le informazioni di versione
     */
    public function getVersionInfo(): array;

    /**
     * Ottiene le informazioni di compatibilità
     */
    public function getCompatibilityInfo(): array;

    /**
     * Ottiene le informazioni di supporto
     */
    public function getSupportInfo(): array;
}
