# Test Doubles Pattern

## Scopo

Il pattern Test Doubles fornisce un meccanismo per sostituire oggetti reali con versioni controllate durante i test, permettendo di isolare il codice sotto test e verificare comportamenti specifici senza dipendenze esterne.

## Come Funziona

I Test Doubles sono oggetti che simulano il comportamento di oggetti reali in un ambiente controllato. Laravel offre diversi tipi di Test Doubles attraverso PHPUnit e le sue funzionalità integrate.

### Tipi di Test Doubles

1. **Mock** - Oggetti che simulano comportamento e verificano interazioni
2. **Stub** - Oggetti che forniscono risposte predefinite
3. **Fake** - Implementazioni semplificate per servizi esterni
4. **Spy** - Oggetti che registrano le chiamate per verifiche successive

## Quando Usarlo

- Testare unità isolate senza dipendenze esterne
- Simulare servizi esterni (API, database, email)
- Verificare interazioni tra oggetti
- Testare scenari di errore difficili da riprodurre
- Accelerare i test evitando operazioni costose

## Quando Evitarlo

- Quando il comportamento reale è critico per il test
- Per test di integrazione end-to-end
- Quando la complessità del mock supera i benefici
- Per testare la logica di business complessa

## Vantaggi

- **Isolamento**: Test indipendenti da servizi esterni
- **Velocità**: Esecuzione rapida senza I/O
- **Controllo**: Comportamenti prevedibili e controllabili
- **Affidabilità**: Test deterministici e riproducibili
- **Debugging**: Facile identificazione dei problemi

## Svantaggi

- **Complessità**: Setup iniziale più complesso
- **Manutenzione**: Mock da aggiornare con i cambiamenti
- **Falsi positivi**: Test che passano ma il codice reale fallisce
- **Over-mocking**: Test troppo accoppiati all'implementazione

## Schema Visivo

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Test Case     │───▶│  Test Double    │───▶│  Real Object    │
│                 │    │   (Mock/Stub)   │    │   (Production)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Verifica       │    │  Comportamento  │    │  Comportamento  │
│  Risultati      │    │  Simulato       │    │  Reale          │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Esempi nel Mondo Reale

- **E-commerce**: Mock di gateway di pagamento per testare flussi di checkout
- **Social Media**: Stub di API per testare funzionalità di condivisione
- **Sistema Email**: Fake per testare invio email senza spam
- **Database**: Mock di repository per testare logica di business
- **Cache**: Stub di servizi di cache per testare performance

## Anti-Pattern

```php
//  Test troppo accoppiato all'implementazione
public function test_user_creation()
{
    $mock = $this->createMock(UserRepository::class);
    $mock->expects($this->exactly(1))
         ->method('create')
         ->with($this->callback(function($user) {
             return $user->name === 'John' && $user->email === 'john@example.com';
         }));
    
    $service = new UserService($mock);
    $service->createUser('John', 'john@example.com');
}

//  Test focalizzato sul comportamento
public function test_user_creation()
{
    $mock = $this->createMock(UserRepository::class);
    $mock->expects($this->once())
         ->method('create')
         ->willReturn(new User(['name' => 'John', 'email' => 'john@example.com']));
    
    $service = new UserService($mock);
    $result = $service->createUser('John', 'john@example.com');
    
    $this->assertInstanceOf(User::class, $result);
    $this->assertEquals('John', $result->name);
}
```

## Troubleshooting

### Problema: Mock non funziona come previsto
**Soluzione**: Verifica che il mock sia configurato correttamente e che i metodi siano chiamati con i parametri giusti.

### Problema: Test fallisce dopo refactoring
**Soluzione**: Usa mock più generici e verifica comportamenti invece di implementazioni specifiche.

### Problema: Test lento nonostante i mock
**Soluzione**: Verifica che non ci siano chiamate a servizi reali e usa fake invece di mock quando possibile.

## Performance

- **Velocità**: I Test Doubles sono molto veloci (nessun I/O)
- **Memoria**: Basso consumo di memoria
- **Scalabilità**: Facilmente scalabili per test complessi
- **Manutenzione**: Richiedono aggiornamenti quando cambia l'interfaccia

## Pattern Correlati

- **Dependency Injection**: Facilita l'uso di Test Doubles
- **Service Container**: Gestisce l'iniezione delle dipendenze
- **Factory Pattern**: Crea Test Doubles in modo consistente
- **Builder Pattern**: Costruisce Test Doubles complessi
- **Strategy Pattern**: Permette di sostituire implementazioni

## Risorse

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Mock Objects](https://phpunit.readthedocs.io/en/9.5/test-doubles.html)
- [Laravel Fake Services](https://laravel.com/docs/facades#testing)
- [Test-Driven Development](https://en.wikipedia.org/wiki/Test-driven_development)
- [Mock Objects Best Practices](https://martinfowler.com/articles/mocksArentStubs.html)
