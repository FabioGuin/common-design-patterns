# Esempio Completo: Memento Pattern

Questo esempio dimostra l'implementazione del **Memento Pattern** in Laravel per implementare funzionalità di undo/redo e checkpoint.

## Funzionalità implementate

- **Sistema di undo/redo** per documenti
- **Checkpoint** per database
- **Backup automatico** di configurazioni
- **Sistema di versioning** per file

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── DocumentController.php
│   └── Services/
│       ├── Mementos/
│       │   ├── DocumentMemento.php
│       │   └── ConfigMemento.php
│       ├── Originators/
│       │   ├── Document.php
│       │   └── Configuration.php
│       └── Caretakers/
│           ├── DocumentHistory.php
│           └── ConfigHistory.php
├── resources/views/
│   └── documents/
│       └── index.blade.php
├── routes/
│   └── web.php
└── composer.json
```

## Esempi di utilizzo

### Undo/Redo per Documenti
```php
$document = new Document();
$history = new DocumentHistory();

$history->saveState($document);
$document->insertText("Hello");
$history->saveState($document);

$history->undo($document); // Rimuove "Hello"
$history->redo($document); // Aggiunge di nuovo "Hello"
```

## Pattern implementati

- **Memento Pattern**: Salvataggio e ripristino dello stato
- **Command Pattern**: Spesso usato insieme per undo/redo
- **State Pattern**: Per gestire stati complessi
