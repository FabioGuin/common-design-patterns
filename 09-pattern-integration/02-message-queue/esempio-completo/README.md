# Esempio Completo: Message Queue Pattern

## Scopo

Questo esempio dimostra l'utilizzo del pattern Message Queue in Laravel per gestire operazioni asincrone e migliorare le performance.

## Funzionalità

- **Producer-Consumer**: Produttori inviano messaggi, consumatori li processano
- **Point-to-Point**: Messaggi diretti tra produttore e consumatore
- **Publish-Subscribe**: Messaggi broadcast a multiple sottoscrizioni
- **Dead Letter Queue**: Gestione di messaggi non processabili
- **Message Routing**: Routing intelligente dei messaggi
- **Message Persistence**: Persistenza dei messaggi per affidabilità

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Jobs/
│   │   ├── SendEmailJob.php
│   │   ├── ProcessOrderJob.php
│   │   └── GenerateReportJob.php
│   ├── Services/
│   │   ├── QueueService.php
│   │   └── MessageService.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Order.php
│   │   └── Report.php
│   ├── Http/Controllers/
│   │   ├── QueueController.php
│   │   └── OrderController.php
│   └── Events/
│       ├── OrderCreated.php
│       └── UserRegistered.php
├── tests/
│   ├── Feature/
│   │   └── QueueTest.php
│   └── Unit/
│       └── QueueServiceTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan queue:work` per avviare i worker
5. Esegui `php artisan test` per vedere i test in azione

## Pattern Utilizzati

- **Producer-Consumer**: Per gestione dei messaggi
- **Observer Pattern**: Per notifiche di eventi
- **Command Pattern**: Per operazioni asincrone
- **Retry Pattern**: Per gestione dei fallimenti
