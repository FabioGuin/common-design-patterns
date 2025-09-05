# Proxy Pattern

## Cosa fa

Il Proxy Pattern fornisce un placeholder o un sostituto per un altro oggetto per controllare l'accesso ad esso. In pratica, crei un "intermediario" che si mette tra il client e l'oggetto reale, permettendoti di aggiungere funzionalità come caching, logging, controllo di accesso, lazy loading o validazione senza modificare l'oggetto originale.

## Perché ti serve

Immagina di avere un servizio che fa chiamate costose a un'API esterna. Ogni volta che qualcuno chiede i dati, fai la chiamata. Con il Proxy, puoi:
- **Cachare** i risultati per evitare chiamate ripetute
- **Controllare l'accesso** prima di permettere l'operazione
- **Fare lazy loading** - caricare i dati solo quando servono davvero
- **Aggiungere logging** per tracciare chi fa cosa
- **Validare** i parametri prima di passare la richiesta

È come avere un segretario che filtra le chiamate e gestisce le richieste per te.

## Come funziona

Il pattern ha tre componenti principali:

1. **Subject (Interfaccia)**: Definisce le operazioni comuni tra il Proxy e l'oggetto reale
2. **RealSubject (Oggetto Reale)**: L'oggetto che fa il lavoro vero
3. **Proxy**: L'intermediario che controlla l'accesso e può aggiungere funzionalità

Il Proxy implementa la stessa interfaccia del RealSubject, così il client non sa se sta parlando con il proxy o con l'oggetto reale.

## Schema visivo

```
Client
  ↓
Subject (Interface)
  ↑
Proxy ←→ RealSubject
```

Il Client chiama il Proxy, che può:
- Fare controlli
- Aggiungere funzionalità (cache, logging, etc.)
- Chiamare il RealSubject quando necessario
- Restituire il risultato al Client

## Quando usarlo

- **Lazy Loading**: Caricare oggetti pesanti solo quando servono
- **Caching**: Memorizzare risultati di operazioni costose
- **Controllo di accesso**: Verificare permessi prima di eseguire operazioni
- **Logging e monitoring**: Tracciare l'uso di risorse
- **Validazione**: Controllare parametri prima di passare la richiesta
- **Remote Proxy**: Gestire oggetti su server remoti
- **Virtual Proxy**: Creare oggetti costosi solo quando necessari

## Pro e contro

### Pro
- **Controllo granulare** sull'accesso agli oggetti
- **Aggiunta trasparente** di funzionalità senza modificare il codice esistente
- **Lazy loading** per migliorare le performance
- **Caching** per ridurre operazioni costose
- **Logging e monitoring** integrati

### Contro
- **Complessità aggiuntiva** - più classi da gestire
- **Overhead** - ogni chiamata passa attraverso il proxy
- **Debugging più difficile** - devi tracciare attraverso il proxy
- **Possibili problemi di performance** se il proxy non è ottimizzato

## Esempi di codice

### Pseudocodice
```
// Interfaccia base
interface ImageInterface {
    display()
}

// Oggetto reale
class RealImage implements ImageInterface {
    private filename
    
    constructor(filename) {
        this.filename = filename
        this.loadFromDisk() // Operazione costosa
    }
    
    display() {
        return "Displaying image: " + this.filename
    }
    
    private loadFromDisk() {
        // Simula caricamento da disco
        sleep(1)
    }
}

// Proxy con lazy loading
class ImageProxy implements ImageInterface {
    private realImage = null
    private filename
    
    constructor(filename) {
        this.filename = filename
        // Non carica l'immagine subito!
    }
    
    display() {
        if (this.realImage == null) {
            this.realImage = new RealImage(this.filename)
        }
        
        return this.realImage.display()
    }
}

// Utilizzo
// Senza proxy - carica subito
image1 = new RealImage('photo1.jpg') // 1 secondo di attesa

// Con proxy - carica solo quando serve
image2 = new ImageProxy('photo2.jpg') // istantaneo
image2.display() // Ora carica e mostra
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Proxy per caching di dati API
- Controllo di accesso integrato
- Lazy loading di risorse costose
- Logging delle operazioni

## Correlati

- **Decorator Pattern**: Anche aggiunge funzionalità, ma il Decorator modifica il comportamento, il Proxy controlla l'accesso
- **Facade Pattern**: Semplifica l'interfaccia, ma il Proxy mantiene la stessa interfaccia
- **Adapter Pattern**: Cambia l'interfaccia, il Proxy la mantiene uguale

## Esempi di uso reale

- **Laravel Eloquent**: Usa proxy per lazy loading delle relazioni
- **Laravel Cache**: Il sistema di cache è un proxy per i dati
- **API Gateway**: Controlla l'accesso alle API
- **ORM Proxy**: Carica oggetti dal database solo quando servono
- **File System Proxy**: Controlla l'accesso ai file
- **Database Connection Pooling**: Gestisce le connessioni al database

## Anti-pattern

❌ **Proxy che fa troppo**: Un proxy che gestisce troppe responsabilità diverse
```php
// SBAGLIATO
class GodProxy implements ImageInterface
{
    public function display(): string
    {
        $this->logAccess();
        $this->checkPermissions();
        $this->validateInput();
        $this->cacheResult();
        $this->sendMetrics();
        $this->updateDatabase();
        // ... troppe responsabilità!
    }
}
```

✅ **Proxy focalizzato**: Un proxy per ogni responsabilità specifica
```php
// GIUSTO
class CachingImageProxy implements ImageInterface
{
    public function display(): string
    {
        return $this->cache->remember('image_' . $this->filename, function() {
            return $this->realImage->display();
        });
    }
}
```

## Troubleshooting

**Problema**: Il proxy non funziona come previsto
**Soluzione**: Verifica che implementi la stessa interfaccia dell'oggetto reale

**Problema**: Performance peggiorate
**Soluzione**: Controlla che il proxy non aggiunga overhead inutile

**Problema**: Cache non funziona
**Soluzione**: Assicurati che il proxy gestisca correttamente la cache

## Performance e considerazioni

- **Lazy Loading**: Migliora le performance iniziali
- **Caching**: Riduce operazioni costose ma usa memoria
- **Overhead**: Ogni chiamata passa attraverso il proxy
- **Memory**: I proxy possono accumulare riferimenti agli oggetti reali

## Risorse utili

- [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Laravel Cache](https://laravel.com/docs/cache)
- [Proxy Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/proxy)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
