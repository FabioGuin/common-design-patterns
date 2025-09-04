# AI Model Abstraction Pattern

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

L'AI Model Abstraction Pattern crea un layer di astrazione che nasconde la complessità dei diversi modelli di intelligenza artificiale dietro un'interfaccia unificata. Invece di gestire direttamente GPT-4, Claude, Gemini o modelli locali, lavori con un'interfaccia standardizzata che si adatta automaticamente al modello migliore per ogni task.

È come avere un "traduttore universale" che parla il linguaggio di ogni modello AI e ti restituisce sempre la stessa interfaccia pulita.

## Perché ti serve

Immagina di dover integrare diversi modelli AI nella tua applicazione:

- **GPT-4**: Ottimo per ragionamento complesso
- **Claude**: Migliore per analisi di documenti lunghi
- **Gemini**: Più veloce per task semplici
- **Modelli locali**: Per privacy e costi

Senza astrazione, dovresti:
- Scrivere codice specifico per ogni modello
- Gestire formati di input/output diversi
- Implementare logiche di fallback complesse
- Aggiornare tutto quando escono nuovi modelli

Con l'AI Model Abstraction Pattern, cambi solo la configurazione e tutto funziona automaticamente.

## Come funziona

Il pattern funziona attraverso una gerarchia di astrazioni:

1. **Model Interface**: Contratto standard per tutti i modelli
2. **Model Adapters**: Adattatori specifici per ogni provider
3. **Model Registry**: Registro dei modelli disponibili
4. **Model Selector**: Logica per scegliere il modello migliore
5. **Response Normalizer**: Standardizzazione delle risposte
6. **Fallback Manager**: Gestione automatica dei fallimenti

## Schema visivo

```
Applicazione
     ↓
┌─────────────────────────────────────┐
│        AI Model Interface          │
│  - generateText()                  │
│  - generateImage()                 │
│  - analyzeDocument()               │
│  - translate()                     │
└─────────────────────────────────────┘
     ↓
┌─────────────────────────────────────┐
│        Model Selector              │
│  - Sceglie modello ottimale        │
│  - Basato su task e requisiti      │
└─────────────────────────────────────┘
     ↓
┌─────────────────────────────────────┐
│        Model Adapters              │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐│
│  │ GPT-4   │ │ Claude  │ │ Gemini  ││
│  │ Adapter │ │ Adapter │ │ Adapter ││
│  └─────────┘ └─────────┘ └─────────┘│
└─────────────────────────────────────┘
     ↓
┌─────────────────────────────────────┐
│      Response Normalizer           │
│  - Standardizza formato output     │
│  - Gestisce errori                 │
│  - Applica fallback se necessario  │
└─────────────────────────────────────┘
     ↓
Risposta Standardizzata
```

*Il diagramma mostra come l'astrazione nasconde la complessità dei diversi modelli AI.*

## Quando usarlo

Usa l'AI Model Abstraction Pattern quando:
- Integri più modelli AI nella stessa applicazione
- Vuoi cambiare modello senza riscrivere il codice
- Hai requisiti diversi per task diversi (velocità vs qualità)
- Vuoi implementare fallback automatici tra modelli
- Hai bisogno di standardizzare le risposte AI

**NON usarlo quando:**
- Usi solo un modello AI e non prevedi di cambiare
- I requisiti di performance sono estremi
- L'applicazione è molto semplice
- Non hai budget per la complessità aggiuntiva

## Pro e contro

**I vantaggi:**
- **Flessibilità**: Cambi modello senza toccare il codice business
- **Ottimizzazione**: Usi il modello migliore per ogni task
- **Affidabilità**: Fallback automatici in caso di problemi
- **Manutenibilità**: Un solo punto per gestire tutti i modelli
- **Testing**: Più facile testare con modelli mock

**Gli svantaggi:**
- **Complessità**: Aggiunge layer di astrazione
- **Overhead**: Piccola latenza per la selezione del modello
- **Debugging**: Può essere più difficile tracciare i problemi
- **Manutenzione**: Devi tenere aggiornati tutti gli adapter

## Esempi di codice

### Esempio base

