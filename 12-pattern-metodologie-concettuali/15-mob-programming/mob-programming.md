# Mob Programming

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

Mob Programming è una metodologia di sviluppo software in cui l'intero team lavora insieme su un singolo computer. Un membro del team (driver) scrive il codice mentre tutti gli altri (mob) osservano, discutono e forniscono feedback in tempo reale.

L'obiettivo è massimizzare la condivisione di conoscenze, migliorare la qualità del codice e accelerare lo sviluppo attraverso la collaborazione di tutto il team.

## Perché ti serve

Senza mob programming, lo sviluppo tradizionale può causare:
- **Conoscenza concentrata** in pochi membri
- **Decisioni architetturali** sbagliate
- **Codice inconsistente** tra i membri del team
- **Problemi di comunicazione** e allineamento
- **Rework costoso** quando le decisioni sono sbagliate

Con mob programming, ottieni:
- **Conoscenza condivisa** in tutto il team
- **Decisioni migliori** grazie alla discussione collettiva
- **Codice consistente** e ben progettato
- **Comunicazione efficace** e allineamento
- **Apprendimento accelerato** per tutti
- **Qualità superiore** del software

## Come funziona

### Ruoli nel Mob Programming

**1. Driver (Conducente)**
- Scrive il codice
- Gestisce la tastiera e il mouse
- Implementa le decisioni del mob
- Si concentra sui dettagli tecnici

**2. Navigator (Navigatore)**
- Guida la direzione del codice
- Identifica problemi e alternative
- Coordina la discussione
- Si concentra sul quadro generale

**3. Mob (Folla)**
- Osserva e partecipa alla discussione
- Fornisce feedback e suggerimenti
- Condivide conoscenze e esperienze
- Aiuta a identificare problemi

### Tecniche di Mob Programming

**1. Round-Robin**
- I ruoli ruotano tra i membri del team
- Ogni membro ha la possibilità di guidare
- Si evita la concentrazione del potere

**2. Strong-Style**
- Solo il navigatore può dare istruzioni
- Il driver non può scrivere senza istruzioni
- Forza la comunicazione verbale

**3. Time-Boxed**
- Sessioni di durata limitata (1-2 ore)
- Pause regolari per evitare affaticamento
- Rotazione dei ruoli ad intervalli fissi

### Flusso di Lavoro

1. **Pianificazione**: Il team discute l'approccio e definisce gli obiettivi
2. **Implementazione**: Si scrive il codice insieme
3. **Discussione**: Si discute ogni decisione importante
4. **Review**: Si rivede il codice scritto
5. **Refactoring**: Si migliora il codice se necessario
6. **Test**: Si verifica che tutto funzioni correttamente

## Quando usarlo

Usa mob programming quando:
- **Hai un team** di 3-8 persone
- **Stai sviluppando** funzionalità complesse
- **Vuoi condividere** conoscenze nel team
- **Il codice è critico** per il business
- **Hai nuovi membri** nel team
- **Stai risolvendo** problemi difficili

**NON usarlo quando:**
- **Il team è** molto grande (>8 persone)
- **Le task sono** molto semplici
- **Non hai** un team disponibile
- **Stai facendo** prototipi rapidi
- **Stai lavorando** su task individuali
- **Il team è** distribuito geograficamente

## Pro e contro

**I vantaggi:**
- **Conoscenza condivisa** in tutto il team
- **Decisioni migliori** grazie alla discussione collettiva
- **Codice consistente** e ben progettato
- **Comunicazione efficace** e allineamento
- **Apprendimento accelerato** per tutti
- **Qualità superiore** del software

**Gli svantaggi:**
- **Costo elevato** in termini di risorse
- **Può rallentare** lo sviluppo iniziale
- **Richiede sincronizzazione** tra tutti i membri
- **Difficile con** team distribuiti
- **Può creare** dipendenze tra i membri
- **Richiede** un team ben coordinato

## Esempi di codice

### Esempio 1: Sviluppo di un Sistema di Autenticazione

