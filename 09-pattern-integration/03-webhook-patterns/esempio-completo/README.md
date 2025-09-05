# Esempio Completo: Webhook Patterns

## Scopo

Questo esempio dimostra l'utilizzo dei Webhook Patterns in Laravel per ricevere notifiche in tempo reale da servizi esterni.

## Funzionalità

- **HTTP Callbacks**: Ricezione di notifiche HTTP POST
- **Event-Driven Architecture**: Reazione a eventi esterni
- **Retry Logic**: Gestione di fallimenti di consegna
- **Signature Verification**: Verifica dell'autenticità dei webhook
- **Idempotency**: Gestione di webhook duplicati
- **Rate Limiting**: Controllo del tasso di webhook ricevuti

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Services/
│   │   ├── WebhookService.php
│   │   └── SignatureService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── WebhookController.php
│   │   │   └── PaymentWebhookController.php
│   │   └── Middleware/
│   │       ├── VerifyWebhookSignature.php
│   │       └── CheckWebhookIdempotency.php
│   ├── Models/
│   │   ├── Webhook.php
│   │   └── Payment.php
│   └── Jobs/
│       ├── ProcessWebhookJob.php
│       └── ProcessPaymentWebhookJob.php
├── tests/
│   ├── Feature/
│   │   └── WebhookTest.php
│   └── Unit/
│       └── WebhookServiceTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan test` per vedere i test in azione

## Pattern Utilizzati

- **Observer Pattern**: Per reazione a eventi
- **Command Pattern**: Per operazioni basate su webhook
- **Retry Pattern**: Per gestione dei fallimenti
- **Circuit Breaker**: Per protezione da sovraccarico
