# Adapter Pattern

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

L'Adapter Pattern ti permette di far funzionare insieme due classi che hanno interfacce incompatibili. È come avere un adattatore per le prese elettriche: se hai un dispositivo con spina americana e una presa europea, usi un adattatore per farli funzionare insieme.

## Perché ti serve

Immagina di avere un sistema di pagamenti che funziona solo con PayPal, ma ora devi integrare Stripe. Invece di riscrivere tutto il codice, crei un adapter che fa sembrare Stripe come PayPal al tuo sistema esistente.

**Problemi che risolve:**
- Integrare librerie di terze parti con interfacce diverse
- Mantenere compatibilità con sistemi legacy
- Evitare di modificare codice esistente quando aggiungi nuove funzionalità
- Unificare interfacce diverse sotto un'interfaccia comune

## Come funziona

1. **Identifica l'interfaccia target** che il tuo codice si aspetta
2. **Crea una classe adapter** che implementa questa interfaccia
3. **L'adapter contiene un riferimento** alla classe che vuoi adattare
4. **L'adapter traduce le chiamate** dal formato target al formato della classe adattata
5. **Il tuo codice usa l'adapter** come se fosse la classe originale

## Schema visivo

```
Client Code
    ↓
Target Interface (PaymentProcessor)
    ↓
Adapter (StripeAdapter)
    ↓
Adaptee (Stripe API)

Flusso:
Client → PaymentProcessor::process() 
      → StripeAdapter::process() 
      → Stripe::charge()
```

**Esempio concreto:**
```
Sistema esistente: PayPal::makePayment($amount)
Nuovo sistema:     Stripe::charge($amount, $currency)

Adapter:          StripeAdapter::makePayment($amount)
                  → Stripe::charge($amount, 'USD')
```

## Quando usarlo

Usa l'Adapter Pattern quando:
- Devi integrare una libreria di terze parti con un'interfaccia diversa
- Vuoi mantenere compatibilità con sistemi legacy senza modificarli
- Hai bisogno di unificare interfacce diverse sotto un'interfaccia comune
- Stai migrando da un sistema a un altro gradualmente
- Vuoi testare il tuo codice con implementazioni mock

**NON usarlo quando:**
- Le interfacce sono già compatibili
- Puoi modificare direttamente il codice esistente
- L'adattamento richiede troppe trasformazioni complesse
- Stai creando un nuovo sistema da zero

## Pro e contro

**I vantaggi:**
- Permette l'integrazione di librerie incompatibili senza modificare il codice esistente
- Mantiene il principio di apertura/chiusura (aperto per estensione, chiuso per modifica)
- Facilita i test usando mock objects
- Riduce l'accoppiamento tra sistemi diversi
- Permette migrazioni graduali tra sistemi

**Gli svantaggi:**
- Aggiunge complessità al codice con una classe in più
- Può creare overhead di performance per le chiamate multiple
- Può nascondere differenze importanti tra le interfacce
- Richiede manutenzione quando cambiano le interfacce adattate

## Esempi di codice

### Pseudocodice
```
// Interfaccia target
interface PaymentProcessor {
    processPayment(amount)
}

// Classe da adattare
class StripeAPI {
    charge(amount, currency) {
        // Logica Stripe
    }
}

// Adapter
class StripeAdapter implements PaymentProcessor {
    private stripe = new StripeAPI()
    
    processPayment(amount) {
        return this.stripe.charge(amount, 'USD')
    }
}

// Utilizzo
processor = new StripeAdapter()
processor.processPayment(100) // Usa Stripe internamente
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Pagamenti con Adapter](./esempio-completo/)** - Integrazione di diversi provider di pagamento

L'esempio include:
- Interfaccia comune per i pagamenti
- Adapter per Stripe e PayPal
- Controller Laravel per gestire i pagamenti
- Vista per testare i diversi provider
- Configurazione per switchare tra provider

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Facade Pattern](./05-facade/facade-pattern.md)** - Fornisce un'interfaccia semplificata a un sottosistema complesso
- **[Bridge Pattern](./02-bridge/bridge-pattern.md)** - Separa l'astrazione dall'implementazione
- **[Decorator Pattern](./04-decorator/decorator-pattern.md)** - Aggiunge funzionalità dinamicamente
- **[Proxy Pattern](./07-proxy/proxy-pattern.md)** - Fornisce un placeholder per un oggetto

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Integrazione di provider di pagamento** (Stripe, PayPal, Square) in e-commerce
- **Adattamento di database** (MySQL, PostgreSQL, MongoDB) con ORM comuni
- **Integrazione di servizi di notifica** (Email, SMS, Push) con interfacce unificate
- **Adattamento di API esterne** per mantenere compatibilità con sistemi legacy
- **Integrazione di librerie di logging** (Monolog, Log4j) con sistemi esistenti

## Anti-pattern

**Cosa NON fare:**
- Non creare adapter che cambiano troppo il comportamento originale
- Non usare adapter quando puoi modificare direttamente il codice esistente
- Non creare adapter che nascondono errori o eccezioni importanti
- Non creare troppi livelli di adapter che rendono il codice difficile da seguire
- Non usare adapter per risolvere problemi di design architetturale

## Troubleshooting

### Problemi comuni
- **Adapter non funziona**: Verifica che l'interfaccia target sia implementata correttamente
- **Performance degradate**: Considera se l'adapter aggiunge troppi livelli di chiamate
- **Errori nascosti**: Assicurati che l'adapter propaghi correttamente le eccezioni
- **Difficoltà di debug**: Aggiungi logging per tracciare le chiamate attraverso l'adapter

### Debug e monitoring
- Usa logging per tracciare le chiamate attraverso l'adapter
- Monitora le performance per identificare colli di bottiglia
- Testa sia l'interfaccia target che quella adattata
- Verifica che le eccezioni vengano propagate correttamente

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Aggiunge un oggetto in più per ogni adapter
- **CPU**: Overhead minimo per le chiamate di traduzione
- **I/O**: Dipende dalla complessità delle trasformazioni dati

### Scalabilità
- **Carico basso**: Impatto trascurabile
- **Carico medio**: Overhead minimo, facilmente gestibile
- **Carico alto**: Considera caching se le trasformazioni sono costose

### Colli di bottiglia
- **Chiamate multiple**: Se l'adapter fa troppe chiamate interne
- **Trasformazioni complesse**: Se la logica di adattamento è pesante
- **Serializzazione**: Se l'adapter converte tra formati di dati diversi

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns/adapter) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Service Container](https://laravel.com/docs/container) - Gestione dipendenze
- [Laravel Facades](https://laravel.com/docs/facades) - Pattern simile all'Adapter

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Laravel Adapter Examples](https://github.com/laravel/patterns) - Esempi specifici per Laravel

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
