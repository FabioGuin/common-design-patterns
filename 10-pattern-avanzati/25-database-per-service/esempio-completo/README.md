# Database Per Service Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Database Per Service in Laravel attraverso un sistema e-commerce con microservizi che utilizzano database dedicati. L'esempio include:

- **UserService** con database PostgreSQL dedicato
- **ProductService** con database MySQL dedicato  
- **OrderService** con database MongoDB dedicato
- **PaymentService** con database PostgreSQL dedicato
- **EventBus** per comunicazione tra servizi
- **API Gateway** per routing delle richieste

## Come funziona l'esempio
Il pattern Database Per Service gestisce:
- **Database dedicati** per ogni microservizio
- **Comunicazione asincrona** tramite eventi
- **Autonomia completa** di ogni servizio
- **Scalabilità indipendente** per ogni database
- **Gestione transazioni** distribuite
- **Sincronizzazione dati** tramite eventi

Quando testi l'esempio, vedrai che:
1. Ogni servizio ha il proprio database
2. I servizi comunicano tramite eventi
3. Le transazioni sono gestite localmente
4. I dati sono sincronizzati tramite eventi
5. Ogni servizio può scalare indipendentemente

## Caratteristiche tecniche
- Database dedicati per ogni microservizio
- Sistema di eventi per comunicazione
- API Gateway per routing
- Gestione transazioni distribuite
- Dashboard per monitorare i servizi
- Sistema di recovery automatico

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **PostgreSQL** per UserService e PaymentService
- **MySQL** per ProductService
- **MongoDB** per OrderService
- **Redis** per EventBus

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Services/*.php app/Services/
cp /path/to/this/example/app/Http/Controllers/*.php app/Http/Controllers/
cp /path/to/this/example/app/Models/*.php app/Models/
mkdir -p resources/views/database-per-service
cp /path/to/this/example/resources/views/database-per-service/example.blade.php resources/views/database-per-service/
cp /path/to/this/example/tests/Feature/DatabasePerServiceTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\UserServiceController;
use App\Http\Controllers\ProductServiceController;
use App\Http\Controllers\OrderServiceController;
use App\Http\Controllers\PaymentServiceController;

// Route per il pattern Database Per Service
Route::get('/database-per-service', [ApiGatewayController::class, 'index']);
Route::get('/database-per-service/test', [ApiGatewayController::class, 'test']);

// Route API Gateway
Route::prefix('api/database-per-service')->group(function () {
    Route::get('/', [ApiGatewayController::class, 'index']);
    Route::post('/test', [ApiGatewayController::class, 'test']);
    Route::get('/services', [ApiGatewayController::class, 'services']);
    Route::get('/stats', [ApiGatewayController::class, 'stats']);
    
    // User Service
    Route::get('/users', [UserServiceController::class, 'index']);
    Route::post('/users', [UserServiceController::class, 'store']);
    Route::get('/users/{id}', [UserServiceController::class, 'show']);
    
    // Product Service
    Route::get('/products', [ProductServiceController::class, 'index']);
    Route::post('/products', [ProductServiceController::class, 'store']);
    Route::get('/products/{id}', [ProductServiceController::class, 'show']);
    
    // Order Service
    Route::get('/orders', [OrderServiceController::class, 'index']);
    Route::post('/orders', [OrderServiceController::class, 'store']);
    Route::get('/orders/{id}', [OrderServiceController::class, 'show']);
    
    // Payment Service
    Route::get('/payments', [PaymentServiceController::class, 'index']);
    Route::post('/payments', [PaymentServiceController::class, 'store']);
    Route::get('/payments/{id}', [PaymentServiceController::class, 'show']);
});
```

### 3. Configura i database

Aggiungi al tuo `config/database.php`:

```php
'connections' => [
    // User Service Database
    'user_service' => [
        'driver' => 'pgsql',
        'host' => env('USER_DB_HOST', '127.0.0.1'),
        'port' => env('USER_DB_PORT', '5432'),
        'database' => env('USER_DB_DATABASE', 'user_service'),
        'username' => env('USER_DB_USERNAME', 'forge'),
        'password' => env('USER_DB_PASSWORD', ''),
    ],
    
    // Product Service Database
    'product_service' => [
        'driver' => 'mysql',
        'host' => env('PRODUCT_DB_HOST', '127.0.0.1'),
        'port' => env('PRODUCT_DB_PORT', '3306'),
        'database' => env('PRODUCT_DB_DATABASE', 'product_service'),
        'username' => env('PRODUCT_DB_USERNAME', 'forge'),
        'password' => env('PRODUCT_DB_PASSWORD', ''),
    ],
    
    // Order Service Database
    'order_service' => [
        'driver' => 'mongodb',
        'host' => env('ORDER_DB_HOST', '127.0.0.1'),
        'port' => env('ORDER_DB_PORT', '27017'),
        'database' => env('ORDER_DB_DATABASE', 'order_service'),
        'username' => env('ORDER_DB_USERNAME', ''),
        'password' => env('ORDER_DB_PASSWORD', ''),
    ],
    
    // Payment Service Database
    'payment_service' => [
        'driver' => 'pgsql',
        'host' => env('PAYMENT_DB_HOST', '127.0.0.1'),
        'port' => env('PAYMENT_DB_PORT', '5432'),
        'database' => env('PAYMENT_DB_DATABASE', 'payment_service'),
        'username' => env('PAYMENT_DB_USERNAME', 'forge'),
        'password' => env('PAYMENT_DB_PASSWORD', ''),
    ],
],
```

### 4. Configura l'ambiente

Aggiungi al tuo `.env`:

```env
# User Service Database
USER_DB_HOST=127.0.0.1
USER_DB_PORT=5432
USER_DB_DATABASE=user_service
USER_DB_USERNAME=forge
USER_DB_PASSWORD=

# Product Service Database
PRODUCT_DB_HOST=127.0.0.1
PRODUCT_DB_PORT=3306
PRODUCT_DB_DATABASE=product_service
PRODUCT_DB_USERNAME=forge
PRODUCT_DB_PASSWORD=

# Order Service Database
ORDER_DB_HOST=127.0.0.1
ORDER_DB_PORT=27017
ORDER_DB_DATABASE=order_service
ORDER_DB_USERNAME=
ORDER_DB_PASSWORD=

# Payment Service Database
PAYMENT_DB_HOST=127.0.0.1
PAYMENT_DB_PORT=5432
PAYMENT_DB_DATABASE=payment_service
PAYMENT_DB_USERNAME=forge
PAYMENT_DB_PASSWORD=

# Event Bus
EVENT_BUS_CONNECTION=redis
```

### 5. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/database-per-service

# Testa via API
curl http://localhost:8000/api/database-per-service/test

# Esegui i test
php artisan test tests/Feature/DatabasePerServiceTest.php
```

### 6. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/database-per-service` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/database-per-service/test`
3. **Test**: Esegui `php artisan test tests/Feature/DatabasePerServiceTest.php`
4. **Database**: Verifica che i dati vengano salvati nei database corretti

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern
php test-standalone.php

# Test rapido inline
php -r "require_once 'app/Services/EventBusService.php'; use App\Services\EventBusService; \$s = new EventBusService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Services/EventBusService.php` - Service per gestione eventi
- `app/Services/UserService.php` - Service per gestione utenti
- `app/Services/ProductService.php` - Service per gestione prodotti
- `app/Services/OrderService.php` - Service per gestione ordini
- `app/Services/PaymentService.php` - Service per gestione pagamenti
- `app/Http/Controllers/ApiGatewayController.php` - Controller per API Gateway
- `app/Http/Controllers/*ServiceController.php` - Controller per ogni servizio
- `app/Models/*.php` - Model per ogni servizio
- `resources/views/database-per-service/example.blade.php` - Vista interattiva
- `tests/Feature/DatabasePerServiceTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto
- `test-standalone.php` - Test standalone per verificare il pattern senza Laravel

## Personalizzazione

### Configurazione
- Modifica le configurazioni dei database
- Personalizza gli eventi tra servizi
- Aggiungi nuovi servizi
- Configura la sincronizzazione

### Estensione
- Implementa nuovi microservizi
- Aggiungi pattern Saga per transazioni distribuite
- Integra con sistemi di monitoring
- Implementa circuit breaker

## Note importanti
- Ogni servizio ha il proprio database dedicato
- I servizi comunicano tramite eventi asincroni
- Le transazioni sono gestite localmente
- I file sono pronti per essere copiati in un progetto Laravel esistente
