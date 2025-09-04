<?php

namespace App\Services\AI\Providers;

interface AIProviderInterface
{
    /**
     * Nome del provider
     */
    public function getName(): string;

    /**
     * Priorità del provider (1 = più alta)
     */
    public function getPriority(): int;

    /**
     * Capacità supportate dal provider
     */
    public function getCapabilities(): array;

    /**
     * Costo per token
     */
    public function getCostPerToken(): float;

    /**
     * Modello utilizzato
     */
    public function getModel(): string;

    /**
     * Verifica se il provider è disponibile
     */
    public function isAvailable(): bool;

    /**
     * Genera testo
     */
    public function generateText(string $prompt, array $options = []): array;

    /**
     * Genera immagine
     */
    public function generateImage(string $prompt, array $options = []): array;

    /**
     * Analizza documento
     */
    public function analyzeDocument(string $content, array $options = []): array;

    /**
     * Traduce testo
     */
    public function translate(string $text, string $targetLanguage): array;

    /**
     * Tempo medio di risposta
     */
    public function getAverageResponseTime(): float;

    /**
     * Aggiorna tempo di risposta
     */
    public function updateResponseTime(float $responseTime): void;
}
