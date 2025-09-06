# Esempio Completo: Proxy Pattern

Questo esempio dimostra l'implementazione del **Proxy Pattern** in Laravel per gestire l'accesso a servizi esterni con caching, controllo di accesso e lazy loading.

## Funzionalità implementate

- **Caching Proxy**: Cache automatica per le chiamate API
- **Access Control Proxy**: Controllo dei permessi utente
- **Lazy Loading Proxy**: Caricamento on-demand delle risorse
- **Logging Proxy**: Tracciamento delle operazioni

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── DataController.php
│   └── Services/
│       ├── DataServiceInterface.php
│       ├── ExternalDataService.php
│       ├── CachingDataProxy.php
│       ├── AccessControlDataProxy.php
│       └── LoggingDataProxy.php
├── resources/views/
│   └── data/
│       └── index.blade.php
├── routes/
│   └── web.php
├── composer.json
└── .env.example
```

## Come testare

1. Installa le dipendenze:
```bash
composer install
```

2. Configura l'ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

3. Avvia il server:
```bash
php artisan serve
```

4. Visita `http://localhost:8000/data` per vedere il proxy in azione

## Esempi di utilizzo

### Caching Proxy
```php
$cachingProxy = new CachingDataProxy(new ExternalDataService());
$data = $cachingProxy->getUserData(123); // Prima chiamata - va all'API
$data = $cachingProxy->getUserData(123); // Seconda chiamata - dalla cache
```

### Access Control Proxy
```php
$accessProxy = new AccessControlDataProxy(new ExternalDataService());
$data = $accessProxy->getUserData(123, 'admin'); //  Accesso consentito
$data = $accessProxy->getUserData(123, 'user');  //  Accesso negato
```

### Logging Proxy
```php
$loggingProxy = new LoggingDataProxy(new ExternalDataService());
$data = $loggingProxy->getUserData(123); // Logga l'operazione
```

## Pattern implementati

- **Proxy Pattern**: Controllo dell'accesso agli oggetti
- **Decorator Pattern**: Composizione dei proxy per funzionalità multiple
- **Strategy Pattern**: Diversi tipi di proxy per diverse esigenze
