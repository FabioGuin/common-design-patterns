# Sistema Blog con Resource Controllers Completo

## Panoramica

Questo esempio dimostra l'implementazione del pattern Resource Controllers in un sistema di blog Laravel. Il sistema utilizza Resource Controllers per gestire post, commenti e categorie seguendo le convenzioni RESTful e le best practices Laravel.

## Architettura

### Resource Controllers
- **PostController**: Gestione completa dei post del blog
- **CommentController**: Gestione commenti sui post
- **CategoryController**: Gestione categorie dei post

### CRUD Operations
- **Create**: Creazione nuove risorse
- **Read**: Lettura e visualizzazione risorse
- **Update**: Aggiornamento risorse esistenti
- **Delete**: Eliminazione risorse

### Features
- **Route Model Binding**: Binding automatico dei modelli
- **Form Request**: Validazione centralizzata
- **Authorization**: Controlli di permessi
- **API Resources**: Serializzazione JSON
- **Pagination**: Paginazione per liste

## Struttura del Progetto

```
app/
├── Http/
│   ├── Controllers/        # Resource Controllers
│   │   ├── PostController.php
│   │   ├── CommentController.php
│   │   └── CategoryController.php
│   ├── Requests/           # Form Requests
│   │   ├── PostRequest.php
│   │   ├── CommentRequest.php
│   │   └── CategoryRequest.php
│   └── Resources/          # API Resources
│       ├── PostResource.php
│       ├── CommentResource.php
│       └── CategoryResource.php
├── Models/                 # Models
│   ├── Post.php
│   ├── Comment.php
│   └── Category.php
└── Services/              # Business logic
    ├── PostService.php
    ├── CommentService.php
    └── CategoryService.php
```

## Funzionalità Implementate

### Post Management
-  Lista post con paginazione
-  Creazione nuovo post
-  Visualizzazione post singolo
-  Modifica post esistente
-  Eliminazione post
-  Ricerca e filtri

### Comment Management
-  Lista commenti per post
-  Creazione nuovo commento
-  Modifica commento
-  Eliminazione commento
-  Approvazione commenti

### Category Management
-  Lista categorie
-  Creazione nuova categoria
-  Modifica categoria
-  Eliminazione categoria
-  Post per categoria

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/resource-controllers`
3. **Testa le funzionalità**:
   - Gestisci i post
   - Aggiungi commenti
   - Crea categorie
   - Testa le operazioni CRUD

## Configurazione

### Database
```bash
# Crea le tabelle
php artisan migrate

# Seed dei dati di esempio
php artisan db:seed
```

### Routes
```php
// Resource routes
Route::resource('posts', PostController::class);
Route::resource('comments', CommentController::class);
Route::resource('categories', CategoryController::class);

// Nested resource routes
Route::resource('posts.comments', CommentController::class)->shallow();
```

## Esempi di Utilizzo

### Post Controller
```php
// Lista post
GET /posts

// Crea nuovo post
POST /posts

// Mostra post specifico
GET /posts/{post}

// Modifica post
PUT /posts/{post}

// Elimina post
DELETE /posts/{post}
```

### Comment Controller
```php
// Lista commenti per post
GET /posts/{post}/comments

// Crea nuovo commento
POST /posts/{post}/comments

// Modifica commento
PUT /comments/{comment}

// Elimina commento
DELETE /comments/{comment}
```

### Category Controller
```php
// Lista categorie
GET /categories

// Crea nuova categoria
POST /categories

// Mostra categoria specifica
GET /categories/{category}

// Modifica categoria
PUT /categories/{category}

// Elimina categoria
DELETE /categories/{category}
```

## Resource Controller Methods

### Standard Methods
- **index()**: Lista delle risorse
- **create()**: Form per creare nuova risorsa
- **store()**: Salva nuova risorsa
- **show()**: Mostra risorsa specifica
- **edit()**: Form per modificare risorsa
- **update()**: Aggiorna risorsa esistente
- **destroy()**: Elimina risorsa

### Custom Methods
- **search()**: Ricerca risorse
- **archive()**: Archivia risorse
- **restore()**: Ripristina risorse archiviate
- **bulk()**: Operazioni su multiple risorse

## API Resources

### Post Resource
```php
class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'category' => new CategoryResource($this->category),
            'comments' => CommentResource::collection($this->comments),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

## Testing

### Unit Tests
```bash
# Esegui i test
php artisan test

# Test specifici per Resource Controllers
php artisan test --filter=ResourceControllerTest
```

### Test Examples
- Test operazioni CRUD
- Test autorizzazione
- Test validazione
- Test API responses

## Best Practices

### Controller Design
- Mantieni i controller focalizzati su una risorsa
- Usa Service Layer per logica business complessa
- Implementa autorizzazione appropriata
- Usa Form Request per validazione

### Route Design
- Usa route resource quando possibile
- Implementa route nested per relazioni
- Considera route API separate
- Usa middleware appropriati

### Performance
- Usa eager loading per relazioni
- Implementa caching per operazioni costose
- Usa paginazione per liste grandi
- Ottimizza le query database

### Security
- Implementa autorizzazione appropriata
- Valida tutti gli input
- Usa CSRF protection
- Sanitizza i dati di output
