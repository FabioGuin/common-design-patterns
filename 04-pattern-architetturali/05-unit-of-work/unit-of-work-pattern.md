# Unit of Work Pattern

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

Il pattern Unit of Work mantiene una lista degli oggetti modificati durante una transazione e coordina la scrittura delle modifiche e la risoluzione dei problemi di concorrenza. Gestisce le transazioni in modo atomico, garantendo che tutte le operazioni vengano completate con successo o che nessuna venga eseguita.

Pensa al Unit of Work come a un manager di cantiere: quando stai costruendo una casa, il manager tiene traccia di tutti i lavori in corso e si assicura che tutto venga completato correttamente. Se qualcosa va storto, può fermare tutto e ripristinare lo stato precedente, come se non fosse mai successo nulla.

## Perché ti serve

Senza Unit of Work, le tue operazioni di database sono frammentate e inconsistenti: alcune operazioni vanno a buon fine, altre falliscono, lasciando i dati in uno stato inconsistente. Risultato? Corruzione dei dati e difficoltà di gestione.

Con Unit of Work ottieni:
- **Atomicità**: Tutte le operazioni o nessuna
- **Consistenza**: I dati rimangono sempre in uno stato valido
- **Isolamento**: Le operazioni non interferiscono tra loro
- **Durabilità**: Le modifiche sono permanenti quando confermate
- **Rollback**: Possibilità di annullare tutte le modifiche
- **Performance**: Ottimizzazione delle operazioni batch
- **Concorrenza**: Gestione dei conflitti di accesso

## Come funziona

1. **Inizia una transazione** con Unit of Work
2. **Registra le operazioni** (insert, update, delete)
3. **Esegui le operazioni** in batch quando necessario
4. **Conferma o annulla** la transazione
5. **Gestisci i conflitti** di concorrenza se necessario

Il flusso è: **Begin → Register Operations → Execute → Commit/Rollback**

## Schema visivo

```
Unit of Work
    ↓
Transaction Manager
    ↓
Repository Layer
    ↓
Database
```

**Flusso dettagliato:**
```
1. Begin Transaction
   ↓
2. Register Insert Operations
   ↓
3. Register Update Operations
   ↓
4. Register Delete Operations
   ↓
5. Execute All Operations
   ↓
6. Check for Conflicts
   ↓
7. Commit or Rollback
   ↓
8. End Transaction
```

## Quando usarlo

Usa il pattern Unit of Work quando:
- Hai operazioni complesse che coinvolgono più entità
- Vuoi garantire la consistenza dei dati
- Hai bisogno di transazioni atomiche
- Vuoi ottimizzare le operazioni batch
- Hai problemi di concorrenza
- Vuoi gestire il rollback delle operazioni
- Hai operazioni che devono essere eseguite insieme

**NON usarlo quando:**
- Hai operazioni semplici e singole
- Vuoi mantenere la semplicità
- Hai vincoli di performance estremi
- L'applicazione è molto piccola
- Le operazioni sono indipendenti

## Pro e contro

**I vantaggi:**
- **Atomicità**: Tutte le operazioni o nessuna
- **Consistenza**: I dati rimangono sempre validi
- **Isolamento**: Operazioni non interferiscono
- **Durabilità**: Modifiche permanenti quando confermate
- **Rollback**: Possibilità di annullare tutto
- **Performance**: Ottimizzazione batch
- **Concorrenza**: Gestione conflitti

**Gli svantaggi:**
- **Complessità**: Gestione più complessa
- **Overhead**: Strato aggiuntivo di astrazione
- **Curva di apprendimento**: Richiede comprensione delle transazioni
- **Over-engineering**: Può essere eccessivo per operazioni semplici
- **Performance**: Può rallentare per operazioni singole

## Esempi di codice

### Pseudocodice

