<?php

namespace App\Services\AI\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ClaudeProvider implements AIProviderInterface
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl = 'https://api.anthropic.com/v1';

    public function __construct()
    {
        $this->apiKey = config('ai.anthropic_api_key');
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ]
        ]);
    }

    /**
     * Genera embedding per un testo
     */
    public function generateEmbedding(string $text): array
    {
        // Claude non ha un endpoint embedding diretto, usiamo una soluzione alternativa
        try {
            $response = $this->client->post($this->baseUrl . '/messages', [
                'json' => [
                    'model' => 'claude-3-sonnet-20240229',
                    'max_tokens' => 1000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Converti questo testo in un array di numeri che rappresentano il significato: {$text}"
                        ]
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $content = $data['content'][0]['text'] ?? '';
            
            // Estrai numeri dalla risposta
            preg_match_all('/-?\d+\.?\d*/', $content, $matches);
            return array_map('floatval', $matches[0]);
        } catch (RequestException $e) {
            Log::error('Errore Claude embedding', ['error' => $e->getMessage()]);
            throw new \Exception('Errore generazione embedding Claude: ' . $e->getMessage());
        }
    }

    /**
     * Genera testo basato su un prompt
     */
    public function generateText(string $prompt): string
    {
        try {
            $response = $this->client->post($this->baseUrl . '/messages', [
                'json' => [
                    'model' => 'claude-3-sonnet-20240229',
                    'max_tokens' => 1000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['content'][0]['text'] ?? '';
        } catch (RequestException $e) {
            Log::error('Errore Claude text generation', ['error' => $e->getMessage()]);
            throw new \Exception('Errore generazione testo Claude: ' . $e->getMessage());
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
        return 'Claude';
    }
}
