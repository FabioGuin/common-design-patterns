# Event-Driven Architecture Pattern - Esempio Completo

## Panoramica

Questo esempio dimostra l'implementazione dell'**Event-Driven Architecture Pattern** in Laravel, un pattern fondamentale per costruire sistemi scalabili e disaccoppiati basati su eventi.

## Cosa fa l'Event-Driven Architecture

L'Event-Driven Architecture (EDA) è un pattern architetturale che promuove la produzione, il rilevamento, il consumo e la reazione agli eventi. I componenti del sistema comunicano attraverso eventi asincroni, creando sistemi più flessibili e scalabili.

## Come funziona

1. **Eventi** rappresentano cambiamenti significativi nel sistema
2. **Publisher** pubblica eventi quando accadono cose importanti
3. **Subscriber** si iscrive agli eventi che li interessano
4. **Event Bus** gestisce la distribuzione degli eventi
5. **Event Store** mantiene un log persistente di tutti gli eventi

## Struttura dell'Esempio

```
esempio-completo/
├── README.md                           # Questa guida
├── composer.json                       # Dipendenze Laravel 11
├── app/
│   ├── Services/
│   │   ├── EventBusService.php        # Servizio principale per l'event bus
│   │   ├── EventStoreService.php      # Servizio per l'event store
│   │   ├── OrderService.php           # Servizio di business che pubblica eventi
│   │   └── NotificationService.php    # Servizio che reagisce agli eventi
│   ├── Models/
│   │   ├── EventStore.php             # Modello per l'event store
│   │   └── Order.php                  # Modello Order di esempio
│   ├── Events/
│   │   ├── OrderCreated.php           # Evento di creazione ordine
│   │   ├── OrderUpdated.php           # Evento di aggiornamento ordine
│   │   ├── OrderCancelled.php         # Evento di cancellazione ordine
│   │   ├── PaymentProcessed.php       # Evento di pagamento processato
│   │   └── InventoryUpdated.php       # Evento di aggiornamento inventario
│   ├── Listeners/
│   │   ├── SendOrderConfirmation.php  # Listener per conferma ordine
│   │   ├── UpdateInventory.php        # Listener per aggiornare inventario
│   │   ├── SendPaymentNotification.php # Listener per notifica pagamento
│   │   └── LogOrderActivity.php       # Listener per log attività
│   ├── Jobs/
│   │   └── ProcessEventJob.php        # Job per processare eventi
│   ├── Http/Controllers/
│   │   └── EventDrivenController.php  # Controller per testare il pattern
│   └── Console/Commands/
│       └── ReplayEventsCommand.php    # Comando per riprodurre eventi
├── resources/views/
│   └── event-driven/
│       └── example.blade.php          # Interfaccia web per testare
├── routes/
│   └── web.php                        # Route per l'esempio
├── database/migrations/
│   ├── create_orders_table.php        # Tabella orders
│   └── create_event_store_table.php   # Tabella event_store
└── tests/
    └── Feature/
        └── EventDrivenTest.php        # Test per il pattern
```

## Caratteristiche Principali

### 1. Event Bus
- Gestisce la pubblicazione e sottoscrizione di eventi
- Supporta pattern di routing degli eventi
- Gestisce errori e retry automatici

### 2. Event Store
- Persistenza di tutti gli eventi del sistema
- Supporto per event sourcing
- Query e replay degli eventi

### 3. Eventi e Listener
- Eventi ben definiti con dati strutturati
- Listener specializzati per ogni tipo di evento
- Gestione asincrona tramite job

### 4. Monitoring e Debugging
- Interfaccia web per monitorare gli eventi
- Log dettagliati per debugging
- Statistiche in tempo reale

## Come Testare

### 1. Setup Iniziale
```bash
composer install
php artisan migrate
php artisan queue:work
```

### 2. Test via Web
- Vai su `/event-driven` per vedere l'interfaccia
- Crea ordini e osserva come gli eventi vengono pubblicati e gestiti

### 3. Test via API
```bash
# Crea un ordine
curl -X POST http://localhost:8000/event-driven/orders \
  -H "Content-Type: application/json" \
  -d '{"customer_name": "Mario Rossi", "amount": 100.50}'

# Verifica gli eventi
curl http://localhost:8000/event-driven/events
```

### 4. Test via Comando
```bash
# Riproduci eventi
php artisan events:replay --from=2024-01-01
```

## Scenari di Test

### Scenario 1: Creazione Ordine
- Crea un ordine
- Viene pubblicato l'evento OrderCreated
- I listener inviano conferma e aggiornano inventario

### Scenario 2: Aggiornamento Ordine
- Aggiorna lo status di un ordine
- Viene pubblicato l'evento OrderUpdated
- I listener inviano notifiche di aggiornamento

### Scenario 3: Pagamento Processato
- Simula un pagamento
- Viene pubblicato l'evento PaymentProcessed
- I listener aggiornano lo status dell'ordine

### Scenario 4: Replay Eventi
- Riproduci eventi da una data specifica
- Verifica che tutti i listener vengano eseguiti
- Testa la resilienza del sistema

## Vantaggi del Pattern

- **Disaccoppiamento**: I componenti non dipendono direttamente l'uno dall'altro
- **Scalabilità**: Facile aggiungere nuovi listener senza modificare il codice esistente
- **Flessibilità**: Cambiamenti nel sistema non impattano altri componenti
- **Audit Trail**: Tutti gli eventi sono tracciati nell'event store

## Considerazioni

- **Complexity**: Aggiunge complessità al sistema
- **Debugging**: Può essere più difficile debuggare sistemi asincroni
- **Consistency**: Richiede gestione attenta della consistenza eventuale
- **Performance**: Gli eventi asincroni possono introdurre latenza

## Pattern Correlati

- **Outbox Pattern**: Per pubblicazione affidabile di eventi
- **Inbox Pattern**: Per ricezione affidabile di eventi
- **Saga Pattern**: Per transazioni distribuite complesse
- **CQRS**: Per separazione di comandi e query

Questo esempio ti mostra come implementare l'Event-Driven Architecture in Laravel per costruire sistemi scalabili e disaccoppiati basati su eventi.
