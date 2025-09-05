# Specification Pattern

## Indice

### Compensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Correlati](#correlati)
- [Esempi di uso reale](#esempi-di-uso-reale)

### Cosa Evitare
- [Anti-pattern](#anti-pattern)
- [Troubleshooting](#troubleshooting)

### Implementazione Pratica
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

### Considerazioni Tecniche
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa

Il pattern Specification incapsula la logica di business in oggetti riutilizzabili e componibili. Le specifiche definiscono criteri di selezione, validazione e business rules in modo modulare e testabile.

Pensa alle Specification come a dei filtri intelligenti: quando devi selezionare prodotti in un negozio, usi diversi criteri (prezzo, marca, categoria). Ogni criterio è una specifica che puoi combinare per creare filtri complessi e riutilizzabili.

## Perché ti serve

Senza Specification, la tua logica di business è sparsa ovunque: nei repository, nei service, nei controller. Risultato? Codice duplicato, difficile da testare e da mantenere.

Con Specification ottieni:
- **Modularità**: Logica di business componibile
- **Riusabilità**: Specifiche riutilizzabili in più contesti
- **Testabilità**: Facile testare singole specifiche
- **Leggibilità**: Codice auto-documentato
- **Flessibilità**: Combinazione dinamica di criteri
- **Manutenibilità**: Modifiche centralizzate
- **Performance**: Ottimizzazione delle query

## Come funziona

1. **Definisci le Specification** per ogni criterio di business
2. **Implementa i metodi** `isSatisfiedBy()` e `toQuery()`
3. **Combina le specifiche** usando operatori logici
4. **Usa le specifiche** nei repository e service
5. **Testa le specifiche** isolatamente

Il flusso è: **Business Rule → Specification → Combination → Application**

## Schema visivo

```
Business Rules
    ↓
Specification Objects
    ↓
Combination Logic
    ↓
Repository/Service
    ↓
Database Query
```

**Flusso dettagliato:**
```
1. Define Business Rule
   ↓
2. Create Specification
   ↓
3. Combine Specifications
   ↓
4. Apply to Repository
   ↓
5. Generate Query
   ↓
6. Execute Query
   ↓
7. Return Results
```

## Quando usarlo

Usa il pattern Specification quando:
- Hai logica di business complessa
- Vuoi riutilizzare criteri di selezione
- Hai bisogno di combinare criteri dinamicamente
- Vuoi testare la logica di business isolatamente
- Hai query complesse da ottimizzare
- Vuoi documentare le regole di business
- Hai bisogno di validazione complessa

**NON usarlo quando:**
- Hai criteri semplici e singoli
- Vuoi mantenere la semplicità
- Hai vincoli di performance estremi
- L'applicazione è molto piccola
- I criteri sono statici

## Pro e contro

**I vantaggi:**
- **Modularità**: Logica componibile e riutilizzabile
- **Testabilità**: Facile testare singole specifiche
- **Leggibilità**: Codice auto-documentato
- **Flessibilità**: Combinazione dinamica
- **Manutenibilità**: Modifiche centralizzate
- **Performance**: Ottimizzazione query
- **Riusabilità**: Specifiche riutilizzabili

**Gli svantaggi:**
- **Complessità**: Più classi da gestire
- **Overhead**: Strato aggiuntivo di astrazione
- **Curva di apprendimento**: Richiede comprensione del pattern
- **Over-engineering**: Può essere eccessivo per criteri semplici
- **Performance**: Può rallentare per criteri semplici

## Esempi di codice

### Pseudocodice

```
// Specification Interface
interface SpecificationInterface {
    function isSatisfiedBy(entity): Boolean
    function toQuery(): QueryBuilder
    function and(specification): SpecificationInterface
    function or(specification): SpecificationInterface
    function not(): SpecificationInterface
}

// Base Specification
abstract class BaseSpecification implements SpecificationInterface {
    function and(specification): SpecificationInterface {
        return new AndSpecification(this, specification)
    }
    
    function or(specification): SpecificationInterface {
        return new OrSpecification(this, specification)
    }
    
    function not(): SpecificationInterface {
        return new NotSpecification(this)
    }
}

// Concrete Specifications
class PriceRangeSpecification extends BaseSpecification {
    constructor(minPrice, maxPrice) {
        this.minPrice = minPrice
        this.maxPrice = maxPrice
    }
    
    function isSatisfiedBy(product): Boolean {
        return product.price >= this.minPrice && product.price <= this.maxPrice
    }
    
    function toQuery(): QueryBuilder {
        return query.whereBetween('price', [this.minPrice, this.maxPrice])
    }
}

class CategorySpecification extends BaseSpecification {
    constructor(categoryId) {
        this.categoryId = categoryId
    }
    
    function isSatisfiedBy(product): Boolean {
        return product.categoryId === this.categoryId
    }
    
    function toQuery(): QueryBuilder {
        return query.where('category_id', this.categoryId)
    }
}

class InStockSpecification extends BaseSpecification {
    function isSatisfiedBy(product): Boolean {
        return product.stock > 0
    }
    
    function toQuery(): QueryBuilder {
        return query.where('stock', '>', 0)
    }
}

// Composite Specifications
class AndSpecification extends BaseSpecification {
    constructor(left, right) {
        this.left = left
        this.right = right
    }
    
    function isSatisfiedBy(entity): Boolean {
        return this.left.isSatisfiedBy(entity) && this.right.isSatisfiedBy(entity)
    }
    
    function toQuery(): QueryBuilder {
        return this.left.toQuery().where(function(query) {
            return this.right.toQuery()
        })
    }
}

class OrSpecification extends BaseSpecification {
    constructor(left, right) {
        this.left = left
        this.right = right
    }
    
    function isSatisfiedBy(entity): Boolean {
        return this.left.isSatisfiedBy(entity) || this.right.isSatisfiedBy(entity)
    }
    
    function toQuery(): QueryBuilder {
        return this.left.toQuery().orWhere(function(query) {
            return this.right.toQuery()
        })
    }
}

// Usage in Service
class ProductService {
    function getProducts(specification): Collection {
        return this.productRepository.findBySpecification(specification)
    }
    
    function getAvailableProducts(): Collection {
        specification = new InStockSpecification()
        return this.getProducts(specification)
    }
    
    function getProductsInPriceRange(minPrice, maxPrice): Collection {
        specification = new PriceRangeSpecification(minPrice, maxPrice)
        return this.getProducts(specification)
    }
    
    function getProductsInCategoryAndPriceRange(categoryId, minPrice, maxPrice): Collection {
        categorySpec = new CategorySpecification(categoryId)
        priceSpec = new PriceRangeSpecification(minPrice, maxPrice)
        specification = categorySpec.and(priceSpec)
        return this.getProducts(specification)
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Specification E-commerce System](./esempio-completo/)** - Sistema e-commerce con Specification Pattern

L'esempio include:
- Specification per prodotti e ordini
- Combinazione di specifiche
- Query optimization
- Test per le specifiche
- Validazione complessa

**Nota per l'implementazione**: L'esempio completo segue il template semplificato con focus sulla dimostrazione del pattern Specification, non su un'applicazione completa.

## Correlati

### Pattern

- **[Repository Pattern](./02-repository/repository-pattern.md)** - Astrae l'accesso ai dati
- **[Service Layer Pattern](./03-service-layer/service-layer-pattern.md)** - Centralizza la logica di business
- **[DTO Pattern](./04-dto/dto-pattern.md)** - Trasferisce dati tra layer
- **[Unit of Work Pattern](./05-unit-of-work/unit-of-work-pattern.md)** - Gestisce le transazioni

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Sistemi e-commerce**: Filtri prodotti complessi
- **Sistemi di ricerca**: Criteri di ricerca avanzati
- **Sistemi di reporting**: Filtri per report
- **Sistemi di validazione**: Regole di business complesse
- **Sistemi di autorizzazione**: Controlli di accesso

## Anti-pattern

**Cosa NON fare:**
- **Fat Specification**: Specification che fanno troppo lavoro
- **Anemic Specification**: Specification senza logica
- **Specification per tutto**: Non serve per criteri semplici
- **Tight Coupling**: Specification troppo legate ai modelli
- **God Specification**: Un singolo Specification per tutto

## Troubleshooting

### Problemi comuni

- **Performance**: Ottimizza le query generate
- **Complessità**: Suddividi specifiche complesse
- **Testing**: Testa le specifiche isolatamente
- **Memory**: Gestisci le specifiche in memoria
- **Query optimization**: Ottimizza le query composite

### Debug e monitoring

- **Log delle specifiche**: Traccia l'applicazione delle specifiche
- **Performance**: Monitora i tempi di esecuzione
- **Query analysis**: Analizza le query generate
- **Memory usage**: Monitora l'uso della memoria

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Gestione delle specifiche in memoria
- **CPU**: Overhead per combinazione di specifiche
- **I/O**: Ottimizzazione delle query generate

### Scalabilità

- **Carico basso**: Specification non aggiungono overhead significativo
- **Carico medio**: Ottimizzazione delle query composite
- **Carico alto**: Caching e ottimizzazioni specifiche

### Colli di bottiglia

- **Query complesse**: Ottimizza le query generate
- **Memory usage**: Gestisci le specifiche in memoria
- **Combination logic**: Ottimizza la logica di combinazione

## Risorse utili

### Documentazione ufficiale

- [Laravel Query Builder](https://laravel.com/docs/queries) - Query builder Laravel
- [Specification Pattern Wikipedia](https://en.wikipedia.org/wiki/Specification_pattern) - Teoria del pattern
- [Martin Fowler on Specification](https://martinfowler.com/apsupp/spec.pdf) - Definizione del pattern

### Laravel specifico

- [Laravel Query Builder](https://laravel.com/docs/queries) - Implementazione Laravel
- [Laravel Eloquent](https://laravel.com/docs/eloquent) - ORM Laravel
- [Laravel Scopes](https://laravel.com/docs/eloquent#query-scopes) - Scope per query

### Esempi e tutorial

- [Laravel Specification](https://laravel.com/docs/queries) - Implementazione Laravel
- [Specification Best Practices](https://laravel.com/docs/queries) - Best practices

### Strumenti di supporto

- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug query
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring applicazione
