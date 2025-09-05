# Outbox Pattern

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

Il Outbox Pattern garantisce la coerenza tra le operazioni di database e la pubblicazione di eventi, scrivendo gli eventi in una tabella "outbox" come parte della stessa transazione del database. È come avere una "cassetta postale" dove metti le lettere da spedire, assicurandoti che vengano inviate anche se il sistema si riavvia.

Pensa a un ufficio postale: quando depositi una lettera, viene registrata nel sistema e poi spedita. Se l'ufficio si chiude per manutenzione, le lettere rimangono registrate e vengono spedite quando riapre. Il pattern outbox funziona allo stesso modo per gli eventi.

## Perché ti serve

Immagina un sistema che deve:
- Garantire la coerenza tra database e eventi
- Gestire transazioni distribuite
- Assicurare la delivery degli eventi
- Gestire i fallimenti in modo elegante
- Mantenere l'integrità dei dati
- Permettere il recovery automatico

Senza outbox pattern:
- Gli eventi possono essere persi
- La coerenza tra database e eventi non è garantita
- I fallimenti causano inconsistenze
- Il recovery è complesso
- L'integrità dei dati è a rischio
- Il sistema è fragile

Con outbox pattern:
- Gli eventi sono sempre preservati
- La coerenza è garantita dalla transazione
- I fallimenti sono gestiti automaticamente
- Il recovery è semplice e affidabile
- L'integrità dei dati è garantita
- Il sistema è robusto e resiliente

## Come funziona

1. **Operazione business**: Esegui l'operazione business nel database
2. **Scrittura outbox**: Scrivi l'evento nella tabella outbox nella stessa transazione
3. **Commit transazione**: Conferma la transazione (database + outbox)
4. **Pubblicazione eventi**: Un processo separato legge dalla tabella outbox
5. **Invio eventi**: Pubblica gli eventi ai consumer
6. **Marcatura completata**: Marca gli eventi come processati

## Schema visivo

```
Operazione Business:
1. Inizia Transazione
2. Aggiorna Dati Business
3. Scrivi Evento in Outbox
4. Commit Transazione

Processo Outbox:
1. Legge Eventi da Outbox
2. Pubblica Eventi
3. Marca come Processati
4. Rimuove da Outbox

Fallimento:
- Se fallisce prima del commit: tutto rollback
- Se fallisce dopo il commit: evento rimane in outbox per retry
```

## Quando usarlo

Usa il Outbox Pattern quando:
- Hai bisogno di coerenza tra database e eventi
- Stai implementando event-driven architecture
- Hai transazioni distribuite
- Vuoi garantire la delivery degli eventi
- Hai bisogno di recovery automatico
- La coerenza dei dati è critica

**NON usarlo quando:**
- Gli eventi non sono critici
- Puoi permetterti la perdita di eventi
- Non hai transazioni distribuite
- I dati non sono critici
- Non hai bisogno di coerenza
- Il sistema è semplice e locale

## Pro e contro

**I vantaggi:**
- Coerenza garantita tra database e eventi
- Delivery affidabile degli eventi
- Recovery automatico
- Gestione elegante dei fallimenti
- Integrità dei dati garantita
- Architettura robusta

**Gli svantaggi:**
- Complessità di implementazione
- Overhead di storage per la tabella outbox
- Latenza aggiuntiva per la pubblicazione
- Gestione della tabella outbox
- Possibili duplicazioni di eventi
- Debugging complesso

## Esempi di codice

