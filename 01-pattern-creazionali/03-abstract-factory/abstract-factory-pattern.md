# Abstract Factory Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Pattern correlati](#pattern-correlati)
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

L'Abstract Factory ti permette di creare famiglie di oggetti correlati senza specificare le loro classi concrete. Definisce un'interfaccia per creare oggetti, ma lascia alle sottoclassi decidere quale famiglia di prodotti creare.

È come avere un'azienda che produce automobili complete: ogni stabilimento produce una famiglia di componenti (motore, carrozzeria, interni) che sono compatibili tra loro, ma ogni stabilimento produce componenti di stile diverso.

## Perché ti serve

Immagina di dover creare un'interfaccia utente che deve funzionare su diversi sistemi operativi (Windows, macOS, Linux). Senza Abstract Factory, finiresti con:

- Codice che conosce troppi dettagli di ogni sistema operativo
- Logica di creazione sparsa e duplicata
- Difficoltà ad aggiungere nuovi sistemi operativi
- Violazione del principio "aperto per estensione, chiuso per modifica"

L'Abstract Factory risolve questo: una factory astratta sa come creare famiglie di componenti, e le factory concrete decidono quale famiglia specifica creare.

## Come funziona

Il meccanismo è più complesso del Factory Method:
1. **AbstractFactory**: Definisce interfacce per creare famiglie di prodotti
2. **ConcreteFactory**: Implementa le interfacce per creare prodotti di una famiglia specifica
3. **AbstractProduct**: Interfaccia base per i prodotti di una famiglia
4. **ConcreteProduct**: Implementazione concreta di un prodotto di una famiglia specifica

Il client usa solo le interfacce astratte, senza sapere quale famiglia concreta viene creata.

## Schema visivo

```
Flusso di creazione:
Client → AbstractFactory → createProductA()
                        → createProductB()
                        ↓
                   ConcreteFactory1 → new ConcreteProductA1()
                                  → new ConcreteProductB1()
                        ↓
                   Restituisce famiglia di prodotti

Gerarchia delle classi:
AbstractFactory
    ↓
ConcreteFactory1 → createProductA() → ConcreteProductA1
                → createProductB() → ConcreteProductB1
ConcreteFactory2 → createProductA() → ConcreteProductA2
                → createProductB() → ConcreteProductB2
```

*Il diagramma mostra come ogni ConcreteFactory crea una famiglia completa di prodotti correlati, garantendo la compatibilità tra i componenti.*

## Quando usarlo

Usa l'Abstract Factory quando:
- Devi creare famiglie di oggetti correlati che devono essere compatibili
- Gestisci diversi temi o stili per la stessa applicazione
- Crei interfacce utente per diversi sistemi operativi
- Gestisci diversi provider di servizi (database, cache, logging)
- Hai bisogno di garantire la compatibilità tra componenti
- Vuoi estendere facilmente il sistema con nuove famiglie

**NON usarlo quando:**
- Hai solo un tipo di prodotto da creare
- I prodotti non sono correlati tra loro
- L'overhead del pattern non è giustificato
- La logica di creazione è troppo complessa per una singola factory

## Pro e contro

**I vantaggi:**
- Garantisce la compatibilità tra prodotti di una famiglia
- Elimina l'accoppiamento tra client e classi concrete
- Facilita l'aggiunta di nuove famiglie di prodotti
- Rispetta il principio Open/Closed
- Migliora la testabilità

**Gli svantaggi:**
- Aumenta significativamente la complessità del codice
- Richiede molte classi e interfacce
- Può essere eccessivo per famiglie semplici
- Difficile da estendere se le famiglie cambiano struttura

## Esempi di codice

