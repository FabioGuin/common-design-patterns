# Hexagonal Architecture Pattern

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

L'Hexagonal Architecture Pattern (noto anche come Ports and Adapters) isola la logica di business dell'applicazione da tutti i sistemi esterni. È come costruire un'isola con ponti che la collegano al mondo esterno, dove l'isola è la tua logica di business e i ponti sono gli adapter.

Pensa a un sistema bancario: la logica di business (calcoli, regole) è al centro, mentre database, API esterne, interfacce utente sono "attaccati" tramite adapter. Se cambi database o interfaccia, la logica di business rimane intatta.

## Perché ti serve

Immagina un'applicazione che deve:
- Essere indipendente da database e framework
- Supportare diversi tipi di interfacce (web, API, CLI)
- Integrare con servizi esterni diversi
- Essere facilmente testabile
- Cambiare tecnologie senza riscrivere la logica
- Mantenere la logica di business pulita

Senza Hexagonal Architecture:
- La logica di business è accoppiata al framework
- Cambiare database richiede riscrittura del codice
- I test sono difficili da scrivere
- L'applicazione dipende da tecnologie specifiche
- È difficile integrare nuovi sistemi esterni
- La manutenzione diventa complessa

Con Hexagonal Architecture:
- La logica di business è completamente isolata
- Puoi cambiare database senza toccare la logica
- I test sono semplici e veloci
- L'applicazione è indipendente dalle tecnologie
- È facile integrare nuovi sistemi esterni
- La manutenzione è più semplice

## Come funziona

1. **Core Domain**: Logica di business pura al centro
2. **Ports**: Interfacce che definiscono i contratti
3. **Adapters**: Implementazioni concrete dei port
4. **Inbound Adapters**: Gestiscono input (web, API, CLI)
5. **Outbound Adapters**: Gestiscono output (database, servizi esterni)
6. **Dependency Injection**: Collega tutto insieme

## Schema visivo

```
                    Inbound Adapters
                         │
                    ┌────▼────┐
                    │  Web    │
                    │  API    │
                    │  CLI    │
                    └────┬────┘
                         │
                    ┌────▼────┐
                    │  Ports  │
                    │ (Input) │
                    └────┬────┘
                         │
                    ┌────▼────┐
                    │  Core   │
                    │ Domain  │
                    │(Business│
                    │ Logic)  │
                    └────┬────┘
                         │
                    ┌────▼────┐
                    │  Ports  │
                    │(Output) │
                    └────┬────┘
                         │
                    ┌────▼────┐
                    │Database │
                    │External │
                    │Services │
                    └─────────┘
```

## Quando usarlo

Usa l'Hexagonal Architecture Pattern quando:
- Hai logica di business complessa e importante
- Devi supportare multiple interfacce (web, API, mobile)
- Vuoi essere indipendente da database e framework
- Hai bisogno di testare facilmente la logica di business
- Devi integrare con molti sistemi esterni
- L'applicazione deve essere mantenibile a lungo termine

**NON usarlo quando:**
- Hai un'applicazione semplice e lineare
- Non hai logica di business complessa
- Non hai bisogno di cambiare tecnologie
- L'overhead di architettura non è giustificato
- Hai vincoli di tempo molto stretti
- L'applicazione è un prototipo

## Pro e contro

**I vantaggi:**
- Logica di business completamente isolata
- Facile da testare e mantenere
- Indipendente da tecnologie esterne
- Flessibile nell'integrazione
- Separazione chiara delle responsabilità
- Facile da estendere e modificare

**Gli svantaggi:**
- Complessità architetturale elevata
- Overhead di codice e astrazione
- Curva di apprendimento ripida
- Può essere eccessivo per progetti semplici
- Richiede disciplina nel design
- Può rallentare lo sviluppo iniziale

## Esempi di codice

