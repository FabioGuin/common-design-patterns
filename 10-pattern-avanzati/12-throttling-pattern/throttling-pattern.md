# Throttling Pattern

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

Il Throttling Pattern limita il numero di richieste che un client può fare in un determinato periodo di tempo, proteggendo il sistema da sovraccarico e abusi. Funziona come un "controllo del traffico" che regola l'accesso alle risorse.

Pensa a un sistema di pagamenti: se un utente prova a fare 1000 pagamenti al secondo, invece di processarli tutti, il throttling pattern limita le richieste a un numero ragionevole (es. 10 al minuto), proteggendo il sistema e garantendo equità.

## Perché ti serve

Immagina un'applicazione che deve:
- Gestire API pubbliche
- Processare pagamenti online
- Gestire upload di file
- Fornire servizi a terze parti
- Proteggere da attacchi DDoS

Senza throttling pattern:
- Il sistema può essere sovraccaricato
- Alcuni utenti possono monopolizzare le risorse
- Attacchi di forza bruta sono possibili
- Il sistema diventa instabile
- Esperienza utente degradata

Con throttling pattern:
- Controllo del carico del sistema
- Equità nell'accesso alle risorse
- Protezione da abusi e attacchi
- Sistema stabile e prevedibile
- Migliore esperienza per tutti gli utenti

## Come funziona

Il Throttling Pattern implementa diverse strategie di limitazione:

### 1. Rate Limiting Semplice
- Limite fisso di richieste per periodo
- Reset del contatore dopo il periodo
- Blocco temporaneo quando si supera il limite

### 2. Rate Limiting a Sliding Window
- Finestra scorrevole per calcolo più preciso
- Contatore basato su intervalli di tempo
- Gestione più granulare del traffico

### 3. Rate Limiting per Utente
- Limiti diversi per ogni utente
- Gestione basata su identità
- Personalizzazione per tipo di utente

### 4. Rate Limiting per Endpoint
- Limiti specifici per ogni endpoint
- Protezione granulare delle risorse
- Configurazione flessibile

**Esempio di configurazione:**
```php
// Rate limiting semplice
'rate_limit' => 100, // richieste
'window' => 3600, // secondi (1 ora)

// Rate limiting per utente
'user_limits' => [
    'free' => ['rate' => 10, 'window' => 60],
    'premium' => ['rate' => 100, 'window' => 60],
    'enterprise' => ['rate' => 1000, 'window' => 60],
],

// Rate limiting per endpoint
'endpoint_limits' => [
    'api/payment' => ['rate' => 5, 'window' => 60],
    'api/upload' => ['rate' => 20, 'window' => 60],
    'api/search' => ['rate' => 100, 'window' => 60],
],
```

## Schema visivo

```
Sistema senza Throttling:
Client A → 1000 req/min → Server → Overload ❌
Client B → 10 req/min → Server → Slow Response ❌

Sistema con Throttling:
Client A → 1000 req/min → Throttler → 10 req/min → Server ✅
Client B → 10 req/min → Throttler → 10 req/min → Server ✅

Strategie di Throttling:
Fixed Window:    [0-60s] 100 req, [60-120s] 100 req
Sliding Window:  [0-60s] 100 req, [1-61s] 100 req
Token Bucket:    Tokens: 100, Refill: 10/sec
Leaky Bucket:    Queue: 100, Process: 10/sec
```

**Flusso di throttling:**
```
Request → Check Rate Limit → Within Limit? → Yes → Process Request
   ↓ No
Check User Type → Apply User Limit → Within Limit? → Yes → Process Request
   ↓ No
Return 429 Too Many Requests
```

## Quando usarlo

Usa Throttling Pattern quando:
- Hai API pubbliche che possono essere abusate
- Vuoi proteggere il sistema da sovraccarico
- Hai risorse limitate da gestire
- Vuoi garantire equità tra utenti
- Stai costruendo servizi per terze parti
- Vuoi prevenire attacchi di forza bruta

**NON usarlo quando:**
- Il sistema ha risorse illimitate
- Non hai problemi di sovraccarico
- Tutti gli utenti hanno accesso equo
- Le richieste sono sempre legittime
- Il sistema è interno e controllato
- Vuoi massima performance senza limiti

## Pro e contro

**I vantaggi:**
- **Protezione**: Previene sovraccarico e abusi
- **Equità**: Garantisce accesso equo alle risorse
- **Stabilità**: Sistema più stabile e prevedibile
- **Sicurezza**: Protegge da attacchi di forza bruta
- **Controllo**: Gestione granulare del traffico
- **Scalabilità**: Sistema più scalabile

**Gli svantaggi:**
- **Complessità**: Aggiunge logica di throttling
- **Configurazione**: Richiede tuning dei limiti
- **False positives**: Può bloccare utenti legittimi
- **Performance**: Overhead per controllo limiti
- **Debugging**: Più difficile tracciare problemi
- **UX**: Può limitare l'esperienza utente

## Esempi di codice

