# State Pattern

## Cosa fa

Il State Pattern permette a un oggetto di alterare il suo comportamento quando il suo stato interno cambia. L'oggetto apparirà come se avesse cambiato la sua classe. È come avere un oggetto che si comporta diversamente a seconda del suo "umore" o stato attuale.

## Perché ti serve

Immagina di avere un ordine che può essere in diversi stati: pending, confirmed, shipped, delivered, cancelled. Ogni stato ha regole diverse:
- **Pending**: Può essere confermato o cancellato
- **Confirmed**: Può essere spedito o cancellato
- **Shipped**: Può essere consegnato
- **Delivered**: Non può più essere modificato

Senza il State Pattern, avresti un sacco di if/else. Con il State:
- **Ogni stato** è una classe separata
- **Comportamento** specifico per ogni stato
- **Transizioni** chiare e controllate
- **Codice** più pulito e manutenibile

## Come funziona

Il pattern ha tre componenti principali:

1. **Context**: L'oggetto che cambia comportamento
2. **State (Interfaccia)**: Definisce l'interfaccia per gli stati
3. **ConcreteState**: Implementazioni specifiche per ogni stato

## Schema visivo

```
Context → State → ConcreteStateA
    ↓              ConcreteStateB
    ↓              ConcreteStateC
```

## Quando usarlo

- **Finite state machines**
- **Workflow** con stati definiti
- **Game development** (character states)
- **Order processing** systems
- **User authentication** states
- **Document approval** workflows

## Pro e contro

### Pro
- **Clear behavior**: Ogni stato ha comportamento chiaro
- **Easy to add states**: Facile aggiungere nuovi stati
- **No conditionals**: Elimina if/else complessi
- **Single responsibility**: Ogni stato ha una responsabilità

### Contro
- **More classes**: Più classi da gestire
- **State transitions**: Può essere complesso gestire le transizioni
- **Memory usage**: Ogni stato è un oggetto
- **Debugging**: Può essere difficile tracciare gli stati

## Esempi di codice

### Interfaccia State
```php
interface StateInterface
{
    public function handle(OrderContext $context): void;
    public function canTransitionTo(string $state): bool;
    public function getStateName(): string;
}
```

### Context
```php
class OrderContext
{
    private StateInterface $state;
    private array $data;
    
    public function __construct()
    {
        $this->state = new PendingState();
        $this->data = [];
    }
    
    public function setState(StateInterface $state): void
    {
        if ($this->state->canTransitionTo($state->getStateName())) {
            $this->state = $state;
        } else {
            throw new InvalidStateTransitionException(
                "Cannot transition from {$this->state->getStateName()} to {$state->getStateName()}"
            );
        }
    }
    
    public function getState(): StateInterface
    {
        return $this->state;
    }
    
    public function handle(): void
    {
        $this->state->handle($this);
    }
    
    public function getData(): array
    {
        return $this->data;
    }
    
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
```

### Stati concreti
```php
class PendingState implements StateInterface
{
    public function handle(OrderContext $context): void
    {
        echo "Order is pending. Waiting for confirmation.\n";
        // Logica specifica per stato pending
    }
    
    public function canTransitionTo(string $state): bool
    {
        return in_array($state, ['confirmed', 'cancelled']);
    }
    
    public function getStateName(): string
    {
        return 'pending';
    }
}

class ConfirmedState implements StateInterface
{
    public function handle(OrderContext $context): void
    {
        echo "Order is confirmed. Preparing for shipment.\n";
        // Logica specifica per stato confirmed
    }
    
    public function canTransitionTo(string $state): bool
    {
        return in_array($state, ['shipped', 'cancelled']);
    }
    
    public function getStateName(): string
    {
        return 'confirmed';
    }
}

class ShippedState implements StateInterface
{
    public function handle(OrderContext $context): void
    {
        echo "Order is shipped. In transit.\n";
        // Logica specifica per stato shipped
    }
    
    public function canTransitionTo(string $state): bool
    {
        return $state === 'delivered';
    }
    
    public function getStateName(): string
    {
        return 'shipped';
    }
}

class DeliveredState implements StateInterface
{
    public function handle(OrderContext $context): void
    {
        echo "Order is delivered. Complete.\n";
        // Logica specifica per stato delivered
    }
    
    public function canTransitionTo(string $state): bool
    {
        return false; // Stato finale
    }
    
    public function getStateName(): string
    {
        return 'delivered';
    }
}
```

### Uso
```php
$order = new OrderContext();

// Stato iniziale
$order->handle(); // "Order is pending. Waiting for confirmation."

// Transizione a confirmed
$order->setState(new ConfirmedState());
$order->handle(); // "Order is confirmed. Preparing for shipment."

// Transizione a shipped
$order->setState(new ShippedState());
$order->handle(); // "Order is shipped. In transit."

// Transizione a delivered
$order->setState(new DeliveredState());
$order->handle(); // "Order is delivered. Complete."
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di workflow per documenti
- Stati di autenticazione utente
- Workflow di approvazione
- Sistema di notifiche basato su stati

## Correlati

- **Strategy Pattern**: Per comportamenti diversi
- **Command Pattern**: Per azioni specifiche per stato
- **Observer Pattern**: Per notificare cambiamenti di stato

## Esempi di uso reale

- **Laravel Eloquent**: Stati dei modelli
- **Laravel Workflow**: Per workflow complessi
- **Order processing**: Stati degli ordini
- **User authentication**: Stati di login
- **Document approval**: Workflow di approvazione
- **Game development**: Stati dei personaggi

## Anti-pattern

❌ **Stato che fa troppo**: Un stato che gestisce troppe responsabilità
```php
// SBAGLIATO
class GodState implements StateInterface
{
    public function handle(OrderContext $context): void
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

✅ **Stato focalizzato**: Un stato per una responsabilità specifica
```php
// GIUSTO
class PendingState implements StateInterface
{
    public function handle(OrderContext $context): void
    {
        // Solo logica per stato pending
    }
}
```

## Troubleshooting

**Problema**: Transizione di stato non valida
**Soluzione**: Verifica che la transizione sia permessa nel metodo `canTransitionTo`

**Problema**: Stato non cambia
**Soluzione**: Controlla che il nuovo stato sia valido e che la transizione sia permessa

**Problema**: Comportamento inaspettato
**Soluzione**: Verifica che il metodo `handle` sia implementato correttamente

## Performance e considerazioni

- **State creation**: Considera di riutilizzare istanze di stato
- **Memory usage**: Ogni stato è un oggetto
- **State persistence**: Per salvare lo stato nel database
- **Validation**: Per validare le transizioni di stato

## Risorse utili

- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Laravel Workflow](https://laravel.com/docs/workflow)
- [State Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/state)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
