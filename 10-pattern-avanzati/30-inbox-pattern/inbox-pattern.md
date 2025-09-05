# Inbox Pattern

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

Il Inbox Pattern garantisce l'elaborazione idempotente degli eventi ricevuti, memorizzando gli eventi in una tabella "inbox" prima di elaborarli e marcandoli come processati. È come avere una "cassetta della posta" dove metti le lettere ricevute, le leggi una per una e le archivi quando hai finito.

Pensa a un ufficio postale: quando arriva una lettera, viene messa in una cassetta, poi un impiegato la prende, la legge, la elabora e la archivia. Se l'ufficio si chiude per manutenzione, le lettere rimangono nella cassetta e vengono elaborate quando riapre.

## Perché ti serve

Immagina un sistema che deve:
- Elaborare eventi in modo idempotente
- Gestire eventi duplicati
- Assicurare l'elaborazione degli eventi
- Gestire i fallimenti in modo elegante
- Mantenere l'integrità dei dati
- Permettere il recovery automatico

Senza inbox pattern:
- Gli eventi possono essere elaborati più volte
- I fallimenti causano inconsistenze
- Il recovery è complesso
- L'integrità dei dati è a rischio
- Il sistema è fragile
- Gli eventi duplicati causano problemi

Con inbox pattern:
- Gli eventi sono elaborati una sola volta
- I fallimenti sono gestiti automaticamente
- Il recovery è semplice e affidabile
- L'integrità dei dati è garantita
- Il sistema è robusto e resiliente
- Gli eventi duplicati sono gestiti automaticamente

## Come funziona

1. **Ricezione evento**: Ricevi l'evento da un producer
2. **Controllo duplicati**: Verifica se l'evento è già stato elaborato
3. **Scrittura inbox**: Scrivi l'evento nella tabella inbox
4. **Elaborazione**: Elabora l'evento
5. **Marcatura completata**: Marca l'evento come processato
6. **Cleanup**: Rimuovi l'evento dalla tabella inbox

## Schema visivo

```
Ricezione Evento:
1. Evento Ricevuto
2. Controllo Duplicati
3. Scrittura in Inbox
4. Elaborazione
5. Marcatura Completata
6. Cleanup

Fallimento:
- Se fallisce prima della scrittura: evento perso
- Se fallisce dopo la scrittura: evento rimane in inbox per retry
- Se fallisce durante l'elaborazione: evento rimane in inbox per retry
```

## Quando usarlo

Usa il Inbox Pattern quando:
- Hai bisogno di elaborazione idempotente
- Ricevi eventi da sistemi esterni
- Vuoi gestire eventi duplicati
- Hai bisogno di recovery automatico
- La coerenza dei dati è critica
- Elabori eventi in modo asincrono

**NON usarlo quando:**
- Gli eventi non sono critici
- Puoi permetterti la perdita di eventi
- Non hai eventi duplicati
- I dati non sono critici
- Non hai bisogno di idempotenza
- Il sistema è semplice e locale

## Pro e contro

**I vantaggi:**
- Elaborazione idempotente garantita
- Gestione automatica dei duplicati
- Recovery automatico
- Gestione elegante dei fallimenti
- Integrità dei dati garantita
- Architettura robusta

**Gli svantaggi:**
- Complessità di implementazione
- Overhead di storage per la tabella inbox
- Latenza aggiuntiva per l'elaborazione
- Gestione della tabella inbox
- Possibili accumuli di eventi
- Debugging complesso

## Esempi di codice

