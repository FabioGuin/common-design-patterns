<?php

namespace App\Services\AI\Providers;

interface AIModelInterface
{
    /**
     * Ottiene il nome del modello
     */
    public function getName(): string;

    /**
     * Ottiene il provider del modello
     */
    public function getProvider(): string;

    /**
     * Ottiene la descrizione del modello
     */
    public function getDescription(): string;

    /**
     * Ottiene le capacità del modello
     */
    public function getCapabilities(): array;

    /**
     * Ottiene il costo per token
     */
    public function getCostPerToken(): float;

    /**
     * Ottiene il numero massimo di token
     */
    public function getMaxTokens(): int;

    /**
     * Ottiene la finestra di contesto
     */
    public function getContextWindow(): int;

    /**
     * Ottiene la priorità del modello
     */
    public function getPriority(): int;

    /**
     * Verifica se il modello è disponibile
     */
    public function isAvailable(): bool;

    /**
     * Imposta la disponibilità del modello
     */
    public function setAvailable(bool $available): void;

    /**
     * Ottiene il tempo medio di risposta
     */
    public function getAverageResponseTime(): float;

    /**
     * Ottiene il tasso di successo
     */
    public function getSuccessRate(): float;

    /**
     * Ottiene i tag del modello
     */
    public function getTags(): array;

    /**
     * Genera testo
     */
    public function generateText(string $prompt, array $options = []): array;

    /**
     * Genera immagine
     */
    public function generateImage(string $prompt, array $options = []): array;

    /**
     * Traduce testo
     */
    public function translate(string $text, string $targetLanguage, array $options = []): array;

    /**
     * Analizza contenuto
     */
    public function analyzeContent(string $content, string $analysisType, array $options = []): array;

    /**
     * Aggiorna le performance del modello
     */
    public function updatePerformance(float $responseTime, bool $success): void;

    /**
     * Ottiene le informazioni del modello
     */
    public function getInfo(): array;

    /**
     * Verifica se il modello supporta una capacità
     */
    public function supportsCapability(string $capability): bool;

    /**
     * Ottiene il costo stimato per una richiesta
     */
    public function estimateCost(string $prompt, array $options = []): float;

    /**
     * Valida le opzioni per il modello
     */
    public function validateOptions(array $options): array;

    /**
     * Ottiene i limiti del modello
     */
    public function getLimits(): array;

    /**
     * Verifica se il modello è configurato correttamente
     */
    public function isConfigured(): bool;

    /**
     * Testa la connessione al modello
     */
    public function testConnection(): bool;

    /**
     * Ottiene le statistiche del modello
     */
    public function getStats(): array;

    /**
     * Resetta le statistiche del modello
     */
    public function resetStats(): void;
}
