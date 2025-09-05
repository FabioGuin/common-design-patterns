# Observer Pattern

## Cosa fa

Il Observer Pattern definisce una dipendenza uno-a-molti tra oggetti in modo che quando un oggetto cambia stato, tutti i suoi dipendenti vengono notificati e aggiornati automaticamente. È come avere un sistema di notifiche dove gli "osservatori" si iscrivono per ricevere aggiornamenti.

## Perché ti serve

Immagina di avere un'applicazione e-commerce dove quando un ordine cambia stato, devi:
- Inviare una email al cliente
- Aggiornare l'inventario
- Notificare il magazzino
- Aggiornare le statistiche
- Inviare una notifica push

Senza l'Observer Pattern, l'oggetto Order dovrebbe conoscere tutti questi sistemi. Con l'Observer:
- **Decoppli** gli oggetti
- **Aggiungi** facilmente nuovi osservatori
- **Rimuovi** osservatori senza modificare il codice esistente
- **Mantieni** il codice pulito e modulare

## Come funziona

Il pattern ha quattro componenti principali:

1. **Subject (Interfaccia)**: Definisce l'interfaccia per gestire osservatori
2. **ConcreteSubject**: Implementa il Subject e notifica gli osservatori
3. **Observer (Interfaccia)**: Definisce l'interfaccia per ricevere notifiche
4. **ConcreteObserver**: Implementazioni specifiche che reagiscono alle notifiche

## Schema visivo

```
Subject → notify() → Observer1
    ↓                Observer2
    ↓                Observer3
    ↓                Observer4
```

## Quando usarlo

- **Event handling** in applicazioni
- **Model-View** architectures
- **Publish-Subscribe** systems
- **Reactive programming**
- **Laravel Events** e **Listeners**
- **Real-time notifications**

## Pro e contro

### Pro
- **Loose coupling**: Subject e Observer sono indipendenti
- **Dynamic relationships**: Puoi aggiungere/rimuovere osservatori a runtime
- **Broadcast communication**: Un subject può notificare molti observer
- **Open/Closed principle**: Facile estendere senza modificare il codice esistente

### Contro
- **Unexpected updates**: Gli observer possono essere chiamati inaspettatamente
- **Memory leaks**: Se non si rimuovono gli observer
- **Performance**: Molti observer possono rallentare le notifiche
- **Debugging**: Difficile tracciare il flusso delle notifiche

## Esempi di codice

### Pseudocodice
```
// Interfaccia Subject
interface SubjectInterface {
    attach(observer: ObserverInterface)
    detach(observer: ObserverInterface)
    notify()
}

// Interfaccia Observer
interface ObserverInterface {
    update(subject: SubjectInterface)
}

// Subject concreto
class Order implements SubjectInterface {
    private observers = []
    private status: string
    private data: array
    
    constructor(status: string = 'pending') {
        this.status = status
        this.data = []
    }
    
    attach(observer: ObserverInterface) {
        this.observers.add(observer)
    }
    
    detach(observer: ObserverInterface) {
        key = this.observers.indexOf(observer)
        if (key != -1) {
            this.observers.removeAt(key)
        }
    }
    
    notify() {
        for observer in this.observers {
            observer.update(this)
        }
    }
    
    setStatus(status: string) {
        this.status = status
        this.notify() // Notifica tutti gli observer
    }
    
    getStatus() returns string {
        return this.status
    }
    
    getData() returns array {
        return this.data
    }
}

// Observer concreto
class EmailNotificationObserver implements ObserverInterface {
    update(subject: SubjectInterface) {
        if (subject instanceof Order) {
            this.sendEmail(subject)
        }
    }
    
    private sendEmail(order: Order) {
        print("Sending email for order status: " + order.getStatus())
        // Logica per inviare email
    }
}

class InventoryObserver implements ObserverInterface {
    update(subject: SubjectInterface) {
        if (subject instanceof Order) {
            this.updateInventory(subject)
        }
    }
    
    private updateInventory(order: Order) {
        print("Updating inventory for order status: " + order.getStatus())
        // Logica per aggiornare inventario
    }
}

// Utilizzo
order = new Order('pending')

// Aggiungi observer
order.attach(new EmailNotificationObserver())
order.attach(new InventoryObserver())

// Cambia stato - notifica automaticamente tutti gli observer
order.setStatus('confirmed')
order.setStatus('shipped')
order.setStatus('delivered')
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di eventi personalizzato
- Notifiche real-time
- Logging automatico
- Sistema di cache con invalidazione

## Correlati

- **Mediator Pattern**: Per comunicazione centralizzata
- **Command Pattern**: Per incapsulare operazioni
- **Strategy Pattern**: Per diversi tipi di notifiche

## Esempi di uso reale

- **Laravel Events**: Sistema di eventi nativo
- **Laravel Broadcasting**: Notifiche real-time
- **Laravel Notifications**: Sistema di notifiche
- **Model observers**: Per reazioni ai cambiamenti del modello
- **Cache invalidation**
- **Real-time dashboards**

## Anti-pattern

❌ **Observer che fa troppo**: Un observer che gestisce troppe responsabilità
```
// SBAGLIATO
class GodObserver implements ObserverInterface {
    update(subject: SubjectInterface) {
        this.sendEmail()
        this.updateDatabase()
        this.sendSMS()
        this.updateCache()
        this.logActivity()
        this.notifySlack()
        // Troppo complesso!
    }
}
```

✅ **Observer focalizzato**: Un observer per una responsabilità specifica
```
// GIUSTO
class EmailObserver implements ObserverInterface {
    update(subject: SubjectInterface) {
        this.sendEmail(subject)
    }
}
```

## Troubleshooting

**Problema**: Observer non viene notificato
**Soluzione**: Verifica che sia stato attaccato correttamente al subject

**Problema**: Memory leak con observer
**Soluzione**: Assicurati di rimuovere gli observer quando non servono più

**Problema**: Observer eseguiti in ordine sbagliato
**Soluzione**: Implementa un sistema di priorità per gli observer

## Performance e considerazioni

- **Async processing**: Per observer pesanti
- **Error handling**: Gestisci errori negli observer
- **Priority system**: Per controllare l'ordine di esecuzione
- **Batching**: Per raggruppare notifiche multiple

## Risorse utili

- [Laravel Events](https://laravel.com/docs/events)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Laravel Notifications](https://laravel.com/docs/notifications)
- [Observer Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/observer)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
