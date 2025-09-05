# Load Balancer Pattern

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

Il Load Balancer Pattern distribuisce il carico di lavoro tra più server o istanze, agendo come un "distributore intelligente" che decide quale server utilizzare per ogni richiesta. È come avere un direttore d'orchestra che coordina i musicisti per ottenere la migliore performance.

Pensa a un ristorante con più cuochi: il load balancer è il maitre che decide quale cuoco assegnare a ogni ordine, considerando il carico di lavoro, le specialità e la disponibilità di ognuno.

## Perché ti serve

Immagina un'applicazione che deve:
- Gestire migliaia di richieste simultanee
- Mantenere alta disponibilità
- Scalare orizzontalmente
- Ottimizzare le risorse
- Gestire picchi di traffico

Senza load balancer:
- Un singolo server si sovraccarica
- I tempi di risposta degradano
- Il sistema diventa instabile
- Non puoi sfruttare più server
- I picchi di traffico causano downtime

Con load balancer:
- Il carico viene distribuito equamente
- Le performance rimangono costanti
- Il sistema è più resiliente
- Puoi aggiungere server facilmente
- I picchi vengono gestiti automaticamente

## Come funziona

1. **Ricezione richieste**: Il load balancer riceve tutte le richieste in arrivo
2. **Valutazione server**: Analizza lo stato e il carico di ogni server disponibile
3. **Selezione server**: Sceglie il server migliore secondo l'algoritmo configurato
4. **Inoltro richiesta**: Invia la richiesta al server selezionato
5. **Gestione risposta**: Restituisce la risposta al client
6. **Monitoraggio**: Tiene traccia dello stato di ogni server

## Schema visivo

```
Client → Load Balancer → Server 1 (30% carico)
    ↓                    → Server 2 (25% carico)
    ↓                    → Server 3 (45% carico)
    ↓
Risposta ← Server selezionato

Algoritmi di distribuzione:
- Round Robin: 1→2→3→1→2→3...
- Least Connections: Server con meno connessioni attive
- Weighted: Server con peso diverso
- IP Hash: Stesso server per stesso IP
```

## Quando usarlo

Usa il Load Balancer Pattern quando:
- Hai più server o istanze dell'applicazione
- Il traffico supera la capacità di un singolo server
- Vuoi migliorare la disponibilità del sistema
- Hai bisogno di scalabilità orizzontale
- Vuoi distribuire il carico geograficamente
- Hai requisiti di performance elevati

**NON usarlo quando:**
- Hai un singolo server
- Il traffico è molto basso
- Non hai budget per più server
- L'applicazione non è stateless
- Hai dipendenze tra le richieste
- La latenza aggiuntiva è inaccettabile

## Pro e contro

**I vantaggi:**
- Distribuzione equa del carico
- Miglioramento della disponibilità
- Scalabilità orizzontale
- Gestione automatica dei picchi
- Possibilità di manutenzione senza downtime
- Ottimizzazione delle risorse

**Gli svantaggi:**
- Complessità di configurazione
- Punto singolo di fallimento (se non ridondato)
- Latenza aggiuntiva
- Costi per hardware/software aggiuntivo
- Gestione della sessione più complessa
- Debugging più difficile

## Esempi di codice

