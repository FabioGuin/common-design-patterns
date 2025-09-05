# Inbox Pattern - Esempio Completo

## Panoramica

Questo esempio dimostra l'implementazione del **Inbox Pattern** in Laravel, un pattern fondamentale per garantire la ricezione affidabile di eventi in sistemi distribuiti.

## Cosa fa l'Inbox Pattern

L'Inbox Pattern risolve il problema della **duplicazione di eventi** in sistemi distribuiti. Quando ricevi un evento:
1. Lo inserisci in una tabella "inbox" con un ID univoco
2. Processi l'evento solo se non è già stato processato
3. Marca l'evento come processato per evitare duplicazioni

## Come funziona

1. **Ricevi un evento** e inseriscilo nell'inbox con un ID univoco
2. **Verifica se è già stato processato** controllando l'ID
3. **Processa l'evento** solo se è nuovo
4. **Marca come processato** per evitare riprocessamenti
5. **Gestisci i retry** in caso di fallimento

## Struttura dell'Esempio

```
esempio-completo/
├── README.md                           # Questa guida
├── composer.json                       # Dipendenze Laravel 11
├── app/
│   ├── Services/
│   │   ├── InboxService.php           # Servizio principale per gestire l'inbox
│   │   ├── EventConsumerService.php   # Servizio per consumare eventi
│   │   └── OrderProcessingService.php # Servizio di business che usa l'inbox
│   ├── Models/
│   │   ├── InboxEvent.php             # Modello per gli eventi in inbox
│   │   └── Order.php                  # Modello Order di esempio
│   ├── Jobs/
│   │   └── ProcessInboxEventsJob.php  # Job per processare eventi inbox
│   ├── Http/Controllers/
│   │   └── InboxController.php        # Controller per testare il pattern
│   └── Console/Commands/
│       └── ProcessInboxCommand.php    # Comando Artisan per processare inbox
├── resources/views/
│   └── inbox/
│       └── example.blade.php          # Interfaccia web per testare
├── routes/
│   └── web.php                        # Route per l'esempio
├── database/migrations/
│   ├── create_orders_table.php        # Tabella orders
│   └── create_inbox_events_table.php  # Tabella inbox_events
└── tests/
    └── Feature/
        └── InboxTest.php              # Test per il pattern
```

## Caratteristiche Principali

### 1. Idempotenza
- Ogni evento ha un ID univoco
- Gli eventi vengono processati solo una volta
- Sistema di tracking per evitare duplicazioni

### 2. Gestione Retry
- Retry automatico in caso di fallimento
- Backoff esponenziale per evitare sovraccarico
- Marcatura definitiva dopo troppi tentativi

### 3. Monitoring
- Interfaccia web per monitorare lo stato dell'inbox
- Log dettagliati per debugging
- Statistiche in tempo reale

### 4. Resilienza
- Gestione di eventi stuck
- Cleanup automatico di eventi vecchi
- Recovery da errori di sistema

## Come Testare

### 1. Setup Iniziale
```bash
composer install
php artisan migrate
php artisan queue:work
```

### 2. Test via Web
- Vai su `/inbox` per vedere l'interfaccia
- Simula l'arrivo di eventi e osserva come vengono gestiti

### 3. Test via API
```bash
# Simula l'arrivo di un evento
curl -X POST http://localhost:8000/inbox/events \
  -H "Content-Type: application/json" \
  -d '{"event_id": "unique-123", "event_type": "OrderCreated", "data": {"order_id": 1}}'

# Verifica lo stato dell'inbox
curl http://localhost:8000/inbox/status
```

### 4. Test via Comando
```bash
# Processa manualmente gli eventi inbox
php artisan inbox:process
```

## Scenari di Test

### Scenario 1: Evento Nuovo
- Ricevi un evento con ID univoco
- L'evento viene inserito nell'inbox
- Viene processato e marcato come completato

### Scenario 2: Evento Duplicato
- Ricevi lo stesso evento due volte
- Il secondo viene ignorato (idempotenza)
- Solo il primo viene processato

### Scenario 3: Fallimento Processing
- Simula un fallimento durante il processing
- L'evento viene marcato per retry
- Dopo alcuni tentativi, viene marcato come fallito

### Scenario 4: Evento Stuck
- Simula un evento in processing da troppo tempo
- Viene ripristinato e ritentato
- Gestione automatica dei timeout

## Vantaggi del Pattern

- **Idempotenza**: Garantisce che gli eventi siano processati una sola volta
- **Affidabilità**: Gestisce automaticamente i fallimenti e i retry
- **Scalabilità**: Il processing degli eventi è asincrono
- **Monitoring**: Facile tracciare lo stato degli eventi

## Considerazioni

- **Storage**: La tabella inbox cresce nel tempo (serve pulizia periodica)
- **Latency**: C'è un piccolo delay tra ricezione e processing
- **Complexity**: Aggiunge complessità al sistema

## Pattern Correlati

- **Outbox Pattern**: Per gestire eventi in uscita
- **Saga Pattern**: Per transazioni distribuite complesse
- **Event Sourcing**: Per audit trail completo

Questo esempio ti mostra come implementare l'Inbox Pattern in Laravel per garantire la ricezione affidabile di eventi in sistemi distribuiti.
