# Null Object Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Null Object in Laravel attraverso un sistema di notifiche con fallback automatico. L'esempio include:

- **Servizi di notifica reali** che inviano email e SMS
- **Null Object Service** che fornisce comportamenti neutri quando i servizi non sono disponibili
- **Factory Pattern** per la selezione automatica del servizio appropriato
- **Interfaccia web interattiva** per testare i diversi scenari
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il pattern Null Object gestisce:
- **Servizi opzionali** per notifiche che potrebbero non essere configurati
- **Fallback automatico** a comportamenti neutri quando i servizi reali non sono disponibili
- **Eliminazione di controlli null** nel codice principale

Quando testi l'esempio, vedrai che:
1. Il sistema funziona sempre, anche senza servizi di notifica configurati
2. I servizi reali inviano notifiche quando disponibili
3. I null object forniscono comportamenti sicuri quando i servizi non sono disponibili
4. La factory seleziona automaticamente il servizio appropriato

## Caratteristiche tecniche
- Implementazione completa del Null Object Pattern
- Integrazione con Laravel Service Container
- Factory Pattern per la selezione dei servizi
- Interfaccia web per testare i diversi scenari
- Test completi con Pest

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Http/Controllers/NullObjectController.php app/Http/Controllers/
cp /path/to/this/example/app/Services/NotificationServiceFactory.php app/Services/
cp /path/to/this/example/app/Services/EmailNotificationService.php app/Services/
cp /path/to/this/example/app/Services/SmsNotificationService.php app/Services/
cp /path/to/this/example/app/Services/NullNotificationService.php app/Services/
cp /path/to/this/example/app/Contracts/NotificationServiceInterface.php app/Contracts/
mkdir -p resources/views/null_object
cp /path/to/this/example/resources/views/null_object/example.blade.php resources/views/null_object/
cp /path/to/this/example/tests/Feature/NullObjectPatternTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\NullObjectController;

// Route per il pattern Null Object
Route::get('/null-object', [NullObjectController::class, 'index']);
Route::get('/null-object/test', [NullObjectController::class, 'test']);

// Route API
Route::prefix('api/null-object')->group(function () {
    Route::get('/', [NullObjectController::class, 'index']);
    Route::post('/test', [NullObjectController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/null-object

# Testa via API
curl http://localhost:8000/api/null-object/test

# Esegui i test
php artisan test tests/Feature/NullObjectPatternTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/null-object` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/null-object/test`
3. **Test**: Esegui `php artisan test tests/Feature/NullObjectPatternTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Http/Controllers/NullObjectController.php` - Controller per testare il pattern
- `app/Services/NotificationServiceFactory.php` - Factory per creare i servizi
- `app/Services/EmailNotificationService.php` - Servizio email reale
- `app/Services/SmsNotificationService.php` - Servizio SMS reale
- `app/Services/NullNotificationService.php` - Implementazione null object
- `app/Contracts/NotificationServiceInterface.php` - Interfaccia comune
- `resources/views/null_object/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/NullObjectPatternTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Puoi configurare quale servizio usare modificando il parametro nella factory:

```php
// Per usare email
$service = NotificationServiceFactory::create('email');

// Per usare SMS
$service = NotificationServiceFactory::create('sms');

// Per usare null object (fallback)
$service = NotificationServiceFactory::create('disabled');
```

### Estensione
Puoi facilmente aggiungere nuovi servizi:

1. Crea una nuova classe che implementa `NotificationServiceInterface`
2. Aggiungi il caso nella factory
3. Il pattern funzionerà automaticamente

## Note importanti
- I file sono pronti per essere copiati in un progetto Laravel esistente
- Il pattern elimina la necessità di controlli null nel codice
- I null object forniscono comportamenti sicuri e prevedibili
- La factory gestisce automaticamente la selezione del servizio appropriato
