# Prompt Engineering Pattern

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

Il Prompt Engineering Pattern è una metodologia strutturata per costruire, ottimizzare e gestire i prompt per i modelli di intelligenza artificiale. Invece di scrivere prompt casuali, questo pattern ti aiuta a creare prompt efficaci, riutilizzabili e ottimizzati per ottenere risultati consistenti e di qualità.

È come avere un "linguaggio di programmazione" per comunicare con l'IA in modo preciso e prevedibile.

## Perché ti serve

Immagina di dover generare contenuti per un e-commerce. Senza prompt engineering:

- Ogni volta riscrivi il prompt da zero
- I risultati sono inconsistenti (a volte troppo lunghi, a volte troppo corti)
- Non riesci a controllare il tono, lo stile o la struttura
- Devi testare manualmente ogni variazione

Con il Prompt Engineering Pattern:

- Hai template riutilizzabili e testati
- Risultati consistenti e prevedibili
- Controllo preciso su output, tono e formato
- Sistema di versioning e ottimizzazione dei prompt

## Come funziona

Il pattern funziona attraverso una struttura gerarchica di prompt:

1. **Template Base**: Struttura generale del prompt
2. **Variabili Dinamiche**: Placeholder per contenuti specifici
3. **Contesto**: Informazioni di background per l'IA
4. **Istruzioni Specifiche**: Regole precise per l'output
5. **Esempi**: Few-shot learning per guidare l'IA
6. **Validazione**: Controlli per verificare la qualità dell'output

## Schema visivo

```
Input Utente: "Genera descrizione prodotto per iPhone 15"

Prompt Template:
┌─────────────────────────────────────┐
│ CONTESTO: E-commerce elettronica    │
│ TONO: Professionale e tecnico       │
│ FORMATO: 3 paragrafi, max 200 parole│
│ ESEMPI: [esempi di descrizioni]     │
│                                     │
│ ISTRUZIONI:                         │
│ - Evidenzia caratteristiche tecniche│
│ - Usa linguaggio persuasivo         │
│ - Includi benefici per l'utente     │
│                                     │
│ PRODOTTO: {nome_prodotto}           │
│ CARATTERISTICHE: {caratteristiche}  │
└─────────────────────────────────────┘
                    ↓
            AI Model Processing
                    ↓
┌─────────────────────────────────────┐
│ OUTPUT VALIDATO:                    │
│ - Lunghezza corretta ✓              │
│ - Tono appropriato ✓                │
│ - Struttura rispettata ✓            │
│ - Contenuto rilevante ✓             │
└─────────────────────────────────────┘
```

*Il diagramma mostra come un prompt strutturato produce output consistenti e di qualità.*

## Quando usarlo

Usa il Prompt Engineering Pattern quando:
- Hai bisogno di output consistenti da modelli AI
- Generi contenuti ripetitivi (descrizioni, email, articoli)
- Vuoi controllare precisamente il formato e il tono
- Hai requisiti specifici di qualità per l'output AI
- Vuoi ottimizzare i costi riducendo le iterazioni

**NON usarlo quando:**
- Hai bisogno di creatività completamente libera
- I requisiti cambiano continuamente senza pattern
- L'output deve essere completamente casuale
- Non hai tempo per strutturare i prompt

## Pro e contro

**I vantaggi:**
- **Consistenza**: Output prevedibili e di qualità costante
- **Efficienza**: Meno iterazioni e test manuali
- **Riutilizzabilità**: Template che funzionano per casi simili
- **Controllo**: Gestione precisa di tono, formato e contenuto
- **Costi**: Riduzione delle chiamate AI non necessarie

**Gli svantaggi:**
- **Complessità iniziale**: Richiede tempo per strutturare i template
- **Rigidità**: Può limitare la creatività dell'IA
- **Manutenzione**: I template vanno aggiornati con i nuovi modelli
- **Curva di apprendimento**: Richiede comprensione dei modelli AI

## Esempi di codice

### Esempio base

```php
<?php

class PromptTemplate
{
    private string $template;
    private array $variables = [];
    private array $examples = [];
    private array $constraints = [];
    
    public function __construct(string $template)
    {
        $this->template = $template;
    }
    
    public function addVariable(string $name, string $value): self
    {
        $this->variables[$name] = $value;
        return $this;
    }
    
    public function addExample(string $input, string $output): self
    {
        $this->examples[] = ['input' => $input, 'output' => $output];
        return $this;
    }
    
    public function addConstraint(string $constraint): self
    {
        $this->constraints[] = $constraint;
        return $this;
    }
    
    public function build(): string
    {
        $prompt = $this->template;
        
        // Sostituisci variabili
        foreach ($this->variables as $name => $value) {
            $prompt = str_replace("{{$name}}", $value, $prompt);
        }
        
        // Aggiungi esempi
        if (!empty($this->examples)) {
            $prompt .= "\n\nEsempi:\n";
            foreach ($this->examples as $example) {
                $prompt .= "Input: {$example['input']}\n";
                $prompt .= "Output: {$example['output']}\n\n";
            }
        }
        
        // Aggiungi vincoli
        if (!empty($this->constraints)) {
            $prompt .= "\nVincoli:\n";
            foreach ($this->constraints as $constraint) {
                $prompt .= "- {$constraint}\n";
            }
        }
        
        return $prompt;
    }
}
```

