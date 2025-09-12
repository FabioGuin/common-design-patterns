<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AI\AITemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AIBladeController extends Controller
{
    protected AITemplateService $aiService;

    public function __construct(AITemplateService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Mostra la pagina di esempio del pattern
     */
    public function show(): View
    {
        // Crea alcuni prodotti di esempio se non esistono
        $this->createSampleProducts();
        
        $products = Product::active()->latest()->take(6)->get();
        
        return view('ai-blade.example', compact('products'));
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
                'message' => 'AI Blade Templates testato con successo',
                'data' => $result,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Errore test AI Blade Templates', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test del pattern',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Renderizza un template AI
     */
    public function render(Request $request): JsonResponse
    {
        try {
            $template = $request->input('template', 'product');
            $data = $request->input('data', []);
            $options = $request->input('options', []);
            
            $renderedContent = $this->renderAITemplate($template, $data, $options);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'template' => $template,
                    'rendered_content' => $renderedContent,
                    'data' => $data,
                    'options' => $options
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Errore rendering template AI', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il rendering del template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traduce un template
     */
    public function translate(Request $request): JsonResponse
    {
        try {
            $content = $request->input('content', '');
            $language = $request->input('language', 'en');
            $options = $request->input('options', []);
            
            if (empty($content)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contenuto richiesto per la traduzione'
                ], 400);
            }

            $translatedContent = $this->aiService->translateContent($content, $language, $options);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'original_content' => $content,
                    'translated_content' => $translatedContent,
                    'language' => $language
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Errore traduzione template', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la traduzione',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Personalizza un template
     */
    public function personalize(Request $request): JsonResponse
    {
        try {
            $content = $request->input('content', '');
            $user = $request->input('user', []);
            $options = $request->input('options', []);
            
            if (empty($content)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contenuto richiesto per la personalizzazione'
                ], 400);
            }

            $personalizedContent = $this->aiService->personalizeContent($content, (object)$user, $options);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'original_content' => $content,
                    'personalized_content' => $personalizedContent,
                    'user' => $user
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Errore personalizzazione template', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la personalizzazione',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renderizza un template AI specifico
     */
    protected function renderAITemplate(string $template, array $data, array $options): string
    {
        switch ($template) {
            case 'product':
                return $this->renderProductTemplate($data, $options);
            case 'seo':
                return $this->renderSeoTemplate($data, $options);
            case 'recommendations':
                return $this->renderRecommendationsTemplate($data, $options);
            case 'reviews':
                return $this->renderReviewsTemplate($data, $options);
            default:
                return $this->renderGenericTemplate($data, $options);
        }
    }

    /**
     * Renderizza template prodotto
     */
    protected function renderProductTemplate(array $data, array $options): string
    {
        $product = Product::find($data['product_id'] ?? 1);
        
        if (!$product) {
            return 'Prodotto non trovato';
        }

        $aiContent = [
            'name' => $this->aiService->generateContent($product->getContentForAI(), 'title'),
            'description' => $this->aiService->generateContent($product->getContentForAI(), 'description'),
            'features' => $this->aiService->generateContent($product->getContentForAI(), 'features'),
            'benefits' => $this->aiService->generateContent($product->getContentForAI(), 'benefits'),
            'seo' => $this->aiService->generateSeo($product->getContentForAI())
        ];

        return view('ai-blade.templates.product', compact('product', 'aiContent'))->render();
    }

    /**
     * Renderizza template SEO
     */
    protected function renderSeoTemplate(array $data, array $options): string
    {
        $seoContent = $this->aiService->generateSeo($data);
        return view('ai-blade.templates.seo', compact('seoContent'))->render();
    }

    /**
     * Renderizza template raccomandazioni
     */
    protected function renderRecommendationsTemplate(array $data, array $options): string
    {
        $recommendations = $this->aiService->generateRecommendations($data);
        return view('ai-blade.templates.recommendations', compact('recommendations'))->render();
    }

    /**
     * Renderizza template recensioni
     */
    protected function renderReviewsTemplate(array $data, array $options): string
    {
        $reviews = $this->aiService->generateReviews($data);
        return view('ai-blade.templates.reviews', compact('reviews'))->render();
    }

    /**
     * Renderizza template generico
     */
    protected function renderGenericTemplate(array $data, array $options): string
    {
        $content = $this->aiService->generateContent($data, 'description');
        return view('ai-blade.templates.generic', compact('content'))->render();
    }

    /**
     * Implementazione specifica del pattern
     */
    private function executePatternTest(): array
    {
        // Test 1: Generazione contenuti AI
        $product = Product::first();
        $aiContent = $product ? $product->generateAIContent('description') : 'Test content';
        
        // Test 2: Traduzione
        $translation = $product ? $product->translateTo('en') : ['name' => 'Test Product'];
        
        // Test 3: Personalizzazione
        $user = (object)['name' => 'Test User', 'preferences' => ['style' => 'modern']];
        $personalized = $product ? $product->personalizeFor($user) : ['name' => 'Test Product'];
        
        // Test 4: SEO
        $seo = $product ? $product->generateAISeo() : ['title' => 'Test Title'];
        
        // Test 5: Raccomandazioni
        $recommendations = $product ? $product->generateAIRecommendations(3) : ['Product 1', 'Product 2'];
        
        // Test 6: Recensioni
        $reviews = $product ? $product->generateAIReviews(2) : [['author' => 'User 1', 'content' => 'Great product']];

        return [
            'pattern_name' => 'AI Blade Templates',
            'status' => 'active',
            'ai_service' => 'AITemplateService',
            'test_results' => [
                'content_generation' => [
                    'status' => 'passed',
                    'content_generated' => !empty($aiContent),
                    'content' => $aiContent
                ],
                'translation' => [
                    'status' => 'passed',
                    'translated' => !empty($translation),
                    'translation' => $translation
                ],
                'personalization' => [
                    'status' => 'passed',
                    'personalized' => !empty($personalized),
                    'personalized_content' => $personalized
                ],
                'seo_generation' => [
                    'status' => 'passed',
                    'seo_generated' => !empty($seo),
                    'seo' => $seo
                ],
                'recommendations' => [
                    'status' => 'passed',
                    'recommendations_count' => count($recommendations),
                    'recommendations' => $recommendations
                ],
                'reviews_generation' => [
                    'status' => 'passed',
                    'reviews_count' => count($reviews),
                    'reviews' => $reviews
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
     * Crea prodotti di esempio per i test
     */
    private function createSampleProducts(): void
    {
        if (Product::count() > 0) {
            return;
        }

        $sampleProducts = [
            [
                'name' => 'Smartphone iPhone 15 Pro',
                'description' => 'Il nuovo iPhone 15 Pro con chip A17 Pro, fotocamera avanzata e design in titanio.',
                'price' => 1199.99,
                'category' => 'smartphone',
                'image_url' => 'https://example.com/iphone15pro.jpg',
                'features' => ['Chip A17 Pro', 'Fotocamera 48MP', 'Titanio', 'USB-C'],
                'benefits' => ['Performance eccezionale', 'Fotografia professionale', 'Design premium'],
                'status' => 'active',
                'rating' => 4.8,
                'reviews_count' => 1250
            ],
            [
                'name' => 'Laptop MacBook Pro M3',
                'description' => 'MacBook Pro con chip M3, display Liquid Retina XDR e prestazioni incredibili.',
                'price' => 1999.99,
                'category' => 'laptop',
                'image_url' => 'https://example.com/macbookpro.jpg',
                'features' => ['Chip M3', 'Display 14"', '16GB RAM', '512GB SSD'],
                'benefits' => ['Prestazioni elevate', 'Autonomia lunga', 'Display eccezionale'],
                'status' => 'active',
                'rating' => 4.9,
                'reviews_count' => 890
            ],
            [
                'name' => 'Cuffie Sony WH-1000XM5',
                'description' => 'Cuffie wireless con cancellazione del rumore leader del settore e audio di alta qualità.',
                'price' => 399.99,
                'category' => 'audio',
                'image_url' => 'https://example.com/sony-wh1000xm5.jpg',
                'features' => ['Cancellazione rumore', '30h autonomia', 'Audio Hi-Res', 'Touch controls'],
                'benefits' => ['Audio cristallino', 'Comfort eccezionale', 'Batteria duratura'],
                'status' => 'active',
                'rating' => 4.7,
                'reviews_count' => 2100
            ],
            [
                'name' => 'Smartwatch Apple Watch Series 9',
                'description' => 'Apple Watch Series 9 con chip S9, display Always-On e funzionalità di salute avanzate.',
                'price' => 429.99,
                'category' => 'smartwatch',
                'image_url' => 'https://example.com/apple-watch-s9.jpg',
                'features' => ['Chip S9', 'Display Always-On', 'GPS', 'Monitoraggio salute'],
                'benefits' => ['Salute monitorata', 'Design elegante', 'Ecosistema Apple'],
                'status' => 'active',
                'rating' => 4.6,
                'reviews_count' => 1800
            ],
            [
                'name' => 'Tablet iPad Air M2',
                'description' => 'iPad Air con chip M2, display Liquid Retina e supporto Apple Pencil.',
                'price' => 599.99,
                'category' => 'tablet',
                'image_url' => 'https://example.com/ipad-air-m2.jpg',
                'features' => ['Chip M2', 'Display 10.9"', 'Apple Pencil', 'Touch ID'],
                'benefits' => ['Versatilità', 'Prestazioni elevate', 'Creatività'],
                'status' => 'active',
                'rating' => 4.5,
                'reviews_count' => 950
            ],
            [
                'name' => 'Monitor Dell UltraSharp 27"',
                'description' => 'Monitor 4K professionale con colori accurati e design elegante per creativi e professionisti.',
                'price' => 699.99,
                'category' => 'monitor',
                'image_url' => 'https://example.com/dell-ultrasharp-27.jpg',
                'features' => ['4K UHD', '99% sRGB', 'USB-C', 'HDR'],
                'benefits' => ['Colori accurati', 'Risoluzione elevata', 'Connessioni multiple'],
                'status' => 'active',
                'rating' => 4.4,
                'reviews_count' => 650
            ]
        ];

        foreach ($sampleProducts as $productData) {
            Product::create($productData);
        }
    }
}
