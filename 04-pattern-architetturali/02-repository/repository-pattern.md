# Repository Pattern

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

Il pattern Repository crea un layer di astrazione tra la logica di business e l'accesso ai dati. Invece di accedere direttamente al database, la tua applicazione interagisce con un repository che si occupa di recuperare, salvare e gestire i dati.

Pensa al Repository come a un magazziniere esperto: tu gli chiedi "portami tutti i prodotti rossi" e lui sa esattamente dove andare a prenderli, senza che tu debba sapere come sono organizzati nel magazzino.

## Perché ti serve

Senza Repository, il tuo codice diventa un disastro di query SQL sparse ovunque. Immagina di avere:
- Query duplicate in ogni controller
- Logica di accesso ai dati mescolata con la logica di business
- Impossibilità di testare senza database
- Difficoltà nel cambiare sistema di storage

Con Repository ottieni:
- **Separazione delle responsabilità**: Logica di business separata dall'accesso ai dati
- **Testabilità**: Puoi mockare i repository per i test
- **Riusabilità**: Stessa logica di accesso in più punti
- **Manutenibilità**: Modifiche centralizzate all'accesso ai dati
- **Flessibilità**: Cambiare database senza toccare la logica di business

## Come funziona

1. **Definisci un'interfaccia** per il repository (es: `UserRepositoryInterface`)
2. **Implementi il repository** con la logica di accesso ai dati (es: `EloquentUserRepository`)
3. **Inietti il repository** nei tuoi service o controller
4. **Usi il repository** per tutte le operazioni sui dati
5. **Laravel risolve automaticamente** l'implementazione corretta

Il flusso è: **Controller → Service → Repository → Database**

## Schema visivo

```
Controller
    ↓
Service Layer
    ↓
Repository Interface
    ↓
Repository Implementation
    ↓
Database/API/File
```

**Flusso dettagliato:**
```
1. UserController@index
   ↓
2. UserService@getAllUsers
   ↓
3. UserRepository@findAll
   ↓
4. EloquentUserRepository@findAll
   ↓
5. User::all() (Eloquent)
   ↓
6. Database Query
   ↓
7. Collection of Users
```

## Quando usarlo

Usa il pattern Repository quando:
- Hai logica di accesso ai dati complessa
- Vuoi testare senza database
- Hai bisogno di cambiare sistema di storage
- Vuoi centralizzare le query
- Stai costruendo un'architettura a layer
- Hai query che si ripetono in più punti

**NON usarlo quando:**
- Hai un'applicazione molto semplice con poche query
- Stai usando solo Eloquent con query semplici
- Non hai bisogno di testare l'accesso ai dati
- Vuoi mantenere la semplicità di Laravel
- Hai vincoli di performance estremi

## Pro e contro

**I vantaggi:**
- **Separazione chiara**: Logica di business separata dall'accesso ai dati
- **Testabilità**: Facile mockare per i test unitari
- **Riusabilità**: Stessa logica in più punti dell'applicazione
- **Manutenibilità**: Modifiche centralizzate
- **Flessibilità**: Cambiare implementazione senza toccare il business logic
- **Consistenza**: Interfaccia uniforme per l'accesso ai dati

**Gli svantaggi:**
- **Complessità aggiuntiva**: Più file e layer da gestire
- **Overhead**: Strato aggiuntivo di astrazione
- **Curva di apprendimento**: Richiede comprensione dell'architettura
- **Over-engineering**: Può essere eccessivo per applicazioni semplici
- **Performance**: Strato aggiuntivo può rallentare (minimamente)

## Esempi di codice

### Pseudocodice

