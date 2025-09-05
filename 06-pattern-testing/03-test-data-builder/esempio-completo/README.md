# Esempio Completo: Test Data Builder Pattern

## Scopo

Questo esempio dimostra l'utilizzo del Test Data Builder pattern in Laravel per creare oggetti di test complessi in modo flessibile e leggibile.

## Funzionalità

- **UserBuilder**: Creazione di utenti con dati realistici
- **ProductBuilder**: Creazione di prodotti con varianti
- **OrderBuilder**: Creazione di ordini complessi
- **AddressBuilder**: Creazione di indirizzi validi
- **PaymentBuilder**: Creazione di dati di pagamento

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   └── Address.php
│   └── Http/Controllers/
│       └── UserController.php
├── tests/
│   ├── Builders/
│   │   ├── UserBuilder.php
│   │   ├── ProductBuilder.php
│   │   ├── OrderBuilder.php
│   │   ├── AddressBuilder.php
│   │   └── PaymentBuilder.php
│   ├── Unit/
│   │   ├── UserTest.php
│   │   ├── ProductTest.php
│   │   └── OrderTest.php
│   └── Feature/
│       └── UserRegistrationTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan test` per vedere i test in azione

## Test Inclusi

- **Unit Test**: Test isolati con Data Builders
- **Feature Test**: Test di integrazione
- **Builder Test**: Test specifici per i builder

## Pattern Utilizzati

- **Test Data Builder**: Per creazione di oggetti complessi
- **Fluent Interface**: Per API leggibili
- **Builder Pattern**: Per costruzione step-by-step
- **Factory Pattern**: Per valori di default realistici
