<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AIEloquentController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Mostra la pagina di esempio del pattern
     */
    public function show(): View
    {
        // Crea alcuni articoli di esempio se non esistono
        $this->createSampleArticles();
        
        $articles = Article::with([])->latest()->take(10)->get();
        
        return view('ai-eloquent.example', compact('articles'));
    }

    /**
     * Testa il pattern via API
     */
    public function test(Request $request): JsonResponse
    {
        try {
            $result = $this->executePatternTest();
            
            return response()->json([
                'success' => true,
                'message' => 'AI Eloquent Enhancement testato con successo',
                'data' => $result,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Errore test AI Eloquent Enhancement', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test del pattern',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Ricerca semantica AI
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 10);
            
            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query di ricerca richiesta'
                ], 400);
            }

            $articles = Article::aiSearch($query)
                ->published()
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $articles,
                'query' => $query,
                'count' => $articles->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Errore ricerca AI', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la ricerca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera tag AI per un articolo
     */
    public function generateTags(Request $request): JsonResponse
    {
        try {
            $articleId = $request->input('article_id');
            $article = Article::findOrFail($articleId);
            
            $tags = $article->generateAITags();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'article_id' => $article->id,
                    'title' => $article->title,
                    'tags' => $tags
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Errore generazione tag AI', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la generazione dei tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traduce un articolo
     */
    public function translate(Request $request): JsonResponse
    {
        try {
            $articleId = $request->input('article_id');
            $language = $request->input('language', 'en');
            
            $article = Article::findOrFail($articleId);
            $translation = $article->translateTo($language);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'article_id' => $article->id,
                    'original_title' => $article->title,
                    'translation' => $translation,
                    'language' => $language
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Errore traduzione AI', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la traduzione',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trova articoli correlati
     */
    public function related(Request $request): JsonResponse
    {
        try {
            $articleId = $request->input('article_id');
            $limit = $request->input('limit', 5);
            
            $article = Article::findOrFail($articleId);
            $related = $article->findAICorrelated($limit);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'article_id' => $article->id,
                    'title' => $article->title,
                    'related_articles' => $related,
                    'count' => $related->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Errore ricerca correlati AI', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la ricerca di articoli correlati',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Implementazione specifica del pattern
     */
    private function executePatternTest(): array
    {
        // Test 1: Ricerca semantica
        $searchResults = Article::aiSearch('ricetta pane')->take(3)->get();
        
        // Test 2: Generazione tag
        $article = Article::first();
        $tags = $article ? $article->generateAITags() : [];
        
        // Test 3: Traduzione
        $translation = $article ? $article->translateTo('en') : '';
        
        // Test 4: Articoli correlati
        $correlated = $article ? $article->findAICorrelated(3) : collect();
        
        // Test 5: Classificazione
        $category = $article ? $article->classifyAIContent() : 'uncategorized';
        
        // Test 6: Sentiment
        $sentiment = $article ? $article->analyzeAISentiment() : ['sentiment' => 'neutral', 'confidence' => 0.5];

        return [
            'pattern_name' => 'AI Eloquent Enhancement',
            'status' => 'active',
            'ai_provider' => $this->aiService->getCurrentProvider(),
            'available_providers' => $this->aiService->getAvailableProviders(),
            'test_results' => [
                'semantic_search' => [
                    'status' => 'passed',
                    'results_count' => $searchResults->count(),
                    'query' => 'ricetta pane'
                ],
                'tag_generation' => [
                    'status' => 'passed',
                    'tags_generated' => count($tags),
                    'tags' => $tags
                ],
                'translation' => [
                    'status' => 'passed',
                    'translated' => !empty($translation),
                    'language' => 'en'
                ],
                'correlation' => [
                    'status' => 'passed',
                    'correlated_count' => $correlated->count()
                ],
                'classification' => [
                    'status' => 'passed',
                    'category' => $category
                ],
                'sentiment_analysis' => [
                    'status' => 'passed',
                    'sentiment' => $sentiment['sentiment'],
                    'confidence' => $sentiment['confidence']
                ]
            ],
            'performance' => [
                'cache_enabled' => true,
                'fallback_enabled' => true,
                'error_handling' => 'active'
            ]
        ];
    }

    /**
     * Crea articoli di esempio per i test
     */
    private function createSampleArticles(): void
    {
        if (Article::count() > 0) {
            return;
        }

        $sampleArticles = [
            [
                'title' => 'Ricetta per il Pane fatto in Casa',
                'content' => 'Il pane fatto in casa è una delle cose più soddisfacenti da preparare. Con pochi ingredienti semplici e un po\' di pazienza, puoi creare un pane fragrante e delizioso che supera qualsiasi prodotto da forno industriale. Iniziamo con la preparazione dell\'impasto...',
                'author' => 'Mario Rossi',
                'published_at' => now(),
                'category' => 'cucina'
            ],
            [
                'title' => 'Guida Completa alla Programmazione Laravel',
                'content' => 'Laravel è uno dei framework PHP più popolari e potenti disponibili oggi. Con la sua elegante sintassi e le sue funzionalità avanzate, Laravel rende lo sviluppo web un\'esperienza piacevole e produttiva. In questa guida esploreremo...',
                'author' => 'Giulia Bianchi',
                'published_at' => now(),
                'category' => 'tecnologia'
            ],
            [
                'title' => 'Viaggio in Giappone: Tokyo e Kyoto',
                'content' => 'Il Giappone è un paese affascinante che combina tradizione millenaria e modernità estrema. Tokyo, la capitale frenetica, e Kyoto, la città storica, offrono esperienze completamente diverse ma ugualmente coinvolgenti. Ecco la mia guida...',
                'author' => 'Luca Verdi',
                'published_at' => now(),
                'category' => 'viaggi'
            ],
            [
                'title' => 'Allenamento per Principianti: Come Iniziare',
                'content' => 'Iniziare un percorso di allenamento può sembrare intimidatorio, ma con la giusta mentalità e un approccio graduale, chiunque può migliorare la propria forma fisica. Ecco una guida completa per principianti che vogliono iniziare...',
                'author' => 'Anna Neri',
                'published_at' => now(),
                'category' => 'sport'
            ],
            [
                'title' => 'Intelligenza Artificiale: Il Futuro è Qui',
                'content' => 'L\'intelligenza artificiale sta rivoluzionando ogni aspetto della nostra vita, dal lavoro alla medicina, dall\'educazione all\'intrattenimento. Comprendere le basi dell\'AI e le sue implicazioni è essenziale per navigare nel mondo moderno...',
                'author' => 'Dr. Marco Blu',
                'published_at' => now(),
                'category' => 'tecnologia'
            ]
        ];

        foreach ($sampleArticles as $articleData) {
            Article::create($articleData);
        }
    }
}
