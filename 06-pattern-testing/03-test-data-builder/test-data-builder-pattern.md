# Test Data Builder Pattern

## Scopo

Il pattern Test Data Builder fornisce un modo flessibile e leggibile per creare oggetti di test complessi, permettendo di specificare solo i valori necessari e fornendo valori di default sensati per gli altri attributi.

## Come Funziona

Il Test Data Builder crea oggetti di test attraverso un'interfaccia fluente che permette di:
- **Specificare valori**: Solo gli attributi necessari per il test
- **Valori di default**: Valori sensati per attributi non specificati
- **Combinazioni**: Creare varianti dell'oggetto facilmente
- **Riusabilità**: Builder riutilizzabili per diversi scenari

## Quando Usarlo

- Creazione di oggetti di test complessi
- Quando si hanno molti attributi opzionali
- Per test che richiedono varianti dello stesso oggetto
- Quando si vuole migliorare la leggibilità dei test
- Per evitare duplicazione di codice nei test

## Quando Evitarlo

- Per oggetti semplici con pochi attributi
- Quando i valori di default non sono chiari
- Per test che richiedono solo un singolo scenario
- Quando l'overhead del pattern supera i benefici

## Vantaggi

- **Leggibilità**: Test più chiari e comprensibili
- **Manutenibilità**: Cambiamenti centralizzati nel builder
- **Flessibilità**: Facile creazione di varianti
- **Riusabilità**: Builder condivisi tra test multipli
- **Default sensati**: Valori predefiniti appropriati

## Svantaggi

- **Complessità**: Setup iniziale più complesso
- **Over-engineering**: Può essere eccessivo per oggetti semplici
- **Manutenzione**: Richiede aggiornamenti quando cambia la struttura
- **Accoppiamento**: Test dipendenti dalla struttura del builder

## Schema Visivo

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Test Case     │───▶│  Data Builder   │───▶│  Test Object    │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Test Logic     │    │  Fluent API     │    │  Complex Object │
│  & Assertions   │    │  & Defaults     │    │  with Data      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Esempi nel Mondo Reale

- **E-commerce**: UserBuilder, ProductBuilder, OrderBuilder
- **Social Media**: PostBuilder, CommentBuilder, UserProfileBuilder
- **Sistema CRM**: ContactBuilder, DealBuilder, CompanyBuilder
- **Applicazioni Web**: FormBuilder, RequestBuilder, ResponseBuilder
- **Database**: QueryBuilder, MigrationBuilder, SeederBuilder

## Anti-Pattern

```php
// ❌ Creazione diretta con molti parametri
public function test_user_registration()
{
    $user = new User([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'phone' => '+393401234567',
        'address' => 'Via Roma 123',
        'city' => 'Milano',
        'postal_code' => '20100',
        'country' => 'IT',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'newsletter' => true,
        'terms_accepted' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $this->assertTrue($user->save());
}

// ✅ Uso del Test Data Builder
public function test_user_registration()
{
    $user = UserBuilder::new()
        ->withName('John Doe')
        ->withEmail('john@example.com')
        ->withPassword('password123')
        ->withAddress('Via Roma 123', 'Milano', '20100', 'IT')
        ->withNewsletter(true)
        ->build();
    
    $this->assertTrue($user->save());
}
```

## Troubleshooting

### Problema: Builder troppo complesso
**Soluzione**: Suddividi in builder più piccoli o usa il pattern Builder con Director.

### Problema: Valori di default non appropriati
**Soluzione**: Usa factory pattern o faker per generare valori realistici.

### Problema: Builder difficili da mantenere
**Soluzione**: Usa trait o mixin per condividere logica comune tra builder.

## Performance

- **Velocità**: Leggero overhead per la creazione di oggetti
- **Memoria**: Basso consumo per builder semplici
- **Scalabilità**: Ottimo per test complessi e riutilizzabili
- **Manutenzione**: Richiede aggiornamenti quando cambia la struttura

## Pattern Correlati

- **Builder Pattern**: Base per la creazione di oggetti complessi
- **Factory Pattern**: Per creare builder o oggetti semplici
- **Fluent Interface**: Per API leggibili e fluenti
- **Object Mother**: Per creare oggetti di test predefiniti
- **Test Fixture**: Per dati di test riutilizzabili

## Risorse

- [Test Data Builders](https://www.jamesshore.com/v2/blog/2011/testing-without-mocks)
- [Builder Pattern in Testing](https://refactoring.guru/design-patterns/builder)
- [Laravel Factories](https://laravel.com/docs/eloquent-factories)
- [Test Data Management](https://martinfowler.com/articles/nonDeterminism.html)
- [Fluent Interface Design](https://martinfowler.com/bliki/FluentInterface.html)
