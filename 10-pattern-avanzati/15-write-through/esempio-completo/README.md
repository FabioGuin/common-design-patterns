# Write-Through Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Write-Through in Laravel attraverso un sistema di gestione prodotti con cache Redis. L'esempio include:

- **WriteThroughService** che gestisce la scrittura simultanea in cache e database
- **ProductController** che utilizza il pattern per le operazioni CRUD
- **Product Model** con implementazione del pattern per la persistenza
- **Test completi** che verificano la coerenza tra cache e database

## Come funziona l'esempio
Il pattern Write-Through gestisce:
- **Scrittura simultanea** per mantenere cache e database sincronizzati
- **Fallback intelligente** che invalida la cache se il database fallisce
- **Letture veloci** sempre servite dalla cache aggiornata
- **Gestione errori** per mantenere la coerenza dei dati

Quando testi l'esempio, vedrai che:
1. Ogni scrittura aggiorna sia Redis che il database
2. Le letture successive sono sempre veloci dalla cache
3. Se il database fallisce, la cache viene invalidata
4. La coerenza tra i due sistemi è sempre mantenuta

## Caratteristiche tecniche
- Integrazione con Redis per la cache
- Gestione transazionale delle scritture
- Fallback automatico in caso di errori
- Interfaccia web per testare le operazioni

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Redis** installato e configurato

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Models/Product.php app/Models/
cp /path/to/this/example/app/Services/WriteThroughService.php app/Services/
cp /path/to/this/example/app/Http/Controllers/ProductController.php app/Http/Controllers/
mkdir -p resources/views/write-through
cp /path/to/this/example/resources/views/write-through/example.blade.php resources/views/write-through/
cp /path/to/this/example/tests/Feature/WriteThroughTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\ProductController;

// Route per il pattern Write-Through
Route::get('/write-through', [ProductController::class, 'index']);
Route::get('/write-through/test', [ProductController::class, 'test']);

// Route API
Route::prefix('api/write-through')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/test', [ProductController::class, 'test']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
});
```

### 3. Configura Redis

Assicurati che Redis sia configurato nel tuo `.env`:

```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Esegui le migrazioni

```bash
# Crea la tabella products
php artisan make:migration create_products_table
```

Copia il contenuto della migrazione da `database/migrations/create_products_table.php` nel tuo progetto.

### 5. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/write-through

# Testa via API
curl http://localhost:8000/api/write-through/test

# Esegui i test
php artisan test tests/Feature/WriteThroughTest.php
```

### 6. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/write-through` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/write-through/test`
3. **Test**: Esegui `php artisan test tests/Feature/WriteThroughTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern

# Test rapido inline
php -r "require_once 'app/Services/WriteThroughService.php'; use App\Services\WriteThroughService; \$s = new WriteThroughService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Models/Product.php` - Model con implementazione del pattern
- `app/Services/WriteThroughService.php` - Service che gestisce il pattern
- `app/Http/Controllers/ProductController.php` - Controller per testare il pattern
- `resources/views/write-through/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/WriteThroughTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
- Modifica i timeout Redis nel service
- Personalizza le chiavi di cache
- Aggiungi logging per il debugging

### Estensione
- Implementa cache distribuita
- Aggiungi metriche di performance
- Integra con sistemi di monitoring

## Note importanti
- Il pattern garantisce coerenza tra cache e database
- Le scritture sono più lente ma le letture sono veloci
- Redis deve essere configurato correttamente
- I file sono pronti per essere copiati in un progetto Laravel esistente