```php
<?php

interface AIModelInterface
{
    public function generateText(string $prompt, array $options = []): string;
    public function generateImage(string $prompt, array $options = []): string;
    public function analyzeDocument(string $content, array $options = []): array;
    public function translate(string $text, string $targetLanguage): string;
    public function getCapabilities(): array;
    public function getCost(): float;
    public function isAvailable(): bool;
}

abstract class BaseAIModel implements AIModelInterface
{
    protected string $name;
    protected array $capabilities;
    protected float $cost;
    
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }
    
    public function getCost(): float
    {
        return $this->cost;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}

class GPT4Adapter extends BaseAIModel
{
    public function __construct()
    {
        $this->name = 'GPT-4';
        $this->capabilities = ['text_generation', 'reasoning', 'code_generation'];
        $this->cost = 0.03;
    }
    
    public function generateText(string $prompt, array $options = []): string
    {
        // Implementazione specifica GPT-4
        $response = $this->callOpenAI($prompt, $options);
        return $response['choices'][0]['message']['content'];
    }
    
    public function generateImage(string $prompt, array $options = []): string
    {
        throw new \Exception('GPT-4 non supporta generazione immagini');
    }
    
    public function analyzeDocument(string $content, array $options = []): array
    {
        // Implementazione analisi documenti con GPT-4
        return $this->analyzeWithGPT4($content, $options);
    }
    
    public function translate(string $text, string $targetLanguage): string
    {
        $prompt = "Traduci il seguente testo in {$targetLanguage}: {$text}";
        return $this->generateText($prompt);
    }
    
    public function isAvailable(): bool
    {
        return $this->checkOpenAIAvailability();
    }
}

class ClaudeAdapter extends BaseAIModel
{
    public function __construct()
    {
        $this->name = 'Claude';
        $this->capabilities = ['text_generation', 'document_analysis', 'long_context'];
        $this->cost = 0.015;
    }
    
    public function generateText(string $prompt, array $options = []): string
    {
        // Implementazione specifica Claude
        $response = $this->callAnthropic($prompt, $options);
        return $response['content'][0]['text'];
    }
    
    public function generateImage(string $prompt, array $options = []): string
    {
        throw new \Exception('Claude non supporta generazione immagini');
    }
    
    public function analyzeDocument(string $content, array $options = []): array
    {
        // Claude è ottimo per analisi documenti lunghi
        return $this->analyzeWithClaude($content, $options);
    }
    
    public function translate(string $text, string $targetLanguage): string
    {
        $prompt = "Translate the following text to {$targetLanguage}: {$text}";
        return $this->generateText($prompt);
    }
    
    public function isAvailable(): bool
    {
        return $this->checkAnthropicAvailability();
    }
}
```

### Esempio per Laravel

```php
<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AIModelRegistry
{
    private array $models = [];
    private array $taskModelMapping = [];
    
    public function registerModel(AIModelInterface $model): void
    {
        $this->models[$model->getName()] = $model;
        
        // Mappa automatica task -> modelli capaci
        foreach ($model->getCapabilities() as $capability) {
            $this->taskModelMapping[$capability][] = $model->getName();
        }
    }
    
    public function getModelForTask(string $task, array $requirements = []): AIModelInterface
    {
        $availableModels = $this->getAvailableModels();
        $capableModels = $this->taskModelMapping[$task] ?? [];
        
        // Filtra modelli capaci e disponibili
        $candidates = array_intersect($capableModels, array_keys($availableModels));
        
        if (empty($candidates)) {
            throw new \Exception("Nessun modello disponibile per il task: {$task}");
        }
        
        // Scegli il modello migliore basato sui requisiti
        return $this->selectBestModel($candidates, $requirements);
    }
    
    private function selectBestModel(array $candidates, array $requirements): AIModelInterface
    {
        $bestModel = null;
        $bestScore = -1;
        
        foreach ($candidates as $modelName) {
            $model = $this->models[$modelName];
            $score = $this->calculateModelScore($model, $requirements);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestModel = $model;
            }
        }
        
        return $bestModel;
    }
    
    private function calculateModelScore(AIModelInterface $model, array $requirements): float
    {
        $score = 0;
        
        // Punteggio basato sul costo (più basso = migliore)
        $score += (1 / $model->getCost()) * 10;
        
        // Punteggio basato sulla disponibilità
        if ($model->isAvailable()) {
            $score += 50;
        }
        
        // Punteggio basato sui requisiti specifici
        if (isset($requirements['max_cost']) && $model->getCost() <= $requirements['max_cost']) {
            $score += 20;
        }
        
        return $score;
    }
    
    private function getAvailableModels(): array
    {
        return array_filter($this->models, function($model) {
            return $model->isAvailable();
        });
    }
}

class AIModelService
{
    private AIModelRegistry $registry;
    
    public function __construct(AIModelRegistry $registry)
    {
        $this->registry = $registry;
    }
    
    public function generateText(string $prompt, array $options = []): array
    {
        $model = $this->registry->getModelForTask('text_generation', $options);
        
        $startTime = microtime(true);
        $result = $model->generateText($prompt, $options);
        $duration = microtime(true) - $startTime;
        
        Log::info('AI Text Generation', [
            'model' => $model->getName(),
            'prompt_length' => strlen($prompt),
            'duration' => $duration,
            'cost' => $model->getCost()
        ]);
        
        return [
            'text' => $result,
            'model' => $model->getName(),
            'duration' => $duration,
            'cost' => $model->getCost()
        ];
    }
    
    public function analyzeDocument(string $content, array $options = []): array
    {
        $model = $this->registry->getModelForTask('document_analysis', $options);
        
        return [
            'analysis' => $model->analyzeDocument($content, $options),
            'model' => $model->getName(),
            'cost' => $model->getCost()
        ];
    }
    
    public function translate(string $text, string $targetLanguage): array
    {
        $model = $this->registry->getModelForTask('translation', [
            'max_cost' => 0.01 // Preferisci modelli economici per traduzioni
        ]);
        
        return [
            'translation' => $model->translate($text, $targetLanguage),
            'model' => $model->getName(),
            'cost' => $model->getCost()
        ];
    }
}
```

