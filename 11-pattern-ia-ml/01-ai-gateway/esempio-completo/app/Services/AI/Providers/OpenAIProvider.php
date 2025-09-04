<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    private array $config;
    private string $name = 'openai';
    private int $priority = 1;
    private array $capabilities = ['text_generation', 'image_generation', 'translation', 'analysis'];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    public function getCostPerToken(): float
    {
        return $this->config['cost_per_token'] ?? 0.00003;
    }

    public function getModel(): string
    {
        return $this->config['model'] ?? 'gpt-4';
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->config['api_key'],
                    'Content-Type' => 'application/json',
                ])
                ->get($this->config['base_url'] . '/models');

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('OpenAI health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->config['api_key'],
                    'Content-Type' => 'application/json',
                ])
                ->post($this->config['base_url'] . '/chat/completions', [
                    'model' => $this->config['model'],
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => $options['max_tokens'] ?? $this->config['max_tokens'],
                    'temperature' => $options['temperature'] ?? $this->config['temperature'],
                    'stream' => false
                ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API error: ' . $response->body());
            }

            $data = $response->json();
            $duration = microtime(true) - $startTime;

            Log::info('OpenAI text generation completed', [
                'duration' => $duration,
                'tokens_used' => $data['usage']['total_tokens'] ?? 0
            ]);

            return [
                'text' => $data['choices'][0]['message']['content'],
                'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                'model' => $this->config['model'],
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI text generation failed', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt)
            ]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->config['api_key'],
                    'Content-Type' => 'application/json',
                ])
                ->post($this->config['base_url'] . '/images/generations', [
                    'prompt' => $prompt,
                    'n' => $options['n'] ?? 1,
                    'size' => $options['size'] ?? '1024x1024',
                    'response_format' => 'url'
                ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI Image API error: ' . $response->body());
            }

            $data = $response->json();
            $duration = microtime(true) - $startTime;

            Log::info('OpenAI image generation completed', [
                'duration' => $duration,
                'images_count' => count($data['data'])
            ]);

            return [
                'image_url' => $data['data'][0]['url'],
                'model' => 'dall-e-3',
                'duration' => $duration,
                'cost' => 0.040 // Costo fisso per immagine DALL-E
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI image generation failed', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt)
            ]);
            throw $e;
        }
    }

    public function analyzeDocument(string $content, array $options = []): array
    {
        $prompt = "Analizza il seguente documento e fornisci un riassunto, punti chiave e sentiment:\n\n{$content}";
        
        return $this->generateText($prompt, $options);
    }

    public function translate(string $text, string $targetLanguage): array
    {
        $prompt = "Traduci il seguente testo in {$targetLanguage}. Mantieni il tono e lo stile originale:\n\n{$text}";
        
        return $this->generateText($prompt);
    }

    public function getAverageResponseTime(): float
    {
        // Implementazione semplificata - in produzione useresti metriche reali
        return Cache::get("openai_avg_response_time", 2.5);
    }

    public function updateResponseTime(float $responseTime): void
    {
        $currentAvg = Cache::get("openai_avg_response_time", 2.5);
        $newAvg = ($currentAvg + $responseTime) / 2;
        Cache::put("openai_avg_response_time", $newAvg, 3600);
    }
}
