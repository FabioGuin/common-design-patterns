<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        try {
            Log::info('Sending order confirmation', [
                'order_id' => $event->orderData['id'],
                'customer_name' => $event->orderData['customer_name']
            ]);

            // Simula l'invio dell'email di conferma
            $this->sendConfirmationEmail($event->orderData);

            Log::info('Order confirmation sent successfully', [
                'order_id' => $event->orderData['id']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation', [
                'order_id' => $event->orderData['id'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Simula l'invio dell'email di conferma
     */
    private function sendConfirmationEmail(array $orderData): void
    {
        // In un sistema reale, qui useresti Mail::send()
        Log::info('Email sent to customer', [
            'to' => $orderData['customer_email'] ?? 'customer@example.com',
            'subject' => 'Conferma Ordine #' . $orderData['id'],
            'body' => "Gentile {$orderData['customer_name']}, il tuo ordine #{$orderData['id']} è stato creato con successo."
        ]);

        // Simula delay di invio email
        usleep(rand(100000, 500000)); // 100-500ms
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error('Order confirmation sending failed', [
            'order_id' => $event->orderData['id'],
            'error' => $exception->getMessage()
        ]);
    }
}
