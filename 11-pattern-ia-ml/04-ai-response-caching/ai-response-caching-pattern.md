# AI Response Caching Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Pattern correlati](#pattern-correlati)
- [Esempi di uso reale](#esempi-di-uso-reale)

### Cosa Evitare
- [Anti-pattern](#anti-pattern)

### Implementazione Pratica
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

### Considerazioni Tecniche
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa

L'AI Response Caching Pattern implementa un sistema di cache intelligente per le risposte dei modelli di intelligenza artificiale. Invece di chiamare l'API AI ogni volta per richieste simili, il sistema memorizza le risposte e le riutilizza quando appropriato, riducendo costi, latenza e carico sui servizi esterni.

È come avere una "memoria fotografica" per l'IA che ricorda le risposte precedenti e le riutilizza quando serve.

## Perché ti serve

Immagina di avere un'app che genera descrizioni prodotto per un e-commerce:

- **Senza cache**: Ogni volta che un utente visita un prodotto, chiami l'API AI
- **Costi**: €0.03 per ogni descrizione = €3000/mese per 100k visite
- **Latenza**: 2-3 secondi per ogni richiesta
- **Rate limiting**: Rischio di superare i limiti dell'API

Con l'AI Response Caching Pattern:
- **Cache hit**: Risposta istantanea dalla cache (50ms)
- **Costi ridotti**: Solo la prima chiamata costa, le altre sono gratuite
- **Scalabilità**: Gestisci migliaia di richieste senza problemi
- **Affidabilità**: Meno dipendenza dalle API esterne

## Come funziona

Il pattern funziona attraverso una strategia di cache multi-livello:

1. **Cache Key Generation**: Crea chiavi uniche basate su prompt e parametri
2. **Cache Lookup**: Controlla se la risposta esiste già
3. **Cache Hit**: Restituisce la risposta memorizzata
4. **Cache Miss**: Chiama l'API AI e memorizza la risposta
5. **Cache Invalidation**: Gestisce l'invalidazione intelligente
6. **Cache Warming**: Pre-carica risposte frequenti

## Schema visivo

```
Richiesta Utente: "Genera descrizione per iPhone 15"
         ↓
┌─────────────────────────────────────┐
│      Cache Key Generator           │
│  - Hash del prompt                 │
│  - Include parametri rilevanti     │
│  - Esclude timestamp/ID casuali    │
└─────────────────────────────────────┘
         ↓
┌─────────────────────────────────────┐
│        Cache Lookup                │
│  - Redis/Memcached                 │
│  - Database cache                  │
│  - File system cache               │
└─────────────────────────────────────┘
         ↓
    Cache Hit? ── SÌ ──→ Risposta Istantanea
         ↓
        NO
         ↓
┌─────────────────────────────────────┐
│        AI API Call                 │
│  - OpenAI/Claude/Gemini            │
│  - Genera risposta                  │
└─────────────────────────────────────┘
         ↓
┌─────────────────────────────────────┐
│        Cache Store                 │
│  - Memorizza risposta              │
│  - Imposta TTL appropriato         │
│  - Aggiunge metadata               │
└─────────────────────────────────────┘
         ↓
    Risposta all'Utente
```

*Il diagramma mostra come il sistema evita chiamate AI duplicate attraverso la cache intelligente.*

## Quando usarlo

Usa l'AI Response Caching Pattern quando:
- Hai richieste AI ripetitive o simili
- Vuoi ridurre i costi delle API AI
- Hai bisogno di migliorare le performance
- Gestisci contenuti che cambiano raramente
- Vuoi ridurre la dipendenza dalle API esterne

**NON usarlo quando:**
- Ogni richiesta è unica e non ripetibile
- I contenuti cambiano molto frequentemente
- Hai requisiti di real-time assoluti
- La cache occupa troppa memoria
- I dati sono sensibili e non possono essere memorizzati

## Pro e contro

**I vantaggi:**
- **Riduzione costi**: Fino al 90% di risparmio su richieste duplicate
- **Performance**: Risposte istantanee per contenuti cached
- **Scalabilità**: Gestisci più traffico senza aumentare i costi
- **Affidabilità**: Meno dipendenza dalle API esterne
- **Rate limiting**: Riduce il rischio di superare i limiti

**Gli svantaggi:**
- **Memoria**: Occupa spazio per memorizzare le risposte
- **Stale data**: Risposte potenzialmente obsolete
- **Complessità**: Gestione della cache e invalidazione
- **Debugging**: Può essere difficile tracciare i problemi
- **Storage**: Necessità di spazio per memorizzare le risposte

## Esempi di codice

### Esempio base

```php
<?php

interface AICacheInterface
{
    public function get(string $key): ?array;
    public function set(string $key, array $value, int $ttl = 3600): void;
    public function delete(string $key): void;
    public function clear(): void;
}

class RedisAICache implements AICacheInterface
{
    private Redis $redis;
    
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }
    
    public function get(string $key): ?array
    {
        $data = $this->redis->get($key);
        return $data ? json_decode($data, true) : null;
    }
    
    public function set(string $key, array $value, int $ttl = 3600): void
    {
        $this->redis->setex($key, $ttl, json_encode($value));
    }
    
    public function delete(string $key): void
    {
        $this->redis->del($key);
    }
    
    public function clear(): void
    {
        $this->redis->flushdb();
    }
}

class AICacheKeyGenerator
{
    public function generateKey(string $prompt, array $options = []): string
    {
        // Normalizza il prompt rimuovendo elementi variabili
        $normalizedPrompt = $this->normalizePrompt($prompt);
        
        // Crea hash basato su prompt e opzioni rilevanti
        $relevantOptions = $this->filterRelevantOptions($options);
        
        $keyData = [
            'prompt' => $normalizedPrompt,
            'options' => $relevantOptions
        ];
        
        return 'ai_cache:' . md5(json_encode($keyData));
    }
    
    private function normalizePrompt(string $prompt): string
    {
        // Rimuovi timestamp, ID casuali, e altri elementi variabili
        $prompt = preg_replace('/\b\d{4}-\d{2}-\d{2}\b/', '[DATE]', $prompt);
        $prompt = preg_replace('/\b[A-Z0-9]{8,}\b/', '[ID]', $prompt);
        
        // Normalizza spazi e caratteri speciali
        $prompt = preg_replace('/\s+/', ' ', trim($prompt));
        
        return $prompt;
    }
    
    private function filterRelevantOptions(array $options): array
    {
        // Mantieni solo le opzioni che influenzano la risposta
        $relevantKeys = ['model', 'temperature', 'max_tokens', 'language'];
        
        return array_intersect_key($options, array_flip($relevantKeys));
    }
}
```

### Esempio per Laravel

```php
<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AICacheService
{
    private AICacheKeyGenerator $keyGenerator;
    private array $cacheConfig;
    
    public function __construct(AICacheKeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
        $this->cacheConfig = config('ai.cache', []);
    }
    
    public function getCachedResponse(string $prompt, array $options = []): ?array
    {
        if (!$this->isCachingEnabled()) {
            return null;
        }
        
        $cacheKey = $this->keyGenerator->generateKey($prompt, $options);
        
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            Log::info('AI Cache Hit', [
                'key' => $cacheKey,
                'prompt_length' => strlen($prompt)
            ]);
            
            return $cached;
        }
        
        Log::info('AI Cache Miss', [
            'key' => $cacheKey,
            'prompt_length' => strlen($prompt)
        ]);
        
        return null;
    }
    
    public function cacheResponse(string $prompt, array $options, array $response): void
    {
        if (!$this->isCachingEnabled()) {
            return;
        }
        
        $cacheKey = $this->keyGenerator->generateKey($prompt, $options);
        $ttl = $this->calculateTTL($prompt, $options);
        
        $cacheData = [
            'response' => $response,
            'cached_at' => now(),
            'prompt_hash' => md5($prompt),
            'options' => $options
        ];
        
        Cache::put($cacheKey, $cacheData, $ttl);
        
        Log::info('AI Response Cached', [
            'key' => $cacheKey,
            'ttl' => $ttl,
            'response_size' => strlen(json_encode($response))
        ]);
    }
    
    private function calculateTTL(string $prompt, array $options): int
    {
        // TTL basato sul tipo di contenuto
        if (str_contains($prompt, 'descrizione prodotto')) {
            return $this->cacheConfig['product_description_ttl'] ?? 86400; // 24h
        }
        
        if (str_contains($prompt, 'traduci') || str_contains($prompt, 'translate')) {
            return $this->cacheConfig['translation_ttl'] ?? 604800; // 7 giorni
        }
        
        if (str_contains($prompt, 'analizza') || str_contains($prompt, 'analyze')) {
            return $this->cacheConfig['analysis_ttl'] ?? 3600; // 1h
        }
        
        // TTL di default
        return $this->cacheConfig['default_ttl'] ?? 3600;
    }
    
    private function isCachingEnabled(): bool
    {
        return $this->cacheConfig['enabled'] ?? true;
    }
    
    public function invalidateByPattern(string $pattern): int
    {
        $keys = Cache::getRedis()->keys("ai_cache:*{$pattern}*");
        $deleted = 0;
        
        foreach ($keys as $key) {
            if (Cache::forget($key)) {
                $deleted++;
            }
        }
        
        Log::info('AI Cache Invalidated', [
            'pattern' => $pattern,
            'keys_deleted' => $deleted
        ]);
        
        return $deleted;
    }
    
    public function getCacheStats(): array
    {
        $keys = Cache::getRedis()->keys('ai_cache:*');
        
        return [
            'total_keys' => count($keys),
            'memory_usage' => $this->calculateMemoryUsage($keys),
            'hit_rate' => $this->calculateHitRate(),
            'oldest_key' => $this->getOldestKey($keys),
            'newest_key' => $this->getNewestKey($keys)
        ];
    }
    
    private function calculateMemoryUsage(array $keys): int
    {
        $totalSize = 0;
        foreach ($keys as $key) {
            $totalSize += strlen(Cache::getRedis()->get($key));
        }
        return $totalSize;
    }
    
    private function calculateHitRate(): float
    {
        // Implementazione semplificata - in produzione useresti metriche più sofisticate
        $hits = Cache::get('ai_cache_hits', 0);
        $misses = Cache::get('ai_cache_misses', 0);
        
        if ($hits + $misses === 0) {
            return 0.0;
        }
        
        return $hits / ($hits + $misses);
    }
}

class CachedAIService
{
    private AICacheService $cacheService;
    private AIModelService $aiService;
    
    public function __construct(AICacheService $cacheService, AIModelService $aiService)
    {
        $this->cacheService = $cacheService;
        $this->aiService = $aiService;
    }
    
    public function generateText(string $prompt, array $options = []): array
    {
        // Controlla cache prima
        $cached = $this->cacheService->getCachedResponse($prompt, $options);
        if ($cached) {
            return array_merge($cached['response'], [
                'cached' => true,
                'cached_at' => $cached['cached_at']
            ]);
        }
        
        // Chiama AI service
        $response = $this->aiService->generateText($prompt, $options);
        
        // Cache la risposta
        $this->cacheService->cacheResponse($prompt, $options, $response);
        
        return array_merge($response, ['cached' => false]);
    }
    
    public function generateImage(string $prompt, array $options = []): array
    {
        // Per le immagini, cache solo l'URL o il path
        $cached = $this->cacheService->getCachedResponse($prompt, $options);
        if ($cached) {
            return array_merge($cached['response'], [
                'cached' => true,
                'cached_at' => $cached['cached_at']
            ]);
        }
        
        $response = $this->aiService->generateImage($prompt, $options);
        
        // Cache solo i metadati, non l'immagine stessa
        $cacheData = [
            'image_url' => $response['image_url'],
            'metadata' => $response['metadata']
        ];
        
        $this->cacheService->cacheResponse($prompt, $options, $cacheData);
        
        return array_merge($response, ['cached' => false]);
    }
}
```

### Configurazione Laravel

```php
<?php

// config/ai.php
return [
    'cache' => [
        'enabled' => env('AI_CACHE_ENABLED', true),
        'driver' => env('AI_CACHE_DRIVER', 'redis'),
        'prefix' => 'ai_cache:',
        
        'ttl' => [
            'default' => 3600, // 1 ora
            'product_description' => 86400, // 24 ore
            'translation' => 604800, // 7 giorni
            'analysis' => 3600, // 1 ora
            'creative_content' => 1800, // 30 minuti
        ],
        
        'max_size' => '100MB', // Dimensione massima cache
        'compression' => true, // Comprimi le risposte lunghe
    ],
    
    'cache_invalidation' => [
        'patterns' => [
            'product_*' => 'when_product_updated',
            'translation_*' => 'when_language_changed',
            'analysis_*' => 'when_data_updated'
        ]
    ]
];

// app/Console/Commands/WarmAICache.php
class WarmAICache extends Command
{
    protected $signature = 'ai:cache:warm {--type=all}';
    protected $description = 'Pre-carica la cache AI con contenuti frequenti';
    
    public function handle()
    {
        $type = $this->option('type');
        
        switch ($type) {
            case 'products':
                $this->warmProductDescriptions();
                break;
            case 'translations':
                $this->warmTranslations();
                break;
            case 'all':
            default:
                $this->warmProductDescriptions();
                $this->warmTranslations();
                break;
        }
        
        $this->info('Cache AI pre-caricata con successo');
    }
    
    private function warmProductDescriptions()
    {
        $products = Product::where('ai_description', null)->get();
        
        foreach ($products as $product) {
            $prompt = "Genera descrizione per {$product->name}";
            app(CachedAIService::class)->generateText($prompt);
        }
    }
    
    private function warmTranslations()
    {
        $commonPhrases = [
            'Add to cart',
            'Buy now',
            'Free shipping',
            'In stock',
            'Out of stock'
        ];
        
        foreach ($commonPhrases as $phrase) {
            $prompt = "Traduci in italiano: {$phrase}";
            app(CachedAIService::class)->generateText($prompt);
        }
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema Cache AI Completo](./esempio-completo/)** - Sistema completo di cache per AI

L'esempio include:
- Cache multi-livello (Redis + Database + File)
- Invalidazione intelligente basata su eventi
- Monitoring e metriche della cache
- Pre-caricamento automatico
- Interface per gestire la cache

## Pattern correlati

- **Cache-Aside Pattern**: Per gestire la cache in modo asincrono
- **Write-Through Pattern**: Per sincronizzare cache e storage
- **Cache Invalidation**: Per gestire l'invalidazione intelligente
- **Observer Pattern**: Per invalidare la cache quando i dati cambiano

## Esempi di uso reale

- **E-commerce**: Cache descrizioni prodotto e traduzioni
- **Content Management**: Cache articoli e contenuti generati
- **Customer Support**: Cache risposte automatiche frequenti
- **Analytics**: Cache analisi e report generati
- **Marketing**: Cache email e contenuti promozionali

## Anti-pattern

**Cosa NON fare:**
- **Cache tutto**: Non cacheare contenuti che cambiano frequentemente
- **TTL troppo lunghi**: Risposte obsolete confondono gli utenti
- **Chiavi non normalizzate**: Duplicati inutili nella cache
- **Senza invalidazione**: Cache che non si aggiorna mai
- **Cache sensibili**: Non cacheare dati personali o sensibili

## Performance e considerazioni

- **Impatto memoria**: Significativo, dipende dalla dimensione delle risposte
- **Impatto CPU**: Basso, principalmente per hash e serializzazione
- **Scalabilità**: Ottima, riduce il carico sulle API esterne
- **Colli di bottiglia**: Spazio di storage per la cache

## Risorse utili

- [Laravel Cache](https://laravel.com/docs/cache) - Sistema cache di Laravel
- [Redis Documentation](https://redis.io/docs/) - Per cache avanzate
- [Cache Patterns](https://docs.aws.amazon.com/AmazonElastiCache/latest/mem-ug/Strategies.html) - Pattern di cache
- [AI Cost Optimization](https://openai.com/pricing) - Per calcolare i risparmi
