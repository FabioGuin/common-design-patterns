# Singleton Pattern
*(Categoria: Creazionale)*

## Indice
- [Abstract](#abstract)
- [Contesto e Motivazione](#contesto-e-motivazione)
- [Soluzione proposta](#soluzione-proposta)
- [Quando usarlo](#quando-usarlo)
- [Vantaggi e Svantaggi](#vantaggi-e-svantaggi)
- [Esempi pratici](#esempi-pratici)
  - [Esempio concettuale](#esempio-concettuale)
  - [Esempio Laravel](#esempio-laravel)
- [Esempi Completi](#esempi-completi)

## Abstract
Il Singleton Pattern garantisce che una classe abbia una sola istanza e fornisce un punto di accesso globale a questa istanza. È particolarmente utile per gestire risorse condivise come connessioni al database, configurazioni dell'applicazione o servizi di logging.

## Contesto e Motivazione
- **Contesto tipico**: Quando hai bisogno di una sola istanza di una classe per tutta l'applicazione, come una connessione al database o un logger
- **Sintomi di un design non ottimale**: 
  - Creazione di multiple istanze della stessa risorsa
  - Inconsistenza nei dati condivisi
  - Spreco di memoria e risorse
  - Difficoltà nel coordinare l'accesso a risorse globali
- **Perché le soluzioni semplici non sono ideali**: Creare variabili globali o istanze statiche può portare a problemi di testabilità, accoppiamento forte e violazione dei principi SOLID.

## Soluzione proposta
- **Idea chiave**: La classe stessa controlla la creazione della propria istanza e impedisce la creazione di istanze multiple
- **Struttura concettuale**: 
  - Costruttore privato per impedire istanziazione diretta
  - Metodo statico per ottenere l'istanza
  - Variabile statica per memorizzare l'istanza unica
- **Ruolo dei partecipanti**:
  - **Singleton**: La classe che implementa il pattern
  - **Client**: Le classi che utilizzano l'istanza singleton

## Quando usarlo
- **Casi d'uso ideali**:
  - Gestione connessioni al database
  - Servizi di logging
  - Configurazioni globali dell'applicazione
  - Cache manager
  - Service container (come in Laravel)
- **Indicatori che suggeriscono l'adozione**:
  - Necessità di una sola istanza per tutta l'applicazione
  - Risorse costose da inizializzare
  - Coordinamento di accesso a risorse condivise
- **Situazioni in cui NON è consigliato**:
  - Quando hai bisogno di multiple istanze
  - In applicazioni multi-threaded senza sincronizzazione
  - Quando rende il codice difficile da testare
  - Per oggetti che cambiano stato frequentemente

## Vantaggi e Svantaggi
**Vantaggi**
- Garantisce una sola istanza dell'oggetto
- Fornisce accesso globale controllato
- Lazy initialization (creazione on-demand)
- Risparmio di memoria e risorse
- Facilita la gestione di risorse condivise

**Svantaggi**
- Può nascondere dipendenze tra classi
- Difficile da testare (stato globale)
- Viola il Single Responsibility Principle
- Può creare problemi in ambienti multi-threaded
- Accoppiamento forte con l'istanza globale

## Esempi pratici

### Esempio concettuale
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

### Esempio Laravel
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

// Utilizzo in Laravel
$logger = LoggerService::getInstance();
$logger->log('User logged in');
```

## Esempi Completi

Per implementazioni complete e funzionanti del Singleton Pattern in Laravel, consulta:

- **[Esempio Completo: Singleton Logger](../../../esempi-completi/01-singleton-logger/)** - Sistema di logging completo con persistenza su file, integrazione Service Container e API RESTful

L'esempio completo include:
- Logger service singleton completo
- Persistenza logs su file
- Integrazione con Laravel Service Container
- Controller e routes per testing
- Service Provider personalizzato
- Gestione livelli di log (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- API per consultazione e gestione logs
