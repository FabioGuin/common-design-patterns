# Value Object Pattern

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

Il Value Object è un oggetto che rappresenta un concetto del dominio il cui valore è determinato dai suoi attributi, non dalla sua identità. Due Value Object con gli stessi valori sono considerati uguali, anche se sono istanze diverse.

Pensa a un indirizzo email: se due oggetti hanno lo stesso valore "mario@example.com", sono la stessa cosa, indipendentemente da quando e dove sono stati creati.

## Perché ti serve

Immagina di gestire un sistema di e-commerce dove devi lavorare con prezzi, indirizzi, e date. Senza Value Object finisci con:

- Prezzi che possono essere negativi o con valute diverse
- Indirizzi incompleti o malformati
- Date che non rispettano il formato
- Confronti tra oggetti che non funzionano come ti aspetti
- Logica di business sparsa ovunque nel codice

Il Value Object ti aiuta a:
- Incapsulare la logica di validazione
- Garantire l'immutabilità dei dati
- Rendere il codice più espressivo e sicuro
- Evitare errori comuni con i tipi primitivi

## Come funziona

1. **Creazione**: Definisci una classe che rappresenta il concetto del dominio
2. **Validazione**: Nel costruttore, valida che i valori siano corretti
3. **Immutabilità**: Non permettere modifiche dopo la creazione
4. **Uguaglianza**: Implementa il confronto basato sui valori, non sull'identità
5. **Comportamento**: Aggiungi metodi per operazioni logiche del dominio

Il pattern funziona creando oggetti che si comportano come valori primitivi ma con la potenza degli oggetti.

## Schema visivo

```
Creazione Value Object:
Input → [Validazione] → [Immutabilità] → Value Object

Confronto Value Object:
Value Object A (mario@example.com) == Value Object B (mario@example.com) → true
Value Object A (mario@example.com) == Value Object C (luigi@example.com) → false

Operazioni:
Value Object → [Metodi di dominio] → Nuovo Value Object
```

## Quando usarlo

Usa il Value Object quando:
- Hai concetti del dominio che hanno una logica di validazione specifica
- Vuoi evitare l'uso di tipi primitivi per concetti complessi
- Hai bisogno di oggetti immutabili per thread safety
- Vuoi rendere il codice più espressivo e autodocumentante
- Devi gestire unità di misura, valute, o formati specifici
- Vuoi centralizzare la logica di business di un concetto

**NON usarlo quando:**
- Hai oggetti che cambiano stato frequentemente
- Il concetto ha un'identità univoca (usa Entity invece)
- La logica è troppo semplice per giustificare una classe
- Hai bisogno di performance estreme con tipi primitivi

## Pro e contro

**I vantaggi:**
- **Sicurezza dei tipi**: Previene errori con tipi primitivi
- **Immutabilità**: Thread-safe e prevedibile
- **Espressività**: Il codice diventa più leggibile
- **Validazione centralizzata**: Logica di business in un posto
- **Facilità di test**: Oggetti semplici da testare
- **Riusabilità**: Stesso Value Object in contesti diversi

**Gli svantaggi:**
- **Overhead di memoria**: Più oggetti in memoria
- **Complessità**: Più classi da gestire
- **Performance**: Leggero impatto rispetto ai primitivi
- **Curva di apprendimento**: Team deve capire il concetto

## Esempi di codice

### Pseudocodice
```
// Struttura base del Value Object
class Email {
    private string value
    
    constructor(string email) {
        if (!isValidEmail(email)) {
            throw new InvalidEmailException()
        }
        this.value = email
    }
    
    public getValue() {
        return this.value
    }
    
    public equals(Email other) {
        return this.value == other.value
    }
    
    public toString() {
        return this.value
    }
}

// Utilizzo
email1 = new Email("mario@example.com")
email2 = new Email("mario@example.com")
// email1.equals(email2) è true
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema E-commerce con Value Object](./esempio-completo/)** - Gestione di prezzi, indirizzi e email con Value Object

L'esempio include:
- Value Object per Email con validazione
- Value Object per Prezzo con valuta
- Value Object per Indirizzo completo
- Controller che dimostra l'uso pratico
- Validazione automatica con Form Request
- Interfaccia web per testare i Value Object

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[DTO Pattern](./04-dto/dto-pattern.md)** - Trasferimento dati tra layer
- **[Repository Pattern](./02-repository/repository-pattern.md)** - Accesso ai dati con Value Object
- **[Service Layer Pattern](./03-service-layer/service-layer-pattern.md)** - Logica di business con Value Object
- **[Specification Pattern](./06-specification/specification-pattern.md)** - Validazione complessa con Value Object

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[Domain-Driven Design](../00-fondamentali/23-domain-driven-design/domain-driven-design.md)** - Modellazione del dominio
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **Sistemi finanziari**: Gestione di valute, importi e tassi di cambio
- **E-commerce**: Prezzi, indirizzi, codici prodotto
- **Sistemi di autenticazione**: Email, password, token
- **Applicazioni geografiche**: Coordinate, indirizzi, distanze
- **Sistemi di misurazione**: Temperature, pesi, dimensioni

## Anti-pattern

**Cosa NON fare:**
- **Value Object mutabili**: Permettere modifiche dopo la creazione
- **Validazione nel getter**: Validare solo quando si accede al valore
- **Confronti per identità**: Usare == invece di equals()
- **Logica di business fuori dal Value Object**: Spargere la logica ovunque
- **Value Object troppo grandi**: Includere troppi concetti in un solo oggetto
- **Ignorare l'immutabilità**: Permettere modifiche dirette ai campi

## Troubleshooting

### Problemi comuni
- **Value Object non immutabile**: Assicurati che i campi siano readonly e non ci siano setter
- **Confronti che non funzionano**: Implementa correttamente equals() e hashCode()
- **Validazione mancante**: Aggiungi validazione nel costruttore
- **Performance lente**: Considera se hai bisogno di Value Object o se i primitivi sono sufficienti
- **Serializzazione**: Implementa correttamente Serializable se necessario

### Debug e monitoring
- **Log dei Value Object**: Aggiungi toString() per debugging
- **Validazione in produzione**: Monitora errori di validazione
- **Performance**: Traccia creazione di Value Object in loop
- **Memoria**: Monitora uso memoria con molti Value Object

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Ogni Value Object occupa più memoria di un primitivo
- **CPU**: Leggero overhead per creazione e garbage collection
- **I/O**: Nessun impatto diretto, ma può influenzare serializzazione

### Scalabilità
- **Carico basso**: Nessun problema, overhead trascurabile
- **Carico medio**: Performance accettabile per la maggior parte dei casi
- **Carico alto**: Considera pooling o caching per Value Object costosi

### Colli di bottiglia
- **Creazione frequente**: Evita creazione in loop stretti
- **Garbage collection**: Troppi Value Object possono causare GC pressure
- **Serializzazione**: Value Object complessi possono essere lenti da serializzare

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali
- [Domain-Driven Design](https://martinfowler.com/bliki/ValueObject.html) - Martin Fowler su Value Object

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Value Objects](https://laravel.com/docs/validation) - Validazione con Value Object
- [Laravel Casts](https://laravel.com/docs/eloquent-mutators#attribute-casting) - Casting con Value Object

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Laravel Value Objects Package](https://github.com/spatie/laravel-value-objects) - Package per Value Object
- [PHP Value Objects](https://github.com/php-value-objects) - Esempi specifici per PHP

### Strumenti di supporto
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
