# Esempio Completo: Mediator Pattern

Questo esempio dimostra l'implementazione del **Mediator Pattern** in Laravel per gestire la comunicazione tra componenti di un'interfaccia utente.

## Funzionalità implementate

- **Sistema di form** con validazione interdipendente
- **Chat system** con notifiche
- **Workflow di approvazione**
- **Sistema di eventi** centralizzato

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── FormController.php
│   └── Services/
│       ├── Mediators/
│       │   ├── MediatorInterface.php
│       │   ├── FormMediator.php
│       │   └── ChatMediator.php
│       └── Colleagues/
│           ├── ColleagueInterface.php
│           ├── TextField.php
│           ├── Button.php
│           └── Checkbox.php
├── resources/views/
│   └── forms/
│       └── index.blade.php
├── routes/
│   └── web.php
└── composer.json
```

## Esempi di utilizzo

### Form con Validazione Interdipendente
```php
$mediator = new FormMediator();
$textField = new TextField();
$button = new SubmitButton();

$mediator->addColleague($textField);
$mediator->addColleague($button);

$textField->setValue("Hello"); // Notifica automaticamente il button
```

## Pattern implementati

- **Mediator Pattern**: Comunicazione centralizzata
- **Observer Pattern**: Per notifiche unidirezionali
- **Command Pattern**: Per incapsulare richieste
