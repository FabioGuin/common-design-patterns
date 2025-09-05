<?php

namespace App\Services;

class NotificationService
{
    private array $notifications = [];

    /**
     * Invia una notifica di conferma ordine
     */
    public function sendOrderConfirmation(array $orderData): array
    {
        \Log::info('Sending order confirmation', $orderData);

        $notificationId = 'NOTIF_' . uniqid();
        $message = "Il tuo ordine #{$orderData['order_id']} è stato confermato. Totale: €{$orderData['total']}";

        $notification = [
            'id' => $notificationId,
            'type' => 'order_confirmation',
            'order_id' => $orderData['order_id'],
            'message' => $message,
            'recipient' => $orderData['customer_email'],
            'status' => 'sent',
            'sent_at' => now()->toISOString(),
        ];

        $this->notifications[$notificationId] = $notification;

        return [
            'success' => true,
            'notification_id' => $notificationId,
            'message' => 'Order confirmation sent successfully',
            'recipient' => $orderData['customer_email'],
        ];
    }

    /**
     * Invia una notifica di spedizione
     */
    public function sendShippingNotification(array $shipmentData): array
    {
        \Log::info('Sending shipping notification', $shipmentData);

        $notificationId = 'NOTIF_' . uniqid();
        $message = "Il tuo ordine è stato spedito. Numero di tracking: {$shipmentData['tracking_number']}";

        $notification = [
            'id' => $notificationId,
            'type' => 'shipping_notification',
            'order_id' => $shipmentData['order_id'],
            'message' => $message,
            'recipient' => $shipmentData['customer_email'],
            'tracking_number' => $shipmentData['tracking_number'],
            'status' => 'sent',
            'sent_at' => now()->toISOString(),
        ];

        $this->notifications[$notificationId] = $notification;

        return [
            'success' => true,
            'notification_id' => $notificationId,
            'message' => 'Shipping notification sent successfully',
            'recipient' => $shipmentData['customer_email'],
        ];
    }

    /**
     * Invia una notifica di consegna
     */
    public function sendDeliveryNotification(array $deliveryData): array
    {
        \Log::info('Sending delivery notification', $deliveryData);

        $notificationId = 'NOTIF_' . uniqid();
        $message = "Il tuo ordine è stato consegnato. Speriamo che tu sia soddisfatto del tuo acquisto!";

        $notification = [
            'id' => $notificationId,
            'type' => 'delivery_notification',
            'order_id' => $deliveryData['order_id'],
            'message' => $message,
            'recipient' => $deliveryData['customer_email'],
            'status' => 'sent',
            'sent_at' => now()->toISOString(),
        ];

        $this->notifications[$notificationId] = $notification;

        return [
            'success' => true,
            'notification_id' => $notificationId,
            'message' => 'Delivery notification sent successfully',
            'recipient' => $deliveryData['customer_email'],
        ];
    }

    /**
     * Invia una notifica di rimborso
     */
    public function sendRefundNotification(array $refundData): array
    {
        \Log::info('Sending refund notification', $refundData);

        $notificationId = 'NOTIF_' . uniqid();
        $message = "Il rimborso per l'ordine #{$refundData['order_id']} è stato processato. Importo: €{$refundData['refund_amount']}";

        $notification = [
            'id' => $notificationId,
            'type' => 'refund_notification',
            'order_id' => $refundData['order_id'],
            'message' => $message,
            'recipient' => $refundData['customer_email'],
            'refund_amount' => $refundData['refund_amount'],
            'status' => 'sent',
            'sent_at' => now()->toISOString(),
        ];

        $this->notifications[$notificationId] = $notification;

        return [
            'success' => true,
            'notification_id' => $notificationId,
            'message' => 'Refund notification sent successfully',
            'recipient' => $refundData['customer_email'],
        ];
    }

    /**
     * Ottiene tutte le notifiche
     */
    public function getAllNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * Ottiene le notifiche per un ordine specifico
     */
    public function getNotificationsForOrder(string $orderId): array
    {
        return array_filter($this->notifications, function ($notification) use ($orderId) {
            return $notification['order_id'] === $orderId;
        });
    }

    /**
     * Ottiene le notifiche per tipo
     */
    public function getNotificationsByType(string $type): array
    {
        return array_filter($this->notifications, function ($notification) use ($type) {
            return $notification['type'] === $type;
        });
    }

    /**
     * Ottiene le statistiche delle notifiche
     */
    public function getNotificationStats(): array
    {
        $total = count($this->notifications);
        $byType = [];

        foreach ($this->notifications as $notification) {
            $type = $notification['type'];
            $byType[$type] = ($byType[$type] ?? 0) + 1;
        }

        return [
            'total_notifications' => $total,
            'by_type' => $byType,
            'last_notification' => $total > 0 ? max(array_column($this->notifications, 'sent_at')) : null,
        ];
    }
}
