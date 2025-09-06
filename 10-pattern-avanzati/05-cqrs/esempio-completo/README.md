# E-commerce CQRS Completo

## Panoramica

Questo esempio dimostra l'implementazione del pattern CQRS in un sistema e-commerce Laravel. Il sistema separa completamente le operazioni di lettura (query) da quelle di scrittura (command) per ottimizzare le performance e la scalabilità.

## Architettura

### Command Side (Scrittura)
- **CreateProductCommand**: Crea nuovi prodotti
- **UpdateProductCommand**: Aggiorna prodotti esistenti
- **CreateOrderCommand**: Crea nuovi ordini
- **Command Handlers**: Gestiscono la logica di business per i comandi

### Query Side (Lettura)
- **ProductQuery**: Ricerca e filtraggio prodotti ottimizzato
- **OrderQuery**: Visualizzazione ordini ottimizzata
- **Query Models**: Modelli ottimizzati per la sola lettura

### Event Bus
- **Event Bus**: Gestisce la sincronizzazione tra command e query
- **Projections**: Aggiornano i modelli di lettura quando cambiano i dati

## Struttura del Progetto

```
app/
├── Commands/           # Command objects
├── Handlers/           # Command handlers
├── Queries/            # Query objects
├── QueryModels/        # Modelli ottimizzati per lettura
├── Events/             # Domain events
├── Projections/        # Event projections
├── Services/           # Business logic services
└── Http/Controllers/   # Controllers per testare il pattern
```

## Funzionalità Implementate

### Command Side
-  Creazione prodotti con validazione
-  Aggiornamento prodotti
-  Creazione ordini con gestione inventario
-  Pubblicazione eventi per sincronizzazione

### Query Side
-  Ricerca prodotti con filtri avanzati
-  Visualizzazione ordini utente
-  Statistiche prodotti
-  Query ottimizzate per performance

### Event Bus
-  Sincronizzazione automatica tra command e query
-  Proiezioni per aggiornare modelli di lettura
-  Gestione errori e retry logic

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/cqrs`
3. **Testa i comandi**:
   - Crea prodotti
   - Aggiorna prodotti
   - Crea ordini
4. **Testa le query**:
   - Cerca prodotti
   - Visualizza ordini
   - Controlla statistiche

## Database

Il sistema usa due database separati:
- **Write DB**: Per operazioni di scrittura (comandi)
- **Read DB**: Per operazioni di lettura (query)

Le tabelle vengono sincronizzate automaticamente tramite eventi.

## Configurazione

Copia `.env.example` in `.env` e configura i database:

```env
# Write Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cqrs_write
DB_USERNAME=root
DB_PASSWORD=

# Read Database
DB_READ_CONNECTION=mysql
DB_READ_HOST=127.0.0.1
DB_READ_PORT=3306
DB_READ_DATABASE=cqrs_read
DB_READ_USERNAME=root
DB_READ_PASSWORD=
```

## Migrazioni

```bash
# Database di scrittura
php artisan migrate --database=mysql

# Database di lettura
php artisan migrate --database=mysql_read
```

## Pattern Implementato

Questo esempio dimostra:
- **Separazione Command/Query**: Modelli e logiche completamente separate
- **Event-Driven Architecture**: Sincronizzazione tramite eventi
- **Optimized Queries**: Query ottimizzate per la sola lettura
- **Scalable Architecture**: Architettura scalabile per sistemi complessi

## Note Tecniche

- **Laravel 11**: Framework aggiornato
- **Event Bus**: Implementazione custom per gestire eventi
- **Dual Database**: Due database separati per command e query
- **Projections**: Aggiornamento automatico dei modelli di lettura
- **Performance**: Query ottimizzate con indici specifici
