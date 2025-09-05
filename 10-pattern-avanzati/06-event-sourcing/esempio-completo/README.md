# Sistema Ordini Event Sourcing

## Panoramica

Questo esempio dimostra l'implementazione del pattern Event Sourcing in un sistema di gestione ordini Laravel. Il sistema memorizza tutti i cambiamenti di stato come eventi immutabili, permettendo audit completo e ricostruzione dello stato in qualsiasi momento.

## Architettura

### Event Store
- **EventStore**: Memorizza tutti gli eventi in sequenza cronologica
- **Event**: Ogni cambiamento di stato diventa un evento immutabile
- **Aggregate Root**: Gestisce la logica business e genera eventi

### Eventi Implementati
- **OrderCreated**: Ordine creato
- **OrderPaid**: Ordine pagato
- **OrderShipped**: Ordine spedito
- **OrderDelivered**: Ordine consegnato
- **OrderCancelled**: Ordine cancellato
- **OrderRefunded**: Ordine rimborsato

### Proiezioni
- **OrderProjection**: Ricostruisce lo stato attuale degli ordini
- **OrderHistoryProjection**: Mantiene la cronologia completa
- **OrderStatsProjection**: Calcola statistiche in tempo reale

## Struttura del Progetto

```
app/
├── Events/              # Eventi di dominio
├── EventStore/          # Event Store implementation
├── Aggregates/          # Aggregate Roots
├── Projections/         # Proiezioni per ricostruire stato
├── Services/            # Business logic services
├── Http/Controllers/    # Controllers per testare il pattern
└── Models/              # Modelli per proiezioni
```

## Funzionalità Implementate

### Event Store
- ✅ Memorizzazione eventi immutabili
- ✅ Controllo di concorrenza con versioning
- ✅ Ricostruzione stato da eventi
- ✅ Query eventi per aggregato

### Aggregate Root
- ✅ Gestione logica business
- ✅ Generazione eventi
- ✅ Validazione invarianti
- ✅ Ricostruzione da eventi

### Proiezioni
- ✅ Stato attuale ordini
- ✅ Cronologia completa
- ✅ Statistiche in tempo reale
- ✅ Audit trail

### Interfaccia
- ✅ Visualizzazione eventi
- ✅ Ricostruzione stato
- ✅ Timeline ordini
- ✅ Debugging tools

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/event-sourcing`
3. **Testa gli eventi**:
   - Crea ordini
   - Cambia stato ordini
   - Visualizza cronologia
4. **Testa le proiezioni**:
   - Ricostruisci stato
   - Visualizza statistiche
   - Debug eventi

## Database

Il sistema usa un database ottimizzato per eventi:
- **events**: Tabella principale per memorizzare eventi
- **order_projections**: Proiezione stato attuale ordini
- **order_history**: Cronologia completa eventi

## Configurazione

Copia `env.example` in `.env` e configura il database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_sourcing
DB_USERNAME=root
DB_PASSWORD=
```

## Migrazioni

```bash
php artisan migrate
```

## Pattern Implementato

Questo esempio dimostra:
- **Event Store**: Memorizzazione immutabile di eventi
- **Aggregate Root**: Gestione logica business e eventi
- **Proiezioni**: Ricostruzione stato da eventi
- **Audit Trail**: Traccia completa di tutti i cambiamenti
- **State Reconstruction**: Ricostruzione stato in qualsiasi momento

## Note Tecniche

- **Laravel 11**: Framework aggiornato
- **Event Store**: Implementazione custom per gestire eventi
- **Aggregate Pattern**: Gestione consistenza e invarianti
- **Projections**: Ricostruzione stato ottimizzata
- **Immutable Events**: Eventi immutabili per audit completo

## Vantaggi Dimostrati

- **Audit Completo**: Ogni cambiamento è tracciato
- **Debugging**: Puoi riprodurre qualsiasi situazione
- **Flessibilità**: Nuove proiezioni senza modificare codice esistente
- **Compliance**: Tracciabilità completa per requisiti legali
- **Analisi**: Dati ricchi per business intelligence
