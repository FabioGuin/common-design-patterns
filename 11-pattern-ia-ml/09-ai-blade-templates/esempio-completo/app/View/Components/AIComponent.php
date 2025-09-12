<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\AI\AITemplateService;

class AIComponent extends Component
{
    protected AITemplateService $aiService;
    public $type;
    public $data;
    public $options;

    /**
     * Create a new component instance.
     */
    public function __construct(string $type, $data = null, array $options = [])
    {
        $this->aiService = app(AITemplateService::class);
        $this->type = $type;
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.ai-component');
    }

    /**
     * Genera contenuto AI
     */
    public function generateContent(): string
    {
        return $this->aiService->generateContent($this->data, $this->type, $this->options);
    }

    /**
     * Traduce contenuto
     */
    public function translateContent(string $language = 'en'): string
    {
        return $this->aiService->translateContent($this->data, $language, $this->options);
    }

    /**
     * Personalizza contenuto
     */
    public function personalizeContent($user): string
    {
        return $this->aiService->personalizeContent($this->data, $user, $this->options);
    }

    /**
     * Genera SEO
     */
    public function generateSeo(): string
    {
        return $this->aiService->generateSeo($this->data, $this->options);
    }

    /**
     * Ottimizza immagine
     */
    public function optimizeImage(): string
    {
        return $this->aiService->optimizeImage($this->data, $this->options);
    }

    /**
     * Genera raccomandazioni
     */
    public function generateRecommendations(): string
    {
        return $this->aiService->generateRecommendations($this->data, $this->options);
    }

    /**
     * Genera recensioni
     */
    public function generateReviews(): string
    {
        return $this->aiService->generateReviews($this->data, $this->options);
    }

    /**
     * Genera meta tag
     */
    public function generateMeta(): string
    {
        return $this->aiService->generateMeta($this->data, $this->options);
    }

    /**
     * Contenuto con cache
     */
    public function cachedContent(callable $callback, string $key = null): string
    {
        $key = $key ?? $this->type . '_' . md5(serialize($this->data));
        return $this->aiService->cachedContent($callback, $key);
    }

    /**
     * Contenuto con fallback
     */
    public function fallbackContent(callable $callback, $fallback = null): string
    {
        return $this->aiService->fallbackContent($callback, $fallback);
    }
}
