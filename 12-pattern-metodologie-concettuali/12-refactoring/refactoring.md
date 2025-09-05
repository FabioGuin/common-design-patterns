# Refactoring

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

Il Refactoring è il processo di miglioramento del codice esistente senza cambiarne il comportamento esterno. L'obiettivo è rendere il codice più pulito, leggibile, manutenibile e performante mantenendo la stessa funzionalità.

Il refactoring si basa su piccole modifiche incrementali che migliorano la struttura del codice senza introdurre bug o cambiare il comportamento del sistema.

## Perché ti serve

Senza refactoring, il codice diventa:
- **Difficile da leggere** e capire
- **Duplicato** e inconsistente
- **Accoppiato** e fragile
- **Lento** da modificare
- **Propenso agli errori** durante le modifiche

Con refactoring, il codice diventa:
- **Leggibile** e comprensibile
- **DRY** (Don't Repeat Yourself)
- **Modulare** e flessibile
- **Facile da modificare** e estendere
- **Più robusto** e meno propenso agli errori

## Come funziona

### Principi del Refactoring

**1. Piccoli Passi**
- Una modifica alla volta
- Test dopo ogni modifica
- Rollback se qualcosa va storto

**2. Test Prima**
- Assicurati che i test passino prima del refactoring
- I test devono coprire il comportamento esistente
- I test devono passare dopo il refactoring

**3. Comportamento Invariante**
- Il comportamento esterno non deve cambiare
- Solo la struttura interna del codice cambia
- L'API pubblica rimane la stessa

### Tecniche di Refactoring

**1. Extract Method**
- Estrai logica complessa in metodi separati
- Riduci la complessità di un metodo
- Migliora la leggibilità

**2. Extract Class**
- Crea nuove classi per responsabilità specifiche
- Applica il Single Responsibility Principle
- Riduci l'accoppiamento

**3. Move Method**
- Sposta metodi in classi più appropriate
- Migliora la coesione
- Riduce l'accoppiamento

**4. Rename**
- Usa nomi più descrittivi
- Migliora la leggibilità
- Riduce la confusione

## Quando usarlo

Usa refactoring quando:
- **Il codice è difficile** da leggere e capire
- **Hai duplicazioni** nel codice
- **Il codice è accoppiato** e fragile
- **Devi aggiungere** nuove funzionalità
- **Vuoi migliorare** le performance
- **Stai applicando** principi SOLID

**NON usarlo quando:**
- **Il codice funziona** e non verrà mai modificato
- **Non hai test** per verificare il comportamento
- **Stai facendo** prototipi rapidi
- **Il team non è formato** sul refactoring
- **Stai lavorando** con codice legacy critico

## Pro e contro

**I vantaggi:**
- **Codice più pulito** e leggibile
- **Meno duplicazioni** e inconsistenze
- **Migliore architettura** e design
- **Facile manutenzione** e estensione
- **Meno bug** durante le modifiche
- **Performance migliorate**

**Gli svantaggi:**
- **Tempo iniziale** per il refactoring
- **Rischio di introdurre** bug
- **Richiede test** completi
- **Può sembrare** "non produttivo"
- **Richiede disciplina** costante





## Principi/Metodologie correlate

- **TDD** - [09-tdd](./09-tdd/tdd.md): Test guidano il refactoring sicuro
- **Clean Code** - [05-clean-code](./05-clean-code/clean-code.md): Obiettivo del refactoring
- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): Principi da applicare durante il refactoring
- **Code Smells**: Identificano quando fare refactoring
- **Technical Debt**: Refactoring riduce il debito tecnico
- **Pair Programming** - [14-pair-programming](./14-pair-programming/pair-programming.md): Collaborazione durante il refactoring

## Risorse utili

### Documentazione ufficiale
- [Refactoring](https://www.amazon.com/Refactoring-Improving-Design-Existing-Code/dp/0134757599) - Martin Fowler
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Robert Martin
- [Refactoring.Guru](https://refactoring.guru/) - Guida online

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
- [Laravel IDE Helper](https://github.com/barryvdh/laravel-ide-helper) - Supporto IDE

### Esempi e tutorial
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel
- [Refactoring Examples](https://refactoring.guru/refactoring/techniques) - Esempi pratici

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
- [Laravel Pint](https://laravel.com/docs/pint) - Code style fixer
