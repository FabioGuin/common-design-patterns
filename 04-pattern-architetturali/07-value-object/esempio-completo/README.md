# Sistema E-commerce con Value Object

## Panoramica

Questo esempio dimostra l'uso del Value Object pattern in un sistema e-commerce Laravel. Implementa Value Object per gestire Email, Prezzo e Indirizzo con validazione e immutabilità.

## Caratteristiche

- **Email Value Object**: Validazione formato email e immutabilità
- **Prezzo Value Object**: Gestione valute e calcoli sicuri
- **Indirizzo Value Object**: Validazione indirizzo completo
- **Form Request**: Validazione automatica con Value Object
- **Controller**: Gestione ordini con Value Object
- **Interfaccia Web**: Test dei Value Object

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con le impostazioni del database
4. Esegui `php artisan migrate`
5. Avvia il server con `php artisan serve`

## Struttura

```
app/
├── ValueObjects/
│   ├── Email.php
│   ├── Price.php
│   └── Address.php
├── Http/
│   ├── Controllers/
│   │   └── OrderController.php
│   └── Requests/
│       └── StoreOrderRequest.php
└── Services/
    └── OrderService.php
```

## Utilizzo

Visita `/orders` per vedere l'interfaccia di test dei Value Object.

## Pattern Demonstrated

- **Value Object**: Oggetti immutabili con validazione
- **Form Request**: Validazione automatica
- **Service Layer**: Logica di business separata
