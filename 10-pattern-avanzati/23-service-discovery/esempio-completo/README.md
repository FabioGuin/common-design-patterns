# Service Discovery Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Service Discovery in Laravel attraverso un registry centralizzato per la gestione dinamica dei servizi. L'esempio include:

- **Service Registry** per registrazione e scoperta dei servizi
- **Health Check** per monitoraggio della salute dei servizi
- **Load Balancer** per bilanciamento del carico tra istanze
- **Service Client** per comunicazione trasparente
- **Heartbeat** per mantenere i servizi attivi
- **Failover** automatico per resilienza
- **Service Catalog** per gestione dei servizi disponibili

## Come funziona l'esempio
Il pattern Service Discovery gestisce:
- **Registrazione**: I servizi si registrano automaticamente al startup
- **Heartbeat**: I servizi inviano heartbeat per confermare la disponibilità
- **Scoperta**: I client cercano servizi nel registry
- **Risoluzione**: Il registry restituisce l'indirizzo del servizio
- **Bilanciamento**: Il registry restituisce istanze multiple per bilanciamento
- **Health Check**: Il registry verifica periodicamente la salute dei servizi
- **Deregistrazione**: I servizi si deregistrano al shutdown
- **Failover**: Gestione automatica dei servizi non disponibili

Quando testi l'esempio, vedrai che:
1. I servizi si registrano automaticamente nel registry
2. Il registry monitora la salute dei servizi
3. I client possono scoprire e comunicare con i servizi
4. Il bilanciamento del carico funziona automaticamente
5. Il failover è trasparente e automatico

