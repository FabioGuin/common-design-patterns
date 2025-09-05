# AI Response Caching Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern AI Response Caching in Laravel attraverso un sistema di caching per ottimizzare le risposte AI. L'esempio include:

- **Caching intelligente** delle risposte AI
- **Invalidazione automatica** del cache
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare il caching
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
L'AI Response Caching creato gestisce:
- **Caching** delle risposte AI per performance migliori
- **Invalidazione** automatica quando necessario
- **Metriche** di hit/miss del cache
- **Ottimizzazione** dei costi API

Quando testi l'esempio, vedrai che:
1. Le risposte AI vengono cachate per richieste simili
2. Il sistema gestisce automaticamente l'invalidazione
3. Le metriche ti mostrano l'efficacia del caching
4. I costi delle API vengono ridotti significativamente

## Caratteristiche tecniche
- Caching intelligente delle risposte AI
- Invalidazione automatica del cache
- Metriche di hit/miss in tempo reale
- Ottimizzazione dei costi API
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare il caching
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
cp /path/to/this/example/app/Models/AICacheEntry.php app/Models/
cp /path/to/this/example/app/Http/Controllers/AICacheController.php app/Http/Controllers/
mkdir -p resources/views/ai-response-caching
cp /path/to/this/example/resources/views/ai-response-caching/example.blade.php resources/views/ai-response-caching/
cp /path/to/this/example/tests/Feature/AIResponseCachingTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\AICacheController;

// Route per il pattern AI Response Caching
Route::get('/ai-response-caching', [AICacheController::class, 'show']);
Route::get('/ai-response-caching/test', [AICacheController::class, 'test']);
Route::post('/ai-response-caching/query', [AICacheController::class, 'query']);

// Route API
Route::prefix('api/ai-response-caching')->group(function () {
    Route::get('/', [AICacheController::class, 'index']);
    Route::post('/query', [AICacheController::class, 'query']);
    Route::get('/test', [AICacheController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/ai-response-caching

# Testa via API
curl http://localhost:8000/api/ai-response-caching/test

# Esegui i test
php artisan test tests/Feature/AIResponseCachingTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/ai-response-caching` e testa il caching
2. **API**: Esegui `curl http://localhost:8000/api/ai-response-caching/test`
3. **Test**: Esegui `php artisan test tests/Feature/AIResponseCachingTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/AICacheEntry.php` - Modello per le entry del cache
- `app/Http/Controllers/AICacheController.php` - Controller per testare il pattern
- `resources/views/ai-response-caching/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/AIResponseCachingTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica il caching in `app/Models/AICacheEntry.php` per personalizzare la gestione del cache.

### Estensione
Aggiungi nuove strategie di caching implementando l'interfaccia appropriata.

## Note importanti
- L'AI Response Caching ottimizza le performance e riduce i costi
- Gestisce automaticamente l'invalidazione del cache
- Include metriche per monitorare l'efficacia
- I file sono pronti per essere copiati in un progetto Laravel esistente
