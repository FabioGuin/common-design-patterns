# Retry Pattern

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

Il Retry Pattern riprova automaticamente le operazioni fallite, gestendo temporaneamente i problemi di rete, sovraccarico dei servizi o errori transitori. Funziona come un meccanismo di resilienza che non si arrende al primo fallimento.

Pensa a un sistema di pagamenti: se il servizio di pagamento è temporaneamente sovraccarico, invece di fallire immediatamente, il retry pattern aspetta un po' e riprova, aumentando le possibilità di successo.

## Perché ti serve

Immagina un'applicazione che deve:
- Chiamare API esterne per dati
- Processare pagamenti online
- Sincronizzare dati tra servizi
- Inviare notifiche email
- Accedere a database esterni

Senza retry pattern:
- I fallimenti temporanei diventano errori permanenti
- L'utente deve riprovare manualmente
- Perdi opportunità di business per problemi temporanei
- Il sistema sembra instabile e inaffidabile

Con retry pattern:
- Recupero automatico da errori temporanei
- Migliore esperienza utente
- Maggiore affidabilità del sistema
- Riduzione degli errori percepiti

## Come funziona

Il Retry Pattern implementa diverse strategie di retry:

### 1. Retry Semplice
- Numero fisso di tentativi
- Intervallo fisso tra i tentativi
- Riprova per tutti i tipi di errore

### 2. Retry con Backoff
- Intervalli crescenti tra i tentativi
- Riduce il carico sui servizi sovraccarichi
- Strategie: Linear, Exponential, Jitter

### 3. Retry Selettivo
- Riprova solo per errori specifici
- Ignora errori permanenti (es. 404, 401)
- Gestisce diversi tipi di errore in modo diverso

### 4. Retry con Circuit Breaker
- Si ferma se troppi fallimenti consecutivi
- Previene sovraccarico di servizi down
- Si riattiva dopo un timeout

**Esempio di configurazione:**
```php
// Retry semplice
'max_attempts' => 3,
'delay' => 1000, // millisecondi

// Retry con backoff esponenziale
'max_attempts' => 5,
'base_delay' => 1000,
'max_delay' => 10000,
'multiplier' => 2.0,

// Retry selettivo
'retryable_errors' => [500, 502, 503, 504],
'non_retryable_errors' => [400, 401, 403, 404],
```

## Schema visivo

```
Operazione con Retry:
Request → Execute → Success ✅
   ↓
Request → Execute → Failure ❌
   ↓
Wait (Backoff) → Retry → Success ✅

Operazione senza Retry:
Request → Execute → Failure ❌ → Give Up

Strategie di Backoff:
Linear:    1000ms → 2000ms → 3000ms → 4000ms
Exponential: 1000ms → 2000ms → 4000ms → 8000ms
Jitter:    1000ms → 1500ms → 3000ms → 5500ms
```

**Flusso di retry:**
```
Operation → Success? → Yes → Return Result
   ↓ No
Increment Attempt → Max Attempts? → Yes → Throw Exception
   ↓ No
Calculate Delay → Wait → Retry Operation
```

## Quando usarlo

Usa Retry Pattern quando:
- Chiami servizi esterni che possono essere temporaneamente indisponibili
- Hai operazioni che possono fallire per problemi di rete
- Vuoi migliorare l'affidabilità del sistema
- Hai errori transitori che si risolvono da soli
- Vuoi ridurre l'impatto di picchi di traffico
- Stai implementando circuit breaker o bulkhead

**NON usarlo quando:**
- Gli errori sono sempre permanenti (es. 404, 401)
- Le operazioni sono idempotenti e costose
- Hai timeout molto lunghi
- Il servizio è sempre down
- Vuoi fallire velocemente per errori di validazione

## Pro e contro

**I vantaggi:**
- **Resilienza**: Recupero automatico da errori temporanei
- **Affidabilità**: Migliore esperienza utente
- **Automazione**: Nessun intervento manuale richiesto
- **Flessibilità**: Configurazione per diversi scenari
- **Performance**: Riduce errori percepiti
- **Robustezza**: Sistema più stabile

**Gli svantaggi:**
- **Complessità**: Aggiunge logica di retry
- **Latency**: Aumenta il tempo di risposta
- **Resource Usage**: Consuma più risorse
- **Cascading Failures**: Può peggiorare situazioni già critiche
- **Debugging**: Più difficile tracciare errori
- **Idempotency**: Richiede operazioni idempotenti

## Esempi di codice

