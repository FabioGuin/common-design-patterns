# SOLID Principles

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Esempi di codice](#esempi-di-codice)
- [Correlati](#correlati)
- [Risorse utili](#risorse-utili)

## Cosa fa

I principi SOLID sono cinque principi fondamentali della programmazione orientata agli oggetti che rendono il software più comprensibile, flessibile e manutenibile. Acronimo di:

- **S** - Single Responsibility Principle (SRP) - Una classe, una responsabilità
- **O** - Open/Closed Principle (OCP) - Aperto per estensione, chiuso per modifica
- **L** - Liskov Substitution Principle (LSP) - Sostituibilità delle sottoclassi
- **I** - Interface Segregation Principle (ISP) - Interfacce specifiche e coese
- **D** - Dependency Inversion Principle (DIP) - Dipendenza da astrazioni

Questi principi, formulati da Robert C. Martin, rappresentano la base per scrivere codice pulito, testabile e manutenibile in Laravel e in qualsiasi linguaggio OOP.

## Perché ti serve

Senza i principi SOLID, il codice Laravel diventa:
- **Monolitico**: Controller con centinaia di righe
- **Accoppiato**: Modifiche in un punto rompono tutto
- **Fragile**: Difficile da testare e debuggare
- **Rigido**: Impossibile estendere senza modificare
- **Duplicato**: Stessa logica sparsa ovunque

Con i principi SOLID, il codice Laravel diventa:
- **Modulare**: Ogni classe ha una responsabilità chiara
- **Flessibile**: Facile da estendere e modificare
- **Testabile**: Ogni componente può essere testato isolatamente
- **Riutilizzabile**: Componenti che funzionano in contesti diversi
- **Manutenibile**: Modifiche localizzate e sicure

## Come funziona

### Single Responsibility Principle (SRP)
Una classe dovrebbe avere una sola ragione per cambiare. In Laravel significa separare:
- **Controller**: Solo gestione HTTP
- **Service**: Logica business
- **Repository**: Accesso ai dati
- **Model**: Rappresentazione dei dati

### Open/Closed Principle (OCP)
Le entità dovrebbero essere aperte per l'estensione ma chiuse per la modifica. In Laravel:
- Usa **interfacce** per definire contratti
- Implementa **strategy pattern** per algoritmi
- Usa **service container** per dependency injection

### Liskov Substitution Principle (LSP)
Gli oggetti di una superclasse dovrebbero essere sostituibili con oggetti delle sue sottoclassi. In Laravel:
- Le **sottoclassi** devono rispettare il contratto della **superclasse**
- I **polimorfismi** devono funzionare correttamente
- Le **eccezioni** devono essere gestite appropriatamente

### Interface Segregation Principle (ISP)
I client non dovrebbero dipendere da interfacce che non usano. In Laravel:
- Crea **interfacce specifiche** per ogni responsabilità
- Evita **interfacce troppo grandi**
- Usa **contracts Laravel** per definire interfacce

### Dependency Inversion Principle (DIP)
Dependi da astrazioni, non da concrezioni. In Laravel:
- Usa **dependency injection** nel service container
- Dipendi da **interfacce**, non da classi concrete
- Usa **service providers** per registrare dipendenze

## Quando usarlo

Usa i principi SOLID quando:
- **Stai progettando** un nuovo sistema Laravel
- **Il codice esistente** è troppo accoppiato
- **Vuoi migliorare** la testabilità
- **Devi riutilizzare** componenti
- **Il sistema sta crescendo** in complessità
- **Stai facendo refactoring** di codice legacy

**NON usarlo quando:**
- **Il progetto è molto semplice** (prototipi rapidi)
- **Stai facendo** prototipi rapidi
- **La separazione aggiunge** complessità inutile
- **Il team non è pronto** per l'architettura
- **Stai lavorando** con codice temporaneo

## Pro e contro

**I vantaggi:**
- **Codice più modulare** e organizzato
- **Facile da testare** e debuggare
- **Componenti riutilizzabili** in contesti diversi
- **Manutenzione semplificata** e localizzata
- **Team può lavorare** in parallelo su moduli diversi
- **Estensibilità** senza modifiche esistenti

**Gli svantaggi:**
- **Può aggiungere complessità** iniziale
- **Richiede più file** e classi
- **Curva di apprendimento** per il team
- **Possibile over-engineering** per progetti semplici
- **Richiede disciplina** costante nel team

## Esempi di codice

### Single Responsibility Principle (SRP)

```php
//  Violazione SRP - Controller con troppe responsabilità
class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validazione (responsabilità 1)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        
        // Creazione utente (responsabilità 2)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        
        // Invio email (responsabilità 3)
        Mail::to($user->email)->send(new WelcomeEmail($user));
        
        // Log (responsabilità 4)
        Log::info('User created', ['user_id' => $user->id]);
        
        // Serializzazione (responsabilità 5)
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->format('Y-m-d H:i:s')
        ]);
    }
}

//  Rispetto SRP - Responsabilità separate
class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private UserTransformer $transformer
    ) {}
    
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());
        return response()->json($this->transformer->transform($user));
    }
}

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserNotifier $notifier,
        private UserLogger $logger
    ) {}
    
    public function createUser(array $data): User
    {
        $user = $this->userRepository->create($data);
        $this->notifier->sendWelcomeEmail($user);
        $this->logger->logUserCreation($user);
        return $user;
    }
}

class UserRepository
{
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }
}

class UserNotifier
{
    public function sendWelcomeEmail(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeEmail($user));
    }
}

class UserLogger
{
    public function logUserCreation(User $user): void
    {
        Log::info('User created', ['user_id' => $user->id]);
    }
}

class UserTransformer
{
    public function transform(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->format('Y-m-d H:i:s')
        ];
    }
}
```

### Open/Closed Principle (OCP)

```php
//  Violazione OCP - Modifica per aggiungere funzionalità
class PaymentProcessor
{
    public function process(Payment $payment): PaymentResult
    {
        switch ($payment->method) {
            case 'credit_card':
                return $this->processCreditCard($payment);
            case 'paypal':
                return $this->processPayPal($payment);
            case 'stripe':
                return $this->processStripe($payment);
            default:
                throw new InvalidArgumentException('Unsupported payment method');
        }
    }
    
    private function processCreditCard(Payment $payment): PaymentResult
    {
        // Logica per carta di credito
    }
    
    private function processPayPal(Payment $payment): PaymentResult
    {
        // Logica per PayPal
    }
    
    private function processStripe(Payment $payment): PaymentResult
    {
        // Logica per Stripe
    }
}

//  Rispetto OCP - Estensione senza modifica
interface PaymentMethod
{
    public function process(Payment $payment): PaymentResult;
}

class CreditCardPayment implements PaymentMethod
{
    public function process(Payment $payment): PaymentResult
    {
        // Logica per carta di credito
        return new PaymentResult(true, 'Credit card payment processed');
    }
}

class PayPalPayment implements PaymentMethod
{
    public function process(Payment $payment): PaymentResult
    {
        // Logica per PayPal
        return new PaymentResult(true, 'PayPal payment processed');
    }
}

class StripePayment implements PaymentMethod
{
    public function process(Payment $payment): PaymentResult
    {
        // Logica per Stripe
        return new PaymentResult(true, 'Stripe payment processed');
    }
}

class PaymentProcessor
{
    public function process(Payment $payment, PaymentMethod $method): PaymentResult
    {
        return $method->process($payment);
    }
}

// Nuovo metodo di pagamento senza modificare PaymentProcessor
class BankTransferPayment implements PaymentMethod
{
    public function process(Payment $payment): PaymentResult
    {
        // Logica per bonifico bancario
        return new PaymentResult(true, 'Bank transfer payment processed');
    }
}
```

### Liskov Substitution Principle (LSP)

```php
//  Violazione LSP - Sottoclasse non sostituibile
abstract class Bird
{
    abstract public function fly(): string;
    abstract public function eat(): string;
}

class Eagle extends Bird
{
    public function fly(): string
    {
        return "Flying high in the sky";
    }
    
    public function eat(): string
    {
        return "Eating small animals";
    }
}

class Penguin extends Bird
{
    public function fly(): string
    {
        throw new Exception("Penguins can't fly!"); // Violazione LSP
    }
    
    public function eat(): string
    {
        return "Eating fish";
    }
}

//  Rispetto LSP - Sottoclassi sostituibili
abstract class Bird
{
    abstract public function move(): string;
    abstract public function eat(): string;
}

class FlyingBird extends Bird
{
    public function move(): string
    {
        return "Flying";
    }
    
    public function eat(): string
    {
        return "Eating while flying";
    }
}

class SwimmingBird extends Bird
{
    public function move(): string
    {
        return "Swimming";
    }
    
    public function eat(): string
    {
        return "Eating while swimming";
    }
}

class Eagle extends FlyingBird
{
    public function move(): string
    {
        return "Flying high in the sky";
    }
}

class Penguin extends SwimmingBird
{
    public function move(): string
    {
        return "Swimming underwater";
    }
}

// Uso polimorfico - funziona con qualsiasi sottoclasse
class BirdWatcher
{
    public function observeBird(Bird $bird): string
    {
        return "The bird is " . $bird->move() . " and " . $bird->eat();
    }
}
```

### Interface Segregation Principle (ISP)

```php
//  Violazione ISP - Interfaccia troppo grande
interface Worker
{
    public function work(): string;
    public function eat(): string;
    public function sleep(): string;
    public function code(): string;
    public function design(): string;
    public function test(): string;
}

class Developer implements Worker
{
    public function work(): string { return "Working"; }
    public function eat(): string { return "Eating"; }
    public function sleep(): string { return "Sleeping"; }
    public function code(): string { return "Coding"; }
    public function design(): string { throw new Exception("I don't design"); }
    public function test(): string { return "Testing"; }
}

class Designer implements Worker
{
    public function work(): string { return "Working"; }
    public function eat(): string { return "Eating"; }
    public function sleep(): string { return "Sleeping"; }
    public function code(): string { throw new Exception("I don't code"); }
    public function design(): string { return "Designing"; }
    public function test(): string { throw new Exception("I don't test"); }
}

//  Rispetto ISP - Interfacce specifiche
interface Workable
{
    public function work(): string;
}

interface Eatable
{
    public function eat(): string;
}

interface Sleepable
{
    public function sleep(): string;
}

interface Codable
{
    public function code(): string;
}

interface Designable
{
    public function design(): string;
}

interface Testable
{
    public function test(): string;
}

class Developer implements Workable, Eatable, Sleepable, Codable, Testable
{
    public function work(): string { return "Working"; }
    public function eat(): string { return "Eating"; }
    public function sleep(): string { return "Sleeping"; }
    public function code(): string { return "Coding"; }
    public function test(): string { return "Testing"; }
}

class Designer implements Workable, Eatable, Sleepable, Designable
{
    public function work(): string { return "Working"; }
    public function eat(): string { return "Eating"; }
    public function sleep(): string { return "Sleeping"; }
    public function design(): string { return "Designing"; }
}

class Tester implements Workable, Eatable, Sleepable, Testable
{
    public function work(): string { return "Working"; }
    public function eat(): string { return "Eating"; }
    public function sleep(): string { return "Sleeping"; }
    public function test(): string { return "Testing"; }
}
```

### Dependency Inversion Principle (DIP)

```php
//  Violazione DIP - Dipendenza da concrezioni
class EmailService
{
    public function send(string $to, string $subject, string $body): void
    {
        // Invio email
        Mail::to($to)->send(new GenericEmail($subject, $body));
    }
}

class SmsService
{
    public function send(string $to, string $message): void
    {
        // Invio SMS
        // Logica per invio SMS
    }
}

class UserService
{
    private $emailService;
    private $smsService;
    
    public function __construct()
    {
        $this->emailService = new EmailService(); // Dipendenza diretta
        $this->smsService = new SmsService(); // Dipendenza diretta
    }
    
    public function createUser(array $data): User
    {
        $user = User::create($data);
        
        // Invio notifiche
        $this->emailService->send($user->email, 'Welcome', 'Welcome to our app');
        $this->smsService->send($user->phone, 'Welcome to our app');
        
        return $user;
    }
}

//  Rispetto DIP - Dipendenza da astrazioni
interface NotificationService
{
    public function send(string $to, string $message): void;
}

class EmailService implements NotificationService
{
    public function send(string $to, string $message): void
    {
        Mail::to($to)->send(new GenericEmail('Notification', $message));
    }
}

class SmsService implements NotificationService
{
    public function send(string $to, string $message): void
    {
        // Logica per invio SMS
    }
}

class PushNotificationService implements NotificationService
{
    public function send(string $to, string $message): void
    {
        // Logica per push notification
    }
}

class UserService
{
    private array $notificationServices;
    
    public function __construct(NotificationService ...$notificationServices)
    {
        $this->notificationServices = $notificationServices;
    }
    
    public function createUser(array $data): User
    {
        $user = User::create($data);
        
        // Invio notifiche tramite tutti i servizi
        foreach ($this->notificationServices as $service) {
            $service->send($user->email, 'Welcome to our app');
        }
        
        return $user;
    }
}

// Registrazione nel Service Provider
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationService::class, function ($app) {
            return new EmailService();
        });
        
        $this->app->bind('notification.services', function ($app) {
            return [
                $app->make(EmailService::class),
                $app->make(SmsService::class),
                $app->make(PushNotificationService::class),
            ];
        });
    }
}
```

## Correlati

### Pattern

- **[DRY Pattern](./01-dry-pattern/dry-pattern.md)** - Evita duplicazione nel codice SOLID
- **[KISS Pattern](./02-kiss-pattern/kiss-pattern.md)** - Mantieni il codice semplice e SOLID
- **[YAGNI Pattern](./03-yagni-pattern/yagni-pattern.md)** - Non over-engineer con SOLID
- **[Clean Code](./05-clean-code/clean-code.md)** - Scrittura di codice pulito che rispetta SOLID
- **[Separation of Concerns](./06-separation-of-concerns/separation-of-concerns.md)** - Separazione delle responsabilità (SRP)
- **[TDD](./09-tdd/tdd.md)** - Test guidano la scrittura di codice SOLID
- **[Refactoring](./12-refactoring/refactoring.md)** - Miglioramento continuo del codice

### Principi e Metodologie

- **[Single Responsibility Principle](https://en.wikipedia.org/wiki/Single_responsibility_principle)** - Una classe, una responsabilità
- **[Open/Closed Principle](https://en.wikipedia.org/wiki/Open%E2%80%93closed_principle)** - Aperto per estensione, chiuso per modifica
- **[Liskov Substitution Principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle)** - Sostituibilità delle sottoclassi
- **[Interface Segregation Principle](https://en.wikipedia.org/wiki/Interface_segregation_principle)** - Interfacce specifiche e coese
- **[Dependency Inversion Principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle)** - Dipendenza da astrazioni

## Risorse utili

### Documentazione ufficiale
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID) - Principi originali
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Robert Martin
- [Clean Architecture](https://www.amazon.com/Clean-Architecture-Craftsmans-Software-Structure/dp/0134494272) - Robert Martin

### Laravel specifico
- [Laravel Service Container](https://laravel.com/docs/container) - Dependency injection
- [Laravel Service Providers](https://laravel.com/docs/providers) - Registrazione servizi
- [Laravel Contracts](https://laravel.com/docs/contracts) - Interfacce Laravel
- [Laravel Repositories](https://laravel.com/docs/eloquent) - Pattern Repository

### Esempi e tutorial
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP
- [Refactoring.Guru](https://refactoring.guru/) - Design patterns e principi

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
