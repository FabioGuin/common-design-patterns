# Service Discovery Pattern

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

Il Service Discovery Pattern permette ai servizi di trovare e comunicare tra loro in modo dinamico senza dover conoscere a priori gli indirizzi di rete. Funziona come un registro centralizzato che mantiene informazioni sui servizi disponibili e le loro posizioni.

Pensa a un elenco telefonico: invece di dover ricordare tutti i numeri di telefono, cerchi il nome della persona e trovi il numero. Il Service Discovery fa la stessa cosa per i servizi: invece di hardcodare gli indirizzi, cerchi il nome del servizio e ottieni l'indirizzo.

## Perché ti serve

Immagina un'applicazione che deve:
- Gestire servizi che cambiano posizione dinamicamente
- Bilanciare il carico tra istanze multiple di servizi
- Gestire failover automatico quando un servizio non è disponibile
- Scalare orizzontalmente aggiungendo nuove istanze
- Gestire servizi in ambienti cloud o containerizzati
- Evitare hardcoding degli indirizzi dei servizi

Senza service discovery:
- I servizi devono conoscere gli indirizzi hardcodati
- Il failover è manuale e complesso
- Il bilanciamento del carico è difficile
- L'aggiunta di nuove istanze richiede configurazione manuale
- I servizi non possono spostarsi dinamicamente
- La gestione degli ambienti è complessa

Con service discovery:
- I servizi si registrano automaticamente
- Il failover è automatico e trasparente
- Il bilanciamento del carico è automatico
- L'aggiunta di nuove istanze è trasparente
- I servizi possono spostarsi dinamicamente
- La gestione degli ambienti è semplificata

## Come funziona

1. **Registrazione**: I servizi si registrano nel registry al startup
2. **Heartbeat**: I servizi inviano heartbeat per confermare la disponibilità
3. **Scoperta**: I client cercano servizi nel registry
4. **Risoluzione**: Il registry restituisce l'indirizzo del servizio
5. **Bilanciamento**: Il registry può restituire istanze multiple per bilanciamento
6. **Health Check**: Il registry verifica periodicamente la salute dei servizi
7. **Deregistrazione**: I servizi si deregistrano al shutdown
8. **Aggiornamento**: Il registry aggiorna le informazioni sui servizi
9. **Notifiche**: I client possono essere notificati dei cambiamenti
10. **Fallback**: Il registry gestisce servizi non disponibili

## Schema visivo

```
┌─────────────────────────────────────────────────────────────┐
│                    Service Registry                         │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │
│  │   Service   │ │   Service   │ │   Service   │          │
│  │   Catalog   │ │   Health    │ │   Load      │          │
│  │             │ │   Monitor   │ │ Balancer    │          │
│  └─────────────┘ └─────────────┘ └─────────────┘          │
└─────────────────┬─────────────────┬─────────────────────────┘
                  │                 │
        ┌─────────▼─────────┐ ┌─────▼─────┐ ┌─────────────────┐
        │   Service A       │ │Service B  │ │   Service C     │
        │   (Instance 1)    │ │(Instance 1)│ │   (Instance 1)  │
        │   ┌─────────────┐ │ │┌─────────┐ │ │ ┌─────────────┐ │
        │   │   Health    │ │ ││ Health  │ │ │ │   Health    │ │
        │   │   Check     │ │ ││ Check   │ │ │ │   Check     │ │
        │   └─────────────┘ │ │└─────────┘ │ │ └─────────────┘ │
        └───────────────────┘ └───────────┘ └─────────────────┘
                  │                 │
        ┌─────────▼─────────┐ ┌─────▼─────┐ ┌─────────────────┐
        │   Service A       │ │Service B  │ │   Service C     │
        │   (Instance 2)    │ │(Instance 2)│ │   (Instance 2)  │
        │   ┌─────────────┐ │ │┌─────────┐ │ │ ┌─────────────┐ │
        │   │   Health    │ │ ││ Health  │ │ │ │   Health    │ │
        │   │   Check     │ │ ││ Check   │ │ │ │   Check     │ │
        │   └─────────────┘ │ │└─────────┘ │ │ └─────────────┘ │
        └───────────────────┘ └───────────┘ └─────────────────┘
                  │                 │
        ┌─────────▼─────────┐ ┌─────▼─────┐ ┌─────────────────┐
        │   Client App      │ │Client App │ │   Client App    │
        │   ┌─────────────┐ │ │┌─────────┐ │ │ ┌─────────────┐ │
        │   │   Service   │ │ ││ Service │ │ │ │   Service   │ │
        │   │ Discovery   │ │ ││Discovery│ │ │ │ Discovery   │ │
        │   │   Client    │ │ ││ Client  │ │ │ │   Client    │ │
        │   └─────────────┘ │ │└─────────┘ │ │ └─────────────┘ │
        └───────────────────┘ └───────────┘ └─────────────────┘
```

## Quando usarlo

Usa il Service Discovery Pattern quando:
- Hai servizi distribuiti che devono comunicare tra loro
- I servizi cambiano posizione dinamicamente
- Hai bisogno di bilanciamento del carico automatico
- Vuoi gestire failover automatico
- Stai usando container o ambienti cloud
- Hai servizi che si avviano e fermano frequentemente
- Vuoi evitare hardcoding degli indirizzi

**NON usarlo quando:**
- Hai un'applicazione monolitica
- I servizi hanno indirizzi fissi e stabili
- Non hai bisogno di bilanciamento del carico
- L'overhead del discovery è eccessivo
- Hai requisiti di performance estremi
- L'architettura è troppo semplice

## Pro e contro

