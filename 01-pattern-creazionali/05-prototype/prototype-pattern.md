# Prototype Pattern

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

Il Prototype Pattern ti permette di creare nuovi oggetti copiando un prototipo esistente, invece di creare l'oggetto da zero. Definisce un'interfaccia per clonare se stesso, permettendo di creare copie personalizzate.

È come avere un timbro: invece di disegnare ogni volta lo stesso disegno, usi il timbro per creare copie identiche, e poi puoi personalizzare ogni copia come vuoi.

## Perché ti serve

Immagina di dover creare molti oggetti simili ma con piccole differenze (come configurazioni di utenti, template di documenti, o impostazioni di sistema). Senza Prototype Pattern, finiresti con:

- Codice che crea oggetti da zero ogni volta
- Logica di inizializzazione duplicata
- Difficoltà a creare varianti di oggetti esistenti
- Violazione del principio DRY (Don't Repeat Yourself)

Il Prototype risolve questo: crei un prototipo una volta, e poi lo cloni tutte le volte che serve, personalizzando ogni copia.

## Come funziona

Il meccanismo è elegante:
1. **Prototype**: Interfaccia che definisce il metodo `clone()`
2. **ConcretePrototype**: Implementazione concreta che implementa `clone()`
3. **Client**: Usa il prototipo per creare copie

Il client clona il prototipo e poi personalizza la copia secondo le sue necessità.

## Schema visivo

```
Flusso di clonazione:
Client → Prototype → clone()
                ↓
           ConcretePrototype → new ConcretePrototype()
                            → copy properties
                            ↓
                       Restituisce copia personalizzata

Gerarchia delle classi:
Prototype (interfaccia)
    ↓
ConcretePrototype1 → clone() → ConcretePrototype1 (copia)
ConcretePrototype2 → clone() → ConcretePrototype2 (copia)
ConcretePrototype3 → clone() → ConcretePrototype3 (copia)
```

*Il diagramma mostra come ogni ConcretePrototype può clonare se stesso per creare copie personalizzate.*

## Quando usarlo

Usa il Prototype Pattern quando:
- Devi creare molti oggetti simili con piccole differenze
- La creazione di un oggetto è costosa (database, file, network)
- Vuoi evitare di creare gerarchie di classi complesse
- Hai bisogno di creare oggetti in runtime
- Vuoi personalizzare oggetti esistenti
- Hai configurazioni complesse che vuoi riutilizzare

**NON usarlo quando:**
- Gli oggetti sono semplici e facili da creare
- Non hai bisogno di copie personalizzate
- La clonazione è più costosa della creazione
- L'overhead del pattern non è giustificato
- Hai solo un tipo di oggetto da creare

## Pro e contro

**I vantaggi:**
- Evita la creazione costosa di oggetti da zero
- Permette la personalizzazione di copie esistenti
- Riduce la complessità delle gerarchie di classi
- Facilita la creazione di oggetti in runtime
- Migliora le performance per oggetti costosi

**Gli svantaggi:**
- Può essere complesso implementare la clonazione corretta
- Difficile gestire la clonazione profonda vs superficiale
- Può creare confusione tra originale e copia
- Difficile da testare se la clonazione è complessa
- Può causare problemi di memoria se non gestita correttamente

## Esempi di codice

### Pseudocodice
```
// Interfaccia Prototype
interface Prototype {
    method clone() returns Prototype
}

// Prototipo concreto
class ConcretePrototype implements Prototype {
    private property1
    private property2
    private property3
    
    constructor(property1, property2, property3) {
        this.property1 = property1
        this.property2 = property2
        this.property3 = property3
    }
    
    method clone() returns Prototype {
        // Clonazione profonda
        return new ConcretePrototype(
            this.property1,
            this.property2,
            this.property3
        )
    }
    
    method setProperty1(value) {
        this.property1 = value
    }
    
    method setProperty2(value) {
        this.property2 = value
    }
    
    method setProperty3(value) {
        this.property3 = value
    }
}

// Utilizzo
prototype = new ConcretePrototype("A", "B", "C")
copy1 = prototype.clone()
copy1.setProperty1("X") // Personalizza la copia

copy2 = prototype.clone()
copy2.setProperty2("Y") // Personalizza un'altra copia

// prototype rimane invariato
// copy1 e copy2 sono copie personalizzate
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Template System con Prototype](./esempio-completo/)** - Sistema completo per gestire template di documenti

L'esempio include:
- Prototype per creare template di documenti (PDF, Word, HTML)
- Clonazione profonda e superficiale
- Personalizzazione di template esistenti
- Integrazione con Laravel Storage
- Service Provider per registrare i prototipi
- Controller con dependency injection
- Test unitari per i prototipi
- API RESTful per gestire i template

## Correlati

### Pattern

- **[Factory Method](./02-factory-method/factory-method-pattern.md)** - Se hai bisogno di creare oggetti diversi invece di copie
- **[Abstract Factory](./03-abstract-factory/abstract-factory-pattern.md)** - Se hai bisogno di creare famiglie di oggetti correlati
- **[Builder](./04-builder/builder-pattern.md)** - Per costruire oggetti complessi passo dopo passo
- **[Singleton](./01-singleton/singleton-pattern.md)** - Spesso usato insieme al Prototype per gestire i prototipi

### Principi e Metodologie

- **[DRY Pattern](../12-pattern-metodologie-concettuali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../12-pattern-metodologie-concettuali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../12-pattern-metodologie-concettuali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../12-pattern-metodologie-concettuali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Laravel Model Factories**: Laravel usa il Prototype Pattern per creare istanze di modelli per i test
- **Symfony Form Prototypes**: Symfony usa il Prototype Pattern per creare copie di form esistenti
- **PHPUnit Test Doubles**: PHPUnit usa il Prototype Pattern per creare copie di mock e stub
- **Document Generators**: Librerie come TCPDF usano il Prototype Pattern per creare copie di documenti
- **Configuration Management**: Sistemi di configurazione usano il Prototype Pattern per creare copie di configurazioni

## Anti-pattern

**Cosa NON fare:**
- **Clonazione superficiale quando serve profonda**: Evita clonazioni superficiali quando gli oggetti hanno riferimenti
- **Clonazione profonda quando serve superficiale**: Non fare clonazioni profonde se non necessario, spreca memoria
- **Prototype senza interfacce**: Sempre definire interfacce astratte per i prototipi
- **Prototype per oggetti semplici**: Non usare il Prototype Pattern per oggetti che si creano facilmente
- **Prototype troppo complessi**: Evita prototipi che fanno troppo lavoro, violano il principio di responsabilità singola

## Troubleshooting

### Problemi comuni
- **"Cannot clone object"**: Assicurati che la classe implementi l'interfaccia Prototype e il metodo clone()
- **"Shallow copy issues"**: Verifica che la clonazione sia profonda se l'oggetto ha riferimenti
- **"Memory leaks"**: Controlla che la clonazione non crei riferimenti circolari
- **"Property not copied"**: Assicurati che tutte le proprietà siano copiate correttamente

### Debug e monitoring
- **Log delle clonazioni**: Aggiungi logging per tracciare quando vengono create le copie
- **Controllo memoria**: Verifica che la clonazione non causi leak di memoria
- **Performance clonazione**: Monitora il tempo di clonazione per identificare prototipi lenti
- **Validazione copie**: Traccia che le copie siano corrette e complete

### Metriche utili
- **Numero di copie create**: Per capire l'utilizzo dei diversi prototipi
- **Tempo di clonazione**: Per identificare prototipi che potrebbero essere ottimizzati
- **Errori di clonazione**: Per identificare problemi con la clonazione
- **Utilizzo memoria**: Per verificare che non ci siano leak di memoria

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead moderato per le classi prototipo e interfacce (tipicamente 10-25KB)
- **CPU**: La clonazione è generalmente più veloce della creazione da zero (1-5ms vs 5-20ms)
- **I/O**: Se i prototipi creano risorse esterne, l'I/O è gestito dai prototipi stessi

### Scalabilità
- **Carico basso**: Perfetto, overhead trascurabile
- **Carico medio**: Funziona bene, l'overhead è compensato dalle performance
- **Carico alto**: Può diventare un collo di bottiglia se la clonazione è complessa

### Colli di bottiglia
- **Clonazione profonda**: Se gli oggetti hanno molti riferimenti, la clonazione può essere lenta
- **Memory allocation**: Creare molte copie può causare frammentazione
- **Circular references**: Se non gestite correttamente, possono causare problemi di memoria
- **Validation**: Se la validazione è complessa, può rallentare la clonazione

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Prototype](https://refactoring.guru/design-patterns/prototype) - Spiegazione visuale con esempi

### Laravel specifico
- [Laravel Model Factories](https://laravel.com/docs/eloquent-factories) - Come Laravel usa il Prototype Pattern
- [Laravel Service Container](https://laravel.com/docs/container) - Per gestire le dipendenze

### Esempi e tutorial
- [Prototype Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
- [Deep vs Shallow Copy in PHP](https://www.php.net/manual/en/language.oop5.cloning.php#language.oop5.cloning) - Spiegazione dettagliata della clonazione

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
