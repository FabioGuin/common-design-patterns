# Composite Pattern

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

Il Composite Pattern ti permette di comporre oggetti in strutture ad albero per rappresentare gerarchie parte-tutto. Tratta oggetti singoli e composizioni di oggetti in modo uniforme, come se fossero la stessa cosa.

## Perché ti serve

Immagina di dover gestire un menu di un ristorante con categorie e sottocategorie. Senza Composite, dovresti trattare diversamente le voci singole e le categorie. Con Composite, puoi trattare tutto come "elementi del menu" e chiedere il prezzo totale senza sapere se stai lavorando con una voce singola o una categoria intera.

**Problemi che risolve:**
- Gestire strutture gerarchiche complesse in modo uniforme
- Aggiungere nuovi tipi di elementi senza modificare il codice esistente
- Eseguire operazioni su intere gerarchie con una sola chiamata
- Rappresentare alberi di oggetti con nodi foglia e nodi compositi

## Come funziona

1. **Crea un'interfaccia comune** che definisce le operazioni per tutti gli elementi
2. **Implementa la classe foglia** per gli elementi singoli
3. **Implementa la classe composita** che contiene una collezione di elementi
4. **La classe composita delega** le operazioni ai suoi figli
5. **Tratta foglie e compositi** allo stesso modo attraverso l'interfaccia comune

## Schema visivo

```
Component (MenuComponent)
├── Leaf (MenuItem)
└── Composite (MenuCategory)
    ├── Leaf (MenuItem)
    ├── Leaf (MenuItem)
    └── Composite (MenuSubCategory)
        ├── Leaf (MenuItem)
        └── Leaf (MenuItem)

Operazioni:
- getPrice() → Somma i prezzi di tutti i figli
- getName() → Restituisce il nome dell'elemento
- add() → Aggiunge un figlio (solo per compositi)
- remove() → Rimuove un figlio (solo per compositi)
```

**Flusso:**
```
Client → MenuComponent::getPrice()
      → MenuCategory::getPrice() 
      → MenuItem::getPrice() + MenuItem::getPrice() + ...
```

## Quando usarlo

Usa il Composite Pattern quando:
- Hai una struttura gerarchica che vuoi trattare uniformemente
- Vuoi eseguire operazioni su intere gerarchie
- Hai bisogno di aggiungere nuovi tipi di elementi facilmente
- Stai lavorando con alberi di oggetti
- Vuoi nascondere la differenza tra oggetti singoli e compositi

**NON usarlo quando:**
- La struttura non è gerarchica
- Hai solo oggetti singoli senza composizioni
- Le operazioni sono troppo diverse tra foglie e compositi
- La gerarchia è troppo semplice (2-3 livelli)

## Pro e contro

**I vantaggi:**
- Tratta oggetti singoli e compositi uniformemente
- Facilita l'aggiunta di nuovi tipi di elementi
- Permette operazioni su intere gerarchie
- Nasconde la complessità della struttura
- Segue il principio di apertura/chiusura

**Gli svantaggi:**
- Può rendere il design troppo generico
- Può essere difficile limitare i tipi di figli
- Può creare confusione se usato inappropriatamente
- Aggiunge complessità per strutture semplici

## Esempi di codice

### Pseudocodice
```
// Interfaccia comune
interface MenuComponent {
    getPrice()
    getName()
    add(component) // Solo per compositi
    remove(component) // Solo per compositi
}

// Foglia
class MenuItem implements MenuComponent {
    private name, price
    
    getPrice() {
        return this.price
    }
    
    getName() {
        return this.name
    }
    
    add(component) {
        throw new Exception("Cannot add to leaf")
    }
}

// Composito
class MenuCategory implements MenuComponent {
    private name
    private children = []
    
    getPrice() {
        total = 0
        for child in this.children {
            total += child.getPrice()
        }
        return total
    }
    
    add(component) {
        this.children.add(component)
    }
}

// Utilizzo
menu = new MenuCategory("Menu")
menu.add(new MenuItem("Pizza", 10))
menu.add(new MenuItem("Pasta", 8))

desserts = new MenuCategory("Desserts")
desserts.add(new MenuItem("Tiramisu", 5))
menu.add(desserts)

total = menu.getPrice() // 23
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Menu con Composite](./esempio-completo/)** - Menu gerarchico con categorie e sottocategorie

L'esempio include:
- Interfaccia comune per elementi del menu
- Implementazioni per voci singole e categorie
- Controller Laravel per gestire il menu
- Vista per visualizzare la struttura gerarchica
- Operazioni per calcolare prezzi e contare elementi

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Decorator Pattern](./04-decorator/decorator-pattern.md)** - Aggiunge funzionalità dinamicamente
- **[Iterator Pattern](../03-pattern-comportamentali/04-iterator/iterator-pattern.md)** - Attraversa collezioni
- **[Visitor Pattern](../03-pattern-comportamentali/11-visitor/visitor-pattern.md)** - Esegue operazioni su strutture complesse
- **[Command Pattern](../03-pattern-comportamentali/02-command/command-pattern.md)** - Incapsula richieste

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Sistemi di menu** con categorie e sottocategorie
- **File system** con cartelle e file
- **Organigrammi aziendali** con dipartimenti e dipendenti
- **Strutture di documenti** con sezioni e paragrafi
- **Sistemi di permessi** con ruoli e utenti

## Anti-pattern

**Cosa NON fare:**
- Non usare Composite per strutture non gerarchiche
- Non creare interfacce troppo generiche che confondono
- Non ignorare le differenze tra foglie e compositi quando necessario
- Non usare Composite per strutture troppo semplici
- Non creare gerarchie troppo profonde che rendono il codice difficile da seguire

## Troubleshooting

### Problemi comuni
- **Operazioni non funzionano**: Verifica che l'interfaccia comune sia implementata correttamente
- **Gerarchia troppo complessa**: Considera se puoi semplificare la struttura
- **Performance degradate**: Ottimizza le operazioni ricorsive
- **Difficoltà di debug**: Aggiungi logging per tracciare le chiamate ricorsive

### Debug e monitoring
- Usa logging per tracciare le chiamate ricorsive
- Monitora le performance per identificare colli di bottiglia
- Testa sia le foglie che i compositi separatamente
- Verifica che le operazioni ricorsive terminino correttamente

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Dipende dalla profondità e ampiezza della gerarchia
- **CPU**: Overhead per le operazioni ricorsive
- **I/O**: Dipende dalle operazioni eseguite sui nodi

### Scalabilità
- **Carico basso**: Impatto trascurabile
- **Carico medio**: Gestibile con gerarchie moderate
- **Carico alto**: Considera caching per operazioni costose

### Colli di bottiglia
- **Operazioni ricorsive**: Se la gerarchia è troppo profonda
- **Collezioni grandi**: Se i compositi hanno troppi figli
- **Operazioni costose**: Se ogni nodo fa operazioni pesanti

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns/composite) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Collections](https://laravel.com/docs/collections) - Gestione collezioni
- [Laravel Eloquent](https://laravel.com/docs/eloquent) - ORM con relazioni

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Laravel Composite Examples](https://github.com/laravel/patterns) - Esempi specifici per Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
