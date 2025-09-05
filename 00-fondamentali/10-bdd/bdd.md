# BDD - Behavior-Driven Development

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

Behavior-Driven Development (BDD) è una metodologia di sviluppo software che si concentra sul comportamento del sistema dal punto di vista dell'utente. BDD estende TDD utilizzando un linguaggio naturale e comprensibile per descrivere le funzionalità.

BDD utilizza il formato **Given-When-Then** per descrivere scenari:
- **Given**: Lo stato iniziale del sistema
- **When**: L'azione che viene eseguita
- **Then**: Il risultato atteso

## Perché ti serve

Senza BDD, lo sviluppo tradizionale causa:
- **Disconnessione** tra business e sviluppo
- **Test tecnici** difficili da capire per i non-tecnici
- **Requisiti ambigui** e mal interpretati
- **Documentazione** separata dal codice
- **Comunicazione** inefficace tra team

Con BDD, ottieni:
- **Comunicazione chiara** tra business e sviluppo
- **Test leggibili** da tutti i membri del team
- **Requisiti chiari** e non ambigui
- **Documentazione vivente** sempre aggiornata
- **Allineamento** tra aspettative e implementazione

## Come funziona

### Struttura BDD

**1. Feature (Funzionalità)**
- Descrizione della funzionalità dal punto di vista del business
- Scritta in linguaggio naturale
- Comprensibile da tutti i stakeholder

**2. Scenario (Scenario)**
- Esempio specifico di come la funzionalità dovrebbe comportarsi
- Utilizza il formato Given-When-Then
- Descrive un caso d'uso concreto

**3. Step Definitions (Definizioni dei Passi)**
- Implementazione tecnica dei passi
- Collega il linguaggio naturale al codice
- Esegue le azioni descritte negli scenari

### Flusso BDD

1. **Conversazione**: Business e sviluppo discutono la funzionalità
2. **Scrittura**: Si scrivono scenari in linguaggio naturale
3. **Implementazione**: Si implementano i step definitions
4. **Esecuzione**: Si eseguono i test per verificare il comportamento
5. **Refactoring**: Si migliora il codice mantenendo i test verdi

## Quando usarlo

Usa BDD quando:
- **Il team include** membri non tecnici
- **I requisiti sono complessi** e ambigui
- **Vuoi migliorare** la comunicazione
- **Stai sviluppando** funzionalità business-critical
- **Hai bisogno** di documentazione vivente

**NON usarlo quando:**
- **Il team è solo tecnico** e preferisce TDD
- **I requisiti sono semplici** e chiari
- **Stai facendo prototipi** rapidi
- **Il team non è formato** su BDD
- **Stai lavorando** con codice legacy

## Pro e contro

**I vantaggi:**
- **Comunicazione migliorata** tra team
- **Requisiti chiari** e non ambigui
- **Documentazione vivente** sempre aggiornata
- **Test leggibili** da tutti
- **Allineamento** tra business e sviluppo
- **Meno bug** dovuti a malintesi

**Gli svantaggi:**
- **Curva di apprendimento** per il team
- **Tempo iniziale** per scrivere scenari
- **Può essere verboso** per funzionalità semplici
- **Richiede disciplina** costante
- **Difficile con codice legacy**

## Esempi di codice

### Esempio 1: Login Utente

```gherkin
# features/user_login.feature
Feature: User Login
  As a user
  I want to be able to log in
  So that I can access my account

  Scenario: Successful login with valid credentials
    Given I am on the login page
    When I enter "john@example.com" as email
    And I enter "password123" as password
    And I click the login button
    Then I should be redirected to the dashboard
    And I should see "Welcome back, John!"

  Scenario: Failed login with invalid credentials
    Given I am on the login page
    When I enter "invalid@example.com" as email
    And I enter "wrongpassword" as password
    And I click the login button
    Then I should see "Invalid credentials"
    And I should remain on the login page
```

### Implementazione Test

