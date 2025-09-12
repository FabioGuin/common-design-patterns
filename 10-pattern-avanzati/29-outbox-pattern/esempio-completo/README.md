# Outbox Pattern - Esempio Completo

## Panoramica

Questo esempio dimostra l'implementazione del **Outbox Pattern** in Laravel, un pattern fondamentale per garantire la pubblicazione affidabile di eventi in sistemi distribuiti.

## Cosa fa l'Outbox Pattern

L'Outbox Pattern risolve il problema della **dual-write** in sistemi distribuiti, dove devi:
1. Aggiornare il database
2. Pubblicare un evento

Il problema è che se uno dei due fallisce, hai inconsistenze. L'Outbox Pattern risolve questo usando una transazione atomica.

## Come funziona

1. **Scrivi nel database** e **nella tabella outbox** nella stessa transazione
2. Un **processo separato** legge dalla tabella outbox e pubblica gli eventi
3. Se la pubblicazione fallisce, il processo riprova
4. Una volta pubblicato con successo, l'evento viene rimosso dall'outbox

## Struttura dell'Esempio

```
esempio-completo/
├── README.md                           # Questa guida
├── composer.json                       # Dipendenze Laravel 11
├── app/
│   ├── Services/
│   │   ├── OutboxService.php          # Servizio principale per gestire l'outbox
│   │   ├── EventPublisherService.php  # Servizio per pubblicare eventi
│   │   └── OrderService.php           # Servizio di business che usa l'outbox
│   ├── Models/
│   │   ├── OutboxEvent.php            # Modello per gli eventi in outbox
│   │   └── Order.php                  # Modello Order di esempio
│   ├── Jobs/
│   │   └── ProcessOutboxEventsJob.php # Job per processare eventi outbox
│   ├── Http/Controllers/
│   │   └── OutboxController.php       # Controller per testare il pattern
│   └── Console/Commands/
│       └── ProcessOutboxCommand.php   # Comando Artisan per processare outbox
├── resources/views/
│   └── outbox/
│       └── example.blade.php          # Interfaccia web per testare
├── routes/
│   └── web.php                        # Route per l'esempio
├── database/migrations/
│   ├── create_orders_table.php        # Tabella orders
│   └── create_outbox_events_table.php # Tabella outbox_events
└── tests/
    └── Feature/
        └── OutboxTest.php             # Test per il pattern
```

## Caratteristiche Principali

### 1. Transazione Atomica
- Gli aggiornamenti al database e l'inserimento nell'outbox avvengono nella stessa transazione
- Se uno fallisce, tutto viene rollback

### 2. Pubblicazione Asincrona
- Un job separato processa gli eventi dall'outbox
- Gestisce automaticamente i retry in caso di fallimento

### 3. Idempotenza
- Gli eventi sono idempotenti per evitare duplicazioni
- Sistema di tracking per evitare riprocessamenti

### 4. Monitoring
- Interfaccia web per monitorare lo stato dell'outbox
- Log dettagliati per debugging

## Come Testare

### 1. Setup Iniziale
```bash
composer install
php artisan migrate
php artisan queue:work
```

### 2. Test via Web
- Vai su `/outbox` per vedere l'interfaccia
- Crea un ordine e osserva come viene gestito l'outbox

### 3. Test via API
```bash
# Crea un ordine
curl -X POST http://localhost:8000/outbox/orders \
  -H "Content-Type: application/json" \
  -d '{"customer_name": "Mario Rossi", "amount": 100.50}'

# Verifica lo stato dell'outbox
curl http://localhost:8000/outbox/status
```

### 4. Test via Comando
```bash
# Processa manualmente gli eventi outbox
php artisan outbox:process
```

## Scenari di Test

### Scenario 1: Successo Completo
- Crea un ordine
- L'evento viene inserito nell'outbox
- Il job processa e pubblica l'evento
- L'evento viene rimosso dall'outbox

### Scenario 2: Fallimento Pubblicazione
- Simula un fallimento nel servizio di pubblicazione
- Il job riprova automaticamente
- Dopo alcuni tentativi, l'evento viene marcato come fallito

### Scenario 3: Rollback Transazione
- Simula un errore durante la creazione dell'ordine
- La transazione viene rollback
- Nessun evento viene inserito nell'outbox

## Vantaggi del Pattern

- **Consistenza**: Garantisce che database e eventi siano sempre sincronizzati
- **Affidabilità**: Gestisce automaticamente i fallimenti di pubblicazione
- **Scalabilità**: Il processing degli eventi è asincrono
- **Monitoring**: Facile tracciare lo stato degli eventi

## Considerazioni

- **Latency**: C'è un piccolo delay tra l'aggiornamento e la pubblicazione
- **Storage**: La tabella outbox cresce nel tempo (serve pulizia periodica)
- **Complexity**: Aggiunge complessità al sistema

## Pattern Correlati

- **Inbox Pattern**: Per gestire eventi in arrivo
- **Saga Pattern**: Per transazioni distribuite complesse
- **Event Sourcing**: Per audit trail completo

Questo esempio ti mostra come implementare l'Outbox Pattern in Laravel per garantire la pubblicazione affidabile di eventi in sistemi distribuiti.
