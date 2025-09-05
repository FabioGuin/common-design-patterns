# Esempio Completo: Command Pattern

Questo esempio dimostra l'implementazione del **Command Pattern** in Laravel per gestire operazioni di undo/redo, queue di comandi e macro commands.

## Funzionalità implementate

- **Sistema di undo/redo** per documenti
- **Queue di comandi** per batch processing
- **Macro commands** per sequenze complesse
- **Logging e auditing** delle operazioni
- **Sistema di notifiche** per comandi

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── DocumentController.php
│   └── Services/
│       ├── Commands/
│       │   ├── CommandInterface.php
│       │   ├── WriteTextCommand.php
│       │   ├── DeleteTextCommand.php
│       │   ├── FormatTextCommand.php
│       │   └── MacroCommand.php
│       ├── Invokers/
│       │   ├── CommandInvoker.php
│       │   └── CommandQueue.php
│       └── Receivers/
│           ├── Document.php
│           └── NotificationService.php
├── resources/views/
│   └── documents/
│       └── index.blade.php
├── routes/
│   └── web.php
├── composer.json
└── .env.example
```

## Come testare

1. Installa le dipendenze:
```bash
composer install
```

2. Configura l'ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

3. Avvia il server:
```bash
php artisan serve
```

4. Visita `http://localhost:8000/documents` per vedere il sistema di comandi

## Esempi di utilizzo

### Sistema di Undo/Redo
```php
$invoker = new CommandInvoker();
$document = new Document();

$invoker->executeCommand(new WriteTextCommand($document, "Hello", 0));
$invoker->executeCommand(new WriteTextCommand($document, " World", 5));

$invoker->undo(); // Rimuove " World"
$invoker->redo(); // Aggiunge di nuovo " World"
```

### Queue di Comandi
```php
$queue = new CommandQueue();
$queue->addCommand(new WriteTextCommand($document, "Text 1", 0));
$queue->addCommand(new WriteTextCommand($document, "Text 2", 10));
$queue->addCommand(new FormatTextCommand($document, 0, 10, 'bold'));

$queue->executeAll(); // Esegue tutti i comandi in sequenza
```

### Macro Commands
```php
$macro = new MacroCommand();
$macro->addCommand(new WriteTextCommand($document, "Title", 0));
$macro->addCommand(new FormatTextCommand($document, 0, 5, 'bold'));
$macro->addCommand(new WriteTextCommand($document, "\nContent", 5));

$invoker->executeCommand($macro); // Esegue tutti i comandi come uno solo
```

## Pattern implementati

- **Command Pattern**: Incapsulamento delle operazioni
- **Macro Command Pattern**: Sequenze di comandi
- **Queue Pattern**: Esecuzione batch di comandi
- **Undo/Redo Pattern**: Operazioni reversibili
