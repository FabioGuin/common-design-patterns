# Object Pool Pattern

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

L'Object Pool Pattern ti permette di riutilizzare oggetti costosi invece di crearli e distruggerli ogni volta. Mantiene un pool di oggetti pre-istanziati che possono essere presi in prestito e restituiti.

È come avere un parcheggio per biciclette: invece di comprare una bicicletta ogni volta che ne hai bisogno, prendi una bicicletta dal parcheggio, la usi, e la riporti indietro per il prossimo utente.

## Perché ti serve

Immagina di dover creare connessioni al database, thread, o oggetti grafici costosi. Senza Object Pool, finiresti con:

- Creazione e distruzione costante di oggetti costosi
- Spreco di risorse di sistema (memoria, CPU, I/O)
- Lentezza nell'applicazione per la creazione ripetuta
- Possibili limiti di risorse del sistema

L'Object Pool risolve questo: crei gli oggetti una volta, li riutilizzi tutte le volte che serve, e li restituisci al pool quando hai finito.

## Come funziona

Il meccanismo è efficiente:
1. **ObjectPool**: Gestisce il pool di oggetti pre-istanziati
2. **PooledObject**: Interfaccia per gli oggetti che possono essere riutilizzati
3. **ConcretePooledObject**: Implementazione concreta dell'oggetto riutilizzabile
4. **Client**: Prende e restituisce oggetti dal pool

Il client prende un oggetto dal pool, lo usa, e lo restituisce quando ha finito.

## Schema visivo

```
Flusso di utilizzo:
Client → ObjectPool → acquire()
                ↓
           PooledObject (disponibile)
                ↓
           Client usa l'oggetto
                ↓
           Client → ObjectPool → release()
                ↓
           PooledObject (disponibile per riuso)

Gestione del pool:
ObjectPool
    ↓
PooledObject1 (disponibile)
PooledObject2 (in uso)
PooledObject3 (disponibile)
PooledObject4 (in uso)
...
```

*Il diagramma mostra come il pool gestisce gli oggetti tra disponibili e in uso, permettendo il riutilizzo efficiente.*

## Quando usarlo

Usa l'Object Pool Pattern quando:
- La creazione di oggetti è costosa (database, thread, file, network)
- Hai bisogno di limitare il numero di istanze di un oggetto
- Vuoi migliorare le performance dell'applicazione
- Gestisci risorse limitate del sistema
- Hai picchi di utilizzo seguiti da periodi di inattività
- Vuoi controllare l'uso di memoria

**NON usarlo quando:**
- Gli oggetti sono semplici e facili da creare
- Non hai limiti di risorse
- L'overhead del pool non è giustificato
- Gli oggetti cambiano stato frequentemente
- Hai bisogno di oggetti sempre freschi

## Pro e contro

**I vantaggi:**
- Migliora significativamente le performance
- Riduce l'uso di memoria e CPU
- Controlla l'uso di risorse limitate
- Evita la creazione costante di oggetti
- Facilita il debugging e il monitoring

**Gli svantaggi:**
- Aumenta la complessità del codice
- Può causare problemi di stato se non gestito correttamente
- Difficile da implementare correttamente
- Può creare memory leak se non gestito
- Può causare problemi di thread safety

## Esempi di codice

