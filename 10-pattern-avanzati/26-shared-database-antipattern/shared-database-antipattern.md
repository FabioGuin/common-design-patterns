# Shared Database Anti-pattern

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

Il Shared Database Anti-pattern è un antipattern che si verifica quando più microservizi condividono lo stesso database, creando accoppiamenti stretti e violando i principi dell'architettura a microservizi. È come avere più dipartimenti aziendali che condividono lo stesso archivio e si intromettono nei documenti degli altri.

Pensa a un ufficio dove tutti i reparti (vendite, contabilità, risorse umane) usano lo stesso armadietto per i documenti: ogni volta che qualcuno sposta o modifica un documento, tutti gli altri ne risentono, creando confusione e conflitti.

## Perché ti serve

È importante riconoscere questo antipattern perché:
- È molto comune nelle migrazioni da monoliti
- Crea problemi di scalabilità e manutenzione
- Viola i principi di autonomia dei microservizi
- Rende difficile il deployment indipendente
- Causa conflitti tra team di sviluppo
- Impedisce l'uso di tecnologie database diverse

Senza riconoscere questo antipattern:
- I microservizi rimangono accoppiati
- Le modifiche di schema impattano tutti i servizi
- I deployment diventano complessi
- La scalabilità è limitata
- I team non possono lavorare indipendentemente
- L'architettura diventa fragile

Riconoscendo questo antipattern:
- Puoi identificare i problemi di accoppiamento
- Puoi pianificare la migrazione verso database separati
- Puoi migliorare l'autonomia dei servizi
- Puoi semplificare i deployment
- Puoi permettere ai team di lavorare indipendentemente
- Puoi creare un'architettura più robusta

## Come funziona

1. **Identificazione**: Riconosci quando i servizi condividono il database
2. **Analisi impatti**: Analizza come le modifiche impattano i servizi
3. **Pianificazione migrazione**: Pianifica la separazione dei database
4. **Implementazione graduale**: Migra gradualmente verso database separati
5. **Monitoraggio**: Monitora i miglioramenti dopo la migrazione
6. **Documentazione**: Documenta le lezioni apprese

## Schema visivo

```
ANTIPATTERN - Shared Database:
User Service ──┐
Order Service ─┼──→ Shared Database
Product Service ─┘
Payment Service ─┘

PROBLEMI:
- Accoppiamento stretto
- Modifiche di schema impattano tutti
- Deployment complesso
- Scalabilità limitata

SOLUZIONE - Database Per Service:
User Service → User Database
Order Service → Order Database
Product Service → Product Database
Payment Service → Payment Database

BENEFICI:
- Servizi disaccoppiati
- Modifiche isolate
- Deployment indipendente
- Scalabilità migliorata
```

## Quando usarlo

**NON usare** il Shared Database Anti-pattern quando:
- Stai progettando una nuova architettura a microservizi
- Vuoi garantire l'autonomia dei servizi
- Hai bisogno di scalabilità indipendente
- I team di sviluppo sono separati
- Vuoi usare tecnologie database diverse
- La coerenza dei dati è critica

**Riconosci** questo antipattern quando:
- I microservizi condividono lo stesso database
- Le modifiche di schema impattano più servizi
- I deployment richiedono coordinamento tra team
- I servizi sono strettamente accoppiati
- La scalabilità è limitata dal database condiviso
- I team non possono lavorare indipendentemente

## Pro e contro

**I vantaggi (perché sembra attraente):**
- Semplice da implementare inizialmente
- Condivisione facile dei dati
- Transazioni ACID semplici
- Meno overhead di gestione
- Costi operativi ridotti

**Gli svantaggi (perché è un antipattern):**
- Accoppiamento stretto tra servizi
- Modifiche di schema impattano tutti i servizi
- Deployment complesso e rischioso
- Scalabilità limitata
- Team di sviluppo non autonomi
- Difficoltà nell'uso di tecnologie diverse
- Violazione dei principi microservizi

## Esempi di codice

### Pseudocodice - ANTIPATTERN
```
// ANTIPATTERN: Servizi che condividono il database
class UserService {
    private sharedDatabase
    
    function createUser(userData) {
        // Modifica tabella users che impatta altri servizi
        return sharedDatabase.query("INSERT INTO users ...")
    }
}

class OrderService {
    private sharedDatabase
    
    function createOrder(orderData) {
        // Dipende dalla struttura della tabella users
        user = sharedDatabase.query("SELECT * FROM users WHERE id = ?", orderData.userId)
        // Modifica tabella orders che impatta altri servizi
        return sharedDatabase.query("INSERT INTO orders ...")
    }
}

class ProductService {
    private sharedDatabase
    
    function updateProduct(productData) {
        // Modifica tabella products che impatta altri servizi
        return sharedDatabase.query("UPDATE products SET ...")
    }
}

// PROBLEMA: Tutti i servizi condividono lo stesso database
// Una modifica di schema impatta tutti i servizi
```

