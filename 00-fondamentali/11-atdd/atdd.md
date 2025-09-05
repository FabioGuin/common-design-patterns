# ATDD - Acceptance Test-Driven Development

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

Acceptance Test-Driven Development (ATDD) è una metodologia di sviluppo software che combina TDD con test di accettazione. ATDD si concentra su test che verificano se il software soddisfa i requisiti di business e le aspettative degli stakeholder.

ATDD utilizza test di accettazione per guidare lo sviluppo, garantendo che ogni funzionalità sia implementata secondo le specifiche del business.

## Perché ti serve

Senza ATDD, lo sviluppo tradizionale causa:
- **Disconnessione** tra requisiti e implementazione
- **Test insufficienti** per verificare l'accettazione
- **Rework costoso** quando i requisiti non sono soddisfatti
- **Comunicazione inefficace** tra team e stakeholder
- **Qualità inconsistente** del software

Con ATDD, ottieni:
- **Allineamento** tra requisiti e implementazione
- **Test completi** per verificare l'accettazione
- **Riduzione del rework** grazie a feedback precoce
- **Comunicazione efficace** tra team e stakeholder
- **Qualità consistente** del software

## Come funziona

### Flusso ATDD

**1. Discussione (Discuss)**
- Team e stakeholder discutono i requisiti
- Si identificano i criteri di accettazione
- Si definiscono i test di accettazione

**2. Sviluppo (Develop)**
- Si scrivono i test di accettazione
- Si implementa il codice per far passare i test
- Si verifica che i test passino

**3. Demo (Demonstrate)**
- Si dimostra la funzionalità agli stakeholder
- Si verifica che soddisfi i requisiti
- Si raccoglie feedback per miglioramenti

### Tipi di Test ATDD

**1. Test di Accettazione Funzionale**
- Verificano che le funzionalità funzionino come richiesto
- Testano il comportamento end-to-end
- Verificano i criteri di accettazione

**2. Test di Accettazione Non Funzionale**
- Verificano performance, sicurezza, usabilità
- Testano requisiti di qualità
- Verificano vincoli tecnici

**3. Test di Accettazione di Integrazione**
- Verificano l'integrazione tra componenti
- Testano l'interazione con sistemi esterni
- Verificano il flusso completo

## Quando usarlo

Usa ATDD quando:
- **I requisiti sono complessi** e critici
- **Hai stakeholder** coinvolti nel processo
- **Vuoi ridurre** il rework
- **Stai sviluppando** funzionalità business-critical
- **Hai bisogno** di feedback precoce

**NON usarlo quando:**
- **I requisiti sono semplici** e chiari
- **Stai facendo prototipi** rapidi
- **Il team non è formato** su ATDD
- **Stai lavorando** con codice legacy complesso
- **Non hai stakeholder** coinvolti

## Pro e contro

**I vantaggi:**
- **Allineamento** tra requisiti e implementazione
- **Riduzione del rework** grazie a feedback precoce
- **Comunicazione migliorata** tra team e stakeholder
- **Qualità consistente** del software
- **Documentazione vivente** sempre aggiornata
- **Meno bug** in produzione

**Gli svantaggi:**
- **Curva di apprendimento** per il team
- **Tempo iniziale** per scrivere test di accettazione
- **Richiede coinvolgimento** degli stakeholder
- **Può essere complesso** per funzionalità semplici
- **Difficile con requisiti** in evoluzione

## Esempi di codice

### Esempio 1: Sistema di E-commerce

