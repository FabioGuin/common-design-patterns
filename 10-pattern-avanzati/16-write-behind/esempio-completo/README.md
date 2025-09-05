# Write-Behind Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Write-Behind in Laravel attraverso un sistema di logging con scrittura asincrona. L'esempio include:

- **WriteBehindService** che gestisce la scrittura immediata in cache e asincrona in database
- **LogController** che utilizza il pattern per le operazioni di logging
- **LogEntry Model** con implementazione del pattern per la persistenza asincrona
- **Background Jobs** che processano le scritture in batch per efficienza

## Come funziona l'esempio
Il pattern Write-Behind gestisce:
- **Scrittura immediata** in cache per risposta veloce
- **Coda asincrona** per le scritture nel database
- **Batch processing** per ottimizzare le performance
- **Gestione errori** con retry automatico per le scritture fallite

Quando testi l'esempio, vedrai che:
1. Ogni log viene scritto immediatamente in cache
2. Le scritture nel database avvengono in background
3. I batch vengono processati automaticamente
4. Le performance rimangono elevate anche con molte scritture

## Caratteristiche tecniche
- Integrazione con Redis per la cache
- Sistema di code Laravel per operazioni asincrone
- Batch processing per efficienza
- Retry logic per gestire i fallimenti
- Interfaccia web per monitorare le operazioni

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Redis** installato e configurato
- **Queue worker** attivo per processare i job

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Models/LogEntry.php app/Models/
cp /path/to/this/example/app/Services/WriteBehindService.php app/Services/
cp /path/to/this/example/app/Jobs/ProcessLogBatch.php app/Jobs/
cp /path/to/this/example/app/Http/Controllers/LogController.php app/Http/Controllers/
mkdir -p resources/views/write-behind
cp /path/to/this/example/resources/views/write-behind/example.blade.php resources/views/write-behind/
cp /path/to/this/example/tests/Feature/WriteBehindTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\LogController;

// Route per il pattern Write-Behind
Route::get('/write-behind', [LogController::class, 'index']);
Route::get('/write-behind/test', [LogController::class, 'test']);

// Route API
Route::prefix('api/write-behind')->group(function () {
    Route::get('/', [LogController::class, 'index']);
    Route::post('/test', [LogController::class, 'test']);
    Route::post('/logs', [LogController::class, 'store']);
    Route::get('/logs', [LogController::class, 'list']);
    Route::get('/stats', [LogController::class, 'stats']);
});
```

### 3. Configura Redis e Queue

Assicurati che Redis e le queue siano configurate nel tuo `.env`:

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Esegui le migrazioni

```bash
# Crea la tabella log_entries
php artisan make:migration create_log_entries_table
```

Copia il contenuto della migrazione da `database/migrations/create_log_entries_table.php` nel tuo progetto.

### 5. Avvia il queue worker

```bash
# Avvia il worker per processare i job
php artisan queue:work --tries=3 --timeout=60
```

### 6. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/write-behind

# Testa via API
curl http://localhost:8000/api/write-behind/test

# Esegui i test
php artisan test tests/Feature/WriteBehindTest.php
```

### 7. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/write-behind` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/write-behind/test`
3. **Test**: Esegui `php artisan test tests/Feature/WriteBehindTest.php`
4. **Queue**: Verifica che i job vengano processati dal worker

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern
php test-standalone.php

# Test rapido inline
php -r "require_once 'app/Services/WriteBehindService.php'; use App\Services\WriteBehindService; \$s = new WriteBehindService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Models/LogEntry.php` - Model con implementazione del pattern
- `app/Services/WriteBehindService.php` - Service che gestisce il pattern
- `app/Jobs/ProcessLogBatch.php` - Job per processare i batch
- `app/Http/Controllers/LogController.php` - Controller per testare il pattern
- `resources/views/write-behind/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/WriteBehindTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto
- `test-standalone.php` - Test standalone per verificare il pattern senza Laravel

## Personalizzazione

### Configurazione
- Modifica la dimensione del batch nel service
- Personalizza i timeout e i retry
- Aggiungi logging per il debugging

### Estensione
- Implementa metriche di performance
- Aggiungi monitoring per la coda
- Integra con sistemi di alerting

## Note importanti
- Il pattern garantisce performance elevate per le scritture
- Le scritture nel database avvengono in background
- È necessario un queue worker attivo
- I file sono pronti per essere copiati in un progetto Laravel esistente
