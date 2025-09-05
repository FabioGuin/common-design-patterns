# Esempio Saga Orchestration Pattern

## Descrizione

Questo esempio dimostra il **Saga Orchestration Pattern** in un'applicazione Laravel. Il pattern gestisce transazioni distribuite complesse utilizzando un orchestratore centrale che coordina i passaggi di una saga, garantendo la consistenza dei dati attraverso multiple operazioni.

## Struttura dell'Esempio

```
esempio-completo/
├── README.md
├── app/
│   ├── Services/
│   │   ├── SagaOrchestratorService.php
│   │   ├── UserService.php
│   │   ├── ProductService.php
│   │   ├── OrderService.php
│   │   ├── PaymentService.php
│   │   ├── InventoryService.php
│   │   └── NotificationService.php
│   ├── Http/Controllers/
│   │   ├── SagaOrchestratorController.php
│   │   ├── UserController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   └── PaymentController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   ├── Payment.php
│   │   ├── Saga.php
│   │   └── SagaStep.php
│   └── Jobs/
│       ├── ProcessSagaStepJob.php
│       ├── CompensateSagaStepJob.php
│       └── CompleteSagaJob.php
├── resources/views/
│   └── saga-orchestration/
│       └── example.blade.php
├── routes/
│   └── web.php
├── tests/Feature/
│   └── SagaOrchestrationTest.php
```

## Caratteristiche dell'Esempio

### 1. **Saga Orchestrator**
- Coordina i passaggi di una saga
- Gestisce la compensazione in caso di errori
- Mantiene lo stato della saga
- Implementa retry e timeout

### 2. **Servizi Distribuiti**
- UserService: Gestione utenti
- ProductService: Gestione prodotti
- OrderService: Gestione ordini
- PaymentService: Gestione pagamenti
- InventoryService: Gestione inventario
- NotificationService: Invio notifiche

### 3. **Modelli Saga**
- Saga: Rappresenta una saga completa
- SagaStep: Rappresenta un singolo passaggio
- Tracking dello stato e dei risultati

### 4. **Jobs Asincroni**
- ProcessSagaStepJob: Esegue un passaggio
- CompensateSagaStepJob: Compensa un passaggio
- CompleteSagaJob: Completa la saga

## Come Eseguire l'Esempio

### 1. **Test Standalone**
```bash
```

### 2. **Test Laravel**
```bash
php artisan test tests/Feature/SagaOrchestrationTest.php
```

### 3. **Interfaccia Web**
```bash
php artisan serve
# Visita: http://localhost:8000/saga-orchestration/example
```

## Esempio di Saga: Creazione Ordine

### 1. **Passaggi della Saga**
1. **ValidateUser**: Verifica che l'utente esista
2. **ReserveInventory**: Riserva l'inventario
3. **CreateOrder**: Crea l'ordine
4. **ProcessPayment**: Processa il pagamento
5. **SendNotification**: Invia notifica di conferma

### 2. **Compensazione**
- Se un passaggio fallisce, tutti i passaggi precedenti vengono compensati
- Le compensazioni sono idempotenti e possono essere ri-eseguite

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
- `sagas` - Saga in esecuzione
- `saga_steps` - Passaggi delle saga

## Vantaggi del Pattern

### 1. **Consistenza Distribuita**
- Garantisce la consistenza dei dati attraverso servizi distribuiti
- Gestisce transazioni complesse senza lock distribuiti

### 2. **Flessibilità**
- Facile aggiungere/rimuovere passaggi
- Logica di business centralizzata nell'orchestratore

### 3. **Resilienza**
- Gestione automatica degli errori
- Compensazione automatica
- Retry e timeout configurabili

### 4. **Monitoraggio**
- Tracking completo dello stato della saga
- Log dettagliati per debugging
- Metriche di performance

## Svantaggi del Pattern

### 1. **Complessità**
- Pattern complesso da implementare
- Richiede gestione dello stato distribuito

### 2. **Single Point of Failure**
- L'orchestratore è un punto di fallimento
- Richiede alta disponibilità

### 3. **Performance**
- Overhead per la coordinazione
- Possibili ritardi per la compensazione

## Configurazione

### 1. **Queue Configuration**
```php
// config/queue.php
'connections' => [
    'saga' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'saga',
        'retry_after' => 90,
    ],
],
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

- Le saga sono idempotenti e possono essere ri-eseguite
- Le compensazioni devono essere idempotenti
- Implementare monitoring e alerting per le saga
- Testare scenari di fallimento e recupero
- Considerare la performance per saga con molti passaggi

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
