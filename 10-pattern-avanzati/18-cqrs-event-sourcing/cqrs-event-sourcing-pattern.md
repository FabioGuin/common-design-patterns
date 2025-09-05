# CQRS + Event Sourcing Pattern

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

Il CQRS + Event Sourcing Pattern combina due pattern potenti: separa le operazioni di lettura (Query) da quelle di scrittura (Command) e memorizza tutti i cambiamenti come una sequenza di eventi immutabili. È come avere un "sistema di audit completo" che traccia ogni modifica e permette di ricostruire lo stato in qualsiasi momento.

Pensa a un sistema bancario: ogni prelievo, deposito e trasferimento viene registrato come evento. Quando vuoi vedere il saldo, il sistema ricostruisce lo stato attuale sommando tutti gli eventi. Quando vuoi fare un'operazione, viene registrato un nuovo evento.

## Perché ti serve

Immagina un'applicazione che deve:
- Tracciare ogni modifica ai dati
- Permettere audit completi
- Gestire operazioni complesse
- Supportare rollback e time travel
- Scalare letture e scritture indipendentemente
- Mantenere coerenza dei dati

Senza CQRS + Event Sourcing:
- Perdi la storia delle modifiche
- Non puoi fare audit completi
- Le operazioni complesse sono difficili da gestire
- Non puoi tornare indietro nel tempo
- Letture e scritture sono accoppiate
- La coerenza dei dati è difficile da mantenere

Con CQRS + Event Sourcing:
- Ogni modifica è tracciata e immutabile
- Audit completo di tutte le operazioni
- Operazioni complesse gestite come eventi
- Time travel e rollback possibili
- Letture e scritture scalano indipendentemente
- Coerenza garantita attraverso gli eventi

## Come funziona

1. **Separazione CQRS**: Commands per scrivere, Queries per leggere
2. **Event Sourcing**: Ogni modifica diventa un evento immutabile
3. **Event Store**: Database dedicato per memorizzare gli eventi
4. **Projections**: Viste materializzate per le query
5. **Replay**: Ricostruzione dello stato da eventi
6. **Sincronizzazione**: Aggiornamento delle projection quando arrivano eventi

## Schema visivo

```
Command Side:
User → Command → Event → Event Store
    ↓
Projection Updates

Query Side:
User → Query → Projection (Read Model)
    ↓
Fast Response

Event Flow:
Command → Event → Event Store → Projection
    ↓
Audit Trail + State Reconstruction
```

## Quando usarlo

Usa il CQRS + Event Sourcing Pattern quando:
- Hai bisogno di audit completo delle operazioni
- Le operazioni sono complesse e devono essere tracciabili
- Vuoi scalare letture e scritture indipendentemente
- Hai bisogno di time travel o rollback
- I dati cambiano frequentemente ma le query sono complesse
- La coerenza eventuale è accettabile

**NON usarlo quando:**
- Hai operazioni semplici e lineari
- Non hai bisogno di audit o tracciamento
- Le performance sono critiche e non puoi tollerare latenza
- Non hai risorse per gestire la complessità
- I dati sono principalmente statici
- La coerenza immediata è critica

## Pro e contro

**I vantaggi:**
- Audit completo e tracciabilità totale
- Scalabilità indipendente di letture e scritture
- Time travel e rollback possibili
- Coerenza eventuale garantita
- Flessibilità nelle query
- Resilienza ai fallimenti

**Gli svantaggi:**
- Complessità di implementazione elevata
- Overhead di storage per gli eventi
- Coerenza eventuale invece che immediata
- Debugging più difficile
- Curva di apprendimento ripida
- Possibili problemi di performance

## Esempi di codice

