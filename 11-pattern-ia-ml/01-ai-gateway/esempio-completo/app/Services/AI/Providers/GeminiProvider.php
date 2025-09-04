<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    private array $config;
    private string $name = 'gemini';
    private int $priority = 3;
    private array $capabilities = ['text_generation', 'translation', 'analysis'];

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
        return $this->config['cost_per_token'] ?? 0.00001;
    }

    public function getModel(): string
    {
        return $this->config['model'] ?? 'gemini-pro';
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get($this->config['base_url'] . '/models', [
                    'key' => $this->config['api_key']
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Gemini health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->config['timeout'])
                ->post($this->config['base_url'] . '/models/' . $this->config['model'] . ':generateContent', [
                    'key' => $this->config['api_key']
                ], [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => $options['max_tokens'] ?? $this->config['max_tokens'],
                        'temperature' => $options['temperature'] ?? $this->config['temperature']
                    ]
                ]);

            if (!$response->successful()) {
                throw new \Exception('Gemini API error: ' . $response->body());
            }

            $data = $response->json();
            $duration = microtime(true) - $startTime;

            Log::info('Gemini text generation completed', [
                'duration' => $duration,
                'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? 0
            ]);

            return [
                'text' => $data['candidates'][0]['content']['parts'][0]['text'],
                'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? 0,
                'model' => $this->config['model'],
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            Log::error('Gemini text generation failed', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt)
            ]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        throw new \Exception('Gemini non supporta la generazione di immagini');
    }

    public function analyzeDocument(string $content, array $options = []): array
    {
        $prompt = "Analizza il seguente documento:\n\n{$content}\n\nFornisci:\n- Riassunto\n- Punti chiave\n- Sentiment\n- Raccomandazioni";
        
        return $this->generateText($prompt, $options);
    }

    public function translate(string $text, string $targetLanguage): array
    {
        $prompt = "Traduci il seguente testo in {$targetLanguage}:\n\n{$text}";
        
        return $this->generateText($prompt);
    }

    public function getAverageResponseTime(): float
    {
        return Cache::get("gemini_avg_response_time", 1.8);
    }

    public function updateResponseTime(float $responseTime): void
    {
        $currentAvg = Cache::get("gemini_avg_response_time", 1.8);
        $newAvg = ($currentAvg + $responseTime) / 2;
        Cache::put("gemini_avg_response_time", $newAvg, 3600);
    }
}
