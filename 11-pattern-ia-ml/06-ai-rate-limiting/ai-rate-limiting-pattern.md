# AI Rate Limiting Pattern

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

Il pattern AI Rate Limiting controlla quante richieste AI un utente o sistema può fare in un determinato periodo di tempo. È come un semaforo intelligente che regola il traffico delle chiamate AI per evitare sovraccarichi e costi eccessivi.

## Perché ti serve

Immagina di avere un'app che usa AI per generare contenuti. Senza rate limiting:
- Un utente potrebbe fare migliaia di richieste al minuto
- I costi AI si moltiplicano all'infinito
- Il servizio AI si blocca per tutti
- L'esperienza utente diventa pessima

Il rate limiting ti protegge da:
- **Costi esplosivi**: Evita che un bug o un utente malintenzionato ti faccia pagare migliaia di euro
- **Sovraccarico servizi**: Mantiene il sistema AI stabile per tutti
- **Abusi**: Previene l'uso eccessivo da parte di singoli utenti
- **Conformità**: Rispetta i limiti dei provider AI

## Come funziona

1. **Tracciamento**: Il sistema conta le richieste per ogni utente/API key
2. **Controllo limiti**: Prima di ogni richiesta, verifica se l'utente ha superato i limiti
3. **Decisione**: Se il limite è raggiunto, blocca o mette in coda la richiesta
4. **Reset temporale**: I contatori si azzerano dopo il periodo definito (es: ogni ora)
5. **Notifica**: L'utente viene informato del limite raggiunto

## Schema visivo

```
Richiesta AI → [Rate Limiter] → Controllo Limite
                    ↓
              Limite OK? → SÌ → Invia a AI Provider
                    ↓
                   NO → Blocca/Attendi/Queue
                    ↓
              [Reset Timer] → Azzera contatori
```

**Flusso dettagliato:**
```
Utente → API → Rate Limiter → Cache/DB (contatori)
                    ↓
              Verifica: richieste < limite?
                    ↓
              SÌ: Invia richiesta AI
              NO: Ritorna errore 429
                    ↓
              Aggiorna contatori
```

## Quando usarlo

Usa l'AI Rate Limiting quando:
- Hai costi AI variabili per richiesta
- Vuoi proteggere il budget da abusi
- Il provider AI ha limiti di chiamate
- Hai utenti con piani diversi (free, premium, enterprise)
- Vuoi garantire equità nell'uso delle risorse
- Hai bisogno di controllare il carico sui servizi AI

**NON usarlo quando:**
- Hai un'app interna con utenti fidati
- I costi AI sono fissi e bassi
- Il volume di richieste è sempre molto basso
- Non hai budget per implementare il sistema

## Pro e contro

**I vantaggi:**
- **Controllo costi**: Previene spese impreviste
- **Stabilità**: Mantiene il servizio AI disponibile
- **Flessibilità**: Puoi definire limiti diversi per utenti diversi
- **Protezione**: Ti protegge da attacchi DDoS o abusi
- **Conformità**: Rispetta i termini dei provider AI

**Gli svantaggi:**
- **Complessità**: Aggiunge logica di controllo al sistema
- **Latency**: Può aggiungere piccoli ritardi alle richieste
- **Storage**: Richiede memorizzazione dei contatori
- **Falsi positivi**: Utenti legittimi potrebbero essere bloccati
- **Manutenzione**: Richiede tuning dei parametri

## Esempi di codice

### Pseudocodice
```
class AIRateLimiter {
    private cache = Redis
    private limits = {
        'free': 100,      // 100 richieste/ora
        'premium': 1000,  // 1000 richieste/ora
        'enterprise': 10000
    }
    
    function checkLimit(userId, plan) {
        key = "rate_limit:" + userId + ":" + currentHour()
        currentCount = cache.get(key) || 0
        limit = limits[plan]
        
        if (currentCount >= limit) {
            return false  // Limite raggiunto
        }
        
        cache.increment(key)
        cache.expire(key, 3600)  // 1 ora
        return true
    }
    
    function makeAIRequest(prompt, userId, plan) {
        if (!checkLimit(userId, plan)) {
            throw new RateLimitExceededException()
        }
        
        return aiProvider.generate(prompt)
    }
}
```


## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[AI Rate Limiting Completo](./esempio-completo/)** - Sistema completo di rate limiting per AI con dashboard, statistiche e gestione utenti

L'esempio include:
- Rate limiting per piani utente diversi
- Dashboard per monitorare l'uso
- Sistema di notifiche per limiti raggiunti
- Cache Redis per performance
- API per controllare i limiti
- Logging e analytics dell'uso

## Correlati

### Pattern

- **[AI Gateway](./01-ai-gateway/ai-gateway-pattern.md)** - Il rate limiting spesso si integra con un gateway AI
- **[AI Fallback](./05-ai-fallback/ai-fallback-pattern.md)** - Quando i limiti sono raggiunti, puoi usare servizi alternativi
- **[AI Response Caching](./04-ai-response-caching/ai-response-caching-pattern.md)** - Riduce le richieste duplicate e aiuta con i limiti
- **[Strategy Pattern](../03-pattern-comportamentali/09-strategy/strategy-pattern.md)** - Per implementare diverse strategie di rate limiting

### Principi e Metodologie

- **[DRY Pattern](../12-pattern-metodologie-concettuali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../12-pattern-metodologie-concettuali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../12-pattern-metodologie-concettuali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../12-pattern-metodologie-concettuali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **OpenAI API**: Ha rate limiting basato su tier di account
- **ChatGPT**: Limita le richieste per utente per evitare abusi
- **GitHub Copilot**: Controlla l'uso per mantenere la qualità
- **Midjourney**: Ha limiti diversi per piani di abbonamento
- **Stable Diffusion APIs**: Rate limiting per controllare i costi

## Anti-pattern

**Cosa NON fare:**
- **Rate limiting troppo rigido**: Bloccare utenti legittimi
- **Nessun rate limiting**: Lasciare il sistema aperto agli abusi
- **Limiti fissi per tutti**: Non considerare i diversi piani utente
- **Dimenticare il reset**: I contatori devono azzerarsi periodicamente
- **Ignorare i burst**: Gestire picchi temporanei di richieste
- **Rate limiting solo lato client**: Sempre validare lato server

## Troubleshooting

### Problemi comuni
- **Limiti troppo bassi**: Gli utenti vengono bloccati troppo spesso
  - *Soluzione*: Analizza l'uso reale e aggiusta i parametri
- **Race conditions**: Richieste simultanee superano i limiti
  - *Soluzione*: Usa operazioni atomiche (INCR in Redis)
- **Cache scaduta**: I contatori si perdono prima del reset
  - *Soluzione*: Imposta TTL più lunghi o usa database persistente
- **Falsi positivi**: Utenti legittimi bloccati
  - *Soluzione*: Implementa whitelist o limiti più alti per utenti fidati

### Debug e monitoring
- **Metriche da tracciare**: Richieste totali, richieste bloccate, utenti attivi
- **Alerting**: Notifica quando i limiti vengono raggiunti frequentemente
- **Dashboard**: Visualizza l'uso in tempo reale per ogni utente
- **Logs**: Registra ogni decisione di rate limiting per debugging

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Cache Redis per contatori (circa 1KB per utente/ora)
- **CPU**: Operazioni atomiche su cache (minimo impatto)
- **I/O**: Lettura/scrittura su Redis per ogni richiesta

### Scalabilità
- **Carico basso**: Funziona perfettamente con poche centinaia di utenti
- **Carico medio**: Redis gestisce migliaia di contatori simultanei
- **Carico alto**: Considera sharding Redis o database distribuiti

### Colli di bottiglia
- **Cache Redis**: Punto singolo di fallimento per il rate limiting
- **Operazioni atomiche**: INCR può diventare lento con molti utenti
- **Reset temporale**: Tutti i contatori si azzerano contemporaneamente

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Rate Limiting](https://laravel.com/docs/rate-limiting) - Rate limiting nativo Laravel

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Redis Rate Limiting](https://redis.io/docs/manual/patterns/distributed-locks/) - Pattern per rate limiting
- [OpenAI Rate Limits](https://platform.openai.com/docs/guides/rate-limits) - Esempi reali
- [AI API Best Practices](https://platform.openai.com/docs/guides/production-best-practices) - Pratiche OpenAI

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
