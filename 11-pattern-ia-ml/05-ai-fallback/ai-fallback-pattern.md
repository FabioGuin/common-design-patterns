# AI Fallback Pattern

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

L'AI Fallback Pattern ti permette di gestire automaticamente i fallimenti dei servizi di intelligenza artificiale, fornendo alternative e strategie di recupero quando i servizi AI primari non sono disponibili o falliscono.

È come avere un piano B per ogni situazione: se il tuo servizio principale non funziona, hai automaticamente un servizio di backup che prende il suo posto, garantendo che l'applicazione continui a funzionare.

## Perché ti serve

Immagina di dover gestire un'applicazione che dipende da servizi AI per funzionalità critiche. Senza AI Fallback Pattern, finiresti con:

- Applicazione che si blocca quando i servizi AI falliscono
- Esperienza utente degradata per errori AI
- Difficoltà a gestire picchi di utilizzo e rate limiting
- Violazione del principio di resilienza

L'AI Fallback Pattern risolve questo: gestione automatica dei fallimenti, strategie di recupero e degradazione elegante per mantenere l'applicazione funzionante.

## Come funziona

Il meccanismo è robusto:
1. **AIFallbackInterface**: Interfaccia per gestire fallback e recupero
2. **ConcreteAIFallback**: Implementazione specifica per diverse strategie di fallback
3. **FallbackManager**: Coordina le strategie di fallback e recupero
4. **HealthChecker**: Monitora la salute dei servizi AI
5. **CircuitBreaker**: Gestisce l'apertura e chiusura dei circuiti

Il client invia richieste al FallbackManager, che gestisce automaticamente fallimenti e recupero.

## Schema visivo

```
Flusso di richiesta:
Client → FallbackManager → checkHealth()
                        ↓
                   Service Available? → SÌ → AI Service → Response
                        ↓
                   NO → Fallback Strategy → Alternative Response

Gestione fallback:
FallbackManager
    ↓
PrimaryAIService (fallisce) → SecondaryAIService (fallisce) → CachedResponse
                          ↓
                     CircuitBreaker → Open/Closed State
                          ↓
                     HealthChecker → Service Status
```

*Il diagramma mostra come l'AI Fallback Pattern gestisce automaticamente i fallimenti dei servizi AI attraverso strategie di fallback e recupero.*

## Quando usarlo

Usa l'AI Fallback Pattern quando:
- Hai servizi AI critici per l'applicazione
- Vuoi garantire alta disponibilità
- Gestisci picchi di utilizzo e rate limiting
- Hai bisogno di degradazione elegante
- Vuoi migliorare la resilienza dell'applicazione
- Gestisci servizi AI esterni non controllabili

**NON usarlo quando:**
- I servizi AI non sono critici
- L'overhead del pattern non è giustificato
- Hai solo servizi AI interni e controllabili
- La complessità del fallback supera i benefici

## Pro e contro

**I vantaggi:**
- Garantisce alta disponibilità dell'applicazione
- Gestisce automaticamente i fallimenti dei servizi AI
- Fornisce degradazione elegante
- Migliora la resilienza dell'applicazione
- Riduce l'impatto degli errori AI
- Facilita il monitoring e il debugging

**Gli svantaggi:**
- Aumenta significativamente la complessità del codice
- Richiede molte classi e interfacce
- Può creare overhead se non implementato correttamente
- Difficile da testare e debuggare
- Può mascherare problemi reali dei servizi AI

## Esempi di codice

