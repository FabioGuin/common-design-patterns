<?php

namespace App\Fallbacks;

use Illuminate\Support\Str;

class NotificationFallback
{
    public function sendEmail(string $to, string $subject, string $body): array
    {
        // Simula invio offline (coda locale)
        usleep(100000); // 100ms

        return [
            'message_id' => Str::uuid()->toString(),
            'type' => 'email',
            'to' => $to,
            'subject' => $subject,
            'status' => 'queued_offline',
            'queued_at' => now()->toISOString(),
            'fallback_reason' => 'Email service unavailable - queued for later delivery',
            'estimated_delivery' => now()->addMinutes(30)->toISOString(),
        ];
    }

    public function sendSms(string $to, string $message): array
    {
        // Simula invio offline (coda locale)
        usleep(100000); // 100ms

        return [
            'message_id' => Str::uuid()->toString(),
            'type' => 'sms',
            'to' => $to,
            'message' => $message,
            'status' => 'queued_offline',
            'queued_at' => now()->toISOString(),
            'fallback_reason' => 'SMS service unavailable - queued for later delivery',
            'estimated_delivery' => now()->addMinutes(15)->toISOString(),
        ];
    }

    public function sendPushNotification(string $userId, string $title, string $body): array
    {
        // Simula invio offline (coda locale)
        usleep(100000); // 100ms

        return [
            'notification_id' => Str::uuid()->toString(),
            'type' => 'push',
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'status' => 'queued_offline',
            'queued_at' => now()->toISOString(),
            'fallback_reason' => 'Push notification service unavailable - queued for later delivery',
            'estimated_delivery' => now()->addMinutes(5)->toISOString(),
        ];
    }
}
