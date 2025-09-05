<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AIProvider;

class AIFallbackController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'AI Fallback Pattern Demo',
            'data' => [
                'pattern_description' => 'AI Fallback gestisce automaticamente i fallimenti dei provider',
                'provider_status' => $this->getProviderStatus()
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
            $result = $this->simulateQueryWithFallback($query);
            $results[] = [
                'query' => $query,
                'result' => $result
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'AI Fallback Test Completed',
            'data' => [
                'test_results' => $results,
                'provider_status' => $this->getProviderStatus()
            ]
        ]);
    }

    public function query(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000'
        ]);

        $query = $request->input('query');
        $result = $this->simulateQueryWithFallback($query);

        return response()->json([
            'success' => true,
            'message' => 'Query processed with fallback',
            'data' => $result
        ]);
    }

    public function show()
    {
        return view('ai-fallback.example');
    }

    private function simulateQueryWithFallback(string $query): array
    {
        $providers = ['OpenAI', 'Claude', 'Gemini'];
        $attempts = [];
        $success = false;
        $response = '';

        foreach ($providers as $provider) {
            $attempt = $this->simulateProviderCall($provider, $query);
            $attempts[] = $attempt;
            
            if ($attempt['success']) {
                $success = true;
                $response = $attempt['response'];
                break;
            }
        }

        return [
            'query' => $query,
            'success' => $success,
            'response' => $response ?: 'Tutti i provider sono falliti',
            'attempts' => $attempts,
            'providers_tried' => count($attempts),
            'fallback_used' => count($attempts) > 1
        ];
    }

    private function simulateProviderCall(string $provider, string $query): array
    {
        // Simula fallimento casuale per dimostrare il fallback
        $shouldFail = rand(1, 10) <= 3; // 30% di probabilità di fallimento
        
        if ($shouldFail) {
            return [
                'provider' => $provider,
                'success' => false,
                'error' => 'Provider temporaneamente non disponibile',
                'response_time' => rand(100, 300)
            ];
        }

        $responses = [
            'OpenAI' => 'Risposta da OpenAI: ' . substr($query, 0, 50) . '...',
            'Claude' => 'Risposta da Claude: ' . substr($query, 0, 50) . '...',
            'Gemini' => 'Risposta da Gemini: ' . substr($query, 0, 50) . '...'
        ];

        return [
            'provider' => $provider,
            'success' => true,
            'response' => $responses[$provider] ?? 'Risposta generica',
            'response_time' => rand(200, 600)
        ];
    }

    private function getProviderStatus(): array
    {
        return [
            'OpenAI' => [
                'status' => 'active',
                'success_rate' => 0.85,
                'last_failure' => '2024-01-15 10:30:00',
                'circuit_breaker' => 'closed'
            ],
            'Claude' => [
                'status' => 'active',
                'success_rate' => 0.92,
                'last_failure' => '2024-01-14 15:45:00',
                'circuit_breaker' => 'closed'
            ],
            'Gemini' => [
                'status' => 'active',
                'success_rate' => 0.78,
                'last_failure' => '2024-01-16 09:15:00',
                'circuit_breaker' => 'closed'
            ]
        ];
    }
}
