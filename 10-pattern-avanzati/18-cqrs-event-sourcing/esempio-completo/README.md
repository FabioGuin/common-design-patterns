# CQRS + Event Sourcing Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern CQRS + Event Sourcing in Laravel attraverso un sistema di gestione ordini con audit completo. L'esempio include:

- **Commands** per gestire le operazioni di scrittura (creare, modificare, cancellare ordini)
- **Events** per tracciare ogni modifica come evento immutabile
- **Event Store** per memorizzare tutti gli eventi
- **Projections** per le query veloci sui dati
- **Sistema di Audit** per tracciare ogni operazione

## Come funziona l'esempio
Il pattern CQRS + Event Sourcing gestisce:
- **Separazione Command/Query**: Operazioni di scrittura separate dalle letture
- **Event Sourcing**: Ogni modifica diventa un evento immutabile
- **Event Store**: Database dedicato per memorizzare gli eventi
- **Projections**: Viste materializzate per query veloci
- **Audit Trail**: Tracciamento completo di ogni operazione

Quando testi l'esempio, vedrai che:
1. Ogni operazione crea un evento immutabile
2. Gli eventi vengono memorizzati nell'Event Store
3. Le projection vengono aggiornate automaticamente
4. Puoi ricostruire lo stato da qualsiasi punto nel tempo
5. L'audit trail è completo e tracciabile

## Caratteristiche tecniche
- Implementazione CQRS con Commands e Queries separati
- Event Sourcing con eventi immutabili
- Event Store per memorizzare gli eventi
- Projections per query veloci
- Sistema di audit e time travel
- Interfaccia web per testare le funzionalità

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Database** (MySQL/PostgreSQL) configurato
- **Queue worker** attivo per processing asincrono

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Commands/*.php app/Commands/
cp /path/to/this/example/app/Events/*.php app/Events/
cp /path/to/this/example/app/Projections/*.php app/Projections/
cp /path/to/this/example/app/Services/*.php app/Services/
cp /path/to/this/example/app/Http/Controllers/*.php app/Http/Controllers/
mkdir -p resources/views/cqrs-event-sourcing
cp /path/to/this/example/resources/views/cqrs-event-sourcing/example.blade.php resources/views/cqrs-event-sourcing/
cp /path/to/this/example/tests/Feature/*.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\OrderController;

// Route per il pattern CQRS + Event Sourcing
Route::get('/cqrs-event-sourcing', [OrderController::class, 'index']);
Route::get('/cqrs-event-sourcing/test', [OrderController::class, 'test']);

// Route API
Route::prefix('api/cqrs-event-sourcing')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/test', [OrderController::class, 'test']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::put('/orders/{id}', [OrderController::class, 'updateOrder']);
    Route::delete('/orders/{id}', [OrderController::class, 'cancelOrder']);
    Route::get('/orders/{id}', [OrderController::class, 'getOrder']);
    Route::get('/orders', [OrderController::class, 'listOrders']);
    Route::get('/events/{id}', [OrderController::class, 'getOrderEvents']);
    Route::get('/audit/{id}', [OrderController::class, 'getAuditTrail']);
    Route::post('/replay/{id}', [OrderController::class, 'replayEvents']);
});
```

### 3. Esegui le migrazioni

```bash
# Crea le tabelle necessarie
php artisan make:migration create_events_table
php artisan make:migration create_order_projections_table
php artisan make:migration create_audit_logs_table
```

Copia il contenuto delle migrazioni da `database/migrations/` nel tuo progetto.

### 4. Configura gli eventi

Aggiungi al tuo `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    // ... altri eventi
    \App\Events\OrderCreated::class => [
        \App\Projections\OrderProjection::class,
    ],
    \App\Events\OrderUpdated::class => [
        \App\Projections\OrderProjection::class,
    ],
    \App\Events\OrderCancelled::class => [
        \App\Projections\OrderProjection::class,
    ],
];
```

### 5. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/cqrs-event-sourcing

# Testa via API
curl http://localhost:8000/api/cqrs-event-sourcing/test

# Esegui i test
php artisan test tests/Feature/CqrsEventSourcingTest.php
```

### 6. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/cqrs-event-sourcing` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/cqrs-event-sourcing/test`
3. **Test**: Esegui `php artisan test tests/Feature/CqrsEventSourcingTest.php`
4. **Eventi**: Verifica che gli eventi vengano processati correttamente

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern

# Test rapido inline
php -r "require_once 'app/Services/EventStoreService.php'; use App\Services\EventStoreService; \$s = new EventStoreService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Commands/CreateOrderCommand.php` - Command per creare ordini
- `app/Commands/UpdateOrderCommand.php` - Command per aggiornare ordini
- `app/Commands/CancelOrderCommand.php` - Command per cancellare ordini
- `app/Events/OrderCreated.php` - Evento per ordine creato
- `app/Events/OrderUpdated.php` - Evento per ordine aggiornato
- `app/Events/OrderCancelled.php` - Evento per ordine cancellato
- `app/Projections/OrderProjection.php` - Projection per query veloci
- `app/Services/EventStoreService.php` - Service per gestire l'Event Store
- `app/Http/Controllers/OrderController.php` - Controller per testare il pattern
- `resources/views/cqrs-event-sourcing/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/CqrsEventSourcingTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
- Modifica la struttura degli eventi
- Personalizza le projection per le tue esigenze
- Aggiungi nuovi command e eventi

### Estensione
- Implementa snapshot per performance migliori
- Aggiungi eventi di sistema
- Integra con sistemi di monitoring

## Note importanti
- Il pattern fornisce audit completo e tracciabilità totale
- Ogni modifica è immutabile e tracciabile
- Le projection devono essere mantenute sincronizzate
- È necessario spazio di storage per gli eventi
- I file sono pronti per essere copiati in un progetto Laravel esistente
