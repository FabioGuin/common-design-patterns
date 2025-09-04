# AI Response Caching Pattern - Esempio Completo

## Descrizione

Questo esempio implementa un sistema completo di AI Response Caching per Laravel che ottimizza le performance e riduce i costi delle chiamate AI attraverso strategie di caching intelligenti, invalidazione automatica e gestione della coerenza dei dati.

## Funzionalità

- **Multi-Strategy Caching**: LRU, LFU, TTL, e strategie personalizzate
- **Intelligent Invalidation**: Invalidazione automatica basata su pattern e regole
- **Cache Warming**: Pre-riscaldamento della cache per performance ottimali
- **Cache Analytics**: Monitoring dettagliato delle performance della cache
- **Compression**: Compressione automatica delle risposte per risparmiare spazio
- **Distributed Caching**: Supporto per cache distribuite (Redis, Memcached)
- **Cache Tags**: Sistema di tag per invalidazione granulare
- **Hit Rate Optimization**: Ottimizzazione automatica del tasso di hit
- **Cost Reduction**: Riduzione significativa dei costi delle API AI

## Struttura del Progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── AICacheController.php
│   ├── Services/AI/
│   │   ├── AICacheService.php
│   │   ├── CacheStrategyManager.php
│   │   ├── CacheInvalidationService.php
│   │   ├── CacheWarmingService.php
│   │   ├── CacheAnalyticsService.php
│   │   └── Strategies/
│   │       ├── LRUCacheStrategy.php
│   │       ├── LFUCacheStrategy.php
│   │       ├── TTLCacheStrategy.php
│   │       └── CustomCacheStrategy.php
│   ├── Models/
│   │   ├── AICacheEntry.php
│   │   ├── CacheHit.php
│   │   └── CacheAnalytics.php
│   └── Providers/
│       └── AICacheServiceProvider.php
├── config/
│   └── ai_cache.php
├── database/migrations/
│   ├── create_ai_cache_entries_table.php
│   ├── create_cache_hits_table.php
│   └── create_cache_analytics_table.php
├── resources/views/
│   └── ai-cache/
│       ├── dashboard.blade.php
│       ├── analytics.blade.php
│       └── management.blade.php
├── routes/
│   └── web.php
├── composer.json
└── README.md
```

## Installazione

1. **Installa le dipendenze**:
   ```bash
   composer install
   ```

2. **Configura le variabili d'ambiente**:
   ```bash
   cp .env.example .env
   ```

3. **Configura la cache**:
   ```env
   CACHE_DRIVER=redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

4. **Esegui le migrazioni**:
   ```bash
   php artisan migrate
   ```

5. **Avvia il server**:
   ```bash
   php artisan serve
   ```

## Utilizzo

### Dashboard Web

Visita `/ai-cache/dashboard` per:
- Visualizzare statistiche della cache
- Monitorare hit rate e performance
- Gestire strategie di caching
- Analizzare i costi risparmiati

### API Endpoints

- `POST /ai-cache/api/cache` - Salva risposta in cache
- `GET /ai-cache/api/cache/{key}` - Recupera risposta dalla cache
- `DELETE /ai-cache/api/cache/{key}` - Invalida entry specifica
- `POST /ai-cache/api/invalidate` - Invalida cache per pattern
- `POST /ai-cache/api/warm` - Pre-riscalda la cache
- `GET /ai-cache/api/analytics` - Ottieni analytics della cache

### Esempio di Utilizzo

```php
use App\Services\AI\AICacheService;

$cacheService = app(AICacheService::class);

// Salva risposta in cache
$cacheService->put('user_query_123', $aiResponse, [
    'ttl' => 3600,
    'tags' => ['user', 'query'],
    'strategy' => 'lru'
]);

// Recupera risposta dalla cache
$cachedResponse = $cacheService->get('user_query_123');

// Invalida cache per pattern
$cacheService->invalidateByPattern('user_*');

// Pre-riscalda cache
$cacheService->warmCache([
    'common_queries' => $commonQueries,
    'popular_requests' => $popularRequests
]);
```

## Configurazione

### Cache Configuration

Configura le strategie di cache in `config/ai_cache.php`:

```php
'strategies' => [
    'lru' => [
        'class' => \App\Services\AI\Strategies\LRUCacheStrategy::class,
        'max_size' => 1000,
        'ttl' => 3600
    ],
    'lfu' => [
        'class' => \App\Services\AI\Strategies\LFUCacheStrategy::class,
        'max_size' => 1000,
        'ttl' => 7200
    ],
    'ttl' => [
        'class' => \App\Services\AI\Strategies\TTLCacheStrategy::class,
        'default_ttl' => 1800,
        'max_ttl' => 86400
    ]
]
```

