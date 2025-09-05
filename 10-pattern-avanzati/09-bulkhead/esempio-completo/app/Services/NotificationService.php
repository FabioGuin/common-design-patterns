<?php

namespace App\Services;

use App\Bulkhead\BulkheadManager;
use Illuminate\Support\Str;

class NotificationService
{
    public function __construct(
        private BulkheadManager $bulkheadManager
    ) {}

    public function sendEmail(string $to, string $subject, string $body): array
    {
        return $this->bulkheadManager->execute('notification_service', function () use ($to, $subject, $body) {
            return $this->performEmailSend($to, $subject, $body);
        });
    }

    public function sendSms(string $to, string $message): array
    {
        return $this->bulkheadManager->execute('notification_service', function () use ($to, $message) {
            return $this->performSmsSend($to, $message);
        });
    }

    public function sendPushNotification(string $userId, string $title, string $body): array
    {
        return $this->bulkheadManager->execute('notification_service', function () use ($userId, $title, $body) {
            return $this->performPushSend($userId, $title, $body);
        });
    }

    private function performEmailSend(string $to, string $subject, string $body): array
    {
        // Simula invio email non critico
        $this->simulateNonCriticalOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 10) === 1) {
            throw new \Exception("Email sending failed");
        }

        return [
            'message_id' => Str::uuid()->toString(),
            'type' => 'email',
            'to' => $to,
            'subject' => $subject,
            'status' => 'sent',
            'sent_at' => now()->toISOString(),
            'priority' => 'low',
        ];
    }

    private function performSmsSend(string $to, string $message): array
    {
        // Simula invio SMS non critico
        $this->simulateNonCriticalOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 12) === 1) {
            throw new \Exception("SMS sending failed");
        }

        return [
            'message_id' => Str::uuid()->toString(),
            'type' => 'sms',
            'to' => $to,
            'message' => $message,
            'status' => 'sent',
            'sent_at' => now()->toISOString(),
            'priority' => 'low',
        ];
    }

    private function performPushSend(string $userId, string $title, string $body): array
    {
        // Simula invio push non critico
        $this->simulateNonCriticalOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 8) === 1) {
            throw new \Exception("Push notification sending failed");
        }

        return [
            'notification_id' => Str::uuid()->toString(),
            'type' => 'push',
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'status' => 'sent',
            'sent_at' => now()->toISOString(),
            'priority' => 'low',
        ];
    }

    private function simulateNonCriticalOperation(): void
    {
        // Simula operazione non critica con priorità bassa
        usleep(rand(200000, 800000)); // 200-800ms
    }

    public function getServiceStatus(): array
    {
        return $this->bulkheadManager->getBulkheadStatus('notification_service') ?? [
            'service_name' => 'notification_service',
            'status' => 'UNKNOWN',
            'message' => 'Bulkhead not initialized'
        ];
    }
}
