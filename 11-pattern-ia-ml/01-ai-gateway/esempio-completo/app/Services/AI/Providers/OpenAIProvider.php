<?php

namespace App\Services\AI\Providers;

class OpenAIProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', 'demo-key');
    }

    public function chat(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            // Simulazione per demo (sostituire con chiamata API reale)
            if ($this->apiKey === 'demo-key') {
                return $this->getDemoResponse($prompt, $startTime);
            }

            // Implementazione reale API OpenAI
            $response = $this->makeApiCall($prompt, $options);
            
            return [
                'success' => true,
                'response' => $response['choices'][0]['message']['content'] ?? 'No response',
                'tokens_used' => $response['usage']['total_tokens'] ?? 0,
                'cost' => $this->calculateCost($response['usage']['total_tokens'] ?? 0),
                'response_time' => (int)((microtime(true) - $startTime) * 1000),
                'provider' => 'openai'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response_time' => (int)((microtime(true) - $startTime) * 1000),
                'provider' => 'openai'
            ];
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'demo-key';
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function getCostPerToken(): float
    {
        return 0.0001; // $0.0001 per token
    }

    private function getDemoResponse(string $prompt, float $startTime): array
    {
        $responses = [
            'Ciao' => 'Ciao! Come posso aiutarti oggi?',
            'Come stai?' => 'Sto bene, grazie! Sono un AI assistant e sono qui per aiutarti.',
            'Raccontami una barzelletta' => 'Perché i programmatori preferiscono il buio? Perché la luce attira i bug! 😄',
            'default' => 'Grazie per il tuo messaggio. Sono un AI assistant e sono qui per aiutarti con le tue domande.'
        ];

        $response = $responses[$prompt] ?? $responses['default'];
        
        return [
            'success' => true,
            'response' => $response,
            'tokens_used' => strlen($prompt) + strlen($response),
            'cost' => $this->calculateCost(strlen($prompt) + strlen($response)),
            'response_time' => (int)((microtime(true) - $startTime) * 1000),
            'provider' => 'openai'
        ];
    }

    private function makeApiCall(string $prompt, array $options): array
    {
        // Implementazione reale della chiamata API OpenAI
        // Questo è un placeholder per la demo
        return [];
    }

    private function calculateCost(int $tokens): float
    {
        return $tokens * $this->getCostPerToken();
    }
}
