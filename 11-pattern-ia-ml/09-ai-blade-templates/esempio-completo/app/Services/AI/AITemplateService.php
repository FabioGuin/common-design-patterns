<?php

namespace App\Services\AI;

use App\Services\AI\Providers\AIProviderInterface;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AITemplateService
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
     * Genera contenuti AI per template
     */
    public function generateContent($data, string $type = 'description', array $options = []): string
    {
        $cacheKey = "ai_content_{$type}_" . md5(serialize($data));
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($data, $type, $options) {
            try {
                $prompt = $this->buildContentPrompt($data, $type, $options);
                return $this->provider->generateText($prompt);
            } catch (\Exception $e) {
                Log::warning('AI Content generation failed', ['error' => $e->getMessage()]);
                return $this->getFallbackContent($data, $type);
            }
        });
    }

    /**
     * Traduce contenuti per template
     */
    public function translateContent($content, string $targetLanguage = 'en', array $options = []): string
    {
        if (is_array($content)) {
            $content = json_encode($content);
        }

        $cacheKey = "ai_translate_" . md5($content . $targetLanguage);
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($content, $targetLanguage, $options) {
            try {
                $prompt = "Traduci il seguente contenuto in {$targetLanguage}, mantenendo il tono e lo stile originale:\n\n{$content}";
                return $this->provider->generateText($prompt);
            } catch (\Exception $e) {
                Log::warning('AI Translation failed', ['error' => $e->getMessage()]);
                return $content; // Fallback al contenuto originale
            }
        });
    }

    /**
     * Personalizza contenuti per utente
     */
    public function personalizeContent($content, $user, array $options = []): string
    {
        $cacheKey = "ai_personalize_" . md5(serialize($content) . $user->id);
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($content, $user, $options) {
            try {
                $userContext = $this->buildUserContext($user);
                $prompt = "Personalizza il seguente contenuto per questo utente: {$userContext}\n\nContenuto: {$content}";
                return $this->provider->generateText($prompt);
            } catch (\Exception $e) {
                Log::warning('AI Personalization failed', ['error' => $e->getMessage()]);
                return $content; // Fallback al contenuto originale
            }
        });
    }

    /**
     * Genera meta tag SEO ottimizzati
     */
    public function generateSeo($data, array $options = []): string
    {
        $cacheKey = "ai_seo_" . md5(serialize($data));
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($data, $options) {
            try {
                $prompt = $this->buildSeoPrompt($data, $options);
                $seoData = $this->provider->generateText($prompt);
                
                return $this->formatSeoMeta($seoData, $data);
            } catch (\Exception $e) {
                Log::warning('AI SEO generation failed', ['error' => $e->getMessage()]);
                return $this->getFallbackSeo($data);
            }
        });
    }

    /**
     * Ottimizza immagini con AI
     */
    public function optimizeImage($imageData, array $options = []): string
    {
        $cacheKey = "ai_image_" . md5(serialize($imageData));
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($imageData, $options) {
            try {
                $prompt = $this->buildImagePrompt($imageData, $options);
                return $this->provider->generateText($prompt);
            } catch (\Exception $e) {
                Log::warning('AI Image optimization failed', ['error' => $e->getMessage()]);
                return $this->getFallbackImage($imageData);
            }
        });
    }

    /**
     * Genera raccomandazioni AI
     */
    public function generateRecommendations($data, array $options = []): string
    {
        $cacheKey = "ai_recommendations_" . md5(serialize($data));
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($data, $options) {
            try {
                $prompt = $this->buildRecommendationsPrompt($data, $options);
                return $this->provider->generateText($prompt);
            } catch (\Exception $e) {
                Log::warning('AI Recommendations failed', ['error' => $e->getMessage()]);
                return $this->getFallbackRecommendations($data);
            }
        });
    }

    /**
     * Genera recensioni AI
     */
    public function generateReviews($data, array $options = []): string
    {
        $cacheKey = "ai_reviews_" . md5(serialize($data));
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($data, $options) {
            try {
                $prompt = $this->buildReviewsPrompt($data, $options);
                return $this->provider->generateText($prompt);
            } catch (\Exception $e) {
                Log::warning('AI Reviews generation failed', ['error' => $e->getMessage()]);
                return $this->getFallbackReviews($data);
            }
        });
    }

    /**
     * Genera meta tag dinamici
     */
    public function generateMeta($data, array $options = []): string
    {
        $cacheKey = "ai_meta_" . md5(serialize($data));
        
        return Cache::remember($cacheKey, config('ai.cache_ttl', 3600), function () use ($data, $options) {
            try {
                $prompt = $this->buildMetaPrompt($data, $options);
                return $this->provider->generateText($prompt);
            } catch (\Exception $e) {
                Log::warning('AI Meta generation failed', ['error' => $e->getMessage()]);
                return $this->getFallbackMeta($data);
            }
        });
    }

    /**
     * Contenuto con cache intelligente
     */
    public function cachedContent($callback, string $key, int $ttl = null): string
    {
        $ttl = $ttl ?? config('ai.cache_ttl', 3600);
        $cacheKey = "ai_cached_{$key}";
        
        return Cache::remember($cacheKey, $ttl, function () use ($callback) {
            return $callback();
        });
    }

    /**
     * Contenuto con fallback automatico
     */
    public function fallbackContent($callback, $fallback = null): string
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            Log::warning('AI Fallback triggered', ['error' => $e->getMessage()]);
            return $fallback ?? 'Contenuto non disponibile';
        }
    }

    /**
     * Costruisce il prompt per contenuti
     */
    protected function buildContentPrompt($data, string $type, array $options): string
    {
        $templates = [
            'description' => 'Crea una descrizione accattivante per questo prodotto: {data}',
            'title' => 'Genera un titolo ottimizzato per SEO per: {data}',
            'features' => 'Elenca le caratteristiche principali di: {data}',
            'benefits' => 'Descrivi i benefici di: {data}',
            'summary' => 'Crea un riassunto conciso di: {data}',
        ];

        $template = $templates[$type] ?? $templates['description'];
        $dataString = is_array($data) ? json_encode($data) : (string)$data;
        
        return str_replace('{data}', $dataString, $template);
    }

    /**
     * Costruisce il prompt per SEO
     */
    protected function buildSeoPrompt($data, array $options): string
    {
        $dataString = is_array($data) ? json_encode($data) : (string)$data;
        
        return "Genera meta tag SEO ottimizzati per questo contenuto:\n\n{$dataString}\n\nIncludi: title, description, keywords, og:title, og:description";
    }

    /**
     * Costruisce il prompt per immagini
     */
    protected function buildImagePrompt($imageData, array $options): string
    {
        $dataString = is_array($imageData) ? json_encode($imageData) : (string)$imageData;
        
        return "Genera alt text ottimizzato e suggerimenti per questa immagine: {$dataString}";
    }

    /**
     * Costruisce il prompt per raccomandazioni
     */
    protected function buildRecommendationsPrompt($data, array $options): string
    {
        $dataString = is_array($data) ? json_encode($data) : (string)$data;
        
        return "Suggerisci prodotti correlati o complementari per: {$dataString}";
    }

    /**
     * Costruisce il prompt per recensioni
     */
    protected function buildReviewsPrompt($data, array $options): string
    {
        $dataString = is_array($data) ? json_encode($data) : (string)$data;
        
        return "Genera recensioni realistiche per questo prodotto: {$dataString}";
    }

    /**
     * Costruisce il prompt per meta tag
     */
    protected function buildMetaPrompt($data, array $options): string
    {
        $dataString = is_array($data) ? json_encode($data) : (string)$data;
        
        return "Genera meta tag personalizzati per: {$dataString}";
    }

    /**
     * Costruisce il contesto utente per personalizzazione
     */
    protected function buildUserContext($user): string
    {
        $context = "Utente: {$user->name}";
        
        if (isset($user->preferences)) {
            $context .= ", Preferenze: " . json_encode($user->preferences);
        }
        
        if (isset($user->purchase_history)) {
            $context .= ", Storia acquisti: " . json_encode($user->purchase_history);
        }
        
        return $context;
    }

    /**
     * Formatta i meta tag SEO
     */
    protected function formatSeoMeta(string $seoData, $originalData): string
    {
        // Parsing semplice del JSON o testo generato
        $meta = json_decode($seoData, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            $html = '';
            if (isset($meta['title'])) {
                $html .= "<title>{$meta['title']}</title>\n";
            }
            if (isset($meta['description'])) {
                $html .= "<meta name=\"description\" content=\"{$meta['description']}\">\n";
            }
            if (isset($meta['keywords'])) {
                $html .= "<meta name=\"keywords\" content=\"{$meta['keywords']}\">\n";
            }
            return $html;
        }
        
        return $this->getFallbackSeo($originalData);
    }

    /**
     * Contenuto di fallback per contenuti
     */
    protected function getFallbackContent($data, string $type): string
    {
        $fallbacks = [
            'description' => 'Descrizione del prodotto non disponibile',
            'title' => 'Titolo del prodotto',
            'features' => 'Caratteristiche del prodotto',
            'benefits' => 'Benefici del prodotto',
            'summary' => 'Riassunto del prodotto',
        ];
        
        return $fallbacks[$type] ?? 'Contenuto non disponibile';
    }

    /**
     * SEO di fallback
     */
    protected function getFallbackSeo($data): string
    {
        $title = is_array($data) ? ($data['title'] ?? 'Prodotto') : (string)$data;
        return "<title>{$title}</title>\n<meta name=\"description\" content=\"Descrizione del prodotto\">\n";
    }

    /**
     * Immagine di fallback
     */
    protected function getFallbackImage($imageData): string
    {
        return 'alt="Immagine del prodotto"';
    }

    /**
     * Raccomandazioni di fallback
     */
    protected function getFallbackRecommendations($data): string
    {
        return 'Prodotti correlati non disponibili';
    }

    /**
     * Recensioni di fallback
     */
    protected function getFallbackReviews($data): string
    {
        return 'Recensioni non disponibili';
    }

    /**
     * Meta di fallback
     */
    protected function getFallbackMeta($data): string
    {
        return '<meta name="robots" content="index, follow">';
    }
}
