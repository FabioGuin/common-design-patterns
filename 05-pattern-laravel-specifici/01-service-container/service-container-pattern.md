# Service Container Pattern

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

Il pattern Service Container (IoC Container) gestisce l'istanziazione e la risoluzione delle dipendenze in modo automatico. Laravel utilizza il Service Container per gestire l'iniezione delle dipendenze, permettendo di registrare servizi e risolverli automaticamente quando richiesti.

Pensa al Service Container come a un magazziniere intelligente: quando hai bisogno di un oggetto, il magazziniere sa esattamente come costruirlo, quali parti servono e come assemblarle. Tu chiedi semplicemente "voglio un martello" e lui te lo porta già montato e pronto all'uso.

## Perché ti serve

Senza Service Container, devi gestire manualmente tutte le dipendenze: creare oggetti, passare parametri, gestire la loro durata. Risultato? Codice accoppiato, difficile da testare e da mantenere.

Con Service Container ottieni:
- **Iniezione automatica**: Dipendenze risolte automaticamente
- **Disaccoppiamento**: Classi non dipendono da implementazioni concrete
- **Testabilità**: Facile sostituire dipendenze nei test
- **Singleton**: Gestione automatica del ciclo di vita
- **Lazy Loading**: Creazione on-demand degli oggetti
- **Configurazione centralizzata**: Tutto in un posto
- **Flessibilità**: Cambio implementazioni senza modificare il codice

## Come funziona

1. **Registri i servizi** nel container
2. **Definisci le dipendenze** nei costruttori
3. **Laravel risolve automaticamente** le dipendenze
4. **Usi i servizi** senza preoccuparti della creazione
5. **Il container gestisce** il ciclo di vita

Il flusso è: **Registration → Resolution → Injection → Usage**

## Schema visivo

```
Service Container
    ↓
Dependency Resolution
    ↓
Automatic Injection
    ↓
Service Usage
```

**Flusso dettagliato:**
```
1. Register Service
   ↓
2. Define Dependencies
   ↓
3. Request Service
   ↓
4. Resolve Dependencies
   ↓
5. Create Instance
   ↓
6. Inject Dependencies
   ↓
7. Return Service
```

## Quando usarlo

Usa il pattern Service Container quando:
- Vuoi disaccoppiare le classi
- Hai bisogno di iniezione delle dipendenze
- Vuoi testare facilmente il codice
- Hai servizi con dipendenze complesse
- Vuoi gestire il ciclo di vita degli oggetti
- Hai bisogno di configurazione centralizzata
- Vuoi implementare il pattern Singleton

**NON usarlo quando:**
- Hai classi semplici senza dipendenze
- Vuoi mantenere la semplicità
- Hai vincoli di performance estremi
- L'applicazione è molto piccola
- Le dipendenze sono statiche

## Pro e contro

**I vantaggi:**
- **Disaccoppiamento**: Classi indipendenti dalle implementazioni
- **Testabilità**: Facile sostituire dipendenze nei test
- **Flessibilità**: Cambio implementazioni senza modificare codice
- **Singleton**: Gestione automatica del ciclo di vita
- **Lazy Loading**: Creazione on-demand
- **Configurazione centralizzata**: Tutto in un posto
- **Iniezione automatica**: Dipendenze risolte automaticamente

**Gli svantaggi:**
- **Complessità**: Curva di apprendimento iniziale
- **Overhead**: Strato aggiuntivo di astrazione
- **Debug**: Può essere difficile debuggare
- **Performance**: Overhead minimo per la risoluzione
- **Over-engineering**: Può essere eccessivo per classi semplici

## Esempi di codice

### Pseudocodice

