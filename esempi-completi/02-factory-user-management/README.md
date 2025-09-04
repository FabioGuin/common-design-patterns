# Factory Method Pattern - User Management System

## Descrizione
Implementazione completa del Factory Method Pattern per la gestione di diversi tipi di utenti (Admin, User, Guest) con ruoli e permessi specifici.

## Caratteristiche
- Factory per creazione utenti (Admin, User, Guest)
- Gestione ruoli e permessi
- Integrazione con Eloquent ORM
- Service Provider per registrazione factory
- Controller con dependency injection
- Test unitari per factory methods
- API RESTful per gestione utenti

## Struttura del Progetto
```
app/
├── Services/UserFactory/
│   ├── UserFactoryInterface.php
│   ├── UserFactory.php
│   ├── AdminUserFactory.php
│   ├── RegularUserFactory.php
│   └── GuestUserFactory.php
├── Models/
│   ├── User.php
│   └── Role.php
├── Http/Controllers/
│   └── UserController.php
└── Providers/
    └── UserFactoryServiceProvider.php
```

## Installazione
1. Copia i file nella tua applicazione Laravel
2. Registra il Service Provider in `config/app.php`
3. Esegui le migrazioni per creare le tabelle
4. Testa l'API con i route forniti

## Utilizzo
```php
// Creazione utente tramite factory
$adminFactory = app(AdminUserFactory::class);
$admin = $adminFactory->createUser([
    'name' => 'Admin User',
    'email' => 'admin@example.com'
]);

// API RESTful
POST /api/users/admin - Crea utente admin
POST /api/users/regular - Crea utente normale
POST /api/users/guest - Crea utente guest
GET /api/users - Lista tutti gli utenti
```

## Test
```bash
php artisan test --filter=UserFactoryTest
```