### Pseudocodice
```
class OutboxService {
    private database
    private eventPublisher
    private outboxTable = 'outbox_events'
    
    function executeWithEvent(operation, eventData) {
        return database.transaction(() => {
            // Esegui operazione business
            result = operation.execute()
            
            // Scrivi evento in outbox
            event = {
                id: generateId(),
                eventType: eventData.type,
                aggregateId: eventData.aggregateId,
                eventData: eventData.data,
                createdAt: now(),
                processed: false
            }
            
            database.insert(outboxTable, event)
            
            return result
        })
    }
    
    function processOutboxEvents() {
        events = database.select(outboxTable, { processed: false })
        
        for (event in events) {
            try {
                // Pubblica evento
                eventPublisher.publish(event.eventType, event.eventData)
                
                // Marca come processato
                database.update(outboxTable, 
                    { id: event.id }, 
                    { processed: true, processedAt: now() }
                )
            } catch (error) {
                // Log error, evento rimane in outbox per retry
                log.error('Failed to publish event', { eventId: event.id, error })
            }
        }
    }
    
    function cleanupProcessedEvents() {
        // Rimuovi eventi processati più vecchi di 7 giorni
        database.delete(outboxTable, {
            processed: true,
            processedAt: { $lt: now().subtract(7, 'days') }
        })
    }
}

class UserService {
    private outboxService
    
    function createUser(userData) {
        return outboxService.executeWithEvent(
            () => {
                user = database.insert('users', userData)
                return user
            },
            {
                type: 'UserCreated',
                aggregateId: user.id,
                data: user
            }
        )
    }
    
    function updateUser(userId, userData) {
        return outboxService.executeWithEvent(
            () => {
                user = database.update('users', { id: userId }, userData)
                return user
            },
            {
                type: 'UserUpdated',
                aggregateId: userId,
                data: user
            }
        )
    }
}

// Utilizzo
outboxService = new OutboxService(database, eventPublisher)
userService = new UserService(outboxService)

// Crea utente con evento
user = userService.createUser({ name: 'John', email: 'john@example.com' })

// Processa eventi in background
setInterval(() => {
    outboxService.processOutboxEvents()
}, 5000) // Ogni 5 secondi
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema E-commerce Outbox](./esempio-completo/)** - Gestione eventi con outbox pattern

L'esempio include:
- Servizio outbox per gestione eventi
- Integrazione con transazioni database
- Processo di pubblicazione eventi
- Gestione fallimenti e retry
- Dashboard per monitorare gli eventi
- API per gestire le operazioni

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Event Sourcing Pattern](./06-event-sourcing/event-sourcing-pattern.md)** - Tracciamento eventi per coerenza
- **[CQRS Pattern](./05-cqrs/cqrs-pattern.md)** - Separazione comandi e query
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite
- **[Inbox Pattern](./30-inbox-pattern/inbox-pattern.md)** - Pattern complementare per consumer
- **[Event-Driven Architecture Pattern](./31-event-driven-architecture/event-driven-architecture-pattern.md)** - Architettura basata su eventi

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **E-commerce**: Gestione ordini, inventario, pagamenti
- **Sistemi bancari**: Transazioni, conti, carte
- **Social media**: Post, like, commenti, notifiche
- **IoT**: Dati sensori, alert, configurazioni
- **SaaS**: Attivazione account, billing, features
- **Gaming**: Punteggi, achievement, transazioni

## Anti-pattern

**Cosa NON fare:**
- Non gestire la tabella outbox
- Ignorare i fallimenti nella pubblicazione
- Non implementare retry per eventi falliti
- Non pulire gli eventi processati
- Non monitorare la tabella outbox
- Ignorare la gestione degli errori

## Troubleshooting

### Problemi comuni
- **Eventi non pubblicati**: Verifica il processo di pubblicazione
- **Tabella outbox piena**: Implementa pulizia automatica
- **Eventi duplicati**: Implementa idempotenza
- **Performance degradate**: Ottimizza le query sulla tabella outbox
- **Fallimenti di pubblicazione**: Implementa retry e alert

### Debug e monitoring
- Monitora la dimensione della tabella outbox
- Traccia i tempi di pubblicazione degli eventi
- Misura i tassi di successo e fallimento
- Controlla i tempi di processing
- Implementa alert per eventi non processati
- Monitora l'utilizzo delle risorse

## Performance e considerazioni

### Impatto sulle risorse
- **Storage**: Overhead per tabella outbox
- **Memoria**: Overhead per gestione eventi
- **CPU**: Carico aggiuntivo per pubblicazione
- **I/O**: Operazioni di lettura/scrittura outbox

### Scalabilità
- **Carico basso**: Performance eccellenti, overhead minimo
- **Carico medio**: Buone performance con gestione ottimizzata
- **Carico alto**: Possibili colli di bottiglia nella pubblicazione

### Colli di bottiglia
- **Tabella outbox**: Può diventare un collo di bottiglia
- **Processo di pubblicazione**: Può impattare le performance
- **Eventi non processati**: Possono accumularsi
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
- [Microservices Patterns](https://microservices.io/patterns/data/transactional-outbox.html) - Pattern per microservizi
- [Outbox Pattern](https://microservices.io/patterns/data/transactional-outbox.html) - Pattern per coerenza eventi

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