### Invalidation Rules

Configura le regole di invalidazione:

```php
'invalidation_rules' => [
    'user_*' => [
        'trigger' => 'user_update',
        'ttl' => 300
    ],
    'product_*' => [
        'trigger' => 'product_update',
        'ttl' => 600
    ],
    'global_*' => [
        'trigger' => 'global_update',
        'ttl' => 0
    ]
]
```

## Strategie di Caching

### LRU (Least Recently Used)

```php
$cacheService->put('key', $data, ['strategy' => 'lru']);
```

### LFU (Least Frequently Used)

```php
$cacheService->put('key', $data, ['strategy' => 'lfu']);
```

### TTL (Time To Live)

```php
$cacheService->put('key', $data, ['strategy' => 'ttl', 'ttl' => 3600]);
```

### Custom Strategy

```php
$cacheService->put('key', $data, ['strategy' => 'custom', 'rules' => $customRules]);
```

## Cache Warming

### Pre-riscaldamento Automatico

```php
$cacheService->warmCache([
    'common_queries' => [
        'What is AI?',
        'How does machine learning work?',
        'Best practices for AI development'
    ],
    'popular_requests' => $popularRequests
]);
```

### Pre-riscaldamento Programmato

```php
// In app/Console/Kernel.php
$schedule->call(function () {
    app(AICacheService::class)->warmCache();
})->hourly();
```

## Analytics e Monitoring

### Hit Rate Analysis

```php
$analytics = $cacheService->getAnalytics();
echo "Hit Rate: " . $analytics['hit_rate'] . "%";
echo "Miss Rate: " . $analytics['miss_rate'] . "%";
echo "Total Requests: " . $analytics['total_requests'];
```

### Cost Savings

```php
$savings = $cacheService->getCostSavings();
echo "Cost Saved: $" . $savings['total_saved'];
echo "API Calls Avoided: " . $savings['calls_avoided'];
```

### Performance Metrics

```php
$performance = $cacheService->getPerformanceMetrics();
echo "Average Response Time: " . $performance['avg_response_time'] . "ms";
echo "Cache Size: " . $performance['cache_size'] . " entries";
```

## Esempi di Test

### Test Cache Hit

```php
$cacheService = app(AICacheService::class);

// Primo accesso (miss)
$start = microtime(true);
$response1 = $cacheService->get('test_key');
$time1 = microtime(true) - $start;

// Secondo accesso (hit)
$start = microtime(true);
$response2 = $cacheService->get('test_key');
$time2 = microtime(true) - $start;

echo "Miss time: " . $time1 . "s";
echo "Hit time: " . $time2 . "s";
echo "Speed improvement: " . ($time1 / $time2) . "x";
```

### Test Invalidation

```php
// Salva in cache
$cacheService->put('user_123', $data, ['tags' => ['user']]);

// Invalida per tag
$cacheService->invalidateByTag('user');

// Verifica invalidazione
$result = $cacheService->get('user_123'); // null
```

### Test Compression

```php
$largeData = str_repeat('Large data content', 1000);

// Salva con compressione
$cacheService->put('large_data', $largeData, ['compress' => true]);

// Recupera (decompressione automatica)
$retrieved = $cacheService->get('large_data');
```

## Troubleshooting

### Cache Non Funziona

1. Verifica la configurazione del driver di cache
2. Controlla i log per errori di connessione
3. Verifica che Redis/Memcached sia in esecuzione
4. Controlla i permessi di scrittura

### Performance Lente

1. Controlla la strategia di caching utilizzata
2. Verifica la dimensione massima della cache
3. Considera l'uso di compressione
4. Ottimizza le regole di invalidazione

### Memoria Insufficiente

1. Riduci la dimensione massima della cache
2. Implementa strategie di eviction più aggressive
3. Usa compressione per ridurre l'uso di memoria
4. Considera cache distribuite

## Estensioni

### Aggiungere Nuova Strategia

1. Crea la classe strategy in `app/Services/AI/Strategies/`
2. Implementa `CacheStrategyInterface`
3. Aggiungi la configurazione in `config/ai_cache.php`
4. Registra la strategia in `CacheStrategyManager`

### Personalizzare Invalidation

1. Modifica le regole in `config/ai_cache.php`
2. Implementa logiche personalizzate in `CacheInvalidationService`
3. Aggiungi trigger personalizzati
4. Testa con dati reali

## Supporto

Per problemi o domande:
1. Controlla i log per errori
2. Verifica la configurazione della cache
3. Testa le strategie singolarmente
4. Consulta la documentazione di Laravel Cache
