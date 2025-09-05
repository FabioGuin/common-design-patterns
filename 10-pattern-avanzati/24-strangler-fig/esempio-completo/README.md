# Strangler Fig Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Strangler Fig in Laravel attraverso un sistema di migrazione graduale da un e-commerce legacy a un nuovo sistema moderno. L'esempio include:

- **StranglerFigService** che gestisce il routing delle richieste tra sistema legacy e nuovo
- **LegacyController** che simula il sistema legacy esistente
- **ModernController** che implementa le nuove funzionalità
- **MigrationConfig** per gestire la configurazione della migrazione

## Come funziona l'esempio
Il pattern Strangler Fig gestisce:
- **Routing intelligente** delle richieste tra sistema legacy e nuovo
- **Migrazione graduale** delle funzionalità una per una
- **Fallback automatico** al sistema legacy in caso di problemi
- **Configurazione dinamica** per controllare la migrazione
- **Monitoraggio** dello stato della migrazione

Quando testi l'esempio, vedrai che:
1. Le richieste vengono indirizzate al sistema appropriato
2. Le funzionalità vengono migrate gradualmente
3. Il sistema legacy rimane funzionante durante la migrazione
4. È possibile fare rollback facilmente
5. La migrazione è completamente trasparente per l'utente

## Caratteristiche tecniche
- Proxy intelligente per routing delle richieste
- Sistema di configurazione per controllare la migrazione
- Fallback automatico al sistema legacy
- Dashboard per monitorare lo stato della migrazione
- API per gestire la migrazione
- Sistema di logging per tracciare le richieste

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Database** configurato per entrambi i sistemi
- **Cache** configurata per le performance

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Services/StranglerFigService.php app/Services/
cp /path/to/this/example/app/Http/Controllers/LegacyController.php app/Http/Controllers/
cp /path/to/this/example/app/Http/Controllers/ModernController.php app/Http/Controllers/
cp /path/to/this/example/app/Http/Controllers/StranglerFigController.php app/Http/Controllers/
mkdir -p resources/views/strangler-fig
cp /path/to/this/example/resources/views/strangler-fig/example.blade.php resources/views/strangler-fig/
cp /path/to/this/example/tests/Feature/StranglerFigTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\StranglerFigController;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\ModernController;

// Route per il pattern Strangler Fig
Route::get('/strangler-fig', [StranglerFigController::class, 'index']);
Route::get('/strangler-fig/test', [StranglerFigController::class, 'test']);

// Route API
Route::prefix('api/strangler-fig')->group(function () {
    Route::get('/', [StranglerFigController::class, 'index']);
    Route::post('/test', [StranglerFigController::class, 'test']);
    Route::get('/status', [StranglerFigController::class, 'status']);
    Route::post('/migrate-feature', [StranglerFigController::class, 'migrateFeature']);
    Route::post('/rollback-feature', [StranglerFigController::class, 'rollbackFeature']);
    Route::get('/features', [StranglerFigController::class, 'features']);
});

// Route legacy (simulate)
Route::prefix('legacy')->group(function () {
    Route::get('/users', [LegacyController::class, 'users']);
    Route::get('/products', [LegacyController::class, 'products']);
    Route::get('/orders', [LegacyController::class, 'orders']);
});

// Route modern (new system)
Route::prefix('modern')->group(function () {
    Route::get('/users', [ModernController::class, 'users']);
    Route::get('/products', [ModernController::class, 'products']);
    Route::get('/orders', [ModernController::class, 'orders']);
});
```

### 3. Configura i servizi

Aggiungi al tuo `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\StranglerFigServiceProvider::class,
],
```

### 4. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/strangler-fig

# Testa via API
curl http://localhost:8000/api/strangler-fig/test

# Esegui i test
php artisan test tests/Feature/StranglerFigTest.php
```

### 5. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/strangler-fig` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/strangler-fig/test`
3. **Test**: Esegui `php artisan test tests/Feature/StranglerFigTest.php`
4. **Migrazione**: Testa la migrazione delle funzionalità

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern
php test-standalone.php

# Test rapido inline
php -r "require_once 'app/Services/StranglerFigService.php'; use App\Services\StranglerFigService; \$s = new StranglerFigService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Services/StranglerFigService.php` - Service che gestisce il pattern
- `app/Http/Controllers/LegacyController.php` - Controller per sistema legacy
- `app/Http/Controllers/ModernController.php` - Controller per sistema moderno
- `app/Http/Controllers/StranglerFigController.php` - Controller per testare il pattern
- `resources/views/strangler-fig/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/StranglerFigTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto
- `test-standalone.php` - Test standalone per verificare il pattern senza Laravel

## Personalizzazione

### Configurazione
- Modifica la configurazione della migrazione
- Personalizza le funzionalità da migrare
- Aggiungi nuove funzionalità
- Configura i fallback

### Estensione
- Implementa metriche di migrazione
- Aggiungi monitoring per la migrazione
- Integra con sistemi di alerting
- Implementa A/B testing

## Note importanti
- Il pattern permette migrazione graduale senza downtime
- Il sistema legacy rimane funzionante durante la migrazione
- È possibile fare rollback facilmente
- I file sono pronti per essere copiati in un progetto Laravel esistente
