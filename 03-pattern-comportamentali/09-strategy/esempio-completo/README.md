# Esempio Completo: Strategy Pattern

Questo esempio dimostra l'implementazione del **Strategy Pattern** in Laravel per gestire algoritmi intercambiabili.

## Funzionalità implementate

- **Sistema di pagamento** multi-strategy
- **Algoritmi di ordinamento**
- **Sistema di notifiche**
- **Validatori configurabili**

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── PaymentController.php
│   └── Services/
│       ├── Strategies/
│       │   ├── StrategyInterface.php
│       │   ├── CreditCardStrategy.php
│       │   ├── PayPalStrategy.php
│       │   └── BankTransferStrategy.php
│       └── Contexts/
│           ├── PaymentProcessor.php
│           └── SortingContext.php
├── resources/views/
│   └── payments/
│       └── index.blade.php
├── routes/
│   └── web.php
└── composer.json
```

## Esempi di utilizzo

### Sistema di Pagamento
```php
$processor = new PaymentProcessor();
$processor->setStrategy(new CreditCardStrategy('1234567890123456', '123'));
$result = $processor->processPayment(100.00);

$processor->setStrategy(new PayPalStrategy('user@example.com', 'password'));
$result = $processor->processPayment(50.00);
```

## Pattern implementati

- **Strategy Pattern**: Algoritmi intercambiabili
- **Context Pattern**: Per usare le strategy
- **Factory Pattern**: Per creare strategy
