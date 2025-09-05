<?php

namespace App\Adapters;

use App\Ports\NotificationServiceInterface;
use App\Domain\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService implements NotificationServiceInterface
{
    protected $fromEmail;
    protected $fromName;

    public function __construct()
    {
        $this->fromEmail = config('mail.from.address', 'noreply@example.com');
        $this->fromName = config('mail.from.name', 'E-commerce Store');
    }

    public function sendOrderConfirmation(Order $order): array
    {
        try {
            $subject = "Conferma Ordine #{$order->getId()}";
            $template = 'emails.order-confirmation';
            $data = [
                'order' => $order,
                'customer_name' => $order->getCustomerName(),
                'order_id' => $order->getId(),
                'total_amount' => $order->getTotalAmount(),
                'items' => $order->getItems()
            ];

            // Simula invio email
            $this->simulateEmailSend($order->getCustomerEmail(), $subject, $template, $data);

            Log::info("Email Notification Service: Conferma ordine inviata", [
                'order_id' => $order->getId(),
                'customer_email' => $order->getCustomerEmail()
            ]);

            return [
                'success' => true,
                'type' => 'order_confirmation',
                'recipient' => $order->getCustomerEmail(),
                'subject' => $subject,
                'provider' => 'email'
            ];

        } catch (\Exception $e) {
            Log::error("Email Notification Service: Errore nell'invio conferma ordine", [
                'order_id' => $order->getId(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'email'
            ];
        }
    }

    public function sendOrderCancellation(Order $order): array
    {
        try {
            $subject = "Cancellazione Ordine #{$order->getId()}";
            $template = 'emails.order-cancellation';
            $data = [
                'order' => $order,
                'customer_name' => $order->getCustomerName(),
                'order_id' => $order->getId(),
                'cancellation_reason' => $order->getCancellationReason()
            ];

            // Simula invio email
            $this->simulateEmailSend($order->getCustomerEmail(), $subject, $template, $data);

            Log::info("Email Notification Service: Notifica cancellazione inviata", [
                'order_id' => $order->getId(),
                'customer_email' => $order->getCustomerEmail()
            ]);

            return [
                'success' => true,
                'type' => 'order_cancellation',
                'recipient' => $order->getCustomerEmail(),
                'subject' => $subject,
                'provider' => 'email'
            ];

        } catch (\Exception $e) {
            Log::error("Email Notification Service: Errore nell'invio notifica cancellazione", [
                'order_id' => $order->getId(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'email'
            ];
        }
    }

    public function sendShippingNotification(Order $order): array
    {
        try {
            $subject = "Spedizione Ordine #{$order->getId()}";
            $template = 'emails.order-shipping';
            $data = [
                'order' => $order,
                'customer_name' => $order->getCustomerName(),
                'order_id' => $order->getId(),
                'tracking_number' => $this->generateTrackingNumber()
            ];

            // Simula invio email
            $this->simulateEmailSend($order->getCustomerEmail(), $subject, $template, $data);

            Log::info("Email Notification Service: Notifica spedizione inviata", [
                'order_id' => $order->getId(),
                'customer_email' => $order->getCustomerEmail()
            ]);

            return [
                'success' => true,
                'type' => 'order_shipping',
                'recipient' => $order->getCustomerEmail(),
                'subject' => $subject,
                'provider' => 'email'
            ];

        } catch (\Exception $e) {
            Log::error("Email Notification Service: Errore nell'invio notifica spedizione", [
                'order_id' => $order->getId(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'email'
            ];
        }
    }

    public function sendDeliveryNotification(Order $order): array
    {
        try {
            $subject = "Consegna Ordine #{$order->getId()}";
            $template = 'emails.order-delivery';
            $data = [
                'order' => $order,
                'customer_name' => $order->getCustomerName(),
                'order_id' => $order->getId(),
                'delivery_date' => now()->format('Y-m-d H:i:s')
            ];

            // Simula invio email
            $this->simulateEmailSend($order->getCustomerEmail(), $subject, $template, $data);

            Log::info("Email Notification Service: Notifica consegna inviata", [
                'order_id' => $order->getId(),
                'customer_email' => $order->getCustomerEmail()
            ]);

            return [
                'success' => true,
                'type' => 'order_delivery',
                'recipient' => $order->getCustomerEmail(),
                'subject' => $subject,
                'provider' => 'email'
            ];

        } catch (\Exception $e) {
            Log::error("Email Notification Service: Errore nell'invio notifica consegna", [
                'order_id' => $order->getId(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'email'
            ];
        }
    }

    public function sendCustomNotification(Order $order, string $type, array $data = []): array
    {
        try {
            $subject = "Notifica Ordine #{$order->getId()} - {$type}";
            $template = "emails.order-{$type}";
            $emailData = array_merge([
                'order' => $order,
                'customer_name' => $order->getCustomerName(),
                'order_id' => $order->getId()
            ], $data);

            // Simula invio email
            $this->simulateEmailSend($order->getCustomerEmail(), $subject, $template, $emailData);

            Log::info("Email Notification Service: Notifica personalizzata inviata", [
                'order_id' => $order->getId(),
                'type' => $type,
                'customer_email' => $order->getCustomerEmail()
            ]);

            return [
                'success' => true,
                'type' => $type,
                'recipient' => $order->getCustomerEmail(),
                'subject' => $subject,
                'provider' => 'email'
            ];

        } catch (\Exception $e) {
            Log::error("Email Notification Service: Errore nell'invio notifica personalizzata", [
                'order_id' => $order->getId(),
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'email'
            ];
        }
    }

    public function isChannelAvailable(string $channel): bool
    {
        $availableChannels = ['email', 'sms', 'push'];
        return in_array($channel, $availableChannels);
    }

    public function getAvailableChannels(): array
    {
        return [
            'email' => 'Email',
            'sms' => 'SMS',
            'push' => 'Push Notification'
        ];
    }

    /**
     * Simula l'invio di un'email
     */
    private function simulateEmailSend(string $to, string $subject, string $template, array $data): void
    {
        // Simula latenza di invio
        usleep(200000); // 200ms

        // Simula successo/failure basato su email
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Log::info("Email simulata inviata", [
                'to' => $to,
                'subject' => $subject,
                'template' => $template
            ]);
        } else {
            throw new \Exception("Email non valida: {$to}");
        }
    }

    /**
     * Genera un numero di tracking
     */
    private function generateTrackingNumber(): string
    {
        return 'TRK' . strtoupper(uniqid());
    }
}