```php
// tests/Feature/UserLoginTest.php
class UserLoginTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_successful_login_with_valid_credentials()
    {
        // Given I am on the login page
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);
        
        // When I enter "john@example.com" as email
        // And I enter "password123" as password
        // And I click the login button
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
        
        // Then I should be redirected to the dashboard
        $response->assertRedirect('/dashboard');
        
        // And I should see "Welcome back, John!"
        $this->get('/dashboard')
            ->assertSee('Welcome back, John!');
    }
    
    public function test_failed_login_with_invalid_credentials()
    {
        // Given I am on the login page
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);
        
        // When I enter "invalid@example.com" as email
        // And I enter "wrongpassword" as password
        // And I click the login button
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword'
        ]);
        
        // Then I should see "Invalid credentials"
        $response->assertSessionHasErrors(['email']);
        
        // And I should remain on the login page
        $response->assertRedirect('/login');
    }
}
```

### Esempio 2: Creazione Ordine

```gherkin
# features/order_creation.feature
Feature: Order Creation
  As a customer
  I want to create an order
  So that I can purchase products

  Scenario: Create order with valid items
    Given I am logged in as "john@example.com"
    And I have the following products in my cart:
      | Product | Price | Quantity |
      | Laptop  | 1000  | 1        |
      | Mouse   | 25    | 2        |
    When I proceed to checkout
    And I enter my shipping address
    And I select "credit_card" as payment method
    And I confirm the order
    Then I should see "Order created successfully"
    And I should receive an order confirmation email
    And the order should be saved in the database

  Scenario: Cannot create order with empty cart
    Given I am logged in as "john@example.com"
    And my cart is empty
    When I try to proceed to checkout
    Then I should see "Your cart is empty"
    And I should not be able to create an order
```

```php
// tests/Feature/OrderCreationTest.php
class OrderCreationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_create_order_with_valid_items()
    {
        // Given I am logged in as "john@example.com"
        $user = User::factory()->create(['email' => 'john@example.com']);
        $this->actingAs($user);
        
        // And I have the following products in my cart
        $laptop = Product::factory()->create(['name' => 'Laptop', 'price' => 1000]);
        $mouse = Product::factory()->create(['name' => 'Mouse', 'price' => 25]);
        
        $cart = [
            ['product_id' => $laptop->id, 'quantity' => 1],
            ['product_id' => $mouse->id, 'quantity' => 2]
        ];
        
        // When I proceed to checkout
        // And I enter my shipping address
        // And I select "credit_card" as payment method
        // And I confirm the order
        $response = $this->post('/orders', [
            'items' => $cart,
            'shipping_address' => '123 Main St, City, Country',
            'payment_method' => 'credit_card'
        ]);
        
        // Then I should see "Order created successfully"
        $response->assertStatus(201)
            ->assertJson(['message' => 'Order created successfully']);
        
        // And I should receive an order confirmation email
        Mail::assertSent(OrderConfirmationMail::class);
        
        // And the order should be saved in the database
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
    }
    
    public function test_cannot_create_order_with_empty_cart()
    {
        // Given I am logged in as "john@example.com"
        $user = User::factory()->create(['email' => 'john@example.com']);
        $this->actingAs($user);
        
        // And my cart is empty
        // When I try to proceed to checkout
        $response = $this->post('/orders', [
            'items' => [],
            'shipping_address' => '123 Main St, City, Country',
            'payment_method' => 'credit_card'
        ]);
        
        // Then I should see "Your cart is empty"
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
        
        // And I should not be able to create an order
        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id
        ]);
    }
}
```

### Esempio 3: Gestione Utenti Admin

```gherkin
# features/admin_user_management.feature
Feature: Admin User Management
  As an admin
  I want to manage users
  So that I can control access to the system

  Scenario: Admin can view all users
    Given I am logged in as an admin
    And there are 3 users in the system
    When I visit the users management page
    Then I should see all 3 users
    And I should see their email addresses
    And I should see their registration dates

  Scenario: Admin can delete a user
    Given I am logged in as an admin
    And there is a user with email "test@example.com"
    When I delete the user "test@example.com"
    Then the user should be removed from the system
    And I should see "User deleted successfully"
    And the user should not be able to log in anymore
```

