# Object Pool Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Pattern correlati](#pattern-correlati)
- [Esempi di uso reale](#esempi-di-uso-reale)

### Cosa Evitare
- [Anti-pattern](#anti-pattern)

### Implementazione Pratica
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

### Considerazioni Tecniche
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa

L'Object Pool Pattern mantiene una "piscina" di oggetti pronti all'uso, invece di crearli e distruggerli ogni volta. È come avere un parcheggio di auto: invece di comprare una nuova auto ogni volta che devi andare da qualche parte, prendi una delle auto già disponibili, la usi, e la rimetti al parcheggio per il prossimo.

## Perché ti serve

Immagina di dover creare 1000 connessioni di database per processare delle richieste. Senza Object Pool:
```php
// Creare e distruggere ogni volta - molto costoso!
for ($i = 0; $i < 1000; $i++) {
    $connection = new DatabaseConnection(); // Costoso!
    $connection->query("SELECT * FROM users");
    unset($connection); // Distruggi la connessione
}
```

Con Object Pool invece:
```php
// Crea la piscina una volta
$pool = new ConnectionPool(10); // 10 connessioni pre-create

// Usa le connessioni dalla piscina
for ($i = 0; $i < 1000; $i++) {
    $connection = $pool->acquire(); // Veloce!
    $connection->query("SELECT * FROM users");
    $pool->release($connection); // Rimetti nella piscina
}
```

Molto più efficiente!

## Come funziona

1. **Pool**: Una collezione di oggetti pre-creati e pronti all'uso
2. **Acquire**: Prendi un oggetto dalla piscina
3. **Use**: Usa l'oggetto per quello che devi fare
4. **Release**: Rimetti l'oggetto nella piscina per il prossimo uso
5. **Reset**: (Opzionale) Pulisci l'oggetto prima di rimetterlo nella piscina

Il pool mantiene gli oggetti vivi e li riutilizza, evitando il costo di creazione e distruzione.

## Schema visivo

```
Scenario 1 (oggetto disponibile):
Client → Pool::acquire()
         ↓
    Pool → Check available objects
         ↓
    Pool → Return existing object
         ↓
    Client → Use object
         ↓
    Client → Pool::release(object)
         ↓
    Pool → Reset and store object

Scenario 2 (oggetto non disponibile):
Client → Pool::acquire()
         ↓
    Pool → Check available objects
         ↓
    Pool → No objects available
         ↓
    Pool → Create new object (if under limit)
         ↓
    Pool → Return new object
```

*Il diagramma mostra come il pool gestisce gli oggetti disponibili e crea nuovi oggetti quando necessario.*

## Quando usarlo

Usa l'Object Pool Pattern quando:
- La creazione di un oggetto è costosa (database, file, network)
- Hai bisogno di molti oggetti temporanei
- Vuoi limitare il numero di oggetti attivi
- Gli oggetti sono pesanti in memoria
- Hai picchi di utilizzo seguiti da periodi di inattività
- Vuoi controllare le risorse del sistema
- Hai oggetti che richiedono inizializzazione complessa

**NON usarlo quando:**
- Gli oggetti sono semplici da creare
- Hai bisogno di oggetti con stato unico
- Il pool diventa troppo complesso da gestire
- Gli oggetti hanno dipendenze complesse
- Hai solo pochi oggetti da gestire

## Pro e contro

**I vantaggi:**
- Riduce il costo di creazione e distruzione
- Limita l'uso di memoria
- Migliora le performance per oggetti costosi
- Controlla il numero di oggetti attivi
- Riutilizzo efficiente delle risorse
- Gestione controllata delle risorse

**Gli svantaggi:**
- Aggiunge complessità al codice
- Può causare memory leak se non gestito bene
- Difficile da debuggare
- Può creare race conditions in ambienti multi-thread
- Gestione dello stato degli oggetti
- Può creare deadlock se non gestito correttamente

## Pattern correlati

- **Singleton**: Spesso usato insieme per gestire il pool come istanza unica
- **Factory Method**: Per creare nuovi oggetti quando il pool è vuoto
- **Prototype**: Per clonare oggetti esistenti nel pool
- **Flyweight**: Per condividere oggetti immutabili

## Esempi di uso reale

- **Laravel Database**: Il connection pool è gestito automaticamente da Laravel
- **Laravel Queue**: Pool di worker per processare job
- **Laravel Cache**: Pool di connessioni Redis/Memcached
- **Laravel Mail**: Pool di connessioni SMTP
- **HTTP Client Libraries**: Pool di connessioni HTTP per migliorare le performance
- **Game Development**: Pool di oggetti di gioco (proiettili, effetti, nemici)

## Anti-pattern

**Cosa NON fare:**
- **Pool infinito**: Non creare pool senza limiti di dimensione
- **Dimenticare il release**: Sempre rilasciare gli oggetti dopo l'uso
- **Pool per oggetti semplici**: Non usare pool per oggetti facili da creare
- **Stato condiviso**: Non condividere stato tra oggetti del pool
- **Pool senza reset**: Sempre pulire gli oggetti prima di rimetterli nel pool
- **Pool thread-unsafe**: In ambienti multi-thread, implementa la sincronizzazione

## Esempi di codice

### Esempio base
```php
<?php

class DatabaseConnection
{
    public function __construct()
    {
        // Simula una connessione costosa
        sleep(1); // 1 secondo per creare la connessione
    }

    public function query(string $sql): array
    {
        // Simula una query
        return ['result' => 'data'];
    }

    public function reset(): void
    {
        // Pulisce lo stato della connessione
        // In un caso reale, potresti chiudere transazioni, etc.
    }
}

class ConnectionPool
{
    private array $available = [];
    private array $inUse = [];
    private int $maxSize;

    public function __construct(int $maxSize = 10)
    {
        $this->maxSize = $maxSize;
    }

    public function acquire(): DatabaseConnection
    {
        if (!empty($this->available)) {
            $connection = array_pop($this->available);
            $this->inUse[] = $connection;
            return $connection;
        }

        if (count($this->inUse) < $this->maxSize) {
            $connection = new DatabaseConnection();
            $this->inUse[] = $connection;
            return $connection;
        }

        throw new Exception('Pool esaurito');
    }

    public function release(DatabaseConnection $connection): void
    {
        $key = array_search($connection, $this->inUse, true);
        if ($key !== false) {
            unset($this->inUse[$key]);
            $connection->reset();
            $this->available[] = $connection;
        }
    }

    public function getStats(): array
    {
        return [
            'available' => count($this->available),
            'in_use' => count($this->inUse),
            'total' => count($this->available) + count($this->inUse)
        ];
    }
}

// Uso
$pool = new ConnectionPool(5);

// Usa le connessioni
$connection1 = $pool->acquire();
$result1 = $connection1->query("SELECT * FROM users");
$pool->release($connection1);

$connection2 = $pool->acquire();
$result2 = $connection2->query("SELECT * FROM posts");
$pool->release($connection2);
```

### Esempio per Laravel
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseConnectionPool
{
    private array $available = [];
    private array $inUse = [];
    private int $maxSize;
    private string $connectionName;

    public function __construct(string $connectionName = 'mysql', int $maxSize = 10)
    {
        $this->connectionName = $connectionName;
        $this->maxSize = $maxSize;
    }

    public function acquire(): \PDO
    {
        if (!empty($this->available)) {
            $connection = array_pop($this->available);
            $this->inUse[] = $connection;
            return $connection;
        }

        if (count($this->inUse) < $this->maxSize) {
            $connection = $this->createConnection();
            $this->inUse[] = $connection;
            return $connection;
        }

        throw new \Exception('Connection pool esaurito');
    }

    public function release(\PDO $connection): void
    {
        $key = array_search($connection, $this->inUse, true);
        if ($key !== false) {
            unset($this->inUse[$key]);
            $this->resetConnection($connection);
            $this->available[] = $connection;
        }
    }

    private function createConnection(): \PDO
    {
        $config = config("database.connections.{$this->connectionName}");
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        
        return new \PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
    }

    private function resetConnection(\PDO $connection): void
    {
        // Chiudi eventuali transazioni aperte
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        
        // Reset di eventuali prepared statements
        $connection->exec("SET SESSION sql_mode = ''");
    }

    public function getStats(): array
    {
        return [
            'available' => count($this->available),
            'in_use' => count($this->inUse),
            'total' => count($this->available) + count($this->inUse),
            'max_size' => $this->maxSize
        ];
    }
}

// Uso nel Service
class DataProcessingService
{
    private DatabaseConnectionPool $pool;

    public function __construct()
    {
        $this->pool = new DatabaseConnectionPool('mysql', 5);
    }

    public function processUsers(array $userIds): array
    {
        $results = [];
        
        foreach ($userIds as $userId) {
            $connection = $this->pool->acquire();
            
            try {
                $stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $results[] = $stmt->fetch();
            } finally {
                $this->pool->release($connection);
            }
        }
        
        return $results;
    }
}
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Connection Pool System](../../../esempi-completi/07-connection-pool-system/)** - Sistema di gestione connessioni con Object Pool Pattern

L'esempio include:
- Pool di connessioni database
- Gestione automatica del ciclo di vita
- Monitoraggio delle performance
- Integrazione con Laravel
- Test completi con Pest
- API RESTful per monitorare il pool
- Gestione degli errori e recovery

## Performance e considerazioni

- **Impatto memoria**: Può essere alto se mantieni molti oggetti nel pool
- **Impatto CPU**: Basso, evita la creazione costante di oggetti
- **Scalabilità**: Ottimo per gestire picchi di utilizzo
- **Colli di bottiglia**: Attenzione ai limiti del pool e ai deadlock

## Risorse utili

- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale
- [Refactoring.Guru](https://refactoring.guru/design-patterns/object-pool) - Spiegazioni visuali
- [Laravel Documentation](https://laravel.com/docs) - Framework specifico
- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php) - Gestione connessioni database
