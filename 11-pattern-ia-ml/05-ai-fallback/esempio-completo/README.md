# AI Fallback Pattern - Esempio Completo

## Descrizione

Questo esempio implementa un sistema completo di AI Fallback per Laravel che garantisce la continuità del servizio AI attraverso strategie di fallback intelligenti, circuit breaker, retry automatici e gestione degli errori robusta.

## Funzionalità

- **Multi-Provider Fallback**: Fallback automatico tra provider AI diversi
- **Circuit Breaker**: Protezione contro provider non funzionanti
- **Retry Strategies**: Tentativi automatici con backoff esponenziale
- **Health Monitoring**: Monitoraggio continuo dello stato dei provider
- **Graceful Degradation**: Degradazione elegante in caso di errori
- **Caching Fallback**: Cache come fallback per risposte precedenti
- **Queue Fallback**: Elaborazione asincrona come fallback
- **Static Response Fallback**: Risposte statiche predefinite
- **Error Classification**: Classificazione intelligente degli errori
- **Performance Tracking**: Tracking delle performance dei fallback

## Struttura del Progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── AIFallbackController.php
│   ├── Services/AI/
│   │   ├── AIFallbackService.php
│   │   ├── CircuitBreakerService.php
│   │   ├── RetryService.php
│   │   ├── HealthMonitorService.php
│   │   ├── FallbackStrategyManager.php
│   │   └── Strategies/
│   │       ├── ProviderFallbackStrategy.php
│   │       ├── CacheFallbackStrategy.php
│   │       ├── QueueFallbackStrategy.php
│   │       ├── StaticFallbackStrategy.php
│   │       └── HybridFallbackStrategy.php
│   ├── Models/
│   │   ├── AIProvider.php
│   │   ├── FallbackLog.php
│   │   └── CircuitBreakerState.php
│   └── Providers/
│       └── AIFallbackServiceProvider.php
├── config/
│   └── ai_fallback.php
├── database/migrations/
│   ├── create_ai_providers_table.php
│   ├── create_fallback_logs_table.php
│   └── create_circuit_breaker_states_table.php
├── resources/views/
│   └── ai-fallback/
│       ├── dashboard.blade.php
│       ├── providers.blade.php
│       └── logs.blade.php
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

3. **Configura i provider AI**:
   ```env
   OPENAI_API_KEY=your_openai_key
   ANTHROPIC_API_KEY=your_anthropic_key
   GOOGLE_AI_API_KEY=your_google_ai_key
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

Visita `/ai-fallback/dashboard` per:
- Visualizzare lo stato dei provider AI
- Monitorare le performance del fallback
- Gestire le strategie di fallback
- Visualizzare i log degli errori

### API Endpoints

- `POST /ai-fallback/api/generate` - Genera testo con fallback
- `GET /ai-fallback/api/providers` - Lista provider disponibili
- `GET /ai-fallback/api/health` - Stato di salute dei provider
- `POST /ai-fallback/api/retry` - Forza retry di una richiesta
- `GET /ai-fallback/api/logs` - Log degli errori e fallback
- `POST /ai-fallback/api/circuit-breaker/reset` - Reset circuit breaker

### Esempio di Utilizzo

```php
use App\Services\AI\AIFallbackService;

$fallbackService = app(AIFallbackService::class);

// Genera testo con fallback automatico
$response = $fallbackService->generateText('What is AI?', [
    'max_tokens' => 100,
    'temperature' => 0.7,
    'fallback_strategy' => 'provider_chain'
]);

// Genera con strategia specifica
$response = $fallbackService->generateWithStrategy('What is AI?', 'cache_fallback', [
    'cache_ttl' => 3600,
    'fallback_to_static' => true
]);
```

## Configurazione

### Provider Configuration

Configura i provider AI in `config/ai_fallback.php`:

```php
'providers' => [
    'openai' => [
        'class' => \App\Services\AI\Providers\OpenAIProvider::class,
        'api_key' => env('OPENAI_API_KEY'),
        'priority' => 1,
        'timeout' => 30,
        'retry_attempts' => 3
    ],
    'anthropic' => [
        'class' => \App\Services\AI\Providers\AnthropicProvider::class,
        'api_key' => env('ANTHROPIC_API_KEY'),
        'priority' => 2,
        'timeout' => 30,
        'retry_attempts' => 3
    ]
]
```

### Fallback Strategies

Configura le strategie di fallback:

```php
'strategies' => [
    'provider_chain' => [
        'class' => \App\Services\AI\Strategies\ProviderFallbackStrategy::class,
        'providers' => ['openai', 'anthropic', 'google_ai'],
        'circuit_breaker' => true
    ],
    'cache_fallback' => [
        'class' => \App\Services\AI\Strategies\CacheFallbackStrategy::class,
        'cache_ttl' => 3600,
        'fallback_to_static' => true
    ]
]
```

## Strategie di Fallback

### Provider Chain Fallback

```php
$response = $fallbackService->generateWithStrategy('prompt', 'provider_chain');
```

### Cache Fallback

```php
$response = $fallbackService->generateWithStrategy('prompt', 'cache_fallback', [
    'cache_ttl' => 3600,
    'fallback_to_static' => true
]);
```

### Queue Fallback

```php
$response = $fallbackService->generateWithStrategy('prompt', 'queue_fallback', [
    'queue_name' => 'ai-processing',
    'delay' => 60
]);
```

### Static Fallback

```php
$response = $fallbackService->generateWithStrategy('prompt', 'static_fallback', [
    'static_responses' => [
        'What is AI?' => 'AI is artificial intelligence...'
    ]
]);
```

### Hybrid Fallback

```php
$response = $fallbackService->generateWithStrategy('prompt', 'hybrid_fallback', [
    'primary_strategy' => 'provider_chain',
    'secondary_strategy' => 'cache_fallback',
    'tertiary_strategy' => 'static_fallback'
]);
```

## Circuit Breaker

### Configurazione

```php
'circuit_breaker' => [
    'enabled' => true,
    'failure_threshold' => 5,
    'recovery_timeout' => 60,
    'half_open_max_calls' => 3
]
```

### Utilizzo

```php
// Verifica stato circuit breaker
$state = $fallbackService->getCircuitBreakerState('openai');