```php
// tests/Acceptance/EcommerceTest.php
class EcommerceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_customer_can_purchase_product()
    {
        // Given: Un cliente registrato e un prodotto disponibile
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create([
            'name' => 'Laptop',
            'price' => 1000,
            'stock' => 5
        ]);
        
        // When: Il cliente aggiunge il prodotto al carrello e procede al checkout
        $this->actingAs($customer);
        
        // Aggiunge prodotto al carrello
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
        
        // Procede al checkout
        $response = $this->post('/checkout', [
            'shipping_address' => '123 Main St, City, Country',
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'expiry_date' => '12/25',
            'cvv' => '123'
        ]);
        
        // Then: L'ordine viene creato con successo
        $response->assertStatus(201);
        
        // Verifica che l'ordine sia stato creato
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => 'pending',
            'total' => 1000
        ]);
        
        // Verifica che il prodotto sia stato aggiunto all'ordine
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000
        ]);
        
        // Verifica che lo stock sia stato ridotto
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 4
        ]);
        
        // Verifica che il cliente riceva una email di conferma
        Mail::assertSent(OrderConfirmationMail::class);
    }
    
    public function test_customer_cannot_purchase_out_of_stock_product()
    {
        // Given: Un cliente registrato e un prodotto esaurito
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create([
            'name' => 'Laptop',
            'price' => 1000,
            'stock' => 0
        ]);
        
        // When: Il cliente cerca di aggiungere il prodotto al carrello
        $this->actingAs($customer);
        
        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
        
        // Then: Viene mostrato un errore
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
        
        // Verifica che nessun ordine sia stato creato
        $this->assertDatabaseMissing('orders', [
            'user_id' => $customer->id
        ]);
    }
}
```

### Esempio 2: Sistema di Gestione Utenti

```php
// tests/Acceptance/UserManagementTest.php
class UserManagementTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_admin_can_manage_users()
    {
        // Given: Un amministratore e alcuni utenti nel sistema
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(3)->create(['role' => 'user']);
        
        $this->actingAs($admin);
        
        // When: L'amministratore accede alla pagina di gestione utenti
        $response = $this->get('/admin/users');
        
        // Then: Può vedere tutti gli utenti
        $response->assertStatus(200);
        $response->assertSee('User Management');
        
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
        
        // When: L'amministratore modifica un utente
        $userToEdit = $users->first();
        $response = $this->put("/admin/users/{$userToEdit->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'moderator'
        ]);
        
        // Then: L'utente viene aggiornato
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $userToEdit->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'moderator'
        ]);
        
        // When: L'amministratore elimina un utente
        $userToDelete = $users->last();
        $response = $this->delete("/admin/users/{$userToDelete->id}");
        
        // Then: L'utente viene eliminato
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id
        ]);
    }
    
    public function test_regular_user_cannot_access_admin_functions()
    {
        // Given: Un utente normale
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        
        // When: L'utente cerca di accedere alle funzioni admin
        $response = $this->get('/admin/users');
        
        // Then: Viene reindirizzato o riceve un errore
        $response->assertStatus(403);
        
        // When: L'utente cerca di eliminare un altro utente
        $otherUser = User::factory()->create();
        $response = $this->delete("/admin/users/{$otherUser->id}");
        
        // Then: L'operazione viene negata
        $response->assertStatus(403);
    }
}
```

### Esempio 3: Sistema di Notifiche

```php
// tests/Acceptance/NotificationSystemTest.php
class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_receives_notification_when_mentioned()
    {
        // Given: Due utenti nel sistema
        $author = User::factory()->create(['name' => 'John Doe']);
        $mentionedUser = User::factory()->create(['name' => 'Jane Smith']);
        
        $this->actingAs($author);
        
        // When: L'autore crea un post menzionando l'altro utente
        $response = $this->post('/posts', [
            'title' => 'Test Post',
            'content' => 'Hey @Jane Smith, check this out!',
            'published' => true
        ]);
        
        // Then: Il post viene creato
        $response->assertStatus(201);
        
        // Verifica che l'utente menzionato riceva una notifica
        $this->assertDatabaseHas('notifications', [
            'user_id' => $mentionedUser->id,
            'type' => 'mention',
            'data' => json_encode([
                'post_id' => $response->json('id'),
                'author' => 'John Doe'
            ])
        ]);
        
        // Verifica che l'utente menzionato riceva una email
        Mail::assertSent(MentionNotificationMail::class, function ($mail) use ($mentionedUser) {
            return $mail->hasTo($mentionedUser->email);
        });
    }
    
    public function test_user_can_mark_notification_as_read()
    {
        // Given: Un utente con una notifica non letta
        $user = User::factory()->create();
        $notification = $user->notifications()->create([
            'type' => 'mention',
            'data' => json_encode(['post_id' => 1, 'author' => 'John Doe']),
            'read_at' => null
        ]);
        
        $this->actingAs($user);
        
        // When: L'utente marca la notifica come letta
        $response = $this->patch("/notifications/{$notification->id}/read");
        
        // Then: La notifica viene marcata come letta
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => now()
        ]);
    }
}
```