### Pseudocodice
```
class ThrottlingManager {
    constructor(config) {
        this.rateLimits = config.rateLimits
        this.userLimits = config.userLimits
        this.endpointLimits = config.endpointLimits
        this.storage = new RateLimitStorage()
    }
    
    async checkRateLimit(request) {
        const key = this.generateKey(request)
        const limit = this.getLimitForRequest(request)
        
        const currentCount = await this.storage.getCount(key, limit.window)
        
        if (currentCount >= limit.rate) {
            throw new RateLimitExceededException('Rate limit exceeded')
        }
        
        await this.storage.incrementCount(key, limit.window)
        return true
    }
    
    generateKey(request) {
        const userId = request.user?.id || 'anonymous'
        const endpoint = request.path
        return `${userId}:${endpoint}`
    }
    
    getLimitForRequest(request) {
        // Controlla limite per endpoint
        if (this.endpointLimits[request.path]) {
            return this.endpointLimits[request.path]
        }
        
        // Controlla limite per utente
        if (request.user && this.userLimits[request.user.type]) {
            return this.userLimits[request.user.type]
        }
        
        // Usa limite di default
        return this.rateLimits.default
    }
    
    async processRequest(request, handler) {
        try {
            await this.checkRateLimit(request)
            return await handler(request)
        } catch (RateLimitExceededException e) {
            return {
                status: 429,
                message: 'Too Many Requests',
                retryAfter: this.getRetryAfter(request)
            }
        }
    }
    
    getRetryAfter(request) {
        const key = this.generateKey(request)
        const limit = this.getLimitForRequest(request)
        return this.storage.getRetryAfter(key, limit.window)
    }
}

// Utilizzo
throttlingManager = new ThrottlingManager({
    rateLimits: {
        default: { rate: 100, window: 3600 }
    },
    userLimits: {
        free: { rate: 10, window: 60 },
        premium: { rate: 100, window: 60 }
    },
    endpointLimits: {
        'api/payment': { rate: 5, window: 60 },
        'api/upload': { rate: 20, window: 60 }
    }
})

// Middleware
async function throttlingMiddleware(request, next) {
    return await throttlingManager.processRequest(request, next)
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Throttling](./esempio-completo/)** - Sistema e-commerce con throttling per API e servizi

L'esempio include:
- Throttling per API di pagamento, inventario e notifiche
- Rate limiting per tipo di utente
- Throttling per endpoint specifici
- Sliding window e token bucket
- Monitoring e metriche di throttling
- Test per scenari di sovraccarico

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Circuit Breaker Pattern](./08-circuit-breaker/circuit-breaker-pattern.md)** - Protezione da servizi esterni problematici
- **[Bulkhead Pattern](./09-bulkhead/bulkhead-pattern.md)** - Isolamento risorse per prevenire cascading failures
- **[Retry Pattern](./10-retry-pattern/retry-pattern.md)** - Riprova automaticamente le operazioni fallite
- **[Timeout Pattern](./11-timeout-pattern/timeout-pattern.md)** - Gestione timeout per operazioni

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development
- **[Microservices](../00-fondamentali/26-microservices/microservices.md)** - Architettura a microservizi

## Esempi di uso reale

- **E-commerce**: Amazon per throttling su API di pagamento e inventario
- **Social Media**: Twitter per throttling su API e servizi
- **Cloud**: AWS per throttling su servizi e API
- **Banking**: Sistemi bancari per throttling su transazioni
- **IoT**: Sistemi industriali per throttling su sensori

## Anti-pattern

**Cosa NON fare:**
- **Limiti troppo rigidi**: Non impostare limiti troppo bassi per utenti legittimi
- **Limiti troppo permissivi**: Non permettere abusi del sistema
- **Limiti uniformi**: Non usare gli stessi limiti per tutti gli utenti
- **Throttling senza monitoring**: Non monitorare l'utilizzo dei limiti
- **Throttling senza fallback**: Non implementare strategie di fallback
- **Throttling senza configurazione**: Non rendere configurabili i limiti

## Troubleshooting

### Problemi comuni
- **False positives**: Verifica configurazione e ottimizza limiti
- **False negatives**: Controlla che i limiti siano appropriati
- **Performance**: Monitora overhead del throttling
- **Configurazione**: Verifica che i limiti siano corretti
- **Storage**: Controlla che lo storage dei contatori sia efficiente

### Debug e monitoring
- **Rate limit usage**: Traccia utilizzo dei limiti per utente
- **Throttling events**: Monitora eventi di throttling
- **Performance impact**: Misura impatto sui tempi di risposta
- **User experience**: Analizza impatto sull'esperienza utente
- **System load**: Monitora carico del sistema

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead per storage dei contatori
- **CPU**: Controllo overhead per throttling
- **I/O**: Accesso a storage per contatori

### Scalabilità
- **Carico basso**: Overhead non giustificato per sistemi semplici
- **Carico medio**: Benefici iniziano a manifestarsi con API pubbliche
- **Carico alto**: Eccellente protezione da sovraccarico

### Colli di bottiglia
- **Configurazione**: Parametri sbagliati possono causare problemi
- **Storage**: Storage dei contatori può diventare collo di bottiglia
- **Monitoring**: Troppi limiti possono complicare il monitoring

## Risorse utili

### Documentazione ufficiale
- [Rate Limiting - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/rate-limiting) - Documentazione Microsoft
- [Rate Limiting - Google](https://cloud.google.com/architecture/rate-limiting-strategies-techniques) - Guida Google

### Laravel specifico
- [Laravel Rate Limiting](https://laravel.com/docs/rate-limiting) - Rate limiting integrato
- [Laravel Throttle Middleware](https://laravel.com/docs/middleware#throttle) - Middleware per throttling

### Esempi e tutorial
- [Rate Limiting in PHP](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico PHP
- [API Rate Limiting](https://microservices.io/patterns/reliability/rate-limiting.html) - Pattern di resilienza

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