### Pseudocodice - SOLUZIONE
```
// SOLUZIONE: Database separati per ogni servizio
class UserService {
    private userDatabase
    
    function createUser(userData) {
        // Database dedicato, modifiche isolate
        return userDatabase.transaction(() => {
            user = userDatabase.create(userData)
            // Comunicazione tramite API o eventi
            eventBus.publish('user.created', user)
            return user
        })
    }
}

class OrderService {
    private orderDatabase
    private userServiceClient
    
    function createOrder(orderData) {
        return orderDatabase.transaction(() => {
            // Comunicazione tramite API
            user = userServiceClient.getUser(orderData.userId)
            if (!user) throw new UserNotFoundError()
            
            order = orderDatabase.create(orderData)
            eventBus.publish('order.created', order)
            return order
        })
    }
}

// BENEFICIO: Servizi disaccoppiati, modifiche isolate
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Migrazione da Shared Database](./esempio-completo/)** - Esempio di migrazione da database condiviso

L'esempio include:
- Sistema legacy con database condiviso
- Identificazione dei problemi di accoppiamento
- Strategia di migrazione graduale
- Implementazione di database separati
- Comunicazione tramite API e eventi
- Monitoraggio dei miglioramenti

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Database Per Service Pattern](./25-database-per-service/database-per-service-pattern.md)** - Soluzione corretta per microservizi
- **[Strangler Fig Pattern](./24-strangler-fig/strangler-fig-pattern.md)** - Migrazione graduale da sistemi legacy
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite
- **[Event Sourcing Pattern](./06-event-sourcing/event-sourcing-pattern.md)** - Tracciamento eventi per coerenza

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Migrazioni da monoliti**: Spesso i monoliti vengono "spezzati" mantenendo il database condiviso
- **Sistemi legacy**: Applicazioni vecchie che sono state "microservizzate" superficialmente
- **Prototipi rapidi**: Sviluppo veloce che non considera l'architettura a lungo termine
- **Team piccoli**: Quando i team sono troppo piccoli per gestire database separati
- **Budget limitati**: Quando non ci sono risorse per gestire più database
- **Pressione temporale**: Quando si deve consegnare velocemente senza considerare l'architettura

## Anti-pattern

**Cosa NON fare (questo è già un antipattern):**
- Condividere database tra microservizi
- Modificare schemi senza considerare l'impatto
- Non implementare meccanismi di comunicazione
- Ignorare i problemi di accoppiamento
- Non pianificare la migrazione
- Continuare a sviluppare su architettura accoppiata

## Troubleshooting

### Problemi comuni
- **Conflitti di schema**: Implementa versioning e migrazioni coordinate
- **Performance degradate**: Ottimizza le query e implementa caching
- **Deployment complesso**: Implementa feature flags e rollback
- **Conflitti tra team**: Definisci confini chiari e processi di comunicazione
- **Dati inconsistenti**: Implementa meccanismi di sincronizzazione

### Debug e monitoring
- Monitora le modifiche di schema e il loro impatto
- Traccia le dipendenze tra servizi
- Misura i tempi di deployment
- Controlla i conflitti di concorrenza
- Implementa alert per modifiche non coordinate
- Monitora le performance del database condiviso

## Performance e considerazioni

### Impatto sulle risorse
- **Storage**: Database condiviso può diventare un collo di bottiglia
- **Memoria**: Connessioni condivise possono causare problemi
- **CPU**: Query complesse impattano tutti i servizi
- **Rete**: Latenza per accessi concorrenti al database

### Scalabilità
- **Carico basso**: Funziona ma con limitazioni
- **Carico medio**: Inizia a mostrare problemi di performance
- **Carico alto**: Diventa un collo di bottiglia critico

### Colli di bottiglia
- **Database condiviso**: Punto singolo di fallimento
- **Modifiche di schema**: Bloccano tutti i servizi
- **Conflitti di concorrenza**: Possono causare deadlock
- **Deployment**: Richiede coordinamento tra tutti i team

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Database](https://laravel.com/docs/database) - Gestione database
- [Laravel Migrations](https://laravel.com/docs/migrations) - Gestione modifiche schema

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Microservices Anti-patterns](https://microservices.io/patterns/data/shared-database.html) - Anti-pattern per microservizi
- [Database Patterns](https://martinfowler.com/articles/microservices.html#databases) - Pattern database per microservizi

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
