# Mediator Pattern

## Cosa fa

Il Mediator Pattern definisce un oggetto che incapsula come un insieme di oggetti interagiscono. Invece di permettere agli oggetti di riferirsi direttamente l'uno all'altro, il Mediator promuove il loose coupling permettendo di variare le loro interazioni indipendentemente.

## Perché ti serve

Immagina di avere un'interfaccia utente con molti componenti (bottoni, campi, liste) che devono comunicare tra loro. Senza un mediatore, ogni componente dovrebbe conoscere tutti gli altri, creando un groviglio di dipendenze. Con il Mediator:

- **Riduci** le dipendenze tra oggetti
- **Centralizzi** la logica di comunicazione
- **Faciliti** la manutenzione e i test
- **Permetti** di aggiungere/rimuovere componenti facilmente

## Come funziona

Il pattern ha quattro componenti principali:

1. **Mediator (Interfaccia)**: Definisce l'interfaccia per comunicare con i colleghi
2. **ConcreteMediator**: Implementa la cooperazione tra i colleghi
3. **Colleague (Interfaccia)**: Definisce l'interfaccia per comunicare con il mediatore
4. **ConcreteColleague**: Implementazioni specifiche che comunicano tramite il mediatore

## Schema visivo

```
Colleague1 ←→ Mediator ←→ Colleague2
     ↓           ↓           ↓
Colleague3 ←→ Mediator ←→ Colleague4
```

## Quando usarlo

- **Interfacce utente** complesse
- **Sistemi di notifica** tra componenti
- **Workflow** con molti step interdipendenti
- **Chat systems** o **messaging**
- **Event handling** centralizzato
- **Form validation** con campi interdipendenti

## Pro e contro

### Pro
- **Loose coupling**: Gli oggetti non conoscono gli altri
- **Centralized control**: Tutta la logica in un posto
- **Easy maintenance**: Facile modificare le interazioni
- **Testability**: Facile testare le interazioni

### Contro
- **Single point of failure**: Se il mediatore fallisce, tutto fallisce
- **Complexity**: Può diventare complesso con molti colleghi
- **Performance**: Può diventare un collo di bottiglia
- **God object**: Il mediatore può diventare troppo complesso

## Esempi di codice

### Interfaccia Mediator
```php
interface MediatorInterface
{
    public function notify(ColleagueInterface $sender, string $event, array $data = []): void;
}
```

### Interfaccia Colleague
```php
interface ColleagueInterface
{
    public function setMediator(MediatorInterface $mediator): void;
    public function notify(string $event, array $data = []): void;
}
```

### Mediatore concreto
```php
class FormMediator implements MediatorInterface
{
    private array $colleagues = [];
    
    public function addColleague(ColleagueInterface $colleague): void
    {
        $this->colleagues[] = $colleague;
        $colleague->setMediator($this);
    }
    
    public function notify(ColleagueInterface $sender, string $event, array $data = []): void
    {
        foreach ($this->colleagues as $colleague) {
            if ($colleague !== $sender) {
                $colleague->handleEvent($event, $data);
            }
        }
    }
}
```

### Colleague concreto
```php
class TextField implements ColleagueInterface
{
    private MediatorInterface $mediator;
    private string $value = '';
    
    public function setMediator(MediatorInterface $mediator): void
    {
        $this->mediator = $mediator;
    }
    
    public function notify(string $event, array $data = []): void
    {
        $this->mediator->notify($this, $event, $data);
    }
    
    public function setValue(string $value): void
    {
        $this->value = $value;
        $this->notify('text_changed', ['value' => $value]);
    }
    
    public function handleEvent(string $event, array $data): void
    {
        switch ($event) {
            case 'form_reset':
                $this->value = '';
                break;
        }
    }
}
```

### Uso
```php
$mediator = new FormMediator();
$textField = new TextField();
$button = new SubmitButton();

$mediator->addColleague($textField);
$mediator->addColleague($button);

$textField->setValue("Hello World"); // Notifica tutti gli altri componenti
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di form con validazione interdipendente
- Chat system con notifiche
- Workflow di approvazione
- Sistema di eventi centralizzato

## Correlati

- **Observer Pattern**: Per notifiche unidirezionali
- **Facade Pattern**: Per semplificare interfacce complesse
- **Command Pattern**: Per incapsulare richieste

## Esempi di uso reale

- **Laravel Events**: Sistema di eventi centralizzato
- **Laravel Broadcasting**: Notifiche real-time
- **Form builders**: Validazione interdipendente
- **Chat applications**
- **Workflow engines**
- **UI frameworks**

## Anti-pattern

❌ **Mediatore che fa troppo**: Un mediatore che gestisce troppe responsabilità
```php
// SBAGLIATO
class GodMediator implements MediatorInterface
{
    public function notify(ColleagueInterface $sender, string $event, array $data = []): void
    {
        $this->validateData();
        $this->processPayment();
        $this->sendEmail();
        $this->updateDatabase();
        $this->logActivity();
        // Troppo complesso!
    }
}
```

✅ **Mediatore focalizzato**: Un mediatore per un dominio specifico
```php
// GIUSTO
class FormMediator implements MediatorInterface
{
    public function notify(ColleagueInterface $sender, string $event, array $data = []): void
    {
        // Gestisce solo la logica del form
    }
}
```

## Troubleshooting

**Problema**: Il mediatore è troppo complesso
**Soluzione**: Suddividi in mediatori più piccoli e specifici

**Problema**: Performance lente
**Soluzione**: Considera di usare eventi asincroni o caching

**Problema**: Difficile debuggare
**Soluzione**: Aggiungi logging dettagliato delle interazioni

## Performance e considerazioni

- **Centralization**: Il mediatore può diventare un collo di bottiglia
- **Memory**: Considera il consumo di memoria con molti colleghi
- **Async processing**: Per operazioni pesanti
- **Caching**: Per evitare calcoli ripetuti

## Risorse utili

- [Laravel Events](https://laravel.com/docs/events)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Mediator Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/mediator)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
