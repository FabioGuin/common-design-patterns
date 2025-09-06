# Sistema di Autorizzazione con Policy Completo

## Panoramica

Questo esempio dimostra l'implementazione del pattern Policy in un sistema di autorizzazione Laravel. Il sistema utilizza Policy per centralizzare la logica di autorizzazione, gestendo permessi complessi basati su ruoli, proprietà e condizioni business.

## Architettura

### Policy Types
- **PostPolicy**: Autorizzazione per operazioni sui post
- **CommentPolicy**: Autorizzazione per operazioni sui commenti
- **UserPolicy**: Autorizzazione per operazioni sugli utenti
- **CategoryPolicy**: Autorizzazione per operazioni sulle categorie

### Authorization Features
- **Role-based**: Autorizzazione basata su ruoli utente
- **Ownership-based**: Autorizzazione basata su proprietà
- **Conditional**: Autorizzazione condizionale
- **Resource-specific**: Autorizzazione specifica per risorsa

### Integration Points
- **Controllers**: Integrazione con Resource Controllers
- **Middleware**: Middleware per autorizzazione globale
- **Blade**: Direttive Blade per autorizzazione
- **API**: Autorizzazione per API endpoints

## Struttura del Progetto

```
app/
├── Policies/                 # Policy classes
│   ├── PostPolicy.php
│   ├── CommentPolicy.php
│   ├── UserPolicy.php
│   └── CategoryPolicy.php
├── Http/
│   ├── Controllers/          # Controllers con autorizzazione
│   │   ├── PostController.php
│   │   ├── CommentController.php
│   │   └── UserController.php
│   └── Middleware/           # Middleware per autorizzazione
│       └── CheckPermission.php
├── Models/                   # Models con autorizzazione
│   ├── Post.php
│   ├── Comment.php
│   ├── User.php
│   └── Category.php
└── Services/                 # Business logic
    ├── AuthorizationService.php
    └── RoleService.php
```

## Funzionalità Implementate

### Post Authorization
-  Visualizzazione post (pubblici vs privati)
-  Creazione post (utenti autenticati)
-  Modifica post (autore o admin)
-  Eliminazione post (admin o autore)
-  Pubblicazione post (admin o moderatore)

### Comment Authorization
-  Visualizzazione commenti (pubblici)
-  Creazione commenti (utenti autenticati)
-  Modifica commenti (autore o admin)
-  Eliminazione commenti (autore, admin o moderatore)
-  Approvazione commenti (admin o moderatore)

### User Authorization
-  Visualizzazione profili (pubblici)
-  Modifica profili (proprio profilo o admin)
-  Eliminazione utenti (admin)
-  Gestione ruoli (admin)
-  Gestione permessi (admin)

### Category Authorization
-  Visualizzazione categorie (pubbliche)
-  Creazione categorie (admin o moderatore)
-  Modifica categorie (admin o moderatore)
-  Eliminazione categorie (admin)

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/policy`
3. **Testa le funzionalità**:
   - Prova ad accedere a risorse con diversi ruoli
   - Testa le operazioni CRUD
   - Verifica i controlli di autorizzazione

## Configurazione

### Database
```bash
# Crea le tabelle
php artisan migrate

# Seed dei dati di esempio
php artisan db:seed
```

### Policy Registration
```php
// In AuthServiceProvider
protected $policies = [
    Post::class => PostPolicy::class,
    Comment::class => CommentPolicy::class,
    User::class => UserPolicy::class,
    Category::class => CategoryPolicy::class,
];
```

## Esempi di Utilizzo

### Controller Authorization
```php
// Nel controller
public function show(Post $post)
{
    $this->authorize('view', $post);
    return view('posts.show', compact('post'));
}

public function update(Post $post)
{
    $this->authorize('update', $post);
    // Logica di aggiornamento
}
```

### Blade Authorization
```blade
@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}">Modifica</a>
@endcan

@can('delete', $post)
    <form action="{{ route('posts.destroy', $post) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit">Elimina</button>
    </form>
@endcan
```

### API Authorization
```php
// Nel controller API
public function index()
{
    $this->authorize('viewAny', Post::class);
    return Post::all();
}
```

## Policy Examples

### PostPolicy
```php
class PostPolicy
{
    public function view(User $user, Post $post)
    {
        // Tutti possono vedere post pubblicati
        if ($post->status === 'published') {
            return true;
        }
        
        // Solo l'autore può vedere i propri post
        return $user->id === $post->user_id;
    }
    
    public function update(User $user, Post $post)
    {
        // Admin può modificare tutto
        if ($user->isAdmin()) {
            return true;
        }
        
        // L'autore può modificare i propri post
        return $user->id === $post->user_id;
    }
}
```

### CommentPolicy
```php
class CommentPolicy
{
    public function approve(User $user, Comment $comment)
    {
        // Solo admin e moderatori possono approvare
        return $user->isAdmin() || $user->isModerator();
    }
    
    public function delete(User $user, Comment $comment)
    {
        // Admin, moderatori e autore possono eliminare
        return $user->isAdmin() || 
               $user->isModerator() || 
               $user->id === $comment->user_id;
    }
}
```

## Testing

### Unit Tests
```bash
# Esegui i test
php artisan test

# Test specifici per Policy
php artisan test --filter=PolicyTest
```

### Test Examples
- Test autorizzazione per diversi ruoli
- Test autorizzazione per proprietà
- Test autorizzazione condizionale
- Test Policy methods

## Best Practices

### Policy Design
- Mantieni le Policy focalizzate su una risorsa
- Usa metodi chiari e descrittivi
- Implementa autorizzazione granulare
- Considera performance e caching

### Controller Integration
- Usa `authorize()` nei controller
- Implementa autorizzazione appropriata
- Gestisci errori di autorizzazione
- Usa middleware quando necessario

### Performance
- Usa caching per Policy complesse
- Ottimizza le query di autorizzazione
- Considera lazy loading per relazioni
- Monitora performance delle Policy

### Security
- Implementa autorizzazione appropriata
- Valida tutti gli input
- Usa HTTPS in produzione
- Monitora tentativi di accesso non autorizzati
