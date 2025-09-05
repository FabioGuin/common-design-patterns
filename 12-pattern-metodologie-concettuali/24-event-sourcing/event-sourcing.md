# Event Sourcing

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

Event Sourcing è un pattern architetturale che immagazzina lo stato di un'applicazione come una sequenza di eventi immutabili. Invece di salvare lo stato corrente, si salvano tutti gli eventi che hanno portato a quello stato, permettendo di ricostruire lo stato in qualsiasi momento e di analizzare la storia delle modifiche.

## Perché ti serve

Event Sourcing ti aiuta a:
- **Tracciare** la storia completa delle modifiche
- **Ricostruire** lo stato in qualsiasi momento
- **Analizzare** i pattern di utilizzo
- **Implementare** audit trail completo
- **Supportare** time travel e debugging
- **Facilitare** l'integrazione tra sistemi

## Come funziona

### Concetti Fondamentali

**Events (Eventi)**
- Rappresentano qualcosa che è accaduto
- Immutabili e immutabili nel tempo
- Contengono tutti i dati necessari
- Esempio: UserRegistered, OrderCreated, PaymentProcessed

**Event Store (Store degli Eventi)**
- Database ottimizzato per eventi
- Append-only (solo aggiunta)
- Versioning e concurrency
- Esempio: EventStore, Apache Kafka

**Aggregates (Aggregati)**
- Insieme di eventi correlati
- Invarianti di business
- Transazioni atomiche
- Esempio: User, Order, Product

**Projections (Proiezioni)**
- Viste denormalizzate degli eventi
- Ottimizzate per query specifiche
- Aggiornate in tempo reale
- Esempio: UserView, OrderSummary

### Flusso di Event Sourcing

**1. Command (Comando)**
- Richiesta di esecuzione di un'azione
- Validazione e autorizzazione
- Generazione di eventi
- Esempio: RegisterUser, CreateOrder

**2. Event Generation (Generazione Eventi)**
- Creazione di eventi immutabili
- Validazione delle regole di business
- Persistenza nel event store
- Esempio: UserRegistered, OrderCreated

**3. Event Storage (Memorizzazione Eventi)**
- Salvataggio in event store
- Append-only per performance
- Versioning per concurrency
- Esempio: EventStore, Database

**4. Event Processing (Elaborazione Eventi)**
- Lettura degli eventi
- Applicazione delle regole
- Aggiornamento delle proiezioni
- Esempio: Event Handlers, Projections

**5. Query (Query)**
- Lettura dalle proiezioni
- Query ottimizzate per uso
- Aggregazione dei dati
- Esempio: UserView, OrderSummary

### Pattern Correlati

**CQRS (Command Query Responsibility Segregation)**
- Separazione tra comandi e query
- Modelli ottimizzati per ogni scopo
- Scalabilità indipendente
- Esempio: Command Model, Query Model

**Saga Pattern**
- Gestione di transazioni distribuite
- Coordinamento tra aggregati
- Compensazione per errori
- Esempio: OrderSaga, PaymentSaga

**Event Replay**
- Ricostruzione dello stato
- Debugging e testing
- Time travel
- Esempio: State Reconstruction, Debugging

## Quando usarlo

Usa Event Sourcing quando:
- **Hai bisogno** di audit trail completo
- **Vuoi tracciare** la storia delle modifiche
- **Hai requisiti** di compliance
- **Vuoi analizzare** i pattern di utilizzo
- **Hai bisogno** di time travel
- **Vuoi** decoupling tra sistemi

**NON usarlo quando:**
- **Il dominio è semplice** e CRUD
- **Non hai bisogno** di audit trail
- **Hai vincoli** di performance rigidi
- **Il team non è** esperto in event sourcing
- **Il progetto è** molto breve
- **Hai requisiti** di query complesse

## Pro e contro

**I vantaggi:**
- **Audit trail** completo
- **Tracciabilità** delle modifiche
- **Time travel** e debugging
- **Decoupling** tra sistemi
- **Scalabilità** orizzontale
- **Analisi** dei pattern

**Gli svantaggi:**
- **Complessità** iniziale elevata
- **Curva di apprendimento** per il team
- **Overhead** per domini semplici
- **Richiede esperienza** in event sourcing
- **Può essere** over-engineering
- **Tempo iniziale** per l'implementazione

## Principi/Metodologie correlate

- **Domain-Driven Design** - [23-domain-driven-design](./23-domain-driven-design/domain-driven-design.md): Eventi come parte del dominio
- **CQRS** - [25-cqrs](./25-cqrs/cqrs.md): Separazione comandi e query
- **Clean Architecture** - [22-clean-architecture](./22-clean-architecture/clean-architecture.md): Architettura per event sourcing
- **TDD** - [09-tdd](./09-tdd/tdd.md): Testabilità degli eventi
- **Refactoring** - [12-refactoring](./12-refactoring/refactoring.md): Miglioramento continuo del modello
- **Microservices** - [26-microservices](./26-microservices/microservices.md): Comunicazione tramite eventi

## Risorse utili

### Documentazione ufficiale
- [Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html) - Articolo di Martin Fowler
- [Event Store](https://eventstore.com/) - Database per event sourcing
- [Apache Kafka](https://kafka.apache.org/) - Piattaforma per eventi

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel Event Sourcing](https://github.com/spatie/laravel-event-sourcing) - Package per event sourcing
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel

### Esempi e tutorial
- [Event Sourcing in PHP](https://github.com/CodelyTV/php-ddd-example) - Esempio in PHP
- [Laravel Event Sourcing Example](https://github.com/spatie/laravel-event-sourcing) - Esempio Laravel
- [Event Sourcing Patterns](https://github.com/ardalis/cleanarchitecture) - Pattern e esempi
