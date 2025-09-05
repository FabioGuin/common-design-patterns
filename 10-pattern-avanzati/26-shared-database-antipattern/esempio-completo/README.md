# Esempio Shared Database Anti-pattern

## Descrizione

Questo esempio dimostra il **Shared Database Anti-pattern** in un'applicazione Laravel. Il pattern mostra i problemi che si verificano quando più servizi condividono lo stesso database, causando accoppiamento forte, problemi di scalabilità e difficoltà di manutenzione.

## Struttura dell'Esempio

```
esempio-completo/
├── README.md
├── app/
│   ├── Services/
│   │   ├── SharedDatabaseService.php
│   │   ├── UserService.php
│   │   ├── ProductService.php
│   │   ├── OrderService.php
│   │   └── PaymentService.php
│   ├── Http/Controllers/
│   │   ├── SharedDatabaseController.php
│   │   ├── UserController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   └── PaymentController.php
│   └── Models/
│       ├── User.php
│       ├── Product.php
│       ├── Order.php
│       ├── Payment.php
│       └── OrderItem.php
├── resources/views/
│   └── shared-database/
│       └── example.blade.php
├── routes/
│   └── web.php
├── tests/Feature/
│   └── SharedDatabaseTest.php
└── test-standalone.php
```

## Caratteristiche dell'Esempio

### 1. **Database Condiviso**
- Tutti i servizi utilizzano la stessa connessione al database
- Tabelle condivise tra servizi diversi
- Schema monolitico con dipendenze incrociate

### 2. **Servizi Accoppiati**
- UserService, ProductService, OrderService, PaymentService
- Dipendenze dirette tra servizi
- Transazioni distribuite complesse

### 3. **Problemi del Pattern**
- **Accoppiamento forte**: Modifiche a un servizio impattano altri
- **Scalabilità limitata**: Impossibile scalare servizi indipendentemente
- **Conflitti di schema**: Modifiche al database bloccano tutti i servizi
- **Transazioni complesse**: Lock su tabelle condivise
- **Difficoltà di testing**: Test isolati impossibili

### 4. **Simulazione Problemi**
- Lock su tabelle condivise
- Conflitti di concorrenza
- Dipendenze circolari
- Performance degradate

## Come Eseguire l'Esempio

### 1. **Test Standalone**
```bash
php test-standalone.php
```

### 2. **Test Laravel**
```bash
php artisan test tests/Feature/SharedDatabaseTest.php
```

### 3. **Interfaccia Web**
```bash
php artisan serve
# Visita: http://localhost:8000/shared-database/example
```

## Problemi Dimostrati

### 1. **Accoppiamento Forte**
- Modifiche al schema User impattano Order e Payment
- Impossibile modificare un servizio senza testare tutti gli altri

### 2. **Conflitti di Concorrenza**
- Lock su tabelle condivise durante operazioni simultanee
- Deadlock tra servizi diversi

### 3. **Scalabilità Limitata**
- Impossibile scalare servizi indipendentemente
- Bottleneck sul database condiviso

### 4. **Difficoltà di Testing**
- Test isolati impossibili
- Setup complesso per test di integrazione

## Soluzioni Alternative

### 1. **Database Per Service**
- Ogni servizio ha il proprio database
- Comunicazione tramite API o eventi

### 2. **Event Sourcing**
- Eventi come fonte di verità
- Database separati per ogni servizio

### 3. **CQRS**
- Separazione tra comandi e query
- Database ottimizzati per ogni operazione

## Configurazione Database

L'esempio utilizza un database condiviso con le seguenti tabelle:

- `users` - Gestita da UserService
- `products` - Gestita da ProductService  
- `orders` - Gestita da OrderService
- `order_items` - Gestita da OrderService
- `payments` - Gestita da PaymentService

## Note Importanti

- Questo è un **anti-pattern** da evitare
- Dimostra i problemi dell'architettura monolitica
- Utile per comprendere perché servire database separati
- Non utilizzare in produzione

## Vantaggi del Pattern (Limitati)

- **Semplicità iniziale**: Un solo database da gestire
- **Transazioni ACID**: Garantite su tutto il sistema
- **Consistenza immediata**: Dati sempre sincronizzati

## Svantaggi del Pattern

- **Accoppiamento forte**: Servizi non indipendenti
- **Scalabilità limitata**: Bottleneck sul database
- **Conflitti di schema**: Modifiche bloccanti
- **Difficoltà di testing**: Test complessi
- **Single point of failure**: Database condiviso
- **Performance degradate**: Lock e conflitti
