# Aggregate Root Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Pattern correlati](#pattern-correlati)
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

Il Aggregate Root Pattern ti permette di raggruppare entità e value object correlati in un'unica unità di consistenza. L'Aggregate Root è l'unico punto di accesso per modificare lo stato dell'aggregato e garantisce che tutte le regole di business vengano rispettate.

Pensa a un ordine e-commerce. Invece di gestire separatamente ordine, prodotti, indirizzo e pagamento, crei un Order Aggregate che contiene tutto e controlla che le modifiche siano coerenti.

## Perché ti serve

Immagina di gestire un ordine e-commerce. Senza Aggregate Root:

- Le modifiche possono essere inconsistenti (ordine cancellato ma pagamento processato)
- Le regole di business sono sparse in tutto il codice
- È difficile garantire l'integrità dei dati
- Le transazioni diventano complesse e fragili

Con l'Aggregate Root Pattern:
- **Consistenza garantita**: Tutte le modifiche passano attraverso l'aggregate root
- **Regole centralizzate**: Tutte le regole di business in un posto
- **Transazioni semplici**: Modifichi solo l'aggregate root
- **Integrità dei dati**: L'aggregate root controlla che tutto sia coerente

## Come funziona

1. **Identifica l'aggregate**: Trova le entità che devono essere sempre coerenti
2. **Scegli il root**: Una delle entità diventa l'aggregate root
3. **Raggruppa le entità**: L'aggregate root contiene tutte le entità correlate
4. **Controlla l'accesso**: Solo l'aggregate root può essere modificato dall'esterno
5. **Implementa le regole**: L'aggregate root applica tutte le regole di business

## Schema visivo

```
Order Aggregate Root
    ├── Order (entità principale)
    ├── OrderItems (entità figlie)
    ├── ShippingAddress (value object)
    ├── BillingAddress (value object)
    └── Payment (entità figlia)

Regole:
- Solo Order può essere modificato dall'esterno
- OrderItems, Payment sono modificati tramite Order
- Tutte le modifiche devono rispettare le regole di business
- Una transazione = una modifica all'Aggregate Root
```

## Quando usarlo

Usa l'Aggregate Root Pattern quando:
- Hai entità che devono essere sempre coerenti tra loro
- Le regole di business coinvolgono multiple entità
- Vuoi garantire l'integrità dei dati in modo semplice
- Hai bisogno di transazioni atomiche su gruppi di entità
- Lavori con domini complessi che hanno regole di business intricate

**NON usarlo quando:**
- Le entità sono indipendenti e non hanno regole condivise
- Hai solo operazioni CRUD semplici senza logica di business
- Le performance sono critiche e l'overhead è troppo alto
- Il dominio è troppo semplice per giustificare la complessità

## Pro e contro

**I vantaggi:**
- **Consistenza garantita**: Tutte le modifiche sono coerenti
- **Regole centralizzate**: Tutte le regole di business in un posto
- **Transazioni semplici**: Modifichi solo l'aggregate root
- **Integrità dei dati**: L'aggregate root controlla la coerenza
- **Manutenibilità**: Codice più organizzato e facile da mantenere

**Gli svantaggi:**
- **Complessità aggiuntiva**: Più classi e logica da gestire
- **Performance**: Può caricare più dati del necessario
- **Accoppiamento**: Le entità sono più accoppiate
- **Curva di apprendimento**: I developer devono capire il pattern
- **Overhead**: Più codice per gestire l'aggregate

## Esempi di codice

### Pseudocodice

