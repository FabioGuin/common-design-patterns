# TDD - Test-Driven Development

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

Test-Driven Development (TDD) è una metodologia di sviluppo software che inverte il ciclo tradizionale di programmazione. Invece di scrivere codice e poi testarlo, TDD prevede di:

1. **Red**: Scrivere un test che fallisce
2. **Green**: Scrivere il codice minimo per far passare il test
3. **Refactor**: Migliorare il codice mantenendo i test verdi

TDD garantisce che ogni funzionalità sia testata fin dall'inizio e che il codice sia progettato per essere testabile.

## Perché ti serve

Senza TDD, lo sviluppo tradizionale causa:
- **Test dopo**: Spesso i test vengono scritti dopo o mai
- **Codice difficile da testare**: Progettato senza considerare i test
- **Bug in produzione**: Errori scoperti troppo tardi
- **Refactoring rischioso**: Paura di rompere il codice esistente
- **Documentazione mancante**: I test servono anche da documentazione

Con TDD, ottieni:
- **Copertura completa**: Ogni funzionalità è testata
- **Codice testabile**: Progettato per essere facilmente testato
- **Meno bug**: Errori catturati durante lo sviluppo
- **Refactoring sicuro**: I test garantiscono che tutto funzioni
- **Documentazione vivente**: I test spiegano come usare il codice

## Come funziona

### Il Ciclo Red-Green-Refactor

**1. Red (Rosso)**
- Scrivi un test per una funzionalità che non esiste ancora
- Il test deve fallire (stato rosso)
- Definisci il comportamento desiderato

**2. Green (Verde)**
- Scrivi il codice minimo per far passare il test
- Non preoccuparti della qualità del codice
- L'obiettivo è solo far passare il test

**3. Refactor (Refactoring)**
- Migliora il codice mantenendo i test verdi
- Applica principi SOLID e clean code
- Rimuovi duplicazioni e migliora la leggibilità

### Regole TDD

1. **Non scrivere codice di produzione** senza un test che fallisce
2. **Non scrivere più test** del necessario per far fallire il test
3. **Non scrivere più codice** del necessario per far passare il test

## Quando usarlo

Usa TDD quando:
- **Stai sviluppando** nuove funzionalità
- **Il codice è critico** e deve essere affidabile
- **Stai facendo refactoring** di codice esistente
- **Vuoi migliorare** la qualità del codice
- **Il team è pronto** per il cambiamento di approccio

**NON usarlo quando:**
- **Stai facendo prototipi** rapidi
- **Il progetto è molto semplice**
- **Stai esplorando** nuove tecnologie
- **Il team non è formato** su TDD
- **Stai lavorando** con codice legacy complesso

## Pro e contro

**I vantaggi:**
- **Codice più pulito** e ben progettato
- **Meno bug** in produzione
- **Refactoring sicuro** e continuo
- **Documentazione vivente** tramite i test
- **Maggiore fiducia** nel codice
- **Design migliore** (testabile = ben progettato)

**Gli svantaggi:**
- **Curva di apprendimento** iniziale
- **Tempo iniziale** maggiore per scrivere i test
- **Richiede disciplina** costante
- **Può sembrare lento** all'inizio
- **Difficile con codice legacy**

## Esempi di codice

### Esempio 1: Calcolatrice Semplice

```php
// 1. RED - Test che fallisce
class CalculatorTest extends TestCase
{
    public function test_can_add_two_numbers()
    {
        $calculator = new Calculator();
        $result = $calculator->add(2, 3);
        $this->assertEquals(5, $result);
    }
}

// 2. GREEN - Codice minimo per far passare il test
class Calculator
{
    public function add($a, $b)
    {
        return 5; // Codice hardcoded per far passare il test
    }
}

// 3. REFACTOR - Migliorare il codice
class Calculator
{
    public function add($a, $b)
    {
        return $a + $b; // Implementazione corretta
    }
}

// 4. RED - Nuovo test
public function test_can_subtract_two_numbers()
{
    $calculator = new Calculator();
    $result = $calculator->subtract(5, 3);
    $this->assertEquals(2, $result);
}

// 5. GREEN - Implementazione
class Calculator
{
    public function add($a, $b)
    {
        return $a + $b;
    }
    
    public function subtract($a, $b)
    {
        return $a - $b;
    }
}
```

