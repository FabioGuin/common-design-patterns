# Test-Driven Development (TDD) e BDD

Le metodologie di testing guidano lo sviluppo verso codice più robusto e affidabile. In Laravel, queste pratiche sono supportate da strumenti potenti come PHPUnit e Pest.

## Test-Driven Development (TDD)

### Ciclo Red-Green-Refactor

#### 1. Red - Scrivere un Test che Fallisce
```php
// tests/Feature/UserRegistrationTest.php
public function test_user_can_register_with_valid_data()
{
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ];
    
    $response = $this->post('/register', $userData);
    
    $response->assertStatus(201);
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com'
    ]);
}
```

#### 2. Green - Implementare il Codice Minimo
```php
// routes/web.php
Route::post('/register', [AuthController::class, 'register']);

// app/Http/Controllers/AuthController.php
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed'
    ]);
    
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password'])
    ]);
    
    return response()->json($user, 201);
}
```

#### 3. Refactor - Migliorare il Codice
```php
// app/Http/Controllers/AuthController.php
public function register(RegisterRequest $request, UserService $userService)
{
    $user = $userService->createUser($request->validated());
    return new UserResource($user);
}

// app/Http/Requests/RegisterRequest.php
class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ];
    }
}
```

### Tipi di Test in TDD

#### Unit Tests
```php
// tests/Unit/UserServiceTest.php
class UserServiceTest extends TestCase
{
    public function test_can_create_user_with_valid_data()
    {
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
```

#### Integration Tests
```php
// tests/Feature/UserRegistrationTest.php
class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_registration_flow()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        
        $response = $this->post('/register', $userData);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
        
        // Verifica che l'utente possa fare login
        $loginResponse = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
        
        $loginResponse->assertStatus(200);
    }
}
```

## Behavior-Driven Development (BDD)

### Struttura Given-When-Then

#### Esempio con Pest
```php
// tests/Feature/UserRegistrationTest.php
test('user can register with valid data', function () {
    // Given: I have valid user data
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ];
    
    // When: I submit the registration form
    $response = $this->post('/register', $userData);
    
    // Then: The user should be created and I should get a success response
    $response->assertStatus(201);
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com'
    ]);
});
```

#### Esempio con Feature Files (Gherkin)
```gherkin
# features/user_registration.feature
Feature: User Registration
  As a visitor
  I want to register for an account
  So that I can access the application

  Scenario: Successful registration with valid data
    Given I am on the registration page
    When I fill in the form with valid data
    And I submit the registration form
    Then I should see a success message
    And I should be able to login with my credentials

  Scenario: Registration fails with invalid email
    Given I am on the registration page
    When I fill in the form with an invalid email
    And I submit the registration form
    Then I should see an error message
    And the user should not be created
```

### BDD con Laravel Dusk
```php
// tests/Browser/UserRegistrationTest.php
class UserRegistrationTest extends DuskTestCase
{
    public function test_user_can_register_successfully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->type('name', 'John Doe')
                    ->type('email', 'john@example.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Register')
                    ->assertPathIs('/dashboard')
                    ->assertSee('Welcome, John Doe!');
        });
    }
}
```

## Acceptance Test-Driven Development (ATDD)

### Definizione dei Criteri di Accettazione
```php
// tests/Acceptance/UserRegistrationAcceptanceTest.php
class UserRegistrationAcceptanceTest extends TestCase
{
    public function test_user_registration_meets_acceptance_criteria()
    {
        // Criterio 1: L'utente può registrarsi con dati validi
        $this->testValidUserRegistration();
        
        // Criterio 2: La validazione impedisce registrazioni con dati invalidi
        $this->testInvalidUserRegistration();
        
        // Criterio 3: L'utente riceve conferma via email
        $this->testWelcomeEmailSent();
        
        // Criterio 4: L'utente può fare login dopo la registrazione
        $this->testUserCanLoginAfterRegistration();
    }
    
    private function testValidUserRegistration()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        
        $response = $this->post('/register', $userData);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }
}
```

## Test Doubles e Mocking

