<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Services\AI\AITemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AIBladeTest extends TestCase
{
    use RefreshDatabase;

    protected AITemplateService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = app(AITemplateService::class);
    }

    public function test_ai_blade_page_loads(): void
    {
        $response = $this->get('/ai-blade');
        $response->assertStatus(200);
        $response->assertSee('AI Blade Templates');
    }

    public function test_ai_blade_api_test_endpoint(): void
    {
        $response = $this->postJson('/api/ai-blade/test');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data',
                    'timestamp'
                ]);
    }

    public function test_ai_template_rendering(): void
    {
        // Crea prodotto di test
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 99.99,
            'category' => 'test',
            'status' => 'active',
            'rating' => 4.5,
            'reviews_count' => 100
        ]);

        $response = $this->postJson('/api/ai-blade/render', [
            'template' => 'product',
            'data' => ['product_id' => $product->id]
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'template',
                        'rendered_content',
                        'data',
                        'options'
                    ]
                ]);
    }

    public function test_ai_template_translation(): void
    {
        $response = $this->postJson('/api/ai-blade/translate', [
            'content' => 'Hello World',
            'language' => 'es'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'original_content',
                        'translated_content',
                        'language'
                    ]
                ]);
    }

    public function test_ai_template_personalization(): void
    {
        $response = $this->postJson('/api/ai-blade/personalize', [
            'content' => 'This is a test product',
            'user' => [
                'name' => 'Test User',
                'preferences' => ['modern', 'minimalist']
            ]
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'original_content',
                        'personalized_content',
                        'user'
                    ]
                ]);
    }

    public function test_ai_template_service_methods(): void
    {
        $testData = ['name' => 'Test Product', 'description' => 'Test description'];
        
        // Test generazione contenuti
        $content = $this->aiService->generateContent($testData, 'description');
        $this->assertIsString($content);

        // Test traduzione
        $translation = $this->aiService->translateContent('Hello World', 'es');
        $this->assertIsString($translation);

        // Test personalizzazione
        $user = (object)['name' => 'Test User', 'preferences' => ['modern']];
        $personalized = $this->aiService->personalizeContent('Test content', $user);
        $this->assertIsString($personalized);

        // Test SEO
        $seo = $this->aiService->generateSeo($testData);
        $this->assertIsString($seo);

        // Test raccomandazioni
        $recommendations = $this->aiService->generateRecommendations($testData);
        $this->assertIsString($recommendations);

        // Test recensioni
        $reviews = $this->aiService->generateReviews($testData);
        $this->assertIsString($reviews);

        // Test meta tag
        $meta = $this->aiService->generateMeta($testData);
        $this->assertIsString($meta);
    }

    public function test_ai_template_caching(): void
    {
        $testData = ['name' => 'Test Product'];
        
        // Pulisci cache
        Cache::flush();

        // Prima chiamata - dovrebbe essere cached
        $content1 = $this->aiService->generateContent($testData, 'description');
        
        // Seconda chiamata - dovrebbe usare cache
        $content2 = $this->aiService->generateContent($testData, 'description');
        
        $this->assertEquals($content1, $content2);
    }

    public function test_ai_template_fallback(): void
    {
        // Simula errore AI disabilitando il servizio
        $this->app->bind(AITemplateService::class, function () {
            $mock = $this->createMock(AITemplateService::class);
            $mock->method('generateContent')->willThrowException(new \Exception('AI Service unavailable'));
            return $mock;
        });

        $testData = ['name' => 'Test Product'];
        
        // Dovrebbe usare fallback senza errori
        $content = $this->aiService->generateContent($testData, 'description');
        $this->assertIsString($content);
    }

    public function test_ai_blade_directives(): void
    {
        // Test che le direttive AI siano registrate
        $this->assertTrue(app('blade.compiler')->hasDirective('aiContent'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiTranslate'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiPersonalize'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiSeo'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiImage'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiRecommendations'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiReviews'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiMeta'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiCache'));
        $this->assertTrue(app('blade.compiler')->hasDirective('aiFallback'));
    }

    public function test_ai_component(): void
    {
        $component = new \App\View\Components\AIComponent('content', 'Test data');
        
        $this->assertEquals('content', $component->type);
        $this->assertEquals('Test data', $component->data);
        $this->assertIsArray($component->options);
    }

    public function test_product_ai_methods(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 99.99,
            'category' => 'test',
            'status' => 'active',
            'rating' => 4.5,
            'reviews_count' => 100
        ]);

        // Test generazione contenuto AI
        $aiContent = $product->generateAIContent('description');
        $this->assertIsString($aiContent);

        // Test traduzione
        $translation = $product->translateTo('en');
        $this->assertIsArray($translation);
        $this->assertArrayHasKey('name', $translation);
        $this->assertArrayHasKey('description', $translation);

        // Test personalizzazione
        $user = (object)['name' => 'Test User', 'preferences' => ['modern']];
        $personalized = $product->personalizeFor($user);
        $this->assertIsArray($personalized);

        // Test SEO
        $seo = $product->generateAISeo();
        $this->assertIsArray($seo);
        $this->assertArrayHasKey('title', $seo);
        $this->assertArrayHasKey('description', $seo);

        // Test raccomandazioni
        $recommendations = $product->generateAIRecommendations(3);
        $this->assertIsArray($recommendations);

        // Test recensioni
        $reviews = $product->generateAIReviews(2);
        $this->assertIsArray($reviews);

        // Test ottimizzazione immagine
        $optimizedImage = $product->optimizeAIImage();
        $this->assertIsString($optimizedImage);
    }

    public function test_product_ai_caching(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 99.99,
            'category' => 'test',
            'status' => 'active',
            'rating' => 4.5,
            'reviews_count' => 100
        ]);

        // Pulisci cache
        Cache::flush();

        // Prima chiamata - dovrebbe essere cached
        $content1 = $product->generateAIContent('description');
        
        // Seconda chiamata - dovrebbe usare cache
        $content2 = $product->generateAIContent('description');
        
        $this->assertEquals($content1, $content2);
    }

    public function test_product_ai_fallback(): void
    {
        // Simula errore AI disabilitando il servizio
        $this->app->bind(\App\Services\AI\AITemplateService::class, function () {
            $mock = $this->createMock(\App\Services\AI\AITemplateService::class);
            $mock->method('generateContent')->willThrowException(new \Exception('AI Service unavailable'));
            return $mock;
        });

        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 99.99,
            'category' => 'test',
            'status' => 'active',
            'rating' => 4.5,
            'reviews_count' => 100
        ]);

        // Dovrebbe usare fallback senza errori
        $content = $product->generateAIContent('description');
        $this->assertIsString($content);
    }

    public function test_ai_template_error_handling(): void
    {
        $response = $this->postJson('/api/ai-blade/translate', [
            'content' => '' // Contenuto vuoto
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Contenuto richiesto per la traduzione'
                ]);

        $response = $this->postJson('/api/ai-blade/personalize', [
            'content' => '' // Contenuto vuoto
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Contenuto richiesto per la personalizzazione'
                ]);
    }
}
