# Materialized View Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Materialized View in Laravel attraverso un sistema di dashboard vendite con report pre-calcolati. L'esempio include:

- **MaterializedViewService** che gestisce la creazione e aggiornamento delle viste
- **SalesController** che utilizza le viste per servire report veloci
- **Models** per ordini, prodotti e categorie con relazioni
- **Scheduled Jobs** per aggiornare automaticamente le viste materializzate

## Come funziona l'esempio
Il pattern Materialized View gestisce:
- **Pre-calcolo** di aggregazioni complesse in tabelle dedicate
- **Aggiornamento periodico** delle viste per mantenere i dati aggiornati
- **Query veloci** sui dati pre-calcolati per report istantanei
- **Sincronizzazione** automatica con i dati sorgente

Quando testi l'esempio, vedrai che:
1. Le viste vengono create e popolate con dati aggregati
2. I report vengono serviti istantaneamente dalle viste
3. Le viste vengono aggiornate automaticamente quando i dati cambiano
4. Le performance sono significativamente migliori rispetto alle query complesse

## Caratteristiche tecniche
- Gestione viste materializzate con Laravel
- Job schedulati per aggiornamento automatico
- Dashboard web per visualizzare i report
- Sistema di cache per ottimizzare ulteriormente le performance
- Interfaccia per monitorare lo stato delle viste

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Database** (MySQL/PostgreSQL) configurato
- **Scheduler** attivo per i job programmati

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Models/*.php app/Models/
cp /path/to/this/example/app/Services/MaterializedViewService.php app/Services/
cp /path/to/this/example/app/Jobs/RefreshMaterializedViews.php app/Jobs/
cp /path/to/this/example/app/Http/Controllers/SalesController.php app/Http/Controllers/
mkdir -p resources/views/materialized-view
cp /path/to/this/example/resources/views/materialized-view/example.blade.php resources/views/materialized-view/
cp /path/to/this/example/tests/Feature/MaterializedViewTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\SalesController;

// Route per il pattern Materialized View
Route::get('/materialized-view', [SalesController::class, 'index']);
Route::get('/materialized-view/test', [SalesController::class, 'test']);

// Route API
Route::prefix('api/materialized-view')->group(function () {
    Route::get('/', [SalesController::class, 'index']);
    Route::post('/test', [SalesController::class, 'test']);
    Route::get('/reports/sales-by-category', [SalesController::class, 'salesByCategory']);
    Route::get('/reports/sales-by-month', [SalesController::class, 'salesByMonth']);
    Route::get('/reports/top-products', [SalesController::class, 'topProducts']);
    Route::post('/refresh', [SalesController::class, 'refreshViews']);
    Route::get('/status', [SalesController::class, 'viewStatus']);
});
```

### 3. Esegui le migrazioni

```bash
# Crea le tabelle necessarie
php artisan make:migration create_products_table
php artisan make:migration create_categories_table
php artisan make:migration create_orders_table
php artisan make:migration create_order_items_table
php artisan make:migration create_materialized_views_table
```

Copia il contenuto delle migrazioni da `database/migrations/` nel tuo progetto.

### 4. Configura il scheduler

Aggiungi al tuo `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Aggiorna le viste materializzate ogni ora
    $schedule->job(new \App\Jobs\RefreshMaterializedViews())->hourly();
}
```

### 5. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/materialized-view

# Testa via API
curl http://localhost:8000/api/materialized-view/test

# Esegui i test
php artisan test tests/Feature/MaterializedViewTest.php
```

### 6. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/materialized-view` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/materialized-view/test`
3. **Test**: Esegui `php artisan test tests/Feature/MaterializedViewTest.php`
4. **Scheduler**: Verifica che i job vengano eseguiti: `php artisan schedule:run`

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern
php test-standalone.php

# Test rapido inline
php -r "require_once 'app/Services/MaterializedViewService.php'; use App\Services\MaterializedViewService; \$s = new MaterializedViewService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Models/Product.php` - Model per i prodotti
- `app/Models/Category.php` - Model per le categorie
- `app/Models/Order.php` - Model per gli ordini
- `app/Models/OrderItem.php` - Model per gli elementi degli ordini
- `app/Services/MaterializedViewService.php` - Service che gestisce il pattern
- `app/Jobs/RefreshMaterializedViews.php` - Job per aggiornare le viste
- `app/Http/Controllers/SalesController.php` - Controller per testare il pattern
- `resources/views/materialized-view/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/MaterializedViewTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto
- `test-standalone.php` - Test standalone per verificare il pattern senza Laravel

## Personalizzazione

### Configurazione
- Modifica la frequenza di aggiornamento delle viste
- Personalizza le query di aggregazione
- Aggiungi nuove viste materializzate

### Estensione
- Implementa viste incrementali per dati in tempo reale
- Aggiungi metriche di performance
- Integra con sistemi di monitoring

## Note importanti
- Il pattern migliora significativamente le performance per query complesse
- Le viste devono essere aggiornate regolarmente per mantenere i dati aggiornati
- È necessario spazio di storage aggiuntivo per le viste
- I file sono pronti per essere copiati in un progetto Laravel esistente
