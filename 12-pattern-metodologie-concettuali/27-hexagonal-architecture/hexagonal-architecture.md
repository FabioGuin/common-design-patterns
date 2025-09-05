# Hexagonal Architecture

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

Hexagonal Architecture (nota anche come Ports and Adapters) è un pattern architetturale che isola la logica di business dell'applicazione dalle interfacce esterne. L'applicazione è circondata da "porti" (interfacce) che sono implementati da "adattatori" che gestiscono le comunicazioni con il mondo esterno.

## Perché ti serve

Hexagonal Architecture ti aiuta a:
- **Isolare** la logica di business dalle interfacce esterne
- **Testare** l'applicazione in isolamento
- **Cambiare** le tecnologie esterne senza impatto
- **Mantenere** la logica di business pulita
- **Facilitare** l'integrazione con sistemi esterni
- **Migliorare** la manutenibilità del codice

## Come funziona

### Concetti Fondamentali

**Ports (Porti)**
- Interfacce che definiscono le operazioni
- Definiti dall'applicazione
- Astratti e indipendenti dalla tecnologia
- Esempio: UserRepository, EmailService, PaymentGateway

**Adapters (Adattatori)**
- Implementazioni concrete dei porti
- Gestiscono le comunicazioni esterne
- Possono essere sostituiti facilmente
- Esempio: DatabaseUserRepository, SMTPEmailService, StripePaymentGateway

**Application Core (Nucleo dell'Applicazione)**
- Logica di business pura
- Indipendente da tecnologie esterne
- Usa solo i porti
- Esempio: UserService, OrderService, PaymentService

### Architettura a Strati

**1. Domain Layer (Strato di Dominio)**
- Entità e regole di business
- Indipendente da tutto
- Contiene la logica core
- Esempio: User, Order, Product

**2. Application Layer (Strato di Applicazione)**
- Casi d'uso e orchestrazione
- Usa i porti per comunicare
- Contiene la logica di applicazione
- Esempio: UserService, OrderService

**3. Infrastructure Layer (Strato di Infrastruttura)**
- Implementazioni concrete dei porti
- Database, API esterne, UI
- Adattatori per le tecnologie
- Esempio: EloquentUserRepository, RESTPaymentGateway

**4. Interface Layer (Strato di Interfaccia)**
- Controllers, CLI, API
- Adattatori per le interfacce utente
- Traduzione tra formati
- Esempio: UserController, OrderAPI

### Pattern di Implementazione

**Dependency Inversion**
- Le dipendenze puntano verso l'interno
- I layer esterni implementano le interfacce
- I layer interni usano le interfacce
- Facilita il testing e la flessibilità

**Interface Segregation**
- Interfacce piccole e specifiche
- Ogni adattatore implementa solo quello che serve
- Riduce l'accoppiamento
- Facilita la sostituzione

**Single Responsibility**
- Ogni adattatore ha una responsabilità
- Separazione delle preoccupazioni
- Facilita la manutenzione
- Riduce la complessità

### Vantaggi dell'Architettura

**Testabilità**
- Test isolati del business logic
- Mock degli adattatori
- Test senza database o API esterne
- Test di integrazione controllati

**Flessibilità**
- Cambio di database trasparente
- Cambio di framework senza impatto
- Cambio di API esterne facile
- Evoluzione graduale del sistema

**Manutenibilità**
- Logica di business isolata
- Responsabilità chiare
- Codice più pulito
- Facile da capire e modificare

## Quando usarlo

Usa Hexagonal Architecture quando:
- **Hai bisogno** di testabilità alta
- **Vuoi isolare** la logica di business
- **Hai molte** integrazioni esterne
- **Vuoi flessibilità** nelle tecnologie
- **Il team è esperto** in architetture
- **Hai requisiti** di manutenibilità

**NON usarlo quando:**
- **Il progetto è molto semplice**
- **Il team non è** esperto in architetture
- **Hai vincoli** di tempo rigidi
- **Il progetto è** un prototipo rapido
- **Non hai** integrazioni esterne
- **Il framework** è stabile e non cambierà

## Pro e contro

**I vantaggi:**
- **Isolamento** della logica di business
- **Testabilità** alta
- **Flessibilità** nelle tecnologie
- **Manutenibilità** del codice
- **Integrazione** facile con sistemi esterni
- **Evoluzione** graduale

**Gli svantaggi:**
- **Complessità** iniziale elevata
- **Curva di apprendimento** per il team
- **Overhead** per progetti semplici
- **Richiede esperienza** architetturale
- **Può essere** over-engineering
- **Tempo iniziale** per l'implementazione

## Principi/Metodologie correlate

- **Clean Architecture** - [22-clean-architecture](./22-clean-architecture/clean-architecture.md): Architettura complementare
- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): Principi per il design
- **Separation of Concerns** - [06-separation-of-concerns](./06-separation-of-concerns/separation-of-concerns.md): Separazione delle responsabilità
- **TDD** - [09-tdd](./09-tdd/tdd.md): Testabilità del business logic
- **Refactoring** - [12-refactoring](./12-refactoring/refactoring.md): Miglioramento continuo dell'architettura
- **Domain-Driven Design** - [23-domain-driven-design](./23-domain-driven-design/domain-driven-design.md): Modellazione del dominio

## Risorse utili

### Documentazione ufficiale
- [Hexagonal Architecture](https://alistair.cockburn.us/hexagonal-architecture/) - Articolo originale di Alistair Cockburn
- [Ports and Adapters](https://herbertograca.com/2017/09/14/ports-adapters-architecture/) - Guida dettagliata
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html) - Architettura correlata

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel Hexagonal](https://github.com/ahmedash95/laravel-hexagonal) - Implementazione Laravel
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel

### Esempi e tutorial
- [Hexagonal Architecture in PHP](https://github.com/CodelyTV/php-ddd-example) - Esempio in PHP
- [Laravel Hexagonal Example](https://github.com/ahmedash95/laravel-hexagonal) - Esempio Laravel
- [Ports and Adapters](https://github.com/ardalis/cleanarchitecture) - Pattern e esempi