### Esempio 4: Sistema di Report

```php
// tests/Acceptance/ReportingSystemTest.php
class ReportingSystemTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_admin_can_generate_sales_report()
    {
        // Given: Un amministratore e alcuni ordini nel sistema
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        // Crea alcuni ordini per il test
        $orders = Order::factory()->count(5)->create([
            'status' => 'completed',
            'total' => 1000,
            'created_at' => now()->subDays(rand(1, 30))
        ]);
        
        // When: L'amministratore genera un report delle vendite
        $response = $this->get('/admin/reports/sales', [
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d')
        ]);
        
        // Then: Il report viene generato con successo
        $response->assertStatus(200);
        $response->assertSee('Sales Report');
        $response->assertSee('Total Sales: $5,000');
        $response->assertSee('Number of Orders: 5');
        
        // Verifica che il report contenga i dati corretti
        $reportData = $response->json();
        $this->assertEquals(5000, $reportData['total_sales']);
        $this->assertEquals(5, $reportData['total_orders']);
    }
    
    public function test_report_can_be_exported_to_csv()
    {
        // Given: Un amministratore e alcuni ordini
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        
        Order::factory()->count(3)->create([
            'status' => 'completed',
            'total' => 500
        ]);
        
        // When: L'amministratore esporta il report in CSV
        $response = $this->get('/admin/reports/sales/export', [
            'format' => 'csv',
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d')
        ]);
        
        // Then: Il file CSV viene generato
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        
        // Verifica che il CSV contenga i dati corretti
        $csvContent = $response->getContent();
        $this->assertStringContains('Order ID,Total,Date', $csvContent);
        $this->assertStringContains('500', $csvContent);
    }
}
```

## Correlati

### Pattern

- **[TDD](./09-tdd/tdd.md)** - Base per ATDD, ATDD estende TDD
- **[BDD](./10-bdd/bdd.md)** - Behavior-Driven Development, complementare ad ATDD
- **[Clean Code](./05-clean-code/clean-code.md)** - ATDD produce codice più pulito

### Principi e Metodologie

- **[Acceptance Test-Driven Development](https://en.wikipedia.org/wiki/Acceptance_test%E2%80%93driven_development)** - Metodologia originale ATDD
- **[User Story](https://en.wikipedia.org/wiki/User_story)** - Storie utente per definire requisiti
- **[Acceptance Criteria](https://en.wikipedia.org/wiki/Acceptance_criteria)** - Criteri di accettazione


## Risorse utili

### Documentazione ufficiale
- [Laravel Testing](https://laravel.com/docs/testing) - Testing in Laravel
- [PHPUnit](https://phpunit.de/) - Framework di testing per PHP
- [Acceptance Testing](https://en.wikipedia.org/wiki/Acceptance_testing) - Principi di testing di accettazione

### Laravel specifico
- [Laravel HTTP Tests](https://laravel.com/docs/http-tests) - Test HTTP in Laravel
- [Laravel Database Testing](https://laravel.com/docs/database-testing) - Test del database
- [Laravel Dusk](https://laravel.com/docs/dusk) - Testing browser per Laravel

### Esempi e tutorial
- [Laravel Testing Examples](https://github.com/laravel/laravel/tree/master/tests) - Esempi ufficiali
- [TDD with Laravel](https://laracasts.com/series/phpunit-testing-in-laravel) - Tutorial Laracasts
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Robert Martin

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
