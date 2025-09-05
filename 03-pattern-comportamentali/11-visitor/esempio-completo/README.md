# Esempio Completo: Visitor Pattern

Questo esempio dimostra l'implementazione del **Visitor Pattern** in Laravel per eseguire operazioni su strutture di oggetti.

## Funzionalità implementate

- **Sistema di analisi file**
- **AST processor** per codice
- **Sistema di validazione**
- **Export di dati** in diversi formati

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── FileController.php
│   └── Services/
│       ├── Visitors/
│           ├── VisitorInterface.php
│           ├── SizeCalculatorVisitor.php
│           ├── FileListerVisitor.php
│           └── ExportVisitor.php
│       └── Elements/
│           ├── ElementInterface.php
│           ├── File.php
│           └── Directory.php
├── resources/views/
│   └── files/
│       └── index.blade.php
├── routes/
│   └── web.php
└── composer.json
```

## Esempi di utilizzo

### Analisi File System
```php
$root = new Directory('root');
$file = new File('document.pdf', 1024);
$root->addChild($file);

$sizeVisitor = new SizeCalculatorVisitor();
$totalSize = $root->accept($sizeVisitor);
```

## Pattern implementati

- **Visitor Pattern**: Operazioni su strutture
- **Composite Pattern**: Per strutture gerarchiche
- **Double Dispatch Pattern**: Per dispatch dinamico
