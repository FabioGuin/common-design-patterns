# Factory Method Pattern

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

Il Factory Method ti permette di creare oggetti senza sapere esattamente quale tipo di oggetto stai creando. Definisce un'interfaccia per creare oggetti, ma lascia alle sottoclassi decidere quale classe specifica istanziare.

È come avere un'azienda che produce automobili: l'azienda sa come produrre auto in generale, ma ogni stabilimento decide se produrre SUV, berline o sportive.

## Perché ti serve

Immagina di dover creare diversi tipi di documenti (PDF, Word, Excel) nel tuo sistema. Senza Factory Method, finiresti con:

- Codice che conosce troppi dettagli di ogni tipo di documento
- Logica di creazione sparsa ovunque
- Difficoltà ad aggiungere nuovi tipi di documenti
- Violazione del principio "aperto per estensione, chiuso per modifica"

Il Factory Method risolve questo: una classe base sa come creare documenti in generale, e le sottoclassi decidono quale tipo specifico creare.

## Come funziona

Il meccanismo è semplice:
1. **Creator astratto**: Definisce il metodo factory ma non implementa la creazione
2. **ConcreteCreator**: Implementa il factory method per creare oggetti specifici
3. **Product**: Interfaccia per gli oggetti che vengono creati
4. **ConcreteProduct**: Implementazione concreta dell'oggetto

Il client usa solo il Creator, senza sapere quale ConcreteProduct viene effettivamente creato.

## Schema visivo

```
Flusso di creazione:
Client → Creator → createProduct()
                ↓
           ConcreteCreator → new ConcreteProduct()
                ↓
           Restituisce Product

Gerarchia delle classi:
AbstractCreator
    ↓
ConcreteCreator1 → createProduct() → ConcreteProduct1
ConcreteCreator2 → createProduct() → ConcreteProduct2
ConcreteCreator3 → createProduct() → ConcreteProduct3
```

*Il diagramma mostra come ogni ConcreteCreator crea il suo tipo specifico di prodotto, ma il client lavora solo con l'interfaccia astratta.*

## Quando usarlo

Usa il Factory Method quando:
- Devi creare oggetti basandoti su configurazione o input dell'utente
- Gestisci diversi formati di file (PDF, DOC, TXT)
- Crei connessioni a database diversi
- Hai un sistema di notifiche (email, SMS, push)
- Gestisci diversi tipi di utenti o ruoli
- Vuoi estendere facilmente il sistema con nuovi tipi di prodotti

**NON usarlo quando:**
- La creazione è semplice e non cambierà mai
- Hai solo un tipo di prodotto
- L'overhead del pattern non è giustificato
- La logica di creazione è troppo complessa per una singola factory

## Pro e contro

**I vantaggi:**
- Elimina l'accoppiamento tra client e classi concrete
- Facilita l'aggiunta di nuovi tipi di prodotti
- Centralizza la logica di creazione
- Rispetta il principio Open/Closed
- Migliora la testabilità

**Gli svantaggi:**
- Aumenta la complessità del codice
- Richiede più classi e interfacce
- Può essere eccessivo per creazioni semplici
- Può creare gerarchie di classi complesse

## Esempi di codice

