# AI Fallback Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern AI Fallback in Laravel attraverso un sistema di fallback automatico per provider AI. L'esempio include:

- **Fallback automatico** tra provider AI
- **Circuit breaker** per gestire i provider non disponibili
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare il fallback
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
L'AI Fallback creato gestisce:
- **Fallback automatico** quando un provider fallisce
- **Circuit breaker** per evitare chiamate a provider down
- **Retry logic** per tentare di recuperare
- **Monitoring** dello stato dei provider

Quando testi l'esempio, vedrai che:
1. Il sistema gestisce automaticamente i fallimenti
2. I provider vengono temporaneamente disabilitati se falliscono
3. Il sistema riprova automaticamente dopo un periodo di cooldown
4. Le metriche ti mostrano lo stato di ogni provider

## Caratteristiche tecniche
- Fallback automatico tra provider AI
- Circuit breaker per gestire provider down
- Retry logic con backoff esponenziale
- Monitoring dello stato dei provider
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare il fallback
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
cp /path/to/this/example/app/Models/AIProvider.php app/Models/
cp /path/to/this/example/app/Http/Controllers/AIFallbackController.php app/Http/Controllers/
mkdir -p resources/views/ai-fallback
cp /path/to/this/example/resources/views/ai-fallback/example.blade.php resources/views/ai-fallback/
cp /path/to/this/example/tests/Feature/AIFallbackTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\AIFallbackController;

// Route per il pattern AI Fallback
Route::get('/ai-fallback', [AIFallbackController::class, 'show']);
Route::get('/ai-fallback/test', [AIFallbackController::class, 'test']);
Route::post('/ai-fallback/query', [AIFallbackController::class, 'query']);

// Route API
Route::prefix('api/ai-fallback')->group(function () {
    Route::get('/', [AIFallbackController::class, 'index']);
    Route::post('/query', [AIFallbackController::class, 'query']);
    Route::get('/test', [AIFallbackController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/ai-fallback

# Testa via API
curl http://localhost:8000/api/ai-fallback/test

# Esegui i test
php artisan test tests/Feature/AIFallbackTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/ai-fallback` e testa il fallback
2. **API**: Esegui `curl http://localhost:8000/api/ai-fallback/test`
3. **Test**: Esegui `php artisan test tests/Feature/AIFallbackTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/AIProvider.php` - Modello per i provider AI
- `app/Http/Controllers/AIFallbackController.php` - Controller per testare il pattern
- `resources/views/ai-fallback/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/AIFallbackTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica il fallback in `app/Models/AIProvider.php` per personalizzare la gestione dei provider.

### Estensione
Aggiungi nuovi provider implementando l'interfaccia appropriata.

## Note importanti
- L'AI Fallback gestisce automaticamente i fallimenti dei provider
- Include circuit breaker per evitare chiamate a provider down
- Gestisce retry logic con backoff esponenziale
- I file sono pronti per essere copiati in un progetto Laravel esistente