### Pseudocodice
```
// Interfacce per i prodotti
interface AbstractProductA {
    method operationA()
}

interface AbstractProductB {
    method operationB()
}

// Prodotti concreti per famiglia 1
class ConcreteProductA1 implements AbstractProductA {
    method operationA() {
        return "Product A1 operation"
    }
}

class ConcreteProductB1 implements AbstractProductB {
    method operationB() {
        return "Product B1 operation"
    }
}

// Factory astratta
interface AbstractFactory {
    method createProductA() returns AbstractProductA
    method createProductB() returns AbstractProductB
}

// Factory concrete
class ConcreteFactory1 implements AbstractFactory {
    method createProductA() returns AbstractProductA {
        return new ConcreteProductA1()
    }
    
    method createProductB() returns AbstractProductB {
        return new ConcreteProductB1()
    }
}

// Utilizzo
factory = new ConcreteFactory1()
productA = factory.createProductA()
productB = factory.createProductB()
// productA e productB sono compatibili
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Temi UI](./esempio-completo/)** - Sistema completo per gestire diversi temi di interfaccia utente

L'esempio include:
- Factory per creare famiglie di componenti UI (bottoni, input, card)
- Temi diversi (Material Design, Bootstrap, Custom)
- Integrazione con Blade templates
- Service Provider per registrare le factory
- Controller con dependency injection
- Test unitari per le factory
- API RESTful per gestire i temi

## Pattern correlati

- **Factory Method**: Se hai bisogno di creare singoli oggetti invece di famiglie
- **Builder**: Per costruire oggetti complessi passo dopo passo
- **Prototype**: Per clonare oggetti esistenti invece di crearli da zero
- **Singleton**: Spesso usato per gestire le istanze delle factory

## Esempi di uso reale

- **Laravel Service Container**: Laravel usa Abstract Factory per gestire diversi provider di servizi
- **Symfony Form Factory**: Symfony usa Abstract Factory per creare famiglie di form fields
- **PHPUnit Test Doubles**: PHPUnit usa Abstract Factory per creare famiglie di mock e stub
- **Document Generators**: Librerie come TCPDF usano Abstract Factory per creare famiglie di documenti
- **Payment Gateways**: Sistemi di pagamento usano Abstract Factory per gestire diversi provider

## Anti-pattern

**Cosa NON fare:**
- **Factory con troppi prodotti**: Evita factory che creano troppi prodotti diversi
- **Factory che conosce tutto**: Non far conoscere alla factory dettagli specifici delle classi concrete
- **Factory senza interfacce**: Sempre definire interfacce astratte per i prodotti e le factory
- **Factory per oggetti semplici**: Non usare Abstract Factory per oggetti che si creano facilmente
- **Factory troppo complesse**: Evita factory che fanno troppo lavoro, violano il principio di responsabilità singola

## Troubleshooting

### Problemi comuni
- **"Cannot instantiate abstract class"**: Assicurati di implementare tutte le interfacce astratte
- **"Wrong product family returned"**: Verifica che il ConcreteFactory restituisca prodotti della stessa famiglia
- **"Factory method not found"**: Controlla che i metodi factory siano definiti correttamente nell'interfaccia
- **"Product interface not implemented"**: Assicurati che i ConcreteProduct implementino le interfacce corrette

### Debug e monitoring
- **Log delle creazioni**: Aggiungi logging per tracciare quale famiglia di prodotti viene creata
- **Controllo compatibilità**: Verifica che i prodotti creati siano compatibili tra loro
- **Performance factory**: Monitora il tempo di creazione per identificare factory lente
- **Memory usage**: Traccia l'uso di memoria per verificare che non ci siano leak

### Metriche utili
- **Numero di famiglie create**: Per capire l'utilizzo dei diversi factory
- **Tempo di creazione famiglia**: Per identificare factory che potrebbero essere ottimizzate
- **Errori di compatibilità**: Per identificare problemi con le famiglie di prodotti
- **Utilizzo interfacce**: Per verificare che i client usino le interfacce astratte

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Overhead significativo per le classi factory e interfacce (tipicamente 20-50KB)
- **CPU**: La creazione tramite Abstract Factory è più lenta del `new` diretto (5-15ms overhead)
- **I/O**: Se i prodotti creano risorse esterne, l'I/O è gestito dai prodotti stessi

### Scalabilità
- **Carico basso**: Funziona bene, overhead accettabile
- **Carico medio**: L'overhead è compensato dalla flessibilità e organizzazione
- **Carico alto**: Può diventare un collo di bottiglia se le factory sono complesse

### Colli di bottiglia
- **Factory complesse**: Se la logica di creazione è troppo elaborata
- **Troppe famiglie**: Gestire centinaia di ConcreteFactory può diventare complesso
- **Memory allocation**: Creare molte famiglie di oggetti può causare frammentazione
- **Reflection**: Se usi reflection per la creazione dinamica, può essere lento

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru - Abstract Factory](https://refactoring.guru/design-patterns/abstract-factory) - Spiegazione visuale con esempi

### Laravel specifico
- [Laravel Service Container](https://laravel.com/docs/container) - Come Laravel gestisce le dipendenze
- [Laravel Service Providers](https://laravel.com/docs/providers) - Per registrare servizi

### Esempi e tutorial
- [Abstract Factory Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
- [Abstract Factory vs Factory Method](https://www.geeksforgeeks.org/abstract-factory-pattern/) - Confronto dettagliato tra i pattern

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
