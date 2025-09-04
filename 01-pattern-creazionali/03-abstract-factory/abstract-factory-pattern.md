# Abstract Factory Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Pattern correlati](#pattern-correlati)
- [Esempi di uso reale](#esempi-di-uso-reale)

### Cosa Evitare
- [Anti-pattern](#anti-pattern)

### Implementazione Pratica
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

### Considerazioni Tecniche
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa

L'Abstract Factory ti permette di creare famiglie di oggetti che vanno insieme, senza sapere esattamente quali classi specifiche stai creando. È come avere un'azienda che produce set completi: se scegli il set "cucina", ottieni forno, frigorifero e lavastoviglie della stessa marca che funzionano perfettamente insieme.

## Perché ti serve

Immagina di dover creare un sistema di pagamento che funziona con Stripe, PayPal e Square. Senza Abstract Factory, finiresti con:

- Oggetti incompatibili tra loro (Stripe gateway con PayPal validator)
- Logica di creazione sparsa e duplicata
- Difficoltà nel cambiare l'intera famiglia di prodotti
- Violazione del principio di coerenza tra oggetti correlati

L'Abstract Factory risolve questo: una factory per Stripe crea gateway, validator e logger tutti compatibili tra loro.

## Come funziona

Il meccanismo è strutturato:
1. **AbstractFactory**: Definisce i metodi per creare ogni tipo di prodotto
2. **ConcreteFactory**: Implementa la creazione di una famiglia specifica
3. **AbstractProduct**: Interfaccia per ogni tipo di oggetto
4. **ConcreteProduct**: Implementazione concreta di un prodotto
5. **Client**: Usa solo le interfacce astratte

Il client chiede alla factory di creare tutti gli oggetti di cui ha bisogno, e la factory garantisce che siano tutti compatibili.

## Schema visivo

```
Scenario 1 (Stripe Factory):
Client → StripeFactory → createGateway() → StripeGateway
                        → createValidator() → StripeValidator
                        → createLogger() → StripeLogger
                        ↓
                   Tutti compatibili tra loro

Scenario 2 (PayPal Factory):
Client → PayPalFactory → createGateway() → PayPalGateway
                        → createValidator() → PayPalValidator
                        → createLogger() → PayPalLogger
                        ↓
                   Tutti compatibili tra loro
```

*Il diagramma mostra come ogni factory crea una famiglia completa di oggetti che funzionano insieme perfettamente.*

## Quando usarlo

Usa l'Abstract Factory quando:
- Hai sistemi di pagamento con diversi provider (Stripe, PayPal, Square)
- Gestisci interfacce utente con diversi temi (Dark, Light, High Contrast)
- Lavori con database diversi (MySQL, PostgreSQL, SQLite)
- Hai sistemi di notifiche multi-canale (Email, SMS, Push, Slack)
- Gestisci diversi ambienti (Development, Staging, Production)
- Vuoi garantire la coerenza tra oggetti correlati

**NON usarlo quando:**
- Hai solo un tipo di prodotto
- I prodotti non sono correlati tra loro
- La complessità aggiuntiva non è giustificata
- Per creazioni semplici e isolate
- Hai bisogno di oggetti molto diversi tra loro

## Pro e contro

**I vantaggi:**
- Garantisce compatibilità tra prodotti correlati
- Facilita il cambio dell'intera famiglia di prodotti
- Isola la creazione di prodotti concreti
- Rispetta il principio Open/Closed
- Migliora la coerenza del sistema

**Gli svantaggi:**
- Aumenta significativamente la complessità
- Richiede molte interfacce e classi
- Può essere eccessivo per famiglie semplici
- Difficile da estendere con nuovi tipi di prodotti
- Può creare gerarchie complesse

## Pattern correlati

- **Factory Method**: Per creare singoli oggetti invece di famiglie
- **Builder**: Per costruire oggetti complessi passo dopo passo
- **Prototype**: Per clonare oggetti esistenti
- **Simple Factory**: Versione semplificata senza famiglie di prodotti

## Esempi di uso reale

- **Payment Gateway Systems**: Sistemi come Stripe, PayPal e Square usano Abstract Factory per creare famiglie di servizi compatibili
- **UI Framework**: Framework come Bootstrap e Material-UI usano Abstract Factory per creare componenti coerenti (bottoni, input, card)
- **Database Abstraction**: ORM come Doctrine usano Abstract Factory per creare famiglie di driver (MySQL, PostgreSQL, SQLite)
- **Cloud Providers**: Servizi AWS, Azure e Google Cloud usano Abstract Factory per creare famiglie di servizi compatibili
- **Cross-Platform Apps**: Applicazioni che devono funzionare su iOS, Android e Web usano Abstract Factory per creare componenti nativi

## Anti-pattern

**Cosa NON fare:**
- **Factory troppo complesse**: Evita factory che creano troppi tipi di oggetti diversi, diventa difficile da mantenere
- **Prodotti non correlati**: Non mettere in una famiglia prodotti che non hanno nulla a che fare tra loro
- **Factory senza coerenza**: Assicurati che tutti i prodotti di una famiglia seguano lo stesso stile e convenzioni
- **Factory per oggetti semplici**: Non usare Abstract Factory per oggetti che si creano facilmente con `new`
- **Factory con troppe responsabilità**: Evita factory che fanno troppo lavoro oltre alla creazione

## Esempi di codice

### Esempio base
```php
<?php

// Abstract Products
interface Button
{
    public function render(): string;
}

interface TextField
{
    public function render(): string;
}

// Concrete Products - Windows Family
class WindowsButton implements Button
{
    public function render(): string
    {
        return "Windows Button rendered";
    }
}

class WindowsTextField implements TextField
{
    public function render(): string
    {
        return "Windows TextField rendered";
    }
}

// Concrete Products - Mac Family
class MacButton implements Button
{
    public function render(): string
    {
        return "Mac Button rendered";
    }
}

class MacTextField implements TextField
{
    public function render(): string
    {
        return "Mac TextField rendered";
    }
}

// Abstract Factory
interface UIFactory
{
    public function createButton(): Button;
    public function createTextField(): TextField;
}

// Concrete Factories
class WindowsUIFactory implements UIFactory
{
    public function createButton(): Button
    {
        return new WindowsButton();
    }
    
    public function createTextField(): TextField
    {
        return new WindowsTextField();
    }
}

class MacUIFactory implements UIFactory
{
    public function createButton(): Button
    {
        return new MacButton();
    }
    
    public function createTextField(): TextField
    {
        return new MacTextField();
    }
}

// Client
class Application
{
    private UIFactory $factory;
    
    public function __construct(UIFactory $factory)
    {
        $this->factory = $factory;
    }
    
    public function createUI(): string
    {
        $button = $this->factory->createButton();
        $textField = $this->factory->createTextField();
        
        return $button->render() . " " . $textField->render();
    }
}

// Utilizzo
$windowsFactory = new WindowsUIFactory();
$app = new Application($windowsFactory);
echo $app->createUI(); // "Windows Button rendered Windows TextField rendered"
```

### Esempio per Laravel
```php
<?php

namespace App\Services\Payment;

// Abstract Products
interface PaymentGateway
{
    public function processPayment(float $amount): bool;
}

interface PaymentValidator
{
    public function validate(array $data): bool;
}

interface PaymentLogger
{
    public function log(string $message): void;
}

// Stripe Family
class StripeGateway implements PaymentGateway
{
    public function processPayment(float $amount): bool
    {
        // Logica Stripe
        return true;
    }
}

class StripeValidator implements PaymentValidator
{
    public function validate(array $data): bool
    {
        // Validazione specifica Stripe
        return true;
    }
}

class StripeLogger implements PaymentLogger
{
    public function log(string $message): void
    {
        // Log specifico per Stripe
        \Log::info("Stripe: " . $message);
    }
}

// PayPal Family
class PayPalGateway implements PaymentGateway
{
    public function processPayment(float $amount): bool
    {
        // Logica PayPal
        return true;
    }
}

class PayPalValidator implements PaymentValidator
{
    public function validate(array $data): bool
    {
        // Validazione specifica PayPal
        return true;
    }
}

class PayPalLogger implements PaymentLogger
{
    public function log(string $message): void
    {
        // Log specifico per PayPal
        \Log::info("PayPal: " . $message);
    }
}

// Abstract Factory
interface PaymentFactory
{
    public function createGateway(): PaymentGateway;
    public function createValidator(): PaymentValidator;
    public function createLogger(): PaymentLogger;
}

// Concrete Factories
class StripePaymentFactory implements PaymentFactory
{
    public function createGateway(): PaymentGateway
    {
        return new StripeGateway();
    }
    
    public function createValidator(): PaymentValidator
    {
        return new StripeValidator();
    }
    
    public function createLogger(): PaymentLogger
    {
        return new StripeLogger();
    }
}

class PayPalPaymentFactory implements PaymentFactory
{
    public function createGateway(): PaymentGateway
    {
        return new PayPalGateway();
    }
    
    public function createValidator(): PaymentValidator
    {
        return new PayPalValidator();
    }
    
    public function createLogger(): PaymentLogger
    {
        return new PayPalLogger();
    }
}

// Service Provider
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentFactory::class, function ($app) {
            $provider = config('payment.default_provider');
            
            return match($provider) {
                'stripe' => new StripePaymentFactory(),
                'paypal' => new PayPalPaymentFactory(),
                default => throw new \InvalidArgumentException("Unsupported payment provider: {$provider}")
            };
        });
    }
}

// Utilizzo in Controller
class PaymentController extends Controller
{
    public function __construct(private PaymentFactory $paymentFactory) {}
    
    public function processPayment(Request $request): JsonResponse
    {
        $validator = $this->paymentFactory->createValidator();
        $gateway = $this->paymentFactory->createGateway();
        $logger = $this->paymentFactory->createLogger();
        
        if (!$validator->validate($request->all())) {
            return response()->json(['error' => 'Invalid data'], 400);
        }
        
        $success = $gateway->processPayment($request->amount);
        $logger->log("Payment processed: " . ($success ? 'success' : 'failed'));
        
        return response()->json(['success' => $success]);
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Pagamento Multi-Provider](../../../esempi-completi/04-abstract-factory-payment/)** - Sistema di pagamento completo con Abstract Factory per gestire diverse famiglie di servizi

L'esempio include:
- Factory per diversi provider di pagamento (Stripe, PayPal)
- Validatori, gateway e logger specifici per ogni provider
- Service Provider per configurazione dinamica
- Controller con dependency injection
- Configurazione basata su ambiente
- Test unitari per ogni famiglia di prodotti
- API RESTful per gestione pagamenti

## Performance e considerazioni

- **Impatto memoria**: Overhead significativo per tutte le interfacce e classi necessarie
- **Impatto CPU**: La creazione tramite Abstract Factory è più lenta del `new` diretto
- **Scalabilità**: Ottimo per sistemi che devono gestire molte famiglie di prodotti diverse
- **Colli di bottiglia**: Può diventare complesso da debuggare e mantenere con molte famiglie

## Risorse utili

- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Abstract Factory](https://refactoring.guru/design-patterns/abstract-factory) - Spiegazione visuale con esempi
- [Laravel Service Container](https://laravel.com/docs/container) - Come Laravel gestisce le dipendenze
- [Abstract Factory in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
