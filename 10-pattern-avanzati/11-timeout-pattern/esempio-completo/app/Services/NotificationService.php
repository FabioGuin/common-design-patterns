<?php

namespace App\Services;

use App\Timeout\TimeoutManager;
use Illuminate\Support\Str;

class NotificationService
{
    public function __construct(
        private TimeoutManager $timeoutManager
    ) {}

    public function sendEmail(string $to, string $subject, string $body): array
    {
        return $this->timeoutManager->execute('notification_service', function () use ($to, $subject, $body) {
            return $this->callExternalEmailService($to, $subject, $body);
        });
    }

    public function sendSms(string $to, string $message): array
    {
        return $this->timeoutManager->execute('notification_service', function () use ($to, $message) {
            return $this->callExternalSmsService($to, $message);
        });
    }

    public function sendPushNotification(string $userId, string $title, string $body): array
    {
        return $this->timeoutManager->execute('notification_service', function () use ($userId, $title, $body) {
            return $this->callExternalPushService($userId, $title, $body);
        });
    }

    private function callExternalEmailService(string $to, string $subject, string $body): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula operazione lenta per testing
        if (rand(1, 8) === 1) {
            $this->simulateSlowOperation();
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
        
        // Simula operazione lenta per testing
        if (rand(1, 9) === 1) {
            $this->simulateSlowOperation();
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
        
        // Simula operazione lenta per testing
        if (rand(1, 10) === 1) {
            $this->simulateSlowOperation();
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
        // Simula latenza di rete normale
        usleep(rand(200000, 800000)); // 200-800ms
    }

    private function simulateSlowOperation(): void
    {
        // Simula operazione lenta che causerà timeout
        usleep(rand(10000000, 20000000)); // 10-20 secondi
    }

    public function getServiceStatus(): array
    {
        return $this->timeoutManager->getTimeoutStatus('notification_service') ?? [
            'service_name' => 'notification_service',
            'status' => 'UNKNOWN',
            'message' => 'Timeout not initialized'
        ];
    }
}