```
// Service Container Interface
interface ContainerInterface {
    function bind(abstract, concrete)
    function singleton(abstract, concrete)
    function make(abstract)
    function instance(abstract, instance)
    function resolve(abstract)
}

// Service Container Implementation
class ServiceContainer implements ContainerInterface {
    private bindings = []
    private instances = []
    
    function bind(abstract, concrete) {
        this.bindings[abstract] = concrete
    }
    
    function singleton(abstract, concrete) {
        this.bindings[abstract] = {
            concrete: concrete,
            shared: true
        }
    }
    
    function make(abstract) {
        if (this.instances[abstract]) {
            return this.instances[abstract]
        }
        
        binding = this.bindings[abstract]
        if (!binding) {
            throw new Exception("Service not found")
        }
        
        instance = this.resolve(binding.concrete)
        
        if (binding.shared) {
            this.instances[abstract] = instance
        }
        
        return instance
    }
    
    function resolve(concrete) {
        if (is_string(concrete)) {
            return this.build(concrete)
        }
        
        if (is_callable(concrete)) {
            return concrete(this)
        }
        
        return concrete
    }
    
    function build(className) {
        reflection = new ReflectionClass(className)
        constructor = reflection.getConstructor()
        
        if (!constructor) {
            return new className()
        }
        
        parameters = constructor.getParameters()
        dependencies = []
        
        foreach (parameters as parameter) {
            type = parameter.getType()
            if (type && !type.isBuiltin()) {
                dependencies[] = this.make(type.getName())
            } else {
                dependencies[] = parameter.getDefaultValue()
            }
        }
        
        return reflection.newInstanceArgs(dependencies)
    }
}

// Service Registration
container.bind('UserRepository', EloquentUserRepository::class)
container.singleton('UserService', UserService::class)
container.bind('EmailService', function(container) {
    return new EmailService(container.make('Config'))
})

// Service Usage
class UserController {
    constructor(userService) {
        this.userService = userService
    }
}

// Automatic Resolution
userController = container.make(UserController::class)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Service Container Blog System](./esempio-completo/)** - Sistema blog con Service Container

L'esempio include:
- Registrazione servizi
- Iniezione dipendenze
- Singleton e binding
- Service Provider
- Test con mock
- Configurazione avanzata

**Nota per l'implementazione**: L'esempio completo segue il template semplificato con focus sulla dimostrazione del pattern Service Container, non su un'applicazione completa.

## Correlati

### Pattern

- **[Service Provider Pattern](./02-service-provider/service-provider-pattern.md)** - Registrazione servizi
- **[Repository Pattern](../04-pattern-architetturali/02-repository/repository-pattern.md)** - Astrazione accesso dati
- **[Service Layer Pattern](../04-pattern-architetturali/03-service-layer/service-layer-pattern.md)** - Logica business centralizzata
- **[Factory Pattern](../01-pattern-creazionali/03-factory/factory-pattern.md)** - Creazione oggetti

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Laravel Framework**: Container integrato
- **Applicazioni enterprise**: Gestione dipendenze complesse
- **Sistemi modulari**: Plugin e estensioni
- **API complesse**: Servizi con molte dipendenze
- **Applicazioni testabili**: Mock e stubbing

## Anti-pattern

**Cosa NON fare:**
- **Service Locator**: Evita il pattern Service Locator
- **God Container**: Container che fa troppo
- **Tight Coupling**: Dipendenze troppo strette
- **Circular Dependencies**: Dipendenze circolari
- **Over-registration**: Registrazione eccessiva

## Troubleshooting

### Problemi comuni

- **Circular Dependencies**: Gestisci le dipendenze circolari
- **Service Not Found**: Verifica la registrazione
- **Performance**: Ottimizza la risoluzione
- **Memory Leaks**: Gestisci il ciclo di vita
- **Testing**: Usa mock appropriati

### Debug e monitoring

- **Log delle risoluzioni**: Traccia la risoluzione dei servizi
- **Performance**: Monitora i tempi di risoluzione
- **Memory usage**: Monitora l'uso della memoria
- **Dependencies**: Visualizza il grafo delle dipendenze

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Gestione delle istanze in memoria
- **CPU**: Overhead per la risoluzione
- **I/O**: Lazy loading ottimizzato

### Scalabilità

- **Carico basso**: Container non aggiunge overhead significativo
- **Carico medio**: Singleton ottimizza le performance
- **Carico alto**: Caching e ottimizzazioni specifiche

### Colli di bottiglia

- **Risoluzione**: Ottimizza la risoluzione delle dipendenze
- **Memory usage**: Gestisci il ciclo di vita delle istanze
- **Circular Dependencies**: Evita le dipendenze circolari

## Risorse utili

### Documentazione ufficiale

- [Laravel Service Container](https://laravel.com/docs/container) - Documentazione ufficiale
- [Dependency Injection](https://laravel.com/docs/container#dependency-injection) - Iniezione dipendenze
- [Service Container Wikipedia](https://en.wikipedia.org/wiki/Service_locator_pattern) - Teoria del pattern

### Laravel specifico

- [Laravel Container](https://laravel.com/docs/container) - Implementazione Laravel
- [Laravel Service Providers](https://laravel.com/docs/providers) - Registrazione servizi
- [Laravel Facades](https://laravel.com/docs/facades) - Facade pattern

### Esempi e tutorial

- [Laravel Container Examples](https://laravel.com/docs/container) - Esempi Laravel
- [Dependency Injection Best Practices](https://laravel.com/docs/container) - Best practices

### Strumenti di supporto

- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug container
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring applicazione
