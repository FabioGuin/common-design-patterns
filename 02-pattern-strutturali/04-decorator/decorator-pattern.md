# Decorator Pattern

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

Il Decorator Pattern ti permette di aggiungere nuove funzionalità a un oggetto esistente senza modificare la sua struttura. È come aggiungere decorazioni a una torta: puoi aggiungere panna, frutta, cioccolato senza cambiare la torta base.

## Perché ti serve

Immagina di avere una classe `Coffee` base e vuoi aggiungere funzionalità come latte, zucchero, cannella. Senza Decorator, dovresti creare classi per ogni combinazione (CoffeeWithMilk, CoffeeWithSugar, CoffeeWithMilkAndSugar, ecc.). Con Decorator, crei decoratori che si avvolgono l'uno nell'altro.

**Problemi che risolve:**
- Aggiunge funzionalità dinamicamente senza modificare il codice esistente
- Evita l'esplosione di classi per ogni combinazione di funzionalità
- Permette di combinare funzionalità in modo flessibile
- Segue il principio di apertura/chiusura (aperto per estensione, chiuso per modifica)

## Come funziona

1. **Crea un'interfaccia comune** per l'oggetto base e i decoratori
2. **Implementa la classe base** che implementa l'interfaccia
3. **Crea una classe decoratore astratta** che implementa l'interfaccia e contiene un riferimento all'oggetto decorato
4. **Implementa decoratori concreti** che aggiungono funzionalità specifiche
5. **I decoratori si avvolgono** l'uno nell'altro per combinare funzionalità

## Schema visivo

```
Component (Coffee)
    ↓
ConcreteComponent (SimpleCoffee)
    ↓
Decorator (CoffeeDecorator)
    ↓
ConcreteDecorator (MilkDecorator)
    ↓
ConcreteDecorator (SugarDecorator)

Esempio:
coffee = new SimpleCoffee()
coffee = new MilkDecorator(coffee)
coffee = new SugarDecorator(coffee)

Risultato: Coffee con latte e zucchero
```

**Flusso:**
```
Client → Coffee::getCost()
      → SugarDecorator::getCost()
      → MilkDecorator::getCost()
      → SimpleCoffee::getCost()
```

## Quando usarlo

Usa il Decorator Pattern quando:
- Vuoi aggiungere funzionalità a oggetti esistenti senza modificarli
- Hai bisogno di combinare funzionalità in modo flessibile
- Vuoi evitare l'esplosione di classi per ogni combinazione
- Hai bisogno di aggiungere/rimuovere funzionalità a runtime
- Stai costruendo un sistema di plugin o estensioni

**NON usarlo quando:**
- Le funzionalità sono troppo diverse tra loro
- Hai solo una o due funzionalità da aggiungere
- Le funzionalità sono strettamente accoppiate all'oggetto base
- Stai creando un sistema semplice senza variazioni

## Pro e contro

**I vantaggi:**
- Aggiunge funzionalità dinamicamente senza modificare il codice esistente
- Permette di combinare funzionalità in modo flessibile
- Evita l'esplosione di classi per ogni combinazione
- Segue il principio di apertura/chiusura
- Permette di aggiungere/rimuovere funzionalità a runtime

**Gli svantaggi:**
- Aggiunge complessità al design
- Può creare molti oggetti piccoli
- Può essere difficile debuggare con molti decoratori
- Può creare overhead di performance per le chiamate multiple

## Esempi di codice

### Pseudocodice
```
// Interfaccia comune
interface Coffee {
    getCost()
    getDescription()
}

// Classe base
class SimpleCoffee implements Coffee {
    getCost() {
        return 2.0
    }
    
    getDescription() {
        return "Simple coffee"
    }
}

// Decoratore astratto
abstract class CoffeeDecorator implements Coffee {
    protected coffee: Coffee
    
    constructor(coffee: Coffee) {
        this.coffee = coffee
    }
    
    getCost() {
        return this.coffee.getCost()
    }
    
    getDescription() {
        return this.coffee.getDescription()
    }
}

// Decoratori concreti
class MilkDecorator extends CoffeeDecorator {
    getCost() {
        return this.coffee.getCost() + 0.5
    }
    
    getDescription() {
        return this.coffee.getDescription() + ", milk"
    }
}

// Utilizzo
coffee = new SimpleCoffee()
coffee = new MilkDecorator(coffee)
coffee = new SugarDecorator(coffee)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Notifiche con Decorator](./esempio-completo/)** - Notifiche con funzionalità aggiuntive

L'esempio include:
- Interfaccia base per le notifiche
- Decoratori per logging, caching, validazione
- Controller Laravel per gestire le notifiche
- Vista per testare le diverse combinazioni
- Sistema di configurazione per i decoratori

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Adapter Pattern](./01-adapter/adapter-pattern.md)** - Adatta interfacce incompatibili
- **[Bridge Pattern](./02-bridge/bridge-pattern.md)** - Separa l'astrazione dall'implementazione
- **[Composite Pattern](./03-composite/composite-pattern.md)** - Compone oggetti in strutture ad albero
- **[Proxy Pattern](./07-proxy/proxy-pattern.md)** - Fornisce un placeholder per un oggetto

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Sistemi di notifica** con funzionalità aggiuntive (logging, caching, validazione)
- **Sistemi di logging** con diversi formattatori e destinazioni
- **Sistemi di caching** con diversi livelli e strategie
- **Sistemi di autenticazione** con diversi provider e metodi
- **Sistemi di validazione** con diverse regole e controlli

## Anti-pattern

**Cosa NON fare:**
- Non usare Decorator per funzionalità troppo diverse tra loro
- Non creare decoratori che modificano lo stato interno dell'oggetto base
- Non usare Decorator per risolvere problemi di design architetturale
- Non creare troppi livelli di decoratori che rendono il codice difficile da seguire
- Non ignorare le performance quando hai molti decoratori

## Troubleshooting

### Problemi comuni
- **Decoratore non funziona**: Verifica che l'interfaccia sia implementata correttamente
- **Performance degradate**: Considera se l'overhead dei decoratori è giustificato
- **Difficoltà di debug**: Aggiungi logging per tracciare le chiamate attraverso i decoratori
- **Stato inconsistente**: Assicurati che i decoratori non modifichino lo stato interno

### Debug e monitoring
- Usa logging per tracciare le chiamate attraverso i decoratori
- Monitora le performance per identificare colli di bottiglia
- Testa sia i decoratori singoli che le combinazioni
- Verifica che le eccezioni vengano propagate correttamente

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Aggiunge un oggetto per ogni decoratore
- **CPU**: Overhead per le chiamate multiple attraverso i decoratori
- **I/O**: Dipende dalle funzionalità aggiunte dai decoratori

### Scalabilità
- **Carico basso**: Impatto trascurabile
- **Carico medio**: Gestibile con pochi decoratori
- **Carico alto**: Considera caching se i decoratori sono costosi

### Colli di bottiglia
- **Chiamate multiple**: Se hai troppi decoratori annidati
- **Decoratori costosi**: Se le funzionalità aggiunte sono pesanti
- **Serializzazione**: Se i decoratori contengono dati non serializzabili

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns/decorator) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Middleware](https://laravel.com/docs/middleware) - Pattern simile al Decorator
- [Laravel Service Container](https://laravel.com/docs/container) - Gestione dipendenze

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Laravel Decorator Examples](https://github.com/laravel/patterns) - Esempi specifici per Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
