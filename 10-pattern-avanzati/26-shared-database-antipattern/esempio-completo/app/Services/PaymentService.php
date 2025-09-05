<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servizio per la gestione dei pagamenti
 * 
 * Questo servizio dimostra i problemi del Shared Database Anti-pattern
 * dove il servizio è fortemente accoppiato al database condiviso.
 */
class PaymentService
{
    private string $id;
    private SharedDatabaseService $sharedDb;
    private array $operationHistory;
    private int $totalOperations;
    private int $failedOperations;

    public function __construct(SharedDatabaseService $sharedDb)
    {
        $this->id = 'payment-service-' . uniqid();
        $this->sharedDb = $sharedDb;
        $this->operationHistory = [];
        $this->totalOperations = 0;
        $this->failedOperations = 0;
        
        Log::info('PaymentService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Crea un nuovo pagamento
     * 
     * Problema: Utilizza il database condiviso, causando accoppiamento forte
     */
    public function createPayment(array $data): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock su multiple tabelle
            $tables = ['payments', 'orders', 'users'];
            foreach ($tables as $table) {
                if (!$this->sharedDb->acquireLock($table, 'write')) {
                    throw new Exception("Failed to acquire lock on $table table");
                }
            }
            
            // Simula la creazione del pagamento
            $payment = new Payment([
                'order_id' => $data['order_id'],
                'user_id' => $data['user_id'],
                'amount' => $data['amount'],
                'method' => $data['method'],
                'status' => 'pending'
            ]);
            $payment->save();
            
            // Rilascia tutti i lock
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'database' => 'shared_database',
                'table' => 'payments',
                'created_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'create_payment',
                'payment_id' => $payment->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Payment created successfully', [
                'service' => $this->id,
                'payment_id' => $payment->id,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            // Rilascia tutti i lock in caso di errore
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $this->operationHistory[] = [
                'operation' => 'create_payment',
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to create payment', [
                'service' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Processa un pagamento
     * 
     * Problema: Modifiche al schema payments impattano altri servizi
     */
    public function processPayment(int $paymentId): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock su multiple tabelle
            $tables = ['payments', 'orders', 'users'];
            foreach ($tables as $table) {
                if (!$this->sharedDb->acquireLock($table, 'write')) {
                    throw new Exception("Failed to acquire lock on $table table");
                }
            }
            
            // Simula il processamento del pagamento
            $payment = Payment::find($paymentId);
            if (!$payment) {
                throw new Exception('Payment not found');
            }
            
            // Simula la verifica del pagamento
            $isPaymentSuccessful = rand(1, 100) <= 85; // 85% di probabilità di successo
            if (!$isPaymentSuccessful) {
                $payment->status = 'failed';
                $payment->error_message = 'Payment processing failed';
            } else {
                $payment->status = 'completed';
                $payment->transaction_id = 'txn_' . uniqid();
                $payment->processed_at = now()->toISOString();
            }
            
            $payment->save();
            
            // Rilascia tutti i lock
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id ?? null,
                'error_message' => $payment->error_message ?? null,
                'database' => 'shared_database',
                'table' => 'payments',
                'processed_at' => $payment->processed_at ?? null,
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'process_payment',
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Payment processed successfully', [
                'service' => $this->id,
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            // Rilascia tutti i lock in caso di errore
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $this->operationHistory[] = [
                'operation' => 'process_payment',
                'payment_id' => $paymentId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to process payment', [
                'service' => $this->id,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Rimborsa un pagamento
     * 
     * Problema: Transazioni complesse che coinvolgono multiple tabelle condivise
     */
    public function refundPayment(int $paymentId, float $amount = null): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock su multiple tabelle
            $tables = ['payments', 'orders', 'users', 'products'];
            foreach ($tables as $table) {
                if (!$this->sharedDb->acquireLock($table, 'write')) {
                    throw new Exception("Failed to acquire lock on $table table");
                }
            }
            
            // Simula il rimborso del pagamento
            $payment = Payment::find($paymentId);
            if (!$payment) {
                throw new Exception('Payment not found');
            }
            
            if ($payment->status !== 'completed') {
                throw new Exception('Payment must be completed to refund');
            }
            
            $refundAmount = $amount ?? $payment->amount;
            if ($refundAmount > $payment->amount) {
                throw new Exception('Refund amount cannot exceed payment amount');
            }
            
            // Simula la creazione del rimborso
            $refund = new Payment([
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => -$refundAmount, // Negativo per rimborso
                'method' => $payment->method,
                'status' => 'refunded',
                'transaction_id' => 'refund_' . uniqid(),
                'processed_at' => now()->toISOString()
            ]);
            $refund->save();
            
            // Rilascia tutti i lock
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'id' => $refund->id,
                'original_payment_id' => $payment->id,
                'order_id' => $refund->order_id,
                'user_id' => $refund->user_id,
                'amount' => $refund->amount,
                'method' => $refund->method,
                'status' => $refund->status,
                'transaction_id' => $refund->transaction_id,
                'database' => 'shared_database',
                'table' => 'payments',
                'processed_at' => $refund->processed_at,
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'refund_payment',
                'payment_id' => $payment->id,
                'refund_id' => $refund->id,
                'amount' => $refundAmount,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            Log::info('Payment refunded successfully', [
                'service' => $this->id,
                'payment_id' => $payment->id,
                'refund_id' => $refund->id,
                'amount' => $refundAmount,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            // Rilascia tutti i lock in caso di errore
            foreach ($tables as $table) {
                $this->sharedDb->releaseLock($table, 'write');
            }
            
            $this->operationHistory[] = [
                'operation' => 'refund_payment',
                'payment_id' => $paymentId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to refund payment', [
                'service' => $this->id,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene un pagamento per ID
     * 
     * Problema: Query su database condiviso con possibili conflitti
     */
    public function getPayment(int $paymentId): ?array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock di lettura
            if (!$this->sharedDb->acquireLock('payments', 'read')) {
                throw new Exception('Failed to acquire read lock on payments table');
            }
            
            // Simula la query
            $payment = Payment::find($paymentId);
            
            $this->sharedDb->releaseLock('payments', 'read');
            
            $duration = microtime(true) - $startTime;
            
            if (!$payment) {
                return null;
            }
            
            $result = [
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'error_message' => $payment->error_message,
                'database' => 'shared_database',
                'table' => 'payments',
                'duration' => $duration
            ];
            
            $this->operationHistory[] = [
                'operation' => 'get_payment',
                'payment_id' => $payment->id,
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true
            ];
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('payments', 'read');
            
            $this->operationHistory[] = [
                'operation' => 'get_payment',
                'payment_id' => $paymentId,
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to get payment', [
                'service' => $this->id,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene tutti i pagamenti
     * 
     * Problema: Query su database condiviso con possibili conflitti
     */
    public function getAllPayments(): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula l'acquisizione di un lock di lettura
            if (!$this->sharedDb->acquireLock('payments', 'read')) {
                throw new Exception('Failed to acquire read lock on payments table');
            }
            
            // Simula la query
            $payments = Payment::all();
            
            $this->sharedDb->releaseLock('payments', 'read');
            
            $duration = microtime(true) - $startTime;
            
            $result = $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'user_id' => $payment->user_id,
                    'amount' => $payment->amount,
                    'method' => $payment->method,
                    'status' => $payment->status,
                    'transaction_id' => $payment->transaction_id,
                    'database' => 'shared_database',
                    'table' => 'payments'
                ];
            })->toArray();
            
            $this->operationHistory[] = [
                'operation' => 'get_all_payments',
                'timestamp' => now()->toISOString(),
                'duration' => $duration,
                'success' => true,
                'count' => count($result)
            ];
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            $this->sharedDb->releaseLock('payments', 'read');
            
            $this->operationHistory[] = [
                'operation' => 'get_all_payments',
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - $startTime,
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to get all payments', [
                'service' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        return [
            'id' => $this->id,
            'service' => 'PaymentService',
            'database' => 'shared_database',
            'table' => 'payments',
            'total_operations' => $this->totalOperations,
            'failed_operations' => $this->failedOperations,
            'success_rate' => $this->totalOperations > 0 
                ? round((($this->totalOperations - $this->failedOperations) / $this->totalOperations) * 100, 2)
                : 100,
            'operation_history' => $this->operationHistory,
            'coupling_level' => 'high', // Alto accoppiamento con database condiviso
            'scalability_issues' => [
                'shared_database' => true,
                'table_locks' => true,
                'schema_dependencies' => true,
                'complex_transactions' => true,
                'cross_service_dependencies' => true,
                'financial_data_coupling' => true
            ]
        ];
    }

    /**
     * Ottiene la cronologia delle operazioni
     */
    public function getOperationHistory(): array
    {
        return $this->operationHistory;
    }
}
