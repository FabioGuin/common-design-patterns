<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Notifica creazione articolo
     */
    public function notifyArticleCreated(Article $article): void
    {
        Log::info("Articolo creato: {$article->title} da {$article->user->name}");
        
        // Invia notifica all'autore
        $this->sendEmail($article->user->email, 'Articolo Creato', [
            'title' => 'Articolo creato con successo',
            'message' => "Il tuo articolo '{$article->title}' è stato creato con successo.",
            'article' => $article
        ]);
    }

    /**
     * Notifica aggiornamento articolo
     */
    public function notifyArticleUpdated(Article $article): void
    {
        Log::info("Articolo aggiornato: {$article->title} da {$article->user->name}");
        
        // Invia notifica all'autore
        $this->sendEmail($article->user->email, 'Articolo Aggiornato', [
            'title' => 'Articolo aggiornato con successo',
            'message' => "Il tuo articolo '{$article->title}' è stato aggiornato con successo.",
            'article' => $article
        ]);
    }

    /**
     * Notifica pubblicazione articolo
     */
    public function notifyArticlePublished(Article $article): void
    {
        Log::info("Articolo pubblicato: {$article->title} da {$article->user->name}");
        
        // Invia notifica all'autore
        $this->sendEmail($article->user->email, 'Articolo Pubblicato', [
            'title' => 'Articolo pubblicato con successo',
            'message' => "Il tuo articolo '{$article->title}' è stato pubblicato e è ora visibile al pubblico.",
            'article' => $article
        ]);

        // Notifica amministratori
        $this->notifyAdmins("Nuovo articolo pubblicato: {$article->title}");
    }

    /**
     * Notifica articolo in bozza
     */
    public function notifyArticleDrafted(Article $article): void
    {
        Log::info("Articolo messo in bozza: {$article->title} da {$article->user->name}");
        
        // Invia notifica all'autore
        $this->sendEmail($article->user->email, 'Articolo in Bozza', [
            'title' => 'Articolo messo in bozza',
            'message' => "Il tuo articolo '{$article->title}' è stato messo in bozza.",
            'article' => $article
        ]);
    }

    /**
     * Notifica eliminazione articolo
     */
    public function notifyArticleDeleted(Article $article): void
    {
        Log::info("Articolo eliminato: {$article->title} da {$article->user->name}");
        
        // Invia notifica all'autore
        $this->sendEmail($article->user->email, 'Articolo Eliminato', [
            'title' => 'Articolo eliminato',
            'message' => "Il tuo articolo '{$article->title}' è stato eliminato.",
            'article' => $article
        ]);
    }

    /**
     * Notifica creazione utente
     */
    public function notifyUserCreated(User $user): void
    {
        Log::info("Utente creato: {$user->name} ({$user->email})");
        
        // Invia notifica all'utente
        $this->sendEmail($user->email, 'Account Creato', [
            'title' => 'Account creato con successo',
            'message' => "Benvenuto {$user->name}! Il tuo account è stato creato con successo.",
            'user' => $user
        ]);

        // Notifica amministratori
        $this->notifyAdmins("Nuovo utente registrato: {$user->name} ({$user->email})");
    }

    /**
     * Notifica aggiornamento utente
     */
    public function notifyUserUpdated(User $user): void
    {
        Log::info("Utente aggiornato: {$user->name} ({$user->email})");
        
        // Invia notifica all'utente
        $this->sendEmail($user->email, 'Profilo Aggiornato', [
            'title' => 'Profilo aggiornato con successo',
            'message' => "Il tuo profilo è stato aggiornato con successo.",
            'user' => $user
        ]);
    }

    /**
     * Notifica attivazione utente
     */
    public function notifyUserActivated(User $user): void
    {
        Log::info("Utente attivato: {$user->name} ({$user->email})");
        
        // Invia notifica all'utente
        $this->sendEmail($user->email, 'Account Attivato', [
            'title' => 'Account attivato',
            'message' => "Il tuo account è stato attivato con successo.",
            'user' => $user
        ]);
    }

    /**
     * Notifica disattivazione utente
     */
    public function notifyUserDeactivated(User $user): void
    {
        Log::info("Utente disattivato: {$user->name} ({$user->email})");
        
        // Invia notifica all'utente
        $this->sendEmail($user->email, 'Account Disattivato', [
            'title' => 'Account disattivato',
            'message' => "Il tuo account è stato disattivato.",
            'user' => $user
        ]);
    }

    /**
     * Notifica cambio ruolo utente
     */
    public function notifyUserRoleChanged(User $user, string $newRole): void
    {
        Log::info("Ruolo utente cambiato: {$user->name} -> {$newRole}");
        
        // Invia notifica all'utente
        $this->sendEmail($user->email, 'Ruolo Aggiornato', [
            'title' => 'Ruolo aggiornato',
            'message' => "Il tuo ruolo è stato aggiornato a: {$newRole}",
            'user' => $user,
            'newRole' => $newRole
        ]);
    }

    /**
     * Notifica eliminazione utente
     */
    public function notifyUserDeleted(User $user): void
    {
        Log::info("Utente eliminato: {$user->name} ({$user->email})");
        
        // Notifica amministratori
        $this->notifyAdmins("Utente eliminato: {$user->name} ({$user->email})");
    }

    /**
     * Invia email di benvenuto
     */
    public function sendWelcomeEmail(User $user): void
    {
        $this->sendEmail($user->email, 'Benvenuto!', [
            'title' => 'Benvenuto nella nostra piattaforma!',
            'message' => "Ciao {$user->name}, benvenuto nella nostra piattaforma!",
            'user' => $user
        ]);
    }

    /**
     * Notifica amministratori
     */
    private function notifyAdmins(string $message): void
    {
        // Invia notifica agli amministratori
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $this->sendEmail($admin->email, 'Notifica Amministratore', [
                'title' => 'Notifica Amministratore',
                'message' => $message
            ]);
        }
    }

    /**
     * Invia email
     */
    private function sendEmail(string $to, string $subject, array $data): void
    {
        try {
            // In un'applicazione reale, useresti Mail::send() o una coda
            Log::info("Email inviata a {$to}: {$subject}");
            
            // Simula invio email
            Mail::send('emails.notification', $data, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error("Errore invio email a {$to}: " . $e->getMessage());
        }
    }

    /**
     * Invia notifica push
     */
    public function sendPushNotification(User $user, string $title, string $message): void
    {
        Log::info("Push notification inviata a {$user->name}: {$title}");
        
        // Implementazione per notifiche push
        // Questo è un esempio semplificato
    }

    /**
     * Invia notifica SMS
     */
    public function sendSmsNotification(User $user, string $message): void
    {
        Log::info("SMS inviato a {$user->name}: {$message}");
        
        // Implementazione per SMS
        // Questo è un esempio semplificato
    }
}
