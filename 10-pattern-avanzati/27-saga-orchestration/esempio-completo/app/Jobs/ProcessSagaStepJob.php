<?php

namespace App\Jobs;

use App\Models\Saga;
use App\Models\SagaStep;
use App\Services\SagaOrchestratorService;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Job per processare un passaggio di una saga
 * 
 * Questo job esegue un singolo passaggio di una saga e gestisce
 * il risultato o l'errore del passaggio.
 */
class ProcessSagaStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $sagaId;
    public int $stepId;
    public string $stepName;
    public array $data;
    public int $tries = 3;
    public int $timeout = 300;

    /**
     * Crea una nuova istanza del job
     */
    public function __construct(int $sagaId, int $stepId, string $stepName, array $data)
    {
        $this->sagaId = $sagaId;
        $this->stepId = $stepId;
        $this->stepName = $stepName;
        $this->data = $data;
    }

    /**
     * Esegue il job
     */
    public function handle(SagaOrchestratorService $orchestrator): void
    {
        $saga = Saga::find($this->sagaId);
        $sagaStep = SagaStep::find($this->stepId);

        if (!$saga || !$sagaStep) {
            Log::error('Saga or step not found', [
                'saga_id' => $this->sagaId,
                'step_id' => $this->stepId
            ]);
            return;
        }

        // Marca il passaggio come in esecuzione
        $sagaStep->markAsRunning();

        try {
            Log::info('Processing saga step', [
                'saga_id' => $this->sagaId,
                'step_id' => $this->stepId,
                'step_name' => $this->stepName
            ]);

            // Esegue il passaggio specifico
            $result = $this->executeStep($this->stepName, $this->data);

            // Marca il passaggio come completato
            $sagaStep->markAsCompleted($result);

            // Notifica l'orchestratore
            $orchestrator->completeStep($this->sagaId, $this->stepId, $result);

            Log::info('Saga step completed successfully', [
                'saga_id' => $this->sagaId,
                'step_id' => $this->stepId,
                'step_name' => $this->stepName
            ]);

        } catch (Exception $e) {
            Log::error('Saga step failed', [
                'saga_id' => $this->sagaId,
                'step_id' => $this->stepId,
                'step_name' => $this->stepName,
                'error' => $e->getMessage()
            ]);

            // Marca il passaggio come fallito
            $sagaStep->markAsFailed($e->getMessage());

            // Notifica l'orchestratore
            $orchestrator->failStep($this->sagaId, $this->stepId, $e->getMessage());
        }
    }

    /**
     * Esegue il passaggio specifico della saga
     */
    private function executeStep(string $stepName, array $data): array
    {
        switch ($stepName) {
            case 'validate_user':
                return $this->validateUser($data);
            
            case 'reserve_inventory':
                return $this->reserveInventory($data);
            
            case 'create_order':
                return $this->createOrder($data);
            
            case 'process_payment':
                return $this->processPayment($data);
            
            case 'send_notification':
                return $this->sendNotification($data);
            
            case 'validate_order':
                return $this->validateOrder($data);
            
            case 'refund_payment':
                return $this->refundPayment($data);
            
            case 'release_inventory':
                return $this->releaseInventory($data);
            
            case 'cancel_order':
                return $this->cancelOrder($data);
            
            case 'send_cancellation_notification':
                return $this->sendCancellationNotification($data);
            
            default:
                throw new Exception("Unknown step: {$stepName}");
        }
    }

    /**
     * Valida l'utente
     */
    private function validateUser(array $data): array
    {
        $userService = new UserService();
        $user = $userService->getUser($data['user_id']);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        return [
            'user_id' => $user['id'],
            'user_name' => $user['name'],
            'user_email' => $user['email'],
            'validated_at' => now()->toISOString()
        ];
    }

    /**
     * Riserva l'inventario
     */
    private function reserveInventory(array $data): array
    {
        $inventoryService = new InventoryService();
        $reservation = $inventoryService->reserveInventory($data['product_id'], $data['quantity']);
        
        return [
            'product_id' => $reservation['product_id'],
            'quantity' => $reservation['quantity'],
            'reservation_id' => $reservation['reservation_id'],
            'reserved_at' => now()->toISOString()
        ];
    }

    /**
     * Crea l'ordine
     */
    private function createOrder(array $data): array
    {
        $orderService = new OrderService();
        $order = $orderService->createOrder($data);
        
        return [
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'total' => $order['total'],
            'status' => $order['status'],
            'created_at' => now()->toISOString()
        ];
    }

    /**
     * Processa il pagamento
     */
    private function processPayment(array $data): array
    {
        $paymentService = new PaymentService();
        $payment = $paymentService->processPayment($data['payment_id']);
        
        return [
            'payment_id' => $payment['id'],
            'order_id' => $payment['order_id'],
            'amount' => $payment['amount'],
            'status' => $payment['status'],
            'transaction_id' => $payment['transaction_id'],
            'processed_at' => now()->toISOString()
        ];
    }

    /**
     * Invia la notifica
     */
    private function sendNotification(array $data): array
    {
        $notificationService = new NotificationService();
        $notification = $notificationService->sendNotification($data['user_id'], $data['message']);
        
        return [
            'notification_id' => $notification['id'],
            'user_id' => $notification['user_id'],
            'message' => $notification['message'],
            'sent_at' => now()->toISOString()
        ];
    }

    /**
     * Valida l'ordine
     */
    private function validateOrder(array $data): array
    {
        $orderService = new OrderService();
        $order = $orderService->getOrder($data['order_id']);
        
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        if ($order['status'] !== 'pending') {
            throw new Exception('Order cannot be cancelled');
        }
        
        return [
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'total' => $order['total'],
            'status' => $order['status'],
            'validated_at' => now()->toISOString()
        ];
    }

    /**
     * Rimborsa il pagamento
     */
    private function refundPayment(array $data): array
    {
        $paymentService = new PaymentService();
        $refund = $paymentService->refundPayment($data['payment_id'], $data['amount']);
        
        return [
            'refund_id' => $refund['id'],
            'payment_id' => $refund['original_payment_id'],
            'amount' => $refund['amount'],
            'status' => $refund['status'],
            'transaction_id' => $refund['transaction_id'],
            'refunded_at' => now()->toISOString()
        ];
    }

    /**
     * Rilascia l'inventario
     */
    private function releaseInventory(array $data): array
    {
        $inventoryService = new InventoryService();
        $release = $inventoryService->releaseInventory($data['reservation_id']);
        
        return [
            'reservation_id' => $release['reservation_id'],
            'product_id' => $release['product_id'],
            'quantity' => $release['quantity'],
            'released_at' => now()->toISOString()
        ];
    }

    /**
     * Cancella l'ordine
     */
    private function cancelOrder(array $data): array
    {
        $orderService = new OrderService();
        $order = $orderService->updateOrderStatus($data['order_id'], 'cancelled');
        
        return [
            'order_id' => $order['id'],
            'status' => $order['status'],
            'cancelled_at' => now()->toISOString()
        ];
    }

    /**
     * Invia la notifica di cancellazione
     */
    private function sendCancellationNotification(array $data): array
    {
        $notificationService = new NotificationService();
        $notification = $notificationService->sendNotification(
            $data['user_id'], 
            'Your order has been cancelled'
        );
        
        return [
            'notification_id' => $notification['id'],
            'user_id' => $notification['user_id'],
            'message' => $notification['message'],
            'sent_at' => now()->toISOString()
        ];
    }

    /**
     * Gestisce il fallimento del job
     */
    public function failed(Exception $exception): void
    {
        Log::error('Saga step job failed', [
            'saga_id' => $this->sagaId,
            'step_id' => $this->stepId,
            'step_name' => $this->stepName,
            'error' => $exception->getMessage()
        ]);

        $sagaStep = SagaStep::find($this->stepId);
        if ($sagaStep) {
            $sagaStep->markAsFailed($exception->getMessage());
        }
    }
}
