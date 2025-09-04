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

### Implementazione Pratica
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

### Considerazioni Tecniche
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa

L'AI Gateway è come un portiere intelligente che gestisce tutte le comunicazioni tra la tua applicazione e i servizi di intelligenza artificiale. Invece di chiamare direttamente OpenAI, Anthropic, o Google AI, passi attraverso questo gateway che si occupa di routing, autenticazione, rate limiting e fallback automatici.

È il punto di accesso unificato che ti permette di cambiare provider AI senza dover riscrivere tutto il codice.

## Perché ti serve

Immagina di avere un'app che usa ChatGPT per generare contenuti. Un giorno OpenAI ha problemi o aumenta i prezzi, e tu vuoi passare a Claude. Senza un gateway, dovresti:

- Cercare tutte le chiamate a OpenAI nel codice
- Cambiare API key, endpoint, formati di richiesta
- Testare tutto da capo
- Gestire errori diversi per ogni provider

Con l'AI Gateway, cambi solo la configurazione e tutto funziona automaticamente.

## Come funziona

Il gateway funziona come un proxy intelligente:

1. **Riceve la richiesta** dalla tua applicazione in un formato standard
2. **Sceglie il provider** migliore (disponibilità, costo, performance)
3. **Traduce la richiesta** nel formato specifico del provider
4. **Invia la richiesta** al servizio AI
5. **Normalizza la risposta** in un formato standard
6. **Gestisce errori** e fallback automatici

## Schema visivo

```
Scenario 1 (Provider principale disponibile):
App → AI Gateway → OpenAI API
                    ↓
               Risposta normalizzata
                    ↓
               App riceve risultato

Scenario 2 (Provider principale down):
App → AI Gateway → OpenAI API (fallisce)
                    ↓
               Fallback automatico
                    ↓
               AI Gateway → Claude API
                    ↓
               Risposta normalizzata
                    ↓
               App riceve risultato (stesso formato)
```

*Il diagramma mostra come il gateway gestisce automaticamente i fallback e mantiene un'interfaccia consistente.*

## Quando usarlo

Usa l'AI Gateway quando:
- Hai bisogno di integrare più provider AI (OpenAI, Anthropic, Google, etc.)
- Vuoi avere fallback automatici in caso di problemi
- Devi gestire rate limiting e costi diversi
- Vuoi standardizzare le chiamate AI in tutta l'applicazione
- Hai bisogno di logging e monitoring centralizzati

**NON usarlo quando:**
- Usi solo un provider AI e non prevedi di cambiare
- Hai requisiti di performance estremi (ogni millisecondo conta)
- L'applicazione è molto semplice e non giustifica la complessità

## Pro e contro

**I vantaggi:**
- **Flessibilità**: Cambi provider senza toccare il codice business
- **Affidabilità**: Fallback automatici in caso di problemi
- **Costi**: Puoi scegliere il provider più economico per ogni richiesta
- **Monitoring**: Logging centralizzato di tutte le chiamate AI
- **Rate limiting**: Gestione intelligente dei limiti di ogni provider

**Gli svantaggi:**
- **Complessità**: Aggiunge un layer di astrazione
- **Latency**: Piccolo overhead per la traduzione delle richieste
- **Manutenzione**: Devi tenere aggiornate le integrazioni con i provider
- **Debugging**: Può essere più difficile tracciare i problemi

## Esempi di codice

### Esempio base

```php
<?php

interface AIProviderInterface
{
    public function generateText(string $prompt, array $options = []): string;
    public function isAvailable(): bool;
    public function getCost(): float;
}

class OpenAIProvider implements AIProviderInterface
{
    public function generateText(string $prompt, array $options = []): string
    {
        // Chiamata a OpenAI API
        $response = $this->callOpenAI($prompt, $options);
        return $response['choices'][0]['text'];
    }
    
    public function isAvailable(): bool
    {
        // Check disponibilità OpenAI
        return $this->checkHealth();
    }
    
    public function getCost(): float
    {
        return 0.002; // Costo per token
    }
}

class AIGateway
{
    private array $providers = [];
    
    public function addProvider(AIProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }
    
    public function generateText(string $prompt, array $options = []): string
    {
        foreach ($this->providers as $provider) {
            if ($provider->isAvailable()) {
                try {
                    return $provider->generateText($prompt, $options);
                } catch (Exception $e) {
                    // Log errore e prova il prossimo provider
                    continue;
                }
            }
        }
        
        throw new Exception('Nessun provider AI disponibile');
    }
}
```

