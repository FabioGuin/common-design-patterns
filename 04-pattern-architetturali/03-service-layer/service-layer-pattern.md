# Service Layer Pattern

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

Il pattern Service Layer incapsula la logica di business in servizi dedicati, separandola dai controller e dai modelli. I service contengono le regole di business, la validazione complessa e l'orchestrazione tra diversi componenti.

Pensa ai Service come a dei manager esperti: quando hai bisogno di fare qualcosa di complesso, chiami il manager giusto che sa come coordinare tutto il lavoro necessario, senza che tu debba preoccuparti dei dettagli.

## Perché ti serve

Senza Service Layer, la tua logica di business finisce sparsa ovunque: nei controller, nei modelli, nelle view. Risultato? Codice duplicato, difficile da testare e da mantenere.

Con Service Layer ottieni:
- **Separazione delle responsabilità**: Logica di business centralizzata
- **Riusabilità**: Stessi service usati in più punti
- **Testabilità**: Puoi testare la logica di business isolatamente
- **Manutenibilità**: Modifiche centralizzate alle regole di business
- **Leggibilità**: Controller più puliti e focalizzati
- **Consistenza**: Stesse regole applicate ovunque

## Come funziona

1. **Definisci i Service** per ogni area di business (es: `UserService`, `OrderService`)
2. **Sposta la logica** dai controller ai service
3. **I Service orchestrano** le operazioni tra repository, modelli e altri service
4. **I Controller chiamano** i service per le operazioni complesse
5. **I Service restituiscono** i risultati ai controller

Il flusso è: **Controller → Service → Repository/Model → Database**

## Schema visivo

```
Controller
    ↓
Service Layer
    ↓
Repository/Model
    ↓
Database
```

**Flusso dettagliato:**
```
1. UserController@create
   ↓
2. UserService@createUser
   ↓
3. UserService@validateData
   ↓
4. UserService@processData
   ↓
5. UserRepository@create
   ↓
6. Database Insert
   ↓
7. UserService@sendWelcomeEmail
   ↓
8. UserService@returnUser
   ↓
9. Controller@returnResponse
```

## Quando usarlo

Usa il pattern Service Layer quando:
- Hai logica di business complessa
- Vuoi separare la logica dai controller
- Hai operazioni che coinvolgono più modelli
- Vuoi centralizzare le regole di business
- Hai bisogno di testare la logica isolatamente
- Vuoi riutilizzare la logica in più punti

**NON usarlo quando:**
- Hai logica molto semplice
- Le operazioni sono solo CRUD basiche
- Vuoi mantenere la semplicità di Laravel
- Hai vincoli di performance estremi
- L'applicazione è molto piccola

## Pro e contro

**I vantaggi:**
- **Separazione chiara**: Logica di business isolata
- **Riusabilità**: Stessi service in più controller
- **Testabilità**: Facile testare la logica di business
- **Manutenibilità**: Modifiche centralizzate
- **Leggibilità**: Controller più puliti
- **Consistenza**: Stesse regole ovunque

**Gli svantaggi:**
- **Complessità aggiuntiva**: Più file e layer
- **Overhead**: Strato aggiuntivo di astrazione
- **Curva di apprendimento**: Richiede comprensione dell'architettura
- **Over-engineering**: Può essere eccessivo per logica semplice
- **Performance**: Strato aggiuntivo può rallentare (minimamente)

## Esempi di codice

### Pseudocodice

