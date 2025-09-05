# Event Sourcing Pattern

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

Event Sourcing memorizza tutti i cambiamenti di stato del tuo sistema come una sequenza di eventi, invece di salvare solo lo stato finale. È come avere un registratore di cassa che tiene traccia di ogni singola transazione, non solo del saldo finale.

Pensa a un conto bancario: invece di salvare solo "saldo: 1000€", salvi "deposito +500€, prelievo -200€, deposito +700€". Così puoi ricostruire lo stato in qualsiasi momento e sapere esattamente cosa è successo.

## Perché ti serve

Immagina un sistema di e-commerce dove un ordine può cambiare stato molte volte: creato, pagato, spedito, consegnato, rimborsato. Con un approccio tradizionale, perdi la storia dei cambiamenti.

Con Event Sourcing:
- **Audit completo**: Sai esattamente quando e perché è cambiato ogni stato
- **Debugging**: Puoi riprodurre qualsiasi situazione passata
- **Compliance**: Hai una traccia completa per requisiti legali
- **Analisi**: Puoi analizzare i pattern di comportamento
- **Ripristino**: Puoi tornare a qualsiasi stato precedente

## Come funziona

Event Sourcing funziona in tre fasi:

1. **Eventi**: Ogni cambiamento diventa un evento immutabile
2. **Event Store**: Tutti gli eventi vengono salvati in sequenza
3. **Proiezioni**: Gli eventi vengono applicati per ricostruire lo stato

Quando hai bisogno dello stato attuale, "riproduci" tutti gli eventi dall'inizio. Quando hai bisogno di una versione passata, riproduci solo gli eventi fino a quel punto.

## Schema visivo

```
Sistema Tradizionale:
Stato Attuale → [Database] → Stato Attuale

Event Sourcing:
Evento 1 → Evento 2 → Evento 3 → Evento 4
   ↓         ↓         ↓         ↓
[Event Store] → [Proiezioni] → [Stato Attuale]
   ↓
[Audit Trail] → [Analisi] → [Debug]
```

**Flusso tipico:**
1. **Comando**: Client invia comando → Validazione
2. **Evento**: Comando genera evento → Event Store
3. **Proiezione**: Evento aggiorna proiezioni → Stato attuale
4. **Query**: Client richiede stato → Proiezione aggiornata

**Ricostruzione stato:**
1. **Eventi**: Carica tutti gli eventi per un aggregato
2. **Applicazione**: Applica eventi in sequenza
3. **Stato**: Ricostruisci stato finale

## Quando usarlo

Usa Event Sourcing quando:
- Hai bisogno di audit trail completo e immutabile
- Il tuo dominio ha logica complessa con molti stati
- Devi supportare funzionalità di "undo" o "ripristino"
- Hai requisiti di compliance e tracciabilità
- Vuoi analizzare il comportamento degli utenti nel tempo
- Hai bisogno di debugging avanzato e riproduzione di scenari
- Stai costruendo sistemi finanziari o critici

**NON usarlo quando:**
- Il tuo sistema è semplice e non ha logica complessa
- Non hai bisogno di audit trail o tracciabilità
- Le performance sono critiche e non puoi permetterti overhead
- Il team non ha esperienza con pattern complessi
- Hai solo operazioni CRUD semplici senza business logic

## Pro e contro

**I vantaggi:**
- **Audit completo**: Traccia immutabile di tutti i cambiamenti
- **Debugging**: Puoi riprodurre qualsiasi situazione passata
- **Flessibilità**: Puoi creare nuove proiezioni senza modificare il codice esistente
- **Compliance**: Facilita i requisiti di tracciabilità e audit
- **Analisi**: Dati ricchi per analisi e business intelligence
- **Ripristino**: Puoi tornare a qualsiasi stato precedente
- **Scalabilità**: Eventi possono essere processati in modo asincrono

**Gli svantaggi:**
- **Complessità**: Aumenta significativamente la complessità del sistema
- **Performance**: Ricostruire lo stato può essere costoso
- **Storage**: Richiede più spazio per memorizzare tutti gli eventi
- **Learning curve**: Richiede conoscenze avanzate per implementarlo bene
- **Debugging**: Può essere difficile debuggare problemi complessi
- **Migrazione**: Difficile migrare da sistemi esistenti

## Esempi di codice

