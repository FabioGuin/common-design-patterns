<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servizio per la gestione delle notifiche
 * 
 * Questo servizio gestisce l'invio di notifiche per il Saga Orchestration Pattern
 */
class NotificationService
{
    private string $id;
    private array $notifications;
    private int $totalOperations;
    private int $failedOperations;

    public function __construct()
    {
        $this->id = 'notification-service-' . uniqid();
        $this->notifications = [];
        $this->totalOperations = 0;
        $this->failedOperations = 0;
        
        Log::info('NotificationService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Invia una notifica
     */
    public function sendNotification(int $userId, string $message): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'invio della notifica
            $notificationId = 'notification_' . uniqid();
            $this->notifications[$notificationId] = [
                'user_id' => $userId,
                'message' => $message,
                'status' => 'sent',
                'sent_at' => now()->toISOString(),
                'type' => 'email'
            ];
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $notificationId,
                'user_id' => $userId,
                'message' => $message,
                'status' => 'sent',
                'sent_at' => $this->notifications[$notificationId]['sent_at'],
                'duration' => $duration
            ];
            
            Log::info('Notification sent successfully', [
                'service' => $this->id,
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            Log::error('Failed to send notification', [
                'service' => $this->id,
                'user_id' => $userId,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Cancella una notifica
     */
    public function cancelNotification(string $notificationId): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            if (!isset($this->notifications[$notificationId])) {
                throw new Exception("Notification {$notificationId} not found");
            }
            
            $notification = $this->notifications[$notificationId];
            $this->notifications[$notificationId]['status'] = 'cancelled';
            $this->notifications[$notificationId]['cancelled_at'] = now()->toISOString();
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $notificationId,
                'user_id' => $notification['user_id'],
                'message' => $notification['message'],
                'status' => 'cancelled',
                'cancelled_at' => $this->notifications[$notificationId]['cancelled_at'],
                'duration' => $duration
            ];
            
            Log::info('Notification cancelled successfully', [
                'service' => $this->id,
                'notification_id' => $notificationId,
                'user_id' => $notification['user_id'],
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            Log::error('Failed to cancel notification', [
                'service' => $this->id,
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Invia una notifica di conferma
     */
    public function sendConfirmationNotification(int $userId, string $type, array $data): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            $message = $this->getConfirmationMessage($type, $data);
            $notification = $this->sendNotification($userId, $message);
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $notification['id'],
                'user_id' => $userId,
                'type' => $type,
                'message' => $message,
                'status' => $notification['status'],
                'sent_at' => $notification['sent_at'],
                'duration' => $duration
            ];
            
            Log::info('Confirmation notification sent', [
                'service' => $this->id,
                'notification_id' => $notification['id'],
                'user_id' => $userId,
                'type' => $type,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            Log::error('Failed to send confirmation notification', [
                'service' => $this->id,
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Invia una notifica di errore
     */
    public function sendErrorNotification(int $userId, string $error, array $context = []): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            $message = $this->getErrorMessage($error, $context);
            $notification = $this->sendNotification($userId, $message);
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $notification['id'],
                'user_id' => $userId,
                'error' => $error,
                'message' => $message,
                'status' => $notification['status'],
                'sent_at' => $notification['sent_at'],
                'duration' => $duration
            ];
            
            Log::info('Error notification sent', [
                'service' => $this->id,
                'notification_id' => $notification['id'],
                'user_id' => $userId,
                'error' => $error,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            Log::error('Failed to send error notification', [
                'service' => $this->id,
                'user_id' => $userId,
                'error' => $error,
                'context' => $context,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene il messaggio di conferma
     */
    private function getConfirmationMessage(string $type, array $data): string
    {
        switch ($type) {
            case 'order_created':
                return "Your order #{$data['order_id']} has been successfully created and is being processed.";
            
            case 'order_cancelled':
                return "Your order #{$data['order_id']} has been successfully cancelled and refunded.";
            
            case 'payment_processed':
                return "Your payment of \${$data['amount']} has been successfully processed.";
            
            case 'payment_refunded':
                return "Your refund of \${$data['amount']} has been successfully processed.";
            
            default:
                return "Your request has been successfully processed.";
        }
    }

    /**
     * Ottiene il messaggio di errore
     */
    private function getErrorMessage(string $error, array $context): string
    {
        switch ($error) {
            case 'insufficient_inventory':
                return "We apologize, but the requested items are no longer available. Please try again later.";
            
            case 'payment_failed':
                return "We apologize, but there was an issue processing your payment. Please try again or contact support.";
            
            case 'order_creation_failed':
                return "We apologize, but there was an issue creating your order. Please try again or contact support.";
            
            case 'order_cancellation_failed':
                return "We apologize, but there was an issue cancelling your order. Please contact support.";
            
            default:
                return "We apologize, but there was an issue processing your request. Please try again or contact support.";
        }
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        $sentNotifications = count(array_filter($this->notifications, fn($n) => $n['status'] === 'sent'));
        $cancelledNotifications = count(array_filter($this->notifications, fn($n) => $n['status'] === 'cancelled'));
        
        return [
            'id' => $this->id,
            'service' => 'NotificationService',
            'total_operations' => $this->totalOperations,
            'failed_operations' => $this->failedOperations,
            'success_rate' => $this->totalOperations > 0 
                ? round((($this->totalOperations - $this->failedOperations) / $this->totalOperations) * 100, 2)
                : 100,
            'total_notifications' => count($this->notifications),
            'sent_notifications' => $sentNotifications,
            'cancelled_notifications' => $cancelledNotifications,
            'notifications' => $this->notifications
        ];
    }

    /**
     * Ottiene le notifiche per un utente
     */
    public function getNotificationsForUser(int $userId): array
    {
        return array_filter($this->notifications, fn($n) => $n['user_id'] === $userId);
    }

    /**
     * Pulisce le notifiche vecchie
     */
    public function cleanupOldNotifications(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        $deletedCount = 0;
        
        foreach ($this->notifications as $notificationId => $notification) {
            if (isset($notification['sent_at']) && 
                now()->parse($notification['sent_at'])->isBefore($cutoffDate)) {
                unset($this->notifications[$notificationId]);
                $deletedCount++;
            }
        }
        
        Log::info('Cleaned up old notifications', [
            'service' => $this->id,
            'deleted_count' => $deletedCount
        ]);
        
        return $deletedCount;
    }
}
