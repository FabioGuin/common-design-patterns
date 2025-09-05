# Esempio Completo: Lazy Loading Pattern

## Scopo

Questo esempio dimostra l'utilizzo del pattern Lazy Loading in Laravel per ottimizzare le performance caricando i dati solo quando necessari.

## Funzionalità

- **Lazy Initialization**: Inizializzazione ritardata di oggetti costosi
- **Proxy Objects**: Oggetti proxy che caricano i dati su richiesta
- **Virtual Proxies**: Proxy che simulano oggetti reali
- **Value Holders**: Contenitori che caricano valori su accesso
- **Ghost Objects**: Oggetti fantasma che si materializzano su accesso

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Services/
│   │   ├── LazyLoadingService.php
│   │   ├── UserLazyService.php
│   │   └── ProductLazyService.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   └── Order.php
│   ├── Http/Controllers/
│   │   ├── LazyLoadingController.php
│   │   └── UserController.php
│   └── Proxies/
│       ├── LazyProxy.php
│       ├── UserProxy.php
│       └── ProductProxy.php
├── tests/
│   ├── Feature/
│   │   └── LazyLoadingTest.php
│   └── Unit/
│       └── LazyLoadingServiceTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan test` per vedere i test in azione

## Pattern Utilizzati

- **Proxy Pattern**: Per implementare lazy loading
- **Virtual Proxy**: Per oggetti costosi
- **Value Object**: Per valori lazy
- **Repository Pattern**: Per astrazione dei dati
