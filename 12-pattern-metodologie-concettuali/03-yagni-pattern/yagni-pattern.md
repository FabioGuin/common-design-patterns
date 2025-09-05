# YAGNI Pattern

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

YAGNI (You Aren't Gonna Need It) ti dice di non aggiungere funzionalità o scrivere codice che non è attualmente necessario. Implementa solo ciò che serve ora, non quello che potresti aver bisogno in futuro.

È il principio che ti salva dall'over-engineering e ti aiuta a concentrarti su ciò che è realmente importante.

## Perché ti serve

Il codice "per il futuro" è:
- Spreco di tempo e risorse
- Complessità inutile
- Difficile da mantenere
- Spesso sbagliato quando arriva il momento
- Distrae dal lavoro reale

Con YAGNI, ti concentri su:
- Funzionalità che servono davvero
- Codice semplice e diretto
- Sviluppo più veloce
- Meno bug e problemi
- Focus sui requisiti reali

## Come funziona

Il principio è semplice: implementa solo ciò che è richiesto ora. In Laravel questo significa:

- Non creare migration per funzionalità future
- Non aggiungere colonne "per sicurezza"
- Non creare service per logica non necessaria
- Non implementare pattern complessi se non servono
- Non aggiungere configurazioni per funzionalità inesistenti

## Quando usarlo

Usa YAGNI quando:
- Stai iniziando un nuovo progetto
- Il codice diventa troppo complesso
- Hai poco tempo per sviluppare
- I requisiti cambiano frequentemente
- Vuoi ridurre i tempi di sviluppo

**NON usarlo quando:**
- Stai lavorando su sistemi critici
- I requisiti futuri sono certi e definiti
- La complessità è necessaria per la scalabilità
- Stai facendo refactoring di codice esistente

## Pro e contro

**I vantaggi:**
- Sviluppo più veloce
- Codice più semplice
- Meno complessità inutile
- Focus sui requisiti reali
- Meno bug da funzionalità non testate

**Gli svantaggi:**
- Potrebbe richiedere refactoring futuro
- Potrebbe non essere ottimale per la scalabilità
- Richiede disciplina per non aggiungere "cose utili"
- Potrebbe sembrare "incompleto" ad alcuni


## Principi/Metodologie correlate

- **KISS Pattern** - [02-kiss-pattern](./02-kiss-pattern/kiss-pattern.md): Mantieni semplice ciò che implementi
- **DRY Pattern** - [01-dry-pattern](./01-dry-pattern/dry-pattern.md): Non duplicare, ma non aggiungere inutile
- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): YAGNI si integra con tutti i principi SOLID
- **Clean Code** - [05-clean-code](./05-clean-code/clean-code.md): Codice pulito e necessario

## Risorse utili

### Documentazione ufficiale
- [Laravel Migrations](https://laravel.com/docs/migrations) - Crea solo le tabelle necessarie
- [Laravel Eloquent](https://laravel.com/docs/eloquent) - Definisci solo i campi richiesti
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Il libro di Robert Martin

### Laravel specifico
- [Laravel Controllers](https://laravel.com/docs/controllers) - Implementa solo gli endpoint necessari
- [Laravel Services](https://laravel.com/docs/container) - Crea solo la logica business richiesta
- [Laravel Helpers](https://laravel.com/docs/helpers) - Usa solo le funzioni necessarie

### Esempi e tutorial
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP
- [Refactoring.Guru](https://refactoring.guru/) - Design patterns e principi

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