```php
// tests/Feature/AdminUserManagementTest.php
class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_admin_can_view_all_users()
    {
        // Given I am logged in as an admin
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        // And there are 3 users in the system
        User::factory()->count(3)->create();
        
        // When I visit the users management page
        $response = $this->get('/admin/users');
        
        // Then I should see all 3 users
        $response->assertStatus(200);
        
        // And I should see their email addresses
        $response->assertSee('Email');
        
        // And I should see their registration dates
        $response->assertSee('Created At');
    }
    
    public function test_admin_can_delete_a_user()
    {
        // Given I am logged in as an admin
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        // And there is a user with email "test@example.com"
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        // When I delete the user "test@example.com"
        $response = $this->delete("/admin/users/{$user->id}");
        
        // Then the user should be removed from the system
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        
        // And I should see "User deleted successfully"
        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);
        
        // And the user should not be able to log in anymore
        $loginResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
        $loginResponse->assertSessionHasErrors(['email']);
    }
}
```

### Esempio 4: API Endpoints

```gherkin
# features/api_user_endpoints.feature
Feature: User API Endpoints
  As a developer
  I want to access user data via API
  So that I can integrate with other systems

  Scenario: Get user profile via API
    Given I have a valid API token
    And there is a user with ID 1
    When I make a GET request to "/api/users/1"
    Then I should receive a 200 status code
    And the response should contain user data
    And the response should not contain sensitive information

  Scenario: Create user via API
    Given I have a valid API token
    When I make a POST request to "/api/users" with:
      """
      {
        "name": "John Doe",
        "email": "john@example.com",
        "password": "password123"
      }
      """
    Then I should receive a 201 status code
    And the user should be created in the database
    And the response should contain the new user ID
```

```php
// tests/Feature/ApiUserEndpointsTest.php
class ApiUserEndpointsTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_get_user_profile_via_api()
    {
        // Given I have a valid API token
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // And there is a user with ID 1
        $targetUser = User::factory()->create(['id' => 1]);
        
        // When I make a GET request to "/api/users/1"
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->get('/api/users/1');
        
        // Then I should receive a 200 status code
        $response->assertStatus(200);
        
        // And the response should contain user data
        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at'
        ]);
        
        // And the response should not contain sensitive information
        $response->assertJsonMissing(['password']);
    }
    
    public function test_create_user_via_api()
    {
        // Given I have a valid API token
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // When I make a POST request to "/api/users"
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->post('/api/users', $userData);
        
        // Then I should receive a 201 status code
        $response->assertStatus(201);
        
        // And the user should be created in the database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        // And the response should contain the new user ID
        $response->assertJsonStructure(['id']);
    }
}
```

## Correlati

### Pattern

- **[TDD](./09-tdd/tdd.md)** - Base per BDD, BDD estende TDD
- **[ATDD](./11-atdd/atdd.md)** - Acceptance Test-Driven Development
- **[Clean Code](./05-clean-code/clean-code.md)** - BDD produce codice più pulito

### Principi e Metodologie

- **[Behavior-Driven Development](https://en.wikipedia.org/wiki/Behavior-driven_development)** - Metodologia originale BDD
- **[Given-When-Then](https://en.wikipedia.org/wiki/Given-When-Then)** - Formato per scenari BDD
- **[Domain-Specific Language](https://en.wikipedia.org/wiki/Domain-specific_language)** - Linguaggio specifico del dominio


## Risorse utili

### Documentazione ufficiale
- [Laravel Testing](https://laravel.com/docs/testing) - Testing in Laravel
- [Behat](https://docs.behat.org/) - Framework BDD per PHP
- [Gherkin](https://cucumber.io/docs/gherkin/) - Linguaggio per scenari BDD

### Laravel specifico
- [Laravel Dusk](https://laravel.com/docs/dusk) - Testing browser per Laravel
- [Laravel HTTP Tests](https://laravel.com/docs/http-tests) - Test HTTP in Laravel
- [Laravel Database Testing](https://laravel.com/docs/database-testing) - Test del database

### Esempi e tutorial
- [BDD with Laravel](https://laracasts.com/series/phpunit-testing-in-laravel) - Tutorial Laracasts
- [Behat with Laravel](https://github.com/laracasts/Behat-Laravel-Extension) - Estensione Behat per Laravel
- [Cucumber](https://cucumber.io/) - Framework BDD

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
