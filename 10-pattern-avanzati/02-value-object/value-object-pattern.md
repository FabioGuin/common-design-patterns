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

Il Value Object Pattern ti permette di creare oggetti che rappresentano valori immutabili. Invece di usare tipi primitivi come stringhe o numeri per rappresentare concetti complessi, crei oggetti che incapsulano il valore e le regole di business associate.

Pensa a un indirizzo email. Invece di usare una semplice stringa, crei un oggetto `Email` che valida il formato, normalizza il valore e fornisce metodi per confrontarlo.

## Perché ti serve

Immagina di lavorare con indirizzi email, prezzi, date o coordinate geografiche. Senza Value Object:

- La validazione è sparsa in tutto il codice
- È facile confondere parametri (prezzo vs quantità)
- Non hai garanzie che i valori siano validi
- Il codice diventa difficile da leggere e mantenere

Con il Value Object Pattern:
- **Validazione centralizzata**: Tutte le regole in un posto
- **Type safety**: Il compilatore ti aiuta a evitare errori
- **Immutabilità**: I valori non possono essere modificati accidentalmente
- **Semantica chiara**: Il codice esprime meglio l'intenzione

## Come funziona

1. **Definisci una classe** che rappresenta il valore
2. **Rendi l'oggetto immutabile** (proprietà readonly, costruttore privato)
3. **Implementa la validazione** nel costruttore
4. **Override di equals/hashCode** per il confronto per valore
5. **Fornisci metodi di utilità** per operazioni comuni

## Schema visivo

```
Primitive Value → Value Object
     ↓
"user@example.com" → Email Object
     ↓
- Validazione formato
- Normalizzazione
- Metodi di confronto
- Immutabilità garantita

Uso nel codice:
email1 = Email.create("user@example.com")
email2 = Email.create("user@example.com")
email1.equals(email2) → true (stesso valore)
```

## Quando usarlo

Usa il Value Object Pattern quando:
- Hai valori che hanno regole di business specifiche
- Vuoi evitare "primitive obsession" (uso eccessivo di tipi primitivi)
- Hai bisogno di validazione centralizzata per certi valori
- Vuoi rendere il codice più espressivo e type-safe
- Lavori con valori che hanno operazioni specifiche (es: date, monete, coordinate)

**NON usarlo quando:**
- I valori sono semplici e non hanno regole di business
- Hai bisogno di modificare i valori frequentemente
- La performance è critica e gli oggetti aggiungono overhead
- I valori sono solo per display e non hanno logica

## Pro e contro

**I vantaggi:**
- **Type safety**: Previene errori di tipo a compile time
- **Validazione centralizzata**: Tutte le regole in un posto
- **Immutabilità**: Previene modifiche accidentali
- **Semantica chiara**: Il codice è più espressivo
- **Riutilizzabilità**: Puoi usare gli stessi value object ovunque

**Gli svantaggi:**
- **Overhead di memoria**: Oggetti invece di primitivi
- **Complessità aggiuntiva**: Più classi da mantenere
- **Performance**: Creazione di oggetti può essere più lenta
- **Curva di apprendimento**: I developer devono capire il pattern

## Esempi di codice

### Pseudocodice

