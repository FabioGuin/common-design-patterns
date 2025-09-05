# Sharding Pattern

## Indice

### Comprensione Base
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

Il Sharding Pattern divide i dati in più database o server (shard) per distribuire il carico e migliorare le performance. Funziona come un "sistema di distribuzione" che partiziona i dati in base a criteri specifici.

Pensa a un sistema e-commerce con milioni di prodotti: invece di avere un unico database gigante, il sharding pattern divide i prodotti in più database (es. per categoria, regione, o ID), permettendo a ogni shard di gestire una porzione specifica dei dati.

## Perché ti serve

Immagina un'applicazione che deve:
- Gestire grandi volumi di dati
- Supportare migliaia di utenti simultanei
- Mantenere performance elevate
- Scalare orizzontalmente
- Ridurre i tempi di query

Senza sharding pattern:
- Il database diventa un collo di bottiglia
- Le query diventano lente con molti dati
- Il sistema non scala orizzontalmente
- Le performance degradano con la crescita
- Backup e manutenzione diventano complessi

Con sharding pattern:
- Distribuzione del carico su più database
- Query più veloci su dataset più piccoli
- Scalabilità orizzontale
- Performance migliori e prevedibili
- Gestione più semplice dei singoli shard

## Come funziona

Il Sharding Pattern implementa diverse strategie di partizionamento:

### 1. Sharding per Chiave
- Partizionamento basato su chiave primaria
- Distribuzione uniforme dei dati
- Accesso diretto al shard corretto

### 2. Sharding per Range
- Partizionamento basato su intervalli di valori
- Dati correlati nello stesso shard
- Query range efficienti

### 3. Sharding per Hash
- Partizionamento basato su hash della chiave
- Distribuzione uniforme garantita
- Accesso prevedibile al shard

### 4. Sharding per Directory
- Partizionamento basato su lookup table
- Flessibilità nella distribuzione
- Gestione dinamica dei shard

**Esempio di configurazione:**
```php
// Sharding per chiave
'sharding' => [
    'strategy' => 'key_based',
    'shards' => [
        'shard_1' => ['host' => 'db1.example.com', 'database' => 'app_shard_1'],
        'shard_2' => ['host' => 'db2.example.com', 'database' => 'app_shard_2'],
        'shard_3' => ['host' => 'db3.example.com', 'database' => 'app_shard_3'],
    ],
    'shard_key' => 'user_id',
    'shard_count' => 3,
],

// Sharding per range
'range_sharding' => [
    'strategy' => 'range_based',
    'ranges' => [
        'shard_1' => ['min' => 1, 'max' => 1000000],
        'shard_2' => ['min' => 1000001, 'max' => 2000000],
        'shard_3' => ['min' => 2000001, 'max' => 3000000],
    ],
],

// Sharding per hash
'hash_sharding' => [
    'strategy' => 'hash_based',
    'hash_function' => 'md5',
    'shard_count' => 4,
],
```

## Schema visivo

```
Sistema senza Sharding:
Client → Load Balancer → Single Database → Performance Issues ❌

Sistema con Sharding:
Client → Load Balancer → Shard Router → Shard 1 (Users 1-1M)
                              ↓
                              Shard 2 (Users 1M-2M)
                              ↓
                              Shard 3 (Users 2M-3M)

Strategie di Sharding:
Key-based:    User ID 123 → Shard 1 (123 % 3 = 0)
Range-based:  User ID 500K → Shard 1 (1-1M range)
Hash-based:   User ID 123 → Hash → Shard 2
Directory:    User ID 123 → Lookup → Shard 3
```

**Flusso di sharding:**
```
Request → Extract Shard Key → Determine Shard → Connect to Shard → Execute Query
   ↓
Response ← Process Result ← Query Shard ← Route to Shard ← Identify Shard
```

## Quando usarlo