### Configurazione Laravel

```php
<?php

// config/ai.php
return [
    'default_models' => [
        'text_generation' => 'gpt-4',
        'document_analysis' => 'claude',
        'translation' => 'gpt-3.5-turbo',
        'image_generation' => 'dall-e-3'
    ],
    
    'models' => [
        'gpt-4' => [
            'adapter' => \App\Services\AI\GPT4Adapter::class,
            'api_key' => env('OPENAI_API_KEY'),
            'endpoint' => 'https://api.openai.com/v1/chat/completions'
        ],
        'claude' => [
            'adapter' => \App\Services\AI\ClaudeAdapter::class,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'endpoint' => 'https://api.anthropic.com/v1/messages'
        ]
    ],
    
    'fallback_strategy' => 'cost_optimized', // cost_optimized, performance_optimized, reliability_optimized
];

// app/Providers/AIServiceProvider.php
class AIServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AIModelRegistry::class, function ($app) {
            $registry = new AIModelRegistry();
            
            // Registra tutti i modelli configurati
            foreach (config('ai.models') as $name => $config) {
                $adapter = new $config['adapter']();
                $registry->registerModel($adapter);
            }
            
            return $registry;
        });
        
        $this->app->singleton(AIModelService::class);
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema Multi-Model AI](./esempio-completo/)** - Sistema completo con supporto per tutti i modelli

L'esempio include:
- Adapter per OpenAI, Anthropic, Google AI
- Sistema di selezione intelligente del modello
- Fallback automatici e retry logic
- Monitoring e metriche per ogni modello
- Interface per testare e confrontare i modelli

## Pattern correlati

- **Adapter Pattern**: Per adattare interfacce diverse dei modelli AI
- **Strategy Pattern**: Per scegliere il modello migliore per ogni task
- **Factory Pattern**: Per creare istanze dei modelli
- **Registry Pattern**: Per gestire la registrazione dei modelli

## Esempi di uso reale

- **Content Management**: Usa GPT-4 per articoli complessi, Claude per analisi documenti
- **Customer Support**: Fallback automatico tra modelli in base alla disponibilità
- **E-commerce**: Modelli diversi per descrizioni, traduzioni, analisi recensioni
- **Research Tools**: Confronto automatico tra risposte di modelli diversi

## Anti-pattern

**Cosa NON fare:**
- **Hardcoding modelli**: Non mettere logica di selezione modelli nel codice business
- **Ignorare fallback**: Senza fallback, un modello down blocca tutto
- **Senza monitoring**: Impossibile ottimizzare senza metriche
- **Adapter troppo generici**: Perdono le specificità di ogni modello
- **Configurazione hardcoded**: Deve essere facilmente modificabile

## Performance e considerazioni

- **Impatto memoria**: Basso, solo per cache delle configurazioni
- **Impatto CPU**: Minimo, principalmente per selezione del modello
- **Scalabilità**: Ottima, ogni modello è indipendente
- **Colli di bottiglia**: Rate limiting dei provider esterni

## Risorse utili

- [OpenAI API Reference](https://platform.openai.com/docs/api-reference) - Documentazione OpenAI
- [Anthropic Claude API](https://docs.anthropic.com/claude/reference) - Documentazione Claude
- [Google AI Studio](https://aistudio.google.com/) - Per modelli Google
- [Laravel Service Container](https://laravel.com/docs/container) - Per dependency injection
