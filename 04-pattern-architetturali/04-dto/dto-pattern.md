# DTO Pattern

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

Il pattern DTO (Data Transfer Object) incapsula i dati per il trasferimento tra layer dell'applicazione. I DTO sono oggetti semplici che contengono solo dati, senza logica di business, e facilitano il trasferimento di informazioni tra diversi componenti.

Pensa ai DTO come a delle buste: quando devi inviare informazioni da un ufficio all'altro, metti tutto in una busta ben organizzata, la chiudi e la spedisci. Il destinatario apre la busta e trova tutto quello che gli serve, senza dover cercare tra mille documenti sparsi.

## Perché ti serve

Senza DTO, i tuoi dati viaggiano in modo disorganizzato tra i layer: array associativi, oggetti complessi, strutture inconsistenti. Risultato? Codice fragile, difficile da mantenere e da testare.

Con DTO ottieni:
- **Struttura chiara**: Dati organizzati e tipizzati
- **Validazione centralizzata**: Controllo dei dati in un punto
- **Versioning**: Gestione delle versioni dei dati
- **Documentazione**: Struttura auto-documentata
- **Testabilità**: Facile creare dati di test
- **Sicurezza**: Controllo su cosa viene trasferito
- **Performance**: Trasferimento ottimizzato dei dati

## Come funziona

1. **Definisci i DTO** per ogni tipo di trasferimento dati
2. **Incapsula i dati** in oggetti strutturati
3. **Valida i dati** durante la creazione del DTO
4. **Trasferisci i DTO** tra i layer dell'applicazione
5. **Estrai i dati** quando necessario

Il flusso è: **Source → DTO → Validation → Transfer → Destination**

## Schema visivo

```
Controller
    ↓
DTO (Data Transfer Object)
    ↓
Service Layer
    ↓
Repository
    ↓
Database
```

**Flusso dettagliato:**
```
1. Request Data
   ↓
2. Create DTO
   ↓
3. Validate DTO
   ↓
4. Transfer to Service
   ↓
5. Service processes DTO
   ↓
6. Create Response DTO
   ↓
7. Return to Controller
   ↓
8. Transform to Response
```

## Quando usarlo

Usa il pattern DTO quando:
- Hai trasferimento di dati tra layer
- Vuoi strutturare i dati in modo consistente
- Hai bisogno di validazione centralizzata
- Vuoi documentare la struttura dei dati
- Hai API con contratti definiti
- Vuoi versionare i dati
- Hai trasferimento di dati complessi

**NON usarlo quando:**
- Hai trasferimento di dati semplici
- Vuoi mantenere la semplicità
- Hai vincoli di performance estremi
- L'applicazione è molto piccola
- I dati sono già ben strutturati

## Pro e contro

**I vantaggi:**
- **Struttura chiara**: Dati organizzati e tipizzati
- **Validazione centralizzata**: Controllo in un punto
- **Documentazione**: Struttura auto-documentata
- **Testabilità**: Facile creare dati di test
- **Sicurezza**: Controllo su cosa viene trasferito
- **Versioning**: Gestione delle versioni
- **Performance**: Trasferimento ottimizzato

**Gli svantaggi:**
- **Complessità aggiuntiva**: Più classi da gestire
- **Overhead**: Strato aggiuntivo di astrazione
- **Duplicazione**: Possibile duplicazione di strutture
- **Curva di apprendimento**: Richiede comprensione del pattern
- **Over-engineering**: Può essere eccessivo per dati semplici

## Esempi di codice

### Pseudocodice

