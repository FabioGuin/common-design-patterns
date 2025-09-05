# Prompt Engineering Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Prompt Engineering in Laravel attraverso un sistema di gestione e ottimizzazione di prompt per AI. L'esempio include:

- **Template di prompt** riutilizzabili e configurabili
- **Validazione e ottimizzazione** dei prompt
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare i prompt
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il Prompt Engineering creato gestisce:
- **Template riutilizzabili** per diversi tipi di prompt
- **Validazione** della struttura e contenuto dei prompt
- **Ottimizzazione** per migliorare le performance
- **Test A/B** per confrontare diverse versioni
- **Metriche** per valutare l'efficacia

Quando testi l'esempio, vedrai che:
1. Puoi creare e testare diversi template di prompt
2. Il sistema valida la struttura dei prompt
3. Puoi confrontare le performance di diversi prompt
4. Le metriche ti aiutano a ottimizzare

## Caratteristiche tecniche
- Template di prompt configurabili
- Sistema di validazione robusto
- Ottimizzazione automatica dei prompt
- Test A/B per confronto performance
- Metriche e analytics
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare l'engineering
- Test PHPUnit completi

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Models/PromptTemplate.php app/Models/
cp /path/to/this/example/app/Services/Prompt/PromptTemplateService.php app/Services/Prompt/
cp /path/to/this/example/app/Services/Prompt/PromptValidationService.php app/Services/Prompt/
cp /path/to/this/example/app/Services/Prompt/Templates/ChatTemplate.php app/Services/Prompt/Templates/
cp /path/to/this/example/app/Services/Prompt/Templates/CodeTemplate.php app/Services/Prompt/Templates/
cp /path/to/this/example/app/Services/Prompt/Templates/TranslationTemplate.php app/Services/Prompt/Templates/
cp /path/to/this/example/app/Http/Controllers/PromptController.php app/Http/Controllers/
mkdir -p resources/views/prompt-engineering
cp /path/to/this/example/resources/views/prompt-engineering/example.blade.php resources/views/prompt-engineering/
cp /path/to/this/example/tests/Feature/PromptEngineeringTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\PromptController;

// Route per il pattern Prompt Engineering
Route::get('/prompt-engineering', [PromptController::class, 'show']);
Route::get('/prompt-engineering/test', [PromptController::class, 'test']);
Route::post('/prompt-engineering/generate', [PromptController::class, 'generatePrompt']);

// Route API
Route::prefix('api/prompt-engineering')->group(function () {
    Route::get('/', [PromptController::class, 'index']);
    Route::post('/generate', [PromptController::class, 'generatePrompt']);
    Route::get('/test', [PromptController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/prompt-engineering

# Testa via API
curl http://localhost:8000/api/prompt-engineering/test

# Esegui i test
php artisan test tests/Feature/PromptEngineeringTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/prompt-engineering` e testa i prompt
2. **API**: Esegui `curl http://localhost:8000/api/prompt-engineering/test`
3. **Test**: Esegui `php artisan test tests/Feature/PromptEngineeringTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/PromptTemplate.php` - Modello per i template di prompt
- `app/Services/Prompt/PromptTemplateService.php` - Servizio principale per i template
- `app/Services/Prompt/PromptValidationService.php` - Servizio di validazione
- `app/Services/Prompt/Templates/ChatTemplate.php` - Template per chat
- `app/Services/Prompt/Templates/CodeTemplate.php` - Template per codice
- `app/Services/Prompt/Templates/TranslationTemplate.php` - Template per traduzioni
- `app/Http/Controllers/PromptController.php` - Controller per testare il pattern
- `resources/views/prompt-engineering/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/PromptEngineeringTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica i template in `app/Services/Prompt/Templates/` per personalizzare i prompt.

### Estensione
Aggiungi nuovi template creando classi che implementano l'interfaccia appropriata.

## Note importanti
- Il Prompt Engineering ottimizza i prompt per migliori risultati AI
- Include validazione e test per garantire qualità
- I template sono riutilizzabili e configurabili
- I file sono pronti per essere copiati in un progetto Laravel esistente
