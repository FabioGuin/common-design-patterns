# Visitor Pattern

## Cosa fa

Il Visitor Pattern rappresenta un'operazione da eseguire sugli elementi di una struttura di oggetti. Visitor ti permette di definire una nuova operazione senza cambiare le classi degli elementi su cui opera. È come avere un "ispettore" che visita ogni elemento di una struttura e esegue operazioni specifiche.

## Perché ti serve

Immagina di avere una struttura di oggetti (albero di file, AST di codice, struttura XML) e vuoi eseguire diverse operazioni (calcolare dimensioni, validare, esportare, formattare). Senza il Visitor Pattern, dovresti aggiungere metodi a ogni classe. Con il Visitor:

- **Aggiungi** nuove operazioni senza modificare le classi esistenti
- **Raggruppa** operazioni correlate in un visitor
- **Mantieni** le classi degli elementi semplici
- **Facilita** l'aggiunta di nuove operazioni

## Come funziona

Il pattern ha quattro componenti principali:

1. **Visitor (Interfaccia)**: Definisce l'interfaccia per visitare elementi
2. **ConcreteVisitor**: Implementazioni specifiche delle operazioni
3. **Element (Interfaccia)**: Definisce l'interfaccia per accettare visitor
4. **ConcreteElement**: Implementazioni specifiche che accettano visitor

## Schema visivo

```
Visitor → visit(ElementA) → ElementA
    ↓    visit(ElementB) → ElementB
    ↓    visit(ElementC) → ElementC
```

## Quando usarlo

- **Strutture complesse** con molte operazioni
- **AST (Abstract Syntax Trees)**
- **File systems** con operazioni diverse
- **XML/JSON processing**
- **Compiler design**
- **Code analysis** tools

## Pro e contro

### Pro
- **Easy to add operations**: Facile aggiungere nuove operazioni
- **Single responsibility**: Ogni visitor ha una responsabilità
- **Open/Closed principle**: Aperto per estensione, chiuso per modifica
- **Grouped operations**: Raggruppa operazioni correlate

### Contro
- **Hard to add elements**: Difficile aggiungere nuovi tipi di elementi
- **Tight coupling**: Visitor e elementi sono accoppiati
- **Complexity**: Può diventare complesso con molti visitor
- **Performance**: Overhead per le chiamate ai metodi

## Esempi di codice

### Interfaccia Visitor
```pseudocodice
interface VisitorInterface {
    function visitFile(file: File): mixed
    function visitDirectory(directory: Directory): mixed
}
```

### Interfaccia Element
```pseudocodice
interface ElementInterface {
    function accept(visitor: VisitorInterface): mixed
}
```

### Elementi concreti
```pseudocodice
class File implements ElementInterface {
    private name: string
    private size: number
    
    constructor(name: string, size: number) {
        this.name = name
        this.size = size
    }
    
    function accept(visitor: VisitorInterface): mixed {
        return visitor.visitFile(this)
    }
    
    function getName(): string {
        return this.name
    }
    
    function getSize(): number {
        return this.size
    }
}

class Directory implements ElementInterface {
    private name: string
    private children: array = []
    
    constructor(name: string) {
        this.name = name
    }
    
    function addChild(element: ElementInterface): void {
        this.children[] = element
    }
    
    function accept(visitor: VisitorInterface): mixed {
        return visitor.visitDirectory(this)
    }
    
    function getName(): string {
        return this.name
    }
    
    function getChildren(): array {
        return this.children
    }
}
```

### Visitor concreti
```pseudocodice
class SizeCalculatorVisitor implements VisitorInterface {
    private totalSize: number = 0
    
    function visitFile(file: File): number {
        this.totalSize += file.getSize()
        return file.getSize()
    }
    
    function visitDirectory(directory: Directory): number {
        directorySize = 0
        foreach (directory.getChildren() as child) {
            directorySize += child.accept(this)
        }
        return directorySize
    }
    
    function getTotalSize(): number {
        return this.totalSize
    }
}

class FileListerVisitor implements VisitorInterface {
    private files: array = []
    
    function visitFile(file: File): array {
        this.files[] = file.getName()
        return [file.getName()]
    }
    
    function visitDirectory(directory: Directory): array {
        files = []
        foreach (directory.getChildren() as child) {
            files = array_merge(files, child.accept(this))
        }
        return files
    }
    
    function getFiles(): array {
        return this.files
    }
}
```

### Uso
```pseudocodice
// Crea struttura
root = new Directory('root')
documents = new Directory('documents')
images = new Directory('images')

root.addChild(documents)
root.addChild(images)

documents.addChild(new File('report.pdf', 1024))
documents.addChild(new File('notes.txt', 512))
images.addChild(new File('photo.jpg', 2048))

// Calcola dimensioni
sizeVisitor = new SizeCalculatorVisitor()
totalSize = root.accept(sizeVisitor)
echo "Total size: " + totalSize + " bytes"

// Lista file
listVisitor = new FileListerVisitor()
files = root.accept(listVisitor)
echo "Files: " + implode(', ', files)
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di analisi file
- AST processor per codice
- Sistema di validazione
- Export di dati in diversi formati

## Correlati

- **Composite Pattern**: Per strutture gerarchiche
- **Strategy Pattern**: Per diversi tipi di visitor
- **Command Pattern**: Per incapsulare operazioni

## Esempi di uso reale

- **Laravel Eloquent**: Per operazioni sui modelli
- **Laravel Collections**: Per operazioni sui dati
- **Code analysis**: Per analizzare codice
- **File processing**: Per operazioni sui file
- **XML/JSON processing**: Per parsing e trasformazione
- **Compiler design**: Per analisi e generazione codice

## Anti-pattern

❌ **Visitor che fa troppo**: Un visitor che gestisce troppe responsabilità
```pseudocodice
// SBAGLIATO
class GodVisitor implements VisitorInterface {
    function visitFile(file: File): mixed {
        this.calculateSize()
        this.validateFile()
        this.exportFile()
        this.logActivity()
        this.updateDatabase()
        // Troppo complesso!
    }
}
```

✅ **Visitor focalizzato**: Un visitor per una responsabilità specifica
```pseudocodice
// GIUSTO
class SizeCalculatorVisitor implements VisitorInterface {
    function visitFile(file: File): number {
        return file.getSize()
    }
}
```

## Troubleshooting

**Problema**: Visitor non funziona
**Soluzione**: Verifica che gli elementi implementino correttamente il metodo `accept`

**Problema**: Difficile aggiungere nuovi tipi di elementi
**Soluzione**: Considera di usare un approccio diverso se devi aggiungere spesso nuovi tipi

**Problema**: Performance lenta
**Soluzione**: Considera di usare caching o ottimizzazioni specifiche

## Performance e considerazioni

- **Method calls**: Considera il costo delle chiamate ai metodi
- **Memory usage**: I visitor possono accumulare stato
- **Caching**: Per risultati costosi da calcolare
- **Batch processing**: Per operazioni su grandi strutture

## Risorse utili

- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Laravel Collections](https://laravel.com/docs/collections)
- [Visitor Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/visitor)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
