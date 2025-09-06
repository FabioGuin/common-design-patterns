# Sistema Email con Job Queue Completo

## Panoramica

Questo esempio dimostra l'implementazione del pattern Job Queue in un sistema di invio email Laravel. Il sistema gestisce diversi tipi di email (benvenuto, newsletter, notifiche) utilizzando code asincrone per migliorare le performance e l'esperienza utente.

## Architettura

### Job Types
- **SendWelcomeEmailJob**: Invio email di benvenuto per nuovi utenti
- **SendNewsletterJob**: Invio newsletter a tutti gli utenti
- **SendNotificationJob**: Invio notifiche push e email
- **ProcessBulkEmailJob**: Elaborazione di invii massivi

### Queue System
- **Database Queue**: Per job semplici e affidabili
- **Redis Queue**: Per job ad alte performance (opzionale)
- **Failed Jobs**: Gestione job falliti con retry automatico

### Monitoring
- **Job Status**: Tracciamento stato dei job
- **Failed Jobs**: Visualizzazione job falliti
- **Performance Metrics**: Tempi di esecuzione e throughput

## Struttura del Progetto

```
app/
├── Jobs/                    # Job classes
│   ├── SendWelcomeEmailJob.php
│   ├── SendNewsletterJob.php
│   ├── SendNotificationJob.php
│   └── ProcessBulkEmailJob.php
├── Services/                # Business logic services
│   ├── EmailService.php
│   ├── UserService.php
│   └── NotificationService.php
├── Http/Controllers/        # Controllers per testare
│   └── EmailController.php
└── Models/                  # Models
    └── User.php
```

## Funzionalità Implementate

### Job Management
-  Creazione e dispatch di job
-  Gestione errori e retry
-  Monitoring dello stato
-  Gestione job falliti

### Email Types
-  Email di benvenuto personalizzate
-  Newsletter con template
-  Notifiche push e email
-  Invii massivi ottimizzati

### Queue Features
-  Configurazione database queue
-  Gestione failed jobs
-  Retry automatico
-  Timeout e limiti

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/email`
3. **Testa le funzionalità**:
   - Registra un nuovo utente (email di benvenuto)
   - Invia newsletter
   - Invia notifiche
   - Monitora i job

## Configurazione

### Database Queue
```bash
# Crea le tabelle per le code
php artisan queue:table
php artisan migrate

# Avvia il worker
php artisan queue:work
```

### Environment Variables
```env
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

## Esempi di Utilizzo

### Invio Email di Benvenuto
```php
// Nel controller
User::create($userData);
SendWelcomeEmailJob::dispatch($user, $userData['email']);
```

### Invio Newsletter
```php
// Invio a tutti gli utenti
$users = User::all();
foreach ($users as $user) {
    SendNewsletterJob::dispatch($user, $newsletterData);
}
```

### Invio Notifiche
```php
// Notifica specifica
SendNotificationJob::dispatch($user, 'Nuovo messaggio', $messageData);
```

## Monitoring e Debug

### Controllo Job
```bash
# Vedi job in coda
php artisan queue:work --once

# Vedi job falliti
php artisan queue:failed

# Riprova job falliti
php artisan queue:retry all
```

### Log e Debug
- I job loggano le loro operazioni
- I failed jobs sono salvati nel database
- Puoi monitorare le performance dei job

## Best Practices

### Job Design
- Mantieni i job piccoli e focalizzati
- Usa timeout appropriati
- Gestisci errori gracefully
- Logga le operazioni importanti

### Performance
- Usa batch processing per operazioni massive
- Configura worker appropriati
- Monitora l'utilizzo delle risorse
- Ottimizza le query nei job

### Reliability
- Implementa retry logic
- Gestisci job falliti
- Usa dead letter queues
- Monitora la salute delle code
