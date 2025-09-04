# Logger Singleton Completo

## Cosa fa
Un sistema di logging personalizzato che usa il Singleton Pattern in Laravel. Il logger mantiene una sola istanza per tutta l'app, gestisce diversi livelli di log e salva tutto su file.

## Perché è utile
- Mostra come implementare il Singleton Pattern nella pratica
- Ti dà un sistema di logging funzionante e completo
- Si integra perfettamente con il Service Container di Laravel
- Include API RESTful per gestire i log

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

## Cosa include

### Singleton Pattern
- Una sola istanza del logger per tutta l'app
- Si crea solo quando serve (lazy initialization)
- Funziona anche in ambienti multi-threaded
- Non può essere clonato o deserializzato

### Gestione dei Log
- 5 livelli di log: DEBUG, INFO, WARNING, ERROR, CRITICAL
- Salvataggio su file usando Storage di Laravel
- Supporto per metadati aggiuntivi (context)
- Timestamp automatico per ogni log

### Integrazione con Laravel
- Service Provider per registrarlo nel container
- Integrazione con il Service Container
- Usa Storage facade per gestire i file
- API RESTful per consultare i log

### Endpoint API
- `GET /logs` - Lista tutti i log
- `GET /logs/level/{level}` - Log per livello specifico
- `POST /logs` - Crea nuovo log
- `DELETE /logs` - Cancella tutti i log
- `GET /logs/stats` - Statistiche dei log

## Come installarlo e usarlo

### 1. Configura l'ambiente
```bash
# Copia il file di configurazione
cp .env.example .env

# Installa le dipendenze (se necessario)
composer install
```

### 2. Registra il Service Provider
Aggiungi in `config/app.php`:
```php
'providers' => [
    // ... altri providers
    App\Providers\LoggerServiceProvider::class,
],
```

### 3. Usalo nel tuo codice
```php
use App\Services\Logger\LoggerService;

// Ottieni l'istanza singleton
$logger = LoggerService::getInstance();

// Log con diversi livelli
$logger->debug('Messaggio di debug', ['user_id' => 123]);
$logger->info('Utente loggato', ['ip' => '192.168.1.1']);
$logger->warning('Uso memoria alto', ['memory' => '512MB']);
$logger->error('Connessione database fallita', ['error' => 'Connection timeout']);
$logger->critical('Errore di sistema', ['component' => 'database']);
```

### 4. Testa le API
```bash
# Avvia il server
php artisan serve

# Testa gli endpoint
curl http://localhost:8000/logs
curl http://localhost:8000/logs/level/error
curl -X POST http://localhost:8000/logs -d '{"level":"info","message":"Messaggio di test"}'
```

## Esempi pratici

### Nel Controller
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

### Nel Middleware
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

## Dettagli dell'implementazione

### Come funziona il Singleton
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

### Integrazione con il Service Container
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

## Vantaggi del pattern

1. **Risparmio memoria**: Una sola istanza per tutta l'app
2. **Consistenza**: Stesso logger ovunque nell'app
3. **Facilità d'uso**: Accesso globale controllato
4. **Integrazione**: Perfetta integrazione con Laravel
5. **Testabilità**: Puoi fare mock per i test

## Cose da considerare

- **Thread Safety**: In ambienti multi-threaded, considera la sincronizzazione
- **Testing**: Usa dependency injection per i test
- **Memoria**: I log in memoria crescono nel tempo
- **Performance**: Scrivere su file può essere costoso

## Link utili

- [Documentazione Singleton Pattern](../../01-pattern-creazionali/01-singleton/singleton-pattern.md)
- [Laravel Service Container](https://laravel.com/docs/container)
- [Laravel Storage](https://laravel.com/docs/filesystem)

---

*Questo esempio ti mostra come implementare il Singleton Pattern in un progetto Laravel reale, con un sistema di logging completo e funzionante.*
