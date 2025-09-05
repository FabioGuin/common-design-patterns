# Hexagonal Architecture Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Hexagonal Architecture in Laravel attraverso un sistema di gestione ordini con logica di business completamente isolata. L'esempio include:

- **Core Domain** con logica di business pura e isolata
- **Ports** per definire i contratti tra core e adapter
- **Adapters** per database, servizi esterni e interfacce
- **Inbound Adapters** per gestire input (web, API, CLI)
- **Outbound Adapters** per gestire output (database, servizi esterni)
- **Dependency Injection** per collegare tutti i componenti

## Come funziona l'esempio
Il pattern Hexagonal Architecture gestisce:
- **Isolamento**: La logica di business è completamente isolata
- **Ports**: Interfacce che definiscono i contratti
- **Adapters**: Implementazioni concrete dei port
- **Dependency Injection**: Collega tutto insieme
- **Testabilità**: Ogni componente può essere testato isolatamente

Quando testi l'esempio, vedrai che:
1. La logica di business è completamente isolata dal framework
2. Puoi cambiare database senza toccare la logica
3. I test sono semplici e veloci
4. È facile integrare nuovi sistemi esterni
5. L'architettura è flessibile e mantenibile

## Caratteristiche tecniche
- Core Domain con logica di business pura
- Ports per definire i contratti
- Adapters per database e servizi esterni
- Inbound adapters per web e API
- Dependency injection per collegare tutto
- Test isolati per ogni componente
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
cp /path/to/this/example/app/Domain/*.php app/Domain/
cp /path/to/this/example/app/Ports/*.php app/Ports/
cp /path/to/this/example/app/Adapters/*.php app/Adapters/
cp /path/to/this/example/app/Services/*.php app/Services/
cp /path/to/this/example/app/Http/Controllers/*.php app/Http/Controllers/
mkdir -p resources/views/hexagonal-architecture
cp /path/to/this/example/resources/views/hexagonal-architecture/example.blade.php resources/views/hexagonal-architecture/
cp /path/to/this/example/tests/Feature/*.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\OrderController;

// Route per il pattern Hexagonal Architecture
Route::get('/hexagonal-architecture', [OrderController::class, 'index']);
Route::get('/hexagonal-architecture/test', [OrderController::class, 'test']);

// Route API
Route::prefix('api/hexagonal-architecture')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/test', [OrderController::class, 'test']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::put('/orders/{id}', [OrderController::class, 'updateOrder']);
    Route::delete('/orders/{id}', [OrderController::class, 'cancelOrder']);
    Route::get('/orders/{id}', [OrderController::class, 'getOrder']);
    Route::get('/orders', [OrderController::class, 'listOrders']);
    Route::get('/stats', [OrderController::class, 'getStats']);
    Route::post('/process-payment/{id}', [OrderController::class, 'processPayment']);
    Route::post('/send-notification/{id}', [OrderController::class, 'sendNotification']);
});
```

### 3. Configura i service provider

Aggiungi al tuo `app/Providers/AppServiceProvider.php`:

```php
use App\Ports\OrderRepositoryInterface;
use App\Adapters\EloquentOrderRepository;
use App\Ports\PaymentServiceInterface;
use App\Adapters\StripePaymentService;
use App\Ports\NotificationServiceInterface;
use App\Adapters\EmailNotificationService;

public function register()
{
    // Bind dei port agli adapter
    $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
    $this->app->bind(PaymentServiceInterface::class, StripePaymentService::class);
    $this->app->bind(NotificationServiceInterface::class, EmailNotificationService::class);
}
```

### 4. Esegui le migrazioni

```bash
# Crea le tabelle necessarie
php artisan make:migration create_orders_table
php artisan make:migration create_order_items_table
```

Copia il contenuto delle migrazioni da `database/migrations/` nel tuo progetto.

### 5. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/hexagonal-architecture

# Testa via API
curl http://localhost:8000/api/hexagonal-architecture/test

# Esegui i test
php artisan test tests/Feature/HexagonalArchitectureTest.php
```

### 6. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/hexagonal-architecture` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/hexagonal-architecture/test`
3. **Test**: Esegui `php artisan test tests/Feature/HexagonalArchitectureTest.php`
4. **Dependency Injection**: Verifica che i port siano collegati agli adapter

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern
php test-standalone.php

# Test rapido inline
php -r "require_once 'app/Domain/OrderService.php'; use App\Domain\OrderService; echo 'Pattern ID: ' . uniqid();"
```

## File inclusi

- `app/Domain/OrderService.php` - Core domain con logica di business
- `app/Domain/Order.php` - Entità di business
- `app/Ports/OrderRepositoryInterface.php` - Port per repository
- `app/Ports/PaymentServiceInterface.php` - Port per pagamenti
- `app/Ports/NotificationServiceInterface.php` - Port per notifiche
- `app/Adapters/EloquentOrderRepository.php` - Adapter per database
- `app/Adapters/StripePaymentService.php` - Adapter per pagamenti
- `app/Adapters/EmailNotificationService.php` - Adapter per notifiche
- `app/Http/Controllers/OrderController.php` - Inbound adapter per web
- `resources/views/hexagonal-architecture/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/HexagonalArchitectureTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto
- `test-standalone.php` - Test standalone per verificare il pattern senza Laravel

## Personalizzazione

### Configurazione
- Modifica i port per le tue esigenze
- Personalizza gli adapter per i tuoi servizi
- Aggiungi nuovi port e adapter

### Estensione
- Implementa nuovi adapter per servizi esterni
- Aggiungi nuovi inbound adapter per interfacce
- Estendi il core domain con nuova logica

## Note importanti
- Il pattern fornisce isolamento completo della logica di business
- Ogni componente può essere testato isolatamente
- È facile cambiare implementazioni senza toccare la logica
- L'architettura è flessibile e mantenibile
- I file sono pronti per essere copiati in un progetto Laravel esistente
