<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;

interface NotificationServiceInterface
{
    public function sendEmail(User $user, string $subject, string $message): bool;
    public function sendSms(User $user, string $message): bool;
    public function sendOrderConfirmation(Order $order): bool;
    public function sendOrderUpdate(Order $order, string $message): bool;
}

class NotificationService implements NotificationServiceInterface
{
    private string $emailUrl;
    private string $smsUrl;

    public function __construct()
    {
        $this->emailUrl = config('services.notification.email_url');
        $this->smsUrl = config('services.notification.sms_url');
    }

    public function sendEmail(User $user, string $subject, string $message): bool
    {
        if (empty($user->email)) {
            return false;
        }

        $response = $this->callEmailService([
            'to' => $user->email,
            'subject' => $subject,
            'message' => $message,
            'user_name' => $user->name
        ]);

        return $response['success'] ?? false;
    }

    public function sendSms(User $user, string $message): bool
    {
        if (!$user->hasPhone()) {
            return false;
        }

        $response = $this->callSmsService([
            'to' => $user->phone,
            'message' => $message,
            'user_name' => $user->name
        ]);

        return $response['success'] ?? false;
    }

    public function sendOrderConfirmation(Order $order): bool
    {
        $subject = "Conferma Ordine #{$order->id}";
        $message = "Il tuo ordine per €{$order->total_amount} è stato confermato.";

        return $this->sendEmail($order->user, $subject, $message);
    }

    public function sendOrderUpdate(Order $order, string $message): bool
    {
        $subject = "Aggiornamento Ordine #{$order->id}";
        $fullMessage = "Il tuo ordine è stato aggiornato: {$message}";

        $emailSent = $this->sendEmail($order->user, $subject, $fullMessage);
        $smsSent = $this->sendSms($order->user, $fullMessage);

        return $emailSent || $smsSent;
    }

    private function callEmailService(array $data): array
    {
        // Simula chiamata al servizio email
        return [
            'success' => true,
            'message_id' => 'msg_' . uniqid(),
            'delivered_at' => now()->toISOString()
        ];
    }

    private function callSmsService(array $data): array
    {
        // Simula chiamata al servizio SMS
        return [
            'success' => true,
            'message_id' => 'sms_' . uniqid(),
            'delivered_at' => now()->toISOString()
        ];
    }
}
