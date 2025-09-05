<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AICacheEntry;

class AICacheController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'AI Response Caching Pattern Demo',
            'data' => [
                'pattern_description' => 'AI Response Caching ottimizza le performance e riduce i costi',
                'cache_stats' => $this->getCacheStats()
            ]
        ]);
    }

    public function test()
    {
        $testQueries = [
            'Ciao, come stai?',
            'Raccontami una barzelletta',
            'Spiega Laravel in poche parole'
        ];

        $results = [];
        foreach ($testQueries as $query) {
            $result = $this->simulateQuery($query);
            $results[] = [
                'query' => $query,
                'result' => $result
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'AI Response Caching Test Completed',
            'data' => [
                'test_results' => $results,
                'cache_stats' => $this->getCacheStats()
            ]
        ]);
    }

    public function query(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000'
        ]);

        $query = $request->input('query');
        $result = $this->simulateQuery($query);

        return response()->json([
            'success' => true,
            'message' => 'Query processed successfully',
            'data' => $result
        ]);
    }

    public function show()
    {
        return view('ai-response-caching.example');
    }

    private function simulateQuery(string $query): array
    {
        $queryHash = md5($query);
        
        // Simula controllo cache
        $cached = $this->getCachedResponse($queryHash);
        if ($cached) {
            return [
                'query' => $query,
                'response' => $cached['response'],
                'from_cache' => true,
                'hit_count' => $cached['hit_count'],
                'cost_saved' => $cached['cost'],
                'response_time' => rand(10, 50)
            ];
        }

        // Simula chiamata API
        $response = $this->generateAIResponse($query);
        $this->cacheResponse($queryHash, $query, $response);

        return [
            'query' => $query,
            'response' => $response,
            'from_cache' => false,
            'hit_count' => 1,
            'cost' => $this->calculateCost($query),
            'response_time' => rand(200, 800)
        ];
    }

    private function getCachedResponse(string $queryHash): ?array
    {
        // Simula controllo cache (in realtà userebbe Redis o database)
        $cache = [
            'Ciao, come stai?' => [
                'response' => 'Ciao! Sto bene, grazie per aver chiesto. Come posso aiutarti oggi?',
                'hit_count' => 3,
                'cost' => 0.001
            ],
            'Raccontami una barzelletta' => [
                'response' => 'Perché i programmatori preferiscono il buio? Perché la luce attira i bug! 😄',
                'hit_count' => 5,
                'cost' => 0.002
            ]
        ];

        return $cache[$queryHash] ?? null;
    }

    private function generateAIResponse(string $query): string
    {
        $responses = [
            'Ciao' => 'Ciao! Come posso aiutarti oggi?',
            'Laravel' => 'Laravel è un framework PHP elegante e potente per lo sviluppo web moderno.',
            'default' => 'Grazie per la tua domanda. Sono un AI assistant e sono qui per aiutarti.'
        ];

        foreach ($responses as $key => $response) {
            if (str_contains(strtolower($query), strtolower($key))) {
                return $response;
            }
        }

        return $responses['default'];
    }

    private function cacheResponse(string $queryHash, string $query, string $response): void
    {
        // Simula salvataggio in cache
        // In realtà salverebbe in Redis o database
    }

    private function calculateCost(string $query): float
    {
        return strlen($query) * 0.0001;
    }

    private function getCacheStats(): array
    {
        return [
            'total_entries' => 15,
            'hit_rate' => 0.75,
            'total_hits' => 45,
            'total_misses' => 15,
            'cost_saved' => 12.50,
            'avg_response_time_cached' => 25,
            'avg_response_time_uncached' => 450
        ];
    }
}
