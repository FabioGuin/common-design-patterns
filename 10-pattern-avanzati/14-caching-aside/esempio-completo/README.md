# E-commerce Caching Aside Pattern

## Panoramica

Questo esempio dimostra l'implementazione del pattern Caching Aside in un sistema e-commerce Laravel. Il sistema implementa caching intelligente per prodotti, utenti e ordini con diverse strategie di cache per ottimizzare le performance e ridurre il carico sul database.

## Architettura

### Caching Implementation
- **CacheManager**: Gestisce le operazioni di cache
- **CacheStrategy**: Implementa diverse strategie di cache
- **CacheConfig**: Configurazione cache per ogni entità
- **CacheInvalidation**: Gestisce invalidazione e refresh

### Entità con Cache
- **Products**: Cache read-through per prodotti
- **Users**: Cache write-through per utenti
- **Orders**: Cache write-behind per ordini
- **Categories**: Cache refresh-ahead per categorie

### Strategie di Cache
- **Read-Through**: Carica dal database se non in cache
- **Write-Through**: Aggiorna database e cache simultaneamente
- **Write-Behind**: Aggiorna cache immediatamente, database in background
- **Refresh-Ahead**: Pre-carica dati correlati

## Struttura del Progetto

```
app/
├── Cache/                    # Cache implementation
├── Services/                 # Servizi con cache
├── Strategies/               # Strategie di cache
├── Config/                   # Configurazione cache
├── Http/Controllers/         # Controllers per testare il pattern
└── Models/                   # Modelli con cache
```

## Funzionalità Implementate

### Cache Core
-  Gestione cache per ogni entità
-  Strategie multiple di cache
-  Cache invalidation e refresh
-  Monitoring e metriche di cache

### Entità con Cache
-  Products con cache read-through (30 min TTL)
-  Users con cache write-through (2 ore TTL)
-  Orders con cache write-behind (15 min TTL)
-  Categories con cache refresh-ahead (1 ora TTL)

### Strategie di Cache
-  Read-Through per dati letti frequentemente
-  Write-Through per dati critici
-  Write-Behind per dati ad alta scrittura
-  Refresh-Ahead per dati correlati

### Monitoring
-  Dashboard per stato cache
-  Metriche di hit/miss ratio
-  Logging dettagliato per ogni operazione
-  Alert per cache problematici

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/caching-aside`
3. **Testa le entità**:
   - Crea e visualizza prodotti
   - Crea e aggiorna utenti
   - Crea e modifica ordini
   - Osserva comportamento cache
4. **Monitora cache**:
   - Visualizza hit/miss ratio
   - Controlla strategie di cache
   - Testa invalidazione

## Database

Il sistema usa tabelle per monitoring:
- **cache_metrics**: Metriche di hit/miss e performance
- **cache_events**: Log dettagliato di ogni operazione
- **cache_invalidations**: Log di invalidazioni cache

## Configurazione

Copia `env.example` in `.env` e configura:

```env
# Cache Configuration
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache TTL
CACHE_PRODUCTS_TTL=1800
CACHE_USERS_TTL=7200
CACHE_ORDERS_TTL=900
CACHE_CATEGORIES_TTL=3600
```

## Migrazioni

```bash
php artisan migrate
```

## Pattern Implementato

Questo esempio dimostra:
- **Caching Aside Pattern**: Carica dati nel cache quando richiesti
- **Read-Through**: Cache per dati letti frequentemente
- **Write-Through**: Cache per dati critici
- **Write-Behind**: Cache per dati ad alta scrittura
- **Refresh-Ahead**: Cache per dati correlati
- **Performance**: Sistema ottimizzato per performance

## Note Tecniche

- **Laravel 11**: Framework aggiornato
- **Cache**: Implementazione custom per gestione cache
- **Strategies**: Strategie multiple per diversi tipi di dati
- **Redis**: Cache distribuito per scalabilità
- **Monitoring**: Dashboard per stato e metriche

## Vantaggi Dimostrati

- **Performance**: Tempi di risposta significativamente migliori
- **Scalabilità**: Riduce il carico sul database
- **Costi**: Riduce i costi di infrastruttura
- **UX**: Migliore esperienza utente
- **Flessibilità**: Strategie diverse per diversi tipi di dati
- **Controllo**: Controllo granulare su cosa viene cachato
