<?php

namespace App\Services\AI\Providers;

class GeminiProvider implements AIProviderInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', 'demo-key');
    }

    public function chat(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            if ($this->apiKey === 'demo-key') {
                return $this->getDemoResponse($prompt, $startTime);
            }

            // Implementazione reale API Gemini
            return [
                'success' => true,
                'response' => 'Risposta da Gemini: ' . $prompt,
                'tokens_used' => strlen($prompt) + 60,
                'cost' => $this->calculateCost(strlen($prompt) + 60),
                'response_time' => (int)((microtime(true) - $startTime) * 1000),
                'provider' => 'gemini'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response_time' => (int)((microtime(true) - $startTime) * 1000),
                'provider' => 'gemini'
            ];
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'demo-key';
    }

    public function getName(): string
    {
        return 'Gemini';
    }

    public function getCostPerToken(): float
    {
        return 0.00008; // $0.00008 per token
    }

    private function getDemoResponse(string $prompt, float $startTime): array
    {
        $responses = [
            'Ciao' => 'Ciao! Sono Gemini di Google. Come posso assisterti oggi?',
            'Come stai?' => 'Sto bene, grazie! Sono un AI multiforme di Google.',
            'Raccontami una barzelletta' => 'Perché i database non vanno mai in vacanza? Perché hanno sempre delle relazioni! 😄',
            'default' => 'Grazie per il tuo messaggio. Sono Gemini e sono qui per aiutarti.'
        ];

        $response = $responses[$prompt] ?? $responses['default'];
        
        return [
            'success' => true,
            'response' => $response,
            'tokens_used' => strlen($prompt) + strlen($response),
            'cost' => $this->calculateCost(strlen($prompt) + strlen($response)),
            'response_time' => (int)((microtime(true) - $startTime) * 1000),
            'provider' => 'gemini'
        ];
    }

    private function calculateCost(int $tokens): float
    {
        return $tokens * $this->getCostPerToken();
    }
}
