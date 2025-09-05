# Unit of Work Pattern - Esempio Completo

## Descrizione

Questo esempio dimostra l'implementazione del pattern Unit of Work in Laravel attraverso un sistema e-commerce che gestisce transazioni atomiche per ordini, prodotti e inventario.

## Caratteristiche

- **Unit of Work Pattern**: Gestione transazioni atomiche
- **Transaction Management**: Coordinamento delle operazioni
- **Concurrency Control**: Gestione dei conflitti di accesso
- **Rollback Support**: Annullamento automatico delle operazioni
- **Batch Operations**: Ottimizzazione delle operazioni batch
- **Testing**: Test per le transazioni

## Struttura del Progetto

```
app/
├── UnitOfWork/
│   ├── UnitOfWorkInterface.php
│   ├── UnitOfWork.php
│   ├── EntityTracker.php
│   └── TransactionManager.php
├── Services/
│   ├── OrderService.php
│   ├── ProductService.php
│   └── InventoryService.php
├── Repositories/
│   ├── OrderRepository.php
│   ├── ProductRepository.php
│   └── InventoryRepository.php
├── Models/
│   ├── Order.php
│   ├── Product.php
│   └── Inventory.php
└── Http/Controllers/
    ├── OrderController.php
    └── ProductController.php
```

## Installazione

1. **Clona il repository**:
   ```bash
   git clone [repository-url]
   cd unit-of-work-ecommerce-example
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
   - Esplora le funzionalità dell'e-commerce

## Funzionalità Implementate

### Unit of Work Pattern
- **UnitOfWork**: Gestione delle transazioni atomiche
- **EntityTracker**: Tracciamento delle entità modificate
- **TransactionManager**: Coordinamento delle operazioni
- **Rollback Support**: Annullamento automatico

### Operazioni Atomiche
- **Order Creation**: Creazione ordini con aggiornamento inventario
- **Product Updates**: Aggiornamento prodotti con controllo stock
- **Inventory Management**: Gestione inventario con transazioni
- **Batch Operations**: Operazioni batch ottimizzate

### Concurrency Control
- **Lock Management**: Gestione dei lock per concorrenza
- **Conflict Resolution**: Risoluzione dei conflitti
- **Deadlock Prevention**: Prevenzione dei deadlock
- **Retry Logic**: Logica di retry per operazioni fallite

## Pattern Unit of Work in Azione

### 1. Unit of Work Interface
```php
interface UnitOfWorkInterface
{
    public function begin(): void;
    public function commit(): void;
    public function rollback(): void;
    public function registerNew($entity): void;
    public function registerDirty($entity): void;
    public function registerDeleted($entity): void;
    public function registerClean($entity): void;
    public function isInTransaction(): bool;
}
```

### 2. Unit of Work Implementation
```php
class UnitOfWork implements UnitOfWorkInterface
{
    private array $newEntities = [];
    private array $dirtyEntities = [];
    private array $deletedEntities = [];
    private array $cleanEntities = [];
    private bool $inTransaction = false;

    public function begin(): void
    {
        if ($this->inTransaction) {
            throw new \Exception('Transaction already started');
        }
        
        $this->inTransaction = true;
        DB::beginTransaction();
    }

    public function commit(): void
    {
        if (!$this->inTransaction) {
            throw new \Exception('No active transaction');
        }
        
        try {
            $this->executeInserts();
            $this->executeUpdates();
            $this->executeDeletes();
            
            DB::commit();
            $this->clear();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function rollback(): void
    {
        if ($this->inTransaction) {
            DB::rollback();
            $this->clear();
        }
    }

    public function registerNew($entity): void
    {
        $this->newEntities[] = $entity;
    }

    public function registerDirty($entity): void
    {
        $this->dirtyEntities[] = $entity;
    }

    public function registerDeleted($entity): void
    {
        $this->deletedEntities[] = $entity;
    }

    public function registerClean($entity): void
    {
        $this->cleanEntities[] = $entity;
    }
}
```

### 3. Service che usa Unit of Work
```php
class OrderService
{
    public function __construct(
        private UnitOfWorkInterface $unitOfWork,
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository,
        private InventoryRepository $inventoryRepository
    ) {}

    public function createOrder(array $orderData, array $products): Order
    {
        $this->unitOfWork->begin();
        
        try {
            // Crea l'ordine
            $order = new Order($orderData);
            $this->unitOfWork->registerNew($order);
            
            // Aggiorna i prodotti e l'inventario
            foreach ($products as $productData) {
                $product = $this->productRepository->findById($productData['id']);
                $product->decreaseStock($productData['quantity']);
                $this->unitOfWork->registerDirty($product);
                
                // Aggiorna inventario
                $inventory = $this->inventoryRepository->findByProductId($product->id);
                $inventory->decreaseQuantity($productData['quantity']);
                $this->unitOfWork->registerDirty($inventory);
            }
            
            // Conferma tutto
            $this->unitOfWork->commit();
            
            return $order;
        } catch (\Exception $e) {
            $this->unitOfWork->rollback();
            throw $e;
        }
    }
}
```

### 4. Controller che usa Unit of Work
```php
class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder(
                $request->input('order'),
                $request->input('products')
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Ordine creato con successo!',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'ordine',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

## Vantaggi del Pattern Unit of Work

1. **Atomicità**: Tutte le operazioni o nessuna
2. **Consistenza**: I dati rimangono sempre validi
3. **Isolamento**: Operazioni non interferiscono
4. **Durabilità**: Modifiche permanenti quando confermate
5. **Rollback**: Possibilità di annullare tutto
6. **Performance**: Ottimizzazione batch
7. **Concorrenza**: Gestione conflitti

## Best Practices Implementate

- **Transaction Scope**: Scope appropriato per le transazioni
- **Error Handling**: Gestione errori centralizzata
- **Rollback Strategy**: Strategia di rollback automatico
- **Concurrency Control**: Controllo della concorrenza
- **Performance**: Ottimizzazione delle operazioni
- **Testing**: Test per le transazioni

## Testing

Per testare l'implementazione:

1. **Test Unit of Work**:
   ```bash
   php artisan test --filter=UnitOfWorkTest
   ```

2. **Test Service**:
   ```bash
   php artisan test --filter=OrderServiceTest
   ```

3. **Test Transaction**:
   ```bash
   php artisan test --filter=TransactionTest
   ```

## Estensioni Possibili

- **Distributed Transactions**: Transazioni distribuite
- **Event Sourcing**: Event sourcing per audit
- **CQRS**: Command Query Responsibility Segregation
- **Saga Pattern**: Pattern Saga per operazioni complesse
- **Compensation**: Compensazione per operazioni fallite

## Conclusione

Questo esempio dimostra come il pattern Unit of Work gestisce le transazioni atomiche in modo coordinato. La gestione delle transazioni e il rollback automatico rendono l'applicazione più robusta e consistente.

Il pattern Unit of Work è particolarmente utile per applicazioni complesse che richiedono operazioni atomiche e gestione della concorrenza.
