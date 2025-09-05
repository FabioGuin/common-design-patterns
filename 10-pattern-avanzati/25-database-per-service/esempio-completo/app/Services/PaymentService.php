<?php

namespace App\Services;

use App\Models\Payment;
use App\Services\EventBusService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    private EventBusService $eventBus;
    private string $connection = 'payment_service';

    public function __construct(EventBusService $eventBus)
    {
        $this->eventBus = $eventBus;
        $this->initializeEventHandlers();
    }

    /**
     * Inizializza i gestori di eventi
     */
    private function initializeEventHandlers(): void
    {
        // Gestisce eventi di creazione ordine
        $this->eventBus->subscribe('OrderCreated', function ($event) {
            $this->handleOrderCreated($event);
        });

        // Gestisce eventi di aggiornamento ordine
        $this->eventBus->subscribe('OrderStatusUpdated', function ($event) {
            $this->handleOrderStatusUpdated($event);
        });
    }

    /**
     * Crea un nuovo pagamento
     */
    public function createPayment(array $paymentData): array
    {
        return DB::connection($this->connection)->transaction(function () use ($paymentData) {
            $payment = new Payment();
            $payment->order_id = $paymentData['order_id'];
            $payment->user_id = $paymentData['user_id'];
            $payment->amount = $paymentData['amount'];
            $payment->method = $paymentData['method'];
            $payment->status = 'pending';
            $payment->transaction_id = $this->generateTransactionId();
            $payment->created_at = now();
            $payment->save();

            // Pubblica evento
            $this->eventBus->publish('PaymentCreated', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'created_at' => $payment->created_at
            ]);

            Log::info("Payment created", [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method
            ]);

            return [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'created_at' => $payment->created_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Ottiene un pagamento per ID
     */
    public function getPayment(int $paymentId): ?array
    {
        $payment = Payment::on($this->connection)->find($paymentId);
        
        if (!$payment) {
            return null;
        }

        return [
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'user_id' => $payment->user_id,
            'amount' => $payment->amount,
            'method' => $payment->method,
            'status' => $payment->status,
            'transaction_id' => $payment->transaction_id,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at,
            'database' => $this->connection
        ];
    }

    /**
     * Ottiene tutti i pagamenti
     */
    public function getAllPayments(): array
    {
        $payments = Payment::on($this->connection)->all();
        
        return $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
                'database' => $this->connection
            ];
        })->toArray();
    }

    /**
     * Processa un pagamento
     */
    public function processPayment(int $paymentId): ?array
    {
        return DB::connection($this->connection)->transaction(function () use ($paymentId) {
            $payment = Payment::on($this->connection)->find($paymentId);
            
            if (!$payment) {
                return null;
            }

            // Simula il processamento del pagamento
            $success = $this->simulatePaymentProcessing($payment);
            
            $payment->status = $success ? 'completed' : 'failed';
            $payment->processed_at = now();
            $payment->updated_at = now();
            $payment->save();

            // Pubblica evento
            $this->eventBus->publish('PaymentProcessed', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'processed_at' => $payment->processed_at
            ]);

            Log::info("Payment processed", [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'status' => $payment->status,
                'success' => $success
            ]);

            return [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'processed_at' => $payment->processed_at,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Rimborsa un pagamento
     */
    public function refundPayment(int $paymentId): ?array
    {
        return DB::connection($this->connection)->transaction(function () use ($paymentId) {
            $payment = Payment::on($this->connection)->find($paymentId);
            
            if (!$payment) {
                return null;
            }

            $payment->status = 'refunded';
            $payment->refunded_at = now();
            $payment->updated_at = now();
            $payment->save();

            // Pubblica evento
            $this->eventBus->publish('PaymentRefunded', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'refunded_at' => $payment->refunded_at
            ]);

            Log::info("Payment refunded", [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'amount' => $payment->amount
            ]);

            return [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'refunded_at' => $payment->refunded_at,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Simula il processamento del pagamento
     */
    private function simulatePaymentProcessing(Payment $payment): bool
    {
        // Simula un tempo di processamento
        usleep(mt_rand(10000, 50000)); // 10-50ms
        
        // Simula un tasso di successo del 90%
        return mt_rand(1, 100) <= 90;
    }

    /**
     * Genera un ID transazione unico
     */
    private function generateTransactionId(): string
    {
        return 'TXN' . strtoupper(uniqid());
    }

    /**
     * Gestisce l'evento di creazione ordine
     */
    private function handleOrderCreated(array $event): void
    {
        $orderId = $event['data']['order_id'];
        $userId = $event['data']['user_id'];
        $total = $event['data']['total'];

        // Crea automaticamente un pagamento per l'ordine
        $this->createPayment([
            'order_id' => $orderId,
            'user_id' => $userId,
            'amount' => $total,
            'method' => 'credit_card'
        ]);

        Log::info("Order created event received", [
            'order_id' => $orderId,
            'user_id' => $userId,
            'total' => $total
        ]);
    }

    /**
     * Gestisce l'evento di aggiornamento stato ordine
     */
    private function handleOrderStatusUpdated(array $event): void
    {
        $orderId = $event['data']['order_id'];
        $newStatus = $event['data']['new_status'];

        Log::info("Order status updated event received", [
            'order_id' => $orderId,
            'new_status' => $newStatus
        ]);

        // In un'implementazione reale, potresti aggiornare lo stato del pagamento
        // in base al nuovo stato dell'ordine
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        $totalPayments = Payment::on($this->connection)->count();
        $totalAmount = Payment::on($this->connection)->sum('amount');
        $statusCounts = Payment::on($this->connection)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'service' => 'PaymentService',
            'database' => $this->connection,
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,
            'status_counts' => $statusCounts,
            'connection_status' => $this->testConnection()
        ];
    }

    /**
     * Testa la connessione al database
     */
    private function testConnection(): bool
    {
        try {
            DB::connection($this->connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Ottiene l'ID del pattern per identificazione
     */
    public function getId(): string
    {
        return 'payment-service-pattern-' . uniqid();
    }
}
