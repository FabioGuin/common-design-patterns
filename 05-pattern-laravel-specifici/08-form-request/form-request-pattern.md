# Form Request Pattern

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

Il Form Request Pattern centralizza la validazione e l'autorizzazione delle richieste HTTP in classi dedicate. Invece di mettere tutta la logica di validazione nei controller, crei classi specifiche che si occupano solo di validare e autorizzare i dati in arrivo.

È come avere un filtro intelligente che controlla tutto prima che i dati arrivino al tuo controller.

## Perché ti serve

Immagina di avere 10 controller con validazione email. Se devi cambiare la regola di validazione, devi modificare 10 posti diversi. Con Form Request, cambi una sola volta e tutti i controller usano la nuova regola.

**Problemi che risolve:**
- **Duplicazione**: Evita di ripetere le stesse regole di validazione
- **Organizzazione**: Separa la validazione dalla logica business
- **Riuso**: Puoi usare la stessa validazione in più controller
- **Testing**: Più facile testare la validazione separatamente
- **Manutenibilità**: Modifiche centralizzate

## Come funziona

### Flusso Base

1. **Richiesta HTTP**: L'utente invia una richiesta
2. **Form Request**: Laravel instanzia la classe Form Request
3. **Autorizzazione**: Controlla se l'utente può fare questa richiesta
4. **Validazione**: Verifica che i dati siano corretti
5. **Controller**: Se tutto ok, passa i dati validati al controller

### Componenti Laravel

**Form Request Class**
- Estende `FormRequest`
- Contiene regole di validazione
- Gestisce autorizzazione
- Personalizza messaggi di errore

**Validation Rules**
- Regole standard Laravel
- Regole personalizzate
- Regole condizionali
- Regole per array e oggetti

**Authorization**
- Metodo `authorize()`
- Controlli di permessi
- Logica di business per autorizzazione

**Custom Messages**
- Messaggi personalizzati per errori
- Localizzazione
- Messaggi specifici per regole

## Schema visivo

```
Richiesta HTTP → Form Request → Autorizzazione → Validazione → Controller
                      ↓              ↓            ↓
                   authorize()   rules()    messages()
                      ↓              ↓            ↓
                   true/false   valid/invalid  error messages
```

**Flusso dettagliato:**
1. **HTTP Request** → Richiesta arriva al controller
2. **Form Request** → Laravel crea istanza della classe
3. **Authorization** → Controlla se l'utente è autorizzato
4. **Validation** → Verifica i dati secondo le regole
5. **Success** → Dati validati passano al controller
6. **Failure** → Ritorna errori di validazione o autorizzazione

## Quando usarlo

Usa Form Request quando:
- **Validazione complessa**: Regole multiple e condizionali
- **Autorizzazione**: Controlli di permessi specifici
- **Riuso**: Stessa validazione in più controller
- **Organizzazione**: Vuoi separare validazione da logica business
- **Testing**: Vuoi testare validazione separatamente
- **Messaggi personalizzati**: Vuoi errori specifici

**NON usarlo quando:**
- **Validazione semplice**: Una o due regole base
- **Prototipi rapidi**: Sviluppo veloce senza struttura
- **Validazione unica**: Usata solo in un posto
- **Performance critica**: Overhead non accettabile

## Pro e contro

**I vantaggi:**
- **Organizzazione**: Codice più pulito e organizzato
- **Riuso**: Stessa validazione in più posti
- **Testing**: Più facile testare la validazione
- **Manutenibilità**: Modifiche centralizzate
- **Autorizzazione**: Controlli di permessi integrati
- **Messaggi personalizzati**: Errori più chiari

**Gli svantaggi:**
- **Complessità**: Più classi da gestire
- **Overhead**: Leggero overhead per classi semplici
- **Learning curve**: Richiede conoscenza di Laravel
- **Over-engineering**: Può essere eccessivo per casi semplici

## Esempi di codice

### Pseudocodice

