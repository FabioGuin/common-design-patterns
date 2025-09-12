<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AIEloquentEnhancement;

class Article extends Model
{
    use AIEloquentEnhancement;

    protected $fillable = [
        'title',
        'content',
        'author',
        'published_at',
        'category',
        'tags',
        'summary'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope per articoli pubblicati
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope per categoria
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope per autore
     */
    public function scopeByAuthor($query, string $author)
    {
        return $query->where('author', $author);
    }

    /**
     * Scope per tag
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope per ricerca nel titolo e contenuto
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Ottiene il contenuto per l'analisi AI
     */
    public function getContentForAI(): string
    {
        return $this->title . ' ' . $this->content;
    }

    /**
     * Genera automaticamente i tag AI se non esistono
     */
    public function getAITagsAttribute(): array
    {
        if (empty($this->tags)) {
            $this->tags = $this->generateAITags();
            $this->save();
        }
        
        return $this->tags;
    }

    /**
     * Genera automaticamente il riassunto AI se non esiste
     */
    public function getAISummaryAttribute(): string
    {
        if (empty($this->summary)) {
            $this->summary = $this->generateAISummary();
            $this->save();
        }
        
        return $this->summary;
    }

    /**
     * Ottiene la categoria AI se non esiste
     */
    public function getAICategoryAttribute(): string
    {
        if (empty($this->category)) {
            $this->category = $this->classifyAIContent();
            $this->save();
        }
        
        return $this->category;
    }

    /**
     * Ottiene il sentiment AI
     */
    public function getAISentimentAttribute(): array
    {
        return $this->analyzeAISentiment();
    }

    /**
     * Ottiene articoli correlati AI
     */
    public function getAICorrelatedAttribute()
    {
        return $this->findAICorrelated();
    }

    /**
     * Traduce l'articolo in una lingua specifica
     */
    public function translateTo(string $language): array
    {
        return [
            'title' => $this->translateTo($this->title, $language),
            'content' => $this->translateTo($this->content, $language),
            'summary' => $this->translateTo($this->summary ?? '', $language)
        ];
    }

    /**
     * Aggiorna tutti i metadati AI
     */
    public function updateAIMetadata(): void
    {
        $this->tags = $this->generateAITags();
        $this->summary = $this->generateAISummary();
        $this->category = $this->classifyAIContent();
        $this->save();
    }

    /**
     * Pulisce la cache AI per questo articolo
     */
    public function clearAICache(): void
    {
        $patterns = [
            "ai_tags_{$this->getTable()}_{$this->getKey()}_*",
            "ai_summary_{$this->getTable()}_{$this->getKey()}_*",
            "ai_translation_{$this->getTable()}_{$this->getKey()}_*",
            "ai_correlated_{$this->getTable()}_{$this->getKey()}_*",
            "ai_classification_{$this->getTable()}_{$this->getKey()}_*",
            "ai_sentiment_{$this->getTable()}_{$this->getKey()}_*"
        ];

        foreach ($patterns as $pattern) {
            \Illuminate\Support\Facades\Cache::forget($pattern);
        }
    }
}
