# Prompt Engineering Pattern - Esempio Completo

## Descrizione

Questo esempio implementa un sistema completo di Prompt Engineering per Laravel che gestisce template di prompt strutturati, variabili dinamiche, validazione dell'output e ottimizzazione automatica dei prompt per ottenere risultati consistenti e di qualità.

## Funzionalità

- **Template System**: Sistema di template per prompt strutturati e riutilizzabili
- **Variable Management**: Gestione dinamica di variabili nei prompt
- **Output Validation**: Validazione automatica della qualità dell'output AI
- **Prompt Optimization**: Ottimizzazione automatica basata su metriche di performance
- **Template Library**: Libreria di template predefiniti per casi d'uso comuni
- **A/B Testing**: Sistema per testare e confrontare diversi prompt
- **Analytics**: Metriche dettagliate per ottimizzare i prompt

## Struttura del Progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── PromptController.php
│   ├── Services/Prompt/
│   │   ├── PromptTemplateService.php
│   │   ├── PromptVariableService.php
│   │   ├── PromptValidationService.php
│   │   ├── PromptOptimizationService.php
│   │   └── Templates/
│   │       ├── ProductDescriptionTemplate.php
│   │       ├── EmailTemplate.php
│   │       ├── TranslationTemplate.php
│   │       └── AnalysisTemplate.php
│   ├── Models/
│   │   ├── PromptTemplate.php
│   │   ├── PromptVariable.php
│   │   └── PromptTest.php
│   └── Providers/
│       └── PromptServiceProvider.php
├── config/
│   └── prompt.php
├── database/migrations/
│   ├── create_prompt_templates_table.php
│   ├── create_prompt_variables_table.php
│   └── create_prompt_tests_table.php
├── resources/views/
│   └── prompt/
│       ├── dashboard.blade.php
│       ├── template-editor.blade.php
│       └── test-results.blade.php
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

Visita `/prompt/dashboard` per:
- Creare e modificare template di prompt
- Testare prompt con variabili diverse
- Visualizzare metriche di performance
- Confrontare versioni diverse di prompt

### API Endpoints

- `POST /prompt/api/generate` - Genera contenuto usando template
- `POST /prompt/api/template` - Crea/modifica template
- `GET /prompt/api/templates` - Lista template disponibili
- `POST /prompt/api/test` - Testa prompt con A/B testing
- `GET /prompt/api/analytics` - Metriche e statistiche

### Esempio di Utilizzo

```php
use App\Services\Prompt\PromptTemplateService;

$promptService = app(PromptTemplateService::class);

// Genera descrizione prodotto
$result = $promptService->generate('product_description', [
    'product_name' => 'iPhone 15 Pro',
    'features' => 'A17 Pro chip, 48MP camera, Titanio',
    'price' => '€1199',
    'category' => 'Smartphone'
]);

// Genera email promozionale
$email = $promptService->generate('promotional_email', [
    'customer_name' => 'Mario Rossi',
    'product' => 'iPhone 15 Pro',
    'discount' => '10%',
    'expiry_date' => '2024-12-31'
]);
```

## Configurazione

### Template Configuration

Configura i template in `config/prompt.php`:

```php
'templates' => [
    'product_description' => [
        'class' => \App\Services\Prompt\Templates\ProductDescriptionTemplate::class,
        'variables' => ['product_name', 'features', 'price', 'category'],
        'validation_rules' => [
            'min_length' => 100,
            'max_length' => 500,
            'required_keywords' => ['caratteristiche', 'prezzo']
        ]
    ]
]
```

### Validation Rules

Configura le regole di validazione:

```php
'validation' => [
    'enabled' => true,
    'rules' => [
        'length' => ['min' => 50, 'max' => 1000],
        'keywords' => ['required' => [], 'forbidden' => []],
        'sentiment' => ['min_score' => 0.3],
        'readability' => ['max_grade' => 12]
    ]
]
```

## Esempi di Template

### Template Descrizione Prodotto

