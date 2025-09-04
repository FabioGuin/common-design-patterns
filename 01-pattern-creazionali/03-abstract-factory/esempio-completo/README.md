# Abstract Factory Pattern - Payment System

## Descrizione
Implementazione completa del pattern Abstract Factory per un sistema di pagamento multi-provider in Laravel. Questo esempio dimostra come creare famiglie di oggetti correlati (gateway, validator, logger) che lavorano insieme in modo compatibile.

## Struttura del Progetto

```
app/
├── Http/Controllers/
│   └── PaymentController.php          # Controller per gestire i pagamenti
├── Providers/
│   └── PaymentServiceProvider.php     # Service Provider per configurazione
└── Services/Payment/
    ├── Factories/
    │   ├── PaymentFactory.php         # Interfaccia Abstract Factory
    │   ├── StripePaymentFactory.php   # Concrete Factory per Stripe
    │   └── PayPalPaymentFactory.php   # Concrete Factory per PayPal
    ├── Gateways/
    │   ├── PaymentGateway.php         # Interfaccia Abstract Product
    │   ├── StripeGateway.php          # Concrete Product per Stripe
    │   └── PayPalGateway.php          # Concrete Product per PayPal
    ├── Validators/
    │   ├── PaymentValidator.php       # Interfaccia Abstract Product
    │   ├── StripeValidator.php        # Concrete Product per Stripe
    │   └── PayPalValidator.php        # Concrete Product per PayPal
    ├── Loggers/
    │   ├── PaymentLogger.php          # Interfaccia Abstract Product
    │   ├── StripeLogger.php           # Concrete Product per Stripe
    │   └── PayPalLogger.php           # Concrete Product per PayPal
    ├── PaymentResult.php              # Value Object per risultati
    ├── PaymentStatus.php              # Enum per stati pagamento
    └── ValidationResult.php           # Value Object per validazione
```

## Caratteristiche Principali

### 1. Famiglie di Prodotti Correlati
- **Stripe Family**: StripeGateway, StripeValidator, StripeLogger
- **PayPal Family**: PayPalGateway, PayPalValidator, PayPalLogger

### 2. Compatibilità Garantita
- I prodotti della stessa famiglia sono progettati per lavorare insieme
- Validatori specifici per ogni provider
- Logging personalizzato per ogni provider

### 3. Configurazione Dinamica
- Service Provider per gestire la configurazione
- Supporto per cambio provider tramite configurazione
- Dependency injection automatica

## Installazione e Configurazione

### 1. Installazione Dipendenze
```bash
composer install
```

### 2. Configurazione Ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurazione Provider
Nel file `.env`:
```env
PAYMENT_DEFAULT_PROVIDER=stripe

# Stripe
STRIPE_API_KEY=sk_test_your_key
STRIPE_WEBHOOK_SECRET=whsec_your_secret

# PayPal
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
```

### 4. Registrazione Service Provider
Nel file `config/app.php`:
```php
'providers' => [
    // ...
    App\Providers\PaymentServiceProvider::class,
],
```

## Utilizzo

### 1. Controller con Dependency Injection
```php
class PaymentController extends Controller
{
    public function __construct(
        private PaymentFactory $paymentFactory
    ) {}
    
    public function processPayment(Request $request)
    {
        $validator = $this->paymentFactory->createValidator();
        $gateway = $this->paymentFactory->createGateway();
        $logger = $this->paymentFactory->createLogger();
        
        // Utilizzo dei prodotti correlati...
    }
}
```

### 2. API Endpoints
```bash
# Processa pagamento
POST /api/payments/process
{
    "amount": 25.50,
    "currency": "USD",
    "card_token": "tok_test_123",
    "customer": {
        "email": "customer@example.com",
        "name": "John Doe"
    }
}

# Rimborsa pagamento
POST /api/payments/refund
{
    "transaction_id": "stripe_123456",
    "amount": 25.50
}

# Verifica stato pagamento
GET /api/payments/status/{transaction_id}
```

### 3. Test del Pattern
```bash
php test-example.php
```

## Vantaggi del Pattern

### 1. Compatibilità Garantita
- I prodotti della stessa famiglia sono progettati per lavorare insieme
- Evita errori di incompatibilità tra componenti

### 2. Flessibilità
- Facile aggiunta di nuovi provider
- Cambio provider tramite configurazione
- Estensibilità senza modificare codice esistente

### 3. Coerenza
- Interfaccia uniforme per tutti i provider
- Comportamento prevedibile
- Logging e validazione standardizzati

## Test

### Esecuzione Test Unitari
```bash
php artisan test
```

### Test Specifici
```bash
php artisan test tests/Unit/PaymentFactoryTest.php
```

## Estensione del Pattern

### Aggiunta Nuovo Provider (es. Square)

1. **Creare Concrete Products**:
```php
class SquareGateway implements PaymentGateway { ... }
class SquareValidator implements PaymentValidator { ... }
class SquareLogger implements PaymentLogger { ... }
```

2. **Creare Concrete Factory**:
```php
class SquarePaymentFactory implements PaymentFactory { ... }
```

3. **Aggiornare Service Provider**:
```php
'square' => new SquarePaymentFactory(
    config('payment.square.access_token'),
    config('payment.square.location_id')
),
```

4. **Aggiungere Configurazione**:
```php
'square' => [
    'access_token' => env('SQUARE_ACCESS_TOKEN'),
    'location_id' => env('SQUARE_LOCATION_ID'),
    'enabled' => env('SQUARE_ENABLED', true),
],
```

## Note Tecniche

- **PHP 8.1+**: Utilizzo di readonly properties e enum
- **Laravel 10+**: Service Provider e dependency injection
- **PSR-4**: Autoloading standard
- **SOLID Principles**: Rispetto dei principi di design
- **Test Coverage**: Test unitari completi

## Link Utili

- [Documentazione Pattern](../../01-pattern-creazionali/03-abstract-factory/abstract-factory-pattern.md)
- [Indice Esempi](../../README.md)
- [Pattern Creazionali](../../01-pattern-creazionali/README.md)

