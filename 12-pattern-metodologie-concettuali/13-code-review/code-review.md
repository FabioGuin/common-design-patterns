# Code Review

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Esempi di codice](#esempi-di-codice)
- [Correlati](#correlati)
- [Risorse utili](#risorse-utili)

## Cosa fa

Code Review è il processo di esaminare il codice scritto da altri sviluppatori prima che venga integrato nel codice principale. L'obiettivo è migliorare la qualità del codice, condividere conoscenze e identificare potenziali problemi.

Il code review non è solo controllo di qualità, ma anche un'opportunità di apprendimento e condivisione di best practices all'interno del team.

## Perché ti serve

Senza code review, il codice può avere:
- **Bug nascosti** non rilevati dai test
- **Problemi di sicurezza** non evidenti
- **Codice inconsistente** con gli standard del team
- **Performance issues** non ottimizzate
- **Architettura sbagliata** che causa problemi futuri

Con code review, ottieni:
- **Qualità superiore** del codice
- **Meno bug** in produzione
- **Conoscenza condivisa** nel team
- **Standard consistenti** di codifica
- **Apprendimento continuo** per tutti
- **Migliore architettura** e design

## Come funziona

### Processo di Code Review

**1. Preparazione**
- L'autore prepara il codice per la review
- Scrive una descrizione chiara delle modifiche
- Esegue i test e verifica che passino
- Controlla il codice per errori evidenti

**2. Review**
- Il reviewer esamina il codice
- Controlla logica, architettura e best practices
- Verifica test e documentazione
- Fornisce feedback costruttivo

**3. Discussione**
- Autore e reviewer discutono i feedback
- Si risolvono i problemi identificati
- Si condividono conoscenze e alternative

**4. Integrazione**
- Il codice viene integrato dopo l'approvazione
- Si monitora il comportamento in produzione
- Si raccoglie feedback per miglioramenti futuri

### Tipi di Code Review

**1. Formal Review**
- Processo strutturato e documentato
- Checklist dettagliate
- Approvazione formale richiesta

**2. Peer Review**
- Review tra colleghi di pari livello
- Processo più informale
- Focus su apprendimento reciproco

**3. Expert Review**
- Review da parte di esperti
- Focus su architettura e design
- Approvazione da senior developer

## Quando usarlo

Usa code review quando:
- **Stai integrando** nuovo codice
- **Hai modifiche** significative
- **Stai lavorando** in team
- **Il codice è critico** per il business
- **Vuoi migliorare** la qualità
- **Hai nuovi membri** nel team

**NON usarlo quando:**
- **Stai facendo** prototipi rapidi
- **Le modifiche sono** molto piccole
- **Non hai tempo** per il processo
- **Il team è** molto piccolo
- **Stai lavorando** da solo

## Pro e contro

**I vantaggi:**
- **Qualità superiore** del codice
- **Meno bug** in produzione
- **Conoscenza condivisa** nel team
- **Standard consistenti** di codifica
- **Apprendimento continuo** per tutti
- **Migliore architettura** e design

**Gli svantaggi:**
- **Tempo aggiuntivo** per il processo
- **Può rallentare** lo sviluppo
- **Richiede formazione** del team
- **Può creare tensioni** se mal gestito
- **Difficile con** team distribuiti

## Esempi di codice

### Esempio 1: Review di un Controller

```php
// ❌ Codice da revieware - Controller con problemi
class UserController extends Controller
{
    public function store(Request $request)
    {
        // Problema: Validazione inline invece di Form Request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        
        // Problema: Logica business nel controller
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        
        // Problema: Invio email nel controller
        Mail::to($user->email)->send(new WelcomeEmail($user));
        
        // Problema: Log nel controller
        Log::info('User created', ['user_id' => $user->id]);
        
        // Problema: Serializzazione inline
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->format('Y-m-d H:i:s')
        ], 201);
    }
}

// ✅ Dopo la review - Controller migliorato
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

// Form Request per validazione
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

// Service per logica business
class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserNotifier $notifier,
        private UserLogger $logger
    ) {}
    
    public function createUser(array $data): User
    {
        $user = $this->userRepository->create($data);
        $this->notifier->sendWelcomeEmail($user);
        $this->logger->logUserCreation($user);
        return $user;
    }
}

// Transformer per serializzazione
class UserTransformer
{
    public function transform(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->format('Y-m-d H:i:s')
        ];
    }
}
```

### Esempio 2: Review di un Service

```php
// ❌ Codice da revieware - Service con problemi
class OrderService
{
    public function createOrder($data)
    {
        // Problema: Nessuna validazione
        $order = Order::create($data);
        
        // Problema: Logica complessa inline
        $total = 0;
        foreach ($data['items'] as $item) {
            $product = Product::find($item['product_id']);
            $total += $product->price * $item['quantity'];
        }
        
        // Problema: Aggiornamento diretto del modello
        $order->total = $total;
        $order->save();
        
        // Problema: Invio email inline
        Mail::to($order->customer->email)->send(new OrderConfirmationMail($order));
        
        return $order;
    }
}

// ✅ Dopo la review - Service migliorato
class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderCalculator $calculator,
        private OrderNotifier $notifier,
        private OrderValidator $validator
    ) {}
    
    public function createOrder(array $data): Order
    {
        $this->validator->validate($data);
        
        $order = $this->orderRepository->create($data);
        $total = $this->calculator->calculateTotal($data['items']);
        
        $this->orderRepository->updateTotal($order, $total);
        $this->notifier->sendConfirmationEmail($order);
        
        return $order;
    }
}

// Validator per validazione
class OrderValidator
{
    public function validate(array $data): void
    {
        if (empty($data['items'])) {
            throw new InvalidArgumentException('Items are required');
        }
        
        foreach ($data['items'] as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                throw new InvalidArgumentException('Invalid item data');
            }
        }
    }
}

// Calculator per calcoli
class OrderCalculator
{
    public function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                throw new InvalidArgumentException('Product not found');
            }
            
            $total += $product->price * $item['quantity'];
        }
        return $total;
    }
}

// Notifier per notifiche
class OrderNotifier
{
    public function sendConfirmationEmail(Order $order): void
    {
        Mail::to($order->customer->email)->send(new OrderConfirmationMail($order));
    }
}
```

### Esempio 3: Review di un Test

```php
// ❌ Test da revieware - Test con problemi
class UserTest extends TestCase
{
    public function test_can_create_user()
    {
        // Problema: Test troppo generico
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }
    
    public function test_user_has_email()
    {
        // Problema: Test banale
        $user = User::factory()->create(['email' => 'test@example.com']);
        $this->assertEquals('test@example.com', $user->email);
    }
}

// ✅ Dopo la review - Test migliorato
class UserTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_user_with_valid_data()
    {
        // Given: Valid user data
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        // When: Creating a user
        $user = User::create($userData);
        
        // Then: User should be created successfully
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $this->assertTrue(Hash::check('password123', $user->password));
    }
    
    public function test_cannot_create_user_with_duplicate_email()
    {
        // Given: An existing user
        User::factory()->create(['email' => 'john@example.com']);
        
        // When: Trying to create another user with same email
        $this->expectException(QueryException::class);
        
        User::create([
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
    }
    
    public function test_user_password_is_hashed()
    {
        // Given: User data with plain password
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'plainpassword'
        ];
        
        // When: Creating a user
        $user = User::create($userData);
        
        // Then: Password should be hashed
        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }
}
```

### Esempio 4: Review di un Model

```php
// ❌ Model da revieware - Model con problemi
class User extends Model
{
    // Problema: Nessuna protezione dei campi
    protected $fillable = ['*'];
    
    // Problema: Metodo troppo complesso
    public function getFullName()
    {
        $name = $this->first_name . ' ' . $this->last_name;
        if ($this->middle_name) {
            $name = $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
        }
        return trim($name);
    }
    
    // Problema: Logica business nel model
    public function sendWelcomeEmail()
    {
        Mail::to($this->email)->send(new WelcomeEmail($this));
    }
}

// ✅ Dopo la review - Model migliorato
class User extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password'
    ];
    
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];
    
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        
        return implode(' ', $parts);
    }
    
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
```

## Correlati

### Pattern

- **[TDD](./09-tdd/tdd.md)** - Test guidano il code review
- **[Clean Code](./05-clean-code/clean-code.md)** - Obiettivo del code review
- **[SOLID Principles](./04-solid-principles/solid-principles.md)** - Principi da verificare durante la review
- **[Pair Programming](./14-pair-programming/pair-programming.md)** - Alternativa al code review
- **[Refactoring](./12-refactoring/refactoring.md)** - Miglioramento del codice dopo la review

### Principi e Metodologie

- **[Code Review](https://en.wikipedia.org/wiki/Code_review)** - Metodologia originale di revisione del codice
- **[Peer Review](https://en.wikipedia.org/wiki/Peer_review)** - Revisione tra pari
- **[Quality Assurance](https://en.wikipedia.org/wiki/Software_quality_assurance)** - Assicurazione della qualità


## Risorse utili

### Documentazione ufficiale
- [Code Review Best Practices](https://google.github.io/eng-practices/review/) - Google
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Robert Martin

### Laravel specifico
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
- [Laravel IDE Helper](https://github.com/barryvdh/laravel-ide-helper) - Supporto IDE
- [Laravel Testing](https://laravel.com/docs/testing) - Testing in Laravel

### Esempi e tutorial
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel
- [Refactoring.Guru](https://refactoring.guru/) - Design patterns e principi

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