### Esempio per Laravel

```php
<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;

class PromptEngineeringService
{
    private array $templates = [];
    
    public function registerTemplate(string $name, string $template): void
    {
        $this->templates[$name] = $template;
    }
    
    public function buildPrompt(string $templateName, array $variables = []): string
    {
        if (!isset($this->templates[$templateName])) {
            throw new \InvalidArgumentException("Template '{$templateName}' non trovato");
        }
        
        $template = $this->templates[$templateName];
        
        // Sostituisci variabili
        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }
        
        return $template;
    }
    
    public function generateContent(string $templateName, array $variables = []): array
    {
        $prompt = $this->buildPrompt($templateName, $variables);
        
        // Cache del prompt per evitare rebuild inutili
        $cacheKey = 'prompt_' . md5($prompt);
        
        return Cache::remember($cacheKey, 3600, function () use ($prompt) {
            return $this->callAI($prompt);
        });
    }
    
    private function callAI(string $prompt): array
    {
        // Implementazione chiamata AI
        // Questo sarebbe integrato con il tuo AI Gateway
        return [
            'content' => 'Contenuto generato',
            'tokens_used' => 150,
            'cost' => 0.003
        ];
    }
}

// Template per descrizioni prodotto
class ProductDescriptionTemplate
{
    public static function getTemplate(): string
    {
        return <<<PROMPT
CONTESTO: Sei un copywriter esperto per un e-commerce di elettronica.

OBIETTIVO: Crea una descrizione prodotto accattivante e tecnica.

FORMATO RICHIESTO:
- Titolo accattivante (max 60 caratteri)
- 3 paragrafi descrittivi (max 200 parole totali)
- Linguaggio tecnico ma accessibile
- Tono persuasivo e professionale

PRODOTTO: {{nome_prodotto}}
CARATTERISTICHE: {{caratteristiche}}
PREZZO: {{prezzo}}
CATEGORIA: {{categoria}}

VINCOLI:
- Non usare superlativi eccessivi
- Includi benefici specifici per l'utente
- Evita gergo tecnico incomprensibile
- Mantieni un tono positivo ma realistico

ESEMPI:
Input: iPhone 15 Pro, A17 Pro chip, 48MP camera, Titanio, €1199
Output: [Descrizione esempio...]
PROMPT;
    }
}
```

### Esempio di utilizzo

```php
<?php

// Nel tuo controller o service
class ProductController extends Controller
{
    public function generateDescription(Request $request)
    {
        $promptService = app(PromptEngineeringService::class);
        
        // Registra il template
        $promptService->registerTemplate(
            'product_description',
            ProductDescriptionTemplate::getTemplate()
        );
        
        // Genera contenuto
        $result = $promptService->generateContent('product_description', [
            'nome_prodotto' => 'iPhone 15 Pro',
            'caratteristiche' => 'A17 Pro chip, 48MP camera, Titanio, 6.1"',
            'prezzo' => '€1199',
            'categoria' => 'Smartphone'
        ]);
        
        return response()->json([
            'description' => $result['content'],
            'tokens_used' => $result['tokens_used'],
            'cost' => $result['cost']
        ]);
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema Content Generation](./esempio-completo/)** - Sistema completo per generazione contenuti

L'esempio include:
- Template per diversi tipi di contenuto (prodotti, blog, email)
- Sistema di validazione dell'output
- Cache intelligente per ottimizzare i costi
- Interface per testare e ottimizzare i prompt
- Metriche di performance e qualità

## Pattern correlati

- **Template Method Pattern**: Per strutturare i template di prompt
- **Strategy Pattern**: Per scegliere il template giusto in base al contesto
- **Builder Pattern**: Per costruire prompt complessi step by step
- **Factory Pattern**: Per creare istanze di prompt specifici

## Esempi di uso reale

- **E-commerce**: Generazione automatica descrizioni prodotto
- **Content Marketing**: Creazione articoli e post social
- **Customer Support**: Risposte automatiche personalizzate
- **Email Marketing**: Generazione email promozionali
- **Documentazione**: Creazione guide e tutorial automatici

## Anti-pattern

**Cosa NON fare:**
- **Prompt troppo lunghi**: L'IA si perde in dettagli eccessivi
- **Istruzioni contraddittorie**: Confondono il modello
- **Mancanza di esempi**: L'IA non capisce il formato desiderato
- **Variabili non validate**: Input non controllati possono rompere il prompt
- **Template troppo rigidi**: Limitano la creatività quando serve

## Performance e considerazioni

- **Impatto memoria**: Basso, solo per cache dei template
- **Impatto CPU**: Minimo, principalmente per sostituzione stringhe
- **Scalabilità**: Ottima, i template sono stateless
- **Colli di bottiglia**: Chiamate AI esterne, non il prompt engineering

## Risorse utili

- [OpenAI Prompt Engineering Guide](https://platform.openai.com/docs/guides/prompt-engineering) - Guida ufficiale OpenAI
- [Anthropic Prompt Engineering](https://docs.anthropic.com/claude/docs/prompt-engineering) - Best practices Claude
- [Prompt Engineering Institute](https://www.promptingguide.ai/) - Risorse avanzate
- [Laravel String Helpers](https://laravel.com/docs/helpers#strings) - Per manipolazione stringhe