Usa Sharding Pattern quando:
- Hai grandi volumi di dati che non entrano in un singolo database
- Le query diventano lente per la dimensione dei dati
- Vuoi scalare orizzontalmente
- Hai dati che possono essere partizionati logicamente
- Vuoi migliorare le performance di lettura/scrittura
- Hai requisiti di disponibilità elevati

**NON usarlo quando:**
- I dati sono relativamente piccoli
- Hai query che devono accedere a tutti i shard
- La complessità non è giustificata
- Hai transazioni che coinvolgono più shard
- Il sistema è in fase di prototipo
- Non hai esperienza con la gestione distribuita

## Pro e contro

**I vantaggi:**
- **Scalabilità**: Scalabilità orizzontale illimitata
- **Performance**: Query più veloci su dataset più piccoli
- **Disponibilità**: Fallimento di un shard non blocca tutto
- **Manutenzione**: Gestione più semplice dei singoli shard
- **Costi**: Possibilità di usare hardware diverso per shard diversi
- **Isolamento**: Dati isolati per sicurezza e compliance

**Gli svantaggi:**
- **Complessità**: Aumenta significativamente la complessità
- **Transazioni**: Difficile gestire transazioni cross-shard
- **Query**: Query complesse richiedono accesso a più shard
- **Rebalancing**: Spostare dati tra shard è complesso
- **Monitoring**: Più difficile monitorare il sistema distribuito
- **Debugging**: Più difficile debuggare problemi distribuiti

## Esempi di codice

