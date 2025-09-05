<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailDirector;
use App\Services\WelcomeEmailBuilder;
use App\Services\NewsletterEmailBuilder;
use App\Services\NotificationEmailBuilder;

class EmailController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Builder Pattern Demo',
            'data' => [
                'pattern_description' => 'Builder costruisce oggetti complessi passo dopo passo',
                'email_types' => ['welcome', 'newsletter', 'notification']
            ]
        ]);
    }

    public function test()
    {
        $director = new EmailDirector();
        $emails = [];

        // Test Welcome Email
        $welcomeBuilder = new WelcomeEmailBuilder();
        $emails[] = $director->buildWelcomeEmail($welcomeBuilder, 'user@example.com', 'noreply@example.com');

        // Test Newsletter Email
        $newsletterBuilder = new NewsletterEmailBuilder();
        $emails[] = $director->buildNewsletterEmail($newsletterBuilder, 'subscriber@example.com', 'newsletter@example.com', '<h2>Newsletter</h2><p>Contenuto newsletter...</p>');

        // Test Notification Email
        $notificationBuilder = new NotificationEmailBuilder();
        $emails[] = $director->buildNotificationEmail($notificationBuilder, 'admin@example.com', 'system@example.com', 'Notifica di sistema importante.');

        return response()->json([
            'success' => true,
            'message' => 'Builder Test Completed',
            'data' => [
                'emails_created' => count($emails),
                'emails' => array_map(fn($email) => $email->toArray(), $emails)
            ]
        ]);
    }

    public function createEmail(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:welcome,newsletter,notification',
            'recipient' => 'required|email',
            'sender' => 'required|email',
            'subject' => 'nullable|string',
            'body' => 'nullable|string'
        ]);

        $director = new EmailDirector();
        $type = $request->input('type');
        $recipient = $request->input('recipient');
        $sender = $request->input('sender');

        $email = match($type) {
            'welcome' => $director->buildWelcomeEmail(new WelcomeEmailBuilder(), $recipient, $sender),
            'newsletter' => $director->buildNewsletterEmail(new NewsletterEmailBuilder(), $recipient, $sender, $request->input('body', '')),
            'notification' => $director->buildNotificationEmail(new NotificationEmailBuilder(), $recipient, $sender, $request->input('body', '')),
        };

        return response()->json([
            'success' => true,
            'message' => 'Email created successfully',
            'data' => $email->toArray()
        ]);
    }

    public function show()
    {
        return view('builder.example');
    }
}
