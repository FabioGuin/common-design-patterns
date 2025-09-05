# Load Balancer Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Load Balancer in Laravel attraverso un sistema di distribuzione del carico tra multiple istanze. L'esempio include:

- **LoadBalancerService** che gestisce la distribuzione delle richieste
- **ServerController** che simula multiple istanze dell'applicazione
- **HealthChecker** che monitora lo stato dei server
- **Algoritmi di distribuzione** (Round Robin, Least Connections, Weighted)

## Come funziona l'esempio
Il pattern Load Balancer gestisce:
- **Distribuzione intelligente** delle richieste tra server
- **Health checking** automatico per monitorare i server
- **Algoritmi di selezione** per ottimizzare la distribuzione
- **Gestione fallimenti** con failover automatico
- **Monitoraggio** delle performance e del carico

Quando testi l'esempio, vedrai che:
1. Le richieste vengono distribuite tra i server disponibili
2. I server vengono monitorati continuamente
3. I server non disponibili vengono esclusi automaticamente
4. La distribuzione avviene secondo l'algoritmo configurato
5. Le performance vengono monitorate e ottimizzate

## Caratteristiche tecniche
- Simulazione di multiple istanze Laravel
- Algoritmi di load balancing configurabili
- Health checking automatico
- Dashboard web per monitorare il sistema
- API per testare la distribuzione del carico
- Configurazione Nginx per load balancing reale

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **Nginx** (opzionale, per load balancing reale)
- **Redis** (opzionale, per sessioni condivise)

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Services/LoadBalancerService.php app/Services/
cp /path/to/this/example/app/Services/HealthCheckerService.php app/Services/
cp /path/to/this/example/app/Http/Controllers/ServerController.php app/Http/Controllers/
cp /path/to/this/example/app/Http/Controllers/LoadBalancerController.php app/Http/Controllers/
mkdir -p resources/views/load-balancer
cp /path/to/this/example/resources/views/load-balancer/example.blade.php resources/views/load-balancer/
cp /path/to/this/example/tests/Feature/LoadBalancerTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\LoadBalancerController;
use App\Http\Controllers\ServerController;

// Route per il pattern Load Balancer
Route::get('/load-balancer', [LoadBalancerController::class, 'index']);
Route::get('/load-balancer/test', [LoadBalancerController::class, 'test']);

// Route API
Route::prefix('api/load-balancer')->group(function () {
    Route::get('/', [LoadBalancerController::class, 'index']);
    Route::post('/test', [LoadBalancerController::class, 'test']);
    Route::get('/servers', [LoadBalancerController::class, 'servers']);
    Route::get('/health', [LoadBalancerController::class, 'health']);
    Route::post('/add-server', [LoadBalancerController::class, 'addServer']);
    Route::delete('/remove-server/{id}', [LoadBalancerController::class, 'removeServer']);
    Route::post('/set-algorithm', [LoadBalancerController::class, 'setAlgorithm']);
});

// Route per simulare i server
Route::prefix('server')->group(function () {
    Route::get('/{id}', [ServerController::class, 'handle']);
    Route::get('/{id}/health', [ServerController::class, 'health']);
    Route::get('/{id}/stats', [ServerController::class, 'stats']);
});
```

### 3. Configura i servizi

Aggiungi al tuo `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\LoadBalancerServiceProvider::class,
],
```

### 4. Configura Nginx (opzionale)

Crea un file di configurazione Nginx per load balancing reale:

```nginx
upstream laravel_backend {
    server 127.0.0.1:8001;
    server 127.0.0.1:8002;
    server 127.0.0.1:8003;
}

server {
    listen 80;
    server_name localhost;

    location / {
        proxy_pass http://laravel_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

### 5. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve --port=8001

# In un altro terminale, avvia altre istanze
php artisan serve --port=8002
php artisan serve --port=8003

# Visita la pagina di test
open http://localhost:8001/load-balancer

# Testa via API
curl http://localhost:8001/api/load-balancer/test

# Esegui i test
php artisan test tests/Feature/LoadBalancerTest.php
```

### 6. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8001/load-balancer` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8001/api/load-balancer/test`
3. **Test**: Esegui `php artisan test tests/Feature/LoadBalancerTest.php`
4. **Load Balancing**: Verifica che le richieste vengano distribuite tra i server

Se tutto funziona, l'integrazione è completata! 🎉

## Test standalone (senza Laravel)

Se vuoi testare solo il pattern senza Laravel:

```bash
# Test completo del pattern

# Test rapido inline
php -r "require_once 'app/Services/LoadBalancerService.php'; use App\Services\LoadBalancerService; \$s = new LoadBalancerService(); echo 'Pattern ID: ' . \$s->getId();"
```

## File inclusi

- `app/Services/LoadBalancerService.php` - Service che gestisce il pattern
- `app/Services/HealthCheckerService.php` - Service per health checking
- `app/Http/Controllers/LoadBalancerController.php` - Controller per testare il pattern
- `app/Http/Controllers/ServerController.php` - Controller per simulare i server
- `resources/views/load-balancer/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/LoadBalancerTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto
- `nginx.conf` - Configurazione Nginx per load balancing reale

## Personalizzazione

### Configurazione
- Modifica gli algoritmi di distribuzione
- Personalizza i parametri di health checking
- Aggiungi nuovi server dinamicamente
- Configura i timeout e i retry

### Estensione
- Implementa algoritmi di distribuzione personalizzati
- Aggiungi metriche di performance
- Integra con sistemi di monitoring
- Implementa sessioni condivise

## Note importanti
- Il pattern migliora significativamente la disponibilità e le performance
- È necessario configurare correttamente gli health check
- I server devono essere stateless per una distribuzione ottimale
- I file sono pronti per essere copiati in un progetto Laravel esistente
