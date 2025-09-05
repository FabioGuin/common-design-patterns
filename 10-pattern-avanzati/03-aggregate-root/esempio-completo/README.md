# Aggregate Root Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Aggregate Root in Laravel attraverso un sistema e-commerce con Order Aggregate per gestire ordini, prodotti e pagamenti. L'esempio include:

- **Order Aggregate Root** che controlla tutte le modifiche all'ordine
- **OrderItem entità figlie** che rappresentano i prodotti nell'ordine
- **Value Object** per indirizzi, prezzi e informazioni di pagamento
- **Regole di business centralizzate** nell'aggregate root
- **Eventi di dominio** per notificare cambiamenti
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il pattern Aggregate Root gestisce:
- **Consistenza garantita** per tutte le modifiche all'ordine
- **Regole centralizzate** per validazione e business logic
- **Transazioni atomiche** su gruppi di entità correlate
- **Integrità dei dati** attraverso il controllo dell'aggregate root

Quando testi l'esempio, vedrai che:
1. Solo l'Order Aggregate Root può essere modificato dall'esterno
2. Le regole di business sono centralizzate e sempre rispettate
3. Le modifiche alle entità figlie passano attraverso l'aggregate root
4. Tutte le operazioni mantengono la consistenza dei dati

## Caratteristiche tecniche
- Implementazione completa del Aggregate Root Pattern
- Order Aggregate con regole di business complete
- Eventi di dominio per notificare cambiamenti
- Repository pattern per persistenza
- Test completi con Pest

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Http/Controllers/AggregateRootController.php app/Http/Controllers/
cp /path/to/this/example/app/Aggregates/Order.php app/Aggregates/
cp /path/to/this/example/app/Entities/OrderItem.php app/Entities/
cp /path/to/this/example/app/ValueObjects/OrderAddress.php app/ValueObjects/
cp /path/to/this/example/app/ValueObjects/OrderPayment.php app/ValueObjects/
cp /path/to/this/example/app/Events/OrderConfirmed.php app/Events/
cp /path/to/this/example/app/Events/OrderCancelled.php app/Events/
cp /path/to/this/example/app/Repositories/OrderRepository.php app/Repositories/
mkdir -p resources/views/aggregate_root
cp /path/to/this/example/resources/views/aggregate_root/example.blade.php resources/views/aggregate_root/
cp /path/to/this/example/tests/Feature/AggregateRootPatternTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\AggregateRootController;

// Route per il pattern Aggregate Root
Route::get('/aggregate-root', [AggregateRootController::class, 'index']);
Route::get('/aggregate-root/test', [AggregateRootController::class, 'test']);

// Route API
Route::prefix('api/aggregate-root')->group(function () {
    Route::get('/', [AggregateRootController::class, 'index']);
    Route::post('/test', [AggregateRootController::class, 'test']);
    Route::post('/order/create', [AggregateRootController::class, 'createOrder']);
    Route::post('/order/{id}/add-item', [AggregateRootController::class, 'addItem']);
    Route::post('/order/{id}/confirm', [AggregateRootController::class, 'confirmOrder']);
    Route::post('/order/{id}/cancel', [AggregateRootController::class, 'cancelOrder']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/aggregate-root

# Testa via API
curl http://localhost:8000/api/aggregate-root/test

# Esegui i test
php artisan test tests/Feature/AggregateRootPatternTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/aggregate-root` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/aggregate-root/test`
3. **Test**: Esegui `php artisan test tests/Feature/AggregateRootPatternTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Http/Controllers/AggregateRootController.php` - Controller per testare il pattern
- `app/Aggregates/Order.php` - Order Aggregate Root
- `app/Entities/OrderItem.php` - OrderItem entità figlia
- `app/ValueObjects/OrderAddress.php` - Value Object per indirizzi
- `app/ValueObjects/OrderPayment.php` - Value Object per pagamenti
- `app/Events/OrderConfirmed.php` - Evento di dominio per ordine confermato
- `app/Events/OrderCancelled.php` - Evento di dominio per ordine cancellato
- `app/Repositories/OrderRepository.php` - Repository per persistenza
- `resources/views/aggregate_root/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/AggregateRootPatternTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Puoi configurare l'Aggregate Root modificando le costanti nelle classi:

```php
// In Order.php
const MAX_ITEMS_PER_ORDER = 50;
const MIN_ORDER_TOTAL = 10.00;

// In OrderItem.php
const MAX_QUANTITY_PER_ITEM = 100;
```

### Estensione
Puoi facilmente aggiungere nuovi aggregate:

1. Crea una nuova classe che estende `AggregateRoot`
2. Implementa le regole di business specifiche
3. Aggiungi le entità figlie necessarie
4. Il pattern funzionerà automaticamente

## Note importanti
- I file sono pronti per essere copiati in un progetto Laravel esistente
- L'Aggregate Root controlla tutte le modifiche e garantisce la consistenza
- Le regole di business sono centralizzate e sempre rispettate
- Le transazioni sono atomiche e sicure
