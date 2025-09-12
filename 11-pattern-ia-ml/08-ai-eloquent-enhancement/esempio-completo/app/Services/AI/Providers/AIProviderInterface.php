<?php

namespace App\Services\AI\Providers;

interface AIProviderInterface
{
    /**
     * Genera embedding per un testo
     */
    public function generateEmbedding(string $text): array;

    /**
     * Genera testo basato su un prompt
     */
    public function generateText(string $prompt): string;

    /**
     * Verifica se il provider è configurato correttamente
     */
    public function isConfigured(): bool;

    /**
     * Ottiene il nome del provider
     */
    public function getName(): string;
}
