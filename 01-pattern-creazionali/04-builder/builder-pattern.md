# Builder Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Pattern correlati](#pattern-correlati)
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

Il Builder Pattern ti permette di costruire oggetti complessi passo dopo passo, usando un processo di costruzione che può creare diverse rappresentazioni dello stesso oggetto.

È come costruire una casa: invece di avere un costruttore che sa tutto e costruisce tutto in una volta, hai diversi specialisti (elettricista, idraulico, muratore) che lavorano in sequenza, ognuno con le sue competenze specifiche.

## Perché ti serve

Immagina di dover creare un'email complessa con allegati, template, destinatari multipli e configurazioni avanzate. Senza Builder Pattern, finiresti con:

- Costruttori con troppi parametri (10+ parametri)
- Logica di costruzione sparsa e duplicata
- Difficoltà a creare varianti dello stesso oggetto
- Violazione del principio "aperto per estensione, chiuso per modifica"

Il Builder risolve questo: un builder sa come costruire l'oggetto passo dopo passo, e puoi avere diversi builder per diverse rappresentazioni.

## Come funziona

Il meccanismo è elegante:
1. **Product**: L'oggetto complesso che vuoi costruire
2. **Builder**: Interfaccia astratta per i passi di costruzione
3. **ConcreteBuilder**: Implementazione specifica del builder
4. **Director**: Opzionale, coordina il processo di costruzione

Il client usa il builder per costruire l'oggetto passo dopo passo, e alla fine ottiene l'oggetto completo.

## Schema visivo

```
Flusso di costruzione:
Client → Director → Builder → buildPartA()
                        → buildPartB()
                        → buildPartC()
                        ↓
                   Product (oggetto completo)

Gerarchia delle classi:
Builder (interfaccia)
    ↓
ConcreteBuilder1 → buildPartA() → Product1
ConcreteBuilder2 → buildPartA() → Product2
ConcreteBuilder3 → buildPartA() → Product3

Director → setBuilder(Builder)
         → construct() → builder.buildPartA()
                      → builder.buildPartB()
                      → builder.buildPartC()
```

*Il diagramma mostra come il Director coordina il Builder per costruire l'oggetto passo dopo passo, permettendo diverse rappresentazioni.*

## Quando usarlo

Usa il Builder Pattern quando:
- Devi creare oggetti complessi con molti parametri
- Hai bisogno di diverse rappresentazioni dello stesso oggetto
- Vuoi costruire oggetti passo dopo passo
- Hai logica di costruzione complessa che vuoi separare
- Vuoi rendere il processo di costruzione più flessibile
- Hai bisogno di validazione durante la costruzione

**NON usarlo quando:**
- L'oggetto è semplice e ha pochi parametri
- Non hai bisogno di diverse rappresentazioni
- La logica di costruzione è banale
- L'overhead del pattern non è giustificato
- Hai solo una rappresentazione dell'oggetto

## Pro e contro

**I vantaggi:**
- Costruisce oggetti complessi passo dopo passo
- Permette diverse rappresentazioni dello stesso oggetto
- Isola la logica di costruzione dal prodotto
- Rispetta il principio Single Responsibility
- Facilita la validazione durante la costruzione

**Gli svantaggi:**
- Aumenta la complessità del codice
- Richiede molte classi e interfacce
- Può essere eccessivo per oggetti semplici
- Difficile da estendere se la struttura cambia
- Può creare oggetti in stato inconsistente

## Esempi di codice

