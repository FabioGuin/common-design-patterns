<?php

namespace App\Listeners;

use App\Events\PaymentProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentNotification implements ShouldQueue
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
    public function handle(PaymentProcessed $event): void
    {
        try {
            Log::info('Sending payment notification', [
                'payment_id' => $event->paymentData['payment_id'],
                'order_id' => $event->paymentData['order_id']
            ]);

            // Simula l'invio della notifica di pagamento
            $this->sendPaymentEmail($event->paymentData);

            Log::info('Payment notification sent successfully', [
                'payment_id' => $event->paymentData['payment_id']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payment notification', [
                'payment_id' => $event->paymentData['payment_id'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Simula l'invio dell'email di notifica pagamento
     */
    private function sendPaymentEmail(array $paymentData): void
    {
        // In un sistema reale, qui useresti Mail::send()
        Log::info('Payment notification email sent', [
            'to' => 'customer@example.com',
            'subject' => 'Pagamento Processato - Ordine #' . $paymentData['order_id'],
            'body' => "Il pagamento per l'ordine #{$paymentData['order_id']} è stato processato con successo. Importo: €{$paymentData['amount']}"
        ]);

        // Simula delay di invio email
        usleep(rand(100000, 500000)); // 100-500ms
    }

    /**
     * Handle a job failure.
     */
    public function failed(PaymentProcessed $event, \Throwable $exception): void
    {
        Log::error('Payment notification sending failed', [
            'payment_id' => $event->paymentData['payment_id'],
            'error' => $exception->getMessage()
        ]);
    }
}
