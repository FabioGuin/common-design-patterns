# AI Batch Processing Pattern - Esempio Completo

## Panoramica

Questo esempio dimostra l'implementazione completa del pattern **AI Batch Processing** in Laravel. Il pattern permette di elaborare grandi quantità di richieste AI in modo efficiente, raggruppandole in batch per ottimizzare le performance e ridurre i costi.

## Caratteristiche Principali

- **Gestione completa dei batch**: Creazione, elaborazione, monitoraggio e gestione errori
- **Integrazione multi-provider**: Supporto per OpenAI, Claude e Gemini
- **Interfaccia web intuitiva**: Dashboard per monitorare i batch in tempo reale
- **Sistema di code asincrone**: Elaborazione non bloccante dei batch
- **Gestione errori robusta**: Retry automatico e gestione fallimenti
- **Statistiche dettagliate**: Metriche e monitoraggio delle performance
- **API REST complete**: Endpoint per integrazione con altri sistemi

## Struttura del Progetto

```
app/
├── Http/Controllers/
│   └── AIBatchController.php          # Controller principale
├── Jobs/
│   └── ProcessBatchJob.php            # Job per elaborazione asincrona
├── Models/
│   ├── BatchJob.php                   # Modello per i batch
│   └── BatchRequest.php               # Modello per le richieste
└── Services/
    ├── AI/
    │   └── AIGatewayService.php       # Gateway per provider AI
    └── Batch/
        └── BatchProcessingService.php # Servizio principale
```

## Installazione

### Prerequisiti

- PHP 8.1+
- Laravel 11+
- Composer
- Database (SQLite, MySQL, PostgreSQL)

### Setup

1. **Clona il repository**
   ```bash
   git clone <repository-url>
   cd ai-batch-processing
   ```

2. **Installa le dipendenze**
   ```bash
   composer install
   ```

3. **Configura l'ambiente**
   ```bash
   cp env.example .env
   php artisan key:generate
   ```

4. **Configura il database**
   ```bash
   # Per SQLite (default)
   touch database/database.sqlite
   
   # Per MySQL/PostgreSQL, configura .env
   php artisan migrate
   ```

5. **Configura le API AI**
   ```bash
   # Aggiungi le tue API key in .env
   OPENAI_API_KEY=your_openai_key
   CLAUDE_API_KEY=your_claude_key
   GEMINI_API_KEY=your_gemini_key
   ```

6. **Avvia il server**
   ```bash
   php artisan serve
   ```

## Utilizzo

### Interfaccia Web

Visita `http://localhost:8000` per accedere all'interfaccia web che include:

- **Dashboard principale**: Statistiche e overview dei batch
- **Creazione batch**: Form per creare nuovi batch
- **Monitoraggio**: Visualizzazione dello stato dei batch in tempo reale
- **Gestione errori**: Retry e cancellazione batch

### API REST

#### Creare un Batch

```bash
curl -X POST http://localhost:8000/api/batch/create \
  -H "Content-Type: application/json" \
  -d '{
    "requests": [
      {"input": "Analizza il sentiment di questo testo: \"Questo prodotto è fantastico!\""},
      {"input": "Traduci in inglese: \"Ciao, come stai?\""}
    ],
    "provider": "openai",
    "model": "gpt-3.5-turbo"
  }'
```

#### Ottenere lo Stato di un Batch

```bash
curl http://localhost:8000/api/batch/1/status
```

#### Processare un Batch

```bash
curl -X POST http://localhost:8000/api/batch/1/process
```

#### Ottenere Statistiche

```bash
curl http://localhost:8000/api/batch/statistics
```

### Utilizzo Programmatico

```php
use App\Services\Batch\BatchProcessingService;
use App\Models\BatchJob;

// Crea un batch
$batchService = app(BatchProcessingService::class);

$batchJob = $batchService->createBatch(
    requests: [
        ['input' => 'Test input 1'],
        ['input' => 'Test input 2'],
    ],
    provider: 'openai',
    model: 'gpt-3.5-turbo',
    options: [
        'batch_size' => 50,
        'priority' => 'normal',
        'name' => 'My Custom Batch'
    ]
);

// Processa il batch
$batchService->processBatch($batchJob);

// Monitora il progresso
$progress = $batchJob->getProgressPercentage();
$status = $batchJob->status;
```

