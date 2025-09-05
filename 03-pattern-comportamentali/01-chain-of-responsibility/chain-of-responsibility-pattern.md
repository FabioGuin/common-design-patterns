# Chain of Responsibility Pattern

## Cosa fa

Il Chain of Responsibility Pattern ti permette di passare richieste lungo una catena di gestori. Ogni gestore decide se processare la richiesta o passarla al prossimo gestore nella catena. È come una catena di montaggio dove ogni stazione può decidere se lavorare sul pezzo o passarlo alla stazione successiva.

## Perché ti serve

Immagina di avere un sistema di approvazione per ordini. Un ordine deve passare attraverso diversi livelli:
- **Validazione**: Controlla se i dati sono corretti
- **Controllo crediti**: Verifica se il cliente ha credito sufficiente
- **Controllo inventario**: Verifica se i prodotti sono disponibili
- **Approvazione manager**: Per ordini sopra una certa soglia
- **Approvazione direttore**: Per ordini molto grandi

Con Chain of Responsibility, ogni controllo è un gestore separato che può:
- **Processare** la richiesta se è di sua competenza
- **Passare** la richiesta al prossimo gestore
- **Fermare** la catena se trova un problema

## Come funziona

Il pattern ha tre componenti principali:

1. **Handler (Interfaccia)**: Definisce l'interfaccia per gestire le richieste
2. **ConcreteHandler (Gestori Concreti)**: Implementazioni specifiche che gestiscono certi tipi di richieste
3. **Client**: Inizia la catena passando la richiesta al primo gestore

Ogni gestore ha un riferimento al prossimo gestore nella catena e decide se processare o passare la richiesta.

## Schema visivo

```
Client
  ↓
Handler (Interface)
  ↑
ConcreteHandler1 → ConcreteHandler2 → ConcreteHandler3
     ↓                    ↓                    ↓
  Processa?             Processa?             Processa?
     ↓                    ↓                    ↓
  Sì: Stop            No: Next            No: Next
```

## Quando usarlo

- **Sistema di approvazioni** multi-livello
- **Middleware** per richieste HTTP
- **Validazione** a cascata di dati
- **Logging** con diversi livelli di priorità
- **Filtri** per contenuti
- **Gestione errori** con diversi livelli di gravità
- **Pipeline di processing** per dati

## Pro e contro

### Pro
- **Flessibilità**: Puoi aggiungere/rimuovere gestori facilmente
- **Disaccoppiamento**: I gestori non conoscono gli altri
- **Riutilizzabilità**: I gestori possono essere riutilizzati in catene diverse
- **Responsabilità singola**: Ogni gestore ha una responsabilità specifica

### Contro
- **Performance**: Ogni richiesta passa attraverso tutti i gestori
- **Debugging difficile**: È difficile tracciare quale gestore ha processato cosa
- **Ordine importante**: L'ordine dei gestori nella catena è critico
- **Possibili loop infiniti** se non gestito correttamente

## Esempi di codice

### Interfaccia base
```php
interface HandlerInterface
{
    public function setNext(HandlerInterface $handler): HandlerInterface;
    public function handle(Request $request): ?Response;
}
```

### Handler astratto
```php
abstract class AbstractHandler implements HandlerInterface
{
    private ?HandlerInterface $nextHandler = null;
    
    public function setNext(HandlerInterface $handler): HandlerInterface
    {
        $this->nextHandler = $handler;
        return $handler;
    }
    
    public function handle(Request $request): ?Response
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($request);
        }
        
        return null;
    }
}
```

### Gestori concreti
```php
class ValidationHandler extends AbstractHandler
{
    public function handle(Request $request): ?Response
    {
        if (!$this->validate($request)) {
            return new Response('Validation failed', 400);
        }
        
        return parent::handle($request);
    }
    
    private function validate(Request $request): bool
    {
        // Logica di validazione
        return !empty($request->getData());
    }
}

class AuthenticationHandler extends AbstractHandler
{
    public function handle(Request $request): ?Response
    {
        if (!$this->authenticate($request)) {
            return new Response('Authentication failed', 401);
        }
        
        return parent::handle($request);
    }
    
    private function authenticate(Request $request): bool
    {
        // Logica di autenticazione
        return $request->hasValidToken();
    }
}
```

### Uso
```php
// Crea la catena
$validation = new ValidationHandler();
$auth = new AuthenticationHandler();
$authorization = new AuthorizationHandler();

$validation->setNext($auth)->setNext($authorization);

// Processa la richiesta
$response = $validation->handle($request);
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di approvazione ordini multi-livello
- Middleware personalizzato per Laravel
- Gestione errori a cascata
- Pipeline di processing per file

## Correlati

- **Middleware Pattern**: Simile ma più specifico per web
- **Decorator Pattern**: Anche aggiunge funzionalità, ma in modo diverso
- **Command Pattern**: Può essere usato insieme per gestire comandi

## Esempi di uso reale

- **Laravel Middleware**: Sistema di middleware per richieste HTTP
- **Sistema di approvazioni**: Approvazioni multi-livello in applicazioni enterprise
- **Pipeline di validazione**: Validazione dati in più fasi
- **Sistema di logging**: Logging con diversi livelli di priorità
- **Filtri contenuti**: Filtri per contenuti inapplicabili
- **Gestione errori**: Gestione errori con diversi livelli di gravità

## Anti-pattern

❌ **Catena troppo lunga**: Una catena con troppi gestori
```php
// SBAGLIATO
$handler1->setNext($handler2)
         ->setNext($handler3)
         ->setNext($handler4)
         ->setNext($handler5)
         ->setNext($handler6)
         ->setNext($handler7)
         ->setNext($handler8); // Troppo complesso!
```

✅ **Catena focalizzata**: Una catena con gestori specifici e necessari
```php
// GIUSTO
$validation->setNext($auth)->setNext($authorization);
```

## Troubleshooting

**Problema**: La richiesta non viene processata
**Soluzione**: Verifica che almeno un gestore nella catena possa gestire la richiesta

**Problema**: Loop infinito
**Soluzione**: Assicurati che ogni gestore chiami `parent::handle()` o restituisca una risposta

**Problema**: Performance lente
**Soluzione**: Considera di usare un approccio diverso se la catena è troppo lunga

## Performance e considerazioni

- **Ordine dei gestori**: Metti i gestori più specifici prima
- **Early return**: Ferma la catena il prima possibile
- **Caching**: Considera di cachare i risultati se appropriato
- **Monitoring**: Traccia le performance di ogni gestore

## Risorse utili

- [Laravel Middleware](https://laravel.com/docs/middleware)
- [Chain of Responsibility su Refactoring.Guru](https://refactoring.guru/design-patterns/chain-of-responsibility)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
- [Laravel Pipeline](https://laravel.com/docs/pipelines)
