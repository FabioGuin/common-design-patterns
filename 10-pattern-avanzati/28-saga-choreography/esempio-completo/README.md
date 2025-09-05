# Esempio Saga Choreography Pattern

## Descrizione

Questo esempio dimostra il **Saga Choreography Pattern** in un'applicazione Laravel. Il pattern gestisce transazioni distribuite complesse utilizzando eventi e messaggi per coordinare i servizi, senza un orchestratore centrale. Ogni servizio è responsabile del proprio comportamento e della propria compensazione.

## Struttura dell'Esempio

```
esempio-completo/
├── README.md
├── app/
│   ├── Services/
│   │   ├── EventBusService.php
│   │   ├── UserService.php
│   │   ├── ProductService.php
│   │   ├── OrderService.php
│   │   ├── PaymentService.php
│   │   ├── InventoryService.php
│   │   └── NotificationService.php
│   ├── Http/Controllers/
│   │   ├── SagaChoreographyController.php
│   │   ├── UserController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   └── PaymentController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   ├── Payment.php
│   │   └── SagaEvent.php
│   ├── Events/
│   │   ├── UserValidated.php
│   │   ├── InventoryReserved.php
│   │   ├── OrderCreated.php
│   │   ├── PaymentProcessed.php
│   │   ├── NotificationSent.php
│   │   ├── OrderCancelled.php
│   │   ├── PaymentRefunded.php
│   │   ├── InventoryReleased.php
│   │   └── NotificationCancelled.php
│   └── Listeners/
│       ├── HandleUserValidated.php
│       ├── HandleInventoryReserved.php
│       ├── HandleOrderCreated.php
│       ├── HandlePaymentProcessed.php
│       ├── HandleNotificationSent.php
│       ├── HandleOrderCancelled.php
│       ├── HandlePaymentRefunded.php
│       ├── HandleInventoryReleased.php
│       └── HandleNotificationCancelled.php
├── resources/views/
│   └── saga-choreography/
│       └── example.blade.php
├── routes/
│   └── web.php
├── tests/Feature/
│   └── SagaChoreographyTest.php
└── test-standalone.php
```

## Caratteristiche dell'Esempio

### 1. **Event Bus**
- Gestisce la pubblicazione e sottoscrizione di eventi
- Implementa pattern Observer per la comunicazione tra servizi
- Supporta eventi asincroni e sincroni

### 2. **Servizi Distribuiti**
- UserService: Gestione utenti
- ProductService: Gestione prodotti
- OrderService: Gestione ordini
- PaymentService: Gestione pagamenti
- InventoryService: Gestione inventario
- NotificationService: Invio notifiche

### 3. **Eventi Saga**
- UserValidated: Utente validato
- InventoryReserved: Inventario riservato
- OrderCreated: Ordine creato
- PaymentProcessed: Pagamento processato
- NotificationSent: Notifica inviata
- OrderCancelled: Ordine cancellato
- PaymentRefunded: Pagamento rimborsato
- InventoryReleased: Inventario rilasciato
- NotificationCancelled: Notifica cancellata

### 4. **Listeners**
- Gestiscono gli eventi pubblicati
- Implementano la logica di business
- Gestiscono la compensazione automatica

## Come Eseguire l'Esempio

### 1. **Test Standalone**
```bash
php test-standalone.php
```

### 2. **Test Laravel**
```bash
php artisan test tests/Feature/SagaChoreographyTest.php
```

### 3. **Interfaccia Web**
```bash
php artisan serve
# Visita: http://localhost:8000/saga-choreography/example
```

## Esempio di Saga: Creazione Ordine

### 1. **Flusso degli Eventi**
1. **UserValidated** → InventoryService riserva inventario
2. **InventoryReserved** → OrderService crea ordine
3. **OrderCreated** → PaymentService processa pagamento
4. **PaymentProcessed** → NotificationService invia notifica
5. **NotificationSent** → Saga completata

### 2. **Compensazione**
- Se un evento fallisce, viene pubblicato un evento di compensazione
- I servizi ascoltano gli eventi di compensazione e eseguono le azioni inverse
- La compensazione è distribuita e gestita da ogni servizio

### 3. **Gestione Errori**
- Retry automatico per errori temporanei
- Timeout per operazioni lunghe
- Fallback per errori permanenti

## Configurazione Database

L'esempio utilizza le seguenti tabelle:

- `users` - Utenti del sistema
- `products` - Prodotti disponibili
- `orders` - Ordini creati
- `payments` - Pagamenti processati
- `saga_events` - Eventi delle saga

## Vantaggi del Pattern

### 1. **Decentralizzazione**
- Nessun orchestratore centrale
- Servizi indipendenti e autonomi
- Maggiore resilienza

### 2. **Scalabilità**
- Servizi possono essere scalati indipendentemente
- Eventi asincroni per performance migliori
- Meno bottleneck

### 3. **Flessibilità**
- Facile aggiungere/rimuovere servizi
- Logica di business distribuita
- Meno accoppiamento

### 4. **Resilienza**
- Nessun single point of failure
- Servizi possono fallire indipendentemente
- Compensazione distribuita

## Svantaggi del Pattern

### 1. **Complessità**
- Difficile tracciare il flusso completo
- Debugging complesso
- Gestione dello stato distribuito

### 2. **Consistenza**
- Eventuale consistenza dei dati
- Possibili race conditions
- Gestione degli eventi duplicati

### 3. **Testing**
- Test di integrazione complessi
- Difficile simulare scenari di fallimento
- Test end-to-end complessi

## Configurazione

### 1. **Event Bus Configuration**
```php
// config/event-bus.php
return [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => 'events',
    'retry_after' => 90,
];
```

### 2. **Saga Configuration**
```php
// config/saga.php
return [
    'timeout' => 300, // 5 minuti
    'retry_attempts' => 3,
    'retry_delay' => 60, // 1 minuto
    'compensation_timeout' => 600, // 10 minuti
];
```

## Note Importanti

- Gli eventi devono essere idempotenti
- Implementare deduplicazione degli eventi
- Gestire gli eventi duplicati
- Monitorare il flusso degli eventi
- Testare scenari di fallimento

## Esempi di Utilizzo

### 1. **E-commerce**
- Creazione ordine con pagamento e inventario
- Cancellazione ordine con rimborso
- Aggiornamento ordine con modifiche

### 2. **Sistema di Prenotazioni**
- Prenotazione con pagamento e conferma
- Cancellazione con rimborso
- Modifica prenotazione

### 3. **Sistema di Iscrizioni**
- Iscrizione con pagamento e conferma
- Cancellazione con rimborso
- Trasferimento tra corsi

## Differenze con Saga Orchestration

| Aspetto | Saga Orchestration | Saga Choreography |
|---------|-------------------|-------------------|
| **Coordinamento** | Orchestratore centrale | Eventi distribuiti |
| **Accoppiamento** | Alto (orchestratore) | Basso (eventi) |
| **Scalabilità** | Limitata | Alta |
| **Resilienza** | Single point of failure | Distribuita |
| **Complessità** | Media | Alta |
| **Debugging** | Facile | Difficile |
| **Testing** | Medio | Complesso |
