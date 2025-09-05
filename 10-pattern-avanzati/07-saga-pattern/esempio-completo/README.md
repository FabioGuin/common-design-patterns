# E-commerce Order Saga

## Panoramica

Questo esempio dimostra l'implementazione del pattern Saga in un sistema e-commerce Laravel. Il sistema gestisce transazioni distribuite tra inventario, pagamenti e notifiche, mantenendo la consistenza attraverso compensazioni automatiche.

## Architettura

### Saga Orchestrator
- **OrderSaga**: Coordina tutte le operazioni dell'ordine
- **SagaState**: Gestisce lo stato della saga
- **CompensationManager**: Gestisce le compensazioni automatiche

### Servizi Distribuiti
- **InventoryService**: Gestisce riserva e rilascio inventario
- **PaymentService**: Processa pagamenti e rimborsi
- **NotificationService**: Invia email e notifiche
- **OrderService**: Gestisce lo stato degli ordini

### Operazioni e Compensazioni
- **Reserve Inventory** → **Release Inventory**
- **Process Payment** → **Refund Payment**
- **Send Confirmation** → **Cancel Confirmation**
- **Update Order** → **Revert Order**

## Struttura del Progetto

```
app/
├── Sagas/              # Saga orchestrators
├── Services/           # Servizi distribuiti
├── Compensations/      # Logica di compensazione
├── Events/             # Eventi per comunicazione
├── Models/             # Modelli per stato
├── Http/Controllers/   # Controllers per testare il pattern
└── Jobs/               # Job per operazioni asincrone
```

## Funzionalità Implementate

### Saga Orchestrator
- ✅ Gestione stato della saga
- ✅ Esecuzione sequenziale delle operazioni
- ✅ Compensazioni automatiche in caso di fallimento
- ✅ Retry logic per operazioni fallite
- ✅ Timeout per evitare saghe bloccate

### Servizi Distribuiti
- ✅ InventoryService con riserva/rilascio
- ✅ PaymentService con pagamento/rimborso
- ✅ NotificationService con invio/cancellazione
- ✅ OrderService con aggiornamento/revert

### Gestione Errori
- ✅ Compensazioni automatiche
- ✅ Retry logic con backoff esponenziale
- ✅ Dead letter queue per operazioni fallite
- ✅ Logging dettagliato per debugging

### Interfaccia
- ✅ Visualizzazione stato saghe
- ✅ Test operazioni e compensazioni
- ✅ Monitoraggio performance
- ✅ Debug tools per troubleshooting

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/saga`
3. **Testa le saghe**:
   - Crea ordini
   - Simula fallimenti
   - Osserva compensazioni
4. **Testa i servizi**:
   - Verifica inventario
   - Controlla pagamenti
   - Monitora notifiche

## Database

Il sistema usa tabelle ottimizzate per le saghe:
- **sagas**: Stato delle saghe in esecuzione
- **saga_steps**: Step eseguiti e compensazioni
- **orders**: Ordini con stato aggiornato
- **inventory_reservations**: Riserve inventario
- **payments**: Transazioni di pagamento

## Configurazione

Copia `env.example` in `.env` e configura il database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saga_pattern
DB_USERNAME=root
DB_PASSWORD=

# Queue Configuration
QUEUE_CONNECTION=database
```

## Migrazioni

```bash
php artisan migrate
```

## Pattern Implementato

Questo esempio dimostra:
- **Saga Orchestration**: Coordinamento centrale delle operazioni
- **Distributed Transactions**: Transazioni tra servizi diversi
- **Compensation Pattern**: Rollback automatico in caso di fallimento
- **Event-Driven**: Comunicazione asincrona tra servizi
- **Resilience**: Gestione errori e retry logic

## Note Tecniche

- **Laravel 11**: Framework aggiornato
- **Queue System**: Operazioni asincrone per performance
- **Event System**: Comunicazione tra servizi
- **State Management**: Gestione stato delle saghe
- **Compensation Logic**: Rollback automatico

## Vantaggi Dimostrati

- **Consistenza Distribuita**: Mantiene consistenza tra servizi
- **Resilienza**: Gestisce fallimenti automaticamente
- **Scalabilità**: Ogni servizio scala indipendentemente
- **Audit**: Traccia completa di operazioni e compensazioni
- **Flessibilità**: Può gestire flussi complessi e non lineari
