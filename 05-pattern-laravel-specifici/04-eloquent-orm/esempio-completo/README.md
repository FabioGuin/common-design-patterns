# Eloquent ORM Pattern - Esempio Completo

## Panoramica

Questo esempio dimostra l'implementazione dell'**Eloquent ORM Pattern** in Laravel, mostrando come utilizzare i modelli Eloquent per gestire dati, relazioni, query complesse e operazioni CRUD in modo elegante e efficiente.

## Struttura del Progetto

```
esempio-completo/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Post.php
│   │   ├── Comment.php
│   │   ├── Tag.php
│   │   ├── Category.php
│   │   └── Profile.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── BlogController.php
│   │       ├── UserController.php
│   │       └── ApiController.php
│   ├── Observers/
│   │   ├── PostObserver.php
│   │   └── UserObserver.php
│   └── Services/
│       ├── BlogService.php
│       └── UserService.php
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_posts_table.php
│   │   ├── create_comments_table.php
│   │   ├── create_tags_table.php
│   │   ├── create_categories_table.php
│   │   ├── create_profiles_table.php
│   │   └── create_post_tag_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── PostSeeder.php
│   │   └── CommentSeeder.php
│   └── factories/
│       ├── UserFactory.php
│       ├── PostFactory.php
│       └── CommentFactory.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── blog/
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   └── create.blade.php
│       └── users/
│           ├── index.blade.php
│           └── show.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── composer.json
└── env.example
```

## Installazione

1. **Clona il repository**
```bash
git clone <repository-url>
cd esempio-completo
```

2. **Installa le dipendenze**
```bash
composer install
```

3. **Configura l'ambiente**
```bash
cp env.example .env
php artisan key:generate
```

4. **Configura il database**
```bash
php artisan migrate
php artisan db:seed
```

5. **Avvia il server**
```bash
php artisan serve
```

## Modelli Implementati

### 1. **User Model**
- Gestione utenti con autenticazione
- Relazioni con Post, Comment, Profile
- Scopes e accessors personalizzati
- Eventi e observer

### 2. **Post Model**
- Gestione articoli del blog
- Relazioni con User, Comment, Tag, Category
- Scopes per filtraggio
- Soft deletes

### 3. **Comment Model**
- Sistema di commenti
- Relazioni polymorphic
- Moderation e approval

### 4. **Tag Model**
- Sistema di tag
- Relazioni many-to-many con Post
- Slug automatico

### 5. **Category Model**
- Categorie per i post
- Relazioni one-to-many con Post
- Gerarchia categorie

### 6. **Profile Model**
- Profili utente estesi
- Relazioni one-to-one con User
- Dati aggiuntivi

## Relazioni Implementate

### 1. **One-to-Many**
```php
// User -> Posts
public function posts()
{
    return $this->hasMany(Post::class);
}

// Post -> Comments
public function comments()
{
    return $this->hasMany(Comment::class);
}
```

### 2. **Many-to-Many**
```php
// Post -> Tags
public function tags()
{
    return $this->belongsToMany(Tag::class);
}

// User -> Roles
public function roles()
{
    return $this->belongsToMany(Role::class);
}
```

### 3. **One-to-One**
```php
// User -> Profile
public function profile()
{
    return $this->hasOne(Profile::class);
}
```

### 4. **Polymorphic**
```php
// Comment -> Postable (Post, Video, etc.)
public function commentable()
{
    return $this->morphTo();
}
```

## Utilizzo

### Query Base

```php
// Creazione
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
]);

// Lettura
$user = User::find(1);
$users = User::where('active', true)->get();
$user = User::where('email', 'john@example.com')->first();

// Aggiornamento
$user->update(['name' => 'Jane Doe']);
User::where('active', false)->update(['status' => 'inactive']);

// Eliminazione
$user->delete();
User::where('created_at', '<', now()->subYear())->delete();
```

### Query con Relazioni

```php
// Eager loading
$posts = Post::with(['user', 'comments.user', 'tags'])->get();

// Lazy loading
$user = User::find(1);
$posts = $user->posts; // Carica automaticamente

// Query con relazioni
$posts = Post::whereHas('user', function ($query) {
    $query->where('active', true);
})->get();
```

### Scopes Personalizzati

```php
// Nel model
public function scopePublished($query)
{
    return $query->where('published', true);
}

public function scopeByUser($query, $userId)
{
    return $query->where('user_id', $userId);
}

// Utilizzo
$posts = Post::published()->byUser(1)->get();
```

### Accessors e Mutators

```php
// Accessor
public function getExcerptAttribute()
{
    return Str::limit($this->content, 100);
}

// Mutator
public function setTitleAttribute($value)
{
    $this->attributes['title'] = $value;
    $this->attributes['slug'] = Str::slug($value);
}
```

## Eventi e Observer

### PostObserver

```php
class PostObserver
{
    public function creating(Post $post)
    {
        $post->slug = Str::slug($post->title);
    }

    public function created(Post $post)
    {
        // Invia notifica
        Mail::to($post->user)->send(new PostCreated($post));
    }
}
```

### Registrazione Observer

```php
// AppServiceProvider
public function boot()
{
    Post::observe(PostObserver::class);
    User::observe(UserObserver::class);
}
```

## Factory e Seeder

### UserFactory

```php
class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ];
    }
}
```

### DatabaseSeeder

```php
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::factory(10)->create();
        Post::factory(50)->create();
        Comment::factory(200)->create();
    }
}
```

## Test

### Test Model

```php
class PostTest extends TestCase
{
    public function test_post_has_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }
}
```

### Test Relazioni

```php
public function test_post_has_many_comments()
{
    $post = Post::factory()->create();
    $comments = Comment::factory(3)->create(['post_id' => $post->id]);
    
    $this->assertCount(3, $post->comments);
}
```

## Performance

### Eager Loading

```php
// Evita N+1 queries
$posts = Post::with(['user', 'comments.user'])->get();

// Lazy loading
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name; // Carica user solo quando necessario
}
```

### Query Optimization

```php
// Usa select specifici
$users = User::select('id', 'name', 'email')->get();

// Usa chunk per grandi dataset
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Processa user
    }
});
```

## Debugging

### Query Logging

```php
// Abilita query logging
DB::enableQueryLog();

$posts = Post::with('user')->get();

$queries = DB::getQueryLog();
dd($queries);
```

### Laravel Debugbar

```bash
composer require barryvdh/laravel-debugbar --dev
```

## Conclusione

Questo esempio dimostra come utilizzare efficacemente l'Eloquent ORM Pattern in Laravel per gestire i dati in modo elegante e efficiente. Eloquent fornisce un'interfaccia potente per le operazioni CRUD, le relazioni e le query complesse, migliorando significativamente la produttività dello sviluppatore.

La chiave per un uso efficace è comprendere le relazioni, ottimizzare le query e seguire le best practices di Laravel per mantenere il codice pulito e performante.
