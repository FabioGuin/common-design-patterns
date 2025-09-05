# Sistema di Template con Flyweight Pattern

## Descrizione

Questo esempio dimostra l'uso del Flyweight Pattern per creare un sistema di template riutilizzabili per documenti. I template condividono le proprietà comuni (layout, stile, struttura) mentre ogni documento ha le sue proprietà specifiche (contenuto, posizione, colore).

## Caratteristiche

- **Template Flyweight** con proprietà condivise
- **Factory** per gestire il riutilizzo dei template
- **Document Context** per le proprietà specifiche
- **Controller Laravel** per gestire i documenti
- **Vista interattiva** per testare il sistema
- **Sistema di logging** per tracciare il riutilizzo

## Installazione

1. Clona il repository
2. Installa le dipendenze: `composer install`
3. Avvia il server: `php artisan serve`
4. Visita `http://localhost:8000/documents`

## Struttura del Progetto

```
app/
├── Http/Controllers/
│   └── DocumentController.php
├── Services/
│   ├── DocumentTemplate.php
│   ├── DocumentTemplateFactory.php
│   └── DocumentContext.php
resources/views/
└── documents/
    └── index.blade.php
routes/
└── web.php
```

## Come Funziona

1. **DocumentTemplate** contiene le proprietà condivise (layout, stile)
2. **DocumentTemplateFactory** gestisce il riutilizzo dei template
3. **DocumentContext** contiene le proprietà specifiche (contenuto, posizione)
4. **Il controller** usa la factory per creare documenti
5. **La vista** permette di testare il sistema di template

## Test

- Visita `/documents` per vedere l'interfaccia
- Prova a creare documenti con diversi template
- Verifica i log per vedere come i template vengono riutilizzati
- Testa le performance con molti documenti
