# AI Model Abstraction Pattern - Esempio Completo

## Descrizione

Questo esempio implementa un sistema completo di AI Model Abstraction per Laravel che gestisce multiple provider AI (OpenAI, Anthropic, Google AI, Hugging Face) attraverso un'interfaccia unificata, con selezione intelligente del modello, fallback automatici e monitoring completo.

## Funzionalità

- **Multi-Provider Support**: OpenAI, Claude, Gemini, Hugging Face
- **Model Selection**: Selezione intelligente basata su task, costo e performance
- **Unified Interface**: Interfaccia standardizzata per tutti i modelli
- **Automatic Fallback**: Fallback automatico tra modelli in caso di problemi
- **Performance Monitoring**: Tracking dettagliato di performance e costi
- **Model Registry**: Registro centralizzato di tutti i modelli disponibili
- **Capability Mapping**: Mappatura delle capacità di ogni modello
- **Cost Optimization**: Ottimizzazione automatica dei costi

## Struttura del Progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── AIModelController.php
│   ├── Services/AI/
│   │   ├── AIModelRegistry.php
│   │   ├── AIModelSelector.php
│   │   ├── AIModelService.php
│   │   ├── ModelPerformanceTracker.php
│   │   └── Providers/
│   │       ├── OpenAIProvider.php
│   │       ├── ClaudeProvider.php
│   │       ├── GeminiProvider.php
│   │       └── HuggingFaceProvider.php
│   ├── Models/
│   │   ├── AIModel.php
│   │   ├── ModelUsage.php
│   │   └── ModelPerformance.php
│   └── Providers/
│       └── AIModelServiceProvider.php
├── config/
│   └── ai_models.php
├── database/migrations/
│   ├── create_ai_models_table.php
│   ├── create_model_usage_table.php
│   └── create_model_performance_table.php
├── resources/views/
│   └── ai-models/
│       ├── dashboard.blade.php
│       ├── model-comparison.blade.php
│       └── performance.blade.php
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
   HUGGINGFACE_API_KEY=your_huggingface_key
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

Visita `/ai-models/dashboard` per:
- Visualizzare tutti i modelli disponibili
- Confrontare performance e costi
- Testare modelli con task specifici
- Monitorare utilizzo e metriche

### API Endpoints

- `POST /ai-models/api/generate` - Genera contenuto usando il modello migliore
- `GET /ai-models/api/models` - Lista modelli disponibili
- `POST /ai-models/api/compare` - Confronta modelli per un task
- `GET /ai-models/api/performance` - Metriche di performance
- `POST /ai-models/api/select-model` - Seleziona modello specifico

### Esempio di Utilizzo

```php
use App\Services\AI\AIModelService;

$modelService = app(AIModelService::class);

// Genera testo usando il modello migliore
$result = $modelService->generateText('Crea una descrizione per iPhone 15', [
    'task' => 'product_description',
    'max_cost' => 0.01,
    'max_duration' => 5.0
]);

// Genera immagine usando il modello specifico
$image = $modelService->generateImage('Un gatto che programma', [
    'model' => 'dall-e-3',
    'size' => '1024x1024'
]);

// Traduci usando il modello più economico
$translation = $modelService->translate('Hello World', 'it', [
    'optimize_for' => 'cost'
]);
```

## Configurazione

### Model Configuration

Configura i modelli in `config/ai_models.php`:

```php
'models' => [
    'gpt-4' => [
        'provider' => 'openai',
        'capabilities' => ['text_generation', 'reasoning', 'code_generation'],
        'cost_per_token' => 0.00003,
        'max_tokens' => 8192,
        'priority' => 1
    ],
    'claude-3-sonnet' => [
        'provider' => 'claude',
        'capabilities' => ['text_generation', 'document_analysis', 'long_context'],
        'cost_per_token' => 0.000015,
        'max_tokens' => 200000,
        'priority' => 2
    ]
]
```

### Selection Strategy

Configura la strategia di selezione:

```php
'selection_strategy' => 'balanced', // balanced, cost_optimized, performance_optimized

'strategies' => [
    'balanced' => [
        'cost_weight' => 0.3,
        'performance_weight' => 0.4,
        'availability_weight' => 0.3
    ],
    'cost_optimized' => [
        'cost_weight' => 0.7,
        'performance_weight' => 0.2,
        'availability_weight' => 0.1
    ]
]
```

