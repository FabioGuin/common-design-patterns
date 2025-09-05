# DTO Pattern - Esempio Completo

## Descrizione

Questo esempio dimostra l'implementazione del pattern DTO (Data Transfer Object) in Laravel attraverso un sistema blog che struttura e valida i dati durante il trasferimento tra i layer dell'applicazione.

## Caratteristiche

- **DTO Pattern**: Strutturazione e validazione dei dati
- **Data Validation**: Validazione centralizzata nei DTO
- **Type Safety**: Tipizzazione forte per i dati
- **API Contracts**: Contratti definiti per le API
- **Data Transformation**: Trasformazione dati tra layer
- **Testing**: Test unitari per i DTO

## Struttura del Progetto

```
app/
├── DTOs/
│   ├── Article/
│   │   ├── CreateArticleDTO.php
│   │   ├── UpdateArticleDTO.php
│   │   ├── ArticleResponseDTO.php
│   │   └── ArticleListDTO.php
│   ├── User/
│   │   ├── CreateUserDTO.php
│   │   ├── UpdateUserDTO.php
│   │   ├── UserResponseDTO.php
│   │   └── UserListDTO.php
│   └── Base/
│       ├── BaseDTO.php
│       └── ValidationTrait.php
├── Http/Controllers/
│   ├── ArticleController.php
│   └── UserController.php
├── Services/
│   ├── ArticleService.php
│   └── UserService.php
└── Models/
    ├── Article.php
    └── User.php
```

## Installazione

1. **Clona il repository**:
   ```bash
   git clone [repository-url]
   cd dto-blog-example
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

### DTO Pattern
- **CreateArticleDTO**: DTO per creazione articoli
- **UpdateArticleDTO**: DTO per aggiornamento articoli
- **ArticleResponseDTO**: DTO per risposta articoli
- **CreateUserDTO**: DTO per creazione utenti
- **UpdateUserDTO**: DTO per aggiornamento utenti
- **UserResponseDTO**: DTO per risposta utenti

### Validazione Centralizzata
- **BaseDTO**: Classe base per tutti i DTO
- **ValidationTrait**: Trait per validazione comune
- **Custom Validation**: Regole di validazione personalizzate

### Trasformazione Dati
- **Data Mapping**: Mappatura tra DTO e modelli
- **Response Transformation**: Trasformazione per le risposte
- **API Serialization**: Serializzazione per le API

## Pattern DTO in Azione

### 1. DTO per Creazione Articolo
```php
class CreateArticleDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly int $userId,
        public readonly ?string $excerpt = null,
        public readonly string $status = 'draft'
    ) {
        $this->validate();
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:50',
            'userId' => 'required|integer|exists:users,id',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published'
        ];
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => $this->userId,
            'excerpt' => $this->excerpt,
            'status' => $this->status
        ];
    }
}
```

### 2. DTO per Risposta Articolo
```php
class ArticleResponseDTO extends BaseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        public readonly string $excerpt,
        public readonly string $status,
        public readonly string $authorName,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    public static function fromModel(Article $article): self
    {
        return new self(
            id: $article->id,
            title: $article->title,
            content: $article->content,
            excerpt: $article->excerpt,
            status: $article->status,
            authorName: $article->user->name,
            createdAt: $article->created_at->format('Y-m-d H:i:s'),
            updatedAt: $article->updated_at->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'author' => [
                'name' => $this->authorName
            ],
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
```

### 3. Service che usa DTO
```php
class ArticleService
{
    public function createArticle(CreateArticleDTO $dto): ArticleResponseDTO
    {
        $article = Article::create($dto->toArray());
        
        return ArticleResponseDTO::fromModel($article);
    }

    public function updateArticle(int $id, UpdateArticleDTO $dto): ArticleResponseDTO
    {
        $article = Article::findOrFail($id);
        $article->update($dto->toArray());
        
        return ArticleResponseDTO::fromModel($article->fresh());
    }

    public function getArticle(int $id): ArticleResponseDTO
    {
        $article = Article::with('user')->findOrFail($id);
        
        return ArticleResponseDTO::fromModel($article);
    }
}
```

### 4. Controller che usa DTO
```php
class ArticleController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $dto = new CreateArticleDTO(
                title: $request->input('title'),
                content: $request->input('content'),
                userId: $request->input('user_id'),
                excerpt: $request->input('excerpt'),
                status: $request->input('status', 'draft')
            );

            $responseDto = $this->articleService->createArticle($dto);

            return response()->json([
                'success' => true,
                'data' => $responseDto->toArray()
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }
}
```

## Vantaggi del Pattern DTO

1. **Struttura Chiara**: Dati organizzati e tipizzati
2. **Validazione Centralizzata**: Controllo in un punto
3. **Documentazione**: Struttura auto-documentata
4. **Testabilità**: Facile creare dati di test
5. **Sicurezza**: Controllo su cosa viene trasferito
6. **Versioning**: Gestione delle versioni dei dati
7. **Performance**: Trasferimento ottimizzato

## Best Practices Implementate

- **Immutable DTO**: DTO immutabili per sicurezza
- **Type Safety**: Tipizzazione forte per i dati
- **Validation**: Validazione centralizzata
- **Documentation**: Struttura auto-documentata
- **Testing**: Test per ogni DTO
- **Error Handling**: Gestione errori centralizzata

## Testing

Per testare l'implementazione:

1. **Test DTO**:
   ```bash
   php artisan test --filter=CreateArticleDTOTest
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

- **DTO Mapper**: Mapper automatico per conversioni
- **DTO Factory**: Factory per creazione DTO
- **DTO Caching**: Caching per DTO complessi
- **DTO Versioning**: Gestione versioni DTO
- **DTO Serialization**: Serializzazione avanzata

## Conclusione

Questo esempio dimostra come il pattern DTO struttura e valida i dati durante il trasferimento tra i layer dell'applicazione. La centralizzazione della validazione e la tipizzazione forte rendono l'applicazione più robusta e manutenibile.

Il pattern DTO è particolarmente utile per applicazioni complesse che richiedono trasferimento di dati strutturato e validato tra diversi componenti.
