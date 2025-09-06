# Interpreter Pattern

## Cosa fa

Il Interpreter Pattern definisce una rappresentazione per la grammatica di un linguaggio insieme a un interprete per interpretare le frasi del linguaggio. È come creare un piccolo linguaggio di programmazione personalizzato per risolvere problemi specifici.

## Perché ti serve

Immagina di dover processare espressioni matematiche come "2 + 3 * 4" o query di ricerca come "title:php AND author:john". Invece di scrivere un parser complesso, puoi:

- **Definire** la grammatica del tuo linguaggio
- **Creare** nodi per ogni elemento (numeri, operatori, parole chiave)
- **Interpretare** le espressioni seguendo la struttura ad albero
- **Estendere** facilmente con nuove regole

## Come funziona

Il pattern ha quattro componenti principali:

1. **AbstractExpression**: Interfaccia per interpretare espressioni
2. **TerminalExpression**: Espressioni terminali (numeri, variabili)
3. **NonTerminalExpression**: Espressioni composte (operazioni)
4. **Context**: Contiene informazioni globali per l'interpretazione

## Schema visivo

```
Expression: "2 + 3 * 4"
         +
        / \
       2   *
          / \
         3   4
```

## Quando usarlo

- **Query languages** (SQL, search queries)
- **Mathematical expressions**
- **Configuration languages**
- **Domain-specific languages (DSL)**
- **Template engines**
- **Rule engines**

## Pro e contro

### Pro
- **Flessibilità**: Facile aggiungere nuove regole
- **Chiarezza**: La grammatica è esplicita
- **Riutilizzabilità**: Le espressioni possono essere riutilizzate
- **Testabilità**: Ogni espressione può essere testata separatamente

### Contro
- **Complessità**: Può diventare complesso per grammatiche grandi
- **Performance**: Interpretazione può essere lenta
- **Debugging**: Difficile debuggare espressioni complesse
- **Learning curve**: Richiede conoscenza di parsing

## Esempi di codice

### Pseudocodice
```
// Interfaccia base
interface ExpressionInterface {
    interpret(context) returns mixed
}

// Espressione terminale
class NumberExpression implements ExpressionInterface {
    private value: number
    
    constructor(value: number) {
        this.value = value
    }
    
    interpret(context) returns number {
        return this.value
    }
}

// Espressione non terminale
class AddExpression implements ExpressionInterface {
    private left: ExpressionInterface
    private right: ExpressionInterface
    
    constructor(left: ExpressionInterface, right: ExpressionInterface) {
        this.left = left
        this.right = right
    }
    
    interpret(context) returns number {
        return this.left.interpret(context) + this.right.interpret(context)
    }
}

// Utilizzo
// Crea l'espressione: 2 + 3
expression = new AddExpression(
    new NumberExpression(2),
    new NumberExpression(3)
)

context = new Context()
result = expression.interpret(context) // 5
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Interprete per espressioni matematiche
- Query builder per ricerche
- Template engine personalizzato
- Sistema di regole configurabile

## Correlati

- **Composite Pattern**: Per costruire alberi di espressioni
- **Strategy Pattern**: Per diversi tipi di interpretazione
- **Visitor Pattern**: Per operazioni sulle espressioni

## Esempi di uso reale

- **Laravel Query Builder**: Interpreta query SQL
- **Blade Templates**: Interpreta template
- **Laravel Validation Rules**: Interpreta regole di validazione
- **Mathematical calculators**
- **Search engines**
- **Configuration parsers**

## Anti-pattern

 **Interprete troppo complesso**: Un interprete che gestisce troppe regole
```
// SBAGLIATO
class GodInterpreter implements ExpressionInterface {
    interpret(context) returns mixed {
        // Gestisce tutto: matematica, stringhe, date, regex, etc.
        // Troppo complesso!
    }
}
```

 **Interprete modulare**: Un interprete per ogni dominio specifico
```
// GIUSTO
class MathExpression implements ExpressionInterface { }
class StringExpression implements ExpressionInterface { }
class DateExpression implements ExpressionInterface { }
```

## Troubleshooting

**Problema**: L'interpretazione è lenta
**Soluzione**: Considera di usare un parser più efficiente o caching

**Problema**: Errori di sintassi difficili da debuggare
**Soluzione**: Aggiungi validazione e messaggi di errore chiari

**Problema**: Grammatica troppo complessa
**Soluzione**: Suddividi in sotto-grammatiche più semplici

## Performance e considerazioni

- **Parsing**: Il parsing può essere costoso
- **Caching**: Considera di cachare espressioni parse
- **Validation**: Valida la sintassi prima dell'interpretazione
- **Memory**: Le espressioni complesse possono usare molta memoria

## Risorse utili

- [Laravel Query Builder](https://laravel.com/docs/queries)
- [Blade Templates](https://laravel.com/docs/blade)
- [Interpreter Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/interpreter)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