```
// Form Request Class
class CreateUserRequest {
    authorize() {
        return user.can('create', User)
    }
    
    rules() {
        return {
            'name': 'required|string|max:255',
            'email': 'required|email|unique:users',
            'password': 'required|min:8|confirmed'
        }
    }
    
    messages() {
        return {
            'email.unique': 'Questa email è già registrata',
            'password.min': 'La password deve essere di almeno 8 caratteri'
        }
    }
}

// Controller
class UserController {
    store(CreateUserRequest request) {
        // I dati sono già validati e autorizzati
        userData = request.validated()
        user = User.create(userData)
        return response.success(user)
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Registrazione con Form Request](./esempio-completo/)** - Sistema completo di validazione e autorizzazione

L'esempio include:
- Form Request per registrazione utenti
- Form Request per aggiornamento profilo
- Form Request per creazione post
- Validazione condizionale
- Autorizzazione basata su ruoli
- Messaggi personalizzati
- Testing delle validazioni

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Service Container Pattern](./01-service-container/service-container-pattern.md)** - Dependency injection per Form Request
- **[Middleware Pattern](./03-middleware/middleware-pattern.md)** - Middleware per validazione globale
- **[Policy Pattern](./10-policy/policy-pattern.md)** - Autorizzazione avanzata
- **[Repository Pattern](../04-pattern-architetturali/02-repository/repository-pattern.md)** - Accesso dati per validazione
- **[Service Layer Pattern](../04-pattern-architetturali/03-service-layer/service-layer-pattern.md)** - Logica business dopo validazione

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione nelle regole di validazione
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi per design delle Form Request
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Codice pulito nelle Form Request
- **[Separation of Concerns](../00-fondamentali/06-separation-of-concerns/separation-of-concerns.md)** - Separazione validazione da logica business
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test per Form Request

## Esempi di uso reale

- **E-commerce**: Validazione ordini, prodotti, pagamenti
- **Social Media**: Validazione post, commenti, messaggi
- **Sistema di fatturazione**: Validazione clienti, fatture, pagamenti
- **CRM**: Validazione contatti, opportunità, attività
- **Sistema di gestione**: Validazione utenti, ruoli, permessi

## Anti-pattern

**Cosa NON fare:**
- **Validazione nei controller**: Metti la validazione nei controller invece che nelle Form Request
- **Form Request troppo grandi**: Una Form Request con centinaia di regole
- **Duplicazione**: Stesse regole in più Form Request
- **Logica business**: Metti logica business nelle Form Request
- **Validazione sincrona**: Usa Form Request per validazione asincrona

## Troubleshooting

### Problemi comuni

- **Validazione non funziona**: Verifica che la Form Request sia iniettata correttamente
- **Autorizzazione fallisce**: Controlla il metodo `authorize()`
- **Messaggi non personalizzati**: Verifica il metodo `messages()`
- **Regole condizionali**: Controlla la sintassi delle regole condizionali
- **Performance lente**: Ottimizza le regole di validazione

### Debug e monitoring

- **Log di validazione**: Usa `Log::info()` per debug
- **Errori di validazione**: Controlla i messaggi di errore
- **Testing**: Usa test per verificare le validazioni
- **Performance**: Monitora i tempi di validazione

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Le Form Request consumano memoria per ogni richiesta
- **CPU**: La validazione utilizza CPU per controllare le regole
- **I/O**: Validazione database per regole come `unique`

### Scalabilità

- **Carico basso**: Form Request standard sono sufficienti
- **Carico medio**: Considera caching per regole complesse
- **Carico alto**: Ottimizza le regole di validazione

### Colli di bottiglia

- **Regole database**: `unique`, `exists` possono essere lente
- **Validazione complessa**: Regole con molte condizioni
- **Autorizzazione**: Controlli di permessi complessi
- **Messaggi personalizzati**: Generazione di messaggi dinamici

## Risorse utili

### Documentazione ufficiale
- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation) - Documentazione ufficiale
- [Laravel Validation Rules](https://laravel.com/docs/validation#available-validation-rules) - Regole di validazione
- [Laravel Authorization](https://laravel.com/docs/authorization) - Sistema di autorizzazione

### Laravel specifico
- [Laravel Custom Validation Rules](https://laravel.com/docs/validation#custom-validation-rules) - Regole personalizzate
- [Laravel Validation Messages](https://laravel.com/docs/validation#custom-error-messages) - Messaggi personalizzati
- [Laravel Request Lifecycle](https://laravel.com/docs/lifecycle) - Ciclo di vita delle richieste

### Esempi e tutorial
- [Laravel Form Request Examples](https://github.com/laravel/framework) - Esempi nel framework
- [Validation Best Practices](https://laravel.com/docs/validation#form-request-validation) - Best practices
- [Custom Validation Rules](https://laravel.com/docs/validation#custom-validation-rules) - Regole personalizzate

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug delle richieste
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring delle richieste
