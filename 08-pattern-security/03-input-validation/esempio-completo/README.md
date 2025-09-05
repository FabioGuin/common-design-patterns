# Esempio Completo: Input Validation Pattern

## Scopo

Questo esempio dimostra l'utilizzo del pattern Input Validation in Laravel per validare, sanitizzare e filtrare tutti gli input dell'utente.

## Funzionalità

- **Client-Side Validation**: Validazione nel browser
- **Server-Side Validation**: Validazione sul server
- **Schema Validation**: Validazione basata su schema
- **Rule-Based Validation**: Validazione basata su regole
- **Type Validation**: Validazione dei tipi di dati
- **Sanitization**: Pulizia e normalizzazione dei dati

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Services/
│   │   ├── ValidationService.php
│   │   └── SanitizationService.php
│   ├── Http/
│   │   ├── Requests/
│   │   │   ├── CreateUserRequest.php
│   │   │   ├── UpdateUserRequest.php
│   │   │   └── CreatePostRequest.php
│   │   └── Controllers/
│   │       ├── ValidationController.php
│   │       └── UserController.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Post.php
│   └── Rules/
│       ├── StrongPassword.php
│       └── UniqueEmail.php
├── tests/
│   ├── Feature/
│   │   └── ValidationTest.php
│   └── Unit/
│       └── ValidationServiceTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan test` per vedere i test in azione

## Pattern Utilizzati

- **Strategy Pattern**: Per diverse strategie di validazione
- **Chain of Responsibility**: Per catene di validazione
- **Decorator Pattern**: Per middleware di validazione
- **Observer Pattern**: Per eventi di validazione
