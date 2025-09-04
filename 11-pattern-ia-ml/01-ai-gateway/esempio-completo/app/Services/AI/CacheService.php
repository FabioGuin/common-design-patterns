<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai.cache', []);
    }

    public function generateKey(string $prompt, array $options = []): string
    {
        // Normalizza il prompt rimuovendo elementi variabili
        $normalizedPrompt = $this->normalizePrompt($prompt);
        
        // Filtra solo le opzioni rilevanti per la cache
        $relevantOptions = $this->filterRelevantOptions($options);
        
        $keyData = [
            'prompt' => $normalizedPrompt,
            'options' => $relevantOptions
        ];
        
        $key = $this->config['prefix'] . md5(json_encode($keyData));
        
        Log::debug('Cache key generated', [
            'key' => $key,
            'prompt_length' => strlen($prompt)
        ]);
        
        return $key;
    }

    public function get(string $key): ?array
    {
        if (!$this->config['enabled']) {
            return null;
        }

        $cached = Cache::get($key);
        
        if ($cached) {
            Log::info('Cache hit', ['key' => $key]);
            return $cached;
        }

        Log::info('Cache miss', ['key' => $key]);
        return null;
    }

    public function set(string $key, array $value, ?int $ttl = null): void
    {
        if (!$this->config['enabled']) {
            return;
        }

        $ttl = $ttl ?? $this->config['ttl'];
        
        // Aggiungi metadata
        $value['cached_at'] = now()->toISOString();
        $value['cache_key'] = $key;
        
        Cache::put($key, $value, $ttl);
        
        Log::info('Response cached', [
            'key' => $key,
            'ttl' => $ttl,
            'size' => strlen(json_encode($value))
        ]);
    }

    public function forget(string $key): bool
    {
        $forgotten = Cache::forget($key);
        
        if ($forgotten) {
            Log::info('Cache entry forgotten', ['key' => $key]);
        }
        
        return $forgotten;
    }

    public function clear(): bool
    {
        $pattern = $this->config['prefix'] . '*';
        $keys = Cache::getRedis()->keys($pattern);
        
        if (empty($keys)) {
            Log::info('No cache entries to clear');
            return true;
        }
        
        $deleted = 0;
        foreach ($keys as $key) {
            if (Cache::forget($key)) {
                $deleted++;
            }
        }
        
        Log::info('Cache cleared', [
            'pattern' => $pattern,
            'keys_deleted' => $deleted
        ]);
        
        return $deleted > 0;
    }

    public function getStats(): array
    {
        $pattern = $this->config['prefix'] . '*';
        $keys = Cache::getRedis()->keys($pattern);
        
        $totalSize = 0;
        $oldestKey = null;
        $newestKey = null;
        $oldestTime = null;
        $newestTime = null;
        
        foreach ($keys as $key) {
            $data = Cache::get($key);
            if ($data) {
                $size = strlen(json_encode($data));
                $totalSize += $size;
                
                $cachedAt = $data['cached_at'] ?? null;
                if ($cachedAt) {
                    $cachedTime = strtotime($cachedAt);
                    
                    if ($oldestTime === null || $cachedTime < $oldestTime) {
                        $oldestTime = $cachedTime;
                        $oldestKey = $key;
                    }
                    
                    if ($newestTime === null || $cachedTime > $newestTime) {
                        $newestTime = $cachedTime;
                        $newestKey = $key;
                    }
                }
            }
        }
        
        return [
            'total_keys' => count($keys),
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'oldest_key' => $oldestKey,
            'newest_key' => $newestKey,
            'oldest_time' => $oldestTime ? date('Y-m-d H:i:s', $oldestTime) : null,
            'newest_time' => $newestTime ? date('Y-m-d H:i:s', $newestTime) : null,
            'hit_rate' => $this->calculateHitRate()
        ];
    }

    private function normalizePrompt(string $prompt): string
    {
        // Rimuovi timestamp e ID casuali
        $prompt = preg_replace('/\b\d{4}-\d{2}-\d{2}\b/', '[DATE]', $prompt);
        $prompt = preg_replace('/\b[A-Z0-9]{8,}\b/', '[ID]', $prompt);
        
        // Normalizza spazi
        $prompt = preg_replace('/\s+/', ' ', trim($prompt));
        
        return $prompt;
    }

    private function filterRelevantOptions(array $options): array
    {
        // Mantieni solo le opzioni che influenzano la risposta
        $relevantKeys = ['model', 'temperature', 'max_tokens', 'language', 'task'];
        
        return array_intersect_key($options, array_flip($relevantKeys));
    }

    private function calculateHitRate(): float
    {
        $hits = Cache::get('ai_cache_hits', 0);
        $misses = Cache::get('ai_cache_misses', 0);
        
        if ($hits + $misses === 0) {
            return 0.0;
        }
        
        return round($hits / ($hits + $misses) * 100, 2);
    }

    public function incrementHit(): void
    {
        Cache::increment('ai_cache_hits');
    }

    public function incrementMiss(): void
    {
        Cache::increment('ai_cache_misses');
    }

    public function getKeysByPattern(string $pattern): array
    {
        $fullPattern = $this->config['prefix'] . $pattern;
        return Cache::getRedis()->keys($fullPattern);
    }

    public function getKeyInfo(string $key): ?array
    {
        $data = Cache::get($key);
        
        if (!$data) {
            return null;
        }
        
        return [
            'key' => $key,
            'cached_at' => $data['cached_at'] ?? null,
            'size_bytes' => strlen(json_encode($data)),
            'ttl' => Cache::getRedis()->ttl($key),
            'has_text' => isset($data['text']),
            'has_image_url' => isset($data['image_url']),
            'provider' => $data['provider'] ?? null
        ];
    }
}