### Pseudocodice
```
// Prodotto complesso
class Product {
    private partA
    private partB
    private partC
    
    method setPartA(partA) {
        this.partA = partA
    }
    
    method setPartB(partB) {
        this.partB = partB
    }
    
    method setPartC(partC) {
        this.partC = partC
    }
}

// Builder astratto
interface Builder {
    method buildPartA()
    method buildPartB()
    method buildPartC()
    method getResult() returns Product
}

// Builder concreto
class ConcreteBuilder implements Builder {
    private product = new Product()
    
    method buildPartA() {
        this.product.setPartA("Part A built")
    }
    
    method buildPartB() {
        this.product.setPartB("Part B built")
    }
    
    method buildPartC() {
        this.product.setPartC("Part C built")
    }
    
    method getResult() returns Product {
        return this.product
    }
}

// Director (opzionale)
class Director {
    private builder
    
    method setBuilder(builder) {
        this.builder = builder
    }
    
    method construct() {
        this.builder.buildPartA()
        this.builder.buildPartB()
        this.builder.buildPartC()
    }
}

// Utilizzo
builder = new ConcreteBuilder()
director = new Director()
director.setBuilder(builder)
director.construct()
product = builder.getResult()
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Email Builder Completo](./esempio-completo/)** - Sistema completo per costruire email complesse

L'esempio include:
- Builder per creare email con allegati, template, destinatari
- Diversi tipi di email (marketing, notifiche, transazionali)
- Integrazione con Laravel Mail
- Validazione durante la costruzione
- Service Provider per registrare i builder
- Controller con dependency injection
- Test unitari per i builder
- API RESTful per gestire le email

## Pattern correlati

- **Factory Method**: Se hai bisogno di creare oggetti semplici invece di complessi
- **Abstract Factory**: Se hai bisogno di creare famiglie di oggetti correlati
- **Prototype**: Per clonare oggetti esistenti invece di costruirli da zero
- **Composite**: Spesso usato insieme al Builder per costruire strutture gerarchiche

## Esempi di uso reale

- **Laravel Query Builder**: Laravel usa il Builder Pattern per costruire query SQL complesse
- **Symfony Form Builder**: Symfony usa il Builder Pattern per costruire form complessi
- **PHPUnit Test Builder**: PHPUnit usa il Builder Pattern per costruire test case complessi
- **Document Generators**: Librerie come TCPDF usano il Builder Pattern per costruire documenti
- **API Client Builders**: Librerie come Guzzle usano il Builder Pattern per costruire richieste HTTP

## Anti-pattern

**Cosa NON fare:**
- **Builder con troppi metodi**: Evita builder con troppi metodi di costruzione
- **Builder che conosce tutto**: Non far conoscere al builder dettagli specifici del prodotto
- **Builder senza interfacce**: Sempre definire interfacce astratte per i builder
- **Builder per oggetti semplici**: Non usare il Builder Pattern per oggetti che si creano facilmente
- **Builder troppo complessi**: Evita builder che fanno troppo lavoro, violano il principio di responsabilità singola

## Troubleshooting

### Problemi comuni
- **"Cannot instantiate abstract class"**: Assicurati di implementare tutte le interfacce astratte del Builder
- **"Product not built correctly"**: Verifica che tutti i passi di costruzione siano chiamati nell'ordine corretto
- **"Builder method not found"**: Controlla che i metodi di costruzione siano definiti correttamente nell'interfaccia
- **"Product in inconsistent state"**: Assicurati che la validazione sia fatta durante la costruzione

### Debug e monitoring
- **Log delle costruzioni**: Aggiungi logging per tracciare ogni passo di costruzione
- **Controllo stato**: Verifica che il prodotto sia in stato consistente dopo ogni passo
- **Performance builder**: Monitora il tempo di costruzione per identificare builder lenti
- **Memory usage**: Traccia l'uso di memoria per verificare che non ci siano leak

### Metriche utili
- **Numero di prodotti costruiti**: Per capire l'utilizzo dei diversi builder
- **Tempo di costruzione**: Per identificare builder che potrebbero essere ottimizzati
- **Errori di costruzione**: Per identificare problemi con i passi di costruzione
- **Utilizzo interfacce**: Per verificare che i client usino le interfacce astratte

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead moderato per le classi builder e interfacce (tipicamente 15-30KB)
- **CPU**: La costruzione passo dopo passo è leggermente più lenta del costruttore diretto (3-10ms overhead)
- **I/O**: Se i prodotti creano risorse esterne, l'I/O è gestito dai prodotti stessi

### Scalabilità
- **Carico basso**: Funziona bene, overhead accettabile
- **Carico medio**: L'overhead è compensato dalla flessibilità e organizzazione
- **Carico alto**: Può diventare un collo di bottiglia se i builder sono complessi

### Colli di bottiglia
- **Builder complessi**: Se la logica di costruzione è troppo elaborata
- **Troppi passi**: Gestire troppi passi di costruzione può diventare complesso
- **Memory allocation**: Creare molti oggetti complessi può causare frammentazione
- **Validation**: Se la validazione è complessa, può rallentare la costruzione

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Builder](https://refactoring.guru/design-patterns/builder) - Spiegazione visuale con esempi

### Laravel specifico
- [Laravel Query Builder](https://laravel.com/docs/queries) - Come Laravel usa il Builder Pattern
- [Laravel Service Container](https://laravel.com/docs/container) - Per gestire le dipendenze

### Esempi e tutorial
- [Builder Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
- [Builder Pattern vs Factory Pattern](https://www.tutorialspoint.com/design_pattern/builder_pattern.htm) - Confronto tra Builder e Factory

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
