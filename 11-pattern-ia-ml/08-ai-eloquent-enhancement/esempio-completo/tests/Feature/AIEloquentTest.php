<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Article;
use App\Services\AI\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AIEloquentTest extends TestCase
{
    use RefreshDatabase;

    protected AIService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = app(AIService::class);
    }

    public function test_ai_eloquent_page_loads(): void
    {
        $response = $this->get('/ai-eloquent');
        $response->assertStatus(200);
        $response->assertSee('AI Eloquent Enhancement');
    }

    public function test_ai_eloquent_api_test_endpoint(): void
    {
        $response = $this->postJson('/api/ai-eloquent/test');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data',
                    'timestamp'
                ]);
    }

    public function test_ai_search_functionality(): void
    {
        // Crea articoli di test
        Article::create([
            'title' => 'Ricetta per il Pane fatto in Casa',
            'content' => 'Il pane fatto in casa è una delle cose più soddisfacenti da preparare.',
            'author' => 'Mario Rossi',
            'published_at' => now(),
            'category' => 'cucina'
        ]);

        $response = $this->postJson('/api/ai-eloquent/search', [
            'query' => 'ricetta pane'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'query',
                    'count'
                ]);
    }

    public function test_ai_tag_generation(): void
    {
        $article = Article::create([
            'title' => 'Guida alla Programmazione Laravel',
            'content' => 'Laravel è un framework PHP moderno e potente.',
            'author' => 'Giulia Bianchi',
            'published_at' => now(),
            'category' => 'tecnologia'
        ]);

        $response = $this->postJson('/api/ai-eloquent/generate-tags', [
            'article_id' => $article->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'article_id',
                        'title',
                        'tags'
                    ]
                ]);
    }

    public function test_ai_translation(): void
    {
        $article = Article::create([
            'title' => 'Ciao Mondo',
            'content' => 'Questo è un articolo di test.',
            'author' => 'Test Author',
            'published_at' => now(),
            'category' => 'test'
        ]);

        $response = $this->postJson('/api/ai-eloquent/translate', [
            'article_id' => $article->id,
            'language' => 'en'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'article_id',
                        'original_title',
                        'translation',
                        'language'
                    ]
                ]);
    }

    public function test_ai_related_articles(): void
    {
        $article = Article::create([
            'title' => 'Articolo di Test',
            'content' => 'Contenuto di test per trovare articoli correlati.',
            'author' => 'Test Author',
            'published_at' => now(),
            'category' => 'test'
        ]);

        $response = $this->postJson('/api/ai-eloquent/related', [
            'article_id' => $article->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'article_id',
                        'title',
                        'related_articles',
                        'count'
                    ]
                ]);
    }

    public function test_ai_eloquent_trait_methods(): void
    {
        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'This is a test article for AI functionality.',
            'author' => 'Test Author',
            'published_at' => now(),
            'category' => 'test'
        ]);

        // Test generazione tag
        $tags = $article->generateAITags();
        $this->assertIsArray($tags);

        // Test traduzione
        $translation = $article->translateTo('en');
        $this->assertIsString($translation);

        // Test riassunto
        $summary = $article->generateAISummary();
        $this->assertIsString($summary);

        // Test classificazione
        $category = $article->classifyAIContent();
        $this->assertIsString($category);

        // Test sentiment
        $sentiment = $article->analyzeAISentiment();
        $this->assertIsArray($sentiment);
        $this->assertArrayHasKey('sentiment', $sentiment);
        $this->assertArrayHasKey('confidence', $sentiment);

        // Test articoli correlati
        $correlated = $article->findAICorrelated();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $correlated);
    }

    public function test_ai_eloquent_caching(): void
    {
        $article = Article::create([
            'title' => 'Test Caching',
            'content' => 'Test content for caching functionality.',
            'author' => 'Test Author',
            'published_at' => now(),
            'category' => 'test'
        ]);

        // Pulisci cache
        Cache::flush();

        // Prima chiamata - dovrebbe essere cached
        $tags1 = $article->generateAITags();
        
        // Seconda chiamata - dovrebbe usare cache
        $tags2 = $article->generateAITags();
        
        $this->assertEquals($tags1, $tags2);
    }

    public function test_ai_eloquent_fallback(): void
    {
        // Simula errore AI disabilitando il servizio
        $this->app->bind(AIService::class, function () {
            $mock = $this->createMock(AIService::class);
            $mock->method('generateTags')->willThrowException(new \Exception('AI Service unavailable'));
            return $mock;
        });

        $article = Article::create([
            'title' => 'Test Fallback',
            'content' => 'Test content for fallback functionality.',
            'author' => 'Test Author',
            'published_at' => now(),
            'category' => 'test'
        ]);

        // Dovrebbe usare fallback senza errori
        $tags = $article->generateAITags();
        $this->assertIsArray($tags);
    }

    public function test_ai_eloquent_scope_methods(): void
    {
        // Crea articoli di test
        Article::create([
            'title' => 'Published Article',
            'content' => 'This is a published article.',
            'author' => 'Author 1',
            'published_at' => now(),
            'category' => 'test'
        ]);

        Article::create([
            'title' => 'Draft Article',
            'content' => 'This is a draft article.',
            'author' => 'Author 2',
            'published_at' => null,
            'category' => 'test'
        ]);

        // Test scope published
        $publishedArticles = Article::published()->get();
        $this->assertCount(1, $publishedArticles);

        // Test scope byCategory
        $testArticles = Article::byCategory('test')->get();
        $this->assertCount(2, $testArticles);

        // Test scope byAuthor
        $author1Articles = Article::byAuthor('Author 1')->get();
        $this->assertCount(1, $author1Articles);

        // Test scope search
        $searchResults = Article::search('published')->get();
        $this->assertCount(1, $searchResults);
    }

    public function test_ai_eloquent_model_attributes(): void
    {
        $article = Article::create([
            'title' => 'Test Attributes',
            'content' => 'Test content for model attributes.',
            'author' => 'Test Author',
            'published_at' => now(),
            'category' => 'test'
        ]);

        // Test accessor AI tags
        $aiTags = $article->ai_tags;
        $this->assertIsArray($aiTags);

        // Test accessor AI summary
        $aiSummary = $article->ai_summary;
        $this->assertIsString($aiSummary);

        // Test accessor AI category
        $aiCategory = $article->ai_category;
        $this->assertIsString($aiCategory);

        // Test accessor AI sentiment
        $aiSentiment = $article->ai_sentiment;
        $this->assertIsArray($aiSentiment);

        // Test accessor AI correlated
        $aiCorrelated = $article->ai_correlated;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $aiCorrelated);
    }

    public function test_ai_eloquent_error_handling(): void
    {
        $response = $this->postJson('/api/ai-eloquent/search', [
            'query' => '' // Query vuota
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Query di ricerca richiesta'
                ]);

        $response = $this->postJson('/api/ai-eloquent/generate-tags', [
            'article_id' => 999 // ID inesistente
        ]);

        $response->assertStatus(500);
    }
}
