# Database Per Service Pattern

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

Il Database Per Service Pattern assegna un database dedicato a ogni microservizio, garantendo che ogni servizio abbia il controllo completo sui propri dati. È come dare a ogni dipartimento aziendale il proprio archivio privato, dove può organizzare i documenti come preferisce senza interferenze.

Pensa a un'azienda con diversi reparti: ogni reparto ha il proprio sistema di archiviazione, le proprie regole di organizzazione e il proprio accesso. Il reparto vendite non può modificare i dati del reparto contabilità, ma possono comunicare quando necessario.

## Perché ti serve

Immagina un'architettura di microservizi che deve:
- Garantire l'autonomia dei servizi
- Evitare accoppiamenti tra servizi
- Permettere tecnologie database diverse
- Scalare indipendentemente
- Mantenere la coerenza dei dati
- Facilitare lo sviluppo e il deployment

Senza database per service pattern:
- I servizi condividono lo stesso database
- Le modifiche di schema impattano tutti i servizi
- I servizi sono accoppiati attraverso i dati
- Non puoi usare tecnologie database diverse
- La scalabilità è limitata
- I deployment sono complessi

Con database per service pattern:
- Ogni servizio ha il proprio database
- Le modifiche di schema sono isolate
- I servizi sono disaccoppiati
- Puoi scegliere la tecnologia database ottimale
- Ogni servizio scala indipendentemente
- I deployment sono semplici

## Come funziona

1. **Identificazione servizi**: Identifica i microservizi e i loro domini
2. **Assegnazione database**: Assegna un database dedicato a ogni servizio
3. **Definizione confini**: Definisci i confini dei dati per ogni servizio
4. **Implementazione comunicazione**: Implementa la comunicazione tra servizi
5. **Gestione transazioni**: Gestisci le transazioni distribuite
6. **Monitoraggio**: Monitora le performance e la coerenza

## Schema visivo

```
Microservizi con Database Dedicati:

User Service → User Database (PostgreSQL)
Order Service → Order Database (MySQL)
Product Service → Product Database (MongoDB)
Payment Service → Payment Database (PostgreSQL)
Notification Service → Notification Database (Redis)

Comunicazione:
User Service ←→ Order Service (API)
Order Service ←→ Product Service (API)
Order Service ←→ Payment Service (API)
```

## Quando usarlo

Usa il Database Per Service Pattern quando:
- Hai un'architettura di microservizi
- I servizi hanno domini di dati distinti
- Vuoi garantire l'autonomia dei servizi
- Hai bisogno di tecnologie database diverse
- Vuoi scalare i servizi indipendentemente
- I team di sviluppo sono separati

**NON usarlo quando:**
- Hai un'applicazione monolitica
- I servizi condividono molti dati
- Le transazioni ACID sono critiche
- Non hai risorse per gestire più database
- I dati sono strettamente correlati
- La coerenza immediata è essenziale

## Pro e contro

**I vantaggi:**
- Autonomia completa dei servizi
- Disaccoppiamento tra servizi
- Flessibilità nella scelta del database
- Scalabilità indipendente
- Deployment indipendente
- Team di sviluppo autonomi

**Gli svantaggi:**
- Complessità di gestione
- Transazioni distribuite complesse
- Possibili inconsistenze temporanee
- Overhead di comunicazione
- Gestione della coerenza
- Costi operativi maggiori

## Esempi di codice