```
// Aggregate Root per Order
class Order {
    private id
    private customerId
    private status
    private items = []
    private shippingAddress
    private billingAddress
    private payment
    
    // Solo l'aggregate root può essere modificato dall'esterno
    function addItem(productId, quantity, price) {
        if (this.status !== 'DRAFT') {
            throw new InvalidOperationException('Cannot modify confirmed order')
        }
        
        if (quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be positive')
        }
        
        item = new OrderItem(productId, quantity, price)
        this.items.add(item)
        this.recalculateTotal()
    }
    
    function removeItem(productId) {
        if (this.status !== 'DRAFT') {
            throw new InvalidOperationException('Cannot modify confirmed order')
        }
        
        this.items.removeWhere(item => item.productId === productId)
        this.recalculateTotal()
    }
    
    function confirm() {
        if (this.items.isEmpty()) {
            throw new InvalidOperationException('Cannot confirm empty order')
        }
        
        if (!this.shippingAddress || !this.billingAddress) {
            throw new InvalidOperationException('Addresses required for confirmation')
        }
        
        this.status = 'CONFIRMED'
        this.confirmedAt = now()
    }
    
    function cancel() {
        if (this.status === 'SHIPPED') {
            throw new InvalidOperationException('Cannot cancel shipped order')
        }
        
        this.status = 'CANCELLED'
        this.cancelledAt = now()
    }
    
    // Metodi privati per logica interna
    private function recalculateTotal() {
        this.total = this.items.sum(item => item.quantity * item.price)
    }
}

// Entità figlia (non modificabile dall'esterno)
class OrderItem {
    private productId
    private quantity
    private price
    
    constructor(productId, quantity, price) {
        this.productId = productId
        this.quantity = quantity
        this.price = price
    }
}

// Utilizzo
order = new Order(customerId)
order.addItem('PROD-001', 2, 10.50)
order.addItem('PROD-002', 1, 25.00)
order.setShippingAddress(address)
order.setBillingAddress(address)
order.confirm()  // Tutte le modifiche sono coerenti
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Order Aggregate](./esempio-completo/)** - Sistema e-commerce con Order Aggregate per gestire ordini, prodotti e pagamenti

L'esempio include:
- Order Aggregate Root con regole di business complete
- OrderItem entità figlie con validazione
- Value Object per indirizzi e prezzi
- Eventi di dominio per notificare cambiamenti
- Repository pattern per persistenza
- Test completi per tutte le regole di business

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Pattern correlati

- **Domain Events**: Spesso usato insieme per notificare cambiamenti
- **Repository Pattern**: Per gestire la persistenza degli aggregate
- **Value Object**: Spesso usato all'interno degli aggregate
- **Factory Pattern**: Per creare aggregate complessi
- **Unit of Work**: Per gestire le transazioni sugli aggregate

## Esempi di uso reale

- **E-commerce**: Order aggregate con prodotti, pagamenti e spedizioni
- **Banking**: Account aggregate con transazioni e saldi
- **Inventory**: Product aggregate con stock, prezzi e categorie
- **Project Management**: Project aggregate con task, risorse e milestone
- **CRM**: Customer aggregate con contatti, opportunità e attività

## Anti-pattern

**Cosa NON fare:**
- Permettere modifiche dirette alle entità figlie dall'esterno
- Creare aggregate troppo grandi (viola il principio di responsabilità singola)
- Dimenticare di validare le regole di business nell'aggregate root
- Usare aggregate per operazioni CRUD semplici
- Creare dipendenze circolari tra aggregate

## Troubleshooting

### Problemi comuni

- **Entità figlie modificate dall'esterno**: Assicurati che solo l'aggregate root sia pubblico
- **Regole di business violate**: Sposta tutta la logica nell'aggregate root
- **Performance lente**: Considera di caricare solo i dati necessari
- **Transazioni complesse**: Usa l'aggregate root come unità di transazione
- **Codice duplicato**: Centralizza la logica nell'aggregate root

### Debug e monitoring

- **Logging**: Traccia tutte le modifiche all'aggregate root
- **Eventi**: Usa domain events per notificare cambiamenti
- **Validazione**: Logga errori di validazione per identificare problemi
- **Testing**: Testa sempre le regole di business nell'aggregate root

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Carica tutte le entità dell'aggregate (può essere molto)
- **CPU**: Validazione e regole di business su ogni modifica
- **I/O**: Caricamento e salvataggio di interi aggregate

### Scalabilità

- **Carico basso**: Funziona bene con pochi utenti
- **Carico medio**: Considera il lazy loading per entità figlie
- **Carico alto**: Potrebbe essere necessario ottimizzare la struttura

### Colli di bottiglia

- **Caricamento completo**: Considera il lazy loading per entità figlie
- **Validazione complessa**: Ottimizza le regole di business
- **Transazioni lunghe**: Mantieni gli aggregate piccoli e focalizzati

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Eloquent](https://laravel.com/docs/eloquent) - ORM per gestire gli aggregate

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html) - DDD e Aggregate
- [Laravel DDD](https://github.com/laravel-ddd/laravel-ddd) - DDD in Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
