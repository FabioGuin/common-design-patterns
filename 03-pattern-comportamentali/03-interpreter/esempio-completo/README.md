# Esempio Completo: Interpreter Pattern

Questo esempio dimostra l'implementazione del **Interpreter Pattern** in Laravel per interpretare espressioni matematiche e query di ricerca.

## Funzionalità implementate

- **Interprete per espressioni matematiche** (+, -, *, /)
- **Query builder** per ricerche
- **Template engine** personalizzato
- **Sistema di regole** configurabile

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── ExpressionController.php
│   └── Services/
│       ├── Expressions/
│       │   ├── ExpressionInterface.php
│       │   ├── NumberExpression.php
│       │   ├── AddExpression.php
│       │   ├── SubtractExpression.php
│       │   └── ExpressionParser.php
│       └── Query/
│           ├── QueryInterface.php
│           ├── FieldExpression.php
│           ├── OperatorExpression.php
│           └── QueryBuilder.php
├── resources/views/
│   └── expressions/
│       └── index.blade.php
├── routes/
│   └── web.php
└── composer.json
```

## Esempi di utilizzo

### Espressioni Matematiche
```php
$parser = new ExpressionParser();
$expression = $parser->parse("2 + 3 * 4");
$result = $expression->interpret(new Context()); // 14
```

### Query Builder
```php
$queryBuilder = new QueryBuilder();
$query = $queryBuilder->build("title:php AND author:john");
$results = $query->interpret(new SearchContext());
```

## Pattern implementati

- **Interpreter Pattern**: Interpretazione di espressioni
- **Composite Pattern**: Per costruire alberi di espressioni
- **Strategy Pattern**: Per diversi tipi di interpretazione
