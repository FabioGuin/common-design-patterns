# E-commerce Sharding Pattern

## Panoramica

Questo esempio dimostra l'implementazione del pattern Sharding in un sistema e-commerce Laravel. Il sistema implementa sharding per utenti, prodotti e ordini con diverse strategie di partizionamento per distribuire il carico e migliorare le performance.

## Architettura

### Sharding Implementation
- **ShardingManager**: Gestisce il routing verso i diversi shard
- **ShardingConfig**: Configurazione sharding per ogni entità
- **ShardRouter**: Router per determinare il shard corretto
- **ConnectionManager**: Gestisce connessioni multiple ai database

### Entità con Sharding
- **Users**: Sharding basato su user_id (key-based)
- **Products**: Sharding basato su categoria (range-based)
- **Orders**: Sharding basato su data (hash-based)
- **Categories**: Sharding basato su directory (directory-based)

### Strategie di Sharding
- **Key-based**: Partizionamento basato su chiave primaria
- **Range-based**: Partizionamento basato su intervalli
- **Hash-based**: Partizionamento basato su hash
- **Directory-based**: Partizionamento basato su lookup table

## Struttura del Progetto

```
app/
├── Sharding/                 # Sharding implementation
├── Models/                   # Modelli con sharding
├── Services/                 # Servizi con sharding
├── Config/                   # Configurazione sharding
├── Http/Controllers/         # Controllers per testare il pattern
└── Database/                 # Migrazioni per ogni shard
```

## Funzionalità Implementate

### Sharding Core
- ✅ Gestione sharding per ogni entità
- ✅ Strategie multiple di partizionamento
- ✅ Routing automatico verso shard corretto
- ✅ Monitoring e metriche di sharding

### Entità con Sharding
- ✅ Users con sharding key-based (3 shard)
- ✅ Products con sharding range-based (per categoria)
- ✅ Orders con sharding hash-based (per data)
- ✅ Categories con sharding directory-based

### Strategie di Sharding
- ✅ Key-based per distribuzione uniforme
- ✅ Range-based per dati correlati
- ✅ Hash-based per distribuzione prevedibile
- ✅ Directory-based per flessibilità

### Monitoring
- ✅ Dashboard per stato sharding
- ✅ Metriche di distribuzione dati
- ✅ Logging dettagliato per ogni shard
- ✅ Alert per shard problematici

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/sharding`
3. **Testa le entità**:
   - Crea utenti e verifica shard
   - Crea prodotti e verifica shard
   - Crea ordini e verifica shard
   - Osserva distribuzione dati
4. **Monitora sharding**:
   - Visualizza distribuzione per shard
   - Controlla performance per shard
   - Testa query cross-shard

## Database

Il sistema usa database multipli:
- **shard_1**: Utenti 1-1M, Prodotti A-C, Ordini 2024
- **shard_2**: Utenti 1M-2M, Prodotti D-F, Ordini 2025
- **shard_3**: Utenti 2M-3M, Prodotti G-Z, Ordini 2026

## Configurazione

Copia `env.example` in `.env` e configura:

```env
# Shard 1
DB_SHARD_1_CONNECTION=mysql
DB_SHARD_1_HOST=127.0.0.1
DB_SHARD_1_PORT=3306
DB_SHARD_1_DATABASE=shard_1
DB_SHARD_1_USERNAME=root
DB_SHARD_1_PASSWORD=

# Shard 2
DB_SHARD_2_CONNECTION=mysql
DB_SHARD_2_HOST=127.0.0.1
DB_SHARD_2_PORT=3306
DB_SHARD_2_DATABASE=shard_2
DB_SHARD_2_USERNAME=root
DB_SHARD_2_PASSWORD=

# Shard 3
DB_SHARD_3_CONNECTION=mysql
DB_SHARD_3_HOST=127.0.0.1
DB_SHARD_3_PORT=3306
DB_SHARD_3_DATABASE=shard_3
DB_SHARD_3_USERNAME=root
DB_SHARD_3_PASSWORD=
```

## Migrazioni

```bash
php artisan migrate --database=shard_1
php artisan migrate --database=shard_2
php artisan migrate --database=shard_3
```

## Pattern Implementato

Questo esempio dimostra:
- **Sharding Pattern**: Divide i dati in più database
- **Key-based Sharding**: Partizionamento per chiave primaria
- **Range-based Sharding**: Partizionamento per intervalli
- **Hash-based Sharding**: Partizionamento per hash
- **Directory-based Sharding**: Partizionamento per lookup
- **Scalability**: Sistema scalabile orizzontalmente

## Note Tecniche

- **Laravel 11**: Framework aggiornato
- **Sharding**: Implementazione custom per gestione shard
- **Strategies**: Strategie multiple per partizionamento
- **Connections**: Gestione connessioni multiple
- **Monitoring**: Dashboard per stato e metriche

## Vantaggi Dimostrati

- **Scalabilità**: Scalabilità orizzontale illimitata
- **Performance**: Query più veloci su dataset più piccoli
- **Disponibilità**: Fallimento di un shard non blocca tutto
- **Manutenzione**: Gestione più semplice dei singoli shard
- **Costi**: Possibilità di usare hardware diverso per shard diversi
- **Isolamento**: Dati isolati per sicurezza e compliance
