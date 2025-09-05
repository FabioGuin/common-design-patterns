<?php

namespace App\Listeners\Order;

use App\Events\Order\OrderCreated;
use App\Events\Order\OrderPaid;
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
     * Handle the order created event.
     */
    public function handleOrderCreated(OrderCreated $event): void
    {
        $this->sendOrderConfirmation($event->order, 'created');
    }

    /**
     * Handle the order paid event.
     */
    public function handleOrderPaid(OrderPaid $event): void
    {
        $this->sendOrderConfirmation($event->order, 'paid');
    }

    /**
     * Send order confirmation email.
     */
    private function sendOrderConfirmation($order, string $status): void
    {
        try {
            Log::info('Sending order confirmation email', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'status' => $status,
                'total_amount' => $order->total_amount
            ]);

            // In un'applicazione reale, useresti:
            // Mail::to($order->user->email)->send(new OrderConfirmationEmail($order, $status));

            // Simula invio email
            $this->simulateEmailSending($order, $status);

            Log::info('Order confirmation email sent successfully', [
                'order_id' => $order->id,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Simulate email sending.
     */
    private function simulateEmailSending($order, string $status): void
    {
        // Simula delay per dimostrare l'asincrono
        sleep(2);

        // Simula contenuto email
        $subject = match($status) {
            'created' => "Conferma Ordine #{$order->id}",
            'paid' => "Pagamento Confermato - Ordine #{$order->id}",
            default => "Aggiornamento Ordine #{$order->id}"
        };

        $message = match($status) {
            'created' => "Il tuo ordine per €{$order->total_amount} è stato creato e è in attesa di pagamento.",
            'paid' => "Il pagamento per il tuo ordine di €{$order->total_amount} è stato confermato.",
            default => "Il tuo ordine è stato aggiornato."
        };

        Log::info('Email content generated', [
            'order_id' => $order->id,
            'subject' => $subject,
            'message' => $message
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        Log::error('Order confirmation email job failed', [
            'event' => get_class($event),
            'order_id' => $event->order->id ?? null,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