**I vantaggi:**
- Gestione dinamica dei servizi
- Bilanciamento del carico automatico
- Failover automatico e trasparente
- Scalabilità orizzontale semplificata
- Gestione degli ambienti semplificata
- Resilienza migliorata
- Configurazione centralizzata

**Gli svantaggi:**
- Punto di fallimento singolo
- Overhead di latenza aggiuntivo
- Complessità di configurazione
- Possibili problemi di consistenza
- Difficoltà di debugging
- Dipendenza da un singolo componente

## Esempi di codice

### Pseudocodice
```
// Service Registry
class ServiceRegistry {
    function register(service) {
        this.services[service.id] = {
            name: service.name,
            address: service.address,
            port: service.port,
            health: 'healthy',
            lastHeartbeat: now(),
            instances: [service]
        };
    }
    
    function discover(serviceName) {
        service = this.services[serviceName];
        if (!service) {
            return null;
        }
        
        // Bilanciamento del carico
        healthyInstances = service.instances.filter(i => i.health === 'healthy');
        if (healthyInstances.length === 0) {
            return null;
        }
        
        return this.loadBalancer.select(healthyInstances);
    }
    
    function heartbeat(serviceId) {
        service = this.services[serviceId];
        if (service) {
            service.lastHeartbeat = now();
            service.health = 'healthy';
        }
    }
}

// Service
class Service {
    function start() {
        this.registry.register({
            id: this.id,
            name: this.name,
            address: this.address,
            port: this.port
        });
        
        this.startHeartbeat();
    }
    
    function startHeartbeat() {
        setInterval(() => {
            this.registry.heartbeat(this.id);
        }, 30000); // 30 secondi
    }
}

// Client
class Client {
    function callService(serviceName, request) {
        service = this.registry.discover(serviceName);
        if (!service) {
            throw new ServiceNotFoundException();
        }
        
        return this.httpClient.post(service.address + ':' + service.port, request);
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema E-commerce con Service Discovery](./esempio-completo/)** - Registry per servizi multipli

L'esempio include:
- Service Registry per registrazione e scoperta
- Health Check per monitoraggio dei servizi
- Load Balancer per bilanciamento del carico
- Service Client per comunicazione trasparente
- Heartbeat per mantenere i servizi attivi
- Failover automatico per resilienza
- Interfaccia web per testare le funzionalità

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[API Gateway Pattern](./21-api-gateway/api-gateway-pattern.md)** - Punto di accesso unificato
- **[Backend for Frontend Pattern](./32-backend-for-frontend/backend-for-frontend-pattern.md)** - API specifiche per client
- **[Microservices Pattern](./20-microservices/microservices-pattern.md)** - Architettura a servizi
- **[Circuit Breaker Pattern](./08-circuit-breaker/circuit-breaker-pattern.md)** - Gestione fallimenti
- **[Load Balancer Pattern](./22-load-balancer/load-balancer-pattern.md)** - Distribuzione carico

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[Separation of Concerns](../00-fondamentali/06-separation-of-concerns/separation-of-concerns.md)** - Separazione delle responsabilità
- **[Single Responsibility Principle](../00-fondamentali/04-solid-principles/solid-principles.md)** - Responsabilità singola

## Esempi di uso reale

- **E-commerce**: Registry per servizi utenti, prodotti, ordini, pagamenti
- **Sistemi bancari**: Registry per servizi conti, transazioni, notifiche
- **Sistemi di streaming**: Registry per servizi contenuti, utenti, raccomandazioni
- **Sistemi IoT**: Registry per servizi dispositivi, dati, analisi
- **Sistemi di social media**: Registry per servizi utenti, post, messaggi
- **Sistemi enterprise**: Registry per servizi interni e esterni

## Anti-pattern

**Cosa NON fare:**
- Hardcodare gli indirizzi dei servizi
- Non implementare health check appropriato
- Ignorare la gestione dei fallimenti
- Non implementare bilanciamento del carico
- Creare dipendenze circolari
- Non gestire la deregistrazione dei servizi

## Troubleshooting

### Problemi comuni
- **Servizio non trovato**: Verifica registrazione e health check
- **Servizio non raggiungibile**: Controlla rete e configurazione
- **Heartbeat mancante**: Verifica configurazione e timeout
- **Bilanciamento non funziona**: Controlla algoritmo e istanze sane
- **Registry non disponibile**: Implementa fallback e cache

### Debug e monitoring
- Monitora la registrazione dei servizi
- Traccia gli heartbeat e health check
- Misura le performance di discovery
- Controlla la disponibilità del registry
- Implementa alert per servizi non disponibili

## Performance e considerazioni

### Impatto sulle risorse
- **CPU**: Overhead per heartbeat e health check
- **Memoria**: Cache dei servizi e metadati
- **I/O**: Comunicazione con il registry

### Scalabilità
- **Carico basso**: Performance accettabili con overhead minimo
- **Carico medio**: Buone performance con caching
- **Carico alto**: Scalabilità eccellente con load balancing

### Colli di bottiglia
- **Registry singolo**: Implementa clustering
- **Heartbeat frequente**: Ottimizza la frequenza
- **Discovery lento**: Implementa caching
- **Health check pesanti**: Semplifica i controlli

## Risorse utili

### Documentazione ufficiale
- [Service Discovery Pattern](https://microservices.io/patterns/service-registry.html) - Guida completa
- [Laravel Service Discovery](https://laravel.com/docs/service-discovery) - Framework specifico

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Horizon](https://laravel.com/docs/horizon) - Gestione code
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting) - Comunicazione real-time

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Service Discovery Examples](https://github.com/service-discovery) - Esempi pratici
- [Laravel Service Discovery](https://github.com/laravel-service-discovery) - Implementazioni Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