```
// Service Layer
class UserService {
    constructor(userRepository, emailService, notificationService) {
        this.userRepository = userRepository
        this.emailService = emailService
        this.notificationService = notificationService
    }
    
    function createUser(userData) {
        // Validazione business
        this.validateUserData(userData)
        
        // Processamento dati
        processedData = this.processUserData(userData)
        
        // Creazione utente
        user = this.userRepository.create(processedData)
        
        // Azioni post-creazione
        this.sendWelcomeEmail(user)
        this.notifyAdmins(user)
        
        return user
    }
    
    function validateUserData(data) {
        if (data.email is empty) {
            throw new ValidationError("Email required")
        }
        if (this.userRepository.emailExists(data.email)) {
            throw new ValidationError("Email already exists")
        }
    }
    
    function processUserData(data) {
        data.password = hashPassword(data.password)
        data.activationToken = generateToken()
        data.status = "pending"
        return data
    }
    
    function sendWelcomeEmail(user) {
        this.emailService.send({
            to: user.email,
            template: "welcome",
            data: { user: user }
        })
    }
}

// Controller
class UserController {
    constructor(userService) {
        this.userService = userService
    }
    
    function create(request) {
        try {
            user = this.userService.createUser(request.data)
            return response.success(user)
        } catch (ValidationError error) {
            return response.error(error.message)
        }
    }
}

// Dependency Injection
container.bind(UserService, UserService)
container.bind(UserController, UserController)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Service Layer Blog System](./esempio-completo/)** - Sistema blog con Service Layer

L'esempio include:
- Service per articoli e utenti
- Logica di business centralizzata
- Validazione e processamento dati
- Orchestrazione tra componenti
- Test unitari per i service

**Nota per l'implementazione**: L'esempio completo segue il template semplificato con focus sulla dimostrazione del pattern Service Layer, non su un'applicazione completa.

## Correlati

### Pattern

- **[MVC Pattern](./01-mvc/mvc-pattern.md)** - Architettura base per applicazioni web
- **[Repository Pattern](./02-repository/repository-pattern.md)** - Astrae l'accesso ai dati
- **[DTO Pattern](./04-dto/dto-pattern.md)** - Trasferisce dati tra layer
- **[Unit of Work Pattern](./05-unit-of-work/unit-of-work-pattern.md)** - Gestisce le transazioni

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **E-commerce**: Service per ordini, pagamenti, inventario
- **Sistemi di gestione**: Service per utenti, permessi, workflow
- **API complesse**: Service per orchestrazione di operazioni
- **Sistemi finanziari**: Service per calcoli e validazioni
- **Applicazioni enterprise**: Service per regole di business complesse

## Anti-pattern

**Cosa NON fare:**
- **Fat Service**: Service che fanno troppo lavoro
- **Anemic Service**: Service che sono solo wrapper
- **Service per tutto**: Non serve per operazioni semplici
- **Tight Coupling**: Service troppo legati tra loro
- **God Service**: Un singolo service che fa tutto

## Troubleshooting

### Problemi comuni

- **Service troppo grandi**: Suddividi in service più piccoli
- **Logica duplicata**: Crea service base comuni
- **Dependency injection**: Verifica che i service siano registrati
- **Testing**: Usa mock per testare i service isolatamente
- **Performance**: Ottimizza le operazioni nei service

### Debug e monitoring

- **Log delle operazioni**: Traccia le operazioni dei service
- **Performance**: Monitora i tempi di esecuzione
- **Errori**: Implementa gestione errori centralizzata
- **Testing**: Testa i service separatamente

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Strato aggiuntivo di astrazione, ma minimo
- **CPU**: Overhead trascurabile per la maggior parte delle applicazioni
- **I/O**: Operazioni database ottimizzate nei service

### Scalabilità

- **Carico basso**: Service non aggiungono overhead significativo
- **Carico medio**: Separazione aiuta nell'ottimizzazione
- **Carico alto**: Caching e ottimizzazioni specifiche per service

### Colli di bottiglia

- **Operazioni complesse**: Ottimizza la logica nei service
- **Database queries**: Usa repository per ottimizzare le query
- **Memory usage**: Gestisci le operazioni che usano molta memoria

## Risorse utili

### Documentazione ufficiale

- [Laravel Service Container](https://laravel.com/docs/container) - Dependency injection
- [Laravel Service Providers](https://laravel.com/docs/providers) - Registrazione servizi
- [Service Layer Pattern Wikipedia](https://en.wikipedia.org/wiki/Service_layer_pattern) - Teoria del pattern

### Laravel specifico

- [Laravel Services](https://laravel.com/docs/container#binding-interfaces-to-implementations) - Implementazione service
- [Laravel Testing](https://laravel.com/docs/testing) - Test con mock
- [Laravel Service Container](https://laravel.com/docs/container) - Gestione dipendenze

### Esempi e tutorial

- [Laravel Service Layer](https://laravel.com/docs/container#binding-interfaces-to-implementations) - Implementazione Laravel
- [Service Layer Best Practices](https://laravel.com/docs/container#binding-interfaces-to-implementations) - Best practices

### Strumenti di supporto

- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug service
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring applicazione
