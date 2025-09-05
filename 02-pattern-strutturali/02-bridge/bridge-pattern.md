# Bridge Pattern

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

Il Bridge Pattern separa l'astrazione dall'implementazione, permettendo a entrambe di variare indipendentemente. È come avere un ponte tra due isole: puoi cambiare il tipo di ponte (legno, ferro, cemento) senza dover ricostruire le isole.

## Perché ti serve

Immagina di avere un sistema di notifiche che deve funzionare con diversi canali (email, SMS, push) e diversi formati (HTML, testo, JSON). Senza Bridge, dovresti creare una classe per ogni combinazione (EmailHTML, EmailText, SMSHTML, ecc.). Con Bridge, hai un'astrazione per i canali e un'implementazione per i formati.

**Problemi che risolve:**
- Evita l'esplosione di classi quando hai due dimensioni che cambiano indipendentemente
- Permette di cambiare implementazione senza toccare l'astrazione
- Facilita l'aggiunta di nuove implementazioni
- Riduce l'accoppiamento tra astrazione e implementazione

## Come funziona

1. **Crea un'interfaccia di implementazione** che definisce i metodi comuni
2. **Crea classi concrete** che implementano questa interfaccia
3. **Crea una classe di astrazione** che contiene un riferimento all'implementazione
4. **L'astrazione delega** le chiamate all'implementazione
5. **Puoi cambiare implementazione** senza modificare l'astrazione

## Schema visivo

```
Abstraction (Notification)
    ↓
Implementation (MessageFormatter)
    ↓
Concrete Implementations (HTMLFormatter, TextFormatter, JSONFormatter)

Esempio:
Notification (Email) → HTMLFormatter
Notification (SMS) → TextFormatter
Notification (Push) → JSONFormatter

Puoi anche:
Notification (Email) → TextFormatter
Notification (SMS) → HTMLFormatter
```

**Flusso:**
```
Client → Notification::send() 
      → MessageFormatter::format() 
      → ConcreteFormatter::format()
```

## Quando usarlo

Usa il Bridge Pattern quando:
- Hai due dimensioni che cambiano indipendentemente
- Vuoi evitare l'esplosione di classi per ogni combinazione
- Hai bisogno di cambiare implementazione a runtime
- Vuoi separare l'astrazione dall'implementazione
- Stai costruendo un framework o libreria

**NON usarlo quando:**
- Hai solo una dimensione che cambia
- L'astrazione e l'implementazione sono strettamente accoppiate
- Le combinazioni sono limitate e non cambieranno
- Stai creando un sistema semplice senza variazioni

## Pro e contro

**I vantaggi:**
- Separa l'astrazione dall'implementazione
- Evita l'esplosione di classi per ogni combinazione
- Permette di cambiare implementazione a runtime
- Facilita l'aggiunta di nuove implementazioni
- Riduce l'accoppiamento tra componenti

**Gli svantaggi:**
- Aggiunge complessità al design
- Può essere difficile da capire inizialmente
- Richiede più codice per implementazioni semplici
- Può creare overhead se non necessario

## Esempi di codice

### Pseudocodice
```
// Interfaccia di implementazione
interface MessageFormatter {
    format(message, data)
}

// Implementazioni concrete
class HTMLFormatter implements MessageFormatter {
    format(message, data) {
        return "<html><body>{$message}</body></html>"
    }
}

class TextFormatter implements MessageFormatter {
    format(message, data) {
        return message
    }
}

// Astrazione
abstract class Notification {
    protected formatter: MessageFormatter
    
    constructor(formatter: MessageFormatter) {
        this.formatter = formatter
    }
    
    abstract send(message, data)
}

// Astrazioni concrete
class EmailNotification extends Notification {
    send(message, data) {
        formatted = this.formatter.format(message, data)
        // Invia email con formatted
    }
}

// Utilizzo
emailHTML = new EmailNotification(new HTMLFormatter())
emailText = new EmailNotification(new TextFormatter())
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Notifiche con Bridge](./esempio-completo/)** - Notifiche multi-canale con formati diversi

L'esempio include:
- Interfaccia per formattatori di messaggi
- Implementazioni concrete (HTML, Text, JSON)
- Astrazioni per diversi canali (Email, SMS, Push)
- Controller Laravel per gestire le notifiche
- Vista per testare le diverse combinazioni
- Configurazione per switchare tra formattatori

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Adapter Pattern](./01-adapter/adapter-pattern.md)** - Adatta interfacce incompatibili
- **[Strategy Pattern](../03-pattern-comportamentali/09-strategy/strategy-pattern.md)** - Cambia algoritmo a runtime
- **[Abstract Factory Pattern](../01-pattern-creazionali/03-abstract-factory/abstract-factory-pattern.md)** - Crea famiglie di oggetti
- **[Decorator Pattern](./04-decorator/decorator-pattern.md)** - Aggiunge funzionalità dinamicamente

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Sistemi di notifica** con diversi canali e formati
- **Rendering di documenti** con diversi formati di output (PDF, HTML, Word)
- **Database abstraction** con diversi driver (MySQL, PostgreSQL, SQLite)
- **Sistemi di logging** con diversi formati e destinazioni
- **API clients** con diversi protocolli (REST, GraphQL, gRPC)

## Anti-pattern

**Cosa NON fare:**
- Non usare Bridge quando hai solo una dimensione che cambia
- Non creare implementazioni troppo specifiche che limitano la flessibilità
- Non accoppiare strettamente astrazione e implementazione
- Non usare Bridge per risolvere problemi di design architetturale
- Non creare troppi livelli di astrazione che rendono il codice difficile da seguire

## Troubleshooting

### Problemi comuni
- **Implementazione non funziona**: Verifica che l'interfaccia di implementazione sia corretta
- **Astrazione troppo complessa**: Semplifica l'astrazione se non serve
- **Difficoltà di debug**: Aggiungi logging per tracciare le chiamate
- **Performance degradate**: Considera se l'overhead del Bridge è giustificato

### Debug e monitoring
- Usa logging per tracciare le chiamate tra astrazione e implementazione
- Monitora le performance per identificare colli di bottiglia
- Testa sia l'astrazione che l'implementazione separatamente
- Verifica che le eccezioni vengano propagate correttamente

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Aggiunge un oggetto in più per ogni astrazione
- **CPU**: Overhead minimo per le chiamate di delega
- **I/O**: Dipende dalla complessità dell'implementazione

### Scalabilità
- **Carico basso**: Impatto trascurabile
- **Carico medio**: Overhead minimo, facilmente gestibile
- **Carico alto**: Considera caching se le implementazioni sono costose

### Colli di bottiglia
- **Chiamate multiple**: Se l'astrazione fa troppe chiamate all'implementazione
- **Implementazioni complesse**: Se la logica di implementazione è pesante
- **Serializzazione**: Se l'astrazione e l'implementazione sono su macchine diverse

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns/bridge) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Service Container](https://laravel.com/docs/container) - Gestione dipendenze
- [Laravel Notifications](https://laravel.com/docs/notifications) - Sistema di notifiche

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Laravel Bridge Examples](https://github.com/laravel/patterns) - Esempi specifici per Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