### Pseudocodice
```
// Command Side
class CreateOrderCommand {
    function execute(orderData) {
        event = new OrderCreatedEvent(orderData)
        eventStore.append(event)
        return event.id
    }
}

class OrderCreatedEvent {
    constructor(orderData) {
        this.id = generateId()
        this.type = 'OrderCreated'
        this.data = orderData
        this.timestamp = now()
    }
}

// Query Side
class OrderQuery {
    function getOrder(id) {
        return projection.getOrder(id)
    }
}

class OrderProjection {
    function handle(event) {
        if (event.type == 'OrderCreated') {
            this.orders[event.id] = event.data
        }
    }
}

// Event Store
class EventStore {
    function append(event) {
        this.events.push(event)
        this.notifyProjections(event)
    }
    
    function getEvents(aggregateId) {
        return this.events.filter(e => e.aggregateId == aggregateId)
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema E-commerce CQRS + Event Sourcing](./esempio-completo/)** - Gestione ordini con audit completo

L'esempio include:
- Commands per creare e modificare ordini
- Events per tracciare ogni modifica
- Event Store per memorizzare gli eventi
- Projections per le query veloci
- Sistema di audit e time travel
- Interfaccia web per testare le funzionalità

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[CQRS Pattern](./05-cqrs/cqrs-pattern.md)** - Separazione di Command e Query
- **[Event Sourcing Pattern](./06-event-sourcing/event-sourcing-pattern.md)** - Memorizzazione eventi immutabili
- **[Materialized View Pattern](./17-materialized-view/materialized-view-pattern.md)** - Viste pre-calcolate per query
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Sistemi bancari**: Tracciamento di ogni transazione
- **E-commerce**: Gestione ordini con audit completo
- **Sistemi di fatturazione**: Tracciamento di ogni modifica
- **Sistemi di inventory**: Gestione scorte con storia completa
- **Sistemi di ticketing**: Tracciamento di ogni stato
- **Sistemi di gaming**: Salvataggio di ogni azione del giocatore

## Anti-pattern

**Cosa NON fare:**
- Usare CQRS + Event Sourcing per operazioni semplici
- Non gestire la coerenza eventuale
- Creare eventi troppo granulari o troppo grossi
- Non implementare meccanismi di replay
- Ignorare la gestione degli errori negli eventi
- Non ottimizzare le projection per le query

## Troubleshooting

### Problemi comuni
- **Eventi duplicati**: Implementa idempotenza negli handler
- **Projection non aggiornate**: Verifica che gli eventi vengano processati
- **Performance lente**: Ottimizza le projection e l'event store
- **Coerenza eventuale**: Implementa meccanismi di sincronizzazione
- **Storage pieno**: Implementa strategie di archiviazione eventi

### Debug e monitoring
- Monitora la latenza tra eventi e projection
- Traccia il numero di eventi per aggregate
- Misura le performance delle query
- Controlla la coerenza tra eventi e projection
- Implementa alert per eventi non processati

## Performance e considerazioni

### Impatto sulle risorse
- **Storage**: Eventi immutabili occupano spazio crescente
- **CPU**: Overhead per processing eventi e projection
- **I/O**: Event Store e projection richiedono storage dedicato

### Scalabilità
- **Carico basso**: Performance accettabili con overhead minimo
- **Carico medio**: Buone performance con projection ottimizzate
- **Carico alto**: Possibili colli di bottiglia nell'event store

### Colli di bottiglia
- **Event Store**: Può diventare un collo di bottiglia
- **Projection**: Possono essere lente da aggiornare
- **Replay**: Può essere costoso per aggregate con molti eventi
- **Coerenza**: La coerenza eventuale può causare inconsistenze temporanee

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Events](https://laravel.com/docs/events) - Sistema eventi integrato
- [Laravel Queues](https://laravel.com/docs/queues) - Code per processing asincrono

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Event Sourcing Patterns](https://martinfowler.com/eaaDev/EventSourcing.html) - Martin Fowler
- [CQRS Patterns](https://martinfowler.com/bliki/CQRS.html) - Martin Fowler

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
