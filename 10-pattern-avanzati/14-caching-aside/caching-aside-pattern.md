# Caching Aside Pattern

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

Il Caching Aside Pattern (o Lazy Loading) carica i dati nel cache solo quando vengono richiesti, mantenendo il cache aggiornato con i dati del database. Funziona come un "sistema di cache intelligente" che gestisce automaticamente il caricamento e l'aggiornamento dei dati.

Pensa a un sistema e-commerce: quando un utente richiede i dettagli di un prodotto, il sistema controlla prima il cache. Se il prodotto non è in cache, lo carica dal database, lo salva in cache e lo restituisce. Le successive richieste per lo stesso prodotto saranno servite direttamente dal cache.

## Perché ti serve

Immagina un'applicazione che deve:
- Servire dati frequentemente richiesti
- Ridurre il carico sul database
- Migliorare i tempi di risposta
- Gestire grandi volumi di utenti
- Ottimizzare le performance

Senza caching aside pattern:
- Ogni richiesta va al database
- I tempi di risposta sono lenti
- Il database è sovraccaricato
- Le performance degradano con l'aumento degli utenti
- I costi di infrastruttura sono alti

Con caching aside pattern:
- Dati serviti velocemente dal cache
- Riduzione significativa del carico database
- Tempi di risposta migliori
- Sistema più scalabile
- Costi di infrastruttura ottimizzati

## Come funziona

Il Caching Aside Pattern implementa diverse strategie di cache:

### 1. Cache Read-Through
- Controlla cache prima del database
- Carica dal database se non in cache
- Salva in cache per future richieste
- Restituisce i dati richiesti

### 2. Cache Write-Through
- Aggiorna database e cache simultaneamente
- Garantisce consistenza tra cache e database
- Più lento per operazioni di scrittura
- Ideale per dati critici

### 3. Cache Write-Behind
- Aggiorna cache immediatamente
- Aggiorna database in background
- Più veloce per operazioni di scrittura
- Possibile perdita di dati in caso di crash

### 4. Cache Refresh-Ahead
- Pre-carica dati che potrebbero essere richiesti
- Riduce i tempi di risposta
- Usa pattern di accesso per predire richieste
- Ottimizza l'esperienza utente

**Esempio di configurazione:**
```php
// Cache configuration
'cache' => [
    'driver' => 'redis',
    'prefix' => 'app_cache',
    'ttl' => 3600, // 1 hour
],

// Cache strategies
'strategies' => [
    'products' => [
        'ttl' => 1800, // 30 minutes
        'strategy' => 'read_through',
        'tags' => ['products', 'catalog'],
    ],
    'users' => [
        'ttl' => 7200, // 2 hours
        'strategy' => 'write_through',
        'tags' => ['users', 'profiles'],
    ],
    'orders' => [
        'ttl' => 900, // 15 minutes
        'strategy' => 'write_behind',
        'tags' => ['orders', 'transactions'],
    ],
],
```

## Schema visivo

```
Sistema senza Cache:
Request → Database → Response (Slow) 

Sistema con Caching Aside:
Request → Cache → Hit? → Yes → Response (Fast) 
   ↓ No
Request → Database → Save to Cache → Response (Fast) 

Strategie di Cache:
Read-Through:    Cache → Database → Cache → Response
Write-Through:   Database + Cache → Response
Write-Behind:    Cache → Response → Database (Background)
Refresh-Ahead:   Cache → Response → Pre-load Related Data
```

**Flusso di caching aside:**
```
Request → Check Cache → Cache Hit? → Yes → Return Cached Data
   ↓ No
Load from Database → Save to Cache → Return Data
   ↓
Update Data → Update Database → Update Cache → Return Updated Data
```

## Quando usarlo

Usa Caching Aside Pattern quando:
- Hai dati che vengono letti frequentemente
- Vuoi ridurre il carico sul database
- I tempi di risposta sono critici
- Hai dati che cambiano raramente
- Vuoi migliorare le performance dell'applicazione
- Stai gestendo grandi volumi di utenti

