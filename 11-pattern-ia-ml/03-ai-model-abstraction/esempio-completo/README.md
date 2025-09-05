# AI Model Abstraction Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern AI Model Abstraction in Laravel attraverso un sistema di astrazione per gestire diversi modelli AI. L'esempio include:

- **Astrazione unificata** per diversi modelli AI
- **Selezione automatica** del modello migliore
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare i modelli
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
L'AI Model Abstraction creato gestisce:
- **Astrazione** delle differenze tra modelli AI
- **Selezione intelligente** del modello ottimale
- **Fallback automatico** tra modelli
- **Metriche di performance** per ottimizzazione

Quando testi l'esempio, vedrai che:
1. Puoi usare diversi modelli AI senza cambiare codice
2. Il sistema seleziona automaticamente il modello migliore
3. Le performance vengono monitorate e ottimizzate
4. È facile aggiungere nuovi modelli

## Caratteristiche tecniche
- Interfaccia comune per tutti i modelli AI
- Selezione automatica del modello ottimale
- Sistema di fallback intelligente
- Metriche di performance in tempo reale
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare l'abstraction
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
cp /path/to/this/example/app/Models/AIModel.php app/Models/
cp /path/to/this/example/app/Http/Controllers/AIModelController.php app/Http/Controllers/
mkdir -p resources/views/ai-model-abstraction
cp /path/to/this/example/resources/views/ai-model-abstraction/example.blade.php resources/views/ai-model-abstraction/
cp /path/to/this/example/tests/Feature/AIModelAbstractionTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\AIModelController;

// Route per il pattern AI Model Abstraction
Route::get('/ai-model-abstraction', [AIModelController::class, 'show']);
Route::get('/ai-model-abstraction/test', [AIModelController::class, 'test']);
Route::post('/ai-model-abstraction/predict', [AIModelController::class, 'predict']);

// Route API
Route::prefix('api/ai-model-abstraction')->group(function () {
    Route::get('/', [AIModelController::class, 'index']);
    Route::post('/predict', [AIModelController::class, 'predict']);
    Route::get('/test', [AIModelController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/ai-model-abstraction

# Testa via API
curl http://localhost:8000/api/ai-model-abstraction/test

# Esegui i test
php artisan test tests/Feature/AIModelAbstractionTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/ai-model-abstraction` e testa i modelli
2. **API**: Esegui `curl http://localhost:8000/api/ai-model-abstraction/test`
3. **Test**: Esegui `php artisan test tests/Feature/AIModelAbstractionTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/AIModel.php` - Modello per i modelli AI
- `app/Http/Controllers/AIModelController.php` - Controller per testare il pattern
- `resources/views/ai-model-abstraction/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/AIModelAbstractionTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica i modelli in `app/Models/AIModel.php` per personalizzare l'astrazione.

### Estensione
Aggiungi nuovi modelli implementando l'interfaccia appropriata.

## Note importanti
- L'AI Model Abstraction astrae le differenze tra modelli AI
- Gestisce automaticamente la selezione del modello ottimale
- Include metriche di performance per ottimizzazione
- I file sono pronti per essere copiati in un progetto Laravel esistente