// Reset circuit breaker
$fallbackService->resetCircuitBreaker('openai');
```

## Retry Strategies

### Configurazione

```php
'retry' => [
    'enabled' => true,
    'max_attempts' => 3,
    'backoff_strategy' => 'exponential',
    'base_delay' => 1000, // millisecondi
    'max_delay' => 30000
]
```

### Utilizzo

```php
// Retry automatico
$response = $fallbackService->generateText('prompt', [
    'retry_enabled' => true,
    'max_retries' => 3
]);

// Retry manuale
$response = $fallbackService->retryRequest($requestId);
```

## Health Monitoring

### Configurazione

```php
'health_monitoring' => [
    'enabled' => true,
    'check_interval' => 60, // secondi
    'timeout' => 10,
    'failure_threshold' => 3
]
```

### Utilizzo

```php
// Verifica salute provider
$health = $fallbackService->getProviderHealth('openai');

// Verifica salute tutti i provider
$allHealth = $fallbackService->getAllProvidersHealth();
```

## Error Classification

### Tipi di Errori

- **Network Error**: Errori di rete (timeout, connessione)
- **Rate Limit Error**: Limiti di rate raggiunti
- **Authentication Error**: Errori di autenticazione
- **Quota Error**: Quote API esaurite
- **Service Error**: Errori del servizio AI
- **Unknown Error**: Errori non classificati

### Utilizzo

```php
// Classifica errore
$errorType = $fallbackService->classifyError($exception);

// Gestisci errore specifico
$response = $fallbackService->handleError($exception, $context);
```

## Logging e Monitoring

### Log Configuration

```php
'logging' => [
    'enabled' => true,
    'log_level' => 'info',
    'log_failures' => true,
    'log_successes' => false,
    'log_retries' => true
]
```

### Utilizzo

```php
// Ottieni log fallback
$logs = $fallbackService->getFallbackLogs([
    'provider' => 'openai',
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31'
]);

// Ottieni statistiche
$stats = $fallbackService->getFallbackStatistics();
```

## Esempi di Test

### Test Fallback Provider

```php
$fallbackService = app(AIFallbackService::class);

// Simula errore provider primario
$response = $fallbackService->generateText('Test prompt', [
    'simulate_provider_failure' => 'openai'
]);

// Verifica fallback
assert($response['provider'] !== 'openai');
assert($response['success'] === true);
```

### Test Circuit Breaker

```php
// Simula multiple failure
for ($i = 0; $i < 6; $i++) {
    $fallbackService->generateText('Test', [
        'simulate_provider_failure' => 'openai'
    ]);
}

// Verifica circuit breaker aperto
$state = $fallbackService->getCircuitBreakerState('openai');
assert($state['state'] === 'open');
```

### Test Retry Strategy

```php
// Test retry con backoff
$start = microtime(true);
$response = $fallbackService->generateText('Test', [
    'retry_enabled' => true,
    'max_retries' => 3,
    'simulate_intermittent_failure' => true
]);
$duration = microtime(true) - $start;

// Verifica che il retry abbia funzionato
assert($response['success'] === true);
assert($response['retry_count'] > 0);
```

## Troubleshooting

### Provider Non Disponibile

1. Verifica le API key
2. Controlla la connessione di rete
3. Verifica i limiti di rate
4. Controlla i log per errori specifici

### Circuit Breaker Aperto

1. Verifica lo stato del provider
2. Controlla i log degli errori
3. Reset manuale se necessario
4. Verifica la configurazione

### Performance Lente

1. Controlla i timeout dei provider
2. Verifica la strategia di retry
3. Ottimizza la configurazione del circuit breaker
4. Considera l'uso di cache

## Estensioni

### Aggiungere Nuovo Provider

1. Crea la classe provider in `app/Services/AI/Providers/`
2. Implementa `AIProviderInterface`
3. Aggiungi la configurazione in `config/ai_fallback.php`
4. Registra il provider in `AIFallbackServiceProvider`

### Aggiungere Nuova Strategia

1. Crea la classe strategy in `app/Services/AI/Strategies/`
2. Implementa `FallbackStrategyInterface`
3. Aggiungi la configurazione in `config/ai_fallback.php`
4. Registra la strategia in `FallbackStrategyManager`

### Personalizzare Error Handling

1. Estendi `ErrorClassifierService`
2. Implementa logiche personalizzate
3. Registra il classificatore personalizzato
4. Testa con scenari reali

## Supporto

Per problemi o domande:
1. Controlla i log per errori
2. Verifica la configurazione dei provider
3. Testa le strategie singolarmente
4. Consulta la documentazione di Laravel
