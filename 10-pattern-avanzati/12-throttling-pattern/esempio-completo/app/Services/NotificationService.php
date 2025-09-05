<?php

namespace App\Services;

use App\Throttling\ThrottlingManager;
use Illuminate\Support\Str;

class NotificationService
{
    public function __construct(
        private ThrottlingManager $throttlingManager
    ) {}

    public function sendEmail(string $to, string $subject, string $body, string $userId): array
    {
        return $this->throttlingManager->execute('notification_service', $userId, function () use ($to, $subject, $body) {
            return $this->callExternalEmailService($to, $subject, $body);
        }, 'api/email');
    }

    public function sendSms(string $to, string $message, string $userId): array
    {
        return $this->throttlingManager->execute('notification_service', $userId, function () use ($to, $message) {
            return $this->callExternalSmsService($to, $message);
        }, 'api/sms');
    }

    public function sendPushNotification(string $userId, string $title, string $body): array
    {
        return $this->throttlingManager->execute('notification_service', $userId, function () use ($title, $body) {
            return $this->callExternalPushService($userId, $title, $body);
        }, 'api/push');
    }

    private function callExternalEmailService(string $to, string $subject, string $body): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();

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

    public function getServiceStatus(string $userId): array
    {
        return $this->throttlingManager->getThrottlingStatus('notification_service', $userId, 'api/email') ?? [
            'service_name' => 'notification_service',
            'status' => 'UNKNOWN',
            'message' => 'Throttling not initialized'
        ];
    }
}
