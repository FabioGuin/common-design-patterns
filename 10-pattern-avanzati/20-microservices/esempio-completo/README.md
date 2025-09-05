# Microservices Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Microservices in Laravel attraverso un sistema e-commerce distribuito con servizi separati. L'esempio include:

- **User Service** per gestione utenti e autenticazione
- **Product Service** per catalogo prodotti e inventario
- **Order Service** per gestione ordini e carrello
- **Payment Service** per elaborazione pagamenti
- **API Gateway** per routing e orchestrazione
- **Service Discovery** per trovare e comunicare con i servizi

## Come funziona l'esempio
Il pattern Microservices gestisce:
- **Decomposizione**: Servizi separati per dominio di business
- **Autonomia**: Ogni servizio è indipendente e autonomo
- **Comunicazione**: I servizi comunicano tramite API REST
- **Database**: Ogni servizio ha il proprio database
- **Deployment**: Ogni servizio può essere deployato indipendentemente
- **Monitoring**: Monitoraggio centralizzato di tutti i servizi

Quando testi l'esempio, vedrai che:
1. Ogni servizio ha la propria responsabilità specifica
2. I servizi comunicano tramite API REST
3. Ogni servizio ha il proprio database
4. L'API Gateway orchestra le chiamate
5. I servizi possono essere deployati indipendentemente
6. Il sistema è resiliente ai fallimenti

## Caratteristiche tecniche
- Servizi separati per dominio di business
- Comunicazione asincrona tra servizi
- Database dedicati per ogni servizio
- API Gateway per routing
- Service Discovery per trovare i servizi
- Interfaccia web per testare le funzionalità

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Database** (MySQL/PostgreSQL) configurato
- **Queue worker** attivo per processing asincrono
- **Redis** per caching e comunicazione

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Services/*.php app/Services/
cp /path/to/this/example/app/Http/Controllers/*.php app/Http/Controllers/
cp /path/to/this/example/app/Models/*.php app/Models/
mkdir -p resources/views/microservices
cp /path/to/this/example/resources/views/microservices/example.blade.php resources/views/microservices/
cp /path/to/this/example/tests/Feature/*.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\MicroservicesController;

// Route per il pattern Microservices
Route::get('/microservices', [MicroservicesController::class, 'index']);
Route::get('/microservices/test', [MicroservicesController::class, 'test']);

// Route API
Route::prefix('api/microservices')->group(function () {
    Route::get('/', [MicroservicesController::class, 'index']);
    Route::post('/test', [MicroservicesController::class, 'test']);
    
    // User Service
    Route::post('/users', [MicroservicesController::class, 'createUser']);
    Route::get('/users/{id}', [MicroservicesController::class, 'getUser']);
    
    // Product Service
    Route::post('/products', [MicroservicesController::class, 'createProduct']);
    Route::get('/products/{id}', [MicroservicesController::class, 'getProduct']);
    Route::get('/products', [MicroservicesController::class, 'listProducts']);
    
    // Order Service
    Route::post('/orders', [MicroservicesController::class, 'createOrder']);
    Route::get('/orders/{id}', [MicroservicesController::class, 'getOrder']);
    Route::get('/orders', [MicroservicesController::class, 'listOrders']);
    
    // Payment Service
    Route::post('/payments', [MicroservicesController::class, 'processPayment']);
    Route::get('/payments/{id}', [MicroservicesController::class, 'getPayment']);
    
    // Service Discovery
    Route::get('/services', [MicroservicesController::class, 'listServices']);
    Route::get('/health', [MicroservicesController::class, 'healthCheck']);
});
```

### 3. Configura i service provider

Aggiungi al tuo `app/Providers/AppServiceProvider.php`:

```php
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ApiGatewayService;
use App\Services\ServiceDiscoveryService;

public function register()
{
    // Registra i servizi
    $this->app->singleton(UserService::class);
    $this->app->singleton(ProductService::class);
    $this->app->singleton(OrderService::class);
    $this->app->singleton(PaymentService::class);
    $this->app->singleton(ApiGatewayService::class);
    $this->app->singleton(ServiceDiscoveryService::class);
}
```

### 4. Esegui le migrazioni

```bash
# Crea le tabelle necessarie
php artisan make:migration create_users_table
php artisan make:migration create_products_table
php artisan make:migration create_orders_table
php artisan make:migration create_payments_table
```

Copia il contenuto delle migrazioni da `database/migrations/` nel tuo progetto.

### 5. Configura Redis

Aggiungi al tuo `.env`:

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 6. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/microservices

# Testa via API
curl http://localhost:8000/api/microservices/test

# Esegui i test
php artisan test tests/Feature/MicroservicesTest.php
```

### 7. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/microservices` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/microservices/test`
3. **Test**: Esegui `php artisan test tests/Feature/MicroservicesTest.php`
4. **Servizi**: Verifica che i servizi comunichino correttamente

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern

# Test rapido inline
php -r "require_once 'app/Services/UserService.php'; use App\Services\UserService; \$s = new UserService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Services/UserService.php` - Servizio per gestione utenti
- `app/Services/ProductService.php` - Servizio per catalogo prodotti
- `app/Services/OrderService.php` - Servizio per gestione ordini
- `app/Services/PaymentService.php` - Servizio per pagamenti
- `app/Services/ApiGatewayService.php` - API Gateway per routing
- `app/Services/ServiceDiscoveryService.php` - Service Discovery
- `app/Models/User.php` - Modello utente
- `app/Models/Product.php` - Modello prodotto
- `app/Models/Order.php` - Modello ordine
- `app/Models/Payment.php` - Modello pagamento
- `app/Http/Controllers/MicroservicesController.php` - Controller per testare il pattern
- `resources/views/microservices/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/MicroservicesTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
- Modifica i servizi per le tue esigenze
- Personalizza la comunicazione tra servizi
- Aggiungi nuovi servizi

### Estensione
- Implementa nuovi microservizi
- Aggiungi service discovery avanzato
- Integra con sistemi di monitoring

## Note importanti
- Il pattern fornisce scalabilità indipendente dei servizi
- Ogni servizio può essere sviluppato e deployato autonomamente
- La comunicazione tra servizi è asincrona e resiliente
- L'architettura è flessibile e mantenibile
- I file sono pronti per essere copiati in un progetto Laravel esistente
