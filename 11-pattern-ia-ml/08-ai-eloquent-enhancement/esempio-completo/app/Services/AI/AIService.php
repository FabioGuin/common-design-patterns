<?php

namespace App\Services\AI;

use App\Services\AI\Providers\AIProviderInterface;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected AIProviderInterface $provider;
    protected array $providers = [];

    public function __construct()
    {
        $this->initializeProviders();
        $this->provider = $this->getDefaultProvider();
    }

    /**
     * Inizializza i provider AI disponibili
     */
    protected function initializeProviders(): void
    {
        $this->providers = [
            'openai' => new OpenAIProvider(),
            'claude' => new ClaudeProvider(),
            'gemini' => new GeminiProvider(),
        ];
    }

    /**
     * Ottiene il provider di default
     */
    protected function getDefaultProvider(): AIProviderInterface
    {
        $defaultProvider = config('ai.default_provider', 'openai');
        
        if (!isset($this->providers[$defaultProvider])) {
            Log::warning("Provider AI {$defaultProvider} non disponibile, uso OpenAI");
            return $this->providers['openai'];
        }
        
        return $this->providers[$defaultProvider];
    }

    /**
     * Genera embedding per un testo
     */
    public function generateEmbedding(string $text): array
    {
        try {
            return $this->provider->generateEmbedding($text);
        } catch (\Exception $e) {
            Log::error('Errore generazione embedding', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Genera tag per un contenuto
     */
    public function generateTags(string $content): array
    {
        try {
            $prompt = "Analizza il seguente contenuto e genera 5-8 tag rilevanti in italiano, separati da virgola:\n\n{$content}";
            $response = $this->provider->generateText($prompt);
            
            return array_map('trim', explode(',', $response));
        } catch (\Exception $e) {
            Log::error('Errore generazione tag', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Traduce un testo in una lingua specifica
     */
    public function translate(string $text, string $targetLanguage): string
    {
        try {
            $prompt = "Traduci il seguente testo in {$targetLanguage}, mantenendo il tono e lo stile originale:\n\n{$text}";
            return $this->provider->generateText($prompt);
        } catch (\Exception $e) {
            Log::error('Errore traduzione', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Genera un riassunto del contenuto
     */
    public function generateSummary(string $content, int $maxLength = 150): string
    {
        try {
            $prompt = "Crea un riassunto conciso del seguente contenuto in massimo {$maxLength} caratteri:\n\n{$content}";
            return $this->provider->generateText($prompt);
        } catch (\Exception $e) {
            Log::error('Errore generazione riassunto', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Trova contenuti correlati
     */
    public function findCorrelated(string $content, string $table, int $limit = 5): array
    {
        try {
            // Implementazione semplificata - in produzione useresti un database vettoriale
            $prompt = "Basandoti sul seguente contenuto, suggerisci {$limit} ID di contenuti correlati dalla tabella {$table}:\n\n{$content}";
            $response = $this->provider->generateText($prompt);
            
            // Estrai numeri dalla risposta (ID correlati)
            preg_match_all('/\d+/', $response, $matches);
            return array_slice($matches[0], 0, $limit);
        } catch (\Exception $e) {
            Log::error('Errore ricerca correlati', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Classifica il contenuto in categorie
     */
    public function classifyContent(string $content, array $categories = []): string
    {
        try {
            $defaultCategories = ['tecnologia', 'cucina', 'viaggi', 'sport', 'cultura', 'business', 'salute', 'educazione'];
            $categories = empty($categories) ? $defaultCategories : $categories;
            
            $categoriesList = implode(', ', $categories);
            $prompt = "Classifica il seguente contenuto in una di queste categorie: {$categoriesList}\n\nContenuto: {$content}\n\nRispondi solo con il nome della categoria:";
            
            $response = trim($this->provider->generateText($prompt));
            
            // Verifica che la risposta sia una categoria valida
            if (in_array(strtolower($response), array_map('strtolower', $categories))) {
                return strtolower($response);
            }
            
            return 'uncategorized';
        } catch (\Exception $e) {
            Log::error('Errore classificazione', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Analizza il sentiment del contenuto
     */
    public function analyzeSentiment(string $content): array
    {
        try {
            $prompt = "Analizza il sentiment del seguente contenuto e rispondi in formato JSON con 'sentiment' (positive/negative/neutral) e 'confidence' (0-1):\n\n{$content}";
            $response = $this->provider->generateText($prompt);
            
            $data = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($data['sentiment'])) {
                return [
                    'sentiment' => $data['sentiment'],
                    'confidence' => $data['confidence'] ?? 0.5
                ];
            }
            
            // Fallback se il JSON non è valido
            return ['sentiment' => 'neutral', 'confidence' => 0.5];
        } catch (\Exception $e) {
            Log::error('Errore analisi sentiment', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Cambia il provider AI
     */
    public function setProvider(string $providerName): void
    {
        if (!isset($this->providers[$providerName])) {
            throw new \InvalidArgumentException("Provider {$providerName} non supportato");
        }
        
        $this->provider = $this->providers[$providerName];
    }

    /**
     * Ottiene il provider corrente
     */
    public function getCurrentProvider(): string
    {
        return array_search($this->provider, $this->providers);
    }

    /**
     * Ottiene la lista dei provider disponibili
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }
}
