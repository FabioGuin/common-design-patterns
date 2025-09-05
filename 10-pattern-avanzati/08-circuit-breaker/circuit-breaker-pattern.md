# Circuit Breaker Pattern

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

Il Circuit Breaker Pattern protegge il tuo sistema da chiamate a servizi esterni che potrebbero essere lenti o non rispondere. Funziona come un interruttore elettrico: quando rileva troppi fallimenti, "apre il circuito" e blocca le chiamate per un periodo di tempo, permettendo al servizio di recuperare.

Pensa a un sistema di pagamenti: se il servizio di pagamento è down, invece di aspettare 30 secondi per ogni richiesta, il circuit breaker blocca immediatamente le chiamate e restituisce un errore veloce, proteggendo la tua applicazione.

## Perché ti serve

Immagina un e-commerce che deve chiamare:
- Servizio di pagamento per processare ordini
- Servizio di inventario per verificare disponibilità
- Servizio di notifiche per inviare email
- Servizio di spedizione per calcolare costi

Se uno di questi servizi è lento o down, senza circuit breaker:
- Le tue pagine si bloccano per 30 secondi
- Gli utenti abbandonano il sito
- Il server si sovraccarica di richieste in attesa
- L'esperienza utente diventa terribile

Con circuit breaker:
- Fallimenti rapidi e chiari
- Sistema resiliente e reattivo
- Protezione automatica da servizi problematici
- Fallback graceful quando possibile

## Come funziona

Il Circuit Breaker ha tre stati principali:

### 1. Closed (Chiuso) - Stato Normale
- Le chiamate passano normalmente
- Conta i fallimenti
- Se i fallimenti superano la soglia, passa a Open

### 2. Open (Aperto) - Stato di Protezione
- Blocca tutte le chiamate
- Restituisce errore immediatamente
- Dopo un timeout, passa a Half-Open

### 3. Half-Open (Semi-Aperto) - Stato di Test
- Permette una chiamata di test
- Se la chiamata riesce, torna a Closed
- Se fallisce, torna a Open

**Parametri configurabili:**
- **Failure Threshold**: Numero di fallimenti per aprire il circuito
- **Timeout**: Tempo di attesa prima di testare di nuovo
- **Success Threshold**: Numero di successi per chiudere il circuito

## Schema visivo

```
Stato Closed (Normale):
Request → Circuit Breaker → External Service
   ↓           ↓                    ↓
Success ← Success Response ← Success
   ↓
Failure Count++

Stato Open (Protezione):
Request → Circuit Breaker → [BLOCKED]
   ↓           ↓
Fast Fail ← Error Response

Stato Half-Open (Test):
Request → Circuit Breaker → External Service (Test)
   ↓           ↓                    ↓
Success ← Success Response ← Success
   ↓
Reset to Closed

Fallimento:
Request → Circuit Breaker → External Service (Test)
   ↓           ↓                    ↓
Failure ← Error Response ← Failure
   ↓
Reset to Open
```

**Flusso temporale:**
```
Time: 0s    → 5s    → 10s   → 15s   → 20s
State: Closed → Open → Open → Half-Open → Closed
```

## Quando usarlo

Usa Circuit Breaker quando:
- Chiami servizi esterni (API, database, file system)
- I servizi esterni possono essere lenti o non rispondere
- Vuoi proteggere la tua applicazione da cascading failures
- Hai bisogno di fallback graceful quando i servizi sono down
- Vuoi migliorare la resilienza del sistema
- Stai costruendo microservizi con comunicazione tra servizi

**NON usarlo quando:**
- Le chiamate sono sempre locali e veloci
- Non hai servizi esterni da chiamare
- I fallimenti sono accettabili e non impattano l'utente
- Il servizio esterno è sempre affidabile
- Le chiamate sono sincrone e bloccanti per design

## Pro e contro

**I vantaggi:**
- **Protezione**: Previene cascading failures e sovraccarico
- **Performance**: Fallimenti rapidi invece di timeout lunghi
- **Resilienza**: Sistema più robusto e reattivo
- **Fallback**: Permette di implementare alternative graceful
- **Monitoring**: Facilita il monitoraggio dello stato dei servizi
- **Auto-recovery**: Recupero automatico quando i servizi tornano online

**Gli svantaggi:**
- **Complessità**: Aggiunge complessità al sistema
- **Configurazione**: Richiede tuning dei parametri
- **False positives**: Può bloccare servizi che funzionano
- **Latency**: Aggiunge piccola latenza per il controllo stato
- **Memory**: Usa memoria per tracciare stato e metriche
- **Testing**: Più difficile testare scenari di fallimento

## Esempi di codice

