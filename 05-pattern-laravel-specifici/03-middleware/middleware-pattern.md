# Middleware Pattern

## Panoramica

Il **Middleware Pattern** è un pattern architetturale che permette di eseguire codice prima e dopo l'elaborazione di una richiesta. In Laravel, i middleware forniscono un meccanismo conveniente per filtrare le richieste HTTP che entrano nell'applicazione, permettendo di gestire autenticazione, autorizzazione, logging, e altre funzionalità trasversali.

## Problema Risolto

### Problemi Comuni
- **Codice duplicato**: Logica ripetuta in ogni controller
- **Preoccupazioni trasversali**: Autenticazione, logging, caching sparse
- **Gestione errori**: Handling degli errori non centralizzato
- **Sicurezza**: Validazione e sanitizzazione disorganizzate

### Esempi di Problemi
```pseudocodice
// Problema: Codice duplicato in ogni controller
class UserController {
    function index() {
        // Controllo autenticazione
        if (!auth()->check()) {
            return redirect('/login');
        }
        
        // Controllo autorizzazione
        if (!auth()->user()->can('view-users')) {
            abort(403);
        }
        
        // Logging
        Log::info('User accessed users list');
        
        // Logica business
        return view('users.index');
    }
}

class PostController {
    function index() {
        // Stesso codice duplicato
        if (!auth()->check()) {
            return redirect('/login');
        }
        // ...
    }
}
```

## Soluzione

### Architettura del Pattern

```pseudocodice
// Struttura base Middleware
class Middleware {
    function handle(request, next) {
        // Pre-processing
        $this->before($request);
        
        // Chiama il prossimo middleware
        $response = $next($request);
        
        // Post-processing
        $this->after($request, $response);
        
        return $response;
    }
}
```

### Componenti Principali

1. **Middleware Base**
   - Interfaccia comune per tutti i middleware
   - Metodo `handle()` per elaborazione
   - Gestione del pipeline

2. **Pipeline di Middleware**
   - Esecuzione sequenziale
   - Passaggio del controllo
   - Gestione delle eccezioni

3. **Registrazione e Binding**
   - Registrazione globale
   - Binding per route specifiche
   - Gruppi di middleware

## Vantaggi

### 1. **Separazione delle Responsabilità**
- Logica trasversale separata
- Controller focalizzati sul business
- Codice più pulito e manutenibile

### 2. **Riusabilità**
- Middleware riutilizzabili
- Composizione flessibile
- Configurazione dinamica

### 3. **Manutenibilità**
- Modifiche centralizzate
- Testing isolato
- Debugging semplificato

### 4. **Performance**
- Esecuzione ottimizzata
- Caching delle risposte
- Terminazione anticipata

## Svantaggi

### 1. **Complessità**
- Curva di apprendimento
- Debugging del pipeline
- Gestione degli errori

### 2. **Performance**
- Overhead del pipeline
- Esecuzione sequenziale
- Memoria aggiuntiva

## Caso d'Uso

### Scenario: Sistema di Blog
```pseudocodice
// Pipeline di middleware per blog
Request -> AuthMiddleware -> LogMiddleware -> CacheMiddleware -> Controller

// AuthMiddleware
class AuthMiddleware {
    function handle(request, next) {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return next(request);
    }
}

// LogMiddleware
class LogMiddleware {
    function handle(request, next) {
        Log::info('Request started', [request->url()]);
        $response = next(request);
        Log::info('Request completed', [response->status()]);
        return response;
    }
}
```

## Implementazione Laravel

### 1. **Creazione Middleware**

```bash
php artisan make:middleware AuthMiddleware
```

### 2. **Implementazione Base**

```pseudocodice
class AuthMiddleware {
    function handle(request, next) {
        if (!auth().check()) {
            return redirect('/login')
        }
        
        return next(request)
    }
}
```

### 3. **Registrazione Middleware**

```pseudocodice
// Kernel.php
protected middleware = [
    TrustProxies::class,
    HandleCors::class,
    // ...
]

protected middlewareGroups = [
    'web' => [
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        // ...
    ],
    'api' => [
        'throttle:api',
        SubstituteBindings::class,
    ],
]

protected routeMiddleware = [
    'auth' => Authenticate::class,
    'auth.basic' => AuthenticateWithBasicAuth::class,
    'cache.headers' => SetCacheHeaders::class,
    'can' => Authorize::class,
    'guest' => RedirectIfAuthenticated::class,
    'password.confirm' => RequirePassword::class,
    'signed' => ValidateSignature::class,
    'throttle' => ThrottleRequests::class,
    'verified' => EnsureEmailIsVerified::class,
]
```

### 4. **Utilizzo nei Controller**