### Test Doubles in Laravel
```php
// tests/Unit/UserServiceTest.php
class UserServiceTest extends TestCase
{
    public function test_sends_welcome_email_after_user_creation()
    {
        // Mock del servizio email
        Mail::fake();
        
        $userService = new UserService();
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $user = $userService->createUser($userData);
        
        // Verifica che l'email sia stata inviata
        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
    
    public function test_handles_email_service_failure()
    {
        // Mock del servizio email per simulare errore
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new Exception('Email service unavailable'));
        
        $userService = new UserService();
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        // Il servizio dovrebbe gestire l'errore gracefully
        $this->expectException(Exception::class);
        $userService->createUser($userData);
    }
}
```

### Mocking di Servizi Esterni
```php
// tests/Unit/PaymentServiceTest.php
class PaymentServiceTest extends TestCase
{
    public function test_processes_payment_successfully()
    {
        // Mock del servizio di pagamento
        $paymentProcessor = Mockery::mock(PaymentProcessor::class);
        $paymentProcessor->shouldReceive('process')
            ->once()
            ->with(Mockery::type(Payment::class))
            ->andReturn(true);
        
        $this->app->instance(PaymentProcessor::class, $paymentProcessor);
        
        $paymentService = new PaymentService($paymentProcessor);
        $payment = new Payment(100.00, 'usd');
        
        $result = $paymentService->processPayment($payment);
        
        $this->assertTrue($result);
    }
}
```

## Test Data Builders

### Pattern Builder per Test Data
```php
// tests/Support/UserBuilder.php
class UserBuilder
{
    private array $attributes = [];
    
    public static function new(): self
    {
        return new self();
    }
    
    public function withName(string $name): self
    {
        $this->attributes['name'] = $name;
        return $this;
    }
    
    public function withEmail(string $email): self
    {
        $this->attributes['email'] = $email;
        return $this;
    }
    
    public function withPassword(string $password): self
    {
        $this->attributes['password'] = Hash::make($password);
        return $this;
    }
    
    public function create(): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ], $this->attributes));
    }
    
    public function make(): User
    {
        return new User(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ], $this->attributes));
    }
}

// Utilizzo nei test
public function test_user_can_update_profile()
{
    $user = UserBuilder::new()
        ->withName('John Doe')
        ->withEmail('john@example.com')
        ->create();
    
    $response = $this->actingAs($user)
        ->put('/profile', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);
    
    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com'
    ]);
}
```

## Test Coverage e Quality

### Configurazione PHPUnit per Coverage
```xml
<!-- phpunit.xml -->
<phpunit>
    <coverage>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory>./app/Console</directory>
            <directory>./app/Exceptions</directory>
        </exclude>
        <report>
            <html outputDirectory="build/coverage"/>
            <text outputFile="build/coverage.txt"/>
        </report>
    </coverage>
</phpunit>
```

### Test Coverage Goals
- **Unit Tests**: 80%+ coverage
- **Integration Tests**: 70%+ coverage
- **Feature Tests**: 60%+ coverage

## Best Practices per Testing in Laravel

### 1. Organizzazione dei Test
```
tests/
├── Unit/           # Test unitari
├── Feature/        # Test di integrazione
├── Browser/        # Test E2E con Dusk
├── Support/        # Helper e builders
└── TestCase.php    # Base test case
```

### 2. Naming Convention
```php
// Unit tests
test('user service can create user with valid data')

// Feature tests  
test('user can register with valid data')

// Browser tests
test('user can complete registration flow')
```

### 3. Test Isolation
```php
// Usa RefreshDatabase per test di integrazione
use RefreshDatabase;

// Usa DatabaseTransactions per test più veloci
use DatabaseTransactions;
```

### 4. Assertions Specifiche
```php
// Invece di assertTrue
$this->assertTrue($user->isActive());

// Usa assertion specifiche
$this->assertTrue($user->is_active);
$this->assertDatabaseHas('users', ['is_active' => true]);
```

## Integrazione con CI/CD

### GitHub Actions Example
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        
    - name: Install dependencies
      run: composer install -n --prefer-dist
      
    - name: Generate key
      run: php artisan key:generate
      
    - name: Run tests
      run: php artisan test --coverage
      
    - name: Upload coverage
      uses: codecov/codecov-action@v1
```

---

*TDD e BDD sono metodologie potenti per sviluppare codice robusto e affidabile. In Laravel, l'ecosistema di testing è maturo e ben integrato, rendendo facile l'applicazione di queste pratiche.*
