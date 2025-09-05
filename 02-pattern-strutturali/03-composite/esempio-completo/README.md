# Sistema di Menu con Composite Pattern

## Descrizione

Questo esempio dimostra l'uso del Composite Pattern per creare un sistema di menu gerarchico con categorie, sottocategorie e voci singole. Il sistema permette di trattare uniformemente tutti gli elementi del menu e calcolare prezzi totali per intere sezioni.

## Caratteristiche

- **Interfaccia comune** per tutti gli elementi del menu
- **Implementazioni** per voci singole e categorie
- **Operazioni ricorsive** per calcolare prezzi e contare elementi
- **Controller Laravel** per gestire il menu
- **Vista interattiva** per visualizzare la struttura gerarchica
- **API** per aggiungere/rimuovere elementi dinamicamente

## Installazione

1. Clona il repository
2. Installa le dipendenze: `composer install`
3. Avvia il server: `php artisan serve`
4. Visita `http://localhost:8000/menu`

## Struttura del Progetto

```
app/
├── Http/Controllers/
│   └── MenuController.php
├── Services/
│   ├── MenuComponentInterface.php
│   ├── MenuItem.php
│   └── MenuCategory.php
resources/views/
└── menu/
    └── index.blade.php
routes/
└── web.php
```

## Come Funziona

1. **MenuComponentInterface** definisce l'interfaccia comune
2. **MenuItem** rappresenta le voci singole del menu
3. **MenuCategory** rappresenta le categorie che possono contenere altri elementi
4. **Il controller** gestisce le operazioni CRUD sul menu
5. **La vista** visualizza la struttura gerarchica in modo interattivo

## Test

- Visita `/menu` per vedere la struttura del menu
- Prova ad aggiungere/rimuovere elementi
- Verifica i calcoli dei prezzi totali
- Testa le operazioni ricorsive
