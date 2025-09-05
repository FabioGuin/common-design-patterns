<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RateLimit;

class AIRateLimitController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'AI Rate Limiting Pattern Demo',
            'data' => [
                'pattern_description' => 'AI Rate Limiting controlla l\'uso delle API AI',
                'rate_limit_stats' => $this->getRateLimitStats()
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
            $result = $this->simulateQueryWithRateLimit($query);
            $results[] = [
                'query' => $query,
                'result' => $result
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'AI Rate Limiting Test Completed',
            'data' => [
                'test_results' => $results,
                'rate_limit_stats' => $this->getRateLimitStats()
            ]
        ]);
    }

    public function query(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000'
        ]);

        $query = $request->input('query');
        $result = $this->simulateQueryWithRateLimit($query);

        return response()->json([
            'success' => true,
            'message' => 'Query processed with rate limiting',
            'data' => $result
        ]);
    }

    public function show()
    {
        return view('ai-rate-limit.example');
    }

    private function simulateQueryWithRateLimit(string $query): array
    {
        $userId = 1; // Simula un utente
        $endpoint = 'ai-query';
        $limit = 10; // 10 richieste per finestra
        $windowMinutes = 60; // Finestra di 60 minuti

        // Simula controllo rate limit
        $rateLimit = $this->checkRateLimit($userId, $endpoint, $limit, $windowMinutes);
        
        if (!$rateLimit['allowed']) {
            return [
                'query' => $query,
                'success' => false,
                'error' => 'Rate limit exceeded',
                'rate_limit' => $rateLimit,
                'response' => null
            ];
        }

        // Simula chiamata API
        $response = $this->generateAIResponse($query);
        $this->incrementRateLimit($userId, $endpoint);

        return [
            'query' => $query,
            'success' => true,
            'response' => $response,
            'rate_limit' => $rateLimit
        ];
    }

    private function checkRateLimit(int $userId, string $endpoint, int $limit, int $windowMinutes): array
    {
        // Simula controllo rate limit (in realtà userebbe Redis o database)
        $currentRequests = rand(0, $limit + 5); // Simula richieste casuali
        $remaining = max(0, $limit - $currentRequests);
        $allowed = $currentRequests < $limit;
        $resetAt = now()->addMinutes($windowMinutes);

        return [
            'allowed' => $allowed,
            'limit' => $limit,
            'remaining' => $remaining,
            'current_requests' => $currentRequests,
            'reset_at' => $resetAt->toDateTimeString(),
            'window_minutes' => $windowMinutes
        ];
    }

    private function incrementRateLimit(int $userId, string $endpoint): void
    {
        // Simula incremento rate limit
        // In realtà incrementerebbe il contatore in Redis o database
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

    private function getRateLimitStats(): array
    {
        return [
            'total_requests_today' => 1250,
            'rate_limited_requests' => 45,
            'average_requests_per_user' => 12.5,
            'peak_requests_per_hour' => 150,
            'active_users' => 85,
            'quota_usage_percentage' => 78.5
        ];
    }
}
