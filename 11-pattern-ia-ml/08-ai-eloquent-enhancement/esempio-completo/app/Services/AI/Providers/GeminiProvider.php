<?php

namespace App\Services\AI\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('ai.google_ai_api_key');
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Genera embedding per un testo
     */
    public function generateEmbedding(string $text): array
    {
        try {
            $response = $this->client->post($this->baseUrl . '/models/embedding-001:embedContent', [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'model' => 'models/embedding-001',
                    'content' => [
                        'parts' => [
                            ['text' => $text]
                        ]
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['embedding']['values'] ?? [];
        } catch (RequestException $e) {
            Log::error('Errore Gemini embedding', ['error' => $e->getMessage()]);
            throw new \Exception('Errore generazione embedding Gemini: ' . $e->getMessage());
        }
    }

    /**
     * Genera testo basato su un prompt
     */
    public function generateText(string $prompt): string
    {
        try {
            $response = $this->client->post($this->baseUrl . '/models/gemini-pro:generateContent', [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 1000,
                        'temperature' => 0.7
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        } catch (RequestException $e) {
            Log::error('Errore Gemini text generation', ['error' => $e->getMessage()]);
            throw new \Exception('Errore generazione testo Gemini: ' . $e->getMessage());
        }
    }

    /**
     * Verifica se il provider è configurato correttamente
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Ottiene il nome del provider
     */
    public function getName(): string
    {
        return 'Gemini';
    }
}
