# CQRS Pattern

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

CQRS (Command Query Responsibility Segregation) separa completamente le operazioni di lettura da quelle di scrittura nel tuo sistema. Invece di usare lo stesso modello per tutto, hai modelli diversi per leggere e scrivere dati.

Pensa a un ristorante: i camerieri (queries) leggono il menu e prendono gli ordini, mentre i cuochi (commands) preparano i piatti. Sono due ruoli completamente diversi che richiedono strumenti e processi diversi.

## Perché ti serve

Immagina un e-commerce con milioni di prodotti. Quando un utente cerca "scarpe rosse", devi:
- Filtrare per categoria
- Filtrare per colore
- Ordinare per prezzo
- Mostrare solo disponibili
- Calcolare sconti in tempo reale

Ma quando un utente compra un prodotto, devi:
- Verificare disponibilità
- Bloccare la quantità
- Calcolare il totale
- Aggiornare l'inventario
- Creare l'ordine

Sono operazioni completamente diverse! Con CQRS puoi ottimizzare ogni operazione per quello che deve fare davvero.

## Come funziona

CQRS funziona separando il tuo sistema in due parti:

1. **Command Side (Scrittura)**: Gestisce tutte le operazioni che modificano i dati
2. **Query Side (Lettura)**: Gestisce tutte le operazioni che leggono i dati

I due lati possono avere:
- Database diversi
- Modelli diversi
- Logiche diverse
- Ottimizzazioni diverse

I dati vengono sincronizzati tra i due lati attraverso eventi o processi di sincronizzazione.

## Schema visivo

```
Sistema Tradizionale:
Client → [Modello Unico] → Database
       ↑                  ↓
   (Lettura)         (Scrittura)

CQRS:
Client → [Query Model] → [Read Database]
       → [Command Model] → [Write Database]
                              ↓
                        [Event Bus] → [Sync Process]
```

**Flusso tipico:**
1. **Command**: Client invia comando → Command Handler → Write DB
2. **Event**: Write DB genera evento → Event Bus
3. **Sync**: Event Bus → Query Model → Read DB
4. **Query**: Client richiede dati → Query Handler → Read DB

## Quando usarlo

Usa CQRS quando:
- Hai operazioni di lettura e scrittura molto diverse tra loro
- Il tuo sistema ha più letture che scritture (rapporto 10:1 o superiore)
- Hai bisogno di ottimizzazioni specifiche per lettura o scrittura
- Stai costruendo un sistema event-driven
- Hai team diversi che lavorano su lettura e scrittura
- Hai bisogno di scalabilità indipendente per lettura e scrittura

**NON usarlo quando:**
- Il tuo sistema è semplice e le operazioni sono simili
- Non hai problemi di performance o scalabilità
- Il team è piccolo e la complessità non è giustificata
- Hai un CRUD semplice senza logica complessa

## Pro e contro

**I vantaggi:**
- **Ottimizzazione indipendente**: Puoi ottimizzare lettura e scrittura separatamente
- **Scalabilità**: Puoi scalare i due lati indipendentemente
- **Flessibilità**: Puoi usare database diversi per ogni lato
- **Team separation**: Team diversi possono lavorare su lati diversi
- **Performance**: Query ottimizzate per la lettura, commands per la scrittura
- **Event-driven**: Facilita l'architettura basata su eventi

**Gli svantaggi:**
- **Complessità**: Aumenta significativamente la complessità del sistema
- **Consistenza**: Può esserci ritardo tra scrittura e lettura
- **Debugging**: Più difficile debuggare problemi che attraversano i due lati
- **Overhead**: Più codice, più infrastruttura, più punti di fallimento
- **Learning curve**: Richiede conoscenze avanzate per implementarlo bene

## Esempi di codice

