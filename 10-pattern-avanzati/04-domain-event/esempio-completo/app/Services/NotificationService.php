<?php

namespace App\Services;

/**
 * Servizio per gestire le notifiche
 * 
 * Gestisce l'invio di email, SMS e notifiche push
 * ai clienti per vari eventi del sistema.
 */
class NotificationService
{
    private array $sentNotifications = [];

    /**
     * Invia email di conferma ordine
     */
    public function sendOrderConfirmationEmail(
        string $customerId,
        string $orderId,
        float $total,
        array $items
    ): void {
        $this->logNotification('email', 'order_confirmation', [
            'customerId' => $customerId,
            'orderId' => $orderId,
            'total' => $total,
            'items' => $items
        ]);
    }

    /**
     * Invia email di cancellazione ordine
     */
    public function sendOrderCancellationEmail(
        string $customerId,
        string $orderId,
        float $total,
        string $reason
    ): void {
        $this->logNotification('email', 'order_cancellation', [
            'customerId' => $customerId,
            'orderId' => $orderId,
            'total' => $total,
            'reason' => $reason
        ]);
    }

    /**
     * Invia notifica di spedizione
     */
    public function sendShippingNotification(
        string $customerId,
        string $orderId,
        string $trackingNumber,
        string $carrier,
        \DateTime $estimatedDelivery
    ): void {
        $this->logNotification('email', 'shipping_notification', [
            'customerId' => $customerId,
            'orderId' => $orderId,
            'trackingNumber' => $trackingNumber,
            'carrier' => $carrier,
            'estimatedDelivery' => $estimatedDelivery->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Invia notifica di pagamento processato
     */
    public function sendPaymentConfirmationEmail(
        string $customerId,
        string $orderId,
        float $amount,
        string $paymentMethod
    ): void {
        $this->logNotification('email', 'payment_confirmation', [
            'customerId' => $customerId,
            'orderId' => $orderId,
            'amount' => $amount,
            'paymentMethod' => $paymentMethod
        ]);
    }

    /**
     * Invia notifica di pagamento fallito
     */
    public function sendPaymentFailedEmail(
        string $customerId,
        string $orderId,
        float $amount,
        string $reason
    ): void {
        $this->logNotification('email', 'payment_failed', [
            'customerId' => $customerId,
            'orderId' => $orderId,
            'amount' => $amount,
            'reason' => $reason
        ]);
    }

    /**
     * Invia SMS
     */
    public function sendSms(string $phoneNumber, string $message): void
    {
        $this->logNotification('sms', 'generic', [
            'phoneNumber' => $phoneNumber,
            'message' => $message
        ]);
    }

    /**
     * Invia notifica push
     */
    public function sendPushNotification(
        string $deviceToken,
        string $title,
        string $body
    ): void {
        $this->logNotification('push', 'generic', [
            'deviceToken' => $deviceToken,
            'title' => $title,
            'body' => $body
        ]);
    }

    /**
     * Restituisce tutte le notifiche inviate
     */
    public function getSentNotifications(): array
    {
        return $this->sentNotifications;
    }

    /**
     * Restituisce le notifiche per tipo
     */
    public function getNotificationsByType(string $type): array
    {
        return array_filter($this->sentNotifications, function ($notification) use ($type) {
            return $notification['type'] === $type;
        });
    }

    /**
     * Restituisce le notifiche per customer
     */
    public function getNotificationsByCustomer(string $customerId): array
    {
        return array_filter($this->sentNotifications, function ($notification) use ($customerId) {
            return isset($notification['data']['customerId']) && 
                   $notification['data']['customerId'] === $customerId;
        });
    }

    /**
     * Pulisce le notifiche inviate
     */
    public function clearNotifications(): void
    {
        $this->sentNotifications = [];
    }

    /**
     * Restituisce statistiche delle notifiche
     */
    public function getStatistics(): array
    {
        $stats = [
            'total' => count($this->sentNotifications),
            'byType' => [],
            'byChannel' => []
        ];

        foreach ($this->sentNotifications as $notification) {
            $type = $notification['type'];
            $channel = $notification['channel'];

            if (!isset($stats['byType'][$type])) {
                $stats['byType'][$type] = 0;
            }
            $stats['byType'][$type]++;

            if (!isset($stats['byChannel'][$channel])) {
                $stats['byChannel'][$channel] = 0;
            }
            $stats['byChannel'][$channel]++;
        }

        return $stats;
    }

    /**
     * Logga una notifica
     */
    private function logNotification(string $channel, string $type, array $data): void
    {
        $this->sentNotifications[] = [
            'channel' => $channel,
            'type' => $type,
            'data' => $data,
            'sentAt' => (new \DateTime())->format('Y-m-d H:i:s')
        ];
    }
}
