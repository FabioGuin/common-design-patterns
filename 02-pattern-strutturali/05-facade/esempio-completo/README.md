# Sistema E-Commerce con Facade Pattern

## Descrizione

Questo esempio dimostra l'uso del Facade Pattern per creare un sistema di e-commerce semplificato che coordina inventario, pagamenti, spedizioni, notifiche e reporting. Il Facade nasconde la complessità del sottosistema e fornisce un'interfaccia semplice per gestire gli ordini.

## Caratteristiche

- **Sottosistema complesso** con servizi multipli
- **Facade unificato** per semplificare le operazioni
- **Controller Laravel** per gestire gli ordini
- **Vista interattiva** per testare le operazioni
- **Sistema di logging** per tracciare le operazioni
- **Gestione errori** centralizzata

## Installazione

1. Clona il repository
2. Installa le dipendenze: `composer install`
3. Avvia il server: `php artisan serve`
4. Visita `http://localhost:8000/orders`

## Struttura del Progetto

```
app/
├── Http/Controllers/
│   └── OrderController.php
├── Services/
│   ├── ECommerceFacade.php
│   ├── InventoryService.php
│   ├── PaymentService.php
│   ├── ShippingService.php
│   ├── NotificationService.php
│   └── ReportingService.php
resources/views/
└── orders/
    └── index.blade.php
routes/
└── web.php
```

## Come Funziona

1. **I servizi** gestiscono aspetti specifici del sistema
2. **Il Facade** coordina tutti i servizi per operazioni complesse
3. **Il controller** usa solo il Facade per gestire gli ordini
4. **La vista** permette di testare le diverse operazioni
5. **Il logging** traccia tutte le operazioni per debugging

## Test

- Visita `/orders` per vedere l'interfaccia
- Prova a creare e gestire ordini
- Verifica i log per vedere come il Facade coordina i servizi
- Testa le diverse operazioni disponibili
