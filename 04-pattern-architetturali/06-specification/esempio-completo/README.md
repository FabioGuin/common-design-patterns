# Specification Pattern - Esempio Completo

## Descrizione

Questo esempio dimostra l'implementazione del pattern Specification in Laravel attraverso un sistema e-commerce che gestisce filtri complessi per prodotti e ordini utilizzando specifiche componibili.

## Caratteristiche

- **Specification Pattern**: Logica di business componibile
- **Query Optimization**: Ottimizzazione delle query generate
- **Composition Logic**: Combinazione dinamica di criteri
- **Business Rules**: Incapsulamento delle regole di business
- **Testing**: Test per le specifiche
- **Performance**: Ottimizzazione delle query

## Struttura del Progetto

```
app/
├── Specifications/
│   ├── Interfaces/
│   │   └── SpecificationInterface.php
│   ├── Base/
│   │   └── BaseSpecification.php
│   ├── Product/
│   │   ├── PriceRangeSpecification.php
│   │   ├── CategorySpecification.php
│   │   ├── InStockSpecification.php
│   │   └── NameContainsSpecification.php
│   ├── Order/
│   │   ├── StatusSpecification.php
│   │   ├── DateRangeSpecification.php
│   │   └── CustomerSpecification.php
│   └── Composite/
│       ├── AndSpecification.php
│       ├── OrSpecification.php
│       └── NotSpecification.php
├── Services/
│   ├── ProductService.php
│   └── OrderService.php
├── Repositories/
│   ├── ProductRepository.php
│   └── OrderRepository.php
└── Http/Controllers/
    ├── ProductController.php
    └── OrderController.php
```

## Installazione

1. **Clona il repository**:
   ```bash
   git clone [repository-url]
   cd specification-ecommerce-example
   ```

2. **Installa le dipendenze**:
   ```bash
   composer install
   ```

3. **Configura l'ambiente**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configura il database**:
   - Crea un database MySQL
   - Aggiorna le credenziali in `.env`
   - Esegui le migrazioni:
     ```bash
     php artisan migrate
     ```

5. **Avvia il server**:
   ```bash
   php artisan serve
   ```

6. **Visita l'applicazione**:
   - Apri `http://localhost:8000` nel browser
   - Esplora le funzionalità dell'e-commerce

## Funzionalità Implementate

### Specification Pattern
- **SpecificationInterface**: Interfaccia base per tutte le specifiche
- **BaseSpecification**: Classe base con logica di combinazione
- **Concrete Specifications**: Specifiche concrete per prodotti e ordini
- **Composite Specifications**: Specifiche composte (AND, OR, NOT)

### Query Optimization
- **Query Builder**: Generazione ottimizzata delle query
- **Eager Loading**: Caricamento anticipato delle relazioni
- **Index Optimization**: Ottimizzazione degli indici
- **Caching**: Caching delle query complesse

### Business Rules
- **Product Filters**: Filtri per prodotti (prezzo, categoria, stock)
- **Order Filters**: Filtri per ordini (stato, data, cliente)
- **Validation Rules**: Regole di validazione complesse
- **Search Criteria**: Criteri di ricerca avanzati

## Pattern Specification in Azione

### 1. Specification Interface
```php
interface SpecificationInterface
{
    public function isSatisfiedBy($entity): bool;
    public function toQuery(): Builder;
    public function and(SpecificationInterface $specification): SpecificationInterface;
    public function or(SpecificationInterface $specification): SpecificationInterface;
    public function not(): SpecificationInterface;
}
```

### 2. Base Specification
```php
abstract class BaseSpecification implements SpecificationInterface
{
    public function and(SpecificationInterface $specification): SpecificationInterface
    {
        return new AndSpecification($this, $specification);
    }

    public function or(SpecificationInterface $specification): SpecificationInterface
    {
        return new OrSpecification($this, $specification);
    }

    public function not(): SpecificationInterface
    {
        return new NotSpecification($this);
    }
}
```

### 3. Concrete Specifications
```php
class PriceRangeSpecification extends BaseSpecification
{
    public function __construct(
        private float $minPrice,
        private float $maxPrice
    ) {}

    public function isSatisfiedBy($product): bool
    {
        return $product->price >= $this->minPrice && $product->price <= $this->maxPrice;
    }

    public function toQuery(): Builder
    {
        return Product::query()->whereBetween('price', [$this->minPrice, $this->maxPrice]);
    }
}

class CategorySpecification extends BaseSpecification
{
    public function __construct(private int $categoryId) {}

    public function isSatisfiedBy($product): bool
    {
        return $product->category_id === $this->categoryId;
    }

    public function toQuery(): Builder
    {
        return Product::query()->where('category_id', $this->categoryId);
    }
}

class InStockSpecification extends BaseSpecification
{
    public function isSatisfiedBy($product): bool
    {
        return $product->stock > 0;
    }

    public function toQuery(): Builder
    {
        return Product::query()->where('stock', '>', 0);
    }
}
```

