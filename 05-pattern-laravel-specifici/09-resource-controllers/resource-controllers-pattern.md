# Resource Controllers Pattern

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

Il Resource Controllers Pattern standardizza la struttura dei controller per operazioni CRUD (Create, Read, Update, Delete) su risorse. Invece di creare controller con metodi casuali, segui una convenzione predefinita che mappa le operazioni HTTP alle azioni del controller.

È come avere un template standard per gestire le tue risorse in modo consistente e prevedibile.

## Perché ti serve

Immagina di avere 20 controller diversi, ognuno con nomi di metodi diversi per le stesse operazioni. Con Resource Controllers, sai sempre che `index()` mostra la lista, `store()` crea, `show()` mostra un elemento, `update()` aggiorna e `destroy()` elimina.

**Problemi che risolve:**
- **Consistenza**: Stessa struttura per tutti i controller
- **Convenzioni**: Nomi standardizzati per le azioni
- **Riduce confusione**: Sviluppatori sanno cosa aspettarsi
- **Riduce duplicazione**: Stessa logica per operazioni simili
- **Facilita manutenzione**: Struttura prevedibile

## Come funziona

### Struttura Standard

**Metodi Resource Controller:**
- `index()` - Mostra lista delle risorse
- `create()` - Mostra form per creare nuova risorsa
- `store()` - Salva nuova risorsa
- `show()` - Mostra risorsa specifica
- `edit()` - Mostra form per modificare risorsa
- `update()` - Aggiorna risorsa esistente
- `destroy()` - Elimina risorsa

**Route Resource:**
- `GET /posts` → `index()`
- `GET /posts/create` → `create()`
- `POST /posts` → `store()`
- `GET /posts/{id}` → `show()`
- `GET /posts/{id}/edit` → `edit()`
- `PUT/PATCH /posts/{id}` → `update()`
- `DELETE /posts/{id}` → `destroy()`

### Convenzioni Laravel

**Naming Convention:**
- Controller: `PostController` (singolare + Controller)
- Model: `Post` (singolare)
- Route: `posts` (plurale)
- View: `posts.index`, `posts.show`, etc.

**Route Binding:**
- Laravel risolve automaticamente i parametri
- `{post}` viene risolto in `Post::findOrFail($id)`

## Schema visivo

```
HTTP Request → Route → Resource Controller → Action Method
     ↓              ↓           ↓              ↓
GET /posts    → posts.index → PostController → index()
POST /posts   → posts.store → PostController → store()
GET /posts/1  → posts.show  → PostController → show()
PUT /posts/1  → posts.update → PostController → update()
DELETE /posts/1 → posts.destroy → PostController → destroy()
```

**Flusso dettagliato:**
1. **HTTP Request** → Richiesta arriva al router
2. **Route Resolution** → Laravel trova la route corrispondente
3. **Controller Instantiation** → Laravel crea istanza del controller
4. **Action Method** → Chiama il metodo appropriato
5. **Response** → Ritorna la risposta

## Quando usarlo

Usa Resource Controllers quando:
- **Operazioni CRUD**: Gestisci risorse con operazioni standard
- **Consistenza**: Vuoi struttura uniforme per tutti i controller
- **Team development**: Più sviluppatori lavorano sul progetto
- **API RESTful**: Stai creando API REST
- **Convenzioni**: Vuoi seguire le convenzioni Laravel
- **Manutenibilità**: Vuoi codice facile da mantenere

**NON usarlo quando:**
- **Operazioni complesse**: Logica business molto specifica
- **Operazioni non-CRUD**: Azioni che non seguono il pattern CRUD
- **Prototipi rapidi**: Sviluppo veloce senza struttura
- **Operazioni batch**: Operazioni su multiple risorse
- **Operazioni asincrone**: Job, queue, etc.

## Pro e contro

**I vantaggi:**
- **Consistenza**: Stessa struttura per tutti i controller
- **Convenzioni**: Nomi standardizzati e prevedibili
- **Riduce confusione**: Sviluppatori sanno cosa aspettarsi
- **Facilita manutenzione**: Struttura uniforme
- **Riduce duplicazione**: Stessa logica per operazioni simili
- **API RESTful**: Segue standard REST

**Gli svantaggi:**
- **Rigidità**: Struttura fissa può essere limitante
- **Over-engineering**: Può essere eccessivo per operazioni semplici
- **Learning curve**: Richiede conoscenza delle convenzioni
- **Complessità**: Può essere complesso per operazioni non standard

## Esempi di codice

### Pseudocodice

