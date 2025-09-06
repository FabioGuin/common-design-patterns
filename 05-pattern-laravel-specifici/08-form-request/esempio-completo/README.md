# Sistema di Registrazione con Form Request Completo

## Panoramica

Questo esempio dimostra l'implementazione del pattern Form Request in un sistema di registrazione e gestione utenti Laravel. Il sistema utilizza Form Request per centralizzare la validazione e l'autorizzazione, separando la logica di validazione dalla logica business.

## Architettura

### Form Request Types
- **CreateUserRequest**: Validazione per creazione nuovi utenti
- **UpdateUserRequest**: Validazione per aggiornamento profilo utente
- **CreatePostRequest**: Validazione per creazione post
- **UpdatePostRequest**: Validazione per aggiornamento post

### Validation Features
- **Regole condizionali**: Validazione basata su altri campi
- **Regole personalizzate**: Validazione custom per casi specifici
- **Autorizzazione**: Controlli di permessi integrati
- **Messaggi personalizzati**: Errori chiari e localizzati

### Authorization System
- **Role-based**: Controlli basati su ruoli utente
- **Resource-based**: Controlli basati su risorse specifiche
- **Conditional**: Autorizzazione condizionale

## Struttura del Progetto

```
app/
├── Http/
│   ├── Requests/           # Form Request classes
│   │   ├── CreateUserRequest.php
│   │   ├── UpdateUserRequest.php
│   │   ├── CreatePostRequest.php
│   │   └── UpdatePostRequest.php
│   └── Controllers/        # Controllers
│       ├── UserController.php
│       └── PostController.php
├── Models/                 # Models
│   ├── User.php
│   └── Post.php
└── Services/              # Business logic
    ├── UserService.php
    └── PostService.php
```

## Funzionalità Implementate

### User Management
-  Registrazione utenti con validazione completa
-  Aggiornamento profilo con validazione condizionale
-  Autorizzazione basata su ruoli
-  Messaggi di errore personalizzati

### Post Management
-  Creazione post con validazione
-  Aggiornamento post con autorizzazione
-  Validazione condizionale per contenuto
-  Controlli di permessi integrati

### Validation Features
-  Regole standard Laravel
-  Regole personalizzate
-  Validazione condizionale
-  Messaggi personalizzati
-  Autorizzazione integrata

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/form-request`
3. **Testa le funzionalità**:
   - Registra un nuovo utente
   - Aggiorna il profilo
   - Crea un post
   - Testa la validazione

## Configurazione

### Database
```bash
# Crea le tabelle
php artisan migrate

# Seed dei dati di esempio
php artisan db:seed
```

### Environment Variables
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

## Esempi di Utilizzo

### Creazione Utente
```php
// Nel controller
public function store(CreateUserRequest $request)
{
    $userData = $request->validated();
    $user = $this->userService->createUser($userData);
    return response()->json($user);
}
```

### Aggiornamento Profilo
```php
// Nel controller
public function update(UpdateUserRequest $request, User $user)
{
    $userData = $request->validated();
    $this->userService->updateUser($user, $userData);
    return response()->json($user);
}
```

### Creazione Post
```php
// Nel controller
public function store(CreatePostRequest $request)
{
    $postData = $request->validated();
    $post = $this->postService->createPost($postData);
    return response()->json($post);
}
```

## Form Request Examples

### CreateUserRequest
```php
public function rules()
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'role' => 'required|in:user,admin,moderator'
    ];
}

public function authorize()
{
    return $this->user()->can('create', User::class);
}
```

### UpdateUserRequest
```php
public function rules()
{
    return [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email,' . $this->user->id,
        'password' => 'sometimes|required|min:8|confirmed'
    ];
}

public function authorize()
{
    return $this->user()->can('update', $this->user);
}
```

## Testing

### Unit Tests
```bash
# Esegui i test
php artisan test

# Test specifici per Form Request
php artisan test --filter=FormRequestTest
```

### Test Examples
- Test validazione regole
- Test autorizzazione
- Test messaggi personalizzati
- Test validazione condizionale

## Best Practices

### Form Request Design
- Mantieni le Form Request focalizzate su una specifica operazione
- Usa regole condizionali per validazione dinamica
- Personalizza i messaggi di errore
- Implementa autorizzazione appropriata

### Performance
- Evita regole database costose quando possibile
- Usa caching per regole complesse
- Ottimizza le query di validazione
- Considera la validazione lato client

### Security
- Implementa autorizzazione appropriata
- Valida tutti gli input
- Usa regole di validazione sicure
- Sanitizza i dati quando necessario
