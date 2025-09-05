# Saga Choreography Pattern

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

Il Saga Choreography Pattern gestisce transazioni distribuite attraverso eventi e comunicazione diretta tra servizi, senza un orchestratore centrale. Ogni servizio sa cosa fare quando riceve un evento e pubblica eventi per comunicare con gli altri servizi. È come avere una danza dove ogni ballerino sa i propri passi e reagisce alla musica, coordinandosi naturalmente con gli altri.

Pensa a un processo di acquisto online: il servizio ordini pubblica un evento "OrdineCreato", il servizio inventario lo ascolta e riserva il prodotto, poi pubblica "ProdottoRiservato", il servizio pagamenti lo ascolta e processa il pagamento, e così via. Se qualcosa va storto, ogni servizio sa come compensare.

## Perché ti serve

Immagina un sistema distribuito che deve:
- Gestire transazioni complesse tra più servizi
- Mantenere i servizi disaccoppiati
- Evitare punti singoli di fallimento
- Permettere scalabilità indipendente
- Gestire i fallimenti in modo distribuito
- Mantenere la coerenza dei dati

Senza saga choreography pattern:
- I servizi sono strettamente accoppiati
- C'è un punto singolo di fallimento
- La scalabilità è limitata
- I fallimenti sono difficili da gestire
- La coordinazione è complessa
- Il sistema è fragile

Con saga choreography pattern:
- I servizi sono disaccoppiati
- Non ci sono punti singoli di fallimento
- Ogni servizio scala indipendentemente
- I fallimenti sono gestiti localmente
- La coordinazione è naturale
- Il sistema è robusto e resiliente

## Come funziona

1. **Definizione eventi**: Definisci gli eventi che i servizi si scambiano
2. **Implementazione listener**: Ogni servizio implementa i listener per gli eventi
3. **Pubblicazione eventi**: I servizi pubblicano eventi quando completano operazioni
4. **Gestione fallimenti**: Ogni servizio gestisce i propri fallimenti
5. **Compensazioni**: I servizi pubblicano eventi di compensazione
6. **Monitoraggio**: Monitora gli eventi per tracciare lo stato delle transazioni

## Schema visivo

```
Saga Choreography:

Servizio A → Evento 1 → Servizio B → Evento 2 → Servizio C
     ↓           ↓           ↓           ↓           ↓
  Operazione  Listener    Operazione  Listener    Operazione
     1          1            2          2            3
     ↓           ↓           ↓           ↓           ↓
  Successo   Pubblica    Successo   Pubblica    Successo
             Evento 2              Evento 3

In caso di fallimento:
Servizio A → Evento 1 → Servizio B → FALLIMENTO → Evento Compensazione
     ↓           ↓           ↓           ↓              ↓
  Operazione  Listener    Operazione  Pubblica      Listener
     1          1            2        Evento Comp.    Compensazione
     ↓           ↓           ↓              ↓              ↓
  Successo   Pubblica    Successo      Servizio A    Compensazione
             Evento 2              (rollback)         Operazione 1
```

## Quando usarlo

Usa il Saga Choreography Pattern quando:
- Vuoi mantenere i servizi disaccoppiati
- Non vuoi punti singoli di fallimento
- I servizi devono scalare indipendentemente
- Hai bisogno di flessibilità nella coordinazione
- I servizi sono sviluppati da team diversi
- Vuoi un'architettura event-driven

**NON usarlo quando:**
- Hai bisogno di coordinazione centralizzata
- I servizi sono strettamente accoppiati
- Hai bisogno di controllo granulare
- I servizi non possono gestire eventi
- Hai bisogno di transazioni ACID
- La coerenza immediata è critica

## Pro e contro

**I vantaggi:**
- Servizi completamente disaccoppiati
- Nessun punto singolo di fallimento
- Scalabilità indipendente
- Flessibilità nella coordinazione
- Architettura event-driven
- Resilienza distribuita

**Gli svantaggi:**
- Complessità di debugging
- Difficoltà nel tracciamento delle transazioni
- Possibili race conditions
- Gestione complessa degli errori
- Difficoltà nel testing
- Possibili inconsistenze temporanee

## Esempi di codice

