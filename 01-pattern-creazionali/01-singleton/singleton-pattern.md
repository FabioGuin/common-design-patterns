# Singleton Pattern

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

Il Singleton ti assicura che una classe abbia sempre e solo una istanza. Quando chiami il metodo per ottenere l'istanza, ricevi sempre la stessa, anche se la chiami da parti diverse del codice.

È perfetto per cose come connessioni al database, configurazioni dell'app o servizi di logging che devono essere condivisi in tutta l'applicazione.

## Perché ti serve

Immagina di avere un logger che deve scrivere su file. Se crei una nuova istanza del logger ogni volta che ne hai bisogno, finirai con:
- File di log sparsi ovunque
- Perdita di messaggi
- Confusione totale

Il Singleton risolve questo problema: una sola istanza, un solo posto dove scrivere, tutto sotto controllo.

## Come funziona

Il trucco è semplice:
1. Il costruttore è privato, così nessuno può fare `new Singleton()`
2. C'è un metodo statico che ti restituisce l'istanza
3. La prima volta che chiami il metodo, crea l'istanza
4. Le volte successive, ti restituisce sempre la stessa

## Schema visivo

```
Flusso di creazione:
Client → getInstance() → Singleton
                        ↓
                   $instance = null?
                        ↓
                   SÌ → Crea nuova istanza
                        ↓
                   Restituisce istanza

Flusso di riutilizzo:
Client → getInstance() → Singleton
                        ↓
                   $instance = null?
                        ↓
                   NO → Restituisce istanza esistente
```

*Il diagramma mostra come il Singleton controlla se l'istanza esiste già e la crea solo se necessario.*

## Quando usarlo

Usa il Singleton quando:
- Hai bisogno di una sola istanza per tutta l'app (database, logger, cache)
- L'oggetto è costoso da creare e vuoi riutilizzarlo
- Devi coordinare l'accesso a una risorsa condivisa
- Vuoi garantire un punto di accesso globale a una risorsa
- Hai bisogno di controllare rigorosamente il numero di istanze

**NON usarlo quando:**
- Hai bisogno di più istanze della stessa classe
- Lavori con applicazioni multi-threaded (può creare problemi)
- Rende il codice difficile da testare
- L'oggetto cambia stato troppo spesso
- Stai usando il Singleton solo per evitare di passare parametri

## Pro e contro

**I vantaggi:**
- Una sola istanza garantita
- Accesso controllato da qualsiasi parte del codice
- Si crea solo quando serve (lazy loading)
- Risparmi memoria e risorse
- Perfetto per risorse condivise

