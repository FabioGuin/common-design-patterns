# Middleware Pattern - Esempio Completo

## Panoramica

Questo esempio dimostra l'implementazione del **Middleware Pattern** in Laravel, mostrando come creare e utilizzare middleware per gestire autenticazione, autorizzazione, logging, caching e altre funzionalità trasversali.

## Struttura del Progetto

```
esempio-completo/
├── app/
│   ├── Http/
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php
│   │   │   ├── RoleMiddleware.php
│   │   │   ├── LogMiddleware.php
│   │   │   ├── CacheMiddleware.php
│   │   │   ├── RateLimitMiddleware.php
│   │   │   ├── FeatureToggleMiddleware.php
│   │   │   ├── ResponseTransformMiddleware.php
│   │   │   └── SecurityHeadersMiddleware.php
│   │   └── Controllers/
│   │       ├── BlogController.php
│   │       ├── AdminController.php
│   │       └── ApiController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Post.php
│   │   └── Role.php
│   └── Services/
│       ├── LogService.php
│       ├── CacheService.php
│       └── SecurityService.php
├── config/
│   ├── middleware.php
│   └── features.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── middleware/
│       │   ├── demo.blade.php
│       │   └── test.blade.php
│       └── blog/
│           ├── index.blade.php
│           └── show.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── composer.json
└── env.example
```

## Installazione

1. **Clona il repository**
```bash
git clone <repository-url>
cd esempio-completo
```

2. **Installa le dipendenze**
```bash
composer install
```

3. **Configura l'ambiente**
```bash
cp env.example .env
php artisan key:generate
```

4. **Configura il database**
```bash
php artisan migrate
```

5. **Avvia il server**
```bash
php artisan serve
```

## Middleware Implementati

### 1. **AuthMiddleware**
- Controllo autenticazione utente
- Redirect per utenti non autenticati
- Supporto per API e web

### 2. **RoleMiddleware**
- Controllo autorizzazione basato su ruoli
- Supporto per ruoli multipli
- Gestione errori personalizzata

### 3. **LogMiddleware**
- Logging delle richieste HTTP
- Metriche di performance
- Filtri per URL specifiche

### 4. **CacheMiddleware**
- Caching delle risposte
- TTL configurabile
- Invalida cache automatica

### 5. **RateLimitMiddleware**
- Rate limiting per API
- Gestione tentativi falliti
- Headers informativi

### 6. **FeatureToggleMiddleware**
- Controllo feature flag
- Configurazione dinamica
- Fallback graceful

### 7. **ResponseTransformMiddleware**
- Trasformazione risposte API
- Formattazione standardizzata
- Metadata aggiuntivi

### 8. **SecurityHeadersMiddleware**
- Headers di sicurezza
- Protezione XSS e CSRF
- Configurazione CSP

## Utilizzo

### Registrazione Middleware

```php
// Kernel.php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\LogMiddleware::class,
        \App\Http\Middleware\SecurityHeadersMiddleware::class,
    ],
    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\LogMiddleware::class,
        \App\Http\Middleware\ResponseTransformMiddleware::class,
    ],
];

protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\AuthMiddleware::class,
    'role' => \App\Http\Middleware\RoleMiddleware::class,
    'cache' => \App\Http\Middleware\CacheMiddleware::class,
    'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
    'feature' => \App\Http\Middleware\FeatureToggleMiddleware::class,
];
```

### Utilizzo nei Controller

```php
class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:editor')->only(['create', 'store', 'edit', 'update']);
        $this->middleware('cache:300')->only(['index', 'show']);
    }
}
```

### Utilizzo nelle Route

```php
// Middleware per route specifiche
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Middleware con parametri
Route::middleware(['feature:new-dashboard'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Middleware per API
Route::middleware(['api', 'rate.limit:100'])->group(function () {
    Route::get('/api/posts', [ApiController::class, 'posts']);
});
```

## Configurazione

### Middleware Groups

```php
// config/middleware.php
return [
    'groups' => [
        'web' => [
            'middleware' => [
                \App\Http\Middleware\EncryptCookies::class,
                \App\Http\Middleware\LogMiddleware::class,
            ],
        ],
        'api' => [
            'middleware' => [
                'throttle:api',
                \App\Http\Middleware\ResponseTransformMiddleware::class,
            ],
        ],
    ],
];
```

### Feature Flags

```php
// config/features.php
return [
    'new-dashboard' => env('FEATURE_NEW_DASHBOARD', false),
    'advanced-search' => env('FEATURE_ADVANCED_SEARCH', true),
    'api-v2' => env('FEATURE_API_V2', false),
];
```

## Test

### Test Middleware

```bash
# Test unitari
php artisan test

# Test specifici middleware
php artisan test --filter=AuthMiddlewareTest
php artisan test --filter=RoleMiddlewareTest
```

### Test Manuali

```bash
# Test autenticazione
curl -H "Accept: application/json" http://localhost:8000/api/posts

# Test rate limiting
for i in {1..10}; do curl http://localhost:8000/api/test; done

# Test feature toggle
curl http://localhost:8000/dashboard
```

## Esempi Pratici

### 1. **Middleware di Autenticazione**

```php
class AuthMiddleware
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (!auth($guard)->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('login'));
        }
        
        return $next($request);
    }
}
```

### 2. **Middleware di Cache**

```php
class CacheMiddleware
{
    public function handle(Request $request, Closure $next, $ttl = 60)
    {
        $key = 'cache:' . md5($request->fullUrl());
        
        if (Cache::has($key)) {
            return Cache::get($key);
        }
        
        $response = $next($request);
        
        if ($response->getStatusCode() === 200) {
            Cache::put($key, $response, $ttl);
        }
        
        return $response;
    }
}
```

### 3. **Middleware di Rate Limiting**

```php
class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next, $maxAttempts = 60)
    {
        $key = $this->resolveRequestSignature($request);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }
        
        RateLimiter::hit($key, 60);
        
        $response = $next($request);
        
        return $this->addHeaders($response, $maxAttempts, $key);
    }
}
```

## Debugging

### Log Middleware

```php
// Abilita logging dettagliato
Log::info('Middleware executed', [
    'middleware' => get_class($this),
    'request' => $request->fullUrl(),
    'user' => auth()->id(),
]);
```

### Debug Pipeline

```php
// Aggiungi debug al pipeline
Route::middleware(['debug', 'auth', 'role:admin'])->group(function () {
    // Route protette
});
```

## Performance

### Ottimizzazioni

1. **Middleware Ordering**: Ordina i middleware per performance
2. **Caching**: Usa cache per middleware pesanti
3. **Early Return**: Termina il pipeline quando possibile
4. **Lazy Loading**: Carica middleware solo quando necessario

### Monitoring

```php
// Monitora performance middleware
$startTime = microtime(true);
$response = $next($request);
$duration = microtime(true) - $startTime;

if ($duration > 0.1) { // > 100ms
    Log::warning('Slow middleware', [
        'middleware' => get_class($this),
        'duration' => $duration,
    ]);
}
```

## Conclusione

Questo esempio dimostra come utilizzare efficacemente il Middleware Pattern in Laravel per gestire le preoccupazioni trasversali in modo pulito e organizzato. Il pattern fornisce un meccanismo potente per filtrare, trasformare e monitorare le richieste HTTP, migliorando la manutenibilità e la sicurezza dell'applicazione.