### Pseudocodice
```
class OrderService {
    private eventBus
    
    function createOrder(orderData) {
        order = this.createOrderLocal(orderData)
        
        // Pubblica evento per avviare la saga
        this.eventBus.publish('OrderCreated', {
            orderId: order.id,
            userId: order.userId,
            productId: order.productId,
            quantity: order.quantity
        })
        
        return order
    }
    
    function handlePaymentProcessed(event) {
        order = this.getOrder(event.orderId)
        order.status = 'PAID'
        this.saveOrder(order)
        
        // Pubblica evento per il prossimo step
        this.eventBus.publish('OrderPaid', {
            orderId: order.id,
            userId: order.userId
        })
    }
    
    function handleInventoryReservationFailed(event) {
        order = this.getOrder(event.orderId)
        order.status = 'CANCELLED'
        this.saveOrder(order)
        
        // Pubblica evento di compensazione
        this.eventBus.publish('OrderCancelled', {
            orderId: order.id,
            reason: 'INVENTORY_UNAVAILABLE'
        })
    }
}

class InventoryService {
    private eventBus
    
    function handleOrderCreated(event) {
        try {
            this.reserveInventory(event.productId, event.quantity)
            
            // Pubblica evento di successo
            this.eventBus.publish('InventoryReserved', {
                orderId: event.orderId,
                productId: event.productId,
                quantity: event.quantity
            })
        } catch (error) {
            // Pubblica evento di fallimento
            this.eventBus.publish('InventoryReservationFailed', {
                orderId: event.orderId,
                productId: event.productId,
                error: error.message
            })
        }
    }
    
    function handleOrderCancelled(event) {
        // Compensazione: rilascia l'inventario riservato
        this.releaseInventory(event.orderId)
    }
}

class PaymentService {
    private eventBus
    
    function handleInventoryReserved(event) {
        try {
            this.processPayment(event.orderId, event.amount)
            
            // Pubblica evento di successo
            this.eventBus.publish('PaymentProcessed', {
                orderId: event.orderId,
                amount: event.amount
            })
        } catch (error) {
            // Pubblica evento di fallimento
            this.eventBus.publish('PaymentFailed', {
                orderId: event.orderId,
                error: error.message
            })
        }
    }
    
    function handleOrderCancelled(event) {
        // Compensazione: rimborsa il pagamento se già processato
        this.refundPayment(event.orderId)
    }
}

// Utilizzo
eventBus = new EventBus()
orderService = new OrderService(eventBus)
inventoryService = new InventoryService(eventBus)
paymentService = new PaymentService(eventBus)

// Registra i listener
eventBus.subscribe('OrderCreated', inventoryService.handleOrderCreated)
eventBus.subscribe('InventoryReserved', paymentService.handleInventoryReserved)
eventBus.subscribe('PaymentProcessed', orderService.handlePaymentProcessed)
eventBus.subscribe('InventoryReservationFailed', orderService.handleInventoryReservationFailed)
eventBus.subscribe('OrderCancelled', inventoryService.handleOrderCancelled)
eventBus.subscribe('OrderCancelled', paymentService.handleOrderCancelled)

// Avvia la saga
orderService.createOrder(orderData)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema E-commerce Choreography](./esempio-completo/)** - Gestione ordini con saga choreography

L'esempio include:
- Sistema di eventi per comunicazione tra servizi
- Servizi disaccoppiati con listener
- Gestione automatica dei fallimenti
- Sistema di compensazioni distribuite
- Dashboard per monitorare gli eventi
- API per gestire le transazioni

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Pattern base per gestione transazioni distribuite
- **[Saga Orchestration Pattern](./27-saga-orchestration/saga-orchestration-pattern.md)** - Alternativa centralizzata
- **[Event Sourcing Pattern](./06-event-sourcing/event-sourcing-pattern.md)** - Tracciamento eventi per coerenza
- **[CQRS Pattern](./05-cqrs/cqrs-pattern.md)** - Separazione comandi e query
- **[Event-Driven Architecture Pattern](./31-event-driven-architecture/event-driven-architecture-pattern.md)** - Architettura basata su eventi

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **E-commerce**: Processi di acquisto, gestione ordini, rimborsi
- **Sistemi bancari**: Trasferimenti, prestiti, aperture conto
- **Viaggi**: Prenotazioni, cancellazioni, modifiche
- **IoT**: Processi di configurazione, aggiornamenti, manutenzione
- **SaaS**: Attivazione account, billing, cancellazioni
- **Gaming**: Transazioni in-game, acquisti, premi

## Anti-pattern

**Cosa NON fare:**
- Non implementare compensazioni per tutti gli eventi
- Ignorare la gestione dei fallimenti
- Non monitorare gli eventi
- Creare eventi troppo complessi
- Non gestire i timeout
- Ignorare la gestione degli errori

## Troubleshooting

### Problemi comuni
- **Eventi persi**: Implementa retry e persistence
- **Race conditions**: Implementa idempotenza e ordering
- **Compensazioni fallite**: Implementa retry e logging
- **Stato inconsistente**: Verifica la logica di compensazione
- **Performance degradate**: Ottimizza la gestione degli eventi
- **Debugging difficile**: Implementa tracing e logging

### Debug e monitoring
- Monitora tutti gli eventi pubblicati e consumati
- Traccia i tempi di elaborazione degli eventi
- Misura i tassi di successo e fallimento
- Controlla i tempi di compensazione
- Implementa alert per eventi non processati
- Monitora l'utilizzo delle risorse per ogni servizio

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead per gestione eventi
- **CPU**: Carico aggiuntivo per elaborazione eventi
- **I/O**: Operazioni di persistenza eventi
- **Rete**: Comunicazione tramite eventi

### Scalabilità
- **Carico basso**: Performance eccellenti, overhead minimo
- **Carico medio**: Buone performance con gestione ottimizzata
- **Carico alto**: Possibili colli di bottiglia nel sistema di eventi

### Colli di bottiglia
- **Sistema di eventi**: Può diventare un collo di bottiglia
- **Persistenza eventi**: Può impattare le performance
- **Elaborazione eventi**: Può causare latenza
- **Gestione errori**: Può rallentare l'esecuzione

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Events](https://laravel.com/docs/events) - Sistema di eventi
- [Laravel Queue](https://laravel.com/docs/queues) - Gestione operazioni asincrone

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Microservices Patterns](https://microservices.io/patterns/data/saga.html) - Pattern per microservizi
- [Saga Pattern](https://microservices.io/patterns/data/saga.html) - Pattern per transazioni distribuite

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