```
// Resource Controller
class PostController {
    index() {
        // Mostra lista di tutti i post
        posts = Post.all()
        return view('posts.index', posts)
    }
    
    create() {
        // Mostra form per creare nuovo post
        return view('posts.create')
    }
    
    store(request) {
        // Salva nuovo post
        post = Post.create(request.validated())
        return redirect('posts.show', post)
    }
    
    show(post) {
        // Mostra post specifico
        return view('posts.show', post)
    }
    
    edit(post) {
        // Mostra form per modificare post
        return view('posts.edit', post)
    }
    
    update(request, post) {
        // Aggiorna post esistente
        post.update(request.validated())
        return redirect('posts.show', post)
    }
    
    destroy(post) {
        // Elimina post
        post.delete()
        return redirect('posts.index')
    }
}

// Route Registration
Route::resource('posts', PostController::class)
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Sistema Blog con Resource Controllers](./esempio-completo/)** - Sistema completo di gestione blog

L'esempio include:
- Resource Controller per post
- Resource Controller per commenti
- Resource Controller per categorie
- Route resource complete
- Form Request integration
- Authorization e validation
- API Resource per JSON responses

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Form Request Pattern](./08-form-request/form-request-pattern.md)** - Validazione per Resource Controllers
- **[Policy Pattern](./10-policy/policy-pattern.md)** - Autorizzazione per Resource Controllers
- **[Service Layer Pattern](../04-pattern-architetturali/03-service-layer/service-layer-pattern.md)** - Logica business per Resource Controllers
- **[Repository Pattern](../04-pattern-architetturali/02-repository/repository-pattern.md)** - Accesso dati per Resource Controllers
- **[DTO Pattern](../04-pattern-architetturali/04-dto/dto-pattern.md)** - Data Transfer Objects per Resource Controllers

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione nei Resource Controllers
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi per design dei Resource Controllers
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Codice pulito nei Resource Controllers
- **[Separation of Concerns](../00-fondamentali/06-separation-of-concerns/separation-of-concerns.md)** - Separazione responsabilità nei Resource Controllers
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test per Resource Controllers

## Esempi di uso reale

- **E-commerce**: Gestione prodotti, ordini, clienti
- **Blog**: Gestione post, commenti, categorie
- **CRM**: Gestione contatti, opportunità, attività
- **Sistema di gestione**: Gestione utenti, ruoli, permessi
- **API REST**: Endpoint per applicazioni mobile/web

## Anti-pattern

**Cosa NON fare:**
- **Metodi non standard**: Usa nomi di metodi diversi dalle convenzioni
- **Logica business nei controller**: Metti la logica business nei controller
- **Controller troppo grandi**: Un controller con troppe responsabilità
- **Duplicazione**: Stessa logica in più controller
- **Mixing concerns**: Mescola responsabilità diverse nello stesso controller

## Troubleshooting

### Problemi comuni

- **Route non funziona**: Verifica che la route sia registrata correttamente
- **Model binding non funziona**: Controlla il nome del parametro nella route
- **Metodo non trovato**: Verifica che il metodo esista nel controller
- **View non trovata**: Controlla il nome della vista
- **Authorization fallisce**: Verifica i middleware e le policy

### Debug e monitoring

- **Route list**: Usa `php artisan route:list` per vedere le route
- **Controller methods**: Verifica che tutti i metodi esistano
- **Model binding**: Controlla che il model sia configurato correttamente
- **Log**: Usa log per debug delle operazioni

## Performance e considerazioni

### Impatto sulle risorse

- **Memoria**: I controller consumano memoria per ogni richiesta
- **CPU**: La logica nei controller utilizza CPU
- **I/O**: Operazioni database nei controller

### Scalabilità

- **Carico basso**: Resource Controllers standard sono sufficienti
- **Carico medio**: Considera caching e ottimizzazioni
- **Carico alto**: Usa Service Layer per logica complessa

### Colli di bottiglia

- **N+1 queries**: Caricamento eager per relazioni
- **Logica complessa**: Sposta logica business nei Service
- **Validazione pesante**: Usa Form Request per validazione
- **Autorizzazione complessa**: Usa Policy per autorizzazione

## Risorse utili

### Documentazione ufficiale
- [Laravel Resource Controllers](https://laravel.com/docs/controllers#resource-controllers) - Documentazione ufficiale
- [Laravel Route Model Binding](https://laravel.com/docs/routing#route-model-binding) - Model binding
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources) - API Resources

### Laravel specifico
- [Laravel Resource Routes](https://laravel.com/docs/routing#resource-routes) - Route resource
- [Laravel Controller Middleware](https://laravel.com/docs/controllers#controller-middleware) - Middleware per controller
- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation) - Validazione

### Esempi e tutorial
- [Laravel Resource Controller Examples](https://github.com/laravel/framework) - Esempi nel framework
- [RESTful API with Laravel](https://laravel.com/docs/sanctum) - API RESTful
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices) - Best practices

### Strumenti di supporto
- [Checklist di Implementazione Pattern](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug delle richieste
- [Laravel Telescope](https://laravel.com/docs/telescope) - Monitoring delle richieste
