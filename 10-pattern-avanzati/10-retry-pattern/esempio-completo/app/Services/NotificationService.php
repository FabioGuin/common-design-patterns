<?php

namespace App\Services;

use App\Retry\RetryManager;
use Illuminate\Support\Str;

class NotificationService
{
    public function __construct(
        private RetryManager $retryManager
    ) {}

    public function sendEmail(string $to, string $subject, string $body): array
    {
        return $this->retryManager->execute('notification_service', function () use ($to, $subject, $body) {
            return $this->callExternalEmailService($to, $subject, $body);
        });
    }

    public function sendSms(string $to, string $message): array
    {
        return $this->retryManager->execute('notification_service', function () use ($to, $message) {
            return $this->callExternalSmsService($to, $message);
        });
    }

    public function sendPushNotification(string $userId, string $title, string $body): array
    {
        return $this->retryManager->execute('notification_service', function () use ($userId, $title, $body) {
            return $this->callExternalPushService($userId, $title, $body);
        });
    }

    private function callExternalEmailService(string $to, string $subject, string $body): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 6) === 1) {
            throw new \Exception("Email service temporarily unavailable", 503);
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

    private function callExternalSmsService(string $to, string $message): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 7) === 1) {
            throw new \Exception("SMS service temporarily unavailable", 502);
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

    private function callExternalPushService(string $userId, string $title, string $body): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 8) === 1) {
            throw new \Exception("Push service temporarily unavailable", 500);
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

    private function simulateExternalCall(): void
    {
        // Simula latenza di rete
        usleep(rand(200000, 800000)); // 200-800ms
    }

    public function getServiceStatus(): array
    {
        return $this->retryManager->getRetryStatus('notification_service') ?? [
            'service_name' => 'notification_service',
            'status' => 'UNKNOWN',
            'message' => 'Retry not initialized'
        ];
    }
}