```
// Interfaccia Repository
interface RepositoryInterface {
    function findAll(): Collection
    function findById(id): Object
    function create(data): Object
    function update(id, data): Boolean
    function delete(id): Boolean
}

// Implementazione Repository
class DatabaseRepository implements RepositoryInterface {
    function findAll(): Collection {
        return database.query("SELECT * FROM table")
    }
    
    function findById(id): Object {
        return database.query("SELECT * FROM table WHERE id = ?", id)
    }
    
    function create(data): Object {
        return database.insert("table", data)
    }
    
    function update(id, data): Boolean {
        return database.update("table", data, "id = ?", id)
    }
    
    function delete(id): Boolean {
        return database.delete("table", "id = ?", id)
    }
}

// Service Layer
class Service {
    constructor(repository: RepositoryInterface) {
        this.repository = repository
    }
    
    function getAll(): Collection {
        return this.repository.findAll()
    }
    
    function create(data): Object {
        // Logica di business
        data = this.validate(data)
        data = this.process(data)
        return this.repository.create(data)
    }
}

// Controller
class Controller {
    constructor(service: Service) {
        this.service = service
    }
    
    function index() {
        data = this.service.getAll()
        return view.render('index', data)
    }
}

// Dependency Injection
container.bind(RepositoryInterface, DatabaseRepository)
container.bind(Service, Service)
container.bind(Controller, Controller)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Repository Blog System](./esempio-completo/)** - Sistema blog con Repository Pattern

L'esempio include:
- Repository per articoli e utenti
- Service layer per logica di business
- Controller che usano i service
- Test unitari con mock dei repository
- Configurazione del service container

**Nota per l'implementazione**: L'esempio completo segue il template semplificato con focus sulla dimostrazione del pattern Repository, non su un'applicazione completa.

## Correlati

### Pattern

- **[MVC Pattern](./01-mvc/mvc-pattern.md)** - Architettura base per applicazioni web
- **[Service Layer Pattern](./03-service-layer/service-layer-pattern.md)** - Incapsula la logica di business
- **[Unit of Work Pattern](./05-unit-of-work/unit-of-work-pattern.md)** - Gestisce le transazioni
- **[DTO Pattern](./04-dto/dto-pattern.md)** - Trasferisce dati tra layer

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Laravel Applications**: Molte applicazioni Laravel usano repository per separare logica
- **Enterprise Applications**: Pattern comune in applicazioni aziendali complesse
- **API Development**: Separazione tra logica di business e accesso ai dati
- **Microservices**: Ogni servizio ha i suoi repository
- **Legacy System Integration**: Wrapper per sistemi legacy

## Anti-pattern

**Cosa NON fare:**
- **Anemic Repository**: Repository che sono solo wrapper di Eloquent
- **God Repository**: Repository che fanno troppo lavoro
- **Repository per tutto**: Non serve per query semplici
- **Over-engineering**: Pattern eccessivo per applicazioni semplici
- **Tight Coupling**: Repository troppo legati a implementazioni specifiche

## Troubleshooting

### Problemi comuni

- **Repository troppo semplici**: Aggiungi logica di business se necessario
- **Dependency injection**: Verifica che i repository siano registrati nel container
- **Testing**: Usa mock per testare senza database
- **Performance**: Ottimizza le query nei repository
- **Naming**: Usa nomi chiari per i metodi del repository

### Debug e monitoring

- **Log delle query**: Traccia le query eseguite dai repository
- **Performance**: Monitora i tempi di esecuzione
- **Errori**: Implementa gestione errori centralizzata
- **Testing**: Testa i repository separatamente

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Strato aggiuntivo di astrazione, ma minimo
- **CPU**: Overhead trascurabile per la maggior parte delle applicazioni
- **I/O**: Query database ottimizzate nei repository

### Scalabilità

- **Carico basso**: Repository non aggiungono overhead significativo
- **Carico medio**: Separazione aiuta nell'ottimizzazione
- **Carico alto**: Caching e ottimizzazioni specifiche per repository

### Colli di bottiglia

- **Database queries**: Ottimizza le query nei repository
- **N+1 queries**: Usa eager loading quando necessario
- **Memory usage**: Gestisci le collection grandi con paginazione

## Risorse utili

### Documentazione ufficiale

- [Laravel Service Container](https://laravel.com/docs/container) - Dependency injection
- [Laravel Service Providers](https://laravel.com/docs/providers) - Registrazione servizi
- [Repository Pattern Wikipedia](https://en.wikipedia.org/wiki/Repository_pattern) - Teoria del pattern

### Laravel specifico

- [Laravel Eloquent](https://laravel.com/docs/eloquent) - ORM di Laravel
- [Laravel Testing](https://laravel.com/docs/testing) - Test con mock
- [Laravel Service Container](https://laravel.com/docs/container) - Gestione dipendenze

### Esempi e tutorial

- [Laravel Repository Pattern](https://laravel.com/docs/eloquent#repositories) - Implementazione Laravel
- [Repository Pattern Best Practices](https://laravel.com/docs/eloquent#repositories) - Best practices

### Strumenti di supporto

- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug query
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring applicazione