### Pseudocodice
```
class CircuitBreaker {
    private state = 'CLOSED'
    private failureCount = 0
    private lastFailureTime = null
    private failureThreshold = 5
    private timeout = 60000 // 60 secondi
    
    async call(serviceFunction) {
        if (this.state === 'OPEN') {
            if (this.shouldAttemptReset()) {
                this.state = 'HALF_OPEN'
            } else {
                throw new CircuitBreakerOpenException()
            }
        }
        
        try {
            result = await serviceFunction()
            this.onSuccess()
            return result
        } catch (error) {
            this.onFailure()
            throw error
        }
    }
    
    onSuccess() {
        this.failureCount = 0
        this.state = 'CLOSED'
    }
    
    onFailure() {
        this.failureCount++
        this.lastFailureTime = now()
        
        if (this.failureCount >= this.failureThreshold) {
            this.state = 'OPEN'
        }
    }
    
    shouldAttemptReset() {
        return now() - this.lastFailureTime > this.timeout
    }
}

// Utilizzo
circuitBreaker = new CircuitBreaker()
try {
    result = await circuitBreaker.call(() => paymentService.processPayment())
} catch (error) {
    if (error instanceof CircuitBreakerOpenException) {
        // Usa fallback o mostra messaggio di errore
        return fallbackPayment()
    }
    throw error
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Circuit Breaker](./esempio-completo/)** - Sistema e-commerce con protezione servizi esterni

L'esempio include:
- Circuit Breaker per servizi di pagamento
- Circuit Breaker per servizi di inventario
- Circuit Breaker per servizi di notifiche
- Fallback strategies per ogni servizio
- Monitoring e dashboard per stato circuit breaker
- Test per scenari di fallimento e recupero

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Retry Pattern](./10-retry-pattern/retry-pattern.md)** - Riprova automaticamente le operazioni fallite
- **[Bulkhead Pattern](./09-bulkhead/bulkhead-pattern.md)** - Isolamento risorse per prevenire cascading failures
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite con resilienza
- **[Timeout Pattern](./11-timeout-pattern/timeout-pattern.md)** - Gestione timeout per operazioni

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development
- **[Microservices](../00-fondamentali/26-microservices/microservices.md)** - Architettura a microservizi

## Esempi di uso reale

- **E-commerce**: Amazon per proteggere chiamate a servizi di pagamento e inventario
- **Banking**: Sistemi bancari per chiamate a servizi di verifica e autorizzazione
- **Social Media**: Twitter per chiamate a servizi di notifiche e feed
- **IoT**: Sistemi industriali per chiamate a sensori e dispositivi
- **Cloud**: AWS Lambda per chiamate a servizi esterni

## Anti-pattern

**Cosa NON fare:**
- **Configurazione sbagliata**: Non impostare soglie troppo basse o timeout troppo lunghi
- **Ignorare fallback**: Non implementare strategie di fallback appropriate
- **Monitoring insufficiente**: Non monitorare lo stato dei circuit breaker
- **Test insufficienti**: Non testare scenari di fallimento e recupero
- **Over-engineering**: Non usare circuit breaker per operazioni semplici

## Troubleshooting

### Problemi comuni
- **Circuit sempre aperto**: Verifica configurazione e soglie di fallimento
- **Circuit sempre chiuso**: Controlla se i fallimenti vengono rilevati correttamente
- **False positives**: Aggiusta soglie e timeout per ridurre falsi positivi
- **Performance degradate**: Verifica overhead del circuit breaker
- **Fallback non funzionanti**: Testa le strategie di fallback

### Debug e monitoring
- **Circuit state monitoring**: Traccia stato di ogni circuit breaker
- **Failure rate tracking**: Monitora tasso di fallimenti per servizio
- **Recovery time**: Misura tempo di recupero dei servizi
- **Fallback usage**: Traccia utilizzo delle strategie di fallback
- **Alerting**: Configura alert per circuit aperti

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead minimo per tracciare stato e metriche
- **CPU**: Controllo stato aggiunge piccola latenza
- **I/O**: Riduce I/O bloccante con fallimenti rapidi

### Scalabilità
- **Carico basso**: Overhead non giustificato per sistemi semplici
- **Carico medio**: Benefici iniziano a manifestarsi con servizi esterni
- **Carico alto**: Eccellente protezione da cascading failures

### Colli di bottiglia
- **Configurazione**: Parametri sbagliati possono causare problemi
- **Monitoring**: Troppi circuit breaker possono complicare il monitoring
- **Fallback**: Strategie di fallback complesse possono diventare collo di bottiglia

## Risorse utili

### Documentazione ufficiale
- [Circuit Breaker Pattern - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/circuit-breaker) - Documentazione Microsoft
- [Circuit Breaker Pattern - Martin Fowler](https://martinfowler.com/bliki/CircuitBreaker.html) - Articolo di Martin Fowler

### Laravel specifico
- [Laravel Circuit Breaker Package](https://github.com/spatie/laravel-circuit-breaker) - Package Spatie per Circuit Breaker
- [Laravel HTTP Client](https://laravel.com/docs/http-client) - Client HTTP con retry e timeout

### Esempi e tutorial
- [Circuit Breaker in PHP](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico PHP
- [Microservices Resilience Patterns](https://microservices.io/patterns/reliability/circuit-breaker.html) - Pattern di resilienza

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
