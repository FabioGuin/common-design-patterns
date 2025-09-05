# Saga Pattern

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

Il Saga Pattern gestisce transazioni distribuite mantenendo la consistenza dei dati attraverso una sequenza di operazioni locali. Ogni operazione ha una compensazione che può essere eseguita per annullare l'effetto dell'operazione precedente.

Pensa a un viaggio in aereo: prenoti volo, hotel, auto. Se il volo viene cancellato, devi cancellare anche hotel e auto. Il Saga Pattern ti aiuta a gestire queste "compensazioni" in modo automatico e coordinato.

## Perché ti serve

Immagina un e-commerce che deve:
1. Verificare disponibilità prodotto
2. Bloccare la quantità
3. Processare il pagamento
4. Aggiornare l'inventario
5. Inviare email di conferma

Se il pagamento fallisce al punto 3, devi:
- Sbloccare la quantità (compensazione del punto 2)
- Non inviare email (compensazione del punto 5)

Senza Saga Pattern, rischi di avere dati inconsistenti o operazioni incomplete.

## Come funziona

Il Saga Pattern funziona in due modalità:

### Orchestration (Orchestrazione)
- Un coordinatore centrale gestisce il flusso
- Ogni step invia il risultato al coordinatore
- Il coordinatore decide il prossimo step o la compensazione

### Choreography (Coreografia)
- Ogni servizio sa cosa fare dopo il proprio step
- I servizi comunicano tramite eventi
- Ogni servizio gestisce le proprie compensazioni

**Flusso tipico:**
1. **Inizio**: Avvia la saga con i parametri iniziali
2. **Step 1**: Esegui operazione locale
3. **Step 2**: Esegui operazione locale
4. **Step N**: Esegui operazione locale
5. **Successo**: Saga completata con successo
6. **Fallimento**: Esegui compensazioni in ordine inverso

## Schema visivo

```
Orchestration:
Saga Orchestrator → Service A → Service B → Service C
       ↓              ↓           ↓           ↓
    [Success] ← [Success] ← [Success] ← [Success]
       ↓
    [Complete]

Fallimento:
Saga Orchestrator → Service A → Service B → Service C
       ↓              ↓           ↓           ↓
    [Failure] ← [Compensate] ← [Compensate] ← [Failure]
       ↓
    [Rollback Complete]

Choreography:
Service A → Event → Service B → Event → Service C
    ↓         ↓         ↓         ↓         ↓
[Success] [Event] [Success] [Event] [Success]
    ↓
[Compensate] ← [Event] ← [Compensate] ← [Event] ← [Failure]
```

**Esempio concreto:**
```
Order Saga:
1. Reserve Inventory → 2. Process Payment → 3. Send Confirmation
   ↓ (if fails)           ↓ (if fails)         ↓ (if fails)
1. Release Inventory ← 2. Refund Payment ← 3. Cancel Confirmation
```

## Quando usarlo

Usa Saga Pattern quando:
- Hai transazioni distribuite tra più servizi
- Non puoi usare transazioni ACID tradizionali
- Hai bisogno di gestire operazioni di lunga durata
- Devi mantenere consistenza eventuale tra servizi
- Hai operazioni che possono fallire e richiedono rollback
- Stai costruendo microservizi con comunicazione asincrona

**NON usarlo quando:**
- Puoi usare transazioni ACID tradizionali
- Le operazioni sono semplici e atomiche
- Non hai bisogno di compensazioni complesse
- Il sistema è monolitico e centralizzato
- Le operazioni sono idempotenti e non richiedono rollback

## Pro e contro

**I vantaggi:**
- **Consistenza distribuita**: Mantiene consistenza tra servizi diversi
- **Flessibilità**: Puoi gestire flussi complessi e non lineari
- **Resilienza**: Gestisce fallimenti e recuperi automaticamente
- **Scalabilità**: Ogni servizio può scalare indipendentemente
- **Audit**: Traccia completa di tutte le operazioni e compensazioni
- **Asincrono**: Può gestire operazioni di lunga durata

**Gli svantaggi:**
- **Complessità**: Aumenta significativamente la complessità del sistema
- **Debugging**: Difficile debuggare flussi distribuiti
- **Consistenza eventuale**: Non garantisce consistenza immediata
- **Overhead**: Richiede infrastruttura aggiuntiva per orchestrazione
- **Testing**: Difficile testare scenari complessi
- **Monitoring**: Richiede monitoring avanzato per tracciare lo stato

## Esempi di codice

