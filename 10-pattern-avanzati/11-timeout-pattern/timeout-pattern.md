# Timeout Pattern

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

Il Timeout Pattern imposta un limite di tempo massimo per le operazioni, terminando automaticamente quelle che impiegano troppo tempo. Funziona come un "timer" che previene operazioni infinite e protegge il sistema da blocchi.

Pensa a un sistema di pagamenti: se il servizio di pagamento non risponde entro 30 secondi, invece di aspettare indefinitamente, il timeout pattern termina l'operazione e restituisce un errore, permettendo al sistema di continuare a funzionare.

## Perché ti serve

Immagina un'applicazione che deve:
- Chiamare API esterne per dati
- Processare pagamenti online
- Sincronizzare dati tra servizi
- Accedere a database esterni
- Elaborare file di grandi dimensioni

Senza timeout pattern:
- Le operazioni lente bloccano il sistema
- L'utente aspetta indefinitamente
- Le risorse si esauriscono
- Il sistema diventa instabile

Con timeout pattern:
- Operazioni terminate automaticamente
- Sistema reattivo e stabile
- Risorse protette da operazioni infinite
- Migliore esperienza utente

## Come funziona

Il Timeout Pattern implementa diverse strategie di timeout:

### 1. Timeout Semplice
- Timeout fisso per tutte le operazioni
- Terminazione automatica al raggiungimento del limite
- Gestione errori standardizzata

### 2. Timeout Dinamico
- Timeout basato sul tipo di operazione
- Configurazione per servizio specifico
- Adattamento al contesto

### 3. Timeout con Retry
- Timeout per ogni tentativo di retry
- Gestione timeout cumulativi
- Strategie di backoff per timeout

### 4. Timeout con Circuit Breaker
- Timeout integrato con circuit breaker
- Prevenzione timeout eccessivi
- Recupero automatico

**Esempio di configurazione:**
```php
// Timeout semplice
'timeout' => 30000, // 30 secondi

// Timeout dinamico
'services' => [
    'payment' => ['timeout' => 15000],
    'inventory' => ['timeout' => 10000],
    'notification' => ['timeout' => 5000],
],

// Timeout con retry
'retry_timeout' => 5000,
'max_total_timeout' => 60000,
```

## Schema visivo

```
Operazione con Timeout:
Request → Start Timer → Execute → Success 
   ↓
Request → Start Timer → Execute → Timeout 
   ↓
Request → Start Timer → Execute → Retry → Success 

Operazione senza Timeout:
Request → Execute → Success 
   ↓
Request → Execute → Hangs Forever 

Strategie di Timeout:
Simple:     30s per tutte le operazioni
Dynamic:    15s per pagamenti, 10s per inventario
Retry:      5s per tentativo, 60s totale
Circuit:    Timeout + Circuit Breaker
```

**Flusso di timeout:**
```
Operation → Start Timer → Execute → Success? → Yes → Return Result
   ↓ No
Timeout? → Yes → Throw TimeoutException
   ↓ No
Continue → Retry? → Yes → Reset Timer → Retry
   ↓ No
Throw Error
```

## Quando usarlo

Usa Timeout Pattern quando:
- Chiami servizi esterni che possono essere lenti
- Hai operazioni che potrebbero bloccarsi
- Vuoi proteggere il sistema da operazioni infinite
- Hai risorse limitate da gestire
- Vuoi migliorare la reattività del sistema
- Stai implementando retry o circuit breaker

**NON usarlo quando:**
- Le operazioni sono sempre veloci e affidabili
- Hai timeout molto lunghi accettabili
- Le operazioni sono critiche e non possono essere interrotte
- Il sistema è stateless e non ha risorse condivise
- Vuoi fallire velocemente per errori di validazione

## Pro e contro

**I vantaggi:**
- **Protezione**: Previene operazioni infinite e blocchi
- **Reattività**: Sistema più reattivo e stabile
- **Risorse**: Protegge risorse da operazioni lente
- **UX**: Migliore esperienza utente
- **Debugging**: Più facile identificare problemi di performance
- **Scalabilità**: Sistema più scalabile

**Gli svantaggi:**
- **Complessità**: Aggiunge logica di timeout
- **Configurazione**: Richiede tuning dei timeout
- **False positives**: Può terminare operazioni valide
- **Debugging**: Più difficile tracciare timeout
- **Resource usage**: Consuma risorse per timer
- **Testing**: Più difficile testare scenari di timeout

## Esempi di codice

