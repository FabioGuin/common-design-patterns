# Flyweight Pattern

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

Il Flyweight Pattern ti permette di condividere oggetti per ridurre l'uso di memoria. È come avere un set di matite colorate: invece di comprare una matita per ogni disegno, ne hai una per ogni colore e la condividi tra tutti i disegni.

## Perché ti serve

Immagina di dover creare migliaia di caratteri per un editor di testo. Senza Flyweight, creeresti un oggetto per ogni carattere con tutte le sue proprietà (font, dimensione, colore). Con Flyweight, crei un oggetto per ogni tipo di carattere e condividi le proprietà comuni.

**Problemi che risolve:**
- Riduce l'uso di memoria quando hai molti oggetti simili
- Condivide le proprietà comuni tra oggetti
- Migliora le performance quando crei molti oggetti
- Evita la duplicazione di dati identici
- Ottimizza l'uso delle risorse del sistema

## Come funziona

1. **Identifica le proprietà intrinseche** (condivisibili) e estrinseche (specifiche per ogni oggetto)
2. **Crea una classe Flyweight** che contiene solo le proprietà intrinseche
3. **Crea una Factory** che gestisce la creazione e il riutilizzo dei Flyweight
4. **Crea una classe Context** che contiene le proprietà estrinseche e un riferimento al Flyweight
5. **La Factory riutilizza** i Flyweight esistenti invece di crearne di nuovi

## Schema visivo

```
FlyweightFactory
    ↓
Flyweight (Character)
    ↓
Context (CharacterContext)

Esempio:
Factory → getCharacter('A') → Character('A', font, size)
Factory → getCharacter('B') → Character('B', font, size)
Factory → getCharacter('A') → Character('A', font, size) // Riutilizza lo stesso oggetto

Context → CharacterContext('A', x=10, y=20, color=red)
Context → CharacterContext('B', x=30, y=20, color=blue)
```

**Flusso:**
```
Client → Factory::getFlyweight(key)
      → Flyweight::operation(extrinsicState)
      → Context::render()
```

## Quando usarlo

Usa il Flyweight Pattern quando:
- Hai molti oggetti simili che condividono proprietà comuni
- L'uso di memoria è un problema
- Le proprietà intrinseche sono molto più numerose di quelle estrinseche
- Vuoi ottimizzare le performance per oggetti pesanti
- Stai lavorando con sistemi che creano molti oggetti identici

**NON usarlo quando:**
- Hai pochi oggetti o oggetti molto diversi
- Le proprietà estrinseche sono più numerose di quelle intrinseche
- L'overhead del pattern supera i benefici
- Stai lavorando con oggetti semplici e leggeri

## Pro e contro

**I vantaggi:**
- Riduce significativamente l'uso di memoria
- Migliora le performance quando crei molti oggetti
- Condivide le proprietà comuni tra oggetti
- Ottimizza l'uso delle risorse del sistema
- Facilita la gestione di oggetti pesanti

**Gli svantaggi:**
- Aggiunge complessità al design
- Può rendere il codice più difficile da capire
- Richiede una separazione chiara tra proprietà intrinseche ed estrinseche
- Può creare problemi di thread safety se non gestito correttamente

## Esempi di codice

### Pseudocodice
```
// Flyweight
class Character {
    private char: string
    private font: string
    private size: number
    
    constructor(char: string, font: string, size: number) {
        this.char = char
        this.font = font
        this.size = size
    }
    
    render(x: number, y: number, color: string) {
        // Renderizza il carattere con le proprietà estrinseche
    }
}

// Factory
class CharacterFactory {
    private characters = new Map()
    
    getCharacter(char: string, font: string, size: number) {
        key = char + font + size
        if (!this.characters.has(key)) {
            this.characters.set(key, new Character(char, font, size))
        }
        return this.characters.get(key)
    }
}

// Context
class CharacterContext {
    private character: Character
    private x: number
    private y: number
    private color: string
    
    constructor(character: Character, x: number, y: number, color: string) {
        this.character = character
        this.x = x
        this.y = y
        this.color = color
    }
    
    render() {
        this.character.render(this.x, this.y, this.color)
    }
}

// Utilizzo
factory = new CharacterFactory()
charA = factory.getCharacter('A', 'Arial', 12)
charB = factory.getCharacter('B', 'Arial', 12)
charA2 = factory.getCharacter('A', 'Arial', 12) // Riutilizza lo stesso oggetto
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Template con Flyweight](./esempio-completo/)** - Template riutilizzabili per documenti

L'esempio include:
- Flyweight per template di documenti
- Factory per gestire il riutilizzo dei template
- Context per le proprietà specifiche di ogni documento
- Controller Laravel per gestire i documenti
- Vista per testare il sistema di template

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Singleton Pattern](../01-pattern-creazionali/01-singleton/singleton-pattern.md)** - Garantisce una sola istanza
- **[Factory Method Pattern](../01-pattern-creazionali/02-factory-method/factory-method-pattern.md)** - Crea oggetti senza specificare le classi
- **[Object Pool Pattern](../01-pattern-creazionali/06-object-pool/object-pool-pattern.md)** - Riutilizza oggetti costosi
- **[Proxy Pattern](./07-proxy/proxy-pattern.md)** - Fornisce un placeholder per un oggetto

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Editor di testo** con migliaia di caratteri
- **Sistemi di gaming** con texture e modelli 3D
- **Sistemi di template** per documenti e email
- **Sistemi di caching** per oggetti pesanti
- **Sistemi di rendering** con elementi grafici ripetuti

## Anti-pattern

**Cosa NON fare:**
- Non usare Flyweight per oggetti semplici e leggeri
- Non creare Flyweight con troppe proprietà estrinseche
- Non ignorare i problemi di thread safety
- Non usare Flyweight quando l'overhead supera i benefici
- Non creare Flyweight troppo complessi

## Troubleshooting

### Problemi comuni
- **Memory leak**: Verifica che i Flyweight vengano rilasciati correttamente
- **Thread safety**: Usa sincronizzazione se necessario
- **Performance degradate**: Considera se l'overhead del pattern è giustificato
- **Difficoltà di debug**: Aggiungi logging per tracciare il riutilizzo

### Debug e monitoring
- Usa logging per tracciare la creazione e il riutilizzo dei Flyweight
- Monitora l'uso di memoria per verificare i benefici
- Testa le performance con e senza il pattern
- Verifica che i Flyweight vengano rilasciati correttamente

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Riduce significativamente l'uso di memoria
- **CPU**: Overhead minimo per la gestione della factory
- **I/O**: Dipende dalle operazioni sui Flyweight

### Scalabilità
- **Carico basso**: Benefici limitati
- **Carico medio**: Benefici significativi
- **Carico alto**: Benefici molto significativi

### Colli di bottiglia
- **Factory lock**: Se la factory diventa un collo di bottiglia
- **Memory pressure**: Se i Flyweight non vengono rilasciati
- **Thread contention**: Se molti thread accedono alla factory

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns/flyweight) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Service Container](https://laravel.com/docs/container) - Gestione dipendenze
- [Laravel Caching](https://laravel.com/docs/cache) - Sistema di caching

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Laravel Flyweight Examples](https://github.com/laravel/patterns) - Esempi specifici per Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
