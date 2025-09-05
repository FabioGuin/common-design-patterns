# Principi Fondamentali di Programmazione

I principi fondamentali sono la base di ogni buona pratica di programmazione. Questi principi guidano le decisioni di design e implementazione in tutti i pattern del progetto.

## DRY (Don't Repeat Yourself)

### Definizione
Ogni pezzo di conoscenza deve avere una rappresentazione unica e autorevole all'interno del sistema.

### Benefici
- **Manutenibilità**: Modifiche in un solo posto
- **Consistenza**: Comportamento uniforme
- **Riduzione errori**: Meno duplicazione = meno bug

### Esempi Laravel

#### ❌ Violazione DRY
```php
// Controller 1
public function store(Request $request)
{
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password)
    ]);
    
    // Logica di validazione duplicata
    if (empty($request->name)) {
        return response()->json(['error' => 'Name required'], 400);
    }
}

// Controller 2 - Stessa logica duplicata
public function update(Request $request, $id)
{
    if (empty($request->name)) {
        return response()->json(['error' => 'Name required'], 400);
    }
    
    $user = User::find($id);
    $user->update($request->all());
}
```

#### ✅ Applicazione DRY
```php
// Form Request per validazione
class UserRequest extends FormRequest
{
    public function rules()
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
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }
}

// Controller pulito
class UserController extends Controller
{
    public function store(UserRequest $request, UserService $userService)
    {
        $user = $userService->createUser($request->validated());
        return response()->json($user, 201);
    }
}
```

## KISS (Keep It Simple, Stupid)

### Definizione
Mantenere il codice il più semplice possibile, evitando complessità inutili.

### Benefici
- **Leggibilità**: Più facile da comprendere
- **Manutenibilità**: Meno complessità = meno problemi
- **Debugging**: Più facile trovare e correggere errori

### Esempi Laravel

#### ❌ Complessità Inutile
```php
public function getUserPosts($userId)
{
    $user = User::find($userId);
    
    if ($user) {
        $posts = $user->posts()->where('status', 'published')->get();
        
        $result = [];
        foreach ($posts as $post) {
            $result[] = [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'author' => $post->user->name,
                'created_at' => $post->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        return response()->json($result);
    }
    
    return response()->json(['error' => 'User not found'], 404);
}
```

#### ✅ Soluzione Semplice
```php
public function getUserPosts($userId)
{
    $posts = Post::with('user')
        ->where('user_id', $userId)
        ->where('status', 'published')
        ->get();
    
    return PostResource::collection($posts);
}
```

## YAGNI (You Aren't Gonna Need It)

### Definizione
Non aggiungere funzionalità o scrivere codice che non è attualmente necessario.

### Benefici
- **Focus**: Concentrazione su ciò che serve
- **Velocità**: Sviluppo più rapido
- **Flessibilità**: Meno vincoli per il futuro

### Esempi Laravel

#### ❌ Over-engineering
```php
// Creare migration per funzionalità future non richieste
Schema::create('user_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('theme')->default('light');
    $table->string('language')->default('en');
    $table->json('notifications')->nullable();
    $table->json('privacy_settings')->nullable();
    // ... altre colonne per funzionalità future
});

// Service complesso per funzionalità non necessarie
class UserPreferenceService
{
    public function setTheme($userId, $theme) { /* ... */ }
    public function setLanguage($userId, $language) { /* ... */ }
    public function setNotifications($userId, $settings) { /* ... */ }
    // ... metodi per funzionalità future
}
```

#### ✅ Implementazione YAGNI
```php
// Solo ciò che serve ora
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    
    // Aggiungere funzionalità solo quando richieste
}
```

## SOLID Principles

### S - Single Responsibility Principle (SRP)
Una classe dovrebbe avere una sola responsabilità.

#### Esempio Laravel
```php
// ❌ Violazione SRP
class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validazione
        $request->validate([
            'name' => 'required',
            'email' => 'required|email'
        ]);
        
        // Logica business
        $user = User::create($request->all());
        
        // Invio email
        Mail::to($user->email)->send(new WelcomeEmail($user));
        
        // Log
        Log::info('User created', ['user_id' => $user->id]);
        
        return response()->json($user);
    }
}

// ✅ Rispetto SRP
class UserController extends Controller
{
    public function store(UserRequest $request, UserService $userService)
    {
        $user = $userService->createUser($request->validated());
        return new UserResource($user);
    }
}

class UserService
{
    public function createUser(array $data): User
    {
        $user = User::create($data);
        
        // Delegare responsabilità specifiche
        $this->sendWelcomeEmail($user);
        $this->logUserCreation($user);
        
        return $user;
    }
}
```