### Pseudocodice
```
// Interfaccia per servizi AI
interface AIServiceInterface {
    method generateText(prompt) returns string
    method isHealthy() returns boolean
    method getPriority() returns integer
}

// Servizio AI primario
class PrimaryAIService implements AIServiceInterface {
    private client
    private priority = 1
    
    method generateText(prompt) returns string {
        response = this.client.post("/v1/chat/completions", {
            model: "gpt-4",
            messages: [{"role": "user", "content": prompt}]
        })
        return response.choices[0].message.content
    }
    
    method isHealthy() returns boolean {
        try {
            this.client.healthCheck()
            return true
        } catch error {
            return false
        }
    }
    
    method getPriority() returns integer {
        return this.priority
    }
}

// Servizio AI secondario
class SecondaryAIService implements AIServiceInterface {
    private client
    private priority = 2
    
    method generateText(prompt) returns string {
        response = this.client.post("/v1/messages", {
            model: "claude-3-sonnet",
            max_tokens: 1000,
            messages: [{"role": "user", "content": prompt}]
        })
        return response.content[0].text
    }
    
    method isHealthy() returns boolean {
        try {
            this.client.healthCheck()
            return true
        } catch error {
            return false
        }
    }
    
    method getPriority() returns integer {
        return this.priority
    }
}

// Circuit Breaker
class CircuitBreaker {
    private state = "CLOSED" // CLOSED, OPEN, HALF_OPEN
    private failureCount = 0
    private lastFailureTime = 0
    private threshold = 5
    private timeout = 60000 // 1 minuto
    
    method canExecute() returns boolean {
        if this.state == "OPEN" {
            if currentTime() - this.lastFailureTime > this.timeout {
                this.state = "HALF_OPEN"
                return true
            }
            return false
        }
        return true
    }
    
    method recordSuccess() {
        this.failureCount = 0
        this.state = "CLOSED"
    }
    
    method recordFailure() {
        this.failureCount++
        this.lastFailureTime = currentTime()
        if this.failureCount >= this.threshold {
            this.state = "OPEN"
        }
    }
}

// Manager per fallback AI
class AIFallbackManager {
    private services = []
    private circuitBreakers = {}
    private cache
    private healthChecker
    
    method addService(service) {
        this.services.add(service)
        this.services.sortByPriority()
        this.circuitBreakers[service] = new CircuitBreaker()
    }
    
    method generateText(prompt) returns string {
        // Prova prima la cache
        cachedResponse = this.cache.retrieve(prompt)
        if cachedResponse != null {
            return cachedResponse
        }
        
        // Prova ogni servizio in ordine di priorità
        for service in this.services {
            circuitBreaker = this.circuitBreakers[service]
            
            if not circuitBreaker.canExecute() {
                continue
            }
            
            if not service.isHealthy() {
                circuitBreaker.recordFailure()
                continue
            }
            
            try {
                response = service.generateText(prompt)
                circuitBreaker.recordSuccess()
                this.cache.store(prompt, response)
                return response
            } catch error {
                circuitBreaker.recordFailure()
                log("Service failed: " + error)
                continue
            }
        }
        
        // Tutti i servizi sono falliti, usa risposta di fallback
        return this.getFallbackResponse(prompt)
    }
    
    method getFallbackResponse(prompt) returns string {
        return "Mi dispiace, il servizio AI non è al momento disponibile. Riprova più tardi."
    }
}

// Utilizzo
manager = new AIFallbackManager()
manager.addService(new PrimaryAIService())
manager.addService(new SecondaryAIService())

response = manager.generateText("Ciao, come stai?")
// Gestisce automaticamente fallimenti e fallback
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[AI Fallback System Completo](./esempio-completo/)** - Sistema completo per gestire fallback AI

L'esempio include:
- Fallback per diversi servizi AI (OpenAI, Claude, Gemini)
- Circuit Breaker per gestire fallimenti
- Health Checker per monitorare i servizi
- Cache per risposte di fallback
- Service Provider per Laravel
- Controller con dependency injection
- Test unitari per il fallback
- API RESTful per gestire il fallback

## Correlati

### Pattern

- **[AI Gateway](./01-ai-gateway/ai-gateway-pattern.md)** - Spesso usato insieme per gestire i provider AI
- **[AI Response Caching](./04-ai-response-caching/ai-response-caching-pattern.md)** - Spesso usato insieme per gestire le risposte AI
- **[Strategy Pattern](../03-pattern-comportamentali/09-strategy/strategy-pattern.md)** - Per implementare diverse strategie di fallback
- **[Template Method](../03-pattern-comportamentali/10-template-method/template-method-pattern.md)** - Per definire il template di fallback

### Principi e Metodologie

- **[DRY Pattern](../12-pattern-metodologie-concettuali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../12-pattern-metodologie-concettuali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../12-pattern-metodologie-concettuali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../12-pattern-metodologie-concettuali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Laravel AI Fallback System**: Laravel usa l'AI Fallback Pattern per gestire fallimenti AI
- **Symfony AI Bundle**: Symfony usa l'AI Fallback Pattern per integrare fallback AI
- **PHP AI Libraries**: Librerie come OpenAI PHP usano l'AI Fallback Pattern
- **Enterprise AI Platforms**: Piattaforme enterprise usano l'AI Fallback Pattern per gestire fallimenti
- **AI Chatbots**: Sistemi di chatbot usano l'AI Fallback Pattern per gestire conversazioni

## Anti-pattern

**Cosa NON fare:**
- **Fallback troppo complessi**: Evita fallback che fanno troppo lavoro, violano il principio di responsabilità singola
- **Fallback senza monitoring**: Aggiungi sempre logging e monitoring per i fallback
- **Fallback senza circuit breaker**: Implementa sempre il circuit breaker per gestire fallimenti
- **Fallback senza health check**: Monitora sempre la salute dei servizi
- **Fallback troppo accoppiati**: Evita fallback che conoscono troppi dettagli dei servizi

## Troubleshooting

### Problemi comuni
- **"All services failed"**: Verifica che almeno un servizio sia disponibile e configurato correttamente
- **"Circuit breaker stuck open"**: Controlla che il timeout del circuit breaker sia configurato correttamente
- **"Health check failing"**: Verifica che i servizi AI siano raggiungibili e funzionanti
- **"Fallback not working"**: Controlla che le strategie di fallback siano implementate correttamente

### Debug e monitoring
- **Log dei fallback**: Aggiungi logging per tracciare ogni fallback e recupero
- **Controllo servizi**: Verifica che i servizi AI siano disponibili e rispondano
- **Performance fallback**: Monitora il tempo di fallback e recupero
- **Error tracking**: Traccia gli errori per identificare servizi problematici

### Metriche utili
- **Tasso di fallback**: Per capire quanto spesso viene usato il fallback
- **Tempo di recupero**: Per identificare servizi lenti a recuperare
- **Disponibilità servizi**: Per identificare servizi più affidabili
- **Utilizzo circuit breaker**: Per capire quanto spesso il circuit breaker si apre

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead moderato per il fallback e i circuit breaker (tipicamente 40-80KB)
- **CPU**: La gestione del fallback è veloce (2-10ms overhead)
- **I/O**: Il fallback può ridurre le chiamate I/O ai servizi AI

### Scalabilità
- **Carico basso**: Perfetto, overhead trascurabile
- **Carico medio**: Funziona bene, il fallback migliora la disponibilità
- **Carico alto**: Essenziale per gestire picchi di utilizzo e fallimenti

### Colli di bottiglia
- **Servizi lenti**: Se i servizi AI sono lenti, può rallentare tutto il sistema
- **Circuit breaker**: Se il circuit breaker si apre troppo spesso, può limitare la disponibilità
- **Health check**: Se il health check è lento, può rallentare il fallback
- **Network latency**: Le chiamate AI dipendono dalla latenza di rete

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Circuit Breaker](https://refactoring.guru/design-patterns/circuit-breaker) - Spiegazione visuale con esempi

### Laravel specifico
- [Laravel AI Fallback System](https://laravel.com/docs/ai) - Come Laravel gestisce il fallback AI
- [Laravel Service Container](https://laravel.com/docs/container) - Per gestire le dipendenze

### Esempi e tutorial
- [AI Fallback Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
- [Graceful Degradation Strategies](https://docs.microsoft.com/en-us/azure/architecture/patterns/gateway-aggregation) - Strategie di degradazione elegante

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
