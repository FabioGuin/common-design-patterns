# AI Rate Limiting Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern AI Rate Limiting in Laravel attraverso un sistema di rate limiting per controllare l'uso delle API AI. L'esempio include:

- **Rate limiting** per controllare l'uso delle API AI
- **Quota management** per gestire i limiti per utente
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare il rate limiting
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
L'AI Rate Limiting creato gestisce:
- **Rate limiting** per controllare le chiamate API
- **Quota management** per gestire i limiti per utente
- **Throttling** per prevenire l'abuso delle API
- **Monitoring** dell'uso delle API

Quando testi l'esempio, vedrai che:
1. Il sistema controlla il numero di chiamate per utente
2. Le quote vengono gestite automaticamente
3. Il throttling previene l'abuso delle API
4. Le metriche ti mostrano l'uso delle API

## Caratteristiche tecniche
- Rate limiting per controllare le chiamate API
- Quota management per gestire i limiti per utente
- Throttling per prevenire l'abuso delle API
- Monitoring dell'uso delle API
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare il rate limiting
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
cp /path/to/this/example/app/Models/RateLimit.php app/Models/
cp /path/to/this/example/app/Http/Controllers/AIRateLimitController.php app/Http/Controllers/
mkdir -p resources/views/ai-rate-limit
cp /path/to/this/example/resources/views/ai-rate-limit/example.blade.php resources/views/ai-rate-limit/
cp /path/to/this/example/tests/Feature/AIRateLimitTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\AIRateLimitController;

// Route per il pattern AI Rate Limiting
Route::get('/ai-rate-limit', [AIRateLimitController::class, 'show']);
Route::get('/ai-rate-limit/test', [AIRateLimitController::class, 'test']);
Route::post('/ai-rate-limit/query', [AIRateLimitController::class, 'query']);

// Route API
Route::prefix('api/ai-rate-limit')->group(function () {
    Route::get('/', [AIRateLimitController::class, 'index']);
    Route::post('/query', [AIRateLimitController::class, 'query']);
    Route::get('/test', [AIRateLimitController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/ai-rate-limit

# Testa via API
curl http://localhost:8000/api/ai-rate-limit/test

# Esegui i test
php artisan test tests/Feature/AIRateLimitTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/ai-rate-limit` e testa il rate limiting
2. **API**: Esegui `curl http://localhost:8000/api/ai-rate-limit/test`
3. **Test**: Esegui `php artisan test tests/Feature/AIRateLimitTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/RateLimit.php` - Modello per il rate limiting
- `app/Http/Controllers/AIRateLimitController.php` - Controller per testare il pattern
- `resources/views/ai-rate-limit/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/AIRateLimitTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica il rate limiting in `app/Models/RateLimit.php` per personalizzare i limiti.

### Estensione
Aggiungi nuove strategie di rate limiting implementando l'interfaccia appropriata.

## Note importanti
- L'AI Rate Limiting controlla l'uso delle API AI
- Gestisce automaticamente le quote per utente
- Previene l'abuso delle API con throttling
- I file sono pronti per essere copiati in un progetto Laravel esistente
