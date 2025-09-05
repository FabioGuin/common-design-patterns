# API Gateway Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern API Gateway in Laravel attraverso un gateway unificato per servizi multipli. L'esempio include:

- **API Gateway** per routing e orchestrazione delle richieste
- **Autenticazione** centralizzata con JWT
- **Autorizzazione** basata su ruoli e permessi
- **Rate Limiting** per controllare la frequenza delle richieste
- **Logging** centralizzato di tutte le richieste
- **Caching** per ottimizzare le performance
- **Monitoring** per tracciare le metriche
- **Gestione Errori** con fallback e retry

## Come funziona l'esempio
Il pattern API Gateway gestisce:
- **Routing**: Determina quale servizio backend gestisce la richiesta
- **Autenticazione**: Verifica le credenziali del client
- **Autorizzazione**: Controlla i permessi per l'operazione richiesta
- **Rate Limiting**: Applica limiti di frequenza se necessario
- **Trasformazione**: Modifica la richiesta e risposta se necessario
- **Delegazione**: Inoltra la richiesta al servizio appropriato
- **Aggregazione**: Combina le risposte se necessario
- **Logging**: Registra tutte le richieste e risposte

Quando testi l'esempio, vedrai che:
1. Il gateway riceve tutte le richieste client
2. Applica autenticazione e autorizzazione
3. Implementa rate limiting e throttling
4. Route le richieste ai servizi appropriati
5. Aggrega e trasforma le risposte
6. Fornisce logging e monitoring centralizzati