```
// Value Object per Email
class Email {
    private readonly string value
    
    private constructor(string email) {
        if (!isValidEmail(email)) {
            throw new InvalidEmailException()
        }
        this.value = normalizeEmail(email)
    }
    
    public static create(string email) {
        return new Email(email)
    }
    
    public getValue() {
        return this.value
    }
    
    public equals(Email other) {
        return this.value === other.value
    }
    
    public toString() {
        return this.value
    }
}

// Value Object per Prezzo
class Price {
    private readonly int cents
    private readonly string currency
    
    private constructor(int cents, string currency) {
        if (cents < 0) throw new InvalidPriceException()
        if (!isValidCurrency(currency)) throw new InvalidCurrencyException()
        this.cents = cents
        this.currency = currency
    }
    
    public static create(int cents, string currency) {
        return new Price(cents, currency)
    }
    
    public add(Price other) {
        if (this.currency !== other.currency) {
            throw new CurrencyMismatchException()
        }
        return new Price(this.cents + other.cents, this.currency)
    }
    
    public equals(Price other) {
        return this.cents === other.cents && this.currency === other.currency
    }
}

// Utilizzo
email1 = Email.create("user@example.com")
email2 = Email.create("user@example.com")
email1.equals(email2) // true

price1 = Price.create(1000, "EUR") // 10.00 EUR
price2 = Price.create(500, "EUR")  // 5.00 EUR
total = price1.add(price2)         // 15.00 EUR
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Value Object E-commerce System](./esempio-completo/)** - Sistema e-commerce con Value Object per prezzi, indirizzi e prodotti

L'esempio include:
- Value Object per prezzi con valute diverse
- Value Object per indirizzi con validazione geografica
- Value Object per SKU prodotti con formati specifici
- Operazioni matematiche tra Value Object
- Test completi per validazione e confronti

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Pattern correlati

- **Builder Pattern**: Spesso usato per creare Value Object complessi
- **Factory Method**: Per creare Value Object con logica di creazione complessa
- **Strategy Pattern**: Per implementare diverse strategie di validazione
- **Template Method**: Per definire il template di creazione di Value Object

## Esempi di uso reale

- **Sistemi finanziari**: Valute, prezzi, percentuali
- **E-commerce**: SKU, codici prodotto, dimensioni
- **Sistemi geografici**: Coordinate, indirizzi, distanze
- **Sistemi di autenticazione**: Token, hash, password
- **Sistemi di misurazione**: Pesi, lunghezze, temperature

## Anti-pattern

**Cosa NON fare:**
- Creare Value Object mutabili (perdono il loro scopo)
- Usare Value Object per entità che cambiano stato
- Implementare logica di business complessa nei Value Object
- Creare Value Object per valori che non hanno regole specifiche
- Dimenticare di implementare equals/hashCode correttamente

## Troubleshooting

### Problemi comuni

- **Value Object mutabili**: Assicurati che le proprietà siano readonly
- **Confronti errati**: Implementa sempre equals e hashCode
- **Validazione mancante**: Valida sempre nel costruttore
- **Performance**: Considera il caching per Value Object frequentemente usati
- **Serializzazione**: Implementa correttamente per JSON/database

### Debug e monitoring

- **Logging**: Traccia la creazione di Value Object per debugging
- **Metriche**: Monitora la frequenza di creazione per ottimizzazioni
- **Validazione**: Logga errori di validazione per identificare problemi
- **Testing**: Testa sempre i casi edge per validazione

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Oggetti invece di primitivi (overhead minimo)
- **CPU**: Validazione e creazione di oggetti (generalmente trascurabile)
- **I/O**: Serializzazione/deserializzazione per database

### Scalabilità

- **Carico basso**: Impatto trascurabile, Value Object sono leggeri
- **Carico medio**: Nessun problema, i Value Object sono progettati per essere efficienti
- **Carico alto**: Considera il caching per Value Object frequentemente usati

### Colli di bottiglia

- **Creazione frequente**: Implementa factory o caching se necessario
- **Validazione complessa**: Ottimizza le regole di validazione
- **Serializzazione**: Usa formati efficienti per database/API

## Risorse utili

### Documentazione ufficiale
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns) - Spiegazioni visuali

### Laravel specifico
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [Laravel Value Objects](https://laravel.com/docs/eloquent-mutators) - Mutators per Value Object

### Esempi e tutorial
- [Pattern Repository](https://github.com/design-patterns) - Esempi di codice
- [Domain-Driven Design](https://martinfowler.com/bliki/ValueObject.html) - Value Object in DDD
- [PHP Value Objects](https://github.com/cweagans/composer-patches) - Librerie PHP

### Strumenti di supporto
- [Checklist di Implementazione](../12-pattern-metodologie-concettuali/checklist-implementazione-pattern.md) - Guida step-by-step
