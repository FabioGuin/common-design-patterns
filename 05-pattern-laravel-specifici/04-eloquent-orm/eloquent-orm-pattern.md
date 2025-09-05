# Eloquent ORM Pattern

## Panoramica

L'**Eloquent ORM Pattern** è un pattern architetturale che implementa l'Object-Relational Mapping (ORM) in Laravel. Eloquent fornisce un'interfaccia elegante e intuitiva per interagire con il database, permettendo di lavorare con i dati come oggetti PHP invece di query SQL raw.

## Problema Risolto

### Problemi Comuni
- **Query SQL complesse**: Scrittura manuale di query SQL
- **Mapping manuale**: Conversione tra record database e oggetti PHP
- **Gestione relazioni**: Join e relazioni complesse
- **Sicurezza**: SQL injection e validazione dati

### Esempi di Problemi
```pseudocodice
// Problema: Query SQL raw complesse
function getUsersWithPosts() {
    $sql = "SELECT u.*, p.title, p.content 
            FROM users u 
            LEFT JOIN posts p ON u.id = p.user_id 
            WHERE u.active = 1 
            ORDER BY u.created_at DESC";
    
    $result = mysqli_query($connection, $sql);
    $users = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'posts' => [
                'title' => $row['title'],
                'content' => $row['content']
            ]
        ];
    }
    
    return $users;
}
```

## Soluzione

### Architettura del Pattern

```pseudocodice
// Struttura base Eloquent Model
class Model {
    protected $table;
    protected $fillable;
    protected $hidden;
    protected $casts;
    
    function save() {
        // Salva nel database
    }
    
    function find($id) {
        // Trova per ID
    }
    
    function where($column, $value) {
        // Filtra per colonna
    }
}
```

### Componenti Principali

1. **Model Base**
   - Mappatura tabella-oggetto
   - Attributi e proprietà
   - Metodi di accesso

2. **Query Builder**
   - Costruzione query fluente
   - Metodi di filtraggio
   - Esecuzione ottimizzata

3. **Relazioni**
   - One-to-One
   - One-to-Many
   - Many-to-Many
   - Polymorphic

4. **Eventi e Observer**
   - Hook del ciclo di vita
   - Validazione automatica
   - Logging e audit

## Vantaggi

### 1. **Produttività**
- Sintassi intuitiva
- Meno codice boilerplate
- Sviluppo rapido

### 2. **Sicurezza**
- Protezione SQL injection
- Validazione automatica
- Sanitizzazione dati

### 3. **Manutenibilità**
- Codice leggibile
- Relazioni chiare
- Refactoring facile

### 4. **Performance**
- Lazy loading
- Eager loading
- Query optimization

## Svantaggi

### 1. **Performance**
- Overhead ORM
- Query N+1
- Memoria aggiuntiva

### 2. **Complessità**
- Curva di apprendimento
- Debugging query
- Ottimizzazione avanzata

## Caso d'Uso

### Scenario: Sistema di Blog
```pseudocodice
// Modello User
class User extends Model {
    protected $fillable = ['name', 'email', 'password'];
    
    function posts() {
        return hasMany(Post::class);
    }
    
    function profile() {
        return hasOne(Profile::class);
    }
}

// Modello Post
class Post extends Model {
    protected $fillable = ['title', 'content', 'user_id'];
    
    function user() {
        return belongsTo(User::class);
    }
    
    function comments() {
        return hasMany(Comment::class);
    }
    
    function tags() {
        return belongsToMany(Tag::class);
    }
}

// Utilizzo
$user = User::find(1);
$posts = $user->posts()->where('published', true)->get();
$recentPosts = Post::with('user', 'comments')->latest()->take(10)->get();
```

## Implementazione Laravel

### 1. **Creazione Model**

```bash
php artisan make:model User -m
```

### 2. **Definizione Model**

```pseudocodice
class User extends Model {
    use HasFactory, Notifiable

    protected fillable = [
        'name',
        'email',
        'password',
    ]

    protected hidden = [
        'password',
        'remember_token',
    ]

    protected casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ]

    function posts() {
        return this.hasMany(Post::class)
    }

    function profile() {
        return this.hasOne(Profile::class)
    }
}
```

### 3. **Migration**

```pseudocodice
Schema.create('users', function (table) {
    table.id()
    table.string('name')
    table.string('email').unique()
    table.timestamp('email_verified_at').nullable()
    table.string('password')
    table.rememberToken()
    table.timestamps()
})
```

### 4. **Query Eloquent**