**Gli svantaggi:**
- Nasconde le dipendenze (difficile capire da dove viene l'oggetto)
- Difficile da testare (stato globale)
- Viola il principio di responsabilità singola
- Problemi con applicazioni multi-threaded
- Crea accoppiamento forte

## Esempi di codice

### Pseudocodice
```
// Struttura base del Singleton
class Singleton {
    private static instance = null
    
    private constructor() {
        // Inizializzazione privata
        // Solo questa classe può creare istanze
    }
    
    public static getInstance() {
        if (instance == null) {
            instance = new Singleton()
        }
        return instance
    }
    
    private clone() {
        // Impedisce la clonazione
    }
    
    private wakeup() {
        // Impedisce la deserializzazione
    }
}

// Utilizzo
oggetto1 = Singleton.getInstance()
oggetto2 = Singleton.getInstance()
// oggetto1 e oggetto2 sono la stessa istanza
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Logger Singleton Completo](./esempio-completo/)** - Un sistema di logging completo con tutto quello che ti serve

L'esempio include:
- Logger service singleton funzionante
- Salvataggio dei log su file
- Integrazione con il Service Container di Laravel
- Controller e routes per testare
- Service Provider personalizzato
- Livelli di log (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- API per leggere e gestire i log

## Correlati

### Pattern

- **[Factory Method](./02-factory-method/factory-method-pattern.md)** - Se hai bisogno di creare istanze diverse ma sempre una per tipo
- **[Object Pool](./06-object-pool/object-pool-pattern.md)** - Per riutilizzare oggetti costosi invece di crearne sempre uno solo
- **[Service Locator](../05-pattern-laravel-specifici/01-service-container/service-container-pattern.md)** - Alternativa al Singleton per l'accesso globale, ma più flessibile
- **[Dependency Injection](../05-pattern-laravel-specifici/01-service-container/service-container-pattern.md)** - Approccio moderno che evita i problemi del Singleton

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Laravel Service Container**: Laravel usa il Singleton per gestire le istanze dei servizi nel container di dipendenze
- **Database Connection**: La maggior parte degli ORM (Eloquent, Doctrine) usa il Singleton per le connessioni al database
- **Logger Systems**: Sistemi di logging come Monolog usano il Singleton per garantire un solo logger per applicazione
- **Configuration Managers**: Gestori di configurazione che leggono file .env o config per evitare letture multiple
- **Cache Systems**: Sistemi di cache come Redis o Memcached spesso usano il Singleton per la connessione

## Anti-pattern

**Cosa NON fare:**
- **Singleton per tutto**: Non usare il Singleton per ogni classe solo perché è comodo
- **Singleton con stato mutabile**: Evita Singleton che cambiano stato frequentemente, rendono il codice imprevedibile
- **Singleton come Service Locator**: Non usare il Singleton per accedere a servizi casuali, viola il principio di responsabilità
- **Singleton thread-unsafe**: In applicazioni multi-threaded, implementa la sincronizzazione correttamente
- **Singleton con troppe responsabilità**: Non mettere troppa logica in una classe Singleton

## Troubleshooting

### Problemi comuni
- **"Cannot instantiate class"**: Assicurati che il costruttore sia privato e che usi `getInstance()`
- **"Multiple instances created"**: Verifica che `$instance` sia statico e controllato correttamente
- **"State not shared"**: Controlla che stai usando sempre la stessa istanza tramite `getInstance()`
- **"Memory leak"**: Se l'istanza non viene mai rilasciata, considera se il Singleton è davvero necessario

### Debug e monitoring
- **Log delle istanze**: Aggiungi logging per tracciare quando viene creata l'istanza
- **Controllo stato**: Monitora lo stato dell'istanza per verificare che sia condivisa correttamente
- **Memory usage**: Traccia l'uso di memoria per verificare che non ci siano leak
- **Thread safety**: In ambienti multi-thread, monitora race conditions

### Metriche utili
- **Numero di chiamate a getInstance()**: Per capire quanto viene usato
- **Tempo di creazione istanza**: Per verificare che il lazy loading funzioni
- **Memoria utilizzata**: Per controllare che non ci siano leak
- **Errori di istanziazione**: Per identificare usi scorretti del pattern

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Una sola istanza in memoria per tutta l'applicazione - molto efficiente (tipicamente 1-10KB)
- **CPU**: Lazy loading significa che l'oggetto si crea solo quando serve - overhead minimo
- **I/O**: Se il Singleton gestisce file o database, le operazioni I/O sono condivise e ottimizzate

### Scalabilità
- **Carico basso**: Perfetto, nessun overhead aggiuntivo
- **Carico medio**: Funziona bene, ma può diventare un collo di bottiglia se l'istanza ha operazioni costose
- **Carico alto**: Può limitare la scalabilità orizzontale se l'istanza ha stato condiviso

### Colli di bottiglia
- **Operazioni costose**: Se l'istanza singleton ha operazioni lente, rallenta tutta l'applicazione
- **Thread contention**: In ambienti multi-thread, l'accesso concorrente può creare colli di bottiglia
- **Memory pressure**: Se l'istanza cresce troppo, può causare problemi di memoria
- **Testing**: Il singleton può rendere i test più lenti e complessi

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Singleton](https://refactoring.guru/design-patterns/singleton) - Spiegazione visuale con esempi

### Laravel specifico
- [Laravel Service Container](https://laravel.com/docs/container) - Come Laravel gestisce le dipendenze
- [Laravel Service Providers](https://laravel.com/docs/providers) - Per registrare servizi singleton

### Esempi e tutorial
- [Singleton Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
- [Singleton Anti-Pattern](https://stackoverflow.com/questions/137975/what-is-so-bad-about-singletons) - Discussione sui problemi del Singleton

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