### Pseudocodice
```
// Interfaccia per oggetti riutilizzabili
interface PooledObject {
    method reset()
    method isAvailable() returns boolean
}

// Oggetto concreto riutilizzabile
class ConcretePooledObject implements PooledObject {
    private isInUse = false
    private data
    
    method reset() {
        this.data = null
        this.isInUse = false
    }
    
    method isAvailable() returns boolean {
        return not this.isInUse
    }
    
    method setInUse() {
        this.isInUse = true
    }
    
    method setData(data) {
        this.data = data
    }
}

// Pool di oggetti
class ObjectPool {
    private availableObjects = []
    private inUseObjects = []
    private maxSize = 10
    
    constructor() {
        // Pre-crea alcuni oggetti
        for i = 0 to 5 {
            this.availableObjects.add(new ConcretePooledObject())
        }
    }
    
    method acquire() returns PooledObject {
        if this.availableObjects.isEmpty() {
            if this.inUseObjects.size() < this.maxSize {
                object = new ConcretePooledObject()
            } else {
                return null // Pool pieno
            }
        } else {
            object = this.availableObjects.removeFirst()
        }
        
        object.setInUse()
        this.inUseObjects.add(object)
        return object
    }
    
    method release(object) {
        object.reset()
        this.inUseObjects.remove(object)
        this.availableObjects.add(object)
    }
}

// Utilizzo
pool = new ObjectPool()
object1 = pool.acquire() // Prende un oggetto dal pool
object1.setData("dati 1")
// Usa l'oggetto...
pool.release(object1) // Restituisce l'oggetto al pool

object2 = pool.acquire() // Può essere lo stesso oggetto di prima
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Database Connection Pool](./esempio-completo/)** - Sistema completo per gestire connessioni al database

L'esempio include:
- Pool per connessioni MySQL, PostgreSQL, SQLite
- Gestione automatica del pool con Laravel
- Monitoring e logging delle connessioni
- Service Provider per registrare il pool
- Controller con dependency injection
- Test unitari per il pool
- API RESTful per monitorare il pool

## Correlati

### Pattern

- **[Singleton](./01-singleton/singleton-pattern.md)** - Se hai bisogno di una sola istanza del pool
- **[Factory Method](./02-factory-method/factory-method-pattern.md)** - Se hai bisogno di creare oggetti diversi nel pool
- **[Prototype](./05-prototype/prototype-pattern.md)** - Se hai bisogno di clonare oggetti esistenti nel pool
- **[Builder](./04-builder/builder-pattern.md)** - Per costruire oggetti complessi nel pool

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Laravel Database Pool**: Laravel usa l'Object Pool Pattern per gestire le connessioni al database
- **Symfony Process Pool**: Symfony usa l'Object Pool Pattern per gestire processi
- **PHPUnit Test Pool**: PHPUnit usa l'Object Pool Pattern per gestire test case
- **Document Generators**: Librerie come TCPDF usano l'Object Pool Pattern per gestire documenti
- **Thread Pools**: Sistemi multi-thread usano l'Object Pool Pattern per gestire thread

## Anti-pattern

**Cosa NON fare:**
- **Pool senza limiti**: Evita pool che possono crescere indefinitamente
- **Oggetti con stato**: Non riutilizzare oggetti che mantengono stato tra gli utilizzi
- **Pool senza reset**: Sempre resettare gli oggetti prima di riutilizzarli
- **Pool thread-unsafe**: In ambienti multi-thread, implementa la sincronizzazione correttamente
- **Pool troppo complessi**: Evita pool che fanno troppo lavoro, violano il principio di responsabilità singola

## Troubleshooting

### Problemi comuni
- **"Pool exhausted"**: Il pool non ha oggetti disponibili, aumenta la dimensione o implementa la creazione dinamica
- **"Object not reset"**: L'oggetto non è stato resettato correttamente prima del riutilizzo
- **"Memory leak"**: Gli oggetti non vengono restituiti al pool, implementa il garbage collection
- **"Thread safety issues"**: In ambienti multi-thread, implementa la sincronizzazione

### Debug e monitoring
- **Log del pool**: Aggiungi logging per tracciare l'utilizzo del pool
- **Controllo stato**: Verifica che gli oggetti siano resettati correttamente
- **Performance pool**: Monitora il tempo di acquisizione e rilascio
- **Memory usage**: Traccia l'uso di memoria per verificare che non ci siano leak

### Metriche utili
- **Numero di oggetti nel pool**: Per capire l'utilizzo del pool
- **Tempo di acquisizione**: Per identificare pool che potrebbero essere ottimizzati
- **Errori di pool**: Per identificare problemi con la gestione del pool
- **Utilizzo memoria**: Per verificare che non ci siano leak di memoria

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead moderato per il pool e gli oggetti pre-istanziati (tipicamente 50-200KB)
- **CPU**: La gestione del pool è molto veloce (0.1-1ms per acquisizione/rilascio)
- **I/O**: Se gli oggetti gestiscono I/O, l'I/O è condiviso e ottimizzato

### Scalabilità
- **Carico basso**: Perfetto, overhead trascurabile
- **Carico medio**: Funziona molto bene, migliora significativamente le performance
- **Carico alto**: Essenziale per gestire picchi di utilizzo senza esaurire le risorse

### Colli di bottiglia
- **Pool size**: Se il pool è troppo piccolo, può diventare un collo di bottiglia
- **Object creation**: Se la creazione di oggetti è molto costosa, il pool è essenziale
- **Memory pressure**: Se il pool è troppo grande, può causare problemi di memoria
- **Thread contention**: In ambienti multi-thread, l'accesso al pool può creare colli di bottiglia

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Object Pool](https://refactoring.guru/design-patterns/object-pool) - Spiegazione visuale con esempi

### Laravel specifico
- [Laravel Database Pool](https://laravel.com/docs/database) - Come Laravel usa l'Object Pool Pattern
- [Laravel Service Container](https://laravel.com/docs/container) - Per gestire le dipendenze

### Esempi e tutorial
- [Object Pool Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
- [Object Pool vs Singleton](https://www.tutorialspoint.com/design_pattern/object_pool_pattern.htm) - Confronto tra Object Pool e Singleton

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