### O - Open/Closed Principle (OCP)
Aperto per estensione, chiuso per modifica.

#### Esempio Laravel
```php
// ✅ Rispetto OCP
interface PaymentProcessor
{
    public function process(Payment $payment): bool;
}

class StripePaymentProcessor implements PaymentProcessor
{
    public function process(Payment $payment): bool
    {
        // Implementazione Stripe
        return true;
    }
}

class PayPalPaymentProcessor implements PaymentProcessor
{
    public function process(Payment $payment): bool
    {
        // Implementazione PayPal
        return true;
    }
}

class PaymentService
{
    public function __construct(private PaymentProcessor $processor) {}
    
    public function processPayment(Payment $payment): bool
    {
        return $this->processor->process($payment);
    }
}
```

### L - Liskov Substitution Principle (LSP)
Gli oggetti derivati devono essere sostituibili con oggetti base.

#### Esempio Laravel
```php
// ✅ Rispetto LSP
abstract class NotificationChannel
{
    abstract public function send(string $message, string $recipient): bool;
}

class EmailChannel extends NotificationChannel
{
    public function send(string $message, string $recipient): bool
    {
        // Invio email
        return true;
    }
}

class SmsChannel extends NotificationChannel
{
    public function send(string $message, string $recipient): bool
    {
        // Invio SMS
        return true;
    }
}

// Tutte le implementazioni sono intercambiabili
class NotificationService
{
    public function send(NotificationChannel $channel, string $message, string $recipient): bool
    {
        return $channel->send($message, $recipient);
    }
}
```

### I - Interface Segregation Principle (ISP)
Interfacce specifiche sono meglio di interfacce generali.

#### Esempio Laravel
```php
// ❌ Violazione ISP
interface UserRepository
{
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function findByEmail($email);
    public function findByRole($role);
    public function getActiveUsers();
    public function getInactiveUsers();
    public function getUserStats();
    public function exportToCsv();
    public function importFromCsv($file);
}

// ✅ Rispetto ISP
interface UserRepository
{
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}

interface UserSearchRepository
{
    public function findByEmail($email);
    public function findByRole($role);
    public function getActiveUsers();
}

interface UserReportRepository
{
    public function getUserStats();
    public function exportToCsv();
}
```

### D - Dependency Inversion Principle (DIP)
Dipendere da astrazioni, non da concrezioni.

#### Esempio Laravel
```php
// ❌ Violazione DIP
class OrderService
{
    public function processOrder(Order $order)
    {
        $paymentProcessor = new StripePaymentProcessor();
        $emailService = new MailgunEmailService();
        
        if ($paymentProcessor->process($order->total)) {
            $emailService->send($order->customer_email, 'Order confirmed');
        }
    }
}

// ✅ Rispetto DIP
class OrderService
{
    public function __construct(
        private PaymentProcessor $paymentProcessor,
        private EmailService $emailService
    ) {}
    
    public function processOrder(Order $order): bool
    {
        if ($this->paymentProcessor->process($order->total)) {
            $this->emailService->send($order->customer_email, 'Order confirmed');
            return true;
        }
        
        return false;
    }
}
```

## Applicazione Pratica

### Checklist per Ogni Pattern
- [ ] **DRY**: Evitare duplicazione di codice
- [ ] **KISS**: Mantenere semplicità
- [ ] **YAGNI**: Implementare solo ciò che serve
- [ ] **SRP**: Una responsabilità per classe
- [ ] **OCP**: Estendere senza modificare
- [ ] **LSP**: Sostituibilità delle implementazioni
- [ ] **ISP**: Interfacce specifiche
- [ ] **DIP**: Dipendere da astrazioni

### Integrazione con Laravel
- Usare **Form Requests** per validazione
- Implementare **Service Classes** per logica business
- Utilizzare **Repository Pattern** per accesso dati
- Sfruttare **Service Container** per dependency injection
- Applicare **Resource Classes** per serializzazione

---

*Questi principi sono la base per implementare pattern di qualità in Laravel. Applicarli sistematicamente migliora la manutenibilità, testabilità e scalabilità del codice.*
