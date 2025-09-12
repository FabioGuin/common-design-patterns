# AI Blade Templates - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra l'AI Blade Templates in Laravel attraverso un sistema e-commerce con template intelligenti. L'esempio include:

- **Direttive Blade personalizzate** per funzionalità AI integrate nei template
- **Template prodotti intelligenti** che generano contenuti personalizzati automaticamente
- **Sistema di traduzione automatica** integrato nei template Blade
- **Personalizzazione basata su preferenze utente** con layout adattivi

## Come funziona l'esempio
L'AI Blade Templates creato gestisce:
- **Direttive AI personalizzate** (@ai.content, @ai.translate, @ai.personalize, @ai.seo)
- **Service AI per template** che gestisce la generazione di contenuti
- **Cache intelligente** per ottimizzare performance e costi
- **Fallback automatico** quando i servizi AI non sono disponibili

Quando testi l'esempio, vedrai che:
1. I template si adattano automaticamente al contenuto e al contesto
2. Le sezioni vengono tradotte automaticamente in tempo reale
3. I contenuti vengono personalizzati per ogni utente
4. I meta tag SEO vengono ottimizzati automaticamente

## Caratteristiche tecniche
- Implementazione di direttive Blade personalizzate per AI
- Service AI specializzato per template e contenuti
- Sistema di cache intelligente per contenuti generati
- Controller per testare le funzionalità AI nei template
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
cp -r /path/to/this/example/app/Providers app/
cp -r /path/to/this/example/app/View app/
cp /path/to/this/example/app/Http/Controllers/AIBladeController.php app/Http/Controllers/
cp /path/to/this/example/app/Models/Product.php app/Models/
mkdir -p resources/views/ai-blade
cp -r /path/to/this/example/resources/views/ai-blade/* resources/views/ai-blade/
cp /path/to/this/example/routes/web.php routes/web.php
cp /path/to/this/example/config/ai.php config/
```

### 2. Registra il Service Provider

Aggiungi al tuo `config/app.php` nella sezione `providers`:

```php
App\Providers\AIBladeServiceProvider::class,
```

### 3. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\AIBladeController;

// Route per il pattern AI Blade Templates
Route::get('/ai-blade', [AIBladeController::class, 'show']);
Route::get('/ai-blade/test', [AIBladeController::class, 'test']);

// Route API
Route::prefix('api/ai-blade')->group(function () {
    Route::get('/', [AIBladeController::class, 'show']);
    Route::post('/render', [AIBladeController::class, 'render']);
    Route::post('/translate', [AIBladeController::class, 'translate']);
    Route::post('/personalize', [AIBladeController::class, 'personalize']);
    Route::post('/test', [AIBladeController::class, 'test']);
});
```

### 4. Configura le chiavi API

Aggiungi al tuo `.env`:

```env
# AI Configuration
OPENAI_API_KEY=your_openai_key_here
ANTHROPIC_API_KEY=your_claude_key_here
GOOGLE_AI_API_KEY=your_gemini_key_here
AI_DEFAULT_PROVIDER=openai
AI_CACHE_TTL=3600
```

### 5. Esegui le migrazioni

```bash
# Crea la tabella products
php artisan make:migration create_products_table
# Copia il contenuto della migration dall'esempio

# Esegui le migrazioni
php artisan migrate
```

### 6. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/ai-blade

# Testa via API
curl -X POST http://localhost:8000/api/ai-blade/render \
  -H "Content-Type: application/json" \
  -d '{"template": "product", "data": {"product_id": 1}}'
```

### 7. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/ai-blade` e clicca sui pulsanti di test
2. **API**: Esegui le chiamate API per testare le funzionalità
3. **Test**: Esegui `php artisan test` per verificare i test

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Providers/AIBladeServiceProvider.php` - Service Provider per registrare le direttive AI
- `app/Services/AI/AITemplateService.php` - Service per gestire template AI
- `app/View/Components/AIComponent.php` - Componente Blade per funzionalità AI
- `app/Http/Controllers/AIBladeController.php` - Controller per testare il pattern
- `app/Models/Product.php` - Modello Product con funzionalità AI
- `resources/views/ai-blade/` - Template Blade con direttive AI
- `routes/web.php` - Route da integrare nel tuo progetto
- `config/ai.php` - Configurazione per i servizi AI
- `database/migrations/create_products_table.php` - Migration per la tabella products
- `tests/Feature/AIBladeTest.php` - Test PHPUnit completi

## Personalizzazione

### Configurazione
L'AI Blade Templates richiede configurazione delle chiavi API AI nel file `.env` e nel file `config/ai.php`.

### Estensione
Aggiungi nuove direttive AI nel `AIBladeServiceProvider` per estendere le funzionalità dei template.

## Note importanti
- Le chiavi API AI sono necessarie per testare le funzionalità
- Il sistema include cache intelligente per ottimizzare costi e performance
- È implementato fallback automatico quando i servizi AI non sono disponibili
- I file sono pronti per essere copiati in un progetto Laravel esistente
