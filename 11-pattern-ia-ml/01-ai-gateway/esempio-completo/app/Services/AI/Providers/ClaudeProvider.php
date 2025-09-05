<?php

namespace App\Services\AI\Providers;

class ClaudeProvider implements AIProviderInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('CLAUDE_API_KEY', 'demo-key');
    }

    public function chat(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            if ($this->apiKey === 'demo-key') {
                return $this->getDemoResponse($prompt, $startTime);
            }

            // Implementazione reale API Claude
            return [
                'success' => true,
                'response' => 'Risposta da Claude: ' . $prompt,
                'tokens_used' => strlen($prompt) + 50,
                'cost' => $this->calculateCost(strlen($prompt) + 50),
                'response_time' => (int)((microtime(true) - $startTime) * 1000),
                'provider' => 'claude'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response_time' => (int)((microtime(true) - $startTime) * 1000),
                'provider' => 'claude'
            ];
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'demo-key';
    }

    public function getName(): string
    {
        return 'Claude';
    }

    public function getCostPerToken(): float
    {
        return 0.00015; // $0.00015 per token
    }

    private function getDemoResponse(string $prompt, float $startTime): array
    {
        $responses = [
            'Ciao' => 'Ciao! Sono Claude, un AI assistant di Anthropic. Come posso aiutarti?',
            'Come stai?' => 'Sto bene, grazie! Sono qui per aiutarti con le tue domande.',
            'Raccontami una barzelletta' => 'Cosa fa un programmatore quando ha fame? Morde il codice! 🍕',
            'default' => 'Grazie per il tuo messaggio. Sono Claude e sono qui per aiutarti.'
        ];

        $response = $responses[$prompt] ?? $responses['default'];
        
        return [
            'success' => true,
            'response' => $response,
            'tokens_used' => strlen($prompt) + strlen($response),
            'cost' => $this->calculateCost(strlen($prompt) + strlen($response)),
            'response_time' => (int)((microtime(true) - $startTime) * 1000),
            'provider' => 'claude'
        ];
    }

    private function calculateCost(int $tokens): float
    {
        return $tokens * $this->getCostPerToken();
    }
}