### Esempio 2: User Service con TDD

```php
// 1. RED - Test per creazione utente
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

// 2. GREEN - Implementazione minima
class UserService
{
    public function createUser(array $data)
    {
        return new User($data);
    }
}

// 3. RED - Test per validazione
public function test_throws_exception_when_email_is_invalid()
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

// 4. GREEN - Implementazione con validazione
class UserService
{
    public function createUser(array $data)
    {
        $this->validateUserData($data);
        return new User($data);
    }
    
    private function validateUserData(array $data)
    {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }
    }
}

// 5. RED - Test per password hash
public function test_password_is_hashed_when_creating_user()
{
    $userService = new UserService();
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123'
    ];
    
    $user = $userService->createUser($userData);
    
    $this->assertNotEquals('password123', $user->password);
    $this->assertTrue(Hash::check('password123', $user->password));
}

// 6. GREEN - Implementazione con hash
class UserService
{
    public function createUser(array $data)
    {
        $this->validateUserData($data);
        
        $data['password'] = Hash::make($data['password']);
        
        return User::create($data);
    }
    
    private function validateUserData(array $data)
    {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }
    }
}
```

### Esempio 3: Repository Pattern con TDD

```php
// 1. RED - Test per repository
class UserRepositoryTest extends TestCase
{
    public function test_can_find_user_by_id()
    {
        $user = User::factory()->create();
        $repository = new UserRepository();
        
        $foundUser = $repository->findById($user->id);
        
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }
    
    public function test_returns_null_when_user_not_found()
    {
        $repository = new UserRepository();
        
        $user = $repository->findById(999);
        
        $this->assertNull($user);
    }
}

// 2. GREEN - Implementazione minima
class UserRepository
{
    public function findById($id)
    {
        return User::find($id);
    }
}

// 3. RED - Test per creazione
public function test_can_create_user()
{
    $repository = new UserRepository();
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123'
    ];
    
    $user = $repository->create($userData);
    
    $this->assertInstanceOf(User::class, $user);
    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
}

// 4. GREEN - Implementazione completa
class UserRepository
{
    public function findById($id)
    {
        return User::find($id);
    }
    
    public function create(array $data)
    {
        return User::create($data);
    }
}
```

### Esempio 4: Controller con TDD

```php
// 1. RED - Test per controller
class UserControllerTest extends TestCase
{
    public function test_can_store_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $response = $this->postJson('/api/users', $userData);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at'
        ]);
    }
    
    public function test_returns_validation_error_for_invalid_data()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ];
        
        $response = $this->postJson('/api/users', $userData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}

// 2. GREEN - Implementazione controller
class UserController extends Controller
{
    public function store(UserRequest $request, UserService $userService)
    {
        $user = $userService->createUser($request->validated());
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at
        ], 201);
    }
}
```

## Principi/Metodologie correlate

- **BDD**: Behavior-Driven Development, evoluzione di TDD
- **ATDD**: Acceptance Test-Driven Development
- **Clean Code**: TDD produce codice più pulito
- **SOLID Principles**: TDD favorisce l'applicazione di SOLID
- **Refactoring**: Parte integrante del ciclo TDD
- **Unit Testing**: Base per TDD

## Risorse utili

### Documentazione ufficiale
- [Laravel Testing](https://laravel.com/docs/testing) - Testing in Laravel
- [PHPUnit](https://phpunit.de/) - Framework di testing per PHP
- [Test-Driven Development](https://en.wikipedia.org/wiki/Test-driven_development) - Principi TDD

### Laravel specifico
- [Laravel Testing Best Practices](https://laravel.com/docs/testing#testing-best-practices) - Best practices
- [Laravel Factories](https://laravel.com/docs/eloquent-factories) - Creazione dati di test
- [Laravel Database Testing](https://laravel.com/docs/database-testing) - Test del database

### Esempi e tutorial
- [Laravel Testing Examples](https://github.com/laravel/laravel/tree/master/tests) - Esempi ufficiali
- [TDD with Laravel](https://laracasts.com/series/phpunit-testing-in-laravel) - Tutorial Laracasts
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Robert Martin

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
