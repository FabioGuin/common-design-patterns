# Domain Event Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Correlati](#correlati)
- [Esempi di uso reale](#esempi-di-uso-reale)

### Cosa Evitare
- [Anti-pattern](#anti-pattern)
- [Troubleshooting](#troubleshooting)

### Implementazione Pratica
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

### Considerazioni Tecniche
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa

Il Domain Event Pattern ti permette di notificare altri sistemi quando succede qualcosa di importante nel tuo dominio. Invece di chiamare direttamente altri servizi, emetti un evento e lasci che chi è interessato se lo gestisca.

Pensa a un ordine e-commerce. Quando l'ordine viene confermato, invece di chiamare direttamente il servizio di notifica, il servizio di inventario e il servizio di fatturazione, emetti un evento "OrderConfirmed" e lasci che questi servizi si iscrivano all'evento.

## Perché ti serve

Immagina di gestire un ordine e-commerce. Senza Domain Events:

- I servizi sono strettamente accoppiati (ordine deve conoscere notifiche, inventario, fatturazione)
- È difficile aggiungere nuovi servizi (devi modificare il codice dell'ordine)
- I test sono complessi (devi mockare tutti i servizi)
- La logica di business è sparsa in tutto il codice

Con il Domain Event Pattern:
- **Disaccoppiamento**: I servizi non si conoscono tra loro
- **Estensibilità**: Aggiungi nuovi servizi senza modificare il codice esistente
- **Testabilità**: Testi ogni servizio indipendentemente
- **Manutenibilità**: Codice più pulito e organizzato

## Come funziona

1. **Identifica gli eventi**: Trova le azioni importanti nel tuo dominio
2. **Crea le classi evento**: Una classe per ogni tipo di evento
3. **Emiti gli eventi**: Quando succede qualcosa, emetti l'evento
4. **Registra i listener**: I servizi si iscrivono agli eventi che li interessano
5. **Gestisci gli eventi**: I listener processano gli eventi ricevuti

## Schema visivo

```
Order Aggregate
    ├── confirm() → emette OrderConfirmed
    ├── cancel() → emette OrderCancelled
    └── ship() → emette OrderShipped

Event Bus
    ├── OrderConfirmed
    │   ├── NotificationService (invia email)
    │   ├── InventoryService (aggiorna stock)
    │   └── BillingService (crea fattura)
    ├── OrderCancelled
    │   ├── NotificationService (invia email)
    │   └── InventoryService (ripristina stock)
    └── OrderShipped
        ├── NotificationService (invia tracking)
        └── ShippingService (aggiorna stato)
```

## Quando usarlo

Usa il Domain Event Pattern quando:
- Hai servizi che devono reagire a cambiamenti nel dominio
- Vuoi disaccoppiare i servizi tra loro
- Hai bisogno di notificare più sistemi per la stessa azione
- Vuoi rendere il sistema più estensibile e testabile
- Lavori con domini complessi che hanno molte interazioni

**NON usarlo quando:**
- Hai solo operazioni CRUD semplici senza logica di business
- I servizi sono già disaccoppiati e non hanno bisogno di comunicare
- Le performance sono critiche e l'overhead è troppo alto
- Il dominio è troppo semplice per giustificare la complessità

## Pro e contro

**I vantaggi:**
- **Disaccoppiamento**: I servizi non si conoscono tra loro
- **Estensibilità**: Aggiungi nuovi servizi senza modificare il codice esistente
- **Testabilità**: Testi ogni servizio indipendentemente
- **Manutenibilità**: Codice più pulito e organizzato
- **Flessibilità**: Puoi aggiungere/rimuovere listener facilmente

**Gli svantaggi:**
- **Complessità aggiuntiva**: Più classi e logica da gestire
- **Debugging difficile**: È più difficile tracciare il flusso degli eventi
- **Performance**: Overhead per la gestione degli eventi
- **Curva di apprendimento**: I developer devono capire il pattern
- **Overhead**: Più codice per gestire gli eventi

## Esempi di codice

### Pseudocodice

```
// Evento di dominio
class OrderConfirmed {
    public orderId
    public customerId
    public total
    public confirmedAt
    
    constructor(orderId, customerId, total) {
        this.orderId = orderId
        this.customerId = customerId
        this.total = total
        this.confirmedAt = now()
    }
}

// Aggregate Root che emette eventi
class Order {
    private id
    private customerId
    private status
    private total
    private events = []
    
    function confirm() {
        if (this.status !== 'DRAFT') {
            throw new InvalidOperationException('Cannot confirm order')
        }
        
        this.status = 'CONFIRMED'
        this.confirmedAt = now()
        
        // Emetti l'evento invece di chiamare direttamente i servizi
        event = new OrderConfirmed(this.id, this.customerId, this.total)
        this.emit(event)
    }
    
    function emit(event) {
        this.events.push(event)
    }
    
    function getEvents() {
        return this.events
    }
}

// Listener per l'evento
class NotificationService {
    function handle(OrderConfirmed event) {
        // Invia email di conferma
        this.sendConfirmationEmail(event.customerId, event.orderId)
    }
}

class InventoryService {
    function handle(OrderConfirmed event) {
        // Aggiorna lo stock
        this.updateStock(event.orderId)
    }
}

class BillingService {
    function handle(OrderConfirmed event) {
        // Crea la fattura
        this.createInvoice(event.orderId, event.total)
    }
}

// Event Bus per gestire gli eventi
class EventBus {
    private listeners = {}
    
    function subscribe(eventType, listener) {
        if (!this.listeners[eventType]) {
            this.listeners[eventType] = []
        }
        this.listeners[eventType].push(listener)
    }
    
    function publish(event) {
        eventType = event.constructor.name
        if (this.listeners[eventType]) {
            this.listeners[eventType].forEach(listener => {
                listener.handle(event)
            })
        }
    }
}

// Utilizzo
order = new Order('order-1', 'customer-123')
order.addItem('PROD-001', 2, 10.50)
order.confirm()  // Emette OrderConfirmed

// I listener si iscrivono agli eventi
eventBus = new EventBus()
eventBus.subscribe('OrderConfirmed', new NotificationService())
eventBus.subscribe('OrderConfirmed', new InventoryService())
eventBus.subscribe('OrderConfirmed', new BillingService())

// Pubblica gli eventi
order.getEvents().forEach(event => {
    eventBus.publish(event)
})
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Domain Events](./esempio-completo/)** - Sistema e-commerce con eventi di dominio per notificare cambiamenti

L'esempio include:
- Eventi di dominio per ordini, pagamenti e spedizioni
- Event Bus per gestire la pubblicazione e sottoscrizione
- Listener per notifiche, inventario e fatturazione
- Test completi per tutti gli scenari
- Interfaccia web per testare gli eventi

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Aggregate Root](./03-aggregate-root/aggregate-root-pattern.md)** - Spesso usato insieme per emettere eventi
- **[Observer Pattern](../03-pattern-comportamentali/07-observer/observer-pattern.md)** - Pattern base per la gestione degli eventi
- **[Mediator Pattern](../03-pattern-comportamentali/05-mediator/mediator-pattern.md)** - Per coordinare le comunicazioni tra oggetti
- **[Command Pattern](../03-pattern-comportamentali/02-command/command-pattern.md)** - Per gestire i comandi che generano eventi

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **E-commerce**: OrderConfirmed, PaymentProcessed, OrderShipped
- **Banking**: AccountCreated, TransactionProcessed, OverdraftDetected
- **Inventory**: StockUpdated, ProductDiscontinued, LowStockAlert
- **Project Management**: TaskCompleted, ProjectDelayed, MilestoneReached
- **CRM**: LeadConverted, OpportunityClosed, CustomerUpgraded

## Anti-pattern

**Cosa NON fare:**
- Emettere eventi per operazioni CRUD semplici
- Creare eventi troppo generici o troppo specifici
- Dimenticare di gestire gli errori nei listener
- Usare eventi per comunicazioni sincrone critiche
- Creare dipendenze circolari tra eventi

## Troubleshooting

### Problemi comuni

- **Eventi non gestiti**: Verifica che i listener siano registrati correttamente
- **Eventi duplicati**: Assicurati di non emettere lo stesso evento più volte
- **Listener che falliscono**: Gestisci gli errori nei listener per non bloccare gli altri
- **Eventi persi**: Considera la persistenza degli eventi per scenari critici
- **Performance lente**: Ottimizza la gestione degli eventi per scenari ad alto volume

### Debug e monitoring

- **Logging**: Traccia tutti gli eventi emessi e gestiti
- **Monitoring**: Monitora le performance dei listener
- **Testing**: Testa sempre i listener in isolamento
- **Error handling**: Gestisci gli errori nei listener senza bloccare il sistema

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Gli eventi vengono memorizzati temporaneamente
- **CPU**: Overhead per la gestione degli eventi
- **I/O**: Possibili chiamate ai listener

### Scalabilità

- **Carico basso**: Funziona bene con pochi eventi
- **Carico medio**: Considera l'elaborazione asincrona
- **Carico alto**: Potrebbe essere necessario ottimizzare la gestione degli eventi

### Colli di bottiglia

- **Event Bus**: Considera l'elaborazione asincrona per eventi non critici
- **Listener lenti**: Ottimizza i listener o usa code asincrone
- **Eventi frequenti**: Considera il batching per eventi simili

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Events](https://laravel.com/docs/events) - Sistema eventi di Laravel

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html) - DDD e Domain Events
- [Laravel DDD](https://github.com/laravel-ddd/laravel-ddd) - DDD in Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
