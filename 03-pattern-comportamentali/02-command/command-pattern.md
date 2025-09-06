# Command Pattern

## Cosa fa

Il Command Pattern incapsula una richiesta come un oggetto, permettendoti di parametrizzare client con diverse richieste, mettere in coda o loggare le richieste, e supportare operazioni di undo. È come trasformare ogni azione in un "comando" che puoi salvare, riprodurre, annullare o mettere in coda.

## Perché ti serve

Immagina di avere un editor di testo con funzionalità di undo/redo. Ogni volta che l'utente fa un'azione (scrive, cancella, formatta), vuoi poterla:
- **Salvare** per poterla annullare dopo
- **Riprodurre** per il redo
- **Mettere in coda** per eseguirla più tardi
- **Loggare** per audit o debug

Con il Command Pattern, ogni azione diventa un oggetto che puoi:
- **Eseguire** quando serve
- **Annullare** se necessario
- **Salvare** per riutilizzare
- **Mettere in coda** per batch processing

## Come funziona

Il pattern ha quattro componenti principali:

1. **Command (Interfaccia)**: Definisce l'interfaccia per eseguire operazioni
2. **ConcreteCommand**: Implementazioni specifiche che incapsulano richieste
3. **Invoker**: Chiama i comandi
4. **Receiver**: L'oggetto che riceve e esegue la richiesta

Il Command incapsula la richiesta e le informazioni necessarie per eseguirla.

## Schema visivo

```
Client → Invoker → Command → Receiver
         ↓
    Command History
    (per undo/redo)
```

## Quando usarlo

- **Undo/Redo** operations
- **Macro** commands (sequenze di comandi)
- **Queue** di comandi per batch processing
- **Logging** e **auditing** delle operazioni
- **Remote** procedure calls
- **Scheduling** di operazioni
- **Wizard** interfaces con step reversibili

## Pro e contro

### Pro
- **Undo/Redo**: Facile implementazione di operazioni reversibili
- **Macro**: Puoi combinare comandi in sequenze
- **Queue**: Puoi mettere in coda e schedulare comandi
- **Logging**: Ogni comando può essere loggato
- **Decoupling**: Il client non conosce il receiver

### Contro
- **Complessità**: Più classi per operazioni semplici
- **Memory**: I comandi occupano memoria per l'undo
- **Performance**: Overhead per operazioni semplici
- **Debugging**: Più difficile tracciare il flusso

## Esempi di codice

### Interfaccia base
```php
interface CommandInterface
{
    public function execute(): void;
    public function undo(): void;
}
```

### Comando concreto
```php
class WriteTextCommand implements CommandInterface
{
    private TextEditor $editor;
    private string $text;
    private int $position;
    
    public function __construct(TextEditor $editor, string $text, int $position)
    {
        $this->editor = $editor;
        $this->text = $text;
        $this->position = $position;
    }
    
    public function execute(): void
    {
        $this->editor->insertText($this->text, $this->position);
    }
    
    public function undo(): void
    {
        $this->editor->deleteText($this->position, strlen($this->text));
    }
}
```

### Invoker
```php
class CommandInvoker
{
    private array $history = [];
    private int $currentPosition = -1;
    
    public function executeCommand(CommandInterface $command): void
    {
        $command->execute();
        
        // Rimuovi comandi futuri se siamo nel mezzo della history
        $this->history = array_slice($this->history, 0, $this->currentPosition + 1);
        
        // Aggiungi il nuovo comando
        $this->history[] = $command;
        $this->currentPosition++;
    }
    
    public function undo(): void
    {
        if ($this->currentPosition >= 0) {
            $this->history[$this->currentPosition]->undo();
            $this->currentPosition--;
        }
    }
    
    public function redo(): void
    {
        if ($this->currentPosition < count($this->history) - 1) {
            $this->currentPosition++;
            $this->history[$this->currentPosition]->execute();
        }
    }
}
```

### Uso
```php
$editor = new TextEditor();
$invoker = new CommandInvoker();

// Esegui comandi
$invoker->executeCommand(new WriteTextCommand($editor, "Hello", 0));
$invoker->executeCommand(new WriteTextCommand($editor, " World", 5));

// Undo
$invoker->undo(); // Rimuove " World"

// Redo
$invoker->redo(); // Aggiunge di nuovo " World"
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di undo/redo per documenti
- Queue di comandi per batch processing
- Macro commands per sequenze complesse
- Logging e auditing delle operazioni

## Correlati

- **Memento Pattern**: Per salvare lo stato per l'undo
- **Observer Pattern**: Per notificare cambiamenti
- **Strategy Pattern**: Per diversi tipi di comandi

## Esempi di uso reale

- **Laravel Jobs**: I job sono comandi eseguibili in coda
- **Laravel Commands**: Artisan commands sono implementazioni del pattern
- **Undo/Redo**: Editor di testo, grafici, CAD
- **Macro**: Registrazione e riproduzione di azioni
- **Queue Systems**: Sistemi di coda per operazioni asincrone
- **Wizard**: Interfacce step-by-step reversibili

## Anti-pattern

**Comando che fa troppo**: Un comando che gestisce troppe responsabilità
```php
// SBAGLIATO
class GodCommand implements CommandInterface
{
    public function execute(): void
    {
        $this->validateData();
        $this->processPayment();
        $this->sendEmail();
        $this->updateDatabase();
        $this->logActivity();
        $this->notifyUsers();
        // Troppo complesso!
    }
}
```

**Comando focalizzato**: Un comando per ogni responsabilità specifica
```php
// GIUSTO
class ProcessPaymentCommand implements CommandInterface
{
    public function execute(): void
    {
        $this->paymentService->process($this->paymentData);
    }
}
```

## Troubleshooting

**Problema**: L'undo non funziona correttamente
**Soluzione**: Assicurati che ogni comando salvi lo stato necessario per l'undo

**Problema**: Memory leak con molti comandi
**Soluzione**: Implementa un limite alla history o usa comandi più leggeri

**Problema**: Comandi non eseguibili
**Soluzione**: Verifica che il receiver sia disponibile e valido

## Performance e considerazioni

- **Memory usage**: I comandi occupano memoria per l'undo
- **History limit**: Considera un limite alla history
- **Command size**: Mantieni i comandi leggeri
- **Batch operations**: Usa macro per operazioni multiple

## Risorse utili

- [Laravel Jobs](https://laravel.com/docs/queues)
- [Laravel Commands](https://laravel.com/docs/artisan)
- [Command Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/command)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