### Pseudocodice
```
class InboxService {
    private database
    private eventProcessor
    private inboxTable = 'inbox_events'
    
    function processEvent(event) {
        // Controlla se l'evento è già stato elaborato
        existingEvent = database.selectOne(inboxTable, { 
            eventId: event.id,
            processed: true 
        })
        
        if (existingEvent) {
            return // Evento già elaborato, ignora
        }
        
        // Controlla se l'evento è già in elaborazione
        processingEvent = database.selectOne(inboxTable, { 
            eventId: event.id,
            processed: false 
        })
        
        if (processingEvent) {
            return // Evento già in elaborazione, ignora
        }
        
        // Scrivi evento in inbox
        inboxEvent = {
            eventId: event.id,
            eventType: event.type,
            eventData: event.data,
            receivedAt: now(),
            processed: false,
            retryCount: 0
        }
        
        database.insert(inboxTable, inboxEvent)
        
        // Elabora evento
        this.processInboxEvent(inboxEvent)
    }
    
    function processInboxEvent(inboxEvent) {
        try {
            // Elabora evento
            eventProcessor.process(inboxEvent.eventType, inboxEvent.eventData)
            
            // Marca come processato
            database.update(inboxTable, 
                { eventId: inboxEvent.eventId }, 
                { processed: true, processedAt: now() }
            )
        } catch (error) {
            // Incrementa contatore retry
            retryCount = inboxEvent.retryCount + 1
            
            if (retryCount < 3) {
                // Riprova dopo un delay
                database.update(inboxTable, 
                    { eventId: inboxEvent.eventId }, 
                    { retryCount: retryCount, nextRetryAt: now().add(5, 'minutes') }
                )
            } else {
                // Marca come fallito
                database.update(inboxTable, 
                    { eventId: inboxEvent.eventId }, 
                    { processed: true, failed: true, failedAt: now(), error: error.message }
                )
            }
        }
    }
    
    function processPendingEvents() {
        events = database.select(inboxTable, { 
            processed: false,
            $or: [
                { nextRetryAt: { $lt: now() } },
                { nextRetryAt: null }
            ]
        })
        
        for (event in events) {
            this.processInboxEvent(event)
        }
    }
    
    function cleanupProcessedEvents() {
        // Rimuovi eventi processati più vecchi di 7 giorni
        database.delete(inboxTable, {
            processed: true,
            processedAt: { $lt: now().subtract(7, 'days') }
        })
    }
}

class UserEventProcessor {
    function process(eventType, eventData) {
        switch (eventType) {
            case 'UserCreated':
                this.handleUserCreated(eventData)
                break
            case 'UserUpdated':
                this.handleUserUpdated(eventData)
                break
            case 'UserDeleted':
                this.handleUserDeleted(eventData)
                break
            default:
                throw new Error(`Unknown event type: ${eventType}`)
        }
    }
    
    function handleUserCreated(data) {
        // Elabora creazione utente
        user = this.createUser(data)
        console.log('User created:', user.id)
    }
    
    function handleUserUpdated(data) {
        // Elabora aggiornamento utente
        user = this.updateUser(data.id, data)
        console.log('User updated:', user.id)
    }
    
    function handleUserDeleted(data) {
        // Elabora eliminazione utente
        this.deleteUser(data.id)
        console.log('User deleted:', data.id)
    }
}

// Utilizzo
eventProcessor = new UserEventProcessor()
inboxService = new InboxService(database, eventProcessor)

// Processa evento
inboxService.processEvent({
    id: 'event-123',
    type: 'UserCreated',
    data: { name: 'John', email: 'john@example.com' }
})

// Processa eventi in background
setInterval(() => {
    inboxService.processPendingEvents()
}, 5000) // Ogni 5 secondi
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema E-commerce Inbox](./esempio-completo/)** - Gestione eventi con inbox pattern

L'esempio include:
- Servizio inbox per gestione eventi
- Elaborazione idempotente
- Gestione duplicati e retry
- Dashboard per monitorare gli eventi
- API per gestire le operazioni
- Sistema di cleanup automatico

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Outbox Pattern](./29-outbox-pattern/outbox-pattern.md)** - Pattern complementare per producer
- **[Event Sourcing Pattern](./06-event-sourcing/event-sourcing-pattern.md)** - Tracciamento eventi per coerenza
- **[CQRS Pattern](./05-cqrs/cqrs-pattern.md)** - Separazione comandi e query
- **[Saga Pattern](./07-saga-pattern/saga-pattern.md)** - Gestione transazioni distribuite
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
- Non gestire la tabella inbox
- Ignorare i fallimenti nell'elaborazione
- Non implementare retry per eventi falliti
- Non pulire gli eventi processati
- Non monitorare la tabella inbox
- Ignorare la gestione degli errori

## Troubleshooting

### Problemi comuni
- **Eventi non elaborati**: Verifica il processo di elaborazione
- **Tabella inbox piena**: Implementa pulizia automatica
- **Eventi duplicati**: Verifica la logica di controllo duplicati
- **Performance degradate**: Ottimizza le query sulla tabella inbox
- **Fallimenti di elaborazione**: Implementa retry e alert

### Debug e monitoring
- Monitora la dimensione della tabella inbox
- Traccia i tempi di elaborazione degli eventi
- Misura i tassi di successo e fallimento
- Controlla i tempi di processing
- Implementa alert per eventi non elaborati
- Monitora l'utilizzo delle risorse

## Performance e considerazioni

### Impatto sulle risorse
- **Storage**: Overhead per tabella inbox
- **Memoria**: Overhead per gestione eventi
- **CPU**: Carico aggiuntivo per elaborazione
- **I/O**: Operazioni di lettura/scrittura inbox

### Scalabilità
- **Carico basso**: Performance eccellenti, overhead minimo
- **Carico medio**: Buone performance con gestione ottimizzata
- **Carico alto**: Possibili colli di bottiglia nell'elaborazione

### Colli di bottiglia
- **Tabella inbox**: Può diventare un collo di bottiglia
- **Processo di elaborazione**: Può impattare le performance
- **Eventi non elaborati**: Possono accumularsi
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
- [Inbox Pattern](https://microservices.io/patterns/data/transactional-outbox.html) - Pattern per coerenza eventi

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
