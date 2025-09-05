# Esempio Completo: Test Doubles Pattern

## Scopo

Questo esempio dimostra l'utilizzo dei Test Doubles in Laravel per testare un sistema di gestione ordini e-commerce senza dipendenze esterne.

## Funzionalità

- **Mock di Repository**: Simula operazioni database
- **Stub di Servizi**: Fornisce risposte predefinite per servizi esterni
- **Fake di Notifiche**: Simula invio email e SMS
- **Spy di Logging**: Verifica chiamate di logging

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Models/
│   │   ├── Order.php
│   │   └── User.php
│   ├── Services/
│   │   ├── OrderService.php
│   │   ├── PaymentService.php
│   │   └── NotificationService.php
│   ├── Repositories/
│   │   └── OrderRepository.php
│   └── Http/Controllers/
│       └── OrderController.php
├── tests/
│   ├── Feature/
│   │   └── OrderTest.php
│   └── Unit/
│       ├── OrderServiceTest.php
│       ├── PaymentServiceTest.php
│       └── NotificationServiceTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan test` per vedere i test in azione

## Test Inclusi

- **Unit Test**: Test isolati con mock e stub
- **Feature Test**: Test di integrazione con fake
- **Spy Test**: Verifica delle chiamate ai servizi
- **Exception Test**: Test degli scenari di errore

## Pattern Utilizzati

- **Mock Objects**: Per repository e servizi esterni
- **Stub Objects**: Per risposte predefinite
- **Fake Objects**: Per servizi di notifica
- **Spy Objects**: Per verificare le chiamate
