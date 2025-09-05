<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $ttl = 60)
    {
        // Solo per richieste GET
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Genera chiave cache
        $cacheKey = $this->generateCacheKey($request);

        // Verifica se esiste in cache
        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);
            
            // Log hit cache
            Log::info('Cache Hit', [
                'url' => $request->fullUrl(),
                'cache_key' => $cacheKey,
                'ttl' => $ttl,
            ]);

            // Aggiungi header per indicare che è una risposta cached
            $cachedResponse->headers->set('X-Cache', 'HIT');
            $cachedResponse->headers->set('X-Cache-Key', $cacheKey);
            $cachedResponse->headers->set('X-Cache-TTL', $ttl);

            return $cachedResponse;
        }

        // Esegui la richiesta
        $response = $next($request);

        // Cache solo se la risposta è valida
        if ($this->shouldCache($response)) {
            // Clona la risposta per evitare problemi con il stream
            $responseToCache = clone $response;
            
            // Rimuovi header che non dovrebbero essere cached
            $this->removeUncacheableHeaders($responseToCache);

            // Salva in cache
            Cache::put($cacheKey, $responseToCache, $ttl);

            // Log cache miss
            Log::info('Cache Miss - Stored', [
                'url' => $request->fullUrl(),
                'cache_key' => $cacheKey,
                'ttl' => $ttl,
                'status_code' => $response->getStatusCode(),
            ]);

            // Aggiungi header per indicare che è stata cached
            $response->headers->set('X-Cache', 'MISS');
            $response->headers->set('X-Cache-Key', $cacheKey);
            $response->headers->set('X-Cache-TTL', $ttl);
        } else {
            // Log cache miss senza storage
            Log::info('Cache Miss - Not Stored', [
                'url' => $request->fullUrl(),
                'reason' => 'Response not cacheable',
                'status_code' => $response->getStatusCode(),
            ]);

            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }

    /**
     * Genera chiave cache univoca per la richiesta
     */
    protected function generateCacheKey(Request $request): string
    {
        $key = 'http_cache:' . md5($request->fullUrl());
        
        // Aggiungi parametri di query se presenti
        if ($request->query()) {
            $key .= ':' . md5(serialize($request->query()));
        }

        // Aggiungi user ID se autenticato (per cache per-utente)
        if (auth()->check()) {
            $key .= ':user:' . auth()->id();
        }

        return $key;
    }

    /**
     * Determina se la risposta dovrebbe essere cached
     */
    protected function shouldCache($response): bool
    {
        // Non cacheare se status code non è 200
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        // Non cacheare se ha header di no-cache
        if ($response->headers->has('Cache-Control')) {
            $cacheControl = $response->headers->get('Cache-Control');
            if (strpos($cacheControl, 'no-cache') !== false || 
                strpos($cacheControl, 'no-store') !== false) {
                return false;
            }
        }

        // Non cacheare se è una risposta JSON con errori
        if ($response->headers->get('Content-Type') === 'application/json') {
            $data = $response->getData(true);
            if (isset($data['success']) && !$data['success']) {
                return false;
            }
        }

        // Non cacheare se la risposta è troppo grande (> 1MB)
        if ($response->headers->has('Content-Length')) {
            $contentLength = (int) $response->headers->get('Content-Length');
            if ($contentLength > 1024 * 1024) { // 1MB
                return false;
            }
        }

        return true;
    }

    /**
     * Rimuove header che non dovrebbero essere cached
     */
    protected function removeUncacheableHeaders($response): void
    {
        $uncacheableHeaders = [
            'Set-Cookie',
            'Authorization',
            'X-Cache',
            'X-Cache-Key',
            'X-Cache-TTL',
            'X-Response-Time',
            'X-Powered-By',
        ];

        foreach ($uncacheableHeaders as $header) {
            $response->headers->remove($header);
        }
    }

    /**
     * Invalida cache per una specifica URL pattern
     */
    public static function invalidateCache(string $pattern): int
    {
        $keys = Cache::getRedis()->keys('http_cache:*');
        $invalidated = 0;

        foreach ($keys as $key) {
            if (strpos($key, $pattern) !== false) {
                Cache::forget($key);
                $invalidated++;
            }
        }

        Log::info('Cache Invalidated', [
            'pattern' => $pattern,
            'invalidated_count' => $invalidated,
        ]);

        return $invalidated;
    }

    /**
     * Pulisce tutta la cache HTTP
     */
    public static function clearAllCache(): int
    {
        $keys = Cache::getRedis()->keys('http_cache:*');
        $cleared = 0;

        foreach ($keys as $key) {
            Cache::forget($key);
            $cleared++;
        }

        Log::info('All HTTP Cache Cleared', [
            'cleared_count' => $cleared,
        ]);

        return $cleared;
    }
}