### Pseudocodice
```
// Command Side
class CreateOrderCommand {
    userId: string
    items: OrderItem[]
    total: number
}

class CreateOrderHandler {
    handle(command: CreateOrderCommand) {
        // Validazione
        // Creazione ordine
        // Salvataggio su write DB
        // Pubblicazione evento
    }
}

// Query Side
class OrderQuery {
    getOrdersByUser(userId: string): OrderView[] {
        // Lettura ottimizzata da read DB
        // Nessuna logica di business
        // Solo presentazione dati
    }
}

// Event Bus
class OrderCreatedEvent {
    orderId: string
    userId: string
    items: OrderItem[]
}

class OrderViewProjection {
    handle(event: OrderCreatedEvent) {
        // Aggiorna read DB
        // Crea view ottimizzata per lettura
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[E-commerce CQRS Completo](./esempio-completo/)** - Sistema e-commerce con separazione command/query

L'esempio include:
- Command handlers per creazione ordini
- Query handlers per ricerca prodotti
- Event bus per sincronizzazione
- Database separati per read/write
- Proiezioni per ottimizzare le query
- Interfaccia web per testare il pattern

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Event Sourcing](./06-event-sourcing/event-sourcing-pattern.md)** - Memorizza eventi invece di stato
- **[Domain Event](./04-domain-event/domain-event-pattern.md)** - Eventi di dominio per sincronizzazione
- **[Repository Pattern](../04-pattern-architetturali/02-repository/repository-pattern.md)** - Astrazione accesso dati
- **[Service Layer](../04-pattern-architetturali/03-service-layer/service-layer-pattern.md)** - Logica business centralizzata

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development
- **[Event-Driven Architecture](../00-fondamentali/15-event-driven-architecture/event-driven-architecture.md)** - Architettura basata su eventi

## Esempi di uso reale

- **E-commerce**: Amazon usa CQRS per separare cataloghi prodotti (query) da gestione ordini (command)
- **Social Media**: Twitter separa timeline (query) da post creation (command)
- **Banking**: Sistemi bancari separano transazioni (command) da reportistica (query)
- **Gaming**: Giochi online separano gameplay (command) da leaderboard (query)

## Anti-pattern

**Cosa NON fare:**
- **CRUD con CQRS**: Non usare CQRS per operazioni CRUD semplici
- **Sincronizzazione sincrona**: Non sincronizzare i due lati in modo sincrono
- **Modelli identici**: Non creare modelli identici per command e query
- **Eventi complessi**: Non creare eventi troppo complessi o accoppiati
- **Over-engineering**: Non applicare CQRS dove non serve

## Troubleshooting

### Problemi comuni
- **Dati non sincronizzati**: Verifica che l'event bus funzioni correttamente e che le proiezioni siano aggiornate
- **Performance query lente**: Ottimizza le proiezioni e considera indici specifici per le query
- **Eventi persi**: Implementa retry logic e dead letter queue per gli eventi
- **Consistenza eventuale**: Documenta chiaramente i ritardi di sincronizzazione agli utenti

### Debug e monitoring
- **Event tracking**: Traccia tutti gli eventi per capire il flusso di sincronizzazione
- **Performance metrics**: Monitora separatamente le performance di command e query
- **Error rates**: Traccia errori separatamente per ogni lato
- **Sync delays**: Monitora i ritardi di sincronizzazione tra i due lati

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Doppio uso di memoria per modelli separati, ma ottimizzazioni specifiche per ogni lato
- **CPU**: Overhead per event processing, ma query più veloci grazie alle ottimizzazioni
- **I/O**: Più operazioni di database, ma operazioni ottimizzate per ogni caso d'uso

### Scalabilità
- **Carico basso**: Overhead non giustificato, meglio usare approcci più semplici
- **Carico medio**: Benefici iniziano a manifestarsi con separazione delle responsabilità
- **Carico alto**: Eccellente scalabilità indipendente per command e query

### Colli di bottiglia
- **Event Bus**: Può diventare collo di bottiglia se non scalato correttamente
- **Sincronizzazione**: Ritardi di sincronizzazione possono causare inconsistenze
- **Read DB**: Può diventare obsoleto se le proiezioni non sono aggiornate

## Risorse utili

### Documentazione ufficiale
- [CQRS Pattern - Microsoft](https://docs.microsoft.com/en-us/azure/architecture/patterns/cqrs) - Documentazione ufficiale Microsoft
- [Event Sourcing and CQRS](https://martinfowler.com/eaaDev/EventSourcing.html) - Martin Fowler su Event Sourcing

### Laravel specifico
- [Laravel CQRS Package](https://github.com/spatie/laravel-event-sourcing) - Package Spatie per Event Sourcing
- [Laravel Event Bus](https://laravel.com/docs/events) - Sistema eventi di Laravel

### Esempi e tutorial
- [CQRS in Laravel](https://laracasts.com/discuss/channels/general-discussion/cqrs-in-laravel) - Discussione Laracasts
- [Event Sourcing Tutorial](https://github.com/buttercup-php/buttercup-protects) - Esempio pratico PHP

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