```
// DTO per creazione utente
class CreateUserDTO {
    properties: name, email, password, role, bio
    
    constructor(data) {
        this.name = data.name
        this.email = data.email
        this.password = data.password
        this.role = data.role || 'user'
        this.bio = data.bio || null
        this.validate()
    }
    
    function validate() {
        if (this.name is empty) {
            throw new ValidationError("Name required")
        }
        if (this.email is not valid) {
            throw new ValidationError("Invalid email")
        }
        if (this.password is too short) {
            throw new ValidationError("Password too short")
        }
    }
    
    function toArray() {
        return {
            name: this.name,
            email: this.email,
            password: this.password,
            role: this.role,
            bio: this.bio
        }
    }
}

// DTO per risposta utente
class UserResponseDTO {
    properties: id, name, email, role, bio, created_at
    
    constructor(user) {
        this.id = user.id
        this.name = user.name
        this.email = user.email
        this.role = user.role
        this.bio = user.bio
        this.created_at = user.created_at
    }
    
    function toArray() {
        return {
            id: this.id,
            name: this.name,
            email: this.email,
            role: this.role,
            bio: this.bio,
            created_at: this.created_at
        }
    }
}

// Service che usa DTO
class UserService {
    function createUser(dto: CreateUserDTO): UserResponseDTO {
        // Crea utente usando DTO
        user = this.userRepository.create(dto.toArray())
        
        // Restituisce DTO di risposta
        return new UserResponseDTO(user)
    }
    
    function getUser(id): UserResponseDTO {
        user = this.userRepository.findById(id)
        return new UserResponseDTO(user)
    }
}

// Controller che usa DTO
class UserController {
    function create(request) {
        // Crea DTO dalla richiesta
        dto = new CreateUserDTO(request.data)
        
        // Usa service con DTO
        responseDto = this.userService.createUser(dto)
        
        // Restituisce risposta
        return response.success(responseDto.toArray())
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[DTO Blog System](./esempio-completo/)** - Sistema blog con DTO Pattern

L'esempio include:
- DTO per articoli e utenti
- Validazione centralizzata
- Trasformazione dati
- API con contratti definiti
- Test per i DTO

**Nota per l'implementazione**: L'esempio completo segue il template semplificato con focus sulla dimostrazione del pattern DTO, non su un'applicazione completa.

## Correlati

### Pattern

- **[MVC Pattern](./01-mvc/mvc-pattern.md)** - Architettura base per applicazioni web
- **[Repository Pattern](./02-repository/repository-pattern.md)** - Astrae l'accesso ai dati
- **[Service Layer Pattern](./03-service-layer/service-layer-pattern.md)** - Centralizza la logica di business
- **[Unit of Work Pattern](./05-unit-of-work/unit-of-work-pattern.md)** - Gestisce le transazioni

### Principi e Metodologie

- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **API REST**: DTO per request/response
- **Microservizi**: Trasferimento dati tra servizi
- **Sistemi di integrazione**: Scambio dati con sistemi esterni
- **Applicazioni enterprise**: Trasferimento dati complessi
- **Sistemi di reporting**: Strutturazione dati per report

## Anti-pattern

**Cosa NON fare:**
- **Fat DTO**: DTO che contengono troppi dati
- **Anemic DTO**: DTO senza validazione
- **DTO per tutto**: Non serve per dati semplici
- **Tight Coupling**: DTO troppo legati ai modelli
- **God DTO**: Un singolo DTO per tutto

## Troubleshooting

### Problemi comuni

- **DTO troppo grandi**: Suddividi in DTO più piccoli
- **Validazione duplicata**: Centralizza la validazione
- **Performance**: Ottimizza la creazione dei DTO
- **Versioning**: Gestisci le versioni dei DTO
- **Mapping**: Usa mapper per conversioni complesse

### Debug e monitoring

- **Log dei DTO**: Traccia la creazione e trasferimento
- **Validation errors**: Monitora gli errori di validazione
- **Performance**: Monitora i tempi di creazione
- **Memory usage**: Gestisci l'uso della memoria

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Oggetti aggiuntivi per i DTO
- **CPU**: Overhead per creazione e validazione
- **I/O**: Trasferimento ottimizzato dei dati

### Scalabilità

- **Carico basso**: DTO non aggiungono overhead significativo
- **Carico medio**: Strutturazione aiuta nell'ottimizzazione
- **Carico alto**: Caching e ottimizzazioni specifiche per DTO

### Colli di bottiglia

- **Creazione DTO**: Ottimizza la creazione
- **Validazione**: Usa validazione lazy quando possibile
- **Serializzazione**: Ottimizza la serializzazione/deserializzazione

## Risorse utili

### Documentazione ufficiale

- [Laravel Data Transfer Objects](https://laravel.com/docs/validation) - Validazione e DTO
- [DTO Pattern Wikipedia](https://en.wikipedia.org/wiki/Data_transfer_object) - Teoria del pattern
- [Martin Fowler on DTO](https://martinfowler.com/eaaCatalog/dataTransferObject.html) - Definizione del pattern

### Laravel specifico

- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation) - Validazione con DTO
- [Laravel Resources](https://laravel.com/docs/eloquent-resources) - Trasformazione dati
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources) - DTO per API

### Esempi e tutorial

- [Laravel DTO Implementation](https://laravel.com/docs/validation#form-request-validation) - Implementazione Laravel
- [DTO Best Practices](https://laravel.com/docs/validation#form-request-validation) - Best practices

### Strumenti di supporto

- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug DTO
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring applicazione
