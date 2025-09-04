# Singleton Pattern

## Cosa fa
Il Singleton ti assicura che una classe abbia sempre e solo una istanza. Quando chiami il metodo per ottenere l'istanza, ricevi sempre la stessa, anche se la chiami da parti diverse del codice.

È perfetto per cose come connessioni al database, configurazioni dell'app o servizi di logging che devono essere condivisi in tutta l'applicazione.

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

## Perché ti serve
Immagina di avere un logger che deve scrivere su file. Se crei una nuova istanza del logger ogni volta che ne hai bisogno, finirai con:
- File di log sparsi ovunque
- Perdita di messaggi
- Spreco di memoria
- Confusione totale

Il Singleton risolve questo problema: una sola istanza, un solo posto dove scrivere, tutto sotto controllo.

## Come funziona
Il trucco è semplice:
1. Il costruttore è privato, così nessuno può fare `new Singleton()`
2. C'è un metodo statico che ti restituisce l'istanza
3. La prima volta che chiami il metodo, crea l'istanza
4. Le volte successive, ti restituisce sempre la stessa


## Quando usarlo
Usa il Singleton quando:
- Hai bisogno di una sola istanza per tutta l'app (database, logger, cache)
- L'oggetto è costoso da creare e vuoi riutilizzarlo
- Devi coordinare l'accesso a una risorsa condivisa

**NON usarlo quando:**
- Hai bisogno di più istanze della stessa classe
- Lavori con applicazioni multi-threaded (può creare problemi)
- Rende il codice difficile da testare
- L'oggetto cambia stato troppo spesso

## Pro e contro
**I vantaggi:**
- Una sola istanza garantita
- Accesso controllato da qualsiasi parte del codice
- Si crea solo quando serve (lazy loading)
- Risparmi memoria e risorse
- Perfetto per risorse condivise

**Gli svantaggi:**
- Nasconde le dipendenze (difficile capire da dove viene l'oggetto)
- Difficile da testare (stato globale)
- Viola il principio di responsabilità singola
- Problemi con applicazioni multi-threaded
- Crea accoppiamento forte

## Esempi di codice

### Esempio base
```php
<?php

class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    private string $connectionString;

    // Costruttore privato per impedire istanziazione diretta
    private function __construct(string $connectionString)
    {
        $this->connectionString = $connectionString;
    }

    // Metodo per ottenere l'istanza singleton
    public static function getInstance(string $connectionString = null): DatabaseConnection
    {
        if (self::$instance === null) {
            if ($connectionString === null) {
                throw new InvalidArgumentException("Connection string required for first initialization");
            }
            self::$instance = new self($connectionString);
        }
        return self::$instance;
    }

    // Impedisce la clonazione e deserializzazione
    private function __clone() {}
    public function __wakeup() { throw new Exception("Cannot unserialize singleton"); }
}

// Utilizzo
$db1 = DatabaseConnection::getInstance("mysql://localhost:3306/mydb");
$db2 = DatabaseConnection::getInstance(); // Restituisce la stessa istanza
var_dump($db1 === $db2); // true
```

### Esempio per Laravel
```php
<?php

namespace App\Services;

class LoggerService
{
    private static ?LoggerService $instance = null;
    private array $logs = [];

    private function __construct() {}

    public static function getInstance(): LoggerService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function log(string $message): void
    {
        $this->logs[] = $message;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    private function __clone() {}
    public function __wakeup() { throw new \Exception("Cannot unserialize singleton"); }
}

// Come usarlo in Laravel
$logger = LoggerService::getInstance();
$logger->log('User logged in');
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Logger Singleton Completo](../../../esempi-completi/01-singleton-logger/)** - Un sistema di logging completo con tutto quello che ti serve

L'esempio include:
- Logger service singleton funzionante
- Salvataggio dei log su file
- Integrazione con il Service Container di Laravel
- Controller e routes per testare
- Service Provider personalizzato
- Livelli di log (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- API per leggere e gestire i log