### Pseudocodice
```
class TimeoutManager {
    constructor(config) {
        this.defaultTimeout = config.defaultTimeout
        this.serviceTimeouts = config.serviceTimeouts
        this.maxTotalTimeout = config.maxTotalTimeout
    }
    
    async execute(serviceName, operation, customTimeout = null) {
        const timeout = customTimeout || 
                       this.serviceTimeouts[serviceName] || 
                       this.defaultTimeout
        
        return Promise.race([
            operation(),
            this.createTimeoutPromise(timeout)
        ])
    }
    
    createTimeoutPromise(timeout) {
        return new Promise((_, reject) => {
            setTimeout(() => {
                reject(new TimeoutException(`Operation timed out after ${timeout}ms`))
            }, timeout)
        })
    }
    
    async executeWithRetry(serviceName, operation, retryConfig) {
        const startTime = Date.now()
        let lastError = null
        
        for (let attempt = 1; attempt <= retryConfig.maxAttempts; attempt++) {
            const remainingTime = this.maxTotalTimeout - (Date.now() - startTime)
            
            if (remainingTime <= 0) {
                throw new TimeoutException('Total timeout exceeded')
            }
            
            try {
                return await this.execute(serviceName, operation, retryConfig.timeout)
            } catch (error) {
                lastError = error
                
                if (error instanceof TimeoutException) {
                    if (attempt < retryConfig.maxAttempts) {
                        await this.sleep(retryConfig.delay)
                        continue
                    }
                }
                
                throw error
            }
        }
        
        throw lastError
    }
    
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms))
    }
}

// Utilizzo
timeoutManager = new TimeoutManager({
    defaultTimeout: 30000,
    serviceTimeouts: {
        'payment': 15000,
        'inventory': 10000,
        'notification': 5000
    },
    maxTotalTimeout: 60000
})

try {
    result = await timeoutManager.execute('payment', () => {
        return paymentService.processPayment(data)
    })
} catch (error) {
    if (error instanceof TimeoutException) {
        // Gestisci timeout
        handleTimeout(error)
    } else {
        // Gestisci altri errori
        handleError(error)
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Timeout](./esempio-completo/)** - Sistema e-commerce con timeout per servizi esterni

L'esempio include:
- Timeout per servizi di pagamento, inventario e notifiche
- Timeout dinamici per ogni servizio
- Timeout con retry integrato
- Circuit breaker con timeout
- Monitoring e metriche di timeout
- Test per scenari di timeout e recupero

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Retry Pattern](./10-retry-pattern/retry-pattern.md)** - Riprova automaticamente le operazioni fallite
- **[Circuit Breaker Pattern](./08-circuit-breaker/circuit-breaker-pattern.md)** - Protezione da servizi esterni problematici
- **[Bulkhead Pattern](./09-bulkhead/bulkhead-pattern.md)** - Isolamento risorse per prevenire cascading failures
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite con resilienza

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development
- **[Microservices](../00-fondamentali/26-microservices/microservices.md)** - Architettura a microservizi

## Esempi di uso reale

- **E-commerce**: Amazon per timeout su servizi di pagamento e inventario
- **Banking**: Sistemi bancari per timeout su transazioni critiche
- **Social Media**: Twitter per timeout su API e servizi esterni
- **IoT**: Sistemi industriali per timeout su sensori e dispositivi
- **Cloud**: AWS Lambda per timeout su funzioni e servizi

## Anti-pattern

**Cosa NON fare:**
- **Timeout troppo lunghi**: Non impostare timeout eccessivamente lunghi
- **Timeout troppo corti**: Non impostare timeout troppo corti per operazioni valide
- **Timeout uniformi**: Non usare lo stesso timeout per tutte le operazioni
- **Timeout senza retry**: Non implementare retry per timeout
- **Timeout senza monitoring**: Non monitorare i timeout
- **Timeout senza fallback**: Non implementare strategie di fallback

## Troubleshooting

### Problemi comuni
- **Timeout eccessivi**: Verifica configurazione e ottimizza operazioni
- **Timeout troppo corti**: Aumenta timeout per operazioni valide
- **Timeout inconsistenti**: Standardizza configurazione timeout
- **Resource leaks**: Verifica che i timer vengano puliti correttamente
- **False positives**: Aggiusta timeout per ridurre falsi positivi

### Debug e monitoring
- **Timeout rate**: Traccia tasso di timeout per servizio
- **Average execution time**: Monitora tempi di esecuzione medi
- **Timeout patterns**: Analizza pattern di timeout per ottimizzare
- **Resource usage**: Monitora utilizzo risorse durante timeout
- **Error correlation**: Correla timeout con altri errori

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead minimo per gestione timer
- **CPU**: Controllo overhead per timeout
- **I/O**: Riduce I/O bloccante con timeout

### Scalabilità
- **Carico basso**: Overhead non giustificato per sistemi semplici
- **Carico medio**: Benefici iniziano a manifestarsi con servizi esterni
- **Carico alto**: Eccellente protezione da operazioni lente

### Colli di bottiglia
- **Configurazione**: Parametri sbagliati possono causare problemi
- **Timer management**: Troppi timer possono causare overhead
- **Monitoring**: Troppi timeout possono complicare il monitoring

## Risorse utili

### Documentazione ufficiale
- [Timeout Pattern - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/timeout) - Documentazione Microsoft
- [Timeout Pattern - Martin Fowler](https://martinfowler.com/bliki/CircuitBreaker.html) - Articolo di Martin Fowler

### Laravel specifico
- [Laravel HTTP Client](https://laravel.com/docs/http-client) - Client HTTP con timeout integrato
- [Laravel Queue Timeout](https://laravel.com/docs/queues#timeout) - Timeout per job in coda

### Esempi e tutorial
- [Timeout in PHP](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico PHP
- [Microservices Resilience Patterns](https://microservices.io/patterns/reliability/timeout.html) - Pattern di resilienza

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
