# AI Eloquent Enhancement - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra l'AI Eloquent Enhancement in Laravel attraverso un sistema di blog con funzionalità AI integrate. L'esempio include:

- **Ricerca semantica** che capisce il significato delle query, non solo le parole chiave
- **Generazione automatica di tag** basata sul contenuto degli articoli
- **Traduzione automatica** degli articoli in diverse lingue
- **Suggerimenti di articoli correlati** basati sul significato semantico

## Come funziona l'esempio
L'AI Eloquent Enhancement creato gestisce:
- **Trait AIEloquentEnhancement** per aggiungere funzionalità AI ai modelli Eloquent
- **Service AI** per gestire le chiamate ai provider AI (OpenAI, Claude, Gemini)
- **Cache intelligente** per ottimizzare le chiamate AI costose
- **Fallback automatico** quando i servizi AI non sono disponibili

Quando testi l'esempio, vedrai che:
1. Puoi cercare articoli con query semantiche come "ricetta pane" e trovare articoli correlati
2. I tag vengono generati automaticamente basandosi sul contenuto
3. Gli articoli possono essere tradotti in tempo reale
4. Il sistema suggerisce articoli correlati basati sul significato

## Caratteristiche tecniche
- Implementazione del trait AIEloquentEnhancement per modelli Eloquent
- Service AI con supporto per multiple provider (OpenAI, Claude, Gemini)
- Cache intelligente per ottimizzare performance e costi
- Controller per testare le funzionalità AI
- Vista interattiva per dimostrare il comportamento
- Test PHPUnit completi per tutte le funzionalità

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Chiavi API AI** (OpenAI, Claude, o Gemini) per testare le funzionalità

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp -r /path/to/this/example/app/Services app/
cp -r /path/to/this/example/app/Traits app/
cp /path/to/this/example/app/Http/Controllers/AIEloquentController.php app/Http/Controllers/
cp /path/to/this/example/app/Models/Article.php app/Models/
mkdir -p resources/views/ai-eloquent
cp /path/to/this/example/resources/views/ai-eloquent/example.blade.php resources/views/ai-eloquent/
cp /path/to/this/example/routes/web.php routes/web.php
cp /path/to/this/example/config/ai.php config/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\AIEloquentController;

// Route per il pattern AI Eloquent Enhancement
Route::get('/ai-eloquent', [AIEloquentController::class, 'show']);
Route::get('/ai-eloquent/test', [AIEloquentController::class, 'test']);

// Route API
Route::prefix('api/ai-eloquent')->group(function () {
    Route::get('/', [AIEloquentController::class, 'index']);
    Route::post('/search', [AIEloquentController::class, 'search']);
    Route::post('/generate-tags', [AIEloquentController::class, 'generateTags']);
    Route::post('/translate', [AIEloquentController::class, 'translate']);
    Route::post('/related', [AIEloquentController::class, 'related']);
});
```

### 3. Configura le chiavi API

Aggiungi al tuo `.env`:

```env
# AI Configuration
OPENAI_API_KEY=your_openai_key_here
ANTHROPIC_API_KEY=your_claude_key_here
GOOGLE_AI_API_KEY=your_gemini_key_here
AI_DEFAULT_PROVIDER=openai
AI_CACHE_TTL=3600
```

### 4. Esegui le migrazioni

```bash
# Crea la tabella articles
php artisan make:migration create_articles_table
# Copia il contenuto della migration dall'esempio

# Esegui le migrazioni
php artisan migrate
```

### 5. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/ai-eloquent

# Testa via API
curl -X POST http://localhost:8000/api/ai-eloquent/search \
  -H "Content-Type: application/json" \
  -d '{"query": "ricetta pane"}'
```

### 6. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/ai-eloquent` e clicca sui pulsanti di test
2. **API**: Esegui le chiamate API per testare le funzionalità
3. **Test**: Esegui `php artisan test` per verificare i test

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Services/AI/AIService.php` - Service per gestire le chiamate AI
- `app/Services/AI/Providers/` - Provider per diversi servizi AI
- `app/Traits/AIEloquentEnhancement.php` - Trait per aggiungere funzionalità AI ai modelli
- `app/Http/Controllers/AIEloquentController.php` - Controller per testare il pattern
- `app/Models/Article.php` - Modello Article con funzionalità AI
- `resources/views/ai-eloquent/example.blade.php` - Vista interattiva per il browser
- `routes/web.php` - Route da integrare nel tuo progetto
- `config/ai.php` - Configurazione per i servizi AI
- `database/migrations/create_articles_table.php` - Migration per la tabella articles
- `tests/Feature/AIEloquentTest.php` - Test PHPUnit completi

## Personalizzazione

### Configurazione
L'AI Eloquent Enhancement richiede configurazione delle chiavi API AI nel file `.env` e nel file `config/ai.php`.

### Estensione
Aggiungi nuovi metodi nel trait `AIEloquentEnhancement` per estendere le funzionalità AI dei tuoi modelli.

## Note importanti
- Le chiavi API AI sono necessarie per testare le funzionalità
- Il sistema include cache intelligente per ottimizzare costi e performance
- È implementato fallback automatico quando i servizi AI non sono disponibili
- I file sono pronti per essere copiati in un progetto Laravel esistente
