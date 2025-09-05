# Sistema di Pagamenti con Adapter Pattern

## Descrizione

Questo esempio dimostra l'uso dell'Adapter Pattern per integrare diversi provider di pagamento (Stripe e PayPal) sotto un'interfaccia comune. Il sistema permette di switchare tra provider senza modificare il codice esistente.

## Caratteristiche

- **Interfaccia comune** per tutti i provider di pagamento
- **Adapter per Stripe** che adatta l'API Stripe all'interfaccia comune
- **Adapter per PayPal** che adatta l'API PayPal all'interfaccia comune
- **Controller Laravel** per gestire i pagamenti
- **Vista interattiva** per testare i diversi provider
- **Configurazione** per switchare tra provider

## Installazione

1. Clona il repository
2. Installa le dipendenze: `composer install`
3. Configura il file `.env` con le tue chiavi API
4. Avvia il server: `php artisan serve`
5. Visita `http://localhost:8000/payments`

## Struttura del Progetto

```
app/
├── Http/Controllers/
│   └── PaymentController.php
├── Services/
│   ├── PaymentProcessorInterface.php
│   ├── StripeAdapter.php
│   └── PayPalAdapter.php
resources/views/
└── payments/
    └── index.blade.php
routes/
└── web.php
```

## Come Funziona

1. **PaymentProcessorInterface** definisce l'interfaccia comune
2. **StripeAdapter** e **PayPalAdapter** implementano l'interfaccia
3. **PaymentController** usa l'interfaccia senza sapere quale provider
4. **La vista** permette di testare entrambi i provider

## Test

- Visita `/payments` per vedere l'interfaccia
- Prova a processare pagamenti con entrambi i provider
- Verifica i log per vedere quale adapter viene usato
