# Code Smells

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

Code Smells sono indicatori di problemi nel codice che suggeriscono la necessità di refactoring. Sono pattern di codice che, pur funzionando correttamente, indicano problemi di design, manutenibilità o leggibilità che possono portare a bug futuri o difficoltà di manutenzione.

## Perché ti serve

Code Smells ti aiuta a:
- **Identificare** problemi nel codice
- **Priorizzare** il refactoring
- **Migliorare** la qualità del codice
- **Prevenire** bug futuri
- **Facilitare** la manutenzione
- **Educare** il team sui problemi comuni

## Come funziona

### Categorie di Code Smells

**Bloaters**
- **Long Method**: Metodi troppo lunghi
- **Large Class**: Classi troppo grandi
- **Long Parameter List**: Liste di parametri troppo lunghe
- **Data Clumps**: Gruppi di dati correlati
- **Primitive Obsession**: Uso eccessivo di tipi primitivi

**Object-Orientation Abusers**
- **Switch Statements**: Switch complessi
- **Temporary Field**: Campi temporanei
- **Refused Bequest**: Ereditarietà inappropriata
- **Alternative Classes**: Classi alternative simili
- **Inappropriate Intimacy**: Classi troppo intime

**Change Preventers**
- **Divergent Change**: Una classe cambia per motivi diversi
- **Shotgun Surgery**: Un cambiamento richiede modifiche in molte classi
- **Parallel Inheritance**: Ereditarietà parallela

**Dispensables**
- **Comments**: Commenti eccessivi
- **Duplicate Code**: Codice duplicato
- **Dead Code**: Codice morto
- **Lazy Class**: Classi inutili
- **Speculative Generality**: Generalizzazione speculativa

**Couplers**
- **Feature Envy**: Una classe usa troppo un'altra
- **Inappropriate Intimacy**: Classi troppo accoppiate
- **Message Chains**: Catene di chiamate
- **Middle Man**: Intermediari inutili

### Rilevamento dei Code Smells

**Static Analysis**
- Strumenti automatici
- Metriche di complessità
- Pattern recognition
- Esempio: SonarQube, PHPStan

**Code Review**
- Revisione manuale
- Esperienza del team
- Checklist di qualità
- Feedback tra pari

**Refactoring Catalogs**
- Cataloghi di refactoring
- Pattern di soluzione
- Best practices
- Esempio: Martin Fowler's Refactoring

### Strategie di Risoluzione

**Refactoring Techniques**
- **Extract Method**: Estrarre metodi
- **Extract Class**: Estrarre classi
- **Move Method**: Spostare metodi
- **Replace Conditional with Polymorphism**: Sostituire condizionali
- **Introduce Parameter Object**: Introdurre oggetti parametro

**Design Patterns**
- **Strategy Pattern**: Per switch statements
- **Factory Pattern**: Per creazione oggetti
- **Observer Pattern**: Per notifiche
- **Command Pattern**: Per operazioni
- **Template Method**: Per algoritmi simili

**Architectural Changes**
- **Separation of Concerns**: Separare responsabilità
- **Dependency Injection**: Iniettare dipendenze
- **Interface Segregation**: Separare interfacce
- **Single Responsibility**: Una responsabilità per classe

## Quando usarlo

Usa Code Smells quando:
- **Hai un progetto** di lunga durata
- **Vuoi migliorare** la qualità del codice
- **Hai bisogno** di identificare problemi
- **Vuoi educare** il team
- **Hai requisiti** di manutenibilità
- **Vuoi** prevenire bug futuri

**NON usarlo quando:**
- **Il progetto è** molto breve
- **Hai vincoli** di tempo rigidi
- **Il team non è** esperto
- **Non hai** strumenti appropriati
- **Il progetto è** un prototipo
- **Non hai** supporto per il refactoring

## Pro e contro

**I vantaggi:**
- **Identificazione** precoce dei problemi
- **Miglioramento** della qualità
- **Prevenzione** di bug futuri
- **Educazione** del team
- **Facilità** di manutenzione
- **Riduzione** del technical debt

**Gli svantaggi:**
- **Soggettività** nella valutazione
- **Overhead** per il rilevamento
- **Richiede** esperienza del team
- **Può essere** difficile da quantificare
- **Richiede** strumenti appropriati
- **Può essere** overwhelming per principianti

## Principi/Metodologie correlate

- **Code Quality** - [29-code-quality](./29-code-quality/code-quality.md): Qualità del codice
- **Technical Debt** - [30-technical-debt](./30-technical-debt/technical-debt.md): Gestione del debito tecnico
- **Refactoring** - [12-refactoring](./12-refactoring/refactoring.md): Miglioramento continuo
- **Clean Code** - [05-clean-code](./05-clean-code/clean-code.md): Principi per codice pulito
- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): Principi per il design
- **Code Review** - [13-code-review](./13-code-review/code-review.md): Revisione del codice

## Risorse utili

### Documentazione ufficiale
- [Refactoring](https://martinfowler.com/books/refactoring.html) - Libro di Martin Fowler
- [Code Smells](https://refactoring.guru/smells) - Catalogo di code smells
- [SonarQube](https://www.sonarqube.org/) - Piattaforma di qualità

### Laravel specifico
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Laravel Testing](https://laravel.com/docs/testing) - Testing in Laravel
- [Laracasts](https://laracasts.com/) - Video tutorial Laravel

### Esempi e tutorial
- [Code Smells Detection](https://github.com/phpstan/phpstan) - Rilevamento automatico
- [Laravel Quality](https://github.com/laravel/framework) - Qualità in Laravel
- [Refactoring Examples](https://github.com/ardalis/cleanarchitecture) - Esempi di refactoring
