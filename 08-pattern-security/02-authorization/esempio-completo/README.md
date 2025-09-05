# Esempio Completo: Authorization Pattern

## Scopo

Questo esempio dimostra l'utilizzo del pattern Authorization in Laravel per controllare l'accesso alle risorse basato sui permessi e ruoli degli utenti.

## Funzionalità

- **Role-Based Access Control (RBAC)**: Controllo basato sui ruoli
- **Permission-Based Access Control**: Controllo basato sui permessi
- **Policy-Based Access Control**: Controllo basato su policy
- **Resource-Based Access Control**: Controllo basato sulle risorse
- **Context-Aware Access Control**: Controllo basato sul contesto

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Services/
│   │   ├── AuthorizationService.php
│   │   └── PermissionService.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   └── Post.php
│   ├── Http/Controllers/
│   │   ├── AuthorizationController.php
│   │   └── PostController.php
│   ├── Policies/
│   │   ├── PostPolicy.php
│   │   └── UserPolicy.php
│   └── Middleware/
│       ├── CheckPermission.php
│       └── CheckRole.php
├── tests/
│   ├── Feature/
│   │   └── AuthorizationTest.php
│   └── Unit/
│       └── AuthorizationServiceTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan test` per vedere i test in azione

## Pattern Utilizzati

- **Strategy Pattern**: Per diverse strategie di autorizzazione
- **Policy Pattern**: Per policy di autorizzazione
- **Decorator Pattern**: Per middleware di autorizzazione
- **Proxy Pattern**: Per protezione di risorse