### Pseudocodice
```
// Core Domain
class OrderService {
    constructor(orderRepository, paymentService, notificationService) {
        this.orderRepository = orderRepository
        this.paymentService = paymentService
        this.notificationService = notificationService
    }
    
    createOrder(orderData) {
        // Logica di business pura
        const order = new Order(orderData)
        this.validateOrder(order)
        this.calculateTotal(order)
        
        // Usa i port per le operazioni
        this.orderRepository.save(order)
        this.paymentService.processPayment(order)
        this.notificationService.sendConfirmation(order)
        
        return order
    }
}

// Port (Interfaccia)
interface OrderRepository {
    save(order)
    findById(id)
    findByCustomerId(customerId)
}

// Adapter (Implementazione)
class EloquentOrderRepository implements OrderRepository {
    save(order) {
        // Implementazione con Eloquent
    }
    
    findById(id) {
        // Implementazione con Eloquent
    }
}

// Inbound Adapter
class OrderController {
    constructor(orderService) {
        this.orderService = orderService
    }
    
    createOrder(request) {
        const order = this.orderService.createOrder(request.data)
        return response.json(order)
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema E-commerce Hexagonal](./esempio-completo/)** - Gestione ordini con architettura esagonale

L'esempio include:
- Core Domain con logica di business pura
- Ports per definire i contratti
- Adapters per database e servizi esterni
- Inbound adapters per web e API
- Dependency injection per collegare tutto
- Test isolati per ogni componente
- Interfaccia web per testare le funzionalità

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Repository Pattern](./02-repository/repository-pattern.md)** - Astrazione dell'accesso ai dati
- **[Service Layer Pattern](./03-service-layer/service-layer-pattern.md)** - Logica di business centralizzata
- **[Dependency Injection Pattern](./04-dependency-injection/dependency-injection-pattern.md)** - Iniezione delle dipendenze
- **[Clean Architecture Pattern](../00-fondamentali/22-clean-architecture/clean-architecture.md)** - Architettura pulita

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[Separation of Concerns](../00-fondamentali/06-separation-of-concerns/separation-of-concerns.md)** - Separazione delle responsabilità
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Sistemi bancari**: Logica di business isolata da database e interfacce
- **E-commerce**: Gestione ordini indipendente da tecnologie
- **Sistemi di fatturazione**: Logica di calcolo separata dall'UI
- **Sistemi di inventory**: Gestione scorte indipendente da database
- **Sistemi di ticketing**: Logica di business isolata da interfacce
- **Sistemi di gaming**: Game logic separata da rendering e input

## Anti-pattern

**Cosa NON fare:**
- Accoppiare la logica di business al framework
- Creare dipendenze dirette verso database o servizi esterni
- Mescolare logica di business con logica di presentazione
- Non definire port chiari e ben definiti
- Creare adapter troppo complessi
- Ignorare la dependency injection

## Troubleshooting

### Problemi comuni
- **Logica di business accoppiata**: Sposta la logica nel core domain
- **Test difficili**: Usa dependency injection e mock
- **Adapters complessi**: Semplifica e separa le responsabilità
- **Port mal definiti**: Definisci interfacce chiare e specifiche
- **Dependency injection complessa**: Usa un container DI

### Debug e monitoring
- Monitora le dipendenze tra i layer
- Traccia le chiamate ai port
- Misura le performance degli adapter
- Controlla l'isolamento del core domain
- Implementa logging per ogni layer

## Performance e considerazioni

### Impatto sulle risorse
- **CPU**: Overhead minimo per l'astrazione
- **Memoria**: Leggero aumento per gli oggetti di astrazione
- **I/O**: Nessun impatto diretto

### Scalabilità
- **Carico basso**: Performance eccellenti
- **Carico medio**: Performance buone con overhead minimo
- **Carico alto**: Performance buone, l'architettura non è un collo di bottiglia

### Colli di bottiglia
- **Core Domain**: Raramente un collo di bottiglia
- **Adapters**: Possono essere ottimizzati indipendentemente
- **Ports**: Nessun impatto sulle performance
- **Dependency Injection**: Overhead trascurabile

## Risorse utili

### Documentazione ufficiale
- [Hexagonal Architecture](https://alistair.cockburn.us/hexagonal-architecture/) - Alistair Cockburn
- [Ports and Adapters](https://herbertograca.com/2017/09/14/ports-adapters-architecture/) - Herbertograca

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Service Container](https://laravel.com/docs/container) - Dependency injection
- [Laravel Testing](https://laravel.com/docs/testing) - Test isolati

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Hexagonal Architecture Examples](https://github.com/hexagonal-architecture) - Esempi pratici
- [Clean Architecture in Laravel](https://github.com/clean-architecture-laravel) - Implementazioni Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
