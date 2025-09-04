# Pattern Creazionali

## Cosa sono
I pattern creazionali ti aiutano a creare oggetti in modo più intelligente. Invece di usare sempre `new Class()`, questi pattern ti danno più controllo su come e quando creare le tue istanze.

## Perché usarli
- Nascondono la complessità della creazione degli oggetti
- Ti permettono di cambiare come crei gli oggetti senza rompere il resto del codice
- Rendono il codice più flessibile e riutilizzabile

## I Pattern che trovi qui

### Singleton
- **File**: `01-singleton/singleton-pattern.md`
- **Cosa fa**: Assicura che una classe abbia una sola istanza
- **Dove lo usi in Laravel**: Service Container, connessioni database, cache
- **Esempio Pratico**: [Logger Singleton](../../esempi-completi/01-singleton-logger/)

### Factory Method
- **File**: `02-factory-method/factory-method-pattern.md`
- **Cosa fa**: Delega la creazione di oggetti alle sottoclassi
- **Dove lo usi in Laravel**: Model factories, Service providers
- **Esempio Pratico**: [User Management Factory](../../esempi-completi/02-factory-user-management/)

### Abstract Factory
- **File**: `03-abstract-factory/abstract-factory-pattern.md`
- **Cosa fa**: Crea gruppi di oggetti che vanno insieme
- **Dove lo usi in Laravel**: Payment gateways, canali di notifica
- **Esempio Pratico**: [Sistema di Pagamento](../../esempi-completi/04-abstract-factory-payment/)

### Builder
- **File**: `04-builder/builder-pattern.md`
- **Cosa fa**: Costruisce oggetti complessi passo dopo passo
- **Dove lo usi in Laravel**: Query Builder, Eloquent, costruzione email
- **Esempio Pratico**: [User Builder System](../../esempi-completi/05-user-builder-system/)

### Prototype
- **File**: `05-prototype/prototype-pattern.md`
- **Cosa fa**: Crea oggetti clonando un modello esistente
- **Dove lo usi in Laravel**: Sistema di template, clonazione documenti
- **Esempio Pratico**: [Document Prototype System](../../esempi-completi/06-document-prototype-system/)

### Object Pool
- **File**: `06-object-pool/object-pool-pattern.md`
- **Cosa fa**: Riutilizza oggetti costosi invece di crearli ogni volta
- **Dove lo usi in Laravel**: Pool di connessioni, cache pools
- **Esempio Pratico**: [Connection Pool System](../../esempi-completi/07-connection-pool-system/)

## Link utili
- [Torna all'indice principale](../../README.md)
- [Vedi gli esempi completi](../../esempi-completi/)
