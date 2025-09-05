# Esempio Completo: Iterator Pattern

Questo esempio dimostra l'implementazione del **Iterator Pattern** in Laravel per iterare su diverse collezioni in modo uniforme.

## Funzionalità implementate

- **Iteratori per diverse collezioni** (array, database, API)
- **Iteratori con filtri** e trasformazioni
- **Iteratori lazy** per grandi dataset
- **Iteratori personalizzati** per strutture complesse

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── CollectionController.php
│   └── Services/
│       ├── Iterators/
│       │   ├── IteratorInterface.php
│       │   ├── ArrayIterator.php
│       │   ├── DatabaseIterator.php
│       │   └── ApiIterator.php
│       └── Collections/
│           ├── CollectionInterface.php
│           ├── BookCollection.php
│           └── ProductCollection.php
├── resources/views/
│   └── collections/
│       └── index.blade.php
├── routes/
│   └── web.php
└── composer.json
```

## Esempi di utilizzo

### Iterazione su Collezioni
```php
$collection = new BookCollection();
$iterator = $collection->createIterator();

foreach ($iterator as $book) {
    echo $book->getTitle() . "\n";
}
```

### Iterazione con Filtri
```php
$filteredIterator = new FilteredIterator($iterator, function($book) {
    return $book->getPrice() < 50;
});
```

## Pattern implementati

- **Iterator Pattern**: Iterazione uniforme
- **Composite Pattern**: Per strutture gerarchiche
- **Strategy Pattern**: Per diversi tipi di iterazione