### 4. Composite Specifications
```php
class AndSpecification extends BaseSpecification
{
    public function __construct(
        private SpecificationInterface $left,
        private SpecificationInterface $right
    ) {}

    public function isSatisfiedBy($entity): bool
    {
        return $this->left->isSatisfiedBy($entity) && $this->right->isSatisfiedBy($entity);
    }

    public function toQuery(): Builder
    {
        return $this->left->toQuery()->where(function ($query) {
            $query->where(function ($subQuery) {
                $this->right->toQuery()->getQuery()->wheres = $subQuery->getQuery()->wheres;
            });
        });
    }
}

class OrSpecification extends BaseSpecification
{
    public function __construct(
        private SpecificationInterface $left,
        private SpecificationInterface $right
    ) {}

    public function isSatisfiedBy($entity): bool
    {
        return $this->left->isSatisfiedBy($entity) || $this->right->isSatisfiedBy($entity);
    }

    public function toQuery(): Builder
    {
        return $this->left->toQuery()->orWhere(function ($query) {
            $query->where(function ($subQuery) {
                $this->right->toQuery()->getQuery()->wheres = $subQuery->getQuery()->wheres;
            });
        });
    }
}
```

### 5. Service che usa le Specifiche
```php
class ProductService
{
    public function getProducts(SpecificationInterface $specification): Collection
    {
        return $specification->toQuery()->get();
    }

    public function getAvailableProducts(): Collection
    {
        $specification = new InStockSpecification();
        return $this->getProducts($specification);
    }

    public function getProductsInPriceRange(float $minPrice, float $maxPrice): Collection
    {
        $specification = new PriceRangeSpecification($minPrice, $maxPrice);
        return $this->getProducts($specification);
    }

    public function getProductsInCategoryAndPriceRange(
        int $categoryId, 
        float $minPrice, 
        float $maxPrice
    ): Collection {
        $categorySpec = new CategorySpecification($categoryId);
        $priceSpec = new PriceRangeSpecification($minPrice, $maxPrice);
        $specification = $categorySpec->and($priceSpec);
        
        return $this->getProducts($specification);
    }

    public function searchProducts(string $searchTerm): Collection
    {
        $nameSpec = new NameContainsSpecification($searchTerm);
        $descriptionSpec = new DescriptionContainsSpecification($searchTerm);
        $specification = $nameSpec->or($descriptionSpec);
        
        return $this->getProducts($specification);
    }
}
```

## Vantaggi del Pattern Specification

1. **Modularità**: Logica componibile e riutilizzabile
2. **Testabilità**: Facile testare singole specifiche
3. **Leggibilità**: Codice auto-documentato
4. **Flessibilità**: Combinazione dinamica di criteri
5. **Manutenibilità**: Modifiche centralizzate
6. **Performance**: Ottimizzazione delle query
7. **Riusabilità**: Specifiche riutilizzabili

## Best Practices Implementate

- **Single Responsibility**: Ogni specifica ha una responsabilità
- **Composition**: Combinazione dinamica di specifiche
- **Query Optimization**: Ottimizzazione delle query generate
- **Testing**: Test per ogni specifica
- **Documentation**: Codice auto-documentato
- **Performance**: Caching e ottimizzazioni

## Testing

Per testare l'implementazione:

1. **Test Specification**:
   ```bash
   php artisan test --filter=PriceRangeSpecificationTest
   ```

2. **Test Service**:
   ```bash
   php artisan test --filter=ProductServiceTest
   ```

3. **Test Composite**:
   ```bash
  php artisan test --filter=AndSpecificationTest
   ```

## Estensioni Possibili

- **Caching**: Caching delle specifiche complesse
- **Validation**: Specifiche per validazione
- **Authorization**: Specifiche per autorizzazione
- **Audit**: Specifiche per audit trail
- **Performance**: Ottimizzazioni avanzate

## Conclusione

Questo esempio dimostra come il pattern Specification incapsula la logica di business in oggetti componibili e riutilizzabili. La modularità e la flessibilità rendono l'applicazione più manutenibile e testabile.

Il pattern Specification è particolarmente utile per applicazioni complesse che richiedono logica di business modulare e query ottimizzate.
