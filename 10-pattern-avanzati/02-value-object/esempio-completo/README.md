# Value Object Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Value Object in Laravel attraverso un sistema e-commerce con Value Object per prezzi, indirizzi e prodotti. L'esempio include:

- **Value Object per prezzi** con supporto per valute diverse e operazioni matematiche
- **Value Object per indirizzi** con validazione geografica e formattazione
- **Value Object per SKU prodotti** con validazione di formato e generazione automatica
- **Operazioni tra Value Object** per calcoli e confronti
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il pattern Value Object gestisce:
- **Validazione centralizzata** per tutti i valori con regole di business
- **Immutabilità** garantita per prevenire modifiche accidentali
- **Type safety** per evitare errori di tipo nel codice
- **Semantica chiara** per rendere il codice più espressivo

Quando testi l'esempio, vedrai che:
1. I Value Object validano automaticamente i valori inseriti
2. Le operazioni tra Value Object sono type-safe e sicure
3. I confronti funzionano per valore, non per riferimento
4. I valori sono immutabili e non possono essere modificati

## Caratteristiche tecniche
- Implementazione completa del Value Object Pattern
- Value Object immutabili con validazione
- Operazioni matematiche tra Value Object
- Serializzazione per database e API
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
cp /path/to/this/example/app/Http/Controllers/ValueObjectController.php app/Http/Controllers/
cp /path/to/this/example/app/ValueObjects/Price.php app/ValueObjects/
cp /path/to/this/example/app/ValueObjects/Address.php app/ValueObjects/
cp /path/to/this/example/app/ValueObjects/ProductSku.php app/ValueObjects/
cp /path/to/this/example/app/ValueObjects/Email.php app/ValueObjects/
mkdir -p resources/views/value_object
cp /path/to/this/example/resources/views/value_object/example.blade.php resources/views/value_object/
cp /path/to/this/example/tests/Feature/ValueObjectPatternTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\ValueObjectController;

// Route per il pattern Value Object
Route::get('/value-object', [ValueObjectController::class, 'index']);
Route::get('/value-object/test', [ValueObjectController::class, 'test']);

// Route API
Route::prefix('api/value-object')->group(function () {
    Route::get('/', [ValueObjectController::class, 'index']);
    Route::post('/test', [ValueObjectController::class, 'test']);
    Route::post('/price/calculate', [ValueObjectController::class, 'calculatePrice']);
    Route::post('/address/validate', [ValueObjectController::class, 'validateAddress']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/value-object

# Testa via API
curl http://localhost:8000/api/value-object/test

# Esegui i test
php artisan test tests/Feature/ValueObjectPatternTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/value-object` e testa l'interfaccia
2. **API**: Esegui `curl http://localhost:8000/api/value-object/test`
3. **Test**: Esegui `php artisan test tests/Feature/ValueObjectPatternTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Http/Controllers/ValueObjectController.php` - Controller per testare il pattern
- `app/ValueObjects/Price.php` - Value Object per prezzi con valute
- `app/ValueObjects/Address.php` - Value Object per indirizzi
- `app/ValueObjects/ProductSku.php` - Value Object per SKU prodotti
- `app/ValueObjects/Email.php` - Value Object per email
- `resources/views/value_object/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/ValueObjectPatternTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Puoi configurare i Value Object modificando le costanti nelle classi:

```php
// In Price.php
const SUPPORTED_CURRENCIES = ['EUR', 'USD', 'GBP'];

// In Address.php
const SUPPORTED_COUNTRIES = ['IT', 'US', 'GB'];
```

### Estensione
Puoi facilmente aggiungere nuovi Value Object:

1. Crea una nuova classe che estende `ValueObject`
2. Implementa i metodi richiesti
3. Aggiungi la validazione nel costruttore
4. Il pattern funzionerà automaticamente

## Note importanti
- I file sono pronti per essere copiati in un progetto Laravel esistente
- I Value Object sono immutabili e type-safe
- La validazione è centralizzata e automatica
- Le operazioni tra Value Object sono sicure e prevedibili
