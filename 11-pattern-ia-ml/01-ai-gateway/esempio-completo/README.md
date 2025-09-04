# AI Gateway Pattern - Esempio Completo

## Descrizione

Questo esempio implementa un sistema completo di AI Gateway per Laravel che gestisce multiple provider AI (OpenAI, Anthropic, Google AI) con fallback automatici, rate limiting, caching e monitoring.

## Funzionalità

- **Multi-Provider Support**: OpenAI GPT-4, Claude, Gemini
- **Fallback Automatico**: Passa automaticamente tra provider in caso di problemi
- **Rate Limiting**: Gestione intelligente dei limiti di ogni provider
- **Caching**: Cache delle risposte per ridurre costi e latenza
- **Monitoring**: Logging e metriche complete
- **Interface Web**: Dashboard per testare e monitorare i provider

## Struttura del Progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── AIGatewayController.php
│   ├── Services/AI/
│   │   ├── AIGatewayService.php
│   │   ├── Providers/
│   │   │   ├── OpenAIProvider.php
│   │   │   ├── ClaudeProvider.php
│   │   │   └── GeminiProvider.php
│   │   ├── RateLimiter.php
│   │   └── CacheService.php
│   └── Providers/
│       └── AIServiceProvider.php
├── config/
│   └── ai.php
├── database/migrations/
│   └── create_ai_requests_table.php
├── resources/views/
│   └── ai-gateway/
│       ├── dashboard.blade.php
│       └── test.blade.php
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

3. **Aggiungi le API key**:
   ```env
   OPENAI_API_KEY=your_openai_key
   ANTHROPIC_API_KEY=your_anthropic_key
   GOOGLE_AI_API_KEY=your_google_key
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

Visita `/ai-gateway/dashboard` per:
- Testare i provider AI
- Visualizzare metriche e statistiche
- Monitorare lo stato dei provider
- Configurare i parametri

### API Endpoints

- `POST /ai-gateway/generate-text` - Genera testo
- `POST /ai-gateway/generate-image` - Genera immagini
- `GET /ai-gateway/status` - Stato dei provider
- `GET /ai-gateway/metrics` - Metriche e statistiche

### Esempio di Utilizzo

```php
use App\Services\AI\AIGatewayService;

$aiService = app(AIGatewayService::class);

// Genera testo
$result = $aiService->generateText('Crea una descrizione per iPhone 15');

// Genera immagine
$image = $aiService->generateImage('Un gatto che programma in PHP');

// Traduci testo
$translation = $aiService->translate('Hello World', 'it');
```

## Configurazione

### Provider AI

Configura i provider in `config/ai.php`:

```php
'providers' => [
    'openai' => [
        'enabled' => true,
        'priority' => 1,
        'api_key' => env('OPENAI_API_KEY'),
        'model' => 'gpt-4',
        'max_tokens' => 2000,
        'temperature' => 0.7
    ],
    'claude' => [
        'enabled' => true,
        'priority' => 2,
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => 'claude-3-sonnet-20240229',
        'max_tokens' => 2000,
        'temperature' => 0.7
    ]
]
```

### Rate Limiting

Configura i limiti per ogni provider:

```php
'rate_limits' => [
    'openai' => [
        'requests_per_minute' => 60,
        'tokens_per_minute' => 150000
    ],
    'claude' => [
        'requests_per_minute' => 50,
        'tokens_per_minute' => 100000
    ]
]
```

### Caching

Configura la cache per le risposte:

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 ora
    'driver' => 'redis'
]
```

## Monitoring

### Logs

I log sono salvati in `storage/logs/laravel.log` e includono:
- Richieste AI con provider utilizzato
- Errori e fallback
- Metriche di performance
- Rate limiting events

### Database

Le richieste sono salvate nella tabella `ai_requests` con:
- Timestamp
- Provider utilizzato
- Durata della richiesta
- Costo stimato
- Successo/fallimento

### Dashboard

La dashboard web mostra:
- Stato dei provider in tempo reale
- Metriche di utilizzo
- Grafici di performance
- Configurazione attuale

## Esempi di Test

### Test Provider Singolo

```php
$provider = new OpenAIProvider();
$result = $provider->generateText('Test prompt');
```

### Test Fallback

```php
// Disabilita OpenAI per testare il fallback
config(['ai.providers.openai.enabled' => false]);

$result = $aiService->generateText('Test fallback');
// Dovrebbe usare Claude automaticamente
```

### Test Rate Limiting

```php
// Simula molte richieste per testare il rate limiting
for ($i = 0; $i < 100; $i++) {
    $aiService->generateText("Test request $i");
}
```

## Troubleshooting

### Provider Non Disponibile

1. Verifica le API key
2. Controlla la connessione internet
3. Verifica i limiti del provider

### Rate Limiting

1. Controlla i limiti configurati
2. Implementa backoff esponenziale
3. Considera l'upgrade del piano

### Cache Issues

1. Verifica la configurazione Redis
2. Controlla i TTL delle cache
3. Pulisci la cache se necessario

## Estensioni

### Aggiungere Nuovo Provider

1. Crea la classe provider in `app/Services/AI/Providers/`
2. Implementa `AIProviderInterface`
3. Aggiungi la configurazione in `config/ai.php`
4. Registra il provider in `AIServiceProvider`

### Personalizzare Fallback

1. Modifica `AIGatewayService::selectProvider()`
2. Implementa logiche di selezione personalizzate
3. Aggiungi metriche per l'ottimizzazione

## Supporto

Per problemi o domande:
1. Controlla i log per errori
2. Verifica la configurazione
3. Testa i provider singolarmente
4. Consulta la documentazione dei provider AI