### Pseudocodice
```
class UserService {
    private userDatabase
    
    function createUser(userData) {
        // Transazione locale
        return userDatabase.transaction(() => {
            user = userDatabase.create(userData)
            // Evento per altri servizi
            eventBus.publish('user.created', user)
            return user
        })
    }
    
    function getUser(userId) {
        return userDatabase.findById(userId)
    }
}

class OrderService {
    private orderDatabase
    private userServiceClient
    private productServiceClient
    
    function createOrder(orderData) {
        return orderDatabase.transaction(() => {
            // Verifica utente (chiamata API)
            user = userServiceClient.getUser(orderData.userId)
            if (!user) throw new UserNotFoundError()
            
            // Verifica prodotto (chiamata API)
            product = productServiceClient.getProduct(orderData.productId)
            if (!product) throw new ProductNotFoundError()
            
            // Crea ordine
            order = orderDatabase.create(orderData)
            
            // Evento per altri servizi
            eventBus.publish('order.created', order)
            return order
        })
    }
}

// Utilizzo
userService = new UserService(userDatabase)
orderService = new OrderService(orderDatabase, userServiceClient, productServiceClient)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Microservizi](./esempio-completo/)** - Sistema e-commerce con database dedicati

L'esempio include:
- User Service con database PostgreSQL
- Product Service con database MySQL
- Order Service con database MongoDB
- Payment Service con database PostgreSQL
- API Gateway per routing delle richieste
- Sistema di eventi per comunicazione tra servizi

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Microservices Pattern](./20-microservices/microservices-pattern.md)** - Architettura di microservizi
- **[API Gateway Pattern](./21-api-gateway/api-gateway-pattern.md)** - Punto di ingresso unificato
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite
- **[Event Sourcing Pattern](./06-event-sourcing/event-sourcing-pattern.md)** - Tracciamento eventi per coerenza
- **[CQRS Pattern](./05-cqrs/cqrs-pattern.md)** - Separazione comandi e query

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **E-commerce**: Servizi separati per utenti, prodotti, ordini, pagamenti
- **Sistemi bancari**: Servizi per conti, transazioni, carte, prestiti
- **Social media**: Servizi per profili, post, messaggi, notifiche
- **IoT**: Servizi per sensori, dati, analytics, alert
- **SaaS**: Servizi per tenant, billing, features, analytics
- **Gaming**: Servizi per giocatori, partite, leaderboard, chat

## Anti-pattern

**Cosa NON fare:**
- Condividere database tra servizi
- Creare dipendenze dirette tra database
- Non gestire la coerenza dei dati
- Ignorare le transazioni distribuite
- Non implementare meccanismi di sincronizzazione
- Creare servizi troppo granulari

## Troubleshooting

### Problemi comuni
- **Inconsistenze dati**: Implementa eventi e sincronizzazione
- **Transazioni distribuite**: Usa pattern Saga o eventi
- **Performance degradate**: Ottimizza le chiamate API tra servizi
- **Deadlock**: Implementa timeout e retry
- **Sincronizzazione fallita**: Implementa meccanismi di recovery

### Debug e monitoring
- Monitora le chiamate API tra servizi
- Traccia le transazioni distribuite
- Misura i tempi di risposta delle comunicazioni
- Controlla la coerenza dei dati
- Implementa alert per inconsistenze
- Monitora l'utilizzo delle risorse per database

## Performance e considerazioni

### Impatto sulle risorse
- **Storage**: Moltiplicazione dei database
- **Memoria**: Overhead per connessioni multiple
- **CPU**: Carico aggiuntivo per comunicazione
- **Rete**: Latenza per chiamate API tra servizi

### Scalabilità
- **Carico basso**: Overhead minimo, buone performance
- **Carico medio**: Possibili colli di bottiglia nelle comunicazioni
- **Carico alto**: Necessità di ottimizzazione delle API

### Colli di bottiglia
- **Comunicazione tra servizi**: Può diventare un collo di bottiglia
- **Transazioni distribuite**: Possono impattare le performance
- **Sincronizzazione**: Può causare latenza aggiuntiva
- **Gestione connessioni**: Molte connessioni database richiedono risorse

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Database](https://laravel.com/docs/database) - Gestione database
- [Laravel HTTP Client](https://laravel.com/docs/http-client) - Comunicazione tra servizi

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Microservices Patterns](https://microservices.io/patterns/data/database-per-service.html) - Pattern per microservizi
- [Database Patterns](https://martinfowler.com/articles/microservices.html#databases) - Pattern database per microservizi

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
