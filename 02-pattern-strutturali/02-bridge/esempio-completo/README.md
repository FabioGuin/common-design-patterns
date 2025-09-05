# Sistema di Notifiche con Bridge Pattern

## Descrizione

Questo esempio dimostra l'uso del Bridge Pattern per creare un sistema di notifiche flessibile che separa i canali di invio (Email, SMS, Push) dai formattatori di messaggi (HTML, Text, JSON). Il sistema permette di combinare qualsiasi canale con qualsiasi formattatore.

## Caratteristiche

- **Interfaccia comune** per i formattatori di messaggi
- **Implementazioni concrete** per HTML, Text e JSON
- **Astrazioni** per diversi canali di notifica
- **Controller Laravel** per gestire le notifiche
- **Vista interattiva** per testare le combinazioni
- **Configurazione** per switchare tra formattatori

## Installazione

1. Clona il repository
2. Installa le dipendenze: `composer install`
3. Configura il file `.env` con le tue credenziali
4. Avvia il server: `php artisan serve`
5. Visita `http://localhost:8000/notifications`

## Struttura del Progetto

```
app/
├── Http/Controllers/
│   └── NotificationController.php
├── Services/
│   ├── MessageFormatterInterface.php
│   ├── HTMLFormatter.php
│   ├── TextFormatter.php
│   ├── JSONFormatter.php
│   ├── NotificationAbstract.php
│   ├── EmailNotification.php
│   ├── SMSNotification.php
│   └── PushNotification.php
resources/views/
└── notifications/
    └── index.blade.php
routes/
└── web.php
```

## Come Funziona

1. **MessageFormatterInterface** definisce l'interfaccia comune per i formattatori
2. **HTMLFormatter, TextFormatter, JSONFormatter** implementano l'interfaccia
3. **NotificationAbstract** è l'astrazione base che contiene un formattatore
4. **EmailNotification, SMSNotification, PushNotification** sono astrazioni concrete
5. **Il controller** permette di combinare qualsiasi canale con qualsiasi formattatore

## Test

- Visita `/notifications` per vedere l'interfaccia
- Prova a inviare notifiche con diverse combinazioni
- Verifica i log per vedere quale formattatore viene usato
