<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\AI\AITemplateService;

class AIBladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AITemplateService::class, function ($app) {
            return new AITemplateService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerAIDirectives();
    }

    /**
     * Registra le direttive AI personalizzate
     */
    protected function registerAIDirectives(): void
    {
        // Direttiva @ai.content per generare contenuti AI
        Blade::directive('aiContent', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->generateContent($expression); ?>";
        });

        // Direttiva @ai.translate per tradurre contenuti
        Blade::directive('aiTranslate', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->translateContent($expression); ?>";
        });

        // Direttiva @ai.personalize per personalizzare contenuti
        Blade::directive('aiPersonalize', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->personalizeContent($expression); ?>";
        });

        // Direttiva @ai.seo per ottimizzare SEO
        Blade::directive('aiSeo', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->generateSeo($expression); ?>";
        });

        // Direttiva @ai.image per ottimizzare immagini
        Blade::directive('aiImage', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->optimizeImage($expression); ?>";
        });

        // Direttiva @ai.recommendations per raccomandazioni
        Blade::directive('aiRecommendations', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->generateRecommendations($expression); ?>";
        });

        // Direttiva @ai.reviews per recensioni AI
        Blade::directive('aiReviews', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->generateReviews($expression); ?>";
        });

        // Direttiva @ai.meta per meta tag dinamici
        Blade::directive('aiMeta', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->generateMeta($expression); ?>";
        });

        // Direttiva @ai.cache per cache intelligente
        Blade::directive('aiCache', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->cachedContent($expression); ?>";
        });

        // Direttiva @ai.fallback per fallback automatico
        Blade::directive('aiFallback', function ($expression) {
            return "<?php echo app(App\Services\AI\AITemplateService::class)->fallbackContent($expression); ?>";
        });
    }
}