### Pseudocodice
```
class RetryManager {
    constructor(config) {
        this.maxAttempts = config.maxAttempts
        this.baseDelay = config.baseDelay
        this.maxDelay = config.maxDelay
        this.multiplier = config.multiplier
        this.retryableErrors = config.retryableErrors
    }
    
    async execute(operation) {
        let lastError = null
        
        for (let attempt = 1; attempt <= this.maxAttempts; attempt++) {
            try {
                return await operation()
            } catch (error) {
                lastError = error
                
                if (!this.shouldRetry(error, attempt)) {
                    throw error
                }
                
                if (attempt < this.maxAttempts) {
                    const delay = this.calculateDelay(attempt)
                    await this.sleep(delay)
                }
            }
        }
        
        throw lastError
    }
    
    shouldRetry(error, attempt) {
        if (attempt >= this.maxAttempts) {
            return false
        }
        
        if (this.retryableErrors && !this.retryableErrors.includes(error.code)) {
            return false
        }
        
        return true
    }
    
    calculateDelay(attempt) {
        const delay = this.baseDelay * Math.pow(this.multiplier, attempt - 1)
        return Math.min(delay, this.maxDelay)
    }
    
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms))
    }
}

// Utilizzo
retryManager = new RetryManager({
    maxAttempts: 3,
    baseDelay: 1000,
    maxDelay: 10000,
    multiplier: 2.0,
    retryableErrors: [500, 502, 503, 504]
})

try {
    result = await retryManager.execute(() => {
        return externalApiCall()
    })
} catch (error) {
    // Gestisci errore finale
    handleError(error)
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Retry](./esempio-completo/)** - Sistema e-commerce con retry per servizi esterni

L'esempio include:
- Retry per servizi di pagamento, inventario e notifiche
- Strategie di backoff diverse per ogni servizio
- Retry selettivo per errori specifici
- Circuit breaker integrato
- Monitoring e metriche di retry
- Test per scenari di fallimento e recupero

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Circuit Breaker Pattern](./08-circuit-breaker/circuit-breaker-pattern.md)** - Protezione da servizi esterni problematici
- **[Bulkhead Pattern](./09-bulkhead/bulkhead-pattern.md)** - Isolamento risorse per prevenire cascading failures
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite con resilienza
- **[Timeout Pattern](./11-timeout-pattern/timeout-pattern.md)** - Gestione timeout per operazioni

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development
- **[Microservices](../00-fondamentali/26-microservices/microservices.md)** - Architettura a microservizi

## Esempi di uso reale

- **E-commerce**: Amazon per retry su servizi di pagamento e inventario
- **Banking**: Sistemi bancari per retry su transazioni critiche
- **Social Media**: Twitter per retry su API e servizi esterni
- **IoT**: Sistemi industriali per retry su sensori e dispositivi
- **Cloud**: AWS Lambda per retry su servizi esterni

## Anti-pattern

**Cosa NON fare:**
- **Retry infinito**: Non riprovare all'infinito senza limiti
- **Retry su errori permanenti**: Non riprovare su 404, 401, 400
- **Retry senza backoff**: Non riprovare immediatamente senza pause
- **Retry su operazioni costose**: Non riprovare su operazioni idempotenti costose
- **Retry senza logging**: Non tracciare i tentativi di retry
- **Retry senza circuit breaker**: Non fermarsi mai anche se il servizio è down

## Troubleshooting

### Problemi comuni
- **Retry infinito**: Verifica che ci sia un limite massimo di tentativi
- **Latency eccessiva**: Controlla strategie di backoff e timeout
- **Resource exhaustion**: Monitora utilizzo risorse durante retry
- **Cascading failures**: Implementa circuit breaker per fermare retry
- **Idempotency issues**: Assicurati che le operazioni siano idempotenti

### Debug e monitoring
- **Retry attempts**: Traccia numero di tentativi per operazione
- **Success rate**: Monitora tasso di successo dopo retry
- **Latency impact**: Misura impatto sui tempi di risposta
- **Error patterns**: Analizza pattern di errori per ottimizzare retry
- **Resource usage**: Monitora utilizzo risorse durante retry

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead minimo per gestione retry
- **CPU**: Controllo overhead per calcolo delay
- **I/O**: Aumenta I/O per tentativi multipli

### Scalabilità
- **Carico basso**: Overhead non giustificato per sistemi semplici
- **Carico medio**: Benefici iniziano a manifestarsi con servizi esterni
- **Carico alto**: Eccellente resilienza e affidabilità

### Colli di bottiglia
- **Configurazione**: Parametri sbagliati possono causare problemi
- **Backoff strategy**: Strategie sbagliate possono aumentare latency
- **Monitoring**: Troppi retry possono complicare il monitoring

## Risorse utili

### Documentazione ufficiale
- [Retry Pattern - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/retry) - Documentazione Microsoft
- [Retry Pattern - Martin Fowler](https://martinfowler.com/bliki/CircuitBreaker.html) - Articolo di Martin Fowler

### Laravel specifico
- [Laravel HTTP Client](https://laravel.com/docs/http-client) - Client HTTP con retry integrato
- [Laravel Queue Retry](https://laravel.com/docs/queues#retrying-failed-jobs) - Retry per job in coda

### Esempi e tutorial
- [Retry in PHP](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico PHP
- [Microservices Resilience Patterns](https://microservices.io/patterns/reliability/retry.html) - Pattern di resilienza

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