```php
// Sessione di Mob Programming: Sviluppo sistema di autenticazione

// Navigator: "Dobbiamo creare un sistema di autenticazione completo"
// Mob: "Iniziamo con i test per definire il comportamento"
// Driver: "Scrivo il test per il login"

class AuthTest extends TestCase
{
    public function test_user_can_login_with_valid_credentials()
    {
        // Mob: "Creiamo un utente per il test"
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        
        // Navigator: "Testiamo il login con credenziali valide"
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        // Mob: "Il login dovrebbe reindirizzare al dashboard"
        $response->assertRedirect('/dashboard');
        
        // Driver: "E l'utente dovrebbe essere autenticato"
        $this->assertAuthenticated();
    }
    
    // Mob: "Aggiungiamo un test per credenziali invalide"
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        
        // Navigator: "Testiamo con password sbagliata"
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
        
        // Mob: "Dovrebbe rimanere non autenticato"
        $this->assertGuest();
        
        // Driver: "E dovrebbe mostrare un errore"
        $response->assertSessionHasErrors(['email']);
    }
}

// Navigator: "Ora implementiamo il controller di autenticazione"
// Mob: "Dobbiamo gestire la validazione e l'autenticazione"
// Driver: "Creo il controller"

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Mob: "Usiamo un Form Request per la validazione"
        $credentials = $request->validated();
        
        // Navigator: "Implementiamo l'autenticazione"
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }
        
        // Mob: "Gestiamo il caso di credenziali invalide"
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.'
        ]);
    }
    
    // Mob: "Aggiungiamo il logout"
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

// Navigator: "Creiamo il Form Request per la validazione"
// Mob: "Dovrebbe validare email e password"
// Driver: "Implemento il Form Request"

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string'
        ];
    }
    
    // Mob: "Aggiungiamo messaggi personalizzati"
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Password is required'
        ];
    }
}
```

### Esempio 2: Sviluppo di un Sistema di Ordini

```php
// Sessione di Mob Programming: Sviluppo sistema di ordini

// Navigator: "Dobbiamo creare un sistema completo per gestire gli ordini"
// Mob: "Iniziamo con i test per definire il comportamento"
// Driver: "Scrivo il test per la creazione di un ordine"

class OrderTest extends TestCase
{
    public function test_can_create_order_with_valid_items()
    {
        // Mob: "Creiamo un utente e alcuni prodotti"
        $user = User::factory()->create();
        $laptop = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        $mouse = Product::factory()->create(['price' => 25, 'stock' => 10]);
        
        // Navigator: "Testiamo la creazione di un ordine"
        $this->actingAs($user);
        
        $response = $this->post('/orders', [
            'items' => [
                ['product_id' => $laptop->id, 'quantity' => 1],
                ['product_id' => $mouse->id, 'quantity' => 2]
            ],
            'shipping_address' => '123 Main St, City, Country',
            'payment_method' => 'credit_card'
        ]);
        
        // Mob: "L'ordine dovrebbe essere creato con successo"
        $response->assertStatus(201);
        
        // Driver: "E dovrebbe essere salvato nel database"
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
    }
}

// Navigator: "Ora implementiamo il servizio per gestire gli ordini"
// Mob: "Dobbiamo separare le responsabilità"
// Driver: "Creo il servizio"

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderCalculator $calculator,
        private OrderValidator $validator,
        private OrderNotifier $notifier
    ) {}
    
    public function createOrder(array $data): Order
    {
        // Mob: "Validiamo i dati prima di procedere"
        $this->validator->validate($data);
        
        // Navigator: "Calcoliamo il totale dell'ordine"
        $total = $this->calculator->calculateTotal($data['items']);
        
        // Driver: "Creiamo l'ordine nel database"
        $order = $this->orderRepository->create([
            'user_id' => $data['user_id'],
            'total' => $total,
            'status' => 'pending',
            'shipping_address' => $data['shipping_address'],
            'payment_method' => $data['payment_method']
        ]);
        
        // Mob: "Inviamo una notifica di conferma"
        $this->notifier->sendConfirmationEmail($order);
        
        return $order;
    }
}

// Navigator: "Creiamo i servizi di supporto"
// Mob: "Separiamo le responsabilità in classi specifiche"
// Driver: "Implemento il calcolatore"

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

// Mob: "E il validatore"
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
```

### Esempio 3: Refactoring di un Controller Complesso

