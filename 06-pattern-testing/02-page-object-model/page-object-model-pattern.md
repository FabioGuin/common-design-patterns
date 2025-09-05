# Page Object Model Pattern

## Scopo

Il pattern Page Object Model (POM) fornisce un'astrazione per le pagine web nei test, incapsulando elementi, azioni e comportamenti di una pagina in una classe dedicata, migliorando la manutenibilità e la leggibilità dei test.

## Come Funziona

Il Page Object Model crea una classe per ogni pagina o componente web, contenente:
- **Elementi**: Selettori per elementi DOM
- **Azioni**: Metodi per interagire con gli elementi
- **Verifiche**: Metodi per validare lo stato della pagina
- **Navigazione**: Metodi per spostarsi tra le pagine

## Quando Usarlo

- Test end-to-end complessi con molte pagine
- Quando gli elementi della pagina cambiano frequentemente
- Per test che coinvolgono più pagine interconnesse
- Quando si vuole riutilizzare la logica di interazione
- Per migliorare la leggibilità dei test

## Quando Evitarlo

- Per test semplici con una sola pagina
- Quando la struttura della pagina è molto stabile
- Per test unitari che non coinvolgono l'interfaccia
- Quando l'overhead del pattern supera i benefici

## Vantaggi

- **Manutenibilità**: Cambiamenti centralizzati in una classe
- **Riusabilità**: Logica condivisa tra test multipli
- **Leggibilità**: Test più chiari e comprensibili
- **Separazione**: Separazione tra test logic e page logic
- **Scalabilità**: Facile aggiunta di nuove pagine

## Svantaggi

- **Complessità**: Setup iniziale più complesso
- **Over-engineering**: Può essere eccessivo per test semplici
- **Accoppiamento**: Test dipendenti dalla struttura delle classi
- **Manutenzione**: Richiede aggiornamenti quando cambia l'UI

## Schema Visivo

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Test Case     │───▶│  Page Object    │───▶│   Web Page      │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Test Logic     │    │  Page Elements  │    │  DOM Elements   │
│  & Assertions   │    │  & Actions      │    │  & Interactions │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Esempi nel Mondo Reale

- **E-commerce**: LoginPage, ProductPage, CartPage, CheckoutPage
- **Social Media**: FeedPage, ProfilePage, SettingsPage
- **Sistema CRM**: DashboardPage, ContactPage, DealPage
- **Applicazioni Web**: HomePage, SearchPage, ResultsPage
- **Dashboard**: AdminPage, ReportsPage, UserManagementPage

## Anti-Pattern

```php
// ❌ Test con logica di pagina inline
public function test_user_login()
{
    $this->visit('/login');
    $this->type('john@example.com', 'email');
    $this->type('password123', 'password');
    $this->press('Login');
    $this->see('Welcome, John!');
}

// ✅ Test con Page Object Model
public function test_user_login()
{
    $loginPage = new LoginPage($this);
    $loginPage->visit()
              ->fillCredentials('john@example.com', 'password123')
              ->submit();
    
    $this->assertTrue($loginPage->isLoggedIn());
    $this->see('Welcome, John!');
}
```

## Troubleshooting

### Problema: Page Object troppo complesso
**Soluzione**: Suddividi in Page Objects più piccoli o usa il pattern Component Object Model.

### Problema: Test lenti a causa di Page Objects
**Soluzione**: Usa lazy loading per gli elementi e cache per le operazioni costose.

### Problema: Page Objects difficili da mantenere
**Soluzione**: Usa factory pattern per creare Page Objects e centralizza i selettori.

## Performance

- **Velocità**: Può essere più lento per test semplici
- **Memoria**: Maggiore consumo per oggetti complessi
- **Scalabilità**: Ottimo per test complessi e riutilizzabili
- **Manutenzione**: Richiede aggiornamenti quando cambia l'UI

## Pattern Correlati

- **Factory Pattern**: Per creare Page Objects
- **Builder Pattern**: Per costruire Page Objects complessi
- **Component Object Model**: Per componenti riutilizzabili
- **Facade Pattern**: Per semplificare l'accesso alle pagine
- **Strategy Pattern**: Per diverse implementazioni di Page Objects

## Risorse

- [Laravel Dusk Documentation](https://laravel.com/docs/dusk)
- [Page Object Model Pattern](https://martinfowler.com/bliki/PageObject.html)
- [Selenium WebDriver](https://selenium-python.readthedocs.io/page-objects.html)
- [Test Automation Best Practices](https://testautomationu.applitools.com/)
- [Web Testing Patterns](https://www.selenium.dev/documentation/test_practices/encouraged/page_object_models/)
