# Memento Pattern

## Cosa fa

Il Memento Pattern cattura e esternalizza lo stato interno di un oggetto in modo che l'oggetto possa essere ripristinato a questo stato in seguito, senza violare l'incapsulamento. È come creare "snapshot" dello stato di un oggetto per poterlo ripristinare dopo.

## Perché ti serve

Immagina di avere un editor di testo con funzionalità di undo/redo. Ogni volta che l'utente fa un'azione, vuoi poterla annullare. Con il Memento Pattern puoi:

- **Salvare** lo stato di un oggetto in un momento specifico
- **Ripristinare** l'oggetto a uno stato precedente
- **Implementare** undo/redo facilmente
- **Mantenere** l'incapsulamento dell'oggetto

## Come funziona

Il pattern ha tre componenti principali:

1. **Originator**: L'oggetto di cui vuoi salvare lo stato
2. **Memento**: Contiene lo stato salvato dell'Originator
3. **Caretaker**: Gestisce i Memento e decide quando salvare/ripristinare

## Schema visivo

```
Originator → Memento ← Caretaker
     ↓         ↑
  save()   restore()
```

## Quando usarlo

- **Undo/Redo** operations
- **Checkpoint/Rollback** systems
- **Game save states**
- **Database transactions**
- **Configuration snapshots**
- **Backup systems**

## Pro e contro

### Pro
- **Encapsulation**: Non viola l'incapsulamento dell'oggetto
- **State management**: Facile gestire stati multipli
- **Undo/Redo**: Implementazione naturale
- **Flexibility**: Puoi salvare solo le parti necessarie

### Contro
- **Memory usage**: Può usare molta memoria per stati complessi
- **Performance**: Salvare/ripristinare può essere costoso
- **Complexity**: Può diventare complesso con oggetti grandi
- **Versioning**: Gestire versioni multiple può essere difficile

## Esempi di codice

### Pseudocodice
```
// Memento
class TextMemento {
    private content: string
    private cursorPosition: number
    private timestamp: DateTime
    
    constructor(content: string, cursorPosition: number) {
        this.content = content
        this.cursorPosition = cursorPosition
        this.timestamp = new DateTime()
    }
    
    getContent() returns string {
        return this.content
    }
    
    getCursorPosition() returns number {
        return this.cursorPosition
    }
    
    getTimestamp() returns DateTime {
        return this.timestamp
    }
}

// Originator
class TextEditor {
    private content = ''
    private cursorPosition = 0
    
    setContent(content: string) {
        this.content = content
        this.cursorPosition = content.length
    }
    
    insertText(text: string, position: number) {
        this.content = this.content.substring(0, position) + text + this.content.substring(position)
        this.cursorPosition = position + text.length
    }
    
    deleteText(start: number, length: number) {
        this.content = this.content.substring(0, start) + this.content.substring(start + length)
        this.cursorPosition = start
    }
    
    createMemento() returns TextMemento {
        return new TextMemento(this.content, this.cursorPosition)
    }
    
    restoreFromMemento(memento: TextMemento) {
        this.content = memento.getContent()
        this.cursorPosition = memento.getCursorPosition()
    }
    
    getContent() returns string {
        return this.content
    }
    
    getCursorPosition() returns number {
        return this.cursorPosition
    }
}

// Caretaker
class TextEditorHistory {
    private history = []
    private currentIndex = -1
    
    saveState(editor: TextEditor) {
        // Rimuovi stati futuri se siamo nel mezzo della history
        this.history = this.history.slice(0, this.currentIndex + 1)
        
        // Aggiungi il nuovo stato
        this.history.add(editor.createMemento())
        this.currentIndex++
    }
    
    undo(editor: TextEditor) returns boolean {
        if (this.currentIndex > 0) {
            this.currentIndex--
            editor.restoreFromMemento(this.history[this.currentIndex])
            return true
        }
        return false
    }
    
    redo(editor: TextEditor) returns boolean {
        if (this.currentIndex < this.history.length - 1) {
            this.currentIndex++
            editor.restoreFromMemento(this.history[this.currentIndex])
            return true
        }
        return false
    }
    
    canUndo() returns boolean {
        return this.currentIndex > 0
    }
    
    canRedo() returns boolean {
        return this.currentIndex < this.history.length - 1
    }
}

// Utilizzo
editor = new TextEditor()
history = new TextEditorHistory()

// Salva stato iniziale
history.saveState(editor)

// Modifica il testo
editor.insertText("Hello", 0)
history.saveState(editor)

editor.insertText(" World", 5)
history.saveState(editor)

// Undo
history.undo(editor) // Rimuove " World"

// Redo
history.redo(editor) // Aggiunge di nuovo " World"
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di undo/redo per documenti
- Checkpoint per database
- Backup automatico di configurazioni
- Sistema di versioning per file

## Correlati

- **Command Pattern**: Spesso usato insieme per undo/redo
- **State Pattern**: Per gestire stati complessi
- **Prototype Pattern**: Per clonare oggetti

## Esempi di uso reale

- **Laravel Eloquent**: Per rollback di transazioni
- **Laravel Backup**: Per backup di database
- **Text editors**: Undo/redo
- **Game engines**: Save states
- **Configuration management**
- **Version control systems**

## Anti-pattern

❌ **Memento troppo grande**: Un memento che salva troppi dati
```
// SBAGLIATO
class GodMemento {
    private entireDatabase: array
    private allUserSessions: array
    private allCacheData: array
    // Troppo pesante!
}
```

✅ **Memento focalizzato**: Un memento che salva solo i dati necessari
```
// GIUSTO
class TextMemento {
    private content: string
    private cursorPosition: number
    // Solo i dati essenziali
}
```

## Troubleshooting

**Problema**: Memory leak con molti memento
**Soluzione**: Implementa un limite alla history o usa memento più leggeri

**Problema**: Performance lenta nel salvare/ripristinare
**Soluzione**: Salva solo i dati che sono cambiati (delta)

**Problema**: Memento non funziona
**Soluzione**: Verifica che l'originator implementi correttamente i metodi

## Performance e considerazioni

- **Memory usage**: I memento occupano memoria
- **Serialization**: Considera la serializzazione per il persistence
- **Delta storage**: Salva solo le differenze per risparmiare memoria
- **Cleanup**: Implementa una strategia di pulizia per memento vecchi

## Risorse utili

- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Laravel Backup](https://laravel.com/docs/backup)
- [Memento Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/memento)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
