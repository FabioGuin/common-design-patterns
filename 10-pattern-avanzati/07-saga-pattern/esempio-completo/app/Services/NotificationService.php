<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    public function sendConfirmation(array $orderData): array
    {
        $orderId = $orderData['order_id'];
        $customerEmail = $orderData['customer_email'] ?? 'customer@example.com';

        // Simula invio email
        $notification = Notification::create([
            'notification_id' => Str::uuid()->toString(),
            'order_id' => $orderId,
            'type' => 'order_confirmation',
            'recipient' => $customerEmail,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Simula fallimento casuale per testing
        if (rand(1, 15) === 1) {
            throw new \Exception("Email service unavailable");
        }

        return [
            'notification_id' => $notification->notification_id,
            'type' => 'order_confirmation',
            'recipient' => $customerEmail,
            'status' => 'sent'
        ];
    }

    public function cancelConfirmation(string $notificationId): array
    {
        $notification = Notification::where('notification_id', $notificationId)->first();
        
        if (!$notification) {
            throw new \Exception("Notification not found: {$notificationId}");
        }

        if ($notification->status !== 'sent') {
            throw new \Exception("Notification not sent, cannot cancel: {$notificationId}");
        }

        // Simula cancellazione
        $notification->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return [
            'notification_id' => $notificationId,
            'status' => 'cancelled'
        ];
    }

    public function sendNotification(string $orderId, string $type, string $recipient): array
    {
        $notification = Notification::create([
            'notification_id' => Str::uuid()->toString(),
            'order_id' => $orderId,
            'type' => $type,
            'recipient' => $recipient,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return [
            'notification_id' => $notification->notification_id,
            'type' => $type,
            'recipient' => $recipient,
            'status' => 'sent'
        ];
    }

    public function getNotification(string $notificationId): ?Notification
    {
        return Notification::where('notification_id', $notificationId)->first();
    }

    public function getAllNotifications(): array
    {
        return Notification::orderBy('sent_at', 'desc')->get()->toArray();
    }

    public function getNotificationsByOrder(string $orderId): array
    {
        return Notification::where('order_id', $orderId)->get()->toArray();
    }
}