## Caratteristiche tecniche
- Registry centralizzato per gestione servizi
- Health check automatico e heartbeat
- Load balancing tra istanze multiple
- Failover automatico e trasparente
- Service catalog per gestione servizi
- Interfaccia web per testare le funzionalità

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Database** (MySQL/PostgreSQL) configurato
- **Redis** per caching e heartbeat
- **Queue** per elaborazione asincrona

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
cp /path/to/this/example/app/Jobs/*.php app/Jobs/
mkdir -p resources/views/service-discovery
cp /path/to/this/example/resources/views/service-discovery/example.blade.php resources/views/service-discovery/
cp /path/to/this/example/tests/Feature/*.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\ServiceDiscoveryController;

// Route per il pattern Service Discovery
Route::get('/service-discovery', [ServiceDiscoveryController::class, 'index']);
Route::get('/service-discovery/test', [ServiceDiscoveryController::class, 'test']);

// Route Service Discovery
Route::prefix('api/v1/discovery')->group(function () {
    Route::get('/', [ServiceDiscoveryController::class, 'index']);
    Route::post('/test', [ServiceDiscoveryController::class, 'test']);
    
    // Service Registry
    Route::prefix('registry')->group(function () {
        Route::get('/', [ServiceDiscoveryController::class, 'listServices']);
        Route::post('/register', [ServiceDiscoveryController::class, 'registerService']);
        Route::put('/{id}/heartbeat', [ServiceDiscoveryController::class, 'heartbeat']);
        Route::delete('/{id}', [ServiceDiscoveryController::class, 'deregisterService']);
        Route::get('/{id}', [ServiceDiscoveryController::class, 'getService']);
    });
    
    // Service Discovery
    Route::prefix('discover')->group(function () {
        Route::get('/{name}', [ServiceDiscoveryController::class, 'discoverService']);
        Route::get('/{name}/instances', [ServiceDiscoveryController::class, 'getServiceInstances']);
        Route::get('/{name}/healthy', [ServiceDiscoveryController::class, 'getHealthyInstances']);
    });
    
    // Health Check
    Route::prefix('health')->group(function () {
        Route::get('/', [ServiceDiscoveryController::class, 'healthCheck']);
        Route::get('/services', [ServiceDiscoveryController::class, 'getServicesHealth']);
        Route::post('/check', [ServiceDiscoveryController::class, 'checkServiceHealth']);
    });
    
    // Load Balancer
    Route::prefix('load-balancer')->group(function () {
        Route::get('/{name}', [ServiceDiscoveryController::class, 'getLoadBalancedInstance']);
        Route::get('/{name}/stats', [ServiceDiscoveryController::class, 'getLoadBalancerStats']);
    });
    
    // Service Catalog
    Route::prefix('catalog')->group(function () {
        Route::get('/', [ServiceDiscoveryController::class, 'getServiceCatalog']);
        Route::get('/categories', [ServiceDiscoveryController::class, 'getServiceCategories']);
        Route::get('/{category}', [ServiceDiscoveryController::class, 'getServicesByCategory']);
    });
});
```

### 3. Registra i middleware

Aggiungi al tuo `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // ... middleware esistenti
        \App\Http\Middleware\ServiceDiscoveryMiddleware::class,
    ],
];

protected $routeMiddleware = [
    // ... middleware esistenti
    'service.discovery' => \App\Http\Middleware\ServiceDiscoveryMiddleware::class,
    'service.health' => \App\Http\Middleware\ServiceHealthMiddleware::class,
];
```

### 4. Configura i service provider

Aggiungi al tuo `app/Providers/AppServiceProvider.php`:

```php
use App\Services\ServiceRegistry;
use App\Services\ServiceDiscovery;
use App\Services\HealthCheckService;
use App\Services\LoadBalancerService;
use App\Services\ServiceCatalogService;
use App\Services\HeartbeatService;

public function register()
{
    // Registra i servizi
    $this->app->singleton(ServiceRegistry::class);
    $this->app->singleton(ServiceDiscovery::class);
    $this->app->singleton(HealthCheckService::class);
    $this->app->singleton(LoadBalancerService::class);
    $this->app->singleton(ServiceCatalogService::class);
    $this->app->singleton(HeartbeatService::class);
}
```

### 5. Esegui le migrazioni

```bash
# Crea le tabelle necessarie
php artisan make:migration create_services_table
php artisan make:migration create_service_instances_table
php artisan make:migration create_service_health_table
php artisan make:migration create_service_catalog_table
```

Copia il contenuto delle migrazioni da `database/migrations/` nel tuo progetto.

### 6. Configura Redis

Aggiungi al tuo `.env`:

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 7. Configura le code

Aggiungi al tuo `config/queue.php`:

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

### 8. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Avvia il worker delle code
php artisan queue:work

# Visita la pagina di test
open http://localhost:8000/service-discovery

# Testa via API
curl http://localhost:8000/api/v1/discovery/health

# Esegui i test
php artisan test tests/Feature/ServiceDiscoveryTest.php
```

### 9. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/service-discovery` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/v1/discovery/health`
3. **Test**: Esegui `php artisan test tests/Feature/ServiceDiscoveryTest.php`
4. **Registry**: Verifica che i servizi si registrino correttamente

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern

# Test rapido inline
php -r "require_once 'app/Services/ServiceRegistry.php'; use App\Services\ServiceRegistry; \$s = new ServiceRegistry(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Services/ServiceRegistry.php` - Registry centrale per servizi
- `app/Services/ServiceDiscovery.php` - Servizio per scoperta servizi
- `app/Services/HealthCheckService.php` - Servizio per health check
- `app/Services/LoadBalancerService.php` - Servizio per bilanciamento carico
- `app/Services/ServiceCatalogService.php` - Servizio per catalogo servizi
- `app/Services/HeartbeatService.php` - Servizio per heartbeat
- `app/Http/Controllers/ServiceDiscoveryController.php` - Controller per testare il pattern
- `app/Http/Middleware/ServiceDiscoveryMiddleware.php` - Middleware per discovery
- `app/Http/Middleware/ServiceHealthMiddleware.php` - Middleware per health check
- `app/Models/Service.php` - Modello per servizi
- `app/Models/ServiceInstance.php` - Modello per istanze servizi
- `app/Models/ServiceHealth.php` - Modello per health check
- `app/Models/ServiceCatalog.php` - Modello per catalogo servizi
- `app/Jobs/HealthCheckJob.php` - Job per health check asincrono
- `app/Jobs/HeartbeatJob.php` - Job per heartbeat asincrono
- `resources/views/service-discovery/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/ServiceDiscoveryTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
- Modifica i servizi per le tue esigenze
- Personalizza gli algoritmi di load balancing
- Aggiusta i timeout e intervalli di heartbeat

### Estensione
- Implementa nuovi algoritmi di load balancing
- Aggiungi nuove funzionalità di health check
- Integra con sistemi di monitoring esterni

## Note importanti
- Il pattern fornisce gestione dinamica dei servizi
- Il registry è il punto centrale per la scoperta
- Il health check mantiene i servizi aggiornati
- Il load balancing distribuisce il carico automaticamente
- I file sono pronti per essere copiati in un progetto Laravel esistente
