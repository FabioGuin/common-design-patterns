# Builder Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Pattern correlati](#pattern-correlati)
- [Esempi di uso reale](#esempi-di-uso-reale)

### Cosa Evitare
- [Anti-pattern](#anti-pattern)

### Implementazione Pratica
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

### Considerazioni Tecniche
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa

Il Builder Pattern ti permette di costruire oggetti complessi passo dopo passo, invece di creare tutto in una volta. È come avere un architetto che ti guida nella costruzione di una casa: prima le fondamenta, poi le pareti, poi il tetto, e così via.

## Perché ti serve

Immagina di dover creare un oggetto `User` con 15 campi diversi. Senza Builder dovresti fare:
```php
$user = new User('Mario', 'Rossi', 'mario@email.com', 'password123', 'Via Roma 1', 'Milano', '20100', 'Italia', '1234567890', '1985-05-15', 'M', 'Sviluppatore', 'Laravel', 'Senior', true);
```

È illeggibile! Con Builder invece:
```php
$user = UserBuilder::create()
    ->withName('Mario', 'Rossi')
    ->withEmail('mario@email.com')
    ->withPassword('password123')
    ->withAddress('Via Roma 1', 'Milano', '20100', 'Italia')
    ->withPhone('1234567890')
    ->withBirthDate('1985-05-15')
    ->withGender('M')
    ->withJob('Sviluppatore', 'Laravel', 'Senior')
    ->isActive()
    ->build();
```

Molto più chiaro e flessibile!

## Come funziona

1. **Builder**: Una classe che ha metodi per impostare ogni parte dell'oggetto
2. **Director**: (Opzionale) Una classe che sa come usare il Builder per creare oggetti specifici
3. **Product**: L'oggetto finale che viene costruito
4. **Metodo build()**: Restituisce l'oggetto completo

Il Builder mantiene lo stato dell'oggetto in costruzione e alla fine lo restituisce completo.

## Schema visivo

```
Scenario 1 (costruzione step-by-step):
Client → Builder::create()
         ↓
    Builder → withName() → withEmail() → withAddress()
         ↓
    Builder → withPhone() → withJob() → isActive()
         ↓
    Builder → build() → User Object

Scenario 2 (costruzione parziale):
Client → Builder::create()
         ↓
    Builder → withName() → withEmail()
         ↓
    Builder → build() → User Object (con solo nome ed email)
```

*Il diagramma mostra come il Builder permette di costruire oggetti in modo flessibile, con tutti i campi o solo alcuni.*

## Quando usarlo

Usa il Builder Pattern quando:
- Hai oggetti con molti parametri (più di 4-5)
- Alcuni parametri sono opzionali
- Vuoi rendere il codice più leggibile
- Hai bisogno di creare varianti dello stesso oggetto
- Vuoi validare i parametri durante la costruzione
- Hai oggetti con configurazioni complesse
- Vuoi costruire oggetti in modo flessibile

**NON usarlo quando:**
- L'oggetto ha solo 2-3 parametri semplici
- Tutti i parametri sono sempre obbligatori
- La costruzione è sempre la stessa
- L'oggetto è molto semplice da creare

## Pro e contro

**I vantaggi:**
- Codice molto più leggibile e manutenibile
- Parametri opzionali gestiti facilmente
- Validazione durante la costruzione
- Possibilità di creare varianti dell'oggetto
- Metodi con nomi descrittivi
- Costruzione flessibile e step-by-step

**Gli svantaggi:**
- Più codice da scrivere
- Può essere eccessivo per oggetti semplici
- Aggiunge complessità per casi semplici
- Richiede più classi e metodi

## Pattern correlati

- **Factory Method**: Quando hai bisogno di creare famiglie di oggetti simili
- **Abstract Factory**: Quando hai bisogno di creare famiglie di oggetti correlati
- **Fluent Interface**: Il Builder spesso usa questo pattern per i metodi concatenati
- **Template Method**: Per definire lo scheletro dell'algoritmo di costruzione

## Esempi di uso reale

- **Laravel Query Builder**: `DB::table('users')->where('active', 1)->orderBy('name')->get()`
- **Laravel Mail**: `Mail::to($user)->subject('Welcome')->view('emails.welcome')->send()`
- **Laravel Validation**: `Validator::make($data, $rules)->sometimes()->required()`
- **HTTP Client Libraries**: Per costruire richieste HTTP complesse
- **Configuration Builders**: Per creare configurazioni complesse

## Anti-pattern

**Cosa NON fare:**
- **Builder per oggetti semplici**: Non usare Builder se hai solo 2-3 parametri
- **Metodi non fluenti**: Evita di non restituire `$this` nei metodi del Builder
- **Validazione nel build()**: Meglio validare durante la costruzione, non alla fine
- **Builder immutabile**: Il Builder deve permettere di modificare lo stato
- **Builder troppo complessi**: Evita Builder con troppi metodi e responsabilità

## Esempi di codice

### Esempio base
```php
<?php

class User
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $phone = null,
        public ?string $address = null,
        public bool $isActive = true
    ) {}
}

class UserBuilder
{
    private string $firstName;
    private string $lastName;
    private string $email;
    private ?string $phone = null;
    private ?string $address = null;
    private bool $isActive = true;

    public static function create(): self
    {
        return new self();
    }

    public function withName(string $firstName, string $lastName): self
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function withPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function withAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function isActive(bool $active = true): self
    {
        $this->isActive = $active;
        return $this;
    }

    public function build(): User
    {
        return new User(
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->phone,
            $this->address,
            $this->isActive
        );
    }
}

// Uso
$user = UserBuilder::create()
    ->withName('Mario', 'Rossi')
    ->withEmail('mario@email.com')
    ->withPhone('1234567890')
    ->withAddress('Via Roma 1')
    ->isActive()
    ->build();
```

### Esempio per Laravel
```php
<?php

namespace App\Builders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserBuilder
{
    private array $data = [];
    private array $profile = [];
    private array $settings = [];

    public static function create(): self
    {
        return new self();
    }

    public function withBasicInfo(string $name, string $email): self
    {
        $this->data = array_merge($this->data, [
            'name' => $name,
            'email' => $email,
        ]);
        return $this;
    }

    public function withPassword(string $password): self
    {
        $this->data['password'] = Hash::make($password);
        return $this;
    }

    public function withProfile(array $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function withSettings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    public function asAdmin(): self
    {
        $this->data['role'] = 'admin';
        return $this;
    }

    public function withEmailVerified(): self
    {
        $this->data['email_verified_at'] = now();
        return $this;
    }

    public function build(): User
    {
        $user = User::create($this->data);
        
        if (!empty($this->profile)) {
            $user->profile()->create($this->profile);
        }
        
        if (!empty($this->settings)) {
            $user->settings()->create($this->settings);
        }
        
        return $user;
    }
}

// Uso nel Controller
class UserController extends Controller
{
    public function store(Request $request)
    {
        $user = UserBuilder::create()
            ->withBasicInfo($request->name, $request->email)
            ->withPassword($request->password)
            ->withProfile([
                'bio' => $request->bio,
                'avatar' => $request->avatar,
            ])
            ->withSettings([
                'notifications' => true,
                'theme' => 'light',
            ])
            ->withEmailVerified()
            ->build();

        return response()->json($user);
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[User Management Builder](../../../esempi-completi/05-user-builder-system/)** - Sistema completo di gestione utenti con Builder Pattern

L'esempio include:
- Builder per creazione utenti complessi
- Validazione durante la costruzione
- Integrazione con Eloquent ORM
- Gestione di relazioni multiple
- Test completi con Pest
- API RESTful per gestire gli utenti
- Gestione di ruoli e permessi

## Performance e considerazioni

- **Impatto memoria**: Leggero overhead per mantenere lo stato del Builder
- **Impatto CPU**: Minimo, solo per la concatenazione dei metodi
- **Scalabilità**: Ottimo, permette di creare oggetti complessi senza problemi
- **Colli di bottiglia**: Nessuno, è un pattern molto efficiente

## Risorse utili

- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns/builder) - Spiegazioni visuali
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Query Builder](https://laravel.com/docs/queries) - Esempio perfetto di Builder Pattern
