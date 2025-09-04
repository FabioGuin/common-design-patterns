<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider implements AIProviderInterface
{
    private array $config;
    private string $name = 'claude';
    private int $priority = 2;
    private array $capabilities = ['text_generation', 'translation', 'analysis', 'long_context'];

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
        return $this->config['cost_per_token'] ?? 0.000015;
    }

    public function getModel(): string
    {
        return $this->config['model'] ?? 'claude-3-sonnet-20240229';
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'x-api-key' => $this->config['api_key'],
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01'
                ])
                ->post($this->config['base_url'] . '/messages', [
                    'model' => $this->config['model'],
                    'max_tokens' => 10,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Test'
                        ]
                    ]
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Claude health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders([
                    'x-api-key' => $this->config['api_key'],
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01'
                ])
                ->post($this->config['base_url'] . '/messages', [
                    'model' => $this->config['model'],
                    'max_tokens' => $options['max_tokens'] ?? $this->config['max_tokens'],
                    'temperature' => $options['temperature'] ?? $this->config['temperature'],
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ]);

            if (!$response->successful()) {
                throw new \Exception('Claude API error: ' . $response->body());
            }

            $data = $response->json();
            $duration = microtime(true) - $startTime;

            Log::info('Claude text generation completed', [
                'duration' => $duration,
                'tokens_used' => $data['usage']['input_tokens'] + $data['usage']['output_tokens']
            ]);

            return [
                'text' => $data['content'][0]['text'],
                'tokens_used' => $data['usage']['input_tokens'] + $data['usage']['output_tokens'],
                'model' => $this->config['model'],
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            Log::error('Claude text generation failed', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt)
            ]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        throw new \Exception('Claude non supporta la generazione di immagini');
    }

    public function analyzeDocument(string $content, array $options = []): array
    {
        $prompt = "Analizza il seguente documento e fornisci:\n1. Riassunto esecutivo\n2. Punti chiave principali\n3. Sentiment analysis\n4. Raccomandazioni\n\nDocumento:\n{$content}";
        
        return $this->generateText($prompt, $options);
    }

    public function translate(string $text, string $targetLanguage): array
    {
        $prompt = "Traduci il seguente testo in {$targetLanguage}. Mantieni il tono, lo stile e il contesto originale:\n\n{$text}";
        
        return $this->generateText($prompt);
    }

    public function getAverageResponseTime(): float
    {
        return Cache::get("claude_avg_response_time", 3.0);
    }

    public function updateResponseTime(float $responseTime): void
    {
        $currentAvg = Cache::get("claude_avg_response_time", 3.0);
        $newAvg = ($currentAvg + $responseTime) / 2;
        Cache::put("claude_avg_response_time", $newAvg, 3600);
    }
}
