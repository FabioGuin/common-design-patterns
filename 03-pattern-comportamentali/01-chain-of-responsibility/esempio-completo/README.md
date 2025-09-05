# Esempio Completo: Chain of Responsibility Pattern

Questo esempio dimostra l'implementazione del **Chain of Responsibility Pattern** in Laravel per gestire un sistema di approvazione ordini multi-livello.

## Funzionalità implementate

- **Sistema di approvazione** con diversi livelli di autorità
- **Validazione a cascata** per ordini
- **Middleware personalizzato** per Laravel
- **Pipeline di processing** per file
- **Gestione errori** con diversi livelli di gravità

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── OrderController.php
│   ├── Http/Middleware/
│   │   └── ChainMiddleware.php
│   └── Services/
│       ├── OrderApproval/
│       │   ├── HandlerInterface.php
│       │   ├── AbstractHandler.php
│       │   ├── ValidationHandler.php
│       │   ├── CreditCheckHandler.php
│       │   ├── InventoryCheckHandler.php
│       │   ├── ManagerApprovalHandler.php
│       │   └── DirectorApprovalHandler.php
│       └── FileProcessing/
│           ├── FileHandlerInterface.php
│           ├── AbstractFileHandler.php
│           ├── ImageHandler.php
│           ├── DocumentHandler.php
│           └── VideoHandler.php
├── resources/views/
│   └── orders/
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

4. Visita `http://localhost:8000/orders` per vedere il sistema di approvazione

## Esempi di utilizzo

### Sistema di Approvazione Ordini
```php
$validation = new ValidationHandler();
$creditCheck = new CreditCheckHandler();
$inventoryCheck = new InventoryCheckHandler();
$managerApproval = new ManagerApprovalHandler();
$directorApproval = new DirectorApprovalHandler();

// Crea la catena
$validation->setNext($creditCheck)
           ->setNext($inventoryCheck)
           ->setNext($managerApproval)
           ->setNext($directorApproval);

// Processa l'ordine
$result = $validation->handle($order);
```

### Pipeline di Processing File
```php
$imageHandler = new ImageHandler();
$documentHandler = new DocumentHandler();
$videoHandler = new VideoHandler();

// Crea la catena
$imageHandler->setNext($documentHandler)->setNext($videoHandler);

// Processa il file
$result = $imageHandler->handle($file);
```

## Pattern implementati

- **Chain of Responsibility Pattern**: Gestione richieste a cascata
- **Middleware Pattern**: Middleware personalizzato per Laravel
- **Strategy Pattern**: Diversi tipi di approvazione
- **Template Method Pattern**: Handler astratti con logica comune
