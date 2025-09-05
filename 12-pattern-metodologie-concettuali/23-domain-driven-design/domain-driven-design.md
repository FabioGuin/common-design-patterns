# Domain-Driven Design (DDD)

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

Domain-Driven Design (DDD) è un approccio allo sviluppo software che pone il dominio del business al centro dell'architettura. Si basa sulla modellazione del dominio attraverso un linguaggio ubiquo condiviso tra sviluppatori e esperti del business, creando un modello che riflette la complessità del dominio reale.

## Perché ti serve

DDD ti aiuta a:
- **Modellare** il dominio del business in modo accurato
- **Comunicare** efficacemente con gli esperti del business
- **Gestire** la complessità del dominio
- **Creare** software che riflette la realtà del business
- **Mantenere** la coerenza tra codice e business
- **Evolvere** il sistema insieme al dominio

## Come funziona

### Concetti Fondamentali

**Ubiquitous Language (Linguaggio Ubiquo)**
- Linguaggio condiviso tra sviluppatori e business
- Termini tecnici e di business allineati
- Documentazione vivente del dominio
- Comunicazione efficace nel team

**Domain Model (Modello del Dominio)**
- Rappresentazione del dominio nel codice
- Entità, Value Objects, Aggregates
- Regole di business esplicite
- Logica del dominio isolata

**Bounded Context (Contesto Delimitato)**
- Confini chiari del dominio
- Modelli indipendenti per contesti diversi
- Comunicazione tra contesti tramite interfacce
- Evoluzione indipendente dei contesti

### Building Blocks

**Entities (Entità)**
- Oggetti con identità unica
- Cambiano nel tempo
- Identificati da un ID
- Esempio: User, Order, Product

**Value Objects (Oggetti Valore)**
- Oggetti senza identità
- Immutabili
- Definiti dai loro attributi
- Esempio: Email, Money, Address

**Aggregates (Aggregati)**
- Insieme di entità e value objects
- Invarianti di business
- Transazioni atomiche
- Esempio: Order con OrderItems

**Domain Services (Servizi di Dominio)**
- Logica che non appartiene a entità
- Operazioni complesse del dominio
- Stateless
- Esempio: PaymentService, NotificationService

**Repositories (Repository)**
- Astrazione per l'accesso ai dati
- Interfacce nel dominio
- Implementazioni nell'infrastruttura
- Esempio: UserRepository, OrderRepository

**Domain Events (Eventi di Dominio)**
- Eventi significativi nel dominio
- Comunicazione tra aggregati
- Decoupling tra componenti
- Esempio: UserRegistered, OrderCompleted

### Strategic Design

**Context Mapping**
- Mappatura dei contesti del sistema
- Relazioni tra contesti
- Pattern di integrazione
- Esempio: Shared Kernel, Customer-Supplier

**Anti-Corruption Layer**
- Protezione del dominio
- Traduzione tra modelli
- Isolamento da sistemi legacy
- Esempio: LegacyAdapter

**Shared Kernel**
- Codice condiviso tra contesti
- Controllo rigoroso dei cambiamenti
- Comunicazione diretta
- Esempio: CommonValueObjects

## Quando usarlo

Usa DDD quando:
- **Il dominio è complesso** e ricco di logica
- **Hai esperti** del business disponibili
- **Il team è esperto** in DDD
- **Il progetto è** di lunga durata
- **Hai bisogno** di modellazione accurata
- **Vuoi** comunicazione efficace con il business

**NON usarlo quando:**
- **Il dominio è semplice** e CRUD
- **Non hai esperti** del business
- **Il team non è** esperto in DDD
- **Il progetto è** molto breve
- **Hai vincoli** di tempo rigidi
- **Il sistema è** principalmente tecnico

## Pro e contro

**I vantaggi:**
- **Modellazione accurata** del dominio
- **Comunicazione efficace** con il business
- **Gestione** della complessità
- **Coerenza** tra codice e business
- **Manutenibilità** a lungo termine
- **Evoluzione** del sistema

**Gli svantaggi:**
- **Complessità** iniziale elevata
- **Curva di apprendimento** per il team
- **Overhead** per domini semplici
- **Richiede esperienza** del business
- **Può essere** over-engineering
- **Tempo iniziale** per la modellazione

## Principi/Metodologie correlate

- **Clean Architecture** - [22-clean-architecture](./22-clean-architecture/clean-architecture.md): Architettura complementare a DDD
- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): Principi per il design del dominio
- **Separation of Concerns** - [06-separation-of-concerns](./06-separation-of-concerns/separation-of-concerns.md): Separazione delle responsabilità
- **TDD** - [09-tdd](./09-tdd/tdd.md): Testabilità del dominio
- **Refactoring** - [12-refactoring](./12-refactoring/refactoring.md): Miglioramento continuo del modello
- **Event Sourcing** - [24-event-sourcing](./24-event-sourcing/event-sourcing.md): Pattern per la persistenza degli eventi

## Risorse utili

### Documentazione ufficiale
- [Domain-Driven Design](https://www.amazon.com/Domain-Driven-Design-Tackling-Complexity-Software/dp/0321125215) - Libro di Eric Evans
- [DDD Community](https://www.domainlanguage.com/) - Sito ufficiale DDD
- [DDD Reference](https://www.domainlanguage.com/ddd/reference/) - Riferimento DDD

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel DDD](https://github.com/ahmedash95/laravel-ddd) - Implementazione DDD in Laravel
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel

### Esempi e tutorial
- [DDD in PHP](https://github.com/CodelyTV/php-ddd-example) - Esempio in PHP
- [Laravel DDD Example](https://github.com/ahmedash95/laravel-ddd-example) - Esempio Laravel
- [DDD Patterns](https://github.com/ardalis/cleanarchitecture) - Pattern e esempi