```
// Unit of Work Interface
interface UnitOfWorkInterface {
    function begin()
    function commit()
    function rollback()
    function registerNew(entity)
    function registerDirty(entity)
    function registerDeleted(entity)
    function registerClean(entity)
}

// Unit of Work Implementation
class UnitOfWork implements UnitOfWorkInterface {
    private newEntities = []
    private dirtyEntities = []
    private deletedEntities = []
    private cleanEntities = []
    private inTransaction = false
    
    function begin() {
        this.inTransaction = true
        database.beginTransaction()
    }
    
    function commit() {
        if (!this.inTransaction) {
            throw new Exception("No active transaction")
        }
        
        try {
            // Esegui tutte le operazioni
            this.executeInserts()
            this.executeUpdates()
            this.executeDeletes()
            
            // Conferma la transazione
            database.commit()
            this.clear()
        } catch (Exception e) {
            this.rollback()
            throw e
        }
    }
    
    function rollback() {
        if (this.inTransaction) {
            database.rollback()
            this.clear()
        }
    }
    
    function registerNew(entity) {
        this.newEntities.add(entity)
    }
    
    function registerDirty(entity) {
        this.dirtyEntities.add(entity)
    }
    
    function registerDeleted(entity) {
        this.deletedEntities.add(entity)
    }
    
    function registerClean(entity) {
        this.cleanEntities.add(entity)
    }
    
    private function executeInserts() {
        foreach (entity in this.newEntities) {
            repository.insert(entity)
        }
    }
    
    private function executeUpdates() {
        foreach (entity in this.dirtyEntities) {
            repository.update(entity)
        }
    }
    
    private function executeDeletes() {
        foreach (entity in this.deletedEntities) {
            repository.delete(entity)
        }
    }
    
    private function clear() {
        this.newEntities.clear()
        this.dirtyEntities.clear()
        this.deletedEntities.clear()
        this.cleanEntities.clear()
        this.inTransaction = false
    }
}

// Service che usa Unit of Work
class OrderService {
    constructor(unitOfWork, orderRepository, productRepository) {
        this.unitOfWork = unitOfWork
        this.orderRepository = orderRepository
        this.productRepository = productRepository
    }
    
    function createOrder(orderData, products) {
        this.unitOfWork.begin()
        
        try {
            // Crea l'ordine
            order = new Order(orderData)
            this.unitOfWork.registerNew(order)
            
            // Aggiorna i prodotti
            foreach (product in products) {
                product.decreaseStock(product.quantity)
                this.unitOfWork.registerDirty(product)
            }
            
            // Conferma tutto
            this.unitOfWork.commit()
            
            return order
        } catch (Exception e) {
            this.unitOfWork.rollback()
            throw e
        }
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Unit of Work E-commerce System](./esempio-completo/)** - Sistema e-commerce con Unit of Work

L'esempio include:
- Unit of Work per gestione transazioni
- Operazioni atomiche per ordini
- Gestione concorrenza
- Rollback automatico
- Test per le transazioni

**Nota per l'implementazione**: L'esempio completo segue il template semplificato con focus sulla dimostrazione del pattern Unit of Work, non su un'applicazione completa.

## Correlati

### Pattern

- **[Repository Pattern](./02-repository/repository-pattern.md)** - Astrae l'accesso ai dati
- **[Service Layer Pattern](./03-service-layer/service-layer-pattern.md)** - Centralizza la logica di business
- **[DTO Pattern](./04-dto/dto-pattern.md)** - Trasferisce dati tra layer
- **[MVC Pattern](./01-mvc/mvc-pattern.md)** - Architettura base per applicazioni web

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Sistemi e-commerce**: Gestione ordini e inventario
- **Sistemi bancari**: Transazioni finanziarie
- **Sistemi di gestione**: Operazioni complesse multi-entità
- **Sistemi di integrazione**: Sincronizzazione dati
- **Applicazioni enterprise**: Operazioni critiche

## Anti-pattern

**Cosa NON fare:**
- **Fat Unit of Work**: Unit of Work che gestisce troppe operazioni
- **Anemic Unit of Work**: Unit of Work senza logica
- **Unit of Work per tutto**: Non serve per operazioni semplici
- **Tight Coupling**: Unit of Work troppo legato ai repository
- **God Unit of Work**: Un singolo Unit of Work per tutto

## Troubleshooting

### Problemi comuni

- **Deadlock**: Gestisci i deadlock delle transazioni
- **Timeout**: Imposta timeout appropriati
- **Concorrenza**: Gestisci i conflitti di concorrenza
- **Performance**: Ottimizza le operazioni batch
- **Memory**: Gestisci l'uso della memoria per entità grandi

### Debug e monitoring

- **Log delle transazioni**: Traccia le operazioni
- **Performance**: Monitora i tempi di esecuzione
- **Deadlock**: Monitora i deadlock
- **Rollback**: Traccia i rollback

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Gestione delle entità in memoria
- **CPU**: Overhead per gestione transazioni
- **I/O**: Operazioni batch ottimizzate

### Scalabilità

- **Carico basso**: Unit of Work non aggiunge overhead significativo
- **Carico medio**: Ottimizzazione delle operazioni batch
- **Carico alto**: Gestione concorrenza e deadlock

### Colli di bottiglia

- **Deadlock**: Gestisci i deadlock
- **Timeout**: Imposta timeout appropriati
- **Memory usage**: Gestisci l'uso della memoria

## Risorse utili

### Documentazione ufficiale

- [Laravel Database Transactions](https://laravel.com/docs/database#database-transactions) - Transazioni database
- [Unit of Work Pattern Wikipedia](https://en.wikipedia.org/wiki/Unit_of_work_pattern) - Teoria del pattern
- [Martin Fowler on Unit of Work](https://martinfowler.com/eaaCatalog/unitOfWork.html) - Definizione del pattern

### Laravel specifico

- [Laravel DB Transactions](https://laravel.com/docs/database#database-transactions) - Implementazione Laravel
- [Laravel Eloquent Transactions](https://laravel.com/docs/eloquent#database-transactions) - Transazioni Eloquent
- [Laravel Queue Jobs](https://laravel.com/docs/queues) - Job per operazioni asincrone

### Esempi e tutorial

- [Laravel Unit of Work](https://laravel.com/docs/database#database-transactions) - Implementazione Laravel
- [Unit of Work Best Practices](https://laravel.com/docs/database#database-transactions) - Best practices

### Strumenti di supporto

- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug transazioni
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring applicazione
