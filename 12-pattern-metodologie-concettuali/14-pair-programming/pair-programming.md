# Pair Programming

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Esempi di codice](#esempi-di-codice)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

Pair Programming è una metodologia di sviluppo software in cui due sviluppatori lavorano insieme su un singolo computer. Un sviluppatore (driver) scrive il codice mentre l'altro (navigator) osserva, pensa e fornisce feedback in tempo reale.

L'obiettivo è migliorare la qualità del codice, condividere conoscenze e ridurre i bug attraverso la collaborazione diretta e il controllo incrociato.

## Perché ti serve

Senza pair programming, lo sviluppo individuale può causare:
- **Bug nascosti** non rilevati durante la scrittura
- **Codice inconsistente** con gli standard del team
- **Conoscenza concentrata** in una sola persona
- **Decisioni architetturali** sbagliate
- **Problemi di sicurezza** non evidenti

Con pair programming, ottieni:
- **Qualità superiore** del codice
- **Meno bug** e errori
- **Conoscenza condivisa** nel team
- **Decisioni migliori** grazie alla discussione
- **Apprendimento continuo** per entrambi
- **Codice più pulito** e manutenibile

## Come funziona

### Ruoli nel Pair Programming

**1. Driver (Conducente)**
- Scrive il codice
- Gestisce la tastiera e il mouse
- Implementa le decisioni prese insieme
- Si concentra sui dettagli tecnici

**2. Navigator (Navigatore)**
- Osserva e pensa strategicamente
- Identifica problemi e alternative
- Fornisce feedback in tempo reale
- Si concentra sul quadro generale

### Tecniche di Pair Programming

**1. Ping-Pong**
- Un partner scrive un test
- L'altro implementa il codice
- Si alternano i ruoli

**2. Driver-Navigator**
- Ruoli fissi durante la sessione
- Driver scrive, Navigator guida
- Si scambiano i ruoli periodicamente

**3. Strong-Style**
- Navigator non può toccare la tastiera
- Driver non può scrivere senza istruzioni
- Forza la comunicazione verbale

### Flusso di Lavoro

1. **Pianificazione**: Si discute l'approccio e si definiscono gli obiettivi
2. **Implementazione**: Si scrive il codice insieme
3. **Review**: Si rivede il codice scritto
4. **Refactoring**: Si migliora il codice se necessario
5. **Test**: Si verifica che tutto funzioni correttamente

## Quando usarlo

Usa pair programming quando:
- **Stai sviluppando** funzionalità complesse
- **Hai nuovi membri** nel team
- **Il codice è critico** per il business
- **Vuoi condividere** conoscenze
- **Stai risolvendo** problemi difficili
- **Hai bisogno** di feedback immediato

**NON usarlo quando:**
- **Le task sono** molto semplici
- **Non hai** un partner disponibile
- **Stai facendo** prototipi rapidi
- **Il team è** molto piccolo
- **Stai lavorando** su task individuali

## Pro e contro

**I vantaggi:**
- **Qualità superiore** del codice
- **Meno bug** e errori
- **Conoscenza condivisa** nel team
- **Decisioni migliori** grazie alla discussione
- **Apprendimento continuo** per entrambi
- **Codice più pulito** e manutenibile

**Gli svantaggi:**
- **Costo doppio** in termini di risorse
- **Può rallentare** lo sviluppo iniziale
- **Richiede sincronizzazione** tra i partner
- **Può creare dipendenze** tra i membri
- **Difficile con** team distribuiti

## Esempi di codice

### Esempio 1: Sviluppo di un Service

```php
// Sessione di Pair Programming: Sviluppo UserService

// Navigator: "Dobbiamo creare un UserService che gestisca la creazione utenti"
// Driver: "Iniziamo con un test per definire il comportamento"

// Test scritto insieme
class UserServiceTest extends TestCase
{
    public function test_can_create_user_with_valid_data()
    {
        // Navigator: "Il test dovrebbe verificare che l'utente venga creato correttamente"
        $userService = new UserService();
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $user = $userService->createUser($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }
}

// Navigator: "Ora implementiamo il servizio minimo per far passare il test"
// Driver: "Creo la classe UserService"

class UserService
{
    public function createUser(array $data): User
    {
        // Navigator: "Dobbiamo validare i dati prima di creare l'utente"
        $this->validateUserData($data);
        
        // Driver: "Creo l'utente con i dati validati"
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }
    
    // Navigator: "Aggiungiamo la validazione come metodo privato"
    private function validateUserData(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Name is required');
        }
        
        if (empty($data['email'])) {
            throw new InvalidArgumentException('Email is required');
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }
}

// Navigator: "Ora aggiungiamo un test per la validazione"
// Driver: "Scrivo il test per email invalida"

public function test_throws_exception_for_invalid_email()
{
    $userService = new UserService();
    $userData = [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123'
    ];
    
    $this->expectException(InvalidArgumentException::class);
    $userService->createUser($userData);
}
```

### Esempio 2: Refactoring di un Controller

```php
// Sessione di Pair Programming: Refactoring UserController

// Navigator: "Questo controller ha troppe responsabilità, refactorizziamolo"
// Driver: "Iniziamo estraendo la validazione in un Form Request"

// Prima del refactoring
class UserController extends Controller
{
    public function store(Request $request)
    {
        // Navigator: "Questa validazione dovrebbe essere in un Form Request"
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        
        // Driver: "E la logica business dovrebbe essere in un Service"
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        
        // Navigator: "E l'invio email dovrebbe essere separato"
        Mail::to($user->email)->send(new WelcomeEmail($user));
        
        return response()->json($user, 201);
    }
}

// Dopo il refactoring - Form Request
class UserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ];
    }
}

// Dopo il refactoring - Service
class UserService
{
    public function __construct(
        private UserNotifier $notifier
    ) {}
    
    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
        
        $this->notifier->sendWelcomeEmail($user);
        
        return $user;
    }
}

// Dopo il refactoring - Controller pulito
class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private UserTransformer $transformer
    ) {}
    
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());
        return response()->json($this->transformer->transform($user), 201);
    }
}
```

### Esempio 3: Sviluppo di un Test

```php
// Sessione di Pair Programming: Sviluppo di test per OrderService

// Navigator: "Dobbiamo testare che l'OrderService calcoli correttamente il totale"
// Driver: "Iniziamo con un test semplice"

class OrderServiceTest extends TestCase
{
    public function test_can_calculate_order_total()
    {
        // Navigator: "Creiamo alcuni prodotti per il test"
        $laptop = Product::factory()->create(['price' => 1000]);
        $mouse = Product::factory()->create(['price' => 25]);
        
        // Driver: "E alcuni item per l'ordine"
        $items = [
            ['product_id' => $laptop->id, 'quantity' => 1],
            ['product_id' => $mouse->id, 'quantity' => 2]
        ];
        
        // Navigator: "Ora testiamo il calcolo del totale"
        $orderService = new OrderService();
        $total = $orderService->calculateTotal($items);
        
        // Driver: "Il totale dovrebbe essere 1000 + (25 * 2) = 1050"
        $this->assertEquals(1050, $total);
    }
    
    // Navigator: "Aggiungiamo un test per lo sconto"
    public function test_can_apply_discount_to_order()
    {
        $laptop = Product::factory()->create(['price' => 1000]);
        $items = [['product_id' => $laptop->id, 'quantity' => 1]];
        
        $orderService = new OrderService();
        $total = $orderService->calculateTotal($items);
        
        // Driver: "Applichiamo uno sconto del 10%"
        $discountedTotal = $orderService->applyDiscount($total, 'SAVE10');
        
        // Navigator: "Il totale scontato dovrebbe essere 900"
        $this->assertEquals(900, $discountedTotal);
    }
}

// Navigator: "Ora implementiamo il servizio per far passare i test"
class OrderService
{
    public function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $total += $product->price * $item['quantity'];
        }
        return $total;
    }
    
    public function applyDiscount(float $total, string $discountCode): float
    {
        $discount = Discount::where('code', $discountCode)->first();
        if ($discount && $discount->is_valid) {
            return $total * (1 - $discount->percentage / 100);
        }
        return $total;
    }
}
```

### Esempio 4: Debugging di un Bug

```php
// Sessione di Pair Programming: Debugging di un bug

// Navigator: "Il test sta fallendo, analizziamo il problema"
// Driver: "Eseguiamo il test per vedere l'errore"

public function test_user_can_login_with_valid_credentials()
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);
    
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);
    
    // Navigator: "Il test si aspetta un redirect ma riceve un errore"
    $response->assertRedirect('/dashboard');
}

// Driver: "Controlliamo il controller di login"
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Navigator: "Il problema potrebbe essere nella validazione"
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        // Driver: "Ecco il problema! Stiamo usando 'email' invece di 'username'"
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->intended('/dashboard');
        }
        
        // Navigator: "E manca il messaggio di errore per credenziali invalide"
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.'
        ]);
    }
}

// Navigator: "Ora aggiungiamo un test per credenziali invalide"
public function test_user_cannot_login_with_invalid_credentials()
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);
    
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);
    
    // Driver: "Il test dovrebbe verificare che l'utente non sia autenticato"
    $this->assertGuest();
    
    // Navigator: "E che venga mostrato un errore"
    $response->assertSessionHasErrors(['email']);
}
```

## Principi/Metodologie correlate

- **TDD** - [09-tdd](./09-tdd/tdd.md): Pair programming si integra bene con TDD
- **Code Review** - [13-code-review](./13-code-review/code-review.md): Alternativa al pair programming
- **Mob Programming** - [15-mob-programming](./15-mob-programming/mob-programming.md): Estensione del pair programming
- **Clean Code** - [05-clean-code](./05-clean-code/clean-code.md): Obiettivo del pair programming
- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): Principi da applicare insieme
- **Refactoring** - [12-refactoring](./12-refactoring/refactoring.md): Miglioramento continuo del codice

## Risorse utili

### Documentazione ufficiale
- [Pair Programming](https://en.wikipedia.org/wiki/Pair_programming) - Principi e tecniche
- [Extreme Programming](https://www.amazon.com/Extreme-Programming-Explained-Embrace-Change/dp/0321278658) - Kent Beck
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Robert Martin

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel Testing](https://laravel.com/docs/testing) - Testing in Laravel
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel

### Esempi e tutorial
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP
- [Refactoring.Guru](https://refactoring.guru/) - Design patterns e principi
- [Pair Programming Tips](https://www.agilealliance.org/glossary/pair-programming/) - Consigli pratici

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
