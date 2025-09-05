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
 * Job per compensare un passaggio di una saga
 * 
 * Questo job esegue la compensazione di un passaggio fallito
 * per mantenere la consistenza dei dati.
 */
class CompensateSagaStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $sagaId;
    public int $stepId;
    public string $compensationStep;
    public array $originalResult;
    public int $tries = 3;
    public int $timeout = 300;

    /**
     * Crea una nuova istanza del job
     */
    public function __construct(int $sagaId, int $stepId, string $compensationStep, array $originalResult)
    {
        $this->sagaId = $sagaId;
        $this->stepId = $stepId;
        $this->compensationStep = $compensationStep;
        $this->originalResult = $originalResult;
    }

    /**
     * Esegue il job
     */
    public function handle(SagaOrchestratorService $orchestrator): void
    {
        $saga = Saga::find($this->sagaId);
        $sagaStep = SagaStep::find($this->stepId);

        if (!$saga || !$sagaStep) {
            Log::error('Saga or step not found for compensation', [
                'saga_id' => $this->sagaId,
                'step_id' => $this->stepId
            ]);
            return;
        }

        try {
            Log::info('Compensating saga step', [
                'saga_id' => $this->sagaId,
                'step_id' => $this->stepId,
                'compensation_step' => $this->compensationStep
            ]);

            // Esegue la compensazione specifica
            $result = $this->executeCompensation($this->compensationStep, $this->originalResult);

            // Aggiorna il passaggio con il risultato della compensazione
            $sagaStep->result = array_merge($sagaStep->result ?? [], [
                'compensation' => $result,
                'compensated_at' => now()->toISOString()
            ]);
            $sagaStep->save();

            Log::info('Saga step compensated successfully', [
                'saga_id' => $this->sagaId,
                'step_id' => $this->stepId,
                'compensation_step' => $this->compensationStep
            ]);

        } catch (Exception $e) {
            Log::error('Saga step compensation failed', [
                'saga_id' => $this->sagaId,
                'step_id' => $this->stepId,
                'compensation_step' => $this->compensationStep,
                'error' => $e->getMessage()
            ]);

            // Marca il passaggio come fallito nella compensazione
            $sagaStep->error = $sagaStep->error . ' | Compensation failed: ' . $e->getMessage();
            $sagaStep->save();
        }
    }

    /**
     * Esegue la compensazione specifica
     */
    private function executeCompensation(string $compensationStep, array $originalResult): array
    {
        switch ($compensationStep) {
            case 'unvalidate_user':
                return $this->unvalidateUser($originalResult);
            
            case 'release_inventory':
                return $this->releaseInventory($originalResult);
            
            case 'delete_order':
                return $this->deleteOrder($originalResult);
            
            case 'refund_payment':
                return $this->refundPayment($originalResult);
            
            case 'cancel_notification':
                return $this->cancelNotification($originalResult);
            
            case 'unvalidate_order':
                return $this->unvalidateOrder($originalResult);
            
            case 'recharge_payment':
                return $this->rechargePayment($originalResult);
            
            case 'reserve_inventory':
                return $this->reserveInventory($originalResult);
            
            case 'restore_order':
                return $this->restoreOrder($originalResult);
            
            case 'cancel_cancellation_notification':
                return $this->cancelCancellationNotification($originalResult);
            
            default:
                throw new Exception("Unknown compensation step: {$compensationStep}");
        }
    }

    /**
     * Annulla la validazione dell'utente
     */
    private function unvalidateUser(array $originalResult): array
    {
        // La validazione dell'utente non richiede compensazione
        // poiché è solo una verifica di lettura
        return [
            'action' => 'unvalidate_user',
            'user_id' => $originalResult['user_id'],
            'compensated_at' => now()->toISOString()
        ];
    }

    /**
     * Rilascia l'inventario riservato
     */
    private function releaseInventory(array $originalResult): array
    {
        $inventoryService = new InventoryService();
        $release = $inventoryService->releaseInventory($originalResult['reservation_id']);
        
        return [
            'action' => 'release_inventory',
            'reservation_id' => $originalResult['reservation_id'],
            'product_id' => $originalResult['product_id'],
            'quantity' => $originalResult['quantity'],
            'released_at' => now()->toISOString()
        ];
    }

    /**
     * Elimina l'ordine creato
     */
    private function deleteOrder(array $originalResult): array
    {
        $orderService = new OrderService();
        $orderService->deleteOrder($originalResult['order_id']);
        
        return [
            'action' => 'delete_order',
            'order_id' => $originalResult['order_id'],
            'deleted_at' => now()->toISOString()
        ];
    }

    /**
     * Rimborsa il pagamento processato
     */
    private function refundPayment(array $originalResult): array
    {
        $paymentService = new PaymentService();
        $refund = $paymentService->refundPayment(
            $originalResult['payment_id'], 
            $originalResult['amount']
        );
        
        return [
            'action' => 'refund_payment',
            'payment_id' => $originalResult['payment_id'],
            'refund_id' => $refund['id'],
            'amount' => $originalResult['amount'],
            'refunded_at' => now()->toISOString()
        ];
    }

    /**
     * Cancella la notifica inviata
     */
    private function cancelNotification(array $originalResult): array
    {
        $notificationService = new NotificationService();
        $notificationService->cancelNotification($originalResult['notification_id']);
        
        return [
            'action' => 'cancel_notification',
            'notification_id' => $originalResult['notification_id'],
            'cancelled_at' => now()->toISOString()
        ];
    }

    /**
     * Annulla la validazione dell'ordine
     */
    private function unvalidateOrder(array $originalResult): array
    {
        // La validazione dell'ordine non richiede compensazione
        // poiché è solo una verifica di lettura
        return [
            'action' => 'unvalidate_order',
            'order_id' => $originalResult['order_id'],
            'compensated_at' => now()->toISOString()
        ];
    }

    /**
     * Ricarica il pagamento rimborsato
     */
    private function rechargePayment(array $originalResult): array
    {
        $paymentService = new PaymentService();
        $payment = $paymentService->rechargePayment(
            $originalResult['refund_id'],
            $originalResult['amount']
        );
        
        return [
            'action' => 'recharge_payment',
            'refund_id' => $originalResult['refund_id'],
            'payment_id' => $payment['id'],
            'amount' => $originalResult['amount'],
            'recharged_at' => now()->toISOString()
        ];
    }

    /**
     * Riserva l'inventario rilasciato
     */
    private function reserveInventory(array $originalResult): array
    {
        $inventoryService = new InventoryService();
        $reservation = $inventoryService->reserveInventory(
            $originalResult['product_id'],
            $originalResult['quantity']
        );
        
        return [
            'action' => 'reserve_inventory',
            'product_id' => $originalResult['product_id'],
            'quantity' => $originalResult['quantity'],
            'reservation_id' => $reservation['reservation_id'],
            'reserved_at' => now()->toISOString()
        ];
    }

    /**
     * Ripristina l'ordine cancellato
     */
    private function restoreOrder(array $originalResult): array
    {
        $orderService = new OrderService();
        $order = $orderService->updateOrderStatus($originalResult['order_id'], 'pending');
        
        return [
            'action' => 'restore_order',
            'order_id' => $originalResult['order_id'],
            'status' => $order['status'],
            'restored_at' => now()->toISOString()
        ];
    }

    /**
     * Cancella la notifica di cancellazione
     */
    private function cancelCancellationNotification(array $originalResult): array
    {
        $notificationService = new NotificationService();
        $notificationService->cancelNotification($originalResult['notification_id']);
        
        return [
            'action' => 'cancel_cancellation_notification',
            'notification_id' => $originalResult['notification_id'],
            'cancelled_at' => now()->toISOString()
        ];
    }

    /**
     * Gestisce il fallimento del job
     */
    public function failed(Exception $exception): void
    {
        Log::error('Saga step compensation job failed', [
            'saga_id' => $this->sagaId,
            'step_id' => $this->stepId,
            'compensation_step' => $this->compensationStep,
            'error' => $exception->getMessage()
        ]);

        $sagaStep = SagaStep::find($this->stepId);
        if ($sagaStep) {
            $sagaStep->error = $sagaStep->error . ' | Compensation job failed: ' . $exception->getMessage();
            $sagaStep->save();
        }
    }
}
