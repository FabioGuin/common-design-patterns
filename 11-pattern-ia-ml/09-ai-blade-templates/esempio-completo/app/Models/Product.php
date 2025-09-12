<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'image_url',
        'features',
        'benefits',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'status',
        'rating',
        'reviews_count'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'benefits' => 'array',
        'rating' => 'decimal:1',
        'reviews_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope per prodotti attivi
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope per categoria
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope per range di prezzo
     */
    public function scopeByPriceRange($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope per rating minimo
     */
    public function scopeByMinRating($query, float $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope per ricerca
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
        });
    }

    /**
     * Ottiene il contenuto per l'analisi AI
     */
    public function getContentForAI(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'features' => $this->features,
            'benefits' => $this->benefits,
            'price' => $this->price
        ];
    }

    /**
     * Genera contenuto AI per il prodotto
     */
    public function generateAIContent(string $type = 'description'): string
    {
        $aiService = app(\App\Services\AI\AITemplateService::class);
        return $aiService->generateContent($this->getContentForAI(), $type);
    }

    /**
     * Traduce il prodotto in una lingua specifica
     */
    public function translateTo(string $language): array
    {
        $aiService = app(\App\Services\AI\AITemplateService::class);
        
        return [
            'name' => $aiService->translateContent($this->name, $language),
            'description' => $aiService->translateContent($this->description, $language),
            'features' => $aiService->translateContent($this->features, $language),
            'benefits' => $aiService->translateContent($this->benefits, $language)
        ];
    }

    /**
     * Personalizza il prodotto per un utente
     */
    public function personalizeFor($user): array
    {
        $aiService = app(\App\Services\AI\AITemplateService::class);
        
        return [
            'name' => $aiService->personalizeContent($this->name, $user),
            'description' => $aiService->personalizeContent($this->description, $user),
            'features' => $aiService->personalizeContent($this->features, $user),
            'benefits' => $aiService->personalizeContent($this->benefits, $user)
        ];
    }

    /**
     * Genera SEO ottimizzato per il prodotto
     */
    public function generateAISeo(): array
    {
        $aiService = app(\App\Services\AI\AITemplateService::class);
        $seoContent = $aiService->generateSeo($this->getContentForAI());
        
        // Parsing del contenuto SEO generato
        preg_match('/<title>(.*?)<\/title>/', $seoContent, $titleMatches);
        preg_match('/<meta name="description" content="(.*?)">/', $seoContent, $descMatches);
        preg_match('/<meta name="keywords" content="(.*?)">/', $seoContent, $keywordsMatches);
        
        return [
            'title' => $titleMatches[1] ?? $this->name,
            'description' => $descMatches[1] ?? $this->description,
            'keywords' => $keywordsMatches[1] ?? $this->category
        ];
    }

    /**
     * Genera raccomandazioni AI per il prodotto
     */
    public function generateAIRecommendations(int $limit = 5): array
    {
        $aiService = app(\App\Services\AI\AITemplateService::class);
        $recommendations = $aiService->generateRecommendations($this->getContentForAI());
        
        // Parsing delle raccomandazioni generate
        $items = explode(',', $recommendations);
        return array_slice(array_map('trim', $items), 0, $limit);
    }

    /**
     * Genera recensioni AI per il prodotto
     */
    public function generateAIReviews(int $limit = 3): array
    {
        $aiService = app(\App\Services\AI\AITemplateService::class);
        $reviews = $aiService->generateReviews($this->getContentForAI());
        
        // Parsing delle recensioni generate
        $reviewItems = explode('---', $reviews);
        $formattedReviews = [];
        
        foreach (array_slice($reviewItems, 0, $limit) as $review) {
            $lines = explode("\n", trim($review));
            if (count($lines) >= 2) {
                $formattedReviews[] = [
                    'author' => trim($lines[0]),
                    'content' => trim($lines[1]),
                    'rating' => rand(3, 5) // Rating casuale per le recensioni generate
                ];
            }
        }
        
        return $formattedReviews;
    }

    /**
     * Ottimizza l'immagine del prodotto con AI
     */
    public function optimizeAIImage(): string
    {
        $aiService = app(\App\Services\AI\AITemplateService::class);
        return $aiService->optimizeImage([
            'url' => $this->image_url,
            'name' => $this->name,
            'category' => $this->category
        ]);
    }

    /**
     * Aggiorna tutti i contenuti AI del prodotto
     */
    public function updateAIContent(): void
    {
        $this->seo_title = $this->generateAISeo()['title'];
        $this->seo_description = $this->generateAISeo()['description'];
        $this->seo_keywords = $this->generateAISeo()['keywords'];
        $this->save();
    }

    /**
     * Pulisce la cache AI per questo prodotto
     */
    public function clearAICache(): void
    {
        $patterns = [
            "ai_content_*_" . md5(serialize($this->getContentForAI())),
            "ai_translate_*_" . md5($this->name . $this->description),
            "ai_personalize_*_" . md5(serialize($this->getContentForAI())),
            "ai_seo_*_" . md5(serialize($this->getContentForAI())),
            "ai_recommendations_*_" . md5(serialize($this->getContentForAI())),
            "ai_reviews_*_" . md5(serialize($this->getContentForAI()))
        ];

        foreach ($patterns as $pattern) {
            \Illuminate\Support\Facades\Cache::forget($pattern);
        }
    }
}
