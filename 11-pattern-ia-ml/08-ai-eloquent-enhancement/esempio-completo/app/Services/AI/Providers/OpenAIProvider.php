<?php

namespace App\Services\AI\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('ai.openai_api_key');
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
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
            $response = $this->client->post($this->baseUrl . '/embeddings', [
                'json' => [
                    'model' => 'text-embedding-ada-002',
                    'input' => $text
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'][0]['embedding'] ?? [];
        } catch (RequestException $e) {
            Log::error('Errore OpenAI embedding', ['error' => $e->getMessage()]);
            throw new \Exception('Errore generazione embedding OpenAI: ' . $e->getMessage());
        }
    }

    /**
     * Genera testo basato su un prompt
     */
    public function generateText(string $prompt): string
    {
        try {
            $response = $this->client->post($this->baseUrl . '/chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 1000,
                    'temperature' => 0.7
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['choices'][0]['message']['content'] ?? '';
        } catch (RequestException $e) {
            Log::error('Errore OpenAI text generation', ['error' => $e->getMessage()]);
            throw new \Exception('Errore generazione testo OpenAI: ' . $e->getMessage());
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
        return 'OpenAI';
    }
}