```php
// Sessione di Mob Programming: Refactoring di un controller complesso

// Navigator: "Questo controller ha troppe responsabilità, refactorizziamolo"
// Mob: "Iniziamo identificando le responsabilità"
// Driver: "Analizzo il codice esistente"

// Prima del refactoring
class UserController extends Controller
{
    public function store(Request $request)
    {
        // Mob: "Validazione inline - dovrebbe essere in Form Request"
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        
        // Navigator: "Logica business nel controller - dovrebbe essere in Service"
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        
        // Mob: "Invio email nel controller - dovrebbe essere in Service"
        Mail::to($user->email)->send(new WelcomeEmail($user));
        
        // Driver: "Log nel controller - dovrebbe essere in Service"
        Log::info('User created', ['user_id' => $user->id]);
        
        // Navigator: "Serializzazione inline - dovrebbe essere in Transformer"
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->format('Y-m-d H:i:s')
        ], 201);
    }
}

// Mob: "Ora refactorizziamo passo per passo"
// Navigator: "Iniziamo con il Form Request"
// Driver: "Creo il Form Request"

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

// Mob: "Ora creiamo il Service"
// Navigator: "Separiamo la logica business"
// Driver: "Implemento il Service"

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

// Mob: "E il Transformer per la serializzazione"
// Navigator: "Separiamo la logica di serializzazione"
// Driver: "Creo il Transformer"

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

// Mob: "Ora il controller è pulito"
// Navigator: "Controller con responsabilità limitate"
// Driver: "Implemento il controller refactorizzato"

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

### Esempio 4: Sviluppo di un Sistema di Notifiche

```php
// Sessione di Mob Programming: Sviluppo sistema di notifiche

// Navigator: "Dobbiamo creare un sistema flessibile per le notifiche"
// Mob: "Iniziamo con i test per definire il comportamento"
// Driver: "Scrivo il test per l'invio di notifiche"

class NotificationTest extends TestCase
{
    public function test_can_send_notification_to_user()
    {
        // Mob: "Creiamo un utente per il test"
        $user = User::factory()->create();
        
        // Navigator: "Testiamo l'invio di una notifica"
        $notification = new UserNotification([
            'title' => 'Welcome!',
            'message' => 'Welcome to our platform',
            'type' => 'info'
        ]);
        
        $user->notify($notification);
        
        // Driver: "La notifica dovrebbe essere salvata nel database"
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'info'
        ]);
    }
}

// Navigator: "Ora implementiamo il sistema di notifiche"
// Mob: "Dobbiamo creare un sistema flessibile e estensibile"
// Driver: "Creo il servizio di notifiche"

class NotificationService
{
    public function __construct(
        private array $channels
    ) {}
    
    public function send(User $user, Notification $notification): void
    {
        foreach ($this->channels as $channel) {
            $channel->send($user, $notification);
        }
    }
}

// Mob: "Creiamo i canali di notifica"
// Navigator: "Implementiamo il pattern Strategy"
// Driver: "Creo l'interfaccia per i canali"

interface NotificationChannel
{
    public function send(User $user, Notification $notification): void;
}

// Mob: "E l'implementazione per email"
class EmailNotificationChannel implements NotificationChannel
{
    public function send(User $user, Notification $notification): void
    {
        Mail::to($user->email)->send(new NotificationMail($notification));
    }
}

// Mob: "E per SMS"
class SmsNotificationChannel implements NotificationChannel
{
    public function send(User $user, Notification $notification): void
    {
        // Implementazione per SMS
    }
}

// Mob: "E per push notification"
class PushNotificationChannel implements NotificationChannel
{
    public function send(User $user, Notification $notification): void
    {
        // Implementazione per push notification
    }
}
```

## Correlati

### Pattern

- **[Pair Programming](./14-pair-programming/pair-programming.md)** - Base per mob programming
- **[TDD](./09-tdd/tdd.md)** - Mob programming si integra bene con TDD
- **[Clean Code](./05-clean-code/clean-code.md)** - Obiettivo del mob programming
- **[SOLID Principles](./04-solid-principles/solid-principles.md)** - Principi da applicare insieme
- **[Refactoring](./12-refactoring/refactoring.md)** - Miglioramento continuo del codice
- **[Code Review](./13-code-review/code-review.md)** - Alternativa al mob programming

### Principi e Metodologie

- **[Mob Programming](https://en.wikipedia.org/wiki/Mob_programming)** - Metodologia originale di programmazione in gruppo
- **[Collective Code Ownership](https://en.wikipedia.org/wiki/Collective_code_ownership)** - Proprietà collettiva del codice
- **[Collaborative Development](https://en.wikipedia.org/wiki/Collaborative_software)** - Sviluppo collaborativo


## Risorse utili

### Documentazione ufficiale
- [Mob Programming](https://mobprogramming.org/) - Sito ufficiale
- [Mob Programming Guide](https://www.agilealliance.org/glossary/mob-programming/) - Agile Alliance
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Robert Martin

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel Testing](https://laravel.com/docs/testing) - Testing in Laravel
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel

### Esempi e tutorial
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP
- [Refactoring.Guru](https://refactoring.guru/) - Design patterns e principi
- [Mob Programming Tips](https://www.agilealliance.org/glossary/mob-programming/) - Consigli pratici

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
