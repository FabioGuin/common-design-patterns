# User Builder System - Esempio Completo

## Cosa fa questo esempio
Questo esempio dimostra l'implementazione del **Builder Pattern** per creare utenti complessi in Laravel. Il sistema permette di costruire utenti con profili, impostazioni e ruoli in modo flessibile e leggibile.

## Caratteristiche principali
- **UserBuilder**: Costruzione step-by-step di utenti complessi
- **Validazione**: Controllo dei dati durante la costruzione
- **Relazioni**: Gestione automatica di profili e impostazioni
- **Ruoli**: Sistema di ruoli flessibile
- **Test**: Test completi con Pest
- **API**: Endpoint REST per dimostrare l'uso

## Struttura del progetto
```
app/
├── Builders/
│   └── UserBuilder.php          # Builder principale
├── Models/
│   ├── User.php                 # Modello User
│   ├── Profile.php              # Modello Profile
│   └── Setting.php              # Modello Setting
├── Http/
│   └── Controllers/
│       └── UserController.php   # Controller per API
└── Services/
    └── UserService.php          # Service per logica business

database/
├── migrations/
│   ├── create_users_table.php
│   ├── create_profiles_table.php
│   └── create_settings_table.php
└── seeders/
    └── UserSeeder.php

tests/
└── Feature/
    └── UserBuilderTest.php      # Test completi

routes/
└── api.php                      # Route API
```

## Come usarlo

### 1. Installazione
```bash
composer install
php artisan migrate
php artisan db:seed
```

### 2. Esempi di uso

#### Builder base
```php
$user = UserBuilder::create()
    ->withBasicInfo('Mario', 'Rossi', 'mario@email.com')
    ->withPassword('password123')
    ->withProfile([
        'bio' => 'Sviluppatore Laravel',
        'avatar' => 'avatar.jpg'
    ])
    ->withSettings([
        'notifications' => true,
        'theme' => 'dark'
    ])
    ->asAdmin()
    ->withEmailVerified()
    ->build();
```

#### Builder per utenti semplici
```php
$user = UserBuilder::create()
    ->withBasicInfo('Giulia', 'Bianchi', 'giulia@email.com')
    ->withPassword('password123')
    ->build();
```

#### Builder per utenti con ruoli specifici
```php
$user = UserBuilder::create()
    ->withBasicInfo('Luca', 'Verdi', 'luca@email.com')
    ->withPassword('password123')
    ->withRole('editor')
    ->withProfile(['bio' => 'Editor esperto'])
    ->build();
```

### 3. API Endpoints
- `POST /api/users` - Crea nuovo utente
- `GET /api/users` - Lista utenti
- `GET /api/users/{id}` - Dettaglio utente
- `PUT /api/users/{id}` - Aggiorna utente
- `DELETE /api/users/{id}` - Elimina utente

### 4. Test
```bash
php artisan test
```

## Vantaggi del Builder Pattern
- **Leggibilità**: Codice molto più chiaro e comprensibile
- **Flessibilità**: Costruisci utenti con solo i campi necessari
- **Validazione**: Controllo dei dati durante la costruzione
- **Manutenibilità**: Facile aggiungere nuovi campi o validazioni
- **Riutilizzabilità**: Stesso builder per diversi tipi di utenti

## Pattern correlati
- **Factory Method**: Per creare diversi tipi di builder
- **Fluent Interface**: I metodi concatenati del builder
- **Validation**: Controllo dei dati durante la costruzione