### Esempio per Laravel

```php
<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AIGatewayService
{
    private array $providers = [];
    private string $defaultProvider;
    
    public function __construct()
    {
        $this->defaultProvider = config('ai.default_provider');
        $this->initializeProviders();
    }
    
    public function generateText(string $prompt, array $options = []): array
    {
        $requestId = uniqid();
        Log::info('AI Request started', ['request_id' => $requestId, 'prompt_length' => strlen($prompt)]);
        
        $providers = $this->getAvailableProviders();
        
        foreach ($providers as $providerName => $provider) {
            try {
                $startTime = microtime(true);
                $result = $provider->generateText($prompt, $options);
                $duration = microtime(true) - $startTime;
                
                Log::info('AI Request completed', [
                    'request_id' => $requestId,
                    'provider' => $providerName,
                    'duration' => $duration,
                    'cost' => $provider->getCost()
                ]);
                
                return [
                    'text' => $result,
                    'provider' => $providerName,
                    'duration' => $duration,
                    'cost' => $provider->getCost()
                ];
                
            } catch (Exception $e) {
                Log::warning('AI Provider failed', [
                    'request_id' => $requestId,
                    'provider' => $providerName,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        throw new Exception('Tutti i provider AI sono indisponibili');
    }
    
    private function getAvailableProviders(): array
    {
        return array_filter($this->providers, function($provider) {
            return $provider->isAvailable();
        });
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema Chat AI Completo](./esempio-completo/)** - Sistema completo di chat con fallback automatici

L'esempio include:
- Integrazione con OpenAI e Anthropic
- Sistema di fallback automatico
- Rate limiting e caching
- Interface utente per testare i provider
- Monitoring e logging completo

## Pattern correlati

- **Adapter Pattern**: Usato per normalizzare le interfacce dei diversi provider AI
- **Strategy Pattern**: Per scegliere dinamicamente il provider migliore
- **Circuit Breaker**: Per gestire i fallimenti dei provider in modo intelligente
- **Proxy Pattern**: Il gateway stesso è un proxy per i servizi AI esterni

## Esempi di uso reale

- **ChatGPT Apps**: Applicazioni che usano più provider per garantire uptime
- **Content Generation**: Sistemi che generano contenuti usando il provider più economico
- **Customer Support**: Bot che passano automaticamente a provider alternativi
- **Research Tools**: Applicazioni che confrontano risposte da provider diversi

## Anti-pattern

**Cosa NON fare:**
- **Hardcoding provider**: Non mettere API key e endpoint direttamente nel codice business
- **Ignorare errori**: Non gestire i fallimenti dei provider può causare crash
- **Senza fallback**: Rimanere bloccati con un solo provider è rischioso
- **Logging insufficiente**: Senza log è impossibile debuggare problemi di integrazione

## Performance e considerazioni

- **Impatto memoria**: Minimo, solo per cache delle configurazioni
- **Impatto CPU**: Basso, principalmente per serializzazione/deserializzazione JSON
- **Scalabilità**: Ottima, può gestire migliaia di richieste concorrenti
- **Colli di bottiglia**: Rate limiting dei provider esterni, non del gateway

## Risorse utili

- [OpenAI API Documentation](https://platform.openai.com/docs) - Documentazione ufficiale OpenAI
- [Anthropic Claude API](https://docs.anthropic.com/) - Documentazione Claude
- [Laravel HTTP Client](https://laravel.com/docs/http-client) - Per le chiamate API
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html) - Per gestire i fallimenti