### Pseudocodice
```
// Saga Orchestrator
class OrderSaga {
    private steps = [
        'reserveInventory',
        'processPayment', 
        'sendConfirmation'
    ]
    
    private compensations = [
        'releaseInventory',
        'refundPayment',
        'cancelConfirmation'
    ]
    
    async execute(orderData) {
        executedSteps = []
        
        try {
            for step in steps {
                result = await this.executeStep(step, orderData)
                executedSteps.push(step)
            }
            return { success: true, result }
        } catch (error) {
            await this.compensate(executedSteps.reverse())
            return { success: false, error }
        }
    }
    
    async compensate(steps) {
        for step in steps {
            compensation = this.getCompensation(step)
            await this.executeCompensation(compensation)
        }
    }
}

// Service Implementation
class InventoryService {
    async reserveInventory(orderData) {
        // Logica per riservare inventario
        if (insufficientStock) {
            throw new InsufficientStockException()
        }
        return { reservationId: 'RES-123' }
    }
    
    async releaseInventory(reservationId) {
        // Logica per rilasciare inventario
        return { success: true }
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce Order Saga](./esempio-completo/)** - Sistema ordini con gestione transazioni distribuite

L'esempio include:
- Saga Orchestrator per coordinare operazioni
- Servizi per inventario, pagamenti e notifiche
- Sistema di compensazioni automatiche
- Gestione errori e retry logic
- Interfaccia per monitorare lo stato delle saghe
- Test per scenari di successo e fallimento

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[CQRS](./05-cqrs/cqrs-pattern.md)** - Separazione command/query che si integra con Saga
- **[Event Sourcing](./06-event-sourcing/event-sourcing-pattern.md)** - Tracciamento eventi per audit delle saghe
- **[Domain Event](./04-domain-event/domain-event-pattern.md)** - Eventi per comunicazione tra servizi
- **[Circuit Breaker](./08-circuit-breaker/circuit-breaker-pattern.md)** - Resilienza per servizi esterni

### Principi e Metodologie

- **[SOLID Principles](../12-pattern-metodologie-concettuali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../12-pattern-metodologie-concettuali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../12-pattern-metodologie-concettuali/09-tdd/tdd.md)** - Test-Driven Development
- **[Microservices](../12-pattern-metodologie-concettuali/20-microservices/microservices.md)** - Architettura a microservizi

## Esempi di uso reale

- **E-commerce**: Amazon per gestire ordini tra inventario, pagamenti e spedizione
- **Banking**: Sistemi bancari per trasferimenti tra conti e verifiche
- **Travel**: Booking.com per prenotazioni volo+hotel+auto
- **Gaming**: Giochi online per transazioni tra valute virtuali
- **IoT**: Sistemi industriali per coordinare operazioni tra dispositivi

## Anti-pattern

**Cosa NON fare:**
- **Saga troppo lunghe**: Non creare saghe con troppi step
- **Compensazioni incomplete**: Non dimenticare di implementare tutte le compensazioni
- **Gestione errori povera**: Non gestire correttamente i fallimenti
- **Accoppiamento forte**: Non creare dipendenze dirette tra servizi
- **Timeout mancanti**: Non implementare timeout per evitare saghe bloccate

## Troubleshooting

### Problemi comuni
- **Saga bloccate**: Implementa timeout e cleanup automatico
- **Compensazioni fallite**: Implementa retry logic e dead letter queue
- **Stato inconsistente**: Usa event sourcing per tracciare lo stato
- **Performance lente**: Ottimizza le chiamate tra servizi
- **Debugging difficile**: Implementa logging dettagliato e tracing

### Debug e monitoring
- **Saga state tracking**: Traccia lo stato di ogni saga
- **Step monitoring**: Monitora l'esecuzione di ogni step
- **Compensation tracking**: Traccia le compensazioni eseguite
- **Performance metrics**: Monitora tempi di esecuzione e fallimenti
- **Error analysis**: Analizza pattern di errore per migliorare resilienza

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead per gestire stato delle saghe e compensazioni
- **CPU**: Processamento per orchestrazione e gestione errori
- **I/O**: Chiamate di rete tra servizi per coordinazione

### Scalabilità
- **Carico basso**: Overhead non giustificato per operazioni semplici
- **Carico medio**: Benefici iniziano a manifestarsi con transazioni distribuite
- **Carico alto**: Eccellente scalabilità con gestione asincrona

### Colli di bottiglia
- **Saga Orchestrator**: Può diventare collo di bottiglia se non scalato
- **Rete**: Chiamate tra servizi possono causare latenza
- **Database**: Stato delle saghe può causare contesa

## Risorse utili

### Documentazione ufficiale
- [Saga Pattern - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/saga) - Documentazione Microsoft
- [Saga Pattern - Microservices.io](https://microservices.io/patterns/data/saga.html) - Pattern dettagliato

### Laravel specifico
- [Laravel Saga Package](https://github.com/spatie/laravel-saga) - Package per Saga Pattern
- [Laravel Queue](https://laravel.com/docs/queues) - Sistema code per operazioni asincrone

### Esempi e tutorial
- [Saga Pattern in PHP](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico
- [Microservices Saga Tutorial](https://microservices.io/patterns/data/saga.html) - Tutorial completo

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