## Configurazione

### Configurazione AI

Il file `config/ai.php` contiene tutte le configurazioni per i provider AI:

```php
'providers' => [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'max_batch_size' => 1000,
        'batch_discount' => 0.1, // 10% di sconto
    ],
    // ... altri provider
],
```

### Configurazione Batch

```php
'batch' => [
    'enabled' => true,
    'default_size' => 100,
    'max_size' => 1000,
    'timeout' => 300,
    'retry_attempts' => 3,
    'queue' => 'ai-batch',
],
```

## Testing

### Esegui i Test

```bash
# Test completi
php artisan test

# Test specifici
php artisan test --filter AIBatchProcessingTest

# Test con coverage
php artisan test --coverage
```

### Test Disponibili

- **Creazione batch**: Verifica la creazione corretta dei batch
- **Elaborazione batch**: Test dell'elaborazione e gestione errori
- **API endpoints**: Test di tutti gli endpoint REST
- **Statistiche**: Verifica del calcolo delle metriche
- **Gestione errori**: Test del retry e cancellazione

## Monitoraggio e Logging

### Log

Il sistema genera log dettagliati per:

- Creazione e completamento batch
- Errori di elaborazione
- Performance e metriche
- Operazioni di retry

### Metriche

Le metriche includono:

- Numero totale di batch
- Tasso di successo
- Tempo medio di elaborazione
- Throughput (richieste/secondo)
- Costi per batch

## Architettura

### Pattern Utilizzati

1. **Service Layer**: Separazione della logica di business
2. **Repository Pattern**: Astrazione dell'accesso ai dati
3. **Queue Pattern**: Elaborazione asincrona
4. **Gateway Pattern**: Integrazione con provider esterni
5. **Factory Pattern**: Creazione di oggetti complessi

### Flusso di Elaborazione

```
1. Creazione Batch → 2. Aggiunta Richieste → 3. Schedulazione → 4. Elaborazione → 5. Completamento
```

## Best Practices

### Performance

- Usa batch size ottimali (100-500 richieste)
- Implementa timeout appropriati
- Monitora l'utilizzo della memoria
- Usa code dedicate per i batch

### Affidabilità

- Implementa retry logic robusta
- Gestisci errori gracefully
- Monitora le metriche in tempo reale
- Implementa circuit breaker per i provider

### Sicurezza

- Valida tutti gli input
- Limita la dimensione dei batch
- Implementa rate limiting
- Proteggi le API key

## Troubleshooting

### Problemi Comuni

1. **Batch che non si processano**
   - Verifica la configurazione delle code
   - Controlla i log per errori
   - Verifica le API key

2. **Rate limiting**
   - Riduci la dimensione del batch
   - Aumenta l'intervallo tra i batch
   - Implementa backoff esponenziale

3. **Memoria insufficiente**
   - Riduci la dimensione del batch
   - Ottimizza la gestione della memoria
   - Usa streaming per batch grandi

### Debug

```bash
# Abilita debug mode
APP_DEBUG=true

# Verifica le code
php artisan queue:work --queue=ai-batch

# Monitora i log
tail -f storage/logs/laravel.log
```

## Estensioni

### Aggiungere Nuovi Provider

1. Estendi `AIGatewayService`
2. Implementa il metodo per il nuovo provider
3. Aggiungi la configurazione in `config/ai.php`
4. Aggiorna i test

### Personalizzare il Batch Processing

1. Estendi `BatchProcessingService`
2. Override dei metodi necessari
3. Implementa logica personalizzata
4. Aggiorna la documentazione

## Contributi

1. Fork del repository
2. Crea un branch per la feature
3. Implementa le modifiche
4. Aggiungi i test
5. Crea una pull request

## Licenza

MIT License - vedi il file LICENSE per i dettagli.

## Supporto

Per domande o problemi:

1. Controlla la documentazione
2. Verifica i test esistenti
3. Crea una issue su GitHub
4. Contatta il team di sviluppo

---

**Nota**: Questo esempio è progettato per scopi didattici e dimostrativi. Per l'uso in produzione, assicurati di implementare ulteriori controlli di sicurezza, monitoring e ottimizzazioni specifiche per il tuo caso d'uso.
