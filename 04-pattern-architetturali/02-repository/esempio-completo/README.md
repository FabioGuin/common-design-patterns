# Repository Pattern - Esempio Completo

## Descrizione

Questo esempio dimostra l'implementazione del pattern Repository in Laravel attraverso un sistema blog che separa l'accesso ai dati dalla logica di business.

## Caratteristiche

- **Repository Interface**: Contratti per l'accesso ai dati
- **Eloquent Implementation**: Implementazione con Eloquent ORM
- **Service Layer**: Logica di business separata
- **Dependency Injection**: Risoluzione automatica delle dipendenze
- **Testing**: Test unitari con mock dei repository
- **Caching**: Implementazione di caching per performance

## Struttura del Progetto

```
app/
├── Repositories/
│   ├── Interfaces/
│   │   ├── ArticleRepositoryInterface.php
│   │   └── UserRepositoryInterface.php
│   ├── Eloquent/
│   │   ├── EloquentArticleRepository.php
│   │   └── EloquentUserRepository.php
│   └── Cached/
│       ├── CachedArticleRepository.php
│       └── CachedUserRepository.php
├── Services/
│   ├── ArticleService.php
│   └── UserService.php
├── Http/Controllers/
│   ├── ArticleController.php
│   └── UserController.php
├── Models/
│   ├── Article.php
│   └── User.php
└── Providers/
    └── RepositoryServiceProvider.php
```

## Installazione

1. **Clona il repository**:
   ```bash
   git clone [repository-url]
   cd repository-blog-example
   ```

2. **Installa le dipendenze**:
   ```bash
   composer install
   ```

3. **Configura l'ambiente**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configura il database**:
   - Crea un database MySQL
   - Aggiorna le credenziali in `.env`
   - Esegui le migrazioni:
     ```bash
     php artisan migrate
     ```

5. **Avvia il server**:
   ```bash
   php artisan serve
   ```

6. **Visita l'applicazione**:
   - Apri `http://localhost:8000` nel browser
   - Esplora le funzionalità del blog

## Funzionalità Implementate

### Repository Layer
- **ArticleRepository**: Gestione articoli con query complesse
- **UserRepository**: Gestione utenti con filtri avanzati
- **Caching**: Implementazione di caching per performance
- **Interfaces**: Contratti chiari per l'accesso ai dati

### Service Layer
- **ArticleService**: Logica di business per articoli
- **UserService**: Logica di business per utenti
- **Validation**: Validazione centralizzata
- **Business Rules**: Regole di business separate

### Controller Layer
- **Thin Controllers**: Controller leggeri che delegano ai service
- **Dependency Injection**: Risoluzione automatica delle dipendenze
- **Error Handling**: Gestione errori centralizzata

## Pattern Repository in Azione

### 1. Interfaccia Repository
```php
interface ArticleRepositoryInterface
{
    public function findAll(): Collection;
    public function findById(int $id): ?Article;
    public function findByAuthor(int $authorId): Collection;
    public function findPublished(): Collection;
    public function search(string $term): Collection;
    public function create(array $data): Article;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
```

### 2. Implementazione Eloquent
```php
class EloquentArticleRepository implements ArticleRepositoryInterface
{
    public function findAll(): Collection
    {
        return Article::with('user')->get();
    }
    
    public function findById(int $id): ?Article
    {
        return Article::with('user')->find($id);
    }
    
    public function findByAuthor(int $authorId): Collection
    {
        return Article::where('user_id', $authorId)
                     ->with('user')
                     ->get();
    }
    
    public function findPublished(): Collection
    {
        return Article::where('status', 'published')
                     ->whereNotNull('published_at')
                     ->with('user')
                     ->orderBy('published_at', 'desc')
                     ->get();
    }
    
    public function search(string $term): Collection
    {
        return Article::where(function ($query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%");
        })->with('user')->get();
    }
    
    public function create(array $data): Article
    {
        return Article::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        $article = $this->findById($id);
        if (!$article) {
            return false;
        }
        
        return $article->update($data);
    }
    
    public function delete(int $id): bool
    {
        $article = $this->findById($id);
        if (!$article) {
            return false;
        }
        
        return $article->delete();
    }
}
```

### 3. Service Layer
```php
class ArticleService
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository
    ) {}
    
    public function getAllArticles(): Collection
    {
        return $this->articleRepository->findAll();
    }
    
    public function getPublishedArticles(): Collection
    {
        return $this->articleRepository->findPublished();
    }
    
    public function getArticleById(int $id): ?Article
    {
        return $this->articleRepository->findById($id);
    }
    
    public function createArticle(array $data): Article
    {
        // Logica di business
        $data['slug'] = Str::slug($data['title']);
        $data['excerpt'] = $this->generateExcerpt($data['content']);
        
        return $this->articleRepository->create($data);
    }
    
    public function searchArticles(string $term): Collection
    {
        if (empty($term)) {
            return collect();
        }
        
        return $this->articleRepository->search($term);
    }
    
    private function generateExcerpt(string $content): string
    {
        return Str::limit(strip_tags($content), 150);
    }
}
```

### 4. Controller
```php
class ArticleController extends Controller
{
    public function __construct(
        private ArticleService $articleService
    ) {}
    
    public function index()
    {
        $articles = $this->articleService->getPublishedArticles();
        return view('articles.index', compact('articles'));
    }
    
    public function show(int $id)
    {
        $article = $this->articleService->getArticleById($id);
        if (!$article) {
            abort(404);
        }
        
        return view('articles.show', compact('article'));
    }
    
    public function search(Request $request)
    {
        $term = $request->get('q');
        $articles = $this->articleService->searchArticles($term);
        
        return view('articles.search', compact('articles', 'term'));
    }
}
```

## Vantaggi del Pattern Repository

1. **Separazione delle Responsabilità**: Logica di business separata dall'accesso ai dati
2. **Testabilità**: Facile mockare i repository per i test
3. **Riusabilità**: Stessa logica in più punti dell'applicazione
4. **Manutenibilità**: Modifiche centralizzate all'accesso ai dati
5. **Flessibilità**: Cambiare implementazione senza toccare il business logic

## Best Practices Implementate

- **Interface Segregation**: Interfacce specifiche per ogni entità
- **Dependency Inversion**: Dipendenza da astrazioni, non da implementazioni
- **Single Responsibility**: Ogni repository ha una responsabilità specifica
- **Open/Closed**: Facile estendere con nuove implementazioni
- **Liskov Substitution**: Implementazioni intercambiabili

## Testing

Per testare l'implementazione:

1. **Test Repository**:
   ```bash
   php artisan test --filter=ArticleRepositoryTest
   ```

2. **Test Service**:
   ```bash
   php artisan test --filter=ArticleServiceTest
   ```

3. **Test Controller**:
   ```bash
   php artisan test --filter=ArticleControllerTest
   ```

## Estensioni Possibili

- **Caching Layer**: Implementare caching per performance
- **API Repository**: Repository per API esterne
- **File Repository**: Repository per file system
- **Memory Repository**: Repository in memoria per test
- **Event Sourcing**: Repository per event sourcing

## Conclusione

Questo esempio dimostra come il pattern Repository organizza l'accesso ai dati in modo pulito e testabile. La separazione tra logica di business e accesso ai dati rende l'applicazione più manutenibile e flessibile.

Il pattern Repository è particolarmente utile per applicazioni complesse che richiedono testabilità e manutenibilità.
