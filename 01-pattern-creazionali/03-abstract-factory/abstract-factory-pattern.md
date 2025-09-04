# Abstract Factory Pattern
*(Categoria: Creazionale)*

## Indice
- [Abstract](#abstract)
- [Contesto e Motivazione](#contesto-e-motivazione)
- [Soluzione proposta](#soluzione-proposta)
- [Quando usarlo](#quando-usarlo)
- [Vantaggi e Svantaggi](#vantaggi-e-svantaggi)
- [Esempi pratici](#esempi-pratici)
  - [Esempio concettuale](#esempio-concettuale)
  - [Esempio Laravel](#esempio-laravel)
- [Esempi Completi](#esempi-completi)

## Abstract
L'Abstract Factory Pattern fornisce un'interfaccia per creare famiglie di oggetti correlati o dipendenti senza specificare le loro classi concrete. Permette di creare oggetti che lavorano insieme come una famiglia, garantendo la compatibilità tra prodotti correlati.

## Contesto e Motivazione
- **Contesto tipico**: Quando hai bisogno di creare famiglie di oggetti correlati che devono lavorare insieme, o quando vuoi garantire che solo prodotti compatibili vengano utilizzati insieme
- **Sintomi di un design non ottimale**: 
  - Creazione di oggetti incompatibili tra loro
  - Logica di creazione sparsa e duplicata
  - Difficoltà nel cambiare l'intera famiglia di prodotti
  - Violazione del principio di coerenza tra oggetti correlati
- **Perché le soluzioni semplici non sono ideali**: Creare oggetti singolarmente può portare a incompatibilità tra prodotti che dovrebbero lavorare insieme, e rende difficile cambiare l'intera famiglia di prodotti.

## Soluzione proposta
- **Idea chiave**: Definisce un'interfaccia per creare famiglie di oggetti correlati, garantendo che i prodotti creati siano compatibili tra loro
- **Struttura concettuale**: 
  - AbstractFactory con metodi per creare ogni tipo di prodotto
  - ConcreteFactory che implementa la creazione di una famiglia specifica
  - AbstractProduct e ConcreteProduct per ogni tipo di oggetto
  - Client che usa solo le interfacce astratte
- **Ruolo dei partecipanti**:
  - **AbstractFactory**: Interfaccia per creare famiglie di prodotti correlati
  - **ConcreteFactory**: Implementa la creazione di una famiglia specifica di prodotti
  - **AbstractProduct**: Interfaccia per un tipo di prodotto
  - **ConcreteProduct**: Implementazione concreta di un prodotto
  - **Client**: Usa solo le interfacce astratte

## Quando usarlo
- **Casi d'uso ideali**:
  - Sistemi di pagamento con diversi provider (Stripe, PayPal, Square)
  - Interfacce utente con diversi temi (Dark, Light, High Contrast)
  - Database con diversi driver (MySQL, PostgreSQL, SQLite)
  - Sistemi di notifica multi-canale (Email, SMS, Push, Slack)
  - Gestione di diversi ambienti (Development, Staging, Production)
- **Indicatori che suggeriscono l'adozione**:
  - Necessità di creare famiglie di oggetti correlati
  - Richiesta di compatibilità tra prodotti della stessa famiglia
  - Necessità di cambiare l'intera famiglia di prodotti
  - Configurazione basata su ambiente o preferenze utente
- **Situazioni in cui NON è consigliato**:
  - Quando hai solo un tipo di prodotto
  - Se i prodotti non sono correlati tra loro
  - Quando la complessità aggiuntiva non è giustificata
  - Per creazioni semplici e isolate

## Vantaggi e Svantaggi
**Vantaggi**
- Garantisce compatibilità tra prodotti correlati
- Facilita il cambio dell'intera famiglia di prodotti
- Isola la creazione di prodotti concreti
- Rispetta il principio Open/Closed
- Migliora la coerenza del sistema

**Svantaggi**
- Aumenta significativamente la complessità
- Richiede molte interfacce e classi
- Può essere eccessivo per famiglie semplici
- Difficile da estendere con nuovi tipi di prodotti
- Può creare gerarchie complesse

## Esempi pratici

### Esempio concettuale
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

### Esempio Laravel
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

## Esempi Completi

Per implementazioni complete e funzionanti dell'Abstract Factory Pattern in Laravel, consulta:

- **[Esempio Completo: Payment Gateway System](../../../esempi-completi/)** - Sistema di pagamento multi-provider con Abstract Factory per gestire diverse famiglie di servizi di pagamento

L'esempio completo include:
- Factory per diversi provider di pagamento (Stripe, PayPal, Square)
- Validatori, gateway e logger specifici per ogni provider
- Service Provider per configurazione dinamica
- Controller con dependency injection
- Configurazione basata su ambiente
- Test unitari per ogni famiglia di prodotti
- API RESTful per gestione pagamenti

