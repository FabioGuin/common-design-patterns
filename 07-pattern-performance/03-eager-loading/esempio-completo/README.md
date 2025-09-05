# Esempio Completo: Eager Loading Pattern

## Scopo

Questo esempio dimostra l'utilizzo del pattern Eager Loading in Laravel per ottimizzare le performance caricando anticipatamente tutte le relazioni necessarie.

## Funzionalità

- **Single Query Loading**: Carica tutte le relazioni in una query
- **Batch Loading**: Carica dati in batch per ridurre le query
- **Preloading**: Carica dati prima che siano necessari
- **Selective Loading**: Carica solo i campi necessari
- **Conditional Loading**: Carica dati basati su condizioni

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Services/
│   │   ├── EagerLoadingService.php
│   │   ├── ProductEagerService.php
│   │   └── UserEagerService.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   └── Category.php
│   ├── Http/Controllers/
│   │   ├── EagerLoadingController.php
│   │   └── ProductController.php
│   └── Repositories/
│       ├── ProductRepository.php
│       └── UserRepository.php
├── tests/
│   ├── Feature/
│   │   └── EagerLoadingTest.php
│   └── Unit/
│       └── EagerLoadingServiceTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan test` per vedere i test in azione

## Pattern Utilizzati

- **Repository Pattern**: Per astrazione delle query
- **Query Builder**: Per costruzione di query complesse
- **Data Mapper**: Per mapping dei dati
- **Unit of Work**: Per gestione delle transazioni
