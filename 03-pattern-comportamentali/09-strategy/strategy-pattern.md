# Strategy Pattern

## Cosa fa

Il Strategy Pattern definisce una famiglia di algoritmi, li incapsula e li rende intercambiabili. Strategy permette all'algoritmo di variare indipendentemente dai client che lo utilizzano. È come avere un "cassetto degli attrezzi" dove puoi scegliere lo strumento giusto per ogni lavoro.

## Perché ti serve

Immagina di avere un sistema di pagamento che deve supportare diversi metodi: carta di credito, PayPal, bonifico, criptovalute. Senza il Strategy Pattern, avresti un sacco di if/else. Con il Strategy:

- **Ogni algoritmo** è una classe separata
- **Puoi cambiare** algoritmo a runtime
- **Facile aggiungere** nuovi algoritmi
- **Codice** più pulito e testabile

## Come funziona

Il pattern ha tre componenti principali:

1. **Strategy (Interfaccia)**: Definisce l'interfaccia per tutti gli algoritmi
2. **ConcreteStrategy**: Implementazioni specifiche degli algoritmi
3. **Context**: Usa una Strategy per eseguire l'algoritmo

## Schema visivo

```
Context → Strategy → ConcreteStrategyA
    ↓              ConcreteStrategyB
    ↓              ConcreteStrategyC
```

## Quando usarlo

- **Algoritmi** intercambiabili
- **Diverse implementazioni** della stessa funzionalità
- **Configurazione** di comportamenti
- **Plugin systems**
- **Validation rules**
- **Sorting algorithms**

## Pro e contro

### Pro
- **Interchangeable**: Algoritmi facilmente intercambiabili
- **Open/Closed**: Facile aggiungere nuovi algoritmi
- **Single responsibility**: Ogni strategy ha una responsabilità
- **Testability**: Ogni strategy può essere testata separatamente

### Contro
- **More classes**: Più classi da gestire
- **Client awareness**: Il client deve conoscere le strategy
- **Communication**: Difficile comunicare tra strategy
- **Overhead**: Può essere eccessivo per algoritmi semplici

## Esempi di codice

### Interfaccia Strategy
```pseudocodice
interface PaymentStrategyInterface {
    function pay(amount: number): PaymentResult
    function getStrategyName(): string
}
```

### Strategy concrete
```pseudocodice
class CreditCardStrategy implements PaymentStrategyInterface {
    private cardNumber: string
    private cvv: string
    
    constructor(cardNumber: string, cvv: string) {
        this.cardNumber = cardNumber
        this.cvv = cvv
    }
    
    function pay(amount: number): PaymentResult {
        // Simula pagamento con carta di credito
        echo "Processing credit card payment of €" + amount
        
        // Logica specifica per carta di credito
        success = this.validateCard() && this.processPayment(amount)
        
        return new PaymentResult(success, success ? 'Payment successful' : 'Payment failed')
    }
    
    function getStrategyName(): string {
        return 'credit_card'
    }
    
    private function validateCard(): Boolean {
        return this.cardNumber.length === 16 && this.cvv.length === 3
    }
    
    private function processPayment(amount: number): Boolean {
        // Simula chiamata API
        return rand(0, 1) === 1
    }
}

class PayPalStrategy implements PaymentStrategyInterface {
    private email: string
    private password: string
    
    constructor(email: string, password: string) {
        this.email = email
        this.password = password
    }
    
    function pay(amount: number): PaymentResult {
        // Simula pagamento con PayPal
        echo "Processing PayPal payment of €" + amount
        
        success = this.authenticate() && this.processPayment(amount)
        
        return new PaymentResult(success, success ? 'Payment successful' : 'Payment failed')
    }
    
    function getStrategyName(): string {
        return 'paypal'
    }
    
    private function authenticate(): Boolean {
        return !empty(this.email) && !empty(this.password)
    }
    
    private function processPayment(amount: number): Boolean {
        // Simula chiamata API PayPal
        return rand(0, 1) === 1
    }
}
```

### Context
```pseudocodice
class PaymentProcessor {
    private strategy: PaymentStrategyInterface
    
    function setStrategy(strategy: PaymentStrategyInterface): void {
        this.strategy = strategy
    }
    
    function processPayment(amount: number): PaymentResult {
        if (!isset(this.strategy)) {
            throw new NoStrategySelectedException('No payment strategy selected')
        }
        
        return this.strategy.pay(amount)
    }
    
    function getCurrentStrategy(): string {
        return this.strategy ? this.strategy.getStrategyName() : 'none'
    }
}
```

### Uso
```pseudocodice
processor = new PaymentProcessor()

// Usa carta di credito
processor.setStrategy(new CreditCardStrategy('1234567890123456', '123'))
result = processor.processPayment(100.00)

// Cambia a PayPal
processor.setStrategy(new PayPalStrategy('user@example.com', 'password'))
result = processor.processPayment(50.00)
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di pagamento multi-strategy
- Algoritmi di ordinamento
- Sistema di notifiche
- Validatori configurabili

## Correlati

- **Factory Pattern**: Per creare strategy
- **Template Method Pattern**: Per algoritmi simili
- **State Pattern**: Per comportamenti che cambiano

## Esempi di uso reale

- **Laravel Validation**: Regole di validazione intercambiabili
- **Laravel Mail**: Driver di posta diversi
- **Laravel Cache**: Driver di cache diversi
- **Payment gateways**: Diversi provider di pagamento
- **Sorting algorithms**: Diversi algoritmi di ordinamento
- **Compression**: Diversi algoritmi di compressione

## Anti-pattern

 **Strategy che fa troppo**: Una strategy che gestisce troppe responsabilità
```pseudocodice
// SBAGLIATO
class GodStrategy implements PaymentStrategyInterface {
    function pay(amount: number): PaymentResult {
        this.validateData()
        this.processPayment()
        this.sendEmail()
        this.updateDatabase()
        this.logActivity()
        // Troppo complesso!
    }
}
```

 **Strategy focalizzata**: Una strategy per una responsabilità specifica
```pseudocodice
// GIUSTO
class CreditCardStrategy implements PaymentStrategyInterface {
    function pay(amount: number): PaymentResult {
        // Solo logica per carta di credito
    }
}
```

## Troubleshooting

**Problema**: Strategy non funziona
**Soluzione**: Verifica che la strategy sia stata impostata correttamente

**Problema**: Impossibile cambiare strategy
**Soluzione**: Controlla che il context supporti il cambio di strategy

**Problema**: Strategy non valida
**Soluzione**: Aggiungi validazione per i parametri della strategy

## Performance e considerazioni

- **Strategy selection**: Considera il costo della selezione della strategy
- **Memory usage**: Ogni strategy è un oggetto
- **Caching**: Per strategy costose da creare
- **Validation**: Per validare i parametri della strategy

## Risorse utili

- [Laravel Validation](https://laravel.com/docs/validation)
- [Laravel Mail](https://laravel.com/docs/mail)
- [Laravel Cache](https://laravel.com/docs/cache)
- [Strategy Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/strategy)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
