# Law of Demeter

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

La Law of Demeter (LoD), anche chiamata "Principle of Least Knowledge", stabilisce che un oggetto dovrebbe conoscere solo i suoi amici diretti e non dovrebbe "parlare con estranei". In pratica, un metodo dovrebbe invocare solo metodi di:

- L'oggetto stesso
- Oggetti passati come parametri
- Oggetti creati dal metodo
- Oggetti istanze dirette della classe

## Perché ti serve

Violare la Law of Demeter causa:
- Accoppiamento forte tra classi
- Difficoltà nel modificare il codice
- Codice fragile e propenso agli errori
- Difficoltà nel testare
- Violazione dell'incapsulamento

Rispettare la Law of Demeter offre:
- Basso accoppiamento
- Maggiore flessibilità
- Codice più robusto
- Facile testing
- Migliore incapsulamento

## Come funziona

Il principio funziona limitando le conoscenze di un oggetto:

**Permesso**: `object.method()`
**Permesso**: `object.property.method()`
**Vietato**: `object.property.property.method()`

Invece di accedere a oggetti distanti, dovresti:
- Aggiungere metodi delegati
- Ristrutturare la gerarchia
- Usare il pattern Facade
- Implementare il pattern Adapter

## Quando usarlo

Usa la Law of Demeter quando:
- Il codice ha troppe "catene" di chiamate
- Vuoi ridurre l'accoppiamento
- Devi migliorare la testabilità
- Vuoi rendere il codice più robusto
- Stai facendo refactoring

**NON usarlo quando:**
- Le catene sono necessarie per la performance
- Il codice è temporaneo
- La complessità aggiuntiva non è giustificata
- Stai lavorando con DTO semplici

## Pro e contro

**I vantaggi:**
- Riduce l'accoppiamento tra classi
- Migliora la testabilità
- Rende il codice più robusto
- Facilita le modifiche future
- Migliora l'incapsulamento

**Gli svantaggi:**
- Può richiedere più metodi delegati
- Può sembrare "verboso"
- Richiede refactoring del codice esistente
- Può aggiungere complessità



## Principi/Metodologie correlate

- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): Si integra con Dependency Inversion Principle
- **Clean Code** - [05-clean-code](./05-clean-code/clean-code.md): Codice pulito e ben organizzato
- **Separation of Concerns** - [06-separation-of-concerns](./06-separation-of-concerns/separation-of-concerns.md): Separazione delle responsabilità
- **Facade Pattern**: Per semplificare l'accesso a sottosistemi complessi
- **Adapter Pattern**: Per adattare interfacce esistenti
- **DTO Pattern**: Per trasferire dati senza violare l'incapsulamento
- **Encapsulation**: Principio base dell'incapsulamento

## Risorse utili

### Documentazione ufficiale
- [Law of Demeter](https://en.wikipedia.org/wiki/Law_of_Demeter) - Principio originale
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Robert Martin
- [Refactoring](https://www.amazon.com/Refactoring-Improving-Design-Existing-Code/dp/0134757599) - Martin Fowler

### Laravel specifico
- [Laravel Eloquent](https://laravel.com/docs/eloquent) - ORM con relazioni
- [Laravel Resources](https://laravel.com/docs/eloquent-resources) - Serializzazione
- [Laravel Facades](https://laravel.com/docs/facades) - Pattern Facade

### Esempi e tutorial
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP
- [Refactoring.Guru](https://refactoring.guru/) - Design patterns e principi

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
