# Domain Event Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Domain Event in Laravel attraverso un sistema e-commerce con eventi di dominio per notificare cambiamenti. L'esempio include:

- **Eventi di dominio** per ordini, pagamenti e spedizioni
- **Event Bus** per gestire la pubblicazione e sottoscrizione
- **Listener** per notifiche, inventario e fatturazione
- **Disaccoppiamento** tra servizi attraverso eventi
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il pattern Domain Event gestisce:
- **Disaccoppiamento** tra servizi attraverso eventi
- **Notifiche automatiche** quando succede qualcosa di importante
- **Estensibilità** per aggiungere nuovi servizi senza modificare il codice esistente
- **Testabilità** per testare ogni servizio indipendentemente

Quando testi l'esempio, vedrai che:
1. Gli eventi vengono emessi quando succede qualcosa di importante
2. I listener si iscrivono agli eventi che li interessano
3. I servizi sono disaccoppiati e non si conoscono tra loro
4. È facile aggiungere nuovi listener senza modificare il codice esistente

## Caratteristiche tecniche
- Implementazione completa del Domain Event Pattern
- Event Bus per gestire eventi e listener
- Listener per notifiche, inventario e fatturazione
- Test completi con Pest
- Interfaccia web per testare gli eventi

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Http/Controllers/DomainEventController.php app/Http/Controllers/
cp /path/to/this/example/app/Events/OrderConfirmed.php app/Events/
cp /path/to/this/example/app/Events/OrderCancelled.php app/Events/
cp /path/to/this/example/app/Events/OrderShipped.php app/Events/
cp /path/to/this/example/app/Events/PaymentProcessed.php app/Events/
cp /path/to/this/example/app/Events/PaymentFailed.php app/Events/
cp /path/to/this/example/app/Listeners/SendOrderConfirmationEmail.php app/Listeners/
cp /path/to/this/example/app/Listeners/UpdateInventory.php app/Listeners/
cp /path/to/this/example/app/Listeners/CreateInvoice.php app/Listeners/
cp /path/to/this/example/app/Listeners/SendOrderCancellationEmail.php app/Listeners/
cp /path/to/this/example/app/Listeners/RestoreInventory.php app/Listeners/
cp /path/to/this/example/app/Listeners/SendShippingNotification.php app/Listeners/
cp /path/to/this/example/app/Listeners/UpdateOrderStatus.php app/Listeners/
cp /path/to/this/example/app/Services/EventBus.php app/Services/
cp /path/to/this/example/app/Services/NotificationService.php app/Services/
cp /path/to/this/example/app/Services/InventoryService.php app/Services/
cp /path/to/this/example/app/Services/BillingService.php app/Services/
cp /path/to/this/example/app/Services/ShippingService.php app/Services/
mkdir -p resources/views/domain_event
cp /path/to/this/example/resources/views/domain_event/example.blade.php resources/views/domain_event/
cp /path/to/this/example/tests/Feature/DomainEventPatternTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\DomainEventController;

// Route per il pattern Domain Event
Route::get('/domain-event', [DomainEventController::class, 'index']);
Route::get('/domain-event/test', [DomainEventController::class, 'test']);

// Route API
Route::prefix('api/domain-event')->group(function () {
    Route::get('/', [DomainEventController::class, 'index']);
    Route::post('/test', [DomainEventController::class, 'test']);
    Route::post('/order/confirm', [DomainEventController::class, 'confirmOrder']);
    Route::post('/order/cancel', [DomainEventController::class, 'cancelOrder']);
    Route::post('/order/ship', [DomainEventController::class, 'shipOrder']);
    Route::post('/payment/process', [DomainEventController::class, 'processPayment']);
    Route::post('/payment/fail', [DomainEventController::class, 'failPayment']);
});
```

### 3. Registra i listener

Aggiungi queste righe al tuo `app/Providers/EventServiceProvider.php`:

```php
use App\Events\OrderConfirmed;
use App\Events\OrderCancelled;
use App\Events\OrderShipped;
use App\Events\PaymentProcessed;
use App\Events\PaymentFailed;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\UpdateInventory;
use App\Listeners\CreateInvoice;
use App\Listeners\SendOrderCancellationEmail;
use App\Listeners\RestoreInventory;
use App\Listeners\SendShippingNotification;
use App\Listeners\UpdateOrderStatus;

protected $listen = [
    OrderConfirmed::class => [
        SendOrderConfirmationEmail::class,
        UpdateInventory::class,
        CreateInvoice::class,
    ],
    OrderCancelled::class => [
        SendOrderCancellationEmail::class,
        RestoreInventory::class,
    ],
    OrderShipped::class => [
        SendShippingNotification::class,
        UpdateOrderStatus::class,
    ],
    PaymentProcessed::class => [
        UpdateOrderStatus::class,
    ],
    PaymentFailed::class => [
        UpdateOrderStatus::class,
    ],
];
```

### 4. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/domain-event

# Testa via API
curl http://localhost:8000/api/domain-event/test

# Esegui i test
php artisan test tests/Feature/DomainEventPatternTest.php
```

### 5. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/domain-event` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/domain-event/test`
3. **Test**: Esegui `php artisan test tests/Feature/DomainEventPatternTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Http/Controllers/DomainEventController.php` - Controller per testare il pattern
- `app/Events/OrderConfirmed.php` - Evento per ordine confermato
- `app/Events/OrderCancelled.php` - Evento per ordine cancellato
- `app/Events/OrderShipped.php` - Evento per ordine spedito
- `app/Events/PaymentProcessed.php` - Evento per pagamento processato
- `app/Events/PaymentFailed.php` - Evento per pagamento fallito
- `app/Listeners/SendOrderConfirmationEmail.php` - Listener per email di conferma
- `app/Listeners/UpdateInventory.php` - Listener per aggiornamento inventario
- `app/Listeners/CreateInvoice.php` - Listener per creazione fattura
- `app/Listeners/SendOrderCancellationEmail.php` - Listener per email di cancellazione
- `app/Listeners/RestoreInventory.php` - Listener per ripristino inventario
- `app/Listeners/SendShippingNotification.php` - Listener per notifica spedizione
- `app/Listeners/UpdateOrderStatus.php` - Listener per aggiornamento status
- `app/Services/EventBus.php` - Event Bus per gestire eventi e listener
- `app/Services/NotificationService.php` - Servizio per notifiche
- `app/Services/InventoryService.php` - Servizio per inventario
- `app/Services/BillingService.php` - Servizio per fatturazione
- `app/Services/ShippingService.php` - Servizio per spedizioni
- `resources/views/domain_event/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/DomainEventPatternTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Puoi configurare il Domain Event Pattern modificando le costanti nelle classi:

```php
// In EventBus.php
const MAX_RETRIES = 3;
const RETRY_DELAY = 1000; // milliseconds

// In NotificationService.php
const EMAIL_TEMPLATE = 'emails.order-confirmation';
const SMS_ENABLED = true;
```

### Estensione
Puoi facilmente aggiungere nuovi eventi e listener:

1. Crea una nuova classe evento che estende `DomainEvent`
2. Crea un nuovo listener che implementa `EventListener`
3. Registra il listener nell'Event Bus
4. Il pattern funzionerà automaticamente

## Note importanti
- I file sono pronti per essere copiati in un progetto Laravel esistente
- Gli eventi disaccoppiano i servizi e rendono il sistema più estensibile
- I listener gestiscono le reazioni agli eventi in modo indipendente
- È facile aggiungere nuovi listener senza modificare il codice esistente