## Esempi di Modelli

### OpenAI Models

```php
'openai' => [
    'gpt-4' => [
        'capabilities' => ['text_generation', 'reasoning', 'code_generation'],
        'cost_per_token' => 0.00003,
        'max_tokens' => 8192,
        'context_window' => 128000
    ],
    'gpt-3.5-turbo' => [
        'capabilities' => ['text_generation', 'translation'],
        'cost_per_token' => 0.0000015,
        'max_tokens' => 4096,
        'context_window' => 16384
    ],
    'dall-e-3' => [
        'capabilities' => ['image_generation'],
        'cost_per_image' => 0.040,
        'max_images' => 1
    ]
]
```

### Claude Models

```php
'claude' => [
    'claude-3-opus' => [
        'capabilities' => ['text_generation', 'reasoning', 'analysis'],
        'cost_per_token' => 0.000075,
        'max_tokens' => 200000,
        'context_window' => 200000
    ],
    'claude-3-sonnet' => [
        'capabilities' => ['text_generation', 'document_analysis'],
        'cost_per_token' => 0.000015,
        'max_tokens' => 200000,
        'context_window' => 200000
    ]
]
```

### Gemini Models

```php
'gemini' => [
    'gemini-pro' => [
        'capabilities' => ['text_generation', 'translation', 'analysis'],
        'cost_per_token' => 0.00001,
        'max_tokens' => 32768,
        'context_window' => 32768
    ],
    'gemini-pro-vision' => [
        'capabilities' => ['text_generation', 'image_analysis'],
        'cost_per_token' => 0.00001,
        'max_tokens' => 16384
    ]
]
```

## Monitoring e Analytics

### Performance Metrics

- **Response Time**: Tempo medio di risposta per modello
- **Success Rate**: Percentuale di successo delle richieste
- **Cost Analysis**: Analisi costi per modello e task
- **Usage Patterns**: Pattern di utilizzo nel tempo
- **Error Tracking**: Tracking dettagliato degli errori

### Model Comparison

- **Side-by-side Comparison**: Confronto diretto tra modelli
- **Performance Benchmarks**: Benchmark per task specifici
- **Cost Efficiency**: Analisi efficienza costi
- **Quality Metrics**: Metriche di qualità dell'output

## Esempi di Test

### Test Model Singolo

```php
$modelService = app(AIModelService::class);

$result = $modelService->testModel('gpt-4', [
    'task' => 'text_generation',
    'prompt' => 'Test prompt',
    'iterations' => 5
]);
```

### Confronto Modelli

```php
$comparison = $modelService->compareModels([
    'gpt-4',
    'claude-3-sonnet',
    'gemini-pro'
], [
    'task' => 'product_description',
    'test_data' => $testData
]);
```

### Benchmark Performance

```php
$benchmark = $modelService->runBenchmark([
    'models' => ['gpt-4', 'claude-3-sonnet'],
    'tasks' => ['text_generation', 'translation', 'analysis'],
    'iterations' => 10
]);
```

## Troubleshooting

### Model Non Disponibile

1. Verifica le API key
2. Controlla la connessione internet
3. Verifica i limiti del provider
4. Controlla i log per errori specifici

### Performance Lente

1. Controlla la strategia di selezione
2. Verifica i timeout configurati
3. Considera modelli più veloci
4. Ottimizza i prompt

### Costi Elevati

1. Usa strategia cost_optimized
2. Seleziona modelli più economici
3. Implementa caching
4. Monitora l'utilizzo

## Estensioni

### Aggiungere Nuovo Provider

1. Crea la classe provider in `app/Services/AI/Providers/`
2. Implementa `AIModelProviderInterface`
3. Aggiungi la configurazione in `config/ai_models.php`
4. Registra il provider in `AIModelServiceProvider`

### Personalizzare Selezione

1. Modifica `AIModelSelector::selectBestModel()`
2. Implementa logiche di selezione personalizzate
3. Aggiungi metriche per l'ottimizzazione
4. Testa con dati reali

## Supporto

Per problemi o domande:
1. Controlla i log per errori
2. Verifica la configurazione dei modelli
3. Testa i provider singolarmente
4. Consulta la documentazione dei provider AI
