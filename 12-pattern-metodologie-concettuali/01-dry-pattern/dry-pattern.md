# DRY Pattern

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Principi/Metodologie correlate](#principi-metodologie-correlate)
- [Risorse utili](#risorse-utili)

## Cosa fa

DRY (Don't Repeat Yourself) ti dice di non ripetere mai la stessa informazione o logica in più posti del codice. Ogni pezzo di conoscenza deve avere una rappresentazione unica e autorevole all'interno del sistema.

È uno dei principi più importanti perché quando duplichi il codice, duplichi anche i problemi: bug, manutenzione, confusione.

## Perché ti serve

Immagina di avere la stessa logica di validazione in 5 controller diversi. Se devi cambiare una regola, devi ricordarti di modificarla in tutti e 5 i posti. Dimentichi uno? Bug in produzione.

Con DRY, cambi una sola volta e tutto si aggiorna automaticamente.

## Come funziona

Il principio è semplice: identifica la logica duplicata e estraila in un posto unico. In Laravel puoi usare:

- **Service Classes** per logica business
- **Form Requests** per validazione
- **Trait** per funzionalità condivise
- **Helper** per utility comuni
- **Model Methods** per logica specifica del dominio

## Quando usarlo

Usa DRY quando:
- Vedi la stessa logica in più posti
- Devi modificare la stessa cosa in più file
- Il codice diventa difficile da mantenere
- Vuoi ridurre i bug da inconsistenze

**NON usarlo quando:**
- La duplicazione è intenzionale e temporanea
- L'astrazione renderebbe il codice più complesso
- Stai facendo prototipi rapidi
- La logica è davvero diversa anche se sembra uguale

## Pro e contro

**I vantaggi:**
- Meno bug da inconsistenze
- Manutenzione più facile
- Codice più pulito e organizzato
- Meno tempo per modifiche future

**Gli svantaggi:**
- Può creare dipendenze tra moduli
- Rischio di over-engineering
- Difficoltà nel trovare il posto giusto per la logica
- Possibile complessità eccessiva


## Principi/Metodologie correlate

- **KISS Pattern** - [02-kiss-pattern](./02-kiss-pattern/kiss-pattern.md): Mantieni semplice l'astrazione DRY
- **YAGNI Pattern** - [03-yagni-pattern](./03-yagni-pattern/yagni-pattern.md): Non over-engineer con DRY
- **SOLID Principles** - [04-solid-principles](./04-solid-principles/solid-principles.md): DRY si integra con Single Responsibility
- **Clean Code** - [05-clean-code](./05-clean-code/clean-code.md): DRY è parte del clean code
- **Separation of Concerns** - [06-separation-of-concerns](./06-separation-of-concerns/separation-of-concerns.md): Separazione delle responsabilità

## Risorse utili

### Documentazione ufficiale
- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation) - Validazione centralizzata
- [Laravel Service Container](https://laravel.com/docs/container) - Dependency injection
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882) - Il libro di Robert Martin

### Laravel specifico
- [Laravel Service Classes](https://laravel.com/docs/container#binding) - Per logica business
- [Laravel Traits](https://www.php.net/manual/en/language.oop5.traits.php) - Per funzionalità condivise
- [Laravel Helpers](https://laravel.com/docs/helpers) - Funzioni utility

### Esempi e tutorial
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices per Laravel
- [Refactoring.Guru](https://refactoring.guru/smells/duplicate-code) - Duplicate Code smell
- [PHP The Right Way](https://phptherightway.com/) - Guida completa per PHP

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../checklist-implementazione-pattern.md) - Guida step-by-step
- [PHPStan](https://phpstan.org/) - Static analysis per PHP
- [SonarQube](https://www.sonarqube.org/) - Code quality analysis