### Pseudocodice
```
class ShardingManager {
    constructor(config) {
        this.strategy = config.strategy
        this.shards = config.shards
        this.shardCount = config.shardCount
        this.shardKey = config.shardKey
    }
    
    getShardForKey(key) {
        switch (this.strategy) {
            case 'key_based':
                return this.getShardByKey(key)
            case 'range_based':
                return this.getShardByRange(key)
            case 'hash_based':
                return this.getShardByHash(key)
            case 'directory_based':
                return this.getShardByDirectory(key)
            default:
                throw new Error('Unknown sharding strategy')
        }
    }
    
    getShardByKey(key) {
        const shardIndex = key % this.shardCount
        return this.shards[`shard_${shardIndex + 1}`]
    }
    
    getShardByRange(key) {
        for (const [shardName, range] of Object.entries(this.ranges)) {
            if (key >= range.min && key <= range.max) {
                return this.shards[shardName]
            }
        }
        throw new Error('No shard found for key')
    }
    
    getShardByHash(key) {
        const hash = this.hashFunction(key)
        const shardIndex = hash % this.shardCount
        return this.shards[`shard_${shardIndex + 1}`]
    }
    
    getShardByDirectory(key) {
        return this.directoryTable[key] || this.getDefaultShard()
    }
    
    async executeQuery(query, shardKey) {
        const shard = this.getShardForKey(shardKey)
        const connection = await this.getConnection(shard)
        return await connection.query(query)
    }
    
    async executeQueryOnAllShards(query) {
        const results = []
        for (const shard of Object.values(this.shards)) {
            const connection = await this.getConnection(shard)
            const result = await connection.query(query)
            results.push(result)
        }
        return this.mergeResults(results)
    }
    
    async getConnection(shard) {
        return new DatabaseConnection({
            host: shard.host,
            database: shard.database,
            username: shard.username,
            password: shard.password
        })
    }
    
    mergeResults(results) {
        // Implementa logica per unire risultati da più shard
        return results.flat()
    }
}

// Utilizzo
shardingManager = new ShardingManager({
    strategy: 'key_based',
    shards: {
        'shard_1': { host: 'db1.example.com', database: 'app_shard_1' },
        'shard_2': { host: 'db2.example.com', database: 'app_shard_2' },
        'shard_3': { host: 'db3.example.com', database: 'app_shard_3' }
    },
    shardCount: 3,
    shardKey: 'user_id'
})

// Query su shard specifico
result = await shardingManager.executeQuery(
    'SELECT * FROM users WHERE id = ?',
    userId
)

// Query su tutti i shard
allUsers = await shardingManager.executeQueryOnAllShards(
    'SELECT * FROM users WHERE status = "active"'
)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Sharding](./esempio-completo/)** - Sistema e-commerce con sharding per utenti e prodotti

L'esempio include:
- Sharding per utenti basato su user_id
- Sharding per prodotti basato su categoria
- Sharding per ordini basato su data
- Gestione connessioni multiple
- Query cross-shard e aggregazioni
- Monitoring e metriche di sharding

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[CQRS Pattern](./05-cqrs/cqrs-pattern.md)** - Separazione tra comandi e query
- **[Event Sourcing Pattern](./06-event-sourcing/event-sourcing-pattern.md)** - Storage basato su eventi
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite
- **[Circuit Breaker Pattern](./08-circuit-breaker/circuit-breaker-pattern.md)** - Protezione da servizi esterni problematici

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development
- **[Microservices](./20-microservices/microservices-pattern.md)** - Architettura a microservizi

## Esempi di uso reale

- **E-commerce**: Amazon per sharding di prodotti e utenti
- **Social Media**: Facebook per sharding di post e utenti
- **Banking**: Sistemi bancari per sharding di conti e transazioni
- **IoT**: Sistemi industriali per sharding di sensori e dati
- **Cloud**: AWS DynamoDB per sharding automatico

## Anti-pattern

**Cosa NON fare:**
- **Sharding prematuro**: Non implementare sharding troppo presto
- **Sharding eccessivo**: Non creare troppi shard piccoli
- **Sharding asimmetrico**: Non creare shard con dimensioni molto diverse
- **Sharding senza monitoring**: Non monitorare la distribuzione dei dati
- **Sharding senza backup**: Non implementare backup per ogni shard
- **Sharding senza rebalancing**: Non implementare strategie di rebalancing

## Troubleshooting

### Problemi comuni
- **Hot spots**: Verifica distribuzione uniforme dei dati
- **Query lente**: Ottimizza query e indici per ogni shard
- **Connessioni**: Gestisci pool di connessioni per ogni shard
- **Transazioni**: Evita transazioni cross-shard quando possibile
- **Monitoring**: Implementa monitoring per ogni shard

### Debug e monitoring
- **Shard distribution**: Monitora distribuzione dei dati per shard
- **Query performance**: Traccia performance delle query per shard
- **Connection pools**: Monitora utilizzo connessioni per shard
- **Data growth**: Traccia crescita dei dati per shard
- **Rebalancing**: Monitora necessità di rebalancing

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead per gestione connessioni multiple
- **CPU**: Overhead per routing e aggregazione
- **I/O**: Distribuzione I/O su più database
- **Rete**: Comunicazione con più database

### Scalabilità
- **Carico basso**: Overhead non giustificato per sistemi semplici
- **Carico medio**: Benefici iniziano a manifestarsi con grandi dataset
- **Carico alto**: Eccellente scalabilità orizzontale

### Colli di bottiglia
- **Shard key**: Scelta sbagliata può causare hot spots
- **Query cross-shard**: Query che accedono a più shard sono lente
- **Rebalancing**: Spostare dati tra shard è costoso
- **Monitoring**: Più difficile monitorare sistema distribuito

## Risorse utili

### Documentazione ufficiale
- [Database Sharding - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/sharding) - Documentazione Microsoft
- [Sharding - MongoDB](https://docs.mongodb.com/manual/sharding/) - Guida MongoDB

### Laravel specifico
- [Laravel Database Connections](https://laravel.com/docs/database#multiple-databases) - Connessioni multiple
- [Laravel Sharding](https://github.com/franzose/laravel-sharding) - Package per sharding

### Esempi e tutorial
- [Sharding in PHP](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico PHP
- [Database Sharding Patterns](https://microservices.io/patterns/data/database-per-service.html) - Pattern di architettura

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