```pseudocodice
// Creazione
user = User.create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash.make('password'),
])

// Lettura
user = User.find(1)
users = User.where('active', true).get()
user = User.where('email', 'john@example.com').first()

// Aggiornamento
user.update(['name' => 'Jane Doe'])
User.where('active', false).update(['status' => 'inactive'])

// Eliminazione
user.delete()
User.where('created_at', '<', now().subYear()).delete()
```

## Esempi Pratici

### 1. **Relazioni One-to-Many**

```pseudocodice
class Post extends Model {
    function user() {
        return this.belongsTo(User::class)
    }

    function comments() {
        return this.hasMany(Comment::class)
    }
}

// Utilizzo
post = Post.with('user', 'comments.user').find(1)
userPosts = User.find(1).posts().published().get()
```

### 2. **Relazioni Many-to-Many**

```pseudocodice
class Post extends Model {
    function tags() {
        return this.belongsToMany(Tag::class)
    }
}

class Tag extends Model {
    function posts() {
        return this.belongsToMany(Post::class)
    }
}

// Utilizzo
post = Post.find(1)
post.tags().attach([1, 2, 3])
post.tags().detach(2)
post.tags().sync([1, 3, 4])
```

### 3. **Relazioni Polymorphic**

```pseudocodice
class Comment extends Model {
    function commentable() {
        return this.morphTo()
    }
}

class Post extends Model {
    function comments() {
        return this.morphMany(Comment::class, 'commentable')
    }
}

// Utilizzo
post = Post.find(1)
comment = post.comments().create([
    'content' => 'Great post!',
    'user_id' => 1,
])
```

### 4. **Scopes e Accessors**

```pseudocodice
class Post extends Model {
    function scopePublished(query) {
        return query.where('published', true)
    }

    function scopeByUser(query, userId) {
        return query.where('user_id', userId)
    }

    function getExcerptAttribute() {
        return Str.limit(this.content, 100)
    }

    function getPublishedAtAttribute(value) {
        return Carbon.parse(value).format('d/m/Y')
    }
}

// Utilizzo
posts = Post.published().byUser(1).get()
excerpt = post.excerpt // Accessor
```

### 5. **Eventi e Observer**

```pseudocodice
class PostObserver {
    function creating(post) {
        post.slug = Str.slug(post.title)
    }

    function created(post) {
        // Invia notifica
        Mail.to(post.user).send(new PostCreated(post))
    }

    function updating(post) {
        if (post.isDirty('title')) {
            post.slug = Str.slug(post.title)
        }
    }
}

// Registrazione Observer
Post.observe(PostObserver::class)
```

## Query Avanzate

### 1. **Eager Loading**

```pseudocodice
// Evita N+1 queries
posts = Post.with(['user', 'comments.user']).get()

// Lazy loading
posts = Post.all()
foreach (posts as post) {
    echo post.user.name // Carica user solo quando necessario
}
```

### 2. **Query Builder Avanzato**

```pseudocodice
posts = Post.query()
    .where('published', true)
    .whereHas('user', function (query) {
        query.where('active', true)
    })
    .withCount('comments')
    .orderBy('created_at', 'desc')
    .paginate(10)
```

### 3. **Raw Queries**

```pseudocodice
users = User.selectRaw('name, email, COUNT(posts.id) as post_count')
    .leftJoin('posts', 'users.id', '=', 'posts.user_id')
    .groupBy('users.id')
    .get()
```

## Best Practices

### 1. **Organizzazione**
- Un model per tabella
- Nomi descrittivi
- Relazioni chiare

### 2. **Performance**
- Usa eager loading
- Evita N+1 queries
- Ottimizza le query

### 3. **Sicurezza**
- Usa fillable/guarded
- Valida i dati
- Sanitizza gli input

### 4. **Testing**
- Testa i model isolatamente
- Mock delle relazioni
- Verifica del comportamento

## Pattern Correlati

- **Active Record**: Pattern base per ORM
- **Repository Pattern**: Astrazione dell'accesso ai dati
- **Unit of Work**: Gestione transazioni
- **Data Mapper**: Separazione logica e persistenza

## Conclusione

L'Eloquent ORM Pattern è il cuore di Laravel per l'interazione con il database. Fornisce un'interfaccia elegante e potente per gestire i dati, le relazioni e le operazioni CRUD, migliorando significativamente la produttività dello sviluppatore.

La chiave per un uso efficace è comprendere le relazioni, ottimizzare le query e seguire le best practices di Laravel per mantenere il codice pulito e performante.
