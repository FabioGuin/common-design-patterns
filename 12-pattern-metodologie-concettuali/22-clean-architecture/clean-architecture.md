# Clean Architecture

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Correlati](#correlati)
- [Risorse utili](#risorse-utili)

## Cosa fa

Clean Architecture è un approccio architetturale che organizza il codice in layer concentrici, dove le dipendenze puntano sempre verso l'interno verso le regole di business. L'obiettivo è creare sistemi indipendenti da framework, database, UI e agenti esterni, mantenendo la testabilità e la flessibilità.

## Perché ti serve

Clean Architecture ti aiuta a:
- **Separare le responsabilità** in layer distinti
- **Indipendenza** da framework e database
- **Testabilità** del codice business
- **Flessibilità** nel cambiare tecnologie
- **Manutenibilità** a lungo termine
- **Scalabilità** del sistema

## Come funziona

### Layer dell'Architettura

**1. Entities (Entità)**
- Regole di business fondamentali
- Indipendenti da qualsiasi framework
- Contengono la logica core dell'applicazione
- Esempio: User, Order, Product

**2. Use Cases (Casi d'Uso)**
- Logica di business specifica dell'applicazione
- Orchestrano il flusso di dati tra entities
- Indipendenti da UI e database
- Esempio: CreateUser, ProcessOrder, SendEmail

**3. Interface Adapters**
- Convertono dati tra use cases e layer esterni
- Contengono controllers, presenters, gateways
- Adattano i dati per i layer esterni
- Esempio: Controllers, Repositories, Presenters

**4. Frameworks & Drivers**
- Database, Web Framework, UI
- Strumenti esterni e librerie
- Implementazioni concrete
- Esempio: Laravel, MySQL, Vue.js

### Regole di Dipendenza

**Dependency Rule**
- Le dipendenze puntano sempre verso l'interno
- I layer esterni dipendono da quelli interni
- I layer interni non conoscono quelli esterni
- Le interfacce sono definite nei layer interni

**Inversion of Control**
- I layer esterni implementano le interfacce
- I layer interni usano le interfacce
- Le dipendenze sono iniettate dall'esterno
- Facilita il testing e la flessibilità

### Principi Architetturali

**Independence**
- Indipendenza da framework
- Indipendenza da database
- Indipendenza da UI
- Indipendenza da agenti esterni

**Testability**
- Business logic testabile in isolamento
- Mock delle dipendenze esterne
- Test unitari senza framework
- Test di integrazione controllati

**Flexibility**
- Cambio di framework senza impatto
- Cambio di database trasparente
- Cambio di UI indipendente
- Evoluzione del sistema graduale

## Quando usarlo

Usa Clean Architecture quando:
- **Il progetto è complesso** e di lunga durata
- **Hai bisogno** di testabilità alta
- **Vuoi indipendenza** da framework
- **Il team è esperto** in architetture
- **Hai requisiti** che cambiano frequentemente
- **Vuoi manutenibilità** a lungo termine

**NON usarlo quando:**
- **Il progetto è molto semplice** e piccolo
- **Il team non è esperto** in architetture
- **Hai vincoli** di tempo molto rigidi
- **Il progetto è** un prototipo rapido
- **Non hai bisogno** di testabilità alta
- **Il framework** è stabile e non cambierà

## Pro e contro

**I vantaggi:**
- **Separazione** delle responsabilità
- **Testabilità** del codice business
- **Indipendenza** da framework
- **Manutenibilità** a lungo termine
- **Flessibilità** nel cambiare tecnologie
- **Scalabilità** del sistema

**Gli svantaggi:**
- **Complessità** iniziale elevata
- **Curva di apprendimento** per il team
- **Overhead** per progetti semplici
- **Richiede esperienza** architetturale
- **Può essere** over-engineering
- **Tempo iniziale** per l'implementazione

## Correlati

### Pattern

- **[SOLID Principles](./04-solid-principles/solid-principles.md)** - Base per Clean Architecture
- **[Separation of Concerns](./06-separation-of-concerns/separation-of-concerns.md)** - Separazione delle responsabilità
- **[Clean Code](./05-clean-code/clean-code.md)** - Codice pulito e ben organizzato
- **[TDD](./09-tdd/tdd.md)** - Testabilità del codice business
- **[Refactoring](./12-refactoring/refactoring.md)** - Miglioramento continuo dell'architettura
- **[Domain-Driven Design](./23-domain-driven-design/domain-driven-design.md)** - Approccio complementare

### Principi e Metodologie

- **[Clean Architecture](https://en.wikipedia.org/wiki/Clean_Architecture)** - Metodologia originale di Robert Martin
- **[Hexagonal Architecture](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software))** - Architettura esagonale
- **[Onion Architecture](https://en.wikipedia.org/wiki/Onion_architecture)** - Architettura a cipolla


## Risorse utili

### Documentazione ufficiale
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html) - Articolo originale di Robert Martin
- [Clean Architecture Book](https://www.amazon.com/Clean-Architecture-Craftsmans-Software-Structure/dp/0134494272) - Libro di Robert Martin
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Libro di Robert Martin

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel Architecture](https://laravel.com/docs/structure) - Struttura dell'applicazione Laravel
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel

### Esempi e tutorial
- [Clean Architecture in PHP](https://github.com/CodelyTV/php-ddd-example) - Esempio in PHP
- [Laravel Clean Architecture](https://github.com/ahmedash95/laravel-clean-architecture) - Implementazione Laravel
- [Clean Architecture Patterns](https://github.com/ardalis/cleanarchitecture) - Pattern e esempi
