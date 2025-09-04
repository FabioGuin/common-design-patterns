# AI Gateway Pattern

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

L'AI Gateway Pattern ti permette di centralizzare e gestire l'accesso a diversi servizi di intelligenza artificiale attraverso un'unica interfaccia. Funziona come un gateway che nasconde la complessità dei diversi provider AI e fornisce un'API uniforme.

È come avere un centralino telefonico: invece di chiamare direttamente ogni numero, chiami il centralino che ti connette al servizio giusto, gestendo automaticamente routing, autenticazione e fallback.

## Perché ti serve

Immagina di dover integrare ChatGPT, Claude, Gemini e altri servizi AI nella tua applicazione. Senza AI Gateway, finiresti con:

- Codice duplicato per ogni provider AI
- Logica di autenticazione sparsa
- Difficoltà a gestire fallback e retry
- Violazione del principio DRY (Don't Repeat Yourself)
- Difficoltà a monitorare e debuggare

L'AI Gateway risolve questo: un'unica interfaccia per tutti i provider, gestione centralizzata di autenticazione, fallback e monitoring.

## Come funziona

Il meccanismo è elegante:
1. **AIGateway**: Interfaccia principale che espone metodi per le operazioni AI
2. **ProviderInterface**: Interfaccia comune per tutti i provider AI
3. **ConcreteProvider**: Implementazione specifica per ogni provider (OpenAI, Anthropic, Google)
4. **ProviderManager**: Gestisce la selezione e il fallback tra provider
5. **Client**: Usa solo l'AIGateway senza conoscere i dettagli dei provider

Il client invia richieste all'AIGateway, che le instrada al provider appropriato.

## Schema visivo

```
Flusso di richiesta:
Client → AIGateway → ProviderManager → selectProvider()
                    ↓
                               ConcreteProvider → API Call
                    ↓
                               Response → AIGateway → Client

Gestione fallback:
Provider1 (fallisce) → Provider2 (fallisce) → Provider3 (successo)
                    ↓
               AIGateway → Client (risposta)

Architettura:
AIGateway
                    ↓
ProviderManager
                    ↓
OpenAIProvider, ClaudeProvider, GeminiProvider
```

*Il diagramma mostra come l'AIGateway centralizza l'accesso ai provider AI, gestendo automaticamente routing e fallback.*

## Quando usarlo

Usa l'AI Gateway Pattern quando:
- Hai bisogno di integrare più provider AI
- Vuoi centralizzare la gestione delle API AI
- Hai bisogno di fallback automatico tra provider
- Vuoi standardizzare l'autenticazione e il monitoring
- Hai bisogno di gestire rate limiting e throttling
- Vuoi facilitare il testing e il debugging

**NON usarlo quando:**
- Hai solo un provider AI
- L'overhead del pattern non è giustificato
- Hai bisogno di funzionalità specifiche di un provider
- La complessità del gateway supera i benefici

## Pro e contro

**I vantaggi:**
- Centralizza la gestione dei provider AI
- Facilita il fallback e il retry automatico
- Standardizza l'autenticazione e il monitoring
- Migliora la testabilità e il debugging
- Riduce l'accoppiamento con provider specifici
- Facilita l'aggiunta di nuovi provider

**Gli svantaggi:**
- Aumenta la complessità del codice
- Può limitare l'accesso a funzionalità specifiche
- Richiede più classi e interfacce
- Può creare overhead se non implementato correttamente
- Difficile da estendere se i provider cambiano significativamente

## Esempi di codice

### Pseudocodice
```
// Interfaccia per i provider AI
interface AIProvider {
    method generateText(prompt) returns string
    method generateImage(description) returns string
    method isAvailable() returns boolean
}

// Provider OpenAI
class OpenAIProvider implements AIProvider {
    private apiKey
    private client
    
    method generateText(prompt) returns string {
        response = this.client.post("/v1/chat/completions", {
            model: "gpt-4",
            messages: [{"role": "user", "content": prompt}]
        })
        return response.choices[0].message.content
    }
    
    method generateImage(description) returns string {
        response = this.client.post("/v1/images/generations", {
            prompt: description,
            n: 1
        })
        return response.data[0].url
    }
    
    method isAvailable() returns boolean {
        return this.client.healthCheck()
    }
}

// Gateway AI
class AIGateway {
    private providers = []
    private fallbackOrder = []
    
    method addProvider(provider) {
        this.providers.add(provider)
    }
    
    method generateText(prompt) returns string {
        for provider in this.fallbackOrder {
            if provider.isAvailable() {
                try {
                    return provider.generateText(prompt)
                } catch error {
                    log("Provider failed: " + error)
                    continue
                }
            }
        }
        throw new Exception("All providers failed")
    }
    
    method generateImage(description) returns string {
        for provider in this.fallbackOrder {
            if provider.isAvailable() {
                try {
                    return provider.generateImage(description)
                } catch error {
                    log("Provider failed: " + error)
                    continue
                }
            }
        }
        throw new Exception("All providers failed")
    }
}

// Utilizzo
gateway = new AIGateway()
gateway.addProvider(new OpenAIProvider())
gateway.addProvider(new ClaudeProvider())
gateway.addProvider(new GeminiProvider())

text = gateway.generateText("Ciao, come stai?")
image = gateway.generateImage("Un gatto che suona il piano")
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[AI Gateway Completo](./esempio-completo/)** - Sistema completo per gestire multiple API AI

L'esempio include:
- Gateway per OpenAI, Claude, Gemini
- Gestione automatica del fallback
- Rate limiting e throttling
- Monitoring e logging
- Service Provider per Laravel
- Controller con dependency injection
- Test unitari per i provider
- API RESTful per gestire le richieste AI

## Pattern correlati

- **Adapter**: Se hai bisogno di adattare interfacce diverse
- **Facade**: Se hai bisogno di semplificare un'interfaccia complessa
- **Strategy**: Se hai bisogno di cambiare algoritmo di selezione provider
- **Circuit Breaker**: Spesso usato insieme all'AI Gateway per gestire i fallimenti

## Esempi di uso reale

- **Laravel AI Gateway**: Laravel usa l'AI Gateway Pattern per gestire diversi provider AI
- **Symfony AI Bundle**: Symfony usa l'AI Gateway Pattern per integrare servizi AI
- **PHP AI Libraries**: Librerie come OpenAI PHP usano l'AI Gateway Pattern
- **Enterprise AI Platforms**: Piattaforme enterprise usano l'AI Gateway Pattern per gestire multiple API
- **AI Chatbots**: Sistemi di chatbot usano l'AI Gateway Pattern per gestire diversi provider

## Anti-pattern

**Cosa NON fare:**
- **Gateway troppo complessi**: Evita gateway che fanno troppo lavoro, violano il principio di responsabilità singola
- **Provider senza interfacce**: Sempre definire interfacce astratte per i provider
- **Gateway senza fallback**: Implementa sempre il fallback automatico tra provider
- **Gateway senza monitoring**: Aggiungi sempre logging e monitoring per i provider
- **Gateway troppo accoppiati**: Evita gateway che conoscono troppi dettagli dei provider

## Troubleshooting

### Problemi comuni
- **"All providers failed"**: Verifica che almeno un provider sia disponibile e configurato correttamente
- **"Provider not found"**: Controlla che il provider sia registrato correttamente nel gateway
- **"Authentication failed"**: Verifica che le API key siano configurate correttamente
- **"Rate limit exceeded"**: Implementa il rate limiting e il throttling

### Debug e monitoring
- **Log delle richieste**: Aggiungi logging per tracciare ogni richiesta ai provider
- **Controllo provider**: Verifica che i provider siano disponibili e rispondano
- **Performance monitoring**: Monitora il tempo di risposta dei provider
- **Error tracking**: Traccia gli errori per identificare provider problematici

### Metriche utili
- **Numero di richieste per provider**: Per capire l'utilizzo dei diversi provider
- **Tempo di risposta**: Per identificare provider lenti
- **Tasso di successo**: Per identificare provider affidabili
- **Utilizzo fallback**: Per capire quanto spesso viene usato il fallback

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead moderato per il gateway e i provider (tipicamente 20-50KB)
- **CPU**: La gestione del gateway è veloce (1-5ms overhead)
- **I/O**: Le chiamate AI sono I/O intensive, il gateway ottimizza la gestione

### Scalabilità
- **Carico basso**: Perfetto, overhead trascurabile
- **Carico medio**: Funziona bene, il fallback migliora la disponibilità
- **Carico alto**: Essenziale per gestire picchi di utilizzo e fallback automatico

### Colli di bottiglia
- **Provider lenti**: Se un provider è lento, può rallentare tutto il sistema
- **Rate limiting**: Se i provider hanno rate limit bassi, può limitare la scalabilità
- **Network latency**: Le chiamate AI dipendono dalla latenza di rete
- **API costs**: I costi delle API AI possono essere significativi

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Gateway](https://refactoring.guru/design-patterns/gateway) - Spiegazione visuale con esempi

### Laravel specifico
- [Laravel AI Gateway](https://laravel.com/docs/ai) - Come Laravel gestisce i servizi AI
- [Laravel Service Container](https://laravel.com/docs/container) - Per gestire le dipendenze

### Esempi e tutorial
- [AI Gateway Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
- [AI Gateway Best Practices](https://docs.anthropic.com/claude/docs/ai-gateway-patterns) - Best practices per gateway AI

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