### Pseudocodice
```
// Event Store
class EventStore {
    saveEvents(aggregateId, events, expectedVersion) {
        // Salva eventi con controllo di concorrenza
    }
    
    getEvents(aggregateId, fromVersion = 0) {
        // Carica tutti gli eventi per un aggregato
    }
}

// Aggregate Root
class Order {
    private events = []
    private version = 0
    
    static fromHistory(events) {
        order = new Order()
        for event in events {
            order.apply(event)
        }
        return order
    }
    
    createOrder(command) {
        event = new OrderCreated(command.orderId, command.customerId)
        this.apply(event)
    }
    
    apply(event) {
        this.events.push(event)
        this.version++
        // Aggiorna stato interno
    }
}

// Event Handler
class OrderProjection {
    handle(OrderCreated event) {
        // Aggiorna proiezione per query
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema Ordini Event Sourcing](./esempio-completo/)** - Sistema e-commerce con tracciamento completo eventi

L'esempio include:
- Event Store per memorizzare eventi
- Aggregate Root per gestire logica business
- Proiezioni per ricostruire stato
- Interfaccia per visualizzare eventi e stato
- Sistema di audit trail completo
- Funzionalità di ripristino e debugging

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[CQRS](./05-cqrs/cqrs-pattern.md)** - Separazione command/query che si integra perfettamente con Event Sourcing
- **[Domain Event](./04-domain-event/domain-event-pattern.md)** - Eventi di dominio per comunicazione
- **[Aggregate Root](./03-aggregate-root/aggregate-root-pattern.md)** - Gestione consistenza e invarianti
- **[Repository Pattern](../04-pattern-architetturali/02-repository/repository-pattern.md)** - Astrazione accesso dati

### Principi e Metodologie

- **[SOLID Principles](../12-pattern-metodologie-concettuali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../12-pattern-metodologie-concettuali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../12-pattern-metodologie-concettuali/09-tdd/tdd.md)** - Test-Driven Development
- **[Event-Driven Architecture](../12-pattern-metodologie-concettuali/15-event-driven-architecture/event-driven-architecture.md)** - Architettura basata su eventi

## Esempi di uso reale

- **Banking**: Sistemi bancari per tracciare ogni transazione e movimento
- **E-commerce**: Amazon per tracciare cambiamenti stato ordini e inventario
- **Gaming**: Giochi online per salvare progressi e azioni dei giocatori
- **Healthcare**: Sistemi medici per tracciare modifiche cartelle cliniche
- **Trading**: Piattaforme di trading per audit completo delle operazioni

## Anti-pattern

**Cosa NON fare:**
- **Eventi troppo granulari**: Non creare eventi per ogni piccolo cambiamento
- **Eventi accoppiati**: Non creare eventi che dipendono l'uno dall'altro
- **Stato negli eventi**: Non memorizzare stato derivato negli eventi
- **Eventi immutabili**: Non modificare mai eventi esistenti
- **Over-engineering**: Non usare Event Sourcing per sistemi semplici

## Troubleshooting

### Problemi comuni
- **Eventi duplicati**: Implementa idempotenza e controllo di concorrenza
- **Performance lente**: Ottimizza le proiezioni e considera snapshot
- **Eventi persi**: Implementa retry logic e dead letter queue
- **Versioning**: Gestisci correttamente il versioning degli eventi
- **Storage growth**: Implementa archiviazione e cleanup degli eventi vecchi

### Debug e monitoring
- **Event replay**: Strumenti per riprodurre eventi e debuggare
- **Event store monitoring**: Monitora dimensioni e performance dell'event store
- **Projection lag**: Traccia il ritardo delle proiezioni
- **Event validation**: Valida la correttezza degli eventi

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Più memoria per memorizzare tutti gli eventi, ma proiezioni ottimizzate
- **CPU**: Overhead per ricostruire stato, ma processamento asincrono degli eventi
- **I/O**: Più operazioni di scrittura per eventi, ma letture ottimizzate dalle proiezioni

### Scalabilità
- **Carico basso**: Overhead non giustificato per sistemi semplici
- **Carico medio**: Benefici iniziano a manifestarsi con audit requirements
- **Carico alto**: Eccellente scalabilità con processamento asincrono

### Colli di bottiglia
- **Event Store**: Può diventare collo di bottiglia se non ottimizzato
- **Proiezioni**: Possono essere lente se non ottimizzate
- **Ricostruzione stato**: Può essere costosa per aggregati con molti eventi

## Risorse utili

### Documentazione ufficiale
- [Event Sourcing - Martin Fowler](https://martinfowler.com/eaaDev/EventSourcing.html) - Articolo fondamentale
- [Event Sourcing Pattern - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/event-sourcing) - Documentazione Microsoft

### Laravel specifico
- [Laravel Event Sourcing Package](https://github.com/spatie/laravel-event-sourcing) - Package Spatie per Event Sourcing
- [Laravel Event Bus](https://laravel.com/docs/events) - Sistema eventi di Laravel

### Esempi e tutorial
- [Event Sourcing in PHP](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico PHP
- [Event Sourcing Tutorial](https://eventstore.com/learn/event-sourcing/) - Tutorial completo

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