```pseudocodice
class PostController extends Controller {
    constructor() {
        this.middleware('auth')
        this.middleware('can:view-posts').only('index')
        this.middleware('can:create-posts').only('create', 'store')
    }
}
```

## Esempi Pratici

### 1. **Middleware di Autenticazione**

```pseudocodice
class AuthMiddleware {
    function handle(request, next, guard = null) {
        if (!auth(guard).check()) {
            if (request.expectsJson()) {
                return response().json(['message' => 'Unauthenticated.'], 401)
            }
            return redirect().guest(route('login'))
        }
        
        return next(request)
    }
}
```

### 2. **Middleware di Autorizzazione**

```pseudocodice
class RoleMiddleware {
    function handle(request, next, role) {
        if (!auth().user().hasRole(role)) {
            abort(403, 'Insufficient permissions.')
        }
        
        return next(request)
    }
}
```

### 3. **Middleware di Logging**

```pseudocodice
class LogMiddleware {
    function handle(request, next) {
        startTime = microtime(true)
        
        response = next(request)
        
        duration = microtime(true) - startTime
        
        Log.info('Request processed', [
            'method' => request.method(),
            'url' => request.fullUrl(),
            'status' => response.getStatusCode(),
            'duration' => duration,
        ])
        
        return response
    }
}
```

### 4. **Middleware di Cache**

```pseudocodice
class CacheMiddleware {
    function handle(request, next, ttl = 60) {
        key = 'cache:' + md5(request.fullUrl())
        
        if (Cache.has(key)) {
            return Cache.get(key)
        }
        
        response = next(request)
        
        if (response.getStatusCode() === 200) {
            Cache.put(key, response, ttl)
        }
        
        return response
    }
}
```

### 5. **Middleware di Rate Limiting**

```pseudocodice
class RateLimitMiddleware {
    function handle(request, next, maxAttempts = 60) {
        key = this.resolveRequestSignature(request)
        
        if (RateLimiter.tooManyAttempts(key, maxAttempts)) {
            return response().json([
                'message' => 'Too many requests.',
                'retry_after' => RateLimiter.availableIn(key)
            ], 429)
        }
        
        RateLimiter.hit(key, 60)
        
        response = next(request)
        
        return this.addHeaders(response, maxAttempts, key)
    }
}
```

## Middleware Avanzati

### 1. **Middleware Parametrizzato**

```pseudocodice
class FeatureToggleMiddleware {
    function handle(request, next, feature) {
        if (!config("features.{feature}", false)) {
            abort(404, 'Feature not available.')
        }
        
        return next(request)
    }
}

// Utilizzo
Route.middleware('feature:new-dashboard').group(function() {
    Route.get('/dashboard', [DashboardController::class, 'index'])
})
```

### 2. **Middleware Condizionale**

```pseudocodice
class ConditionalMiddleware {
    function handle(request, next) {
        if (this.shouldRun(request)) {
            return next(request)
        }
        
        // Salta il middleware
        return next(request)
    }
    
    protected function shouldRun(request): Boolean {
        return request.is('admin/*') && !request.user().isAdmin()
    }
}
```

### 3. **Middleware di Trasformazione**

```pseudocodice
class ResponseTransformMiddleware {
    function handle(request, next) {
        response = next(request)
        
        if (request.expectsJson() && response.getStatusCode() === 200) {
            data = response.getData(true)
            transformed = this.transformData(data)
            response.setData(transformed)
        }
        
        return response
    }
}
```

## Best Practices

### 1. **Organizzazione**
- Un middleware per una responsabilità
- Nomi descrittivi e consistenti
- Documentazione chiara

### 2. **Performance**
- Evita operazioni pesanti
- Usa cache quando appropriato
- Termina il pipeline quando necessario

### 3. **Testing**
- Testa i middleware isolatamente
- Mock delle dipendenze
- Verifica del comportamento

### 4. **Sicurezza**
- Valida sempre gli input
- Sanitizza i dati
- Gestisci gli errori appropriatamente

## Pattern Correlati

- **Chain of Responsibility**: Pipeline di elaborazione
- **Decorator Pattern**: Aggiunta di funzionalità
- **Proxy Pattern**: Controllo dell'accesso
- **Observer Pattern**: Notifiche e logging

## Conclusione

Il Middleware Pattern è essenziale in Laravel per gestire le preoccupazioni trasversali in modo pulito e organizzato. Fornisce un meccanismo potente per filtrare, trasformare e monitorare le richieste HTTP, migliorando la manutenibilità e la sicurezza dell'applicazione.

La chiave per un uso efficace è mantenere i middleware focalizzati, ben testati e ottimizzati per le performance, seguendo le best practices di Laravel.
