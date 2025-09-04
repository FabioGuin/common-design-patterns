# Esempio Completo: Singleton Logger

## Descrizione
Implementazione completa di un sistema di logging personalizzato che utilizza il Singleton Pattern in Laravel. Questo esempio dimostra come creare un logger che mantiene una sola istanza per tutta l'applicazione, gestisce diversi livelli di log e persiste i dati su file.

## Obiettivo
- Dimostrare l'implementazione pratica del Singleton Pattern
- Creare un sistema di logging funzionante e completo
- Integrare il pattern con il Service Container di Laravel
- Fornire API RESTful per la gestione dei logs

## Struttura del Progetto

```
01-singleton-logger/
├── README.md                    # Questo file
├── app/
│   ├── Services/
│   │   └── Logger/
│   │       ├── LoggerService.php    # Singleton Logger Service
│   │       ├── LogLevel.php         # Enum per livelli di log
│   │       └── LogEntry.php         # Classe per entry di log
│   ├── Http/
│   │   └── Controllers/
│   │       └── LogController.php    # Controller per API logs
│   └── Providers/
│       └── LoggerServiceProvider.php # Service Provider
├── routes/
│   └── web.php                 # Routes per testing
├── composer.json               # Dipendenze
└── .env.example               # Configurazione ambiente
```

## Caratteristiche Implementate

### Singleton Pattern
- **Una sola istanza** del logger per tutta l'applicazione
- **Lazy initialization** - creazione on-demand
- **Thread-safe** per ambienti multi-threaded
- **Prevenzione clonazione** e deserializzazione

### Gestione Logs
- **5 livelli di log**: DEBUG, INFO, WARNING, ERROR, CRITICAL
- **Persistenza su file** con Storage di Laravel
- **Context support** per metadati aggiuntivi
- **Timestamp automatico** per ogni entry

### Integrazione Laravel
- **Service Provider** per registrazione nel container
- **Service Container** integration
- **Storage facade** per gestione file
- **API RESTful** per consultazione logs

### API Endpoints
- `GET /logs` - Lista tutti i logs
- `GET /logs/level/{level}` - Logs per livello specifico
- `POST /logs` - Crea nuovo log
- `DELETE /logs` - Cancella tutti i logs
- `GET /logs/stats` - Statistiche logs

## Installazione e Utilizzo

### 1. Configurazione Ambiente
```bash
# Copia il file di configurazione
cp .env.example .env

# Installa dipendenze (se necessario)
composer install
```

### 2. Registrazione Service Provider
Aggiungi in `config/app.php`:
```php
'providers' => [
    // ... altri providers
    App\Providers\LoggerServiceProvider::class,
],
```

### 3. Utilizzo Base
```php
use App\Services\Logger\LoggerService;

// Ottenere l'istanza singleton
$logger = LoggerService::getInstance();

// Logging con diversi livelli
$logger->debug('Debug message', ['user_id' => 123]);
$logger->info('User logged in', ['ip' => '192.168.1.1']);
$logger->warning('High memory usage', ['memory' => '512MB']);
$logger->error('Database connection failed', ['error' => 'Connection timeout']);
$logger->critical('System failure', ['component' => 'database']);
```

### 4. Testing API
```bash
# Avvia il server
php artisan serve

# Test endpoints
curl http://localhost:8000/logs
curl http://localhost:8000/logs/level/error
curl -X POST http://localhost:8000/logs -d '{"level":"info","message":"Test message"}'
```

## Esempi di Utilizzo

### Logger Service
```php
<?php

namespace App\Http\Controllers;

use App\Services\Logger\LoggerService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $logger = LoggerService::getInstance();
        
        try {
            // Logica di login
            $user = $this->authenticate($request);
            
            $logger->info('User login successful', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            $logger->error('Login failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
            
            return response()->json(['error' => 'Login failed'], 401);
        }
    }
}
```

### Middleware Integration
```php
<?php

namespace App\Http\Middleware;

use App\Services\Logger\LoggerService;
use Closure;

class RequestLoggingMiddleware
{
    public function handle($request, Closure $next)
    {
        $logger = LoggerService::getInstance();
        
        $logger->info('Request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        $response = $next($request);
        
        $logger->info('Response sent', [
            'status_code' => $response->getStatusCode(),
            'response_time' => microtime(true) - LARAVEL_START
        ]);
        
        return $response;
    }
}
```

## Pattern Implementation Details

### Singleton Implementation
```php
class LoggerService
{
    private static ?LoggerService $instance = null;
    
    private function __construct() {}
    
    public static function getInstance(): LoggerService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Prevenzione clonazione e deserializzazione
    private function __clone() {}
    public function __wakeup() { 
        throw new \Exception("Cannot unserialize singleton"); 
    }
}
```

### Service Container Integration
```php
class LoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoggerService::class, function ($app) {
            return LoggerService::getInstance();
        });
    }
}
```

## Vantaggi del Pattern

1. **Risparmio Memoria**: Una sola istanza per tutta l'applicazione
2. **Consistenza**: Stesso logger in tutta l'applicazione
3. **Facilità d'uso**: Accesso globale controllato
4. **Integrazione**: Perfetta integrazione con Laravel
5. **Testabilità**: Possibilità di mock per i test

## Considerazioni

- **Thread Safety**: In ambienti multi-threaded, considerare sincronizzazione
- **Testing**: Usare dependency injection per i test
- **Memory**: I logs in memoria crescono nel tempo
- **Performance**: Scrittura su file può essere costosa

## Link Utili

- [Singleton Pattern Documentation](../../01-pattern-creazionali/01-singleton/singleton-pattern.md)
- [Laravel Service Container](https://laravel.com/docs/container)
- [Laravel Storage](https://laravel.com/docs/filesystem)

---

*Questo esempio dimostra l'implementazione pratica del Singleton Pattern in un contesto Laravel reale, fornendo un sistema di logging completo e funzionante.*