```php
class ProductDescriptionTemplate extends BaseTemplate
{
    protected string $template = <<<PROMPT
CONTESTO: Sei un copywriter esperto per un e-commerce di elettronica.

OBIETTIVO: Crea una descrizione prodotto accattivante e tecnica.

FORMATO RICHIESTO:
- Titolo accattivante (max 60 caratteri)
- 3 paragrafi descrittivi (max 200 parole totali)
- Linguaggio tecnico ma accessibile
- Tono persuasivo e professionale

PRODOTTO: {{product_name}}
CARATTERISTICHE: {{features}}
PREZZO: {{price}}
CATEGORIA: {{category}}

VINCOLI:
- Evidenzia caratteristiche tecniche
- Usa linguaggio persuasivo
- Includi benefici per l'utente
- Mantieni un tono positivo ma realistico
PROMPT;
}
```

### Template Email Promozionale

```php
class EmailTemplate extends BaseTemplate
{
    protected string $template = <<<PROMPT
CONTESTO: Sei un esperto di email marketing per un e-commerce.

OBIETTIVO: Crea un'email promozionale persuasiva e personalizzata.

FORMATO RICHIESTO:
- Oggetto accattivante (max 50 caratteri)
- Corpo email in 2-3 paragrafi
- Call-to-action chiara
- Tono amichevole e professionale

CLIENTE: {{customer_name}}
PRODOTTO: {{product}}
SCONTO: {{discount}}
SCADENZA: {{expiry_date}}

VINCOLI:
- Personalizza per il cliente
- Crea urgenza con la scadenza
- Evidenzia il valore dello sconto
- Mantieni un tono professionale
PROMPT;
}
```

## Monitoring e Analytics

### Metriche Disponibili

- **Template Performance**: Qualità media per template
- **Variable Impact**: Impatto delle variabili sui risultati
- **A/B Test Results**: Confronto tra versioni diverse
- **Validation Success Rate**: Percentuale di output validi
- **Cost Analysis**: Costi per template e variabile

### Dashboard Analytics

La dashboard mostra:
- Grafici di performance per template
- Confronto A/B test in tempo reale
- Top template più utilizzati
- Metriche di validazione
- Analisi costi e ROI

## Esempi di Test

### Test Template Singolo

```php
$promptService = app(PromptTemplateService::class);

$result = $promptService->testTemplate('product_description', [
    'product_name' => 'iPhone 15 Pro',
    'features' => 'A17 Pro chip, 48MP camera',
    'price' => '€1199',
    'category' => 'Smartphone'
]);

// Risultato include: output, score, validation_results, cost
```

### A/B Testing

```php
$testResults = $promptService->runABTest([
    'template_a' => 'product_description_v1',
    'template_b' => 'product_description_v2',
    'variables' => $testVariables,
    'iterations' => 10
]);

// Confronta performance, qualità e costi
```

## Troubleshooting

### Template Non Funziona

1. Verifica le variabili richieste
2. Controlla la sintassi del template
3. Testa con dati di esempio
4. Verifica le regole di validazione

### Output di Bassa Qualità

1. Ottimizza il template
2. Aggiungi esempi specifici
3. Migliora le istruzioni
4. Testa con A/B testing

### Performance Lente

1. Ottimizza le variabili
2. Riduci la complessità del template
3. Usa cache per template frequenti
4. Considera template più semplici

## Estensioni

### Aggiungere Nuovo Template

1. Crea la classe template in `app/Services/Prompt/Templates/`
2. Estendi `BaseTemplate`
3. Definisci il template e le variabili
4. Aggiungi la configurazione in `config/prompt.php`
5. Registra il template nel service provider

### Personalizzare Validazione

1. Crea nuove regole di validazione
2. Implementa `ValidationRuleInterface`
3. Aggiungi le regole al template
4. Configura i parametri di validazione

## Supporto

Per problemi o domande:
1. Controlla i log per errori
2. Verifica la configurazione del template
3. Testa con dati di esempio
4. Consulta la documentazione dei template
