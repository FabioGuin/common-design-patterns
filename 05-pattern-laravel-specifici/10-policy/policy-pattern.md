# Policy Pattern

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

Il Policy Pattern centralizza la logica di autorizzazione in classi dedicate chiamate Policy. Invece di mettere controlli di permessi sparsi nel codice, crei classi specifiche che si occupano solo di decidere chi può fare cosa su una risorsa.

È come avere un guardiano intelligente che sa esattamente chi può accedere a cosa e in quali condizioni.

## Perché ti serve

Immagina di avere controlli di autorizzazione sparsi in 20 controller diversi. Se devi cambiare una regola di permesso, devi modificare 20 posti diversi. Con le Policy, cambi una sola volta e tutti i controller usano la nuova regola.

**Problemi che risolve:**
- **Centralizzazione**: Tutta la logica di autorizzazione in un posto
- **Riuso**: Stesse regole in più controller
- **Manutenibilità**: Modifiche centralizzate
- **Testing**: Più facile testare l'autorizzazione
- **Organizzazione**: Codice più pulito e organizzato

## Come funziona

### Struttura Base

**Policy Class:**
- Estende `Policy`
- Contiene metodi per ogni azione
- Riceve utente e risorsa come parametri
- Ritorna `true` o `false`

**Metodi Policy:**
- `view()` - Può visualizzare la risorsa
- `create()` - Può creare la risorsa
- `update()` - Può aggiornare la risorsa
- `delete()` - Può eliminare la risorsa
- `restore()` - Può ripristinare la risorsa
- `forceDelete()` - Può eliminare definitivamente

**Utilizzo:**
- `$this->authorize('view', $post)`
- `$this->authorize('update', $post)`
- `Gate::allows('update', $post)`
- `@can('update', $post)`

### Registrazione Policy

**Service Provider:**
```php
protected $policies = [
    Post::class => PostPolicy::class,
    User::class => UserPolicy::class,
];
```

**Auto-discovery:**
- Laravel trova automaticamente le Policy
- Nome: `PostPolicy` per `Post` model
- Posizione: `app/Policies/`

## Schema visivo

```
Richiesta → Controller → Policy → Autorizzazione
    ↓           ↓         ↓         ↓
User + Post → authorize() → view() → true/false
    ↓           ↓         ↓         ↓
Controller ← Response ← Policy ← Decision
```

**Flusso dettagliato:**
1. **Richiesta** → Utente fa richiesta per risorsa
2. **Controller** → Controller riceve richiesta
3. **Authorization** → Controller chiama `authorize()`
4. **Policy** → Laravel trova Policy appropriata
5. **Method** → Chiama metodo specifico (es: `view()`)
6. **Decision** → Policy ritorna `true` o `false`
7. **Response** → Controller procede o nega accesso

## Quando usarlo

Usa Policy quando:
- **Autorizzazione complessa**: Regole di permesso elaborate
- **Risorse multiple**: Stesse regole per più risorse
- **Logica business**: Autorizzazione basata su logica business
- **Team development**: Più sviluppatori lavorano sul progetto
- **Manutenibilità**: Vuoi codice facile da mantenere
- **Testing**: Vuoi testare autorizzazione separatamente

**NON usarlo quando:**
- **Autorizzazione semplice**: Controlli base (admin, user)
- **Prototipi rapidi**: Sviluppo veloce senza struttura
- **Autorizzazione unica**: Usata solo in un posto
- **Performance critica**: Overhead non accettabile

## Pro e contro

**I vantaggi:**
- **Centralizzazione**: Tutta la logica in un posto
- **Riuso**: Stesse regole in più controller
- **Manutenibilità**: Modifiche centralizzate
- **Testing**: Più facile testare l'autorizzazione
- **Organizzazione**: Codice più pulito
- **Flessibilità**: Regole complesse e condizionali

**Gli svantaggi:**
- **Complessità**: Più classi da gestire
- **Overhead**: Leggero overhead per controlli semplici
- **Learning curve**: Richiede conoscenza di Laravel
- **Over-engineering**: Può essere eccessivo per casi semplici

## Esempi di codice

### Pseudocodice