**NON usarlo quando:**
- I dati cambiano molto frequentemente
- Hai requisiti di consistenza molto alti
- Il cache non porta benefici significativi
- I dati sono molto piccoli
- Hai problemi di memoria limitata
- I dati sono sensibili e non possono essere cachati

## Pro e contro

**I vantaggi:**
- **Performance**: Tempi di risposta significativamente migliori
- **Scalabilità**: Riduce il carico sul database
- **Costi**: Riduce i costi di infrastruttura
- **UX**: Migliore esperienza utente
- **Flessibilità**: Strategie diverse per diversi tipi di dati
- **Controllo**: Controllo granulare su cosa viene cachato

**Gli svantaggi:**
- **Complessità**: Aggiunge complessità al sistema
- **Consistenza**: Possibili problemi di consistenza
- **Memoria**: Consuma memoria per il cache
- **Debugging**: Più difficile debuggare problemi di cache
- **Configurazione**: Richiede tuning dei parametri
- **Invalidazione**: Gestione complessa dell'invalidazione

## Esempi di codice

### Pseudocodice
```
class CacheManager {
    constructor(cache, database) {
        this.cache = cache
        this.database = database
        this.strategies = {}
    }
    
    async get(key, strategy = 'read_through') {
        switch (strategy) {
            case 'read_through':
                return await this.readThrough(key)
            case 'write_through':
                return await this.writeThrough(key)
            case 'write_behind':
                return await this.writeBehind(key)
            case 'refresh_ahead':
                return await this.refreshAhead(key)
            default:
                throw new Error('Unknown cache strategy')
        }
    }
    
    async readThrough(key) {
        // Controlla cache
        let data = await this.cache.get(key)
        
        if (data) {
            return data
        }
        
        // Carica dal database
        data = await this.database.get(key)
        
        if (data) {
            // Salva in cache
            await this.cache.set(key, data, this.getTTL(key))
        }
        
        return data
    }
    
    async writeThrough(key, data) {
        // Aggiorna database
        await this.database.update(key, data)
        
        // Aggiorna cache
        await this.cache.set(key, data, this.getTTL(key))
        
        return data
    }
    
    async writeBehind(key, data) {
        // Aggiorna cache immediatamente
        await this.cache.set(key, data, this.getTTL(key))
        
        // Aggiorna database in background
        this.updateDatabaseAsync(key, data)
        
        return data
    }
    
    async refreshAhead(key) {
        // Carica dati principali
        const data = await this.readThrough(key)
        
        // Pre-carica dati correlati
        this.preloadRelatedData(key, data)
        
        return data
    }
    
    async updateDatabaseAsync(key, data) {
        try {
            await this.database.update(key, data)
        } catch (error) {
            // Gestisci errori di aggiornamento database
            console.error('Database update failed:', error)
        }
    }
    
    async preloadRelatedData(key, data) {
        // Implementa logica per pre-caricare dati correlati
        const relatedKeys = this.getRelatedKeys(key, data)
        
        for (const relatedKey of relatedKeys) {
            if (!await this.cache.has(relatedKey)) {
                const relatedData = await this.database.get(relatedKey)
                if (relatedData) {
                    await this.cache.set(relatedKey, relatedData, this.getTTL(relatedKey))
                }
            }
        }
    }
    
    getTTL(key) {
        return this.strategies[key]?.ttl || 3600
    }
    
    getRelatedKeys(key, data) {
        // Implementa logica per determinare chiavi correlate
        return []
    }
}

// Utilizzo
cacheManager = new CacheManager(redis, database)

// Read-through
product = await cacheManager.get('product:123', 'read_through')

// Write-through
await cacheManager.set('product:123', productData, 'write_through')

// Write-behind
await cacheManager.set('order:456', orderData, 'write_behind')

// Refresh-ahead
user = await cacheManager.get('user:789', 'refresh_ahead')
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Caching Aside](./esempio-completo/)** - Sistema e-commerce con caching per prodotti, utenti e ordini

L'esempio include:
- Caching per prodotti con strategia read-through
- Caching per utenti con strategia write-through
- Caching per ordini con strategia write-behind
- Cache invalidation e refresh
- Monitoring e metriche di cache
- Test per diverse strategie di cache

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Write-Through Pattern](./15-write-through/write-through-pattern.md)** - Aggiornamento simultaneo di cache e database
- **[Write-Behind Pattern](./16-write-behind/write-behind-pattern.md)** - Aggiornamento cache immediato e database in background
- **[Materialized View Pattern](./17-materialized-view/materialized-view-pattern.md)** - Viste materializzate per query complesse
- **[CQRS Pattern](./05-cqrs/cqrs-pattern.md)** - Separazione tra comandi e query

### Principi e Metodologie

- **[SOLID Principles](../12-pattern-metodologie-concettuali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../12-pattern-metodologie-concettuali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../12-pattern-metodologie-concettuali/09-tdd/tdd.md)** - Test-Driven Development
- **[Performance Optimization](../12-pattern-metodologie-concettuali/67-performance-optimization/performance-optimization.md)** - Ottimizzazione delle performance

## Esempi di uso reale

- **E-commerce**: Amazon per caching di prodotti e categorie
- **Social Media**: Facebook per caching di post e profili utenti
- **Banking**: Sistemi bancari per caching di conti e transazioni
- **IoT**: Sistemi industriali per caching di sensori e dati
- **Cloud**: AWS ElastiCache per caching distribuito

## Anti-pattern

**Cosa NON fare:**
- **Cache eccessivo**: Non cachare tutto indiscriminatamente
- **Cache senza TTL**: Non impostare time-to-live per il cache
- **Cache senza invalidazione**: Non implementare strategie di invalidazione
- **Cache senza monitoring**: Non monitorare l'efficacia del cache
- **Cache senza fallback**: Non gestire fallimenti del cache
- **Cache senza strategia**: Non usare strategie appropriate per i dati

## Troubleshooting

### Problemi comuni
- **Cache miss eccessivi**: Verifica strategia di cache e TTL
- **Cache hit bassi**: Ottimizza chiavi di cache e strategie
- **Inconsistenza dati**: Implementa strategie di invalidazione
- **Memoria esaurita**: Ottimizza dimensioni cache e TTL
- **Performance degradate**: Monitora metriche di cache

### Debug e monitoring
- **Cache hit ratio**: Traccia rapporto tra hit e miss
- **Cache size**: Monitora dimensioni del cache
- **TTL effectiveness**: Analizza efficacia dei TTL
- **Invalidation patterns**: Monitora pattern di invalidazione
- **Memory usage**: Traccia utilizzo memoria del cache

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Consumo significativo per cache
- **CPU**: Overhead per gestione cache
- **I/O**: Riduzione I/O database
- **Rete**: Comunicazione con cache distribuito

### Scalabilità
- **Carico basso**: Overhead non giustificato per sistemi semplici
- **Carico medio**: Benefici iniziano a manifestarsi
- **Carico alto**: Eccellente miglioramento delle performance

### Colli di bottiglia
- **Cache size**: Dimensioni insufficienti causano miss frequenti
- **TTL**: TTL sbagliati causano invalidazione prematura
- **Strategia**: Strategia sbagliata per tipo di dati
- **Invalidation**: Invalidazione eccessiva riduce efficacia

## Risorse utili

### Documentazione ufficiale
- [Caching Patterns - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/cache-aside) - Documentazione Microsoft
- [Caching Strategies - Redis](https://redis.io/docs/manual/patterns/) - Guida Redis

### Laravel specifico
- [Laravel Cache](https://laravel.com/docs/cache) - Sistema di cache integrato
- [Laravel Redis](https://laravel.com/docs/redis) - Cache con Redis

### Esempi e tutorial
- [Caching in PHP](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico PHP
- [Cache Patterns](https://microservices.io/patterns/data/cqrs.html) - Pattern di architettura

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