### Pseudocodice
```
// Interfaccia per i prodotti
interface Product {
    method create()
}

// Prodotti concreti
class ConcreteProduct1 implements Product {
    method create() {
        return "Product 1 created"
    }
}

class ConcreteProduct2 implements Product {
    method create() {
        return "Product 2 created"
    }
}

// Creator astratto
abstract class Creator {
    abstract method createProduct() returns Product
    
    method generateProduct() returns string {
        product = this.createProduct()
        return product.create()
    }
}

// Creator concreti
class ConcreteCreator1 extends Creator {
    method createProduct() returns Product {
        return new ConcreteProduct1()
    }
}

class ConcreteCreator2 extends Creator {
    method createProduct() returns Product {
        return new ConcreteProduct2()
    }
}

// Utilizzo
creator1 = new ConcreteCreator1()
result1 = creator1.generateProduct() // "Product 1 created"

creator2 = new ConcreteCreator2()
result2 = creator2.generateProduct() // "Product 2 created"
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Gestione Utenti con Factory](./esempio-completo/)** - Sistema di gestione utenti con factory per diversi tipi di utenti e ruoli

L'esempio include:
- Factory per creare utenti (Admin, User, Guest)
- Gestione ruoli e permessi
- Integrazione con Eloquent ORM
- Service Provider per registrare le factory
- Controller con dependency injection
- Test unitari per i factory methods
- API RESTful per gestire gli utenti

## Correlati

### Pattern

- **[Abstract Factory](./03-abstract-factory/abstract-factory-pattern.md)** - Se hai bisogno di creare famiglie di oggetti correlati
- **[Builder](./04-builder/builder-pattern.md)** - Per costruire oggetti complessi passo dopo passo
- **[Prototype](./05-prototype/prototype-pattern.md)** - Per clonare oggetti esistenti invece di crearli da zero
- **[Singleton](./01-singleton/singleton-pattern.md)** - Per garantire una sola istanza di factory

### Principi e Metodologie

- **[DRY Pattern](../12-pattern-metodologie-concettuali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../12-pattern-metodologie-concettuali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../12-pattern-metodologie-concettuali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../12-pattern-metodologie-concettuali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Laravel Model Factories**: Laravel usa il Factory Method per creare istanze di modelli per i test e il seeding
- **Symfony Form Factory**: Symfony usa factory per creare diversi tipi di form fields (text, email, password)
- **PHPUnit Test Doubles**: PHPUnit usa factory per creare mock, stub e fake objects
- **Document Generators**: Librerie come TCPDF e FPDF usano factory per creare diversi tipi di documenti
- **Payment Gateways**: Sistemi di pagamento usano factory per creare diversi provider (Stripe, PayPal, Square)

## Anti-pattern

**Cosa NON fare:**
- **Factory con troppi parametri**: Evita factory che richiedono molti parametri, rendono il codice difficile da usare
- **Factory che conosce tutto**: Non far conoscere alla factory dettagli specifici delle classi concrete
- **Factory senza interfacce**: Sempre definire interfacce astratte per i prodotti e le factory
- **Factory per oggetti semplici**: Non usare factory per oggetti che si creano facilmente con `new`
- **Factory troppo complesse**: Evita factory che fanno troppo lavoro, violano il principio di responsabilità singola

## Troubleshooting

### Problemi comuni
- **"Cannot instantiate abstract class"**: Assicurati di implementare tutti i metodi astratti del Creator
- **"Wrong product type returned"**: Verifica che il ConcreteCreator restituisca il tipo corretto di Product
- **"Factory method not found"**: Controlla che il metodo factory sia definito correttamente nella classe astratta
- **"Product interface not implemented"**: Assicurati che i ConcreteProduct implementino l'interfaccia Product

### Debug e monitoring
- **Log delle creazioni**: Aggiungi logging per tracciare quale tipo di prodotto viene creato
- **Controllo tipi**: Verifica che i prodotti creati siano del tipo corretto
- **Performance factory**: Monitora il tempo di creazione per identificare factory lente
- **Memory usage**: Traccia l'uso di memoria per verificare che non ci siano leak

### Metriche utili
- **Numero di prodotti creati per tipo**: Per capire l'utilizzo dei diversi factory
- **Tempo di creazione**: Per identificare factory che potrebbero essere ottimizzate
- **Errori di creazione**: Per identificare problemi con i factory method
- **Utilizzo interfacce**: Per verificare che i client usino le interfacce astratte

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Leggero overhead per le classi factory e interfacce (tipicamente 5-15KB)
- **CPU**: La creazione tramite factory è leggermente più lenta del `new` diretto (1-5ms overhead)
- **I/O**: Se i prodotti creano risorse esterne, l'I/O è gestito dai prodotti stessi

### Scalabilità
- **Carico basso**: Perfetto, overhead trascurabile
- **Carico medio**: Funziona bene, l'overhead è compensato dalla flessibilità
- **Carico alto**: Può diventare un collo di bottiglia se i factory sono complessi

### Colli di bottiglia
- **Factory complesse**: Se la logica di creazione è troppo elaborata
- **Troppi tipi di prodotti**: Gestire centinaia di ConcreteCreator può diventare complesso
- **Memory allocation**: Creare molti oggetti diversi può causare frammentazione
- **Reflection**: Se usi reflection per la creazione dinamica, può essere lento

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Factory Method](https://refactoring.guru/design-patterns/factory-method) - Spiegazione visuale con esempi

### Laravel specifico
- [Laravel Model Factories](https://laravel.com/docs/eloquent-factories) - Come Laravel usa le factory
- [Laravel Service Container](https://laravel.com/docs/container) - Per gestire le dipendenze

### Esempi e tutorial
- [Factory Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
- [Factory Pattern vs Abstract Factory](https://www.tutorialspoint.com/design_pattern/factory_pattern.htm) - Confronto tra pattern factory

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