### Pseudocodice
```
class LoadBalancer {
    private servers = []
    private algorithm
    private healthChecker
    
    function addServer(server) {
        servers.add(server)
        healthChecker.monitor(server)
    }
    
    function removeServer(server) {
        servers.remove(server)
        healthChecker.stopMonitoring(server)
    }
    
    function routeRequest(request) {
        availableServers = servers.filter(server => server.isHealthy())
        
        if (availableServers.isEmpty()) {
            throw new NoAvailableServersException()
        }
        
        selectedServer = algorithm.selectServer(availableServers, request)
        return selectedServer.handleRequest(request)
    }
}

class RoundRobinAlgorithm {
    private currentIndex = 0
    
    function selectServer(servers, request) {
        server = servers[currentIndex]
        currentIndex = (currentIndex + 1) % servers.length
        return server
    }
}

// Utilizzo
loadBalancer = new LoadBalancer()
loadBalancer.addServer(new Server("server1:8080"))
loadBalancer.addServer(new Server("server2:8080"))
loadBalancer.addServer(new Server("server3:8080"))

response = loadBalancer.routeRequest(request)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Load Balancer Laravel](./esempio-completo/)** - Sistema di load balancing per applicazioni Laravel

L'esempio include:
- Configurazione di più istanze Laravel
- Algoritmi di distribuzione del carico
- Health checking automatico
- Gestione delle sessioni
- Monitoraggio delle performance
- Configurazione Nginx per load balancing

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Circuit Breaker Pattern](./08-circuit-breaker/circuit-breaker-pattern.md)** - Gestione fallimenti per servizi esterni
- **[Retry Pattern](./10-retry-pattern/retry-pattern.md)** - Riprova automaticamente operazioni fallite
- **[Timeout Pattern](./11-timeout-pattern/timeout-pattern.md)** - Gestione timeout per operazioni
- **[API Gateway Pattern](./21-api-gateway/api-gateway-pattern.md)** - Punto di ingresso unificato per API
- **[Service Discovery Pattern](./23-service-discovery/service-discovery-pattern.md)** - Scoperta automatica dei servizi

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Siti web ad alto traffico**: Distribuzione del carico tra server web
- **API pubbliche**: Bilanciamento delle richieste tra istanze
- **Microservizi**: Distribuzione del carico tra servizi
- **Database**: Load balancing per cluster di database
- **CDN**: Distribuzione geografica del contenuto
- **Gaming**: Bilanciamento del carico per server di gioco

## Anti-pattern

**Cosa NON fare:**
- Usare un singolo load balancer senza ridondanza
- Non implementare health checking
- Ignorare la gestione delle sessioni
- Non monitorare le performance dei server
- Usare algoritmi inappropriati per il caso d'uso
- Non considerare la latenza geografica

## Troubleshooting

### Problemi comuni
- **Server non raggiungibili**: Verifica la configurazione di rete e firewall
- **Distribuzione non uniforme**: Controlla l'algoritmo di distribuzione
- **Sessioni perse**: Implementa sessioni condivise o sticky sessions
- **Performance degradate**: Monitora il carico e aggiungi server
- **Health check falliti**: Verifica la configurazione degli health check

### Debug e monitoring
- Monitora il carico di ogni server
- Traccia i tempi di risposta
- Controlla lo stato degli health check
- Misura la latenza del load balancer
- Implementa alert per server down
- Analizza i pattern di traffico

## Performance e considerazioni

### Impatto sulle risorse
- **CPU**: Overhead minimo per la distribuzione
- **Memoria**: Cache per sessioni e configurazioni
- **Rete**: Latenza aggiuntiva per il routing
- **I/O**: Monitoraggio continuo dei server

### Scalabilità
- **Carico basso**: Performance eccellenti, overhead minimo
- **Carico medio**: Buone performance con distribuzione uniforme
- **Carico alto**: Gestisce bene i picchi con server aggiuntivi

### Colli di bottiglia
- **Load balancer**: Può diventare un collo di bottiglia se non ridondato
- **Health check lenti**: Possono impattare la distribuzione
- **Sessioni non condivise**: Limitano la flessibilità di distribuzione
- **Configurazione errata**: Può causare distribuzione non uniforme

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Horizon](https://laravel.com/docs/horizon) - Monitoraggio code
- [Laravel Octane](https://laravel.com/docs/octane) - Performance boost

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Nginx Load Balancing](https://nginx.org/en/docs/http/load_balancing.html) - Configurazione Nginx
- [HAProxy Documentation](https://www.haproxy.org/#docs) - Load balancer avanzato

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
