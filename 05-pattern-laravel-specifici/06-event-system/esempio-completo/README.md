# Esempio Completo: Event System Pattern

## Scopo

Questo esempio dimostra l'utilizzo del sistema di eventi di Laravel per gestire notifiche, logging e azioni asincrone in un'applicazione e-commerce.

## Funzionalità

- **User Events**: Registrazione, login, logout, aggiornamento profilo
- **Order Events**: Creazione, pagamento, spedizione, consegna, cancellazione
- **Product Events**: Creazione, aggiornamento, eliminazione, cambio prezzo
- **Notification Events**: Invio email, SMS, notifiche push
- **Audit Events**: Logging delle azioni per audit trail

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Events/
│   │   ├── User/
│   │   │   ├── UserRegistered.php
│   │   │   ├── UserLoggedIn.php
│   │   │   └── UserProfileUpdated.php
│   │   ├── Order/
│   │   │   ├── OrderCreated.php
│   │   │   ├── OrderPaid.php
│   │   │   ├── OrderShipped.php
│   │   │   └── OrderDelivered.php
│   │   └── Product/
│   │       ├── ProductCreated.php
│   │       └── ProductPriceChanged.php
│   ├── Listeners/
│   │   ├── User/
│   │   │   ├── SendWelcomeEmail.php
│   │   │   ├── LogUserActivity.php
│   │   │   └── UpdateLastLogin.php
│   │   ├── Order/
│   │   │   ├── SendOrderConfirmation.php
│   │   │   ├── UpdateInventory.php
│   │   │   └── NotifyAdmin.php
│   │   └── Product/
│   │       ├── LogPriceChange.php
│   │       └── NotifySubscribers.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Order.php
│   │   └── Product.php
│   └── Http/Controllers/
│       ├── UserController.php
│       ├── OrderController.php
│       └── ProductController.php
├── tests/
│   ├── Feature/
│   │   ├── UserEventTest.php
│   │   ├── OrderEventTest.php
│   │   └── ProductEventTest.php
│   └── Unit/
│       └── EventListenerTest.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan event:list` per vedere gli eventi registrati
5. Esegui `php artisan test` per vedere i test in azione

## Eventi Inclusi

- **User Events**: Gestione eventi utente
- **Order Events**: Gestione eventi ordini
- **Product Events**: Gestione eventi prodotti
- **Custom Events**: Eventi personalizzati per business logic

## Pattern Utilizzati

- **Observer Pattern**: Per ascoltare eventi
- **Event Sourcing**: Per tracciare cambiamenti
- **Publish-Subscribe**: Per comunicazione asincrona
- **Command Pattern**: Per azioni che possono essere annullate
