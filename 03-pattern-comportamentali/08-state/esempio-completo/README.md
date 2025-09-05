# Esempio Completo: State Pattern

Questo esempio dimostra l'implementazione del **State Pattern** in Laravel per gestire stati e workflow complessi.

## Funzionalità implementate

- **Sistema di workflow** per documenti
- **Stati di autenticazione** utente
- **Workflow di approvazione**
- **Sistema di notifiche** basato su stati

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── WorkflowController.php
│   └── Services/
│       ├── States/
│       │   ├── StateInterface.php
│       │   ├── PendingState.php
│       │   ├── ApprovedState.php
│       │   └── RejectedState.php
│       └── Contexts/
│           ├── DocumentContext.php
│           └── UserContext.php
├── resources/views/
│   └── workflow/
│       └── index.blade.php
├── routes/
│   └── web.php
└── composer.json
```

## Esempi di utilizzo

### Workflow di Approvazione
```php
$document = new DocumentContext();
$document->setState(new PendingState());
$document->handle(); // "Document is pending"

$document->setState(new ApprovedState());
$document->handle(); // "Document is approved"
```

## Pattern implementati

- **State Pattern**: Gestione stati e comportamenti
- **Context Pattern**: Per oggetti che cambiano stato
- **Transition Pattern**: Per transizioni di stato
