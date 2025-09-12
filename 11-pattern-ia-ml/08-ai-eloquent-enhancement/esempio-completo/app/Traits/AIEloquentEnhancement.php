<?php

namespace App\Traits;

use App\Services\AI\AIService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait AIEloquentEnhancement
{
    /**
     * Esegue una ricerca semantica AI sui contenuti del modello
     */
    public function scopeAiSearch(Builder $query, string $searchQuery, string $field = 'content'): Builder
    {
        try {
            $aiService = app(AIService::class);
            
            // Genera embedding per la query di ricerca
            $embedding = $aiService->generateEmbedding($searchQuery);
            
            // Per ora, facciamo una ricerca semplice basata su similarità di parole
            // In un'implementazione reale, useresti un database vettoriale
            return $query->where($field, 'like', '%' . $searchQuery . '%')
                        ->orWhere($field, 'like', '%' . $this->expandSearchTerms($searchQuery) . '%');
        } catch (\Exception $e) {
            Log::warning('AI Search fallback to simple search', ['error' => $e->getMessage()]);
            return $query->where($field, 'like', '%' . $searchQuery . '%');
        }
    }

    /**
     * Genera tag automaticamente per il contenuto
     */
    public function generateAITags(): array
    {
        $cacheKey = "ai_tags_{$this->getTable()}_{$this->getKey()}_{$this->updated_at->timestamp}";
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () {
            try {
                $aiService = app(AIService::class);
                return $aiService->generateTags($this->getContentForAI());
            } catch (\Exception $e) {
                Log::warning('AI Tag generation failed', ['error' => $e->getMessage()]);
                return $this->generateFallbackTags();
            }
        });
    }

    /**
     * Traduce il contenuto in una lingua specifica
     */
    public function translateTo(string $targetLanguage, string $field = 'content'): string
    {
        $cacheKey = "ai_translation_{$this->getTable()}_{$this->getKey()}_{$targetLanguage}_{$this->updated_at->timestamp}";
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($targetLanguage, $field) {
            try {
                $aiService = app(AIService::class);
                return $aiService->translate($this->$field, $targetLanguage);
            } catch (\Exception $e) {
                Log::warning('AI Translation failed', ['error' => $e->getMessage()]);
                return $this->$field; // Fallback al contenuto originale
            }
        });
    }

    /**
     * Genera un riassunto del contenuto
     */
    public function generateAISummary(int $maxLength = 150): string
    {
        $cacheKey = "ai_summary_{$this->getTable()}_{$this->getKey()}_{$maxLength}_{$this->updated_at->timestamp}";
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($maxLength) {
            try {
                $aiService = app(AIService::class);
                return $aiService->generateSummary($this->getContentForAI(), $maxLength);
            } catch (\Exception $e) {
                Log::warning('AI Summary generation failed', ['error' => $e->getMessage()]);
                return $this->generateFallbackSummary($maxLength);
            }
        });
    }

    /**
     * Trova contenuti correlati basati su similarità semantica
     */
    public function findAICorrelated(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "ai_correlated_{$this->getTable()}_{$this->getKey()}_{$limit}_{$this->updated_at->timestamp}";
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($limit) {
            try {
                $aiService = app(AIService::class);
                $correlatedIds = $aiService->findCorrelated($this->getContentForAI(), $this->getTable(), $limit);
                
                return static::whereIn('id', $correlatedIds)->get();
            } catch (\Exception $e) {
                Log::warning('AI Correlation failed', ['error' => $e->getMessage()]);
                return $this->findFallbackCorrelated($limit);
            }
        });
    }

    /**
     * Classifica il contenuto in categorie
     */
    public function classifyAIContent(array $categories = []): string
    {
        $cacheKey = "ai_classification_{$this->getTable()}_{$this->getKey()}_{$this->updated_at->timestamp}";
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($categories) {
            try {
                $aiService = app(AIService::class);
                return $aiService->classifyContent($this->getContentForAI(), $categories);
            } catch (\Exception $e) {
                Log::warning('AI Classification failed', ['error' => $e->getMessage()]);
                return 'uncategorized';
            }
        });
    }

    /**
     * Analizza il sentiment del contenuto
     */
    public function analyzeAISentiment(): array
    {
        $cacheKey = "ai_sentiment_{$this->getTable()}_{$this->getKey()}_{$this->updated_at->timestamp}";
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () {
            try {
                $aiService = app(AIService::class);
                return $aiService->analyzeSentiment($this->getContentForAI());
            } catch (\Exception $e) {
                Log::warning('AI Sentiment analysis failed', ['error' => $e->getMessage()]);
                return ['sentiment' => 'neutral', 'confidence' => 0.5];
            }
        });
    }

    /**
     * Ottiene il contenuto da analizzare per l'AI
     */
    protected function getContentForAI(): string
    {
        // Combina titolo e contenuto per una migliore analisi
        $content = '';
        
        if (isset($this->title)) {
            $content .= $this->title . ' ';
        }
        
        if (isset($this->content)) {
            $content .= $this->content;
        }
        
        return trim($content);
    }

    /**
     * Espande i termini di ricerca per migliorare i risultati
     */
    protected function expandSearchTerms(string $query): string
    {
        // Implementazione semplice di espansione termini
        $expansions = [
            'ricetta' => ['cucina', 'preparazione', 'ingredienti'],
            'pane' => ['panificazione', 'lievito', 'forno'],
            'tutorial' => ['guida', 'istruzioni', 'come fare'],
            'recensione' => ['opinione', 'valutazione', 'giudizio']
        ];
        
        $expandedQuery = $query;
        foreach ($expansions as $term => $synonyms) {
            if (stripos($query, $term) !== false) {
                $expandedQuery .= ' ' . implode(' ', $synonyms);
            }
        }
        
        return $expandedQuery;
    }

    /**
     * Genera tag di fallback quando l'AI non è disponibile
     */
    protected function generateFallbackTags(): array
    {
        $content = strtolower($this->getContentForAI());
        $tags = [];
        
        $commonTags = [
            'cucina' => ['ricetta', 'cucina', 'cibo', 'ingredienti', 'preparazione'],
            'tecnologia' => ['tech', 'tecnologia', 'software', 'programmazione', 'computer'],
            'viaggi' => ['viaggio', 'vacanza', 'turismo', 'destinazione', 'luogo'],
            'sport' => ['sport', 'fitness', 'allenamento', 'esercizio', 'salute']
        ];
        
        foreach ($commonTags as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($content, $keyword) !== false) {
                    $tags[] = $category;
                    break;
                }
            }
        }
        
        return array_unique($tags);
    }

    /**
     * Genera un riassunto di fallback
     */
    protected function generateFallbackSummary(int $maxLength): string
    {
        $content = $this->getContentForAI();
        $sentences = preg_split('/[.!?]+/', $content);
        $summary = '';
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($summary . $sentence) <= $maxLength) {
                $summary .= $sentence . '. ';
            } else {
                break;
            }
        }
        
        return trim($summary) ?: substr($content, 0, $maxLength) . '...';
    }

    /**
     * Trova contenuti correlati di fallback
     */
    protected function findFallbackCorrelated(int $limit): \Illuminate\Database\Eloquent\Collection
    {
        // Implementazione semplice basata su parole chiave comuni
        $content = strtolower($this->getContentForAI());
        $words = array_filter(explode(' ', $content), function($word) {
            return strlen($word) > 3;
        });
        
        $query = static::where('id', '!=', $this->getKey());
        
        foreach (array_slice($words, 0, 5) as $word) {
            $query->orWhere('content', 'like', '%' . $word . '%');
        }
        
        return $query->limit($limit)->get();
    }
}
