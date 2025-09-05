# Iterator Pattern

## Cosa fa

Il Iterator Pattern fornisce un modo per accedere sequenzialmente agli elementi di una collezione senza esporre la sua rappresentazione sottostante. È come avere un "cursore" che ti permette di navigare attraverso una collezione senza sapere come è implementata internamente.

## Perché ti serve

Immagina di avere diverse strutture dati (array, liste, alberi, database) e vuoi iterare attraverso i loro elementi in modo uniforme. Invece di scrivere codice specifico per ogni tipo, puoi:

- **Nascondere** la complessità della struttura dati
- **Iterare** in modo uniforme indipendentemente dal tipo
- **Controllare** il processo di iterazione (pause, resume, filter)
- **Supportare** diversi tipi di iterazione (forward, backward, random)

## Come funziona

Il pattern ha quattro componenti principali:

1. **Iterator (Interfaccia)**: Definisce l'interfaccia per iterare
2. **ConcreteIterator**: Implementazione specifica per un tipo di collezione
3. **Aggregate (Interfaccia)**: Definisce l'interfaccia per creare iteratori
4. **ConcreteAggregate**: Implementazione specifica che crea iteratori

## Schema visivo

```
Client → Aggregate → Iterator → Elements
         ↓
    createIterator()
```

## Quando usarlo

- **Collezioni complesse** (alberi, grafi)
- **Diverse strutture dati** con iterazione uniforme
- **Iterazione controllata** (pause, resume, filter)
- **Lazy loading** di dati
- **Streaming** di dati
- **Database cursors**

## Pro e contro

### Pro
- **Uniformità**: Stessa interfaccia per diverse collezioni
- **Encapsulation**: Nasconde la struttura interna
- **Flessibilità**: Diversi tipi di iterazione
- **Lazy loading**: Carica dati solo quando necessario

### Contro
- **Overhead**: Può aggiungere complessità per collezioni semplici
- **Performance**: Può essere più lento per operazioni semplici
- **Memory**: Può usare più memoria per mantenere lo stato

## Esempi di codice

### Interfaccia Iterator
```php
interface IteratorInterface
{
    public function current(): mixed;
    public function next(): void;
    public function key(): mixed;
    public function valid(): bool;
    public function rewind(): void;
}
```

### Interfaccia Aggregate
```php
interface AggregateInterface
{
    public function createIterator(): IteratorInterface;
}
```

### Collezione concreta
```php
class BookCollection implements AggregateInterface
{
    private array $books = [];
    
    public function addBook(Book $book): void
    {
        $this->books[] = $book;
    }
    
    public function createIterator(): IteratorInterface
    {
        return new BookIterator($this->books);
    }
}
```

### Iteratore concreto
```php
class BookIterator implements IteratorInterface
{
    private array $books;
    private int $position = 0;
    
    public function __construct(array $books)
    {
        $this->books = $books;
    }
    
    public function current(): Book
    {
        return $this->books[$this->position];
    }
    
    public function next(): void
    {
        $this->position++;
    }
    
    public function key(): int
    {
        return $this->position;
    }
    
    public function valid(): bool
    {
        return isset($this->books[$this->position]);
    }
    
    public function rewind(): void
    {
        $this->position = 0;
    }
}
```

### Uso
```php
$collection = new BookCollection();
$collection->addBook(new Book("PHP Guide"));
$collection->addBook(new Book("Laravel Tutorial"));

$iterator = $collection->createIterator();

foreach ($iterator as $book) {
    echo $book->getTitle() . "\n";
}
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Iteratori per diverse collezioni
- Iteratori con filtri e trasformazioni
- Iteratori per database e API
- Iteratori lazy per grandi dataset

## Correlati

- **Composite Pattern**: Per iterare su strutture gerarchiche
- **Visitor Pattern**: Per operazioni durante l'iterazione
- **Strategy Pattern**: Per diversi tipi di iterazione

## Esempi di uso reale

- **Laravel Collections**: Usa iteratori internamente
- **Laravel Eloquent**: Iterazione su modelli
- **PHP SPL Iterators**: Iterator, ArrayIterator, etc.
- **Database cursors**
- **File reading**
- **API pagination**

## Anti-pattern

❌ **Iteratore che fa troppo**: Un iteratore che gestisce troppe responsabilità
```php
// SBAGLIATO
class GodIterator implements IteratorInterface
{
    public function current(): mixed
    {
        $this->validateData();
        $this->transformData();
        $this->cacheData();
        $this->logAccess();
        // Troppo complesso!
    }
}
```

✅ **Iteratore focalizzato**: Un iteratore per una responsabilità specifica
```php
// GIUSTO
class SimpleIterator implements IteratorInterface
{
    public function current(): mixed
    {
        return $this->items[$this->position];
    }
}
```

## Troubleshooting

**Problema**: L'iterazione è lenta
**Soluzione**: Considera lazy loading o caching

**Problema**: Memory leak durante l'iterazione
**Soluzione**: Assicurati di liberare le risorse dopo l'uso

**Problema**: Iterazione non funziona
**Soluzione**: Verifica che l'iteratore implementi correttamente l'interfaccia

## Performance e considerazioni

- **Lazy loading**: Carica dati solo quando necessario
- **Memory usage**: Considera il consumo di memoria
- **Caching**: Cachare risultati se appropriato
- **Batch processing**: Per grandi dataset

## Risorse utili

- [Laravel Collections](https://laravel.com/docs/collections)
- [PHP SPL Iterators](https://www.php.net/manual/en/spl.iterators.php)
- [Iterator Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/iterator)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
