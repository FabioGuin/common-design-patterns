# Service Container Pattern - Esempio Completo

## Descrizione

Questo esempio dimostra l'implementazione del pattern Service Container in Laravel attraverso un sistema blog che gestisce l'iniezione delle dipendenze e la risoluzione automatica dei servizi.

## Caratteristiche

- **Service Container Pattern**: Gestione automatica delle dipendenze
- **Dependency Injection**: Iniezione automatica delle dipendenze
- **Service Registration**: Registrazione centralizzata dei servizi
- **Singleton Management**: Gestione del ciclo di vita degli oggetti
- **Lazy Loading**: Creazione on-demand dei servizi
- **Testing**: Test con mock e stubbing

## Struttura del Progetto

```
app/
├── Services/
│   ├── UserService.php
│   ├── ArticleService.php
│   ├── EmailService.php
│   └── CacheService.php
├── Repositories/
│   ├── UserRepository.php
│   └── ArticleRepository.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── BlogServiceProvider.php
├── Http/Controllers/
│   ├── UserController.php
│   └── ArticleController.php
└── Models/
    ├── User.php
    └── Article.php
```

## Installazione

1. **Clona il repository**:
   ```bash
   git clone [repository-url]
   cd service-container-blog-example
   ```

2. **Installa le dipendenze**:
   ```bash
   composer install
   ```

3. **Configura l'ambiente**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configura il database**:
   - Crea un database MySQL
   - Aggiorna le credenziali in `.env`
   - Esegui le migrazioni:
     ```bash
     php artisan migrate
     ```

5. **Avvia il server**:
   ```bash
   php artisan serve
   ```

6. **Visita l'applicazione**:
   - Apri `http://localhost:8000` nel browser
   - Esplora le funzionalità del blog

## Funzionalità Implementate

### Service Container Pattern
- **Service Registration**: Registrazione centralizzata dei servizi
- **Dependency Injection**: Iniezione automatica delle dipendenze
- **Singleton Management**: Gestione del ciclo di vita
- **Lazy Loading**: Creazione on-demand
- **Service Resolution**: Risoluzione automatica dei servizi

### Dependency Management
- **Constructor Injection**: Iniezione tramite costruttore
- **Method Injection**: Iniezione tramite metodi
- **Interface Binding**: Binding di interfacce
- **Closure Binding**: Binding di closure
- **Instance Binding**: Binding di istanze

### Service Lifecycle
- **Singleton**: Una sola istanza per richiesta
- **Transient**: Nuova istanza ogni volta
- **Scoped**: Istanza per scope specifico
- **Lazy**: Creazione on-demand

## Pattern Service Container in Azione

### 1. Service Registration
```php
// app/Providers/BlogServiceProvider.php
class BlogServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind interface to implementation
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        
        // Singleton services
        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService($app->make('config'));
        });
        
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService($app->make('cache'));
        });
        
        // Service with dependencies
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepositoryInterface::class),
                $app->make(EmailService::class),
                $app->make(CacheService::class)
            );
        });
    }
}
```

### 2. Service with Dependencies
```php
// app/Services/UserService.php
class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailService $emailService,
        private CacheService $cacheService
    ) {}

    public function createUser(array $data): User
    {
        $user = $this->userRepository->create($data);
        
        $this->emailService->sendWelcomeEmail($user);
        $this->cacheService->forget('users.list');
        
        return $user;
    }
}
```

### 3. Controller with Dependency Injection
```php
// app/Http/Controllers/UserController.php
class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function store(Request $request)
    {
        $user = $this->userService->createUser($request->validated());
        
        return response()->json($user, 201);
    }
}
```

### 4. Service Resolution
```php
// Manual resolution
$userService = app(UserService::class);

// From container
$userService = $this->app->make(UserService::class);

// With parameters
$userService = app(UserService::class, ['param' => 'value']);

// Check if bound
if (app()->bound(UserService::class)) {
    $userService = app(UserService::class);
}
```

### 5. Service Provider Registration
```php
// config/app.php
'providers' => [
    // ...
    App\Providers\BlogServiceProvider::class,
],
```

## Vantaggi del Pattern Service Container

1. **Disaccoppiamento**: Classi indipendenti dalle implementazioni
2. **Testabilità**: Facile sostituire dipendenze nei test
3. **Flessibilità**: Cambio implementazioni senza modificare codice
4. **Singleton**: Gestione automatica del ciclo di vita
5. **Lazy Loading**: Creazione on-demand
6. **Configurazione centralizzata**: Tutto in un posto
7. **Iniezione automatica**: Dipendenze risolte automaticamente

## Best Practices Implementate

- **Interface Segregation**: Uso di interfacce per il binding
- **Dependency Inversion**: Dipendenza da astrazioni
- **Single Responsibility**: Ogni servizio ha una responsabilità
- **Lazy Loading**: Creazione on-demand dei servizi
- **Singleton Pattern**: Gestione del ciclo di vita
- **Service Providers**: Registrazione centralizzata

## Testing

Per testare l'implementazione:

1. **Test Service**:
   ```bash
   php artisan test --filter=UserServiceTest
   ```

2. **Test Controller**:
   ```bash
   php artisan test --filter=UserControllerTest
   ```

3. **Test Container**:
   ```bash
   php artisan test --filter=ServiceContainerTest
   ```

## Estensioni Possibili

- **Service Discovery**: Scoperta automatica dei servizi
- **Service Decorators**: Decoratori per i servizi
- **Service Factories**: Factory per la creazione di servizi
- **Service Proxies**: Proxy per i servizi
- **Service Events**: Eventi per i servizi

## Conclusione

Questo esempio dimostra come il pattern Service Container gestisce l'iniezione delle dipendenze in modo automatico. Il disaccoppiamento e la testabilità rendono l'applicazione più manutenibile e flessibile.

Il pattern Service Container è particolarmente utile per applicazioni complesse che richiedono gestione avanzata delle dipendenze e testabilità.
