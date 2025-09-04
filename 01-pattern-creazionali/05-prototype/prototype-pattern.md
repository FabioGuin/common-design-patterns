# Prototype Pattern

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)
- [Pattern correlati](#pattern-correlati)
- [Esempi di uso reale](#esempi-di-uso-reale)
- [Anti-pattern](#anti-pattern)
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa
Il Prototype Pattern ti permette di creare nuovi oggetti clonando un prototipo esistente, invece di crearli da zero. È come avere uno stampo per i biscotti: una volta che hai il primo biscotto perfetto, puoi usarlo come modello per farne altri identici.

## Perché ti serve
Immagina di dover creare 100 oggetti `EmailTemplate` con configurazioni complesse. Senza Prototype dovresti:
```php
// Creare ogni template da zero - molto lento!
$template1 = new EmailTemplate('Welcome', 'Welcome to our site!', ['header' => 'blue', 'footer' => 'gray']);
$template2 = new EmailTemplate('Welcome', 'Welcome to our site!', ['header' => 'blue', 'footer' => 'gray']);
// ... ripetere 100 volte
```

Con Prototype invece:
```php
// Crei il prototipo una volta
$prototype = new EmailTemplate('Welcome', 'Welcome to our site!', ['header' => 'blue', 'footer' => 'gray']);

// Poi cloni velocemente
$templates = [];
for ($i = 0; $i < 100; $i++) {
    $templates[] = clone $prototype;
}
```

Molto più veloce e efficiente!

## Come funziona
1. **Prototype**: Un oggetto che implementa l'interfaccia `Cloneable` o usa `clone`
2. **Cloning**: PHP ha il supporto nativo con `clone` e `__clone()`
3. **Deep vs Shallow Copy**: Decidi se clonare anche gli oggetti interni
4. **Registry**: (Opzionale) Un registro per gestire diversi prototipi

Il pattern sfrutta la capacità di PHP di clonare oggetti, permettendo di creare copie identiche senza ricostruire tutto.

## Schema visivo
```
Scenario 1 (clonazione semplice):
Client → Prototype Object
         ↓
    Client → clone $prototype
         ↓
    PHP → __clone() method
         ↓
    Result → New Object (copia del prototipo)

Scenario 2 (clonazione con modifiche):
Client → Prototype Object
         ↓
    Client → clone $prototype
         ↓
    Client → modify cloned object
         ↓
    Result → New Object (copia modificata)
```

*Il diagramma mostra come il Prototype permette di creare copie veloci di oggetti complessi, con o senza modifiche successive.*

## Quando usarlo
Usa il Prototype Pattern quando:
- La creazione di un oggetto è costosa (database, file, network)
- Hai oggetti con configurazioni complesse
- Vuoi creare varianti di un oggetto base
- Hai bisogno di copie identiche di oggetti
- Vuoi evitare di ricostruire oggetti simili

**NON usarlo quando:**
- Gli oggetti sono semplici da creare
- Hai solo 2-3 istanze da creare
- Gli oggetti sono molto diversi tra loro
- La clonazione è più costosa della creazione

## Pro e contro
**I vantaggi:**
- Creazione veloce di oggetti complessi
- Evita di ricostruire configurazioni complesse
- Permette di creare varianti facilmente
- Sfrutta la capacità nativa di PHP
- Riduce il carico computazionale

**Gli svantaggi:**
- Può essere confuso con la clonazione profonda
- Gestione della memoria per oggetti grandi
- Difficile da debuggare se la clonazione non funziona bene
- Può creare dipendenze inaspettate tra oggetti

## Esempi di codice

### Esempio base
```php
<?php

class EmailTemplate
{
    public function __construct(
        public string $subject,
        public string $body,
        public array $styles,
        public array $recipients = []
    ) {}

    public function __clone()
    {
        // Clonazione profonda degli array
        $this->styles = array_map(fn($style) => is_object($style) ? clone $style : $style, $this->styles);
        $this->recipients = array_map(fn($recipient) => is_object($recipient) ? clone $recipient : $recipient, $this->recipients);
    }

    public function addRecipient(string $email): self
    {
        $this->recipients[] = $email;
        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }
}

// Uso
$welcomeTemplate = new EmailTemplate(
    'Welcome!',
    'Welcome to our amazing service!',
    ['header' => 'blue', 'footer' => 'gray']
);

// Clona il template base
$newsletterTemplate = clone $welcomeTemplate;
$newsletterTemplate->setSubject('Newsletter Weekly');

$promoTemplate = clone $welcomeTemplate;
$promoTemplate->setSubject('Special Offer!');
```

### Esempio per Laravel
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'title', 'content', 'template_id', 'metadata', 'settings'
    ];

    protected $casts = [
        'metadata' => 'array',
        'settings' => 'array'
    ];

    public function __clone()
    {
        // Clonazione profonda degli array
        $this->metadata = $this->metadata ? array_map(
            fn($item) => is_array($item) ? $item : $item,
            $this->metadata
        ) : [];
        
        $this->settings = $this->settings ? array_map(
            fn($item) => is_array($item) ? $item : $item,
            $this->settings
        ) : [];
    }

    public static function createFromTemplate(Document $template, string $newTitle): self
    {
        $clone = clone $template;
        $clone->title = $newTitle;
        $clone->id = null; // Reset ID per nuovo record
        $clone->created_at = null;
        $clone->updated_at = null;
        return $clone;
    }
}

// Uso nel Controller
class DocumentController extends Controller
{
    public function duplicate(Document $document)
    {
        $newDocument = Document::createFromTemplate(
            $document,
            $document->title . ' (Copy)'
        );
        
        $newDocument->save();
        
        return response()->json($newDocument);
    }

    public function createFromTemplate(Request $request)
    {
        $template = Document::findOrFail($request->template_id);
        
        $document = Document::createFromTemplate(
            $template,
            $request->title
        );
        
        // Modifica alcuni campi specifici
        if ($request->has('content')) {
            $document->content = $request->content;
        }
        
        $document->save();
        
        return response()->json($document);
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Document Template System](../../../esempi-completi/03-repository-pattern/)** - Sistema di gestione documenti con clonazione di template

L'esempio include:
- Clonazione di documenti complessi
- Gestione di metadati e impostazioni
- Integrazione con Eloquent ORM
- Clonazione profonda di relazioni
- Test completi con Pest

## Pattern correlati
- **Factory Method**: Quando hai bisogno di creare famiglie di oggetti simili
- **Builder**: Quando hai bisogno di costruire oggetti complessi passo dopo passo
- **Registry**: Spesso usato insieme per gestire diversi prototipi

## Esempi di uso reale
- **Laravel Eloquent**: Clonazione di modelli per creare copie
- **Laravel Mail**: Clonazione di template email per personalizzazioni
- **Laravel Forms**: Clonazione di form per creare varianti
- **Laravel Jobs**: Clonazione di job per creare varianti simili

## Anti-pattern
**Cosa NON fare:**
- **Clonazione superficiale di oggetti complessi**: Usa sempre `__clone()` per oggetti con riferimenti
- **Clonazione di oggetti con risorse**: Non clonare file handle, connessioni DB, etc.
- **Clonazione senza reset ID**: Ricorda di resettare ID e timestamp per nuovi record
- **Clonazione eccessiva**: Non clonare oggetti semplici che sono facili da creare

## Performance e considerazioni
- **Impatto memoria**: Può essere alto se cloni oggetti molto grandi
- **Impatto CPU**: Basso, la clonazione è veloce in PHP
- **Scalabilità**: Ottimo per creare molte copie di oggetti complessi
- **Colli di bottiglia**: Attenzione alla clonazione profonda di oggetti molto grandi

## Risorse utili
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns/prototype) - Spiegazioni visuali
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [PHP Clone Documentation](https://www.php.net/manual/en/language.oop5.cloning.php) - Documentazione ufficiale PHP
