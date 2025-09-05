# Sistema di Notifiche con Decorator Pattern

## Descrizione

Questo esempio dimostra l'uso del Decorator Pattern per creare un sistema di notifiche flessibile dove puoi aggiungere funzionalità come logging, caching, validazione e throttling senza modificare il codice esistente. Ogni decoratore si avvolge attorno al precedente per combinare le funzionalità.

## Caratteristiche

- **Interfaccia base** per le notifiche
- **Decoratori** per logging, caching, validazione e throttling
- **Combinazioni flessibili** di decoratori
- **Controller Laravel** per gestire le notifiche
- **Vista interattiva** per testare le diverse combinazioni
- **Sistema di configurazione** per i decoratori

## Installazione

1. Clona il repository
2. Installa le dipendenze: `composer install`
3. Avvia il server: `php artisan serve`
4. Visita `http://localhost:8000/notifications`

## Struttura del Progetto

```
app/
├── Http/Controllers/
│   └── NotificationController.php
├── Services/
│   ├── NotificationInterface.php
│   ├── BaseNotification.php
│   ├── LoggingDecorator.php
│   ├── CachingDecorator.php
│   ├── ValidationDecorator.php
│   └── ThrottlingDecorator.php
resources/views/
└── notifications/
    └── index.blade.php
routes/
└── web.php
```

## Come Funziona

1. **NotificationInterface** definisce l'interfaccia comune
2. **BaseNotification** è l'implementazione base
3. **I decoratori** si avvolgono attorno alle notifiche per aggiungere funzionalità
4. **Il controller** permette di combinare diversi decoratori
5. **La vista** permette di testare le diverse combinazioni

## Test

- Visita `/notifications` per vedere l'interfaccia
- Prova a inviare notifiche con diverse combinazioni di decoratori
- Verifica i log per vedere quale decoratore viene usato
- Testa le funzionalità di caching e throttling
