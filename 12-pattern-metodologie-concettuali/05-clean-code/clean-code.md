# Clean Code

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

Clean Code è un insieme di principi e pratiche per scrivere codice pulito, leggibile e manutenibile. Il codice pulito è facile da capire, modificare e testare, riducendo i costi di manutenzione e la probabilità di errori.

Il concetto è stato formalizzato da Robert C. Martin nel libro "Clean Code" e si basa su principi semplici ma potenti per migliorare la qualità del software.

## Perché ti serve

Il codice sporco causa:
- Difficoltà di comprensione
- Tempo sprecato per capire cosa fa
- Bug frequenti durante le modifiche
- Resistenza del team a toccare il codice
- Costi di manutenzione elevati

Il codice pulito offre:
- Comprensione immediata
- Modifiche sicure e veloci
- Meno bug e errori
- Team più produttivo
- Manutenzione economica

## Come funziona

Clean Code si basa su principi fondamentali:

**Nomi Significativi**: Usa nomi che rivelano l'intenzione
**Funzioni Piccole**: Una funzione fa una cosa sola
**Commenti**: Solo quando necessario, il codice deve parlare da solo
**Formattazione**: Codice ben formattato e consistente
**Gestione Errori**: Gestione pulita degli errori
**Test**: Codice testabile e ben testato

## Quando usarlo

Usa Clean Code quando:
- Stai scrivendo nuovo codice
- Il codice esistente è difficile da leggere
- Devi fare refactoring
- Il team ha difficoltà a collaborare
- Vuoi ridurre i bug

**NON usarlo quando:**
- Stai facendo prototipi rapidi
- Il codice è temporaneo
- Non hai tempo per applicare tutti i principi
- Il team non è pronto per il cambiamento

## Pro e contro

**I vantaggi:**
- Codice più leggibile e comprensibile
- Manutenzione più facile e veloce
- Meno bug e errori
- Team più produttivo
- Riduzione dei costi di sviluppo

**Gli svantaggi:**
- Richiede tempo iniziale per applicare i principi
- Curva di apprendimento per il team
- Può sembrare "troppo" per progetti piccoli
- Richiede disciplina costante


## Principi/Metodologie correlate

- **DRY Pattern** - [01-dry-pattern](./01-dry-pattern/dry-pattern.md): Evita duplicazione nel codice pulito
- **KISS Pattern** - [02-kiss-pattern](./02-kiss-pattern/kiss-pattern.md): Mantieni il codice semplice
- **YAGNI Pattern** - [03-yagni-pattern](./03-yagni-pattern/yagni-pattern.md): Non over-engineer il codice
- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): Base per codice pulito e ben strutturato
- **TDD** - [09-tdd](./09-tdd/tdd.md): Test guidano la scrittura di codice pulito
- **Refactoring** - [12-refactoring](./12-refactoring/refactoring.md): Miglioramento continuo del codice

## Risorse utili

### Documentazione ufficiale
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Il libro di Robert Martin
- [Clean Architecture](https://www.amazon.com/Clean-Architecture-Craftsmans-Software-Structure/dp/0134494272) - Architettura pulita
- [Refactoring](https://www.amazon.com/Refactoring-Improving-Design-Existing-Code/dp/0134757599) - Martin Fowler

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
- [Laravel Helpers](https://laravel.com/docs/helpers) - Funzioni utility

### Esempi e tutorial
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP
- [Refactoring.Guru](https://refactoring.guru/) - Design patterns e principi
- [Clean Code PHP](https://github.com/jupeter/clean-code-php) - Esempi in PHP

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
- [PHP CS Fixer](https://cs.symfony.com/) - Code style fixer