## Caratteristiche tecniche
- Gateway unificato per servizi multipli
- Autenticazione JWT centralizzata
- Autorizzazione basata su ruoli
- Rate limiting e throttling
- Caching intelligente
- Logging e monitoring centralizzati
- Gestione errori e fallback
- Interfaccia web per testare le funzionalità

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Database** (MySQL/PostgreSQL) configurato
- **Redis** per caching e rate limiting
- **JWT** per autenticazione (opzionale)

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Services/*.php app/Services/
cp /path/to/this/example/app/Http/Controllers/*.php app/Http/Controllers/
cp /path/to/this/example/app/Http/Middleware/*.php app/Http/Middleware/
cp /path/to/this/example/app/Models/*.php app/Models/
mkdir -p resources/views/api-gateway
cp /path/to/this/example/resources/views/api-gateway/example.blade.php resources/views/api-gateway/
cp /path/to/this/example/tests/Feature/*.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\ApiGatewayController;

// Route per il pattern API Gateway
Route::get('/api-gateway', [ApiGatewayController::class, 'index']);
Route::get('/api-gateway/test', [ApiGatewayController::class, 'test']);

// Route API Gateway
Route::prefix('api/v1')->middleware(['api.gateway'])->group(function () {
    Route::get('/', [ApiGatewayController::class, 'index']);
    Route::post('/test', [ApiGatewayController::class, 'test']);
    
    // User Service
    Route::prefix('users')->group(function () {
        Route::get('/', [ApiGatewayController::class, 'listUsers']);
        Route::post('/', [ApiGatewayController::class, 'createUser']);
        Route::get('/{id}', [ApiGatewayController::class, 'getUser']);
        Route::put('/{id}', [ApiGatewayController::class, 'updateUser']);
        Route::delete('/{id}', [ApiGatewayController::class, 'deleteUser']);
    });
    
    // Product Service
    Route::prefix('products')->group(function () {
        Route::get('/', [ApiGatewayController::class, 'listProducts']);
        Route::post('/', [ApiGatewayController::class, 'createProduct']);
        Route::get('/{id}', [ApiGatewayController::class, 'getProduct']);
        Route::put('/{id}', [ApiGatewayController::class, 'updateProduct']);
    });
    
    // Order Service
    Route::prefix('orders')->group(function () {
        Route::get('/', [ApiGatewayController::class, 'listOrders']);
        Route::post('/', [ApiGatewayController::class, 'createOrder']);
        Route::get('/{id}', [ApiGatewayController::class, 'getOrder']);
        Route::put('/{id}/status', [ApiGatewayController::class, 'updateOrderStatus']);
    });
    
    // Payment Service
    Route::prefix('payments')->group(function () {
        Route::get('/', [ApiGatewayController::class, 'listPayments']);
        Route::post('/', [ApiGatewayController::class, 'processPayment']);
        Route::get('/{id}', [ApiGatewayController::class, 'getPayment']);
        Route::post('/{id}/refund', [ApiGatewayController::class, 'refundPayment']);
    });
    
    // Gateway Management
    Route::prefix('gateway')->group(function () {
        Route::get('/health', [ApiGatewayController::class, 'healthCheck']);
        Route::get('/stats', [ApiGatewayController::class, 'getStats']);
        Route::get('/services', [ApiGatewayController::class, 'listServices']);
    });
});
```

### 3. Registra i middleware

Aggiungi al tuo `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // ... middleware esistenti
        \App\Http\Middleware\ApiGatewayMiddleware::class,
    ],
];

protected $routeMiddleware = [
    // ... middleware esistenti
    'api.gateway' => \App\Http\Middleware\ApiGatewayMiddleware::class,
    'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
    'auth.jwt' => \App\Http\Middleware\JwtAuthMiddleware::class,
];
```

### 4. Configura i service provider

Aggiungi al tuo `app/Providers/AppServiceProvider.php`:

```php
use App\Services\ApiGatewayService;
use App\Services\AuthenticationService;
use App\Services\AuthorizationService;
use App\Services\RateLimitService;
use App\Services\LoggingService;
use App\Services\CachingService;
use App\Services\MonitoringService;

public function register()
{
    // Registra i servizi
    $this->app->singleton(ApiGatewayService::class);
    $this->app->singleton(AuthenticationService::class);
    $this->app->singleton(AuthorizationService::class);
    $this->app->singleton(RateLimitService::class);
    $this->app->singleton(LoggingService::class);
    $this->app->singleton(CachingService::class);
    $this->app->singleton(MonitoringService::class);
}
```

### 5. Esegui le migrazioni

```bash
# Crea le tabelle necessarie
php artisan make:migration create_api_requests_table
php artisan make:migration create_api_services_table
php artisan make:migration create_api_users_table
```

Copia il contenuto delle migrazioni da `database/migrations/` nel tuo progetto.

### 6. Configura Redis

Aggiungi al tuo `.env`:

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 7. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/api-gateway

# Testa via API
curl http://localhost:8000/api/v1/gateway/health

# Esegui i test
php artisan test tests/Feature/ApiGatewayTest.php
```

### 8. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/api-gateway` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/v1/gateway/health`
3. **Test**: Esegui `php artisan test tests/Feature/ApiGatewayTest.php`
4. **Gateway**: Verifica che il gateway route correttamente le richieste

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern
php test-standalone.php

# Test rapido inline
php -r "require_once 'app/Services/ApiGatewayService.php'; use App\Services\ApiGatewayService; \$s = new ApiGatewayService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Services/ApiGatewayService.php` - Servizio principale del gateway
- `app/Services/AuthenticationService.php` - Servizio per autenticazione
- `app/Services/AuthorizationService.php` - Servizio per autorizzazione
- `app/Services/RateLimitService.php` - Servizio per rate limiting
- `app/Services/LoggingService.php` - Servizio per logging
- `app/Services/CachingService.php` - Servizio per caching
- `app/Services/MonitoringService.php` - Servizio per monitoring
- `app/Http/Controllers/ApiGatewayController.php` - Controller per testare il pattern
- `app/Http/Middleware/ApiGatewayMiddleware.php` - Middleware per il gateway
- `app/Http/Middleware/RateLimitMiddleware.php` - Middleware per rate limiting
- `app/Http/Middleware/JwtAuthMiddleware.php` - Middleware per autenticazione JWT
- `app/Models/ApiRequest.php` - Modello per richieste API
- `app/Models/ApiService.php` - Modello per servizi API
- `app/Models/ApiUser.php` - Modello per utenti API
- `resources/views/api-gateway/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/ApiGatewayTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto
- `test-standalone.php` - Test standalone per verificare il pattern senza Laravel

## Personalizzazione

### Configurazione
- Modifica i servizi per le tue esigenze
- Personalizza l'autenticazione e autorizzazione
- Aggiusta i limiti di rate limiting

### Estensione
- Implementa nuovi servizi backend
- Aggiungi nuove funzionalità di sicurezza
- Integra con sistemi di monitoring esterni

## Note importanti
- Il pattern fornisce un punto di accesso unificato per i client
- L'autenticazione e autorizzazione sono centralizzate
- Il rate limiting e throttling sono uniformi
- Il logging e monitoring sono centralizzati
- I file sono pronti per essere copiati in un progetto Laravel esistente
