<?php

namespace App\Ports;

use App\Domain\Order;

interface NotificationServiceInterface
{
    /**
     * Invia conferma ordine
     */
    public function sendOrderConfirmation(Order $order): array;

    /**
     * Invia notifica di cancellazione ordine
     */
    public function sendOrderCancellation(Order $order): array;

    /**
     * Invia notifica di spedizione
     */
    public function sendShippingNotification(Order $order): array;

    /**
     * Invia notifica di consegna
     */
    public function sendDeliveryNotification(Order $order): array;

    /**
     * Invia notifica personalizzata
     */
    public function sendCustomNotification(Order $order, string $type, array $data = []): array;

    /**
     * Verifica se un canale di notifica è disponibile
     */
    public function isChannelAvailable(string $channel): bool;

    /**
     * Ottiene i canali di notifica disponibili
     */
    public function getAvailableChannels(): array;
}