```
// Policy Class
class PostPolicy {
    view(user, post) {
        // Tutti possono vedere post pubblicati
        if (post.status === 'published') return true
        
        // Solo l'autore può vedere i propri post
        return user.id === post.user_id
    }
    
    create(user) {
        // Solo utenti autenticati possono creare post
        return user !== null
    }
    
    update(user, post) {
        // Solo l'autore o admin possono aggiornare
        return user.id === post.user_id || user.role === 'admin'
    }
    
    delete(user, post) {
        // Solo admin possono eliminare
        return user.role === 'admin'
    }
}

// Controller
class PostController {
    show(user, post) {
        // Controlla autorizzazione
        this.authorize('view', post)
        
        // Procede con la logica
        return view('post.show', post)
    }
    
    update(user, post) {
        // Controlla autorizzazione
        this.authorize('update', post)
        
        // Procede con l'aggiornamento
        post.update(request.data)
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema di Autorizzazione con Policy](./esempio-completo/)** - Sistema completo di gestione permessi

L'esempio include:
- Policy per post, commenti e utenti
- Autorizzazione basata su ruoli
- Autorizzazione basata su proprietà
- Autorizzazione condizionale
- Testing delle Policy
- Middleware per autorizzazione
- Blade directives per autorizzazione

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Form Request Pattern](./08-form-request/form-request-pattern.md)** - Validazione e autorizzazione
- **[Resource Controllers Pattern](./09-resource-controllers/resource-controllers-pattern.md)** - Controller con autorizzazione
- **[Middleware Pattern](./03-middleware/middleware-pattern.md)** - Middleware per autorizzazione globale
- **[Service Layer Pattern](../04-pattern-architetturali/03-service-layer/service-layer-pattern.md)** - Logica business con autorizzazione
- **[Repository Pattern](../04-pattern-architetturali/02-repository/repository-pattern.md)** - Accesso dati con autorizzazione

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione nelle Policy
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi per design delle Policy
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Codice pulito nelle Policy
- **[Separation of Concerns](../00-fondamentali/06-separation-of-concerns/separation-of-concerns.md)** - Separazione autorizzazione da logica business
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test per Policy

## Esempi di uso reale

- **E-commerce**: Autorizzazione per prodotti, ordini, clienti
- **Blog**: Autorizzazione per post, commenti, categorie
- **CRM**: Autorizzazione per contatti, opportunità, attività
- **Sistema di gestione**: Autorizzazione per utenti, ruoli, permessi
- **API REST**: Autorizzazione per endpoint

## Anti-pattern

**Cosa NON fare:**
- **Autorizzazione nei controller**: Metti controlli di autorizzazione nei controller
- **Policy troppo grandi**: Una Policy con centinaia di metodi
- **Duplicazione**: Stesse regole in più Policy
- **Logica business**: Metti logica business nelle Policy
- **Autorizzazione sincrona**: Usa Policy per autorizzazione asincrona

## Troubleshooting

### Problemi comuni

- **Policy non trovata**: Verifica che la Policy sia registrata
- **Autorizzazione fallisce**: Controlla la logica della Policy
- **Metodo non trovato**: Verifica che il metodo esista nella Policy
- **Parametri sbagliati**: Controlla i parametri passati alla Policy
- **Cache Policy**: Pulisci la cache delle Policy

### Debug e monitoring

- **Log di autorizzazione**: Usa `Log::info()` per debug
- **Gate facade**: Usa `Gate::allows()` per test
- **Policy testing**: Usa test per verificare le Policy
- **Performance**: Monitora i tempi di autorizzazione

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: Le Policy consumano memoria per ogni richiesta
- **CPU**: La logica di autorizzazione utilizza CPU
- **I/O**: Query database per controlli di autorizzazione

### Scalabilità

- **Carico basso**: Policy standard sono sufficienti
- **Carico medio**: Considera caching per Policy complesse
- **Carico alto**: Ottimizza le query di autorizzazione

### Colli di bottiglia

- **Query database**: Controlli di autorizzazione con query
- **Policy complesse**: Logica di autorizzazione elaborata
- **Cache Policy**: Cache delle Policy non ottimizzata
- **Middleware**: Middleware di autorizzazione pesanti

## Risorse utili

### Documentazione ufficiale
- [Laravel Authorization](https://laravel.com/docs/authorization) - Documentazione ufficiale
- [Laravel Policies](https://laravel.com/docs/authorization#creating-policies) - Policy
- [Laravel Gates](https://laravel.com/docs/authorization#gates) - Gates

### Laravel specifico
- [Laravel Policy Registration](https://laravel.com/docs/authorization#registering-policies) - Registrazione Policy
- [Laravel Policy Methods](https://laravel.com/docs/authorization#policy-methods) - Metodi Policy
- [Laravel Blade Directives](https://laravel.com/docs/blade#authorization) - Direttive Blade

### Esempi e tutorial
- [Laravel Policy Examples](https://github.com/laravel/framework) - Esempi nel framework
- [Authorization Best Practices](https://laravel.com/docs/authorization#authorizing-actions) - Best practices
- [Policy Testing](https://laravel.com/docs/testing#testing-policies) - Testing Policy

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug delle richieste
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring delle richieste
