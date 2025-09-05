<?php

namespace App\Sagas;

use App\Models\Saga;
use App\Models\SagaStep;
use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

class OrderSaga
{
    private const STEPS = [
        'reserve_inventory',
        'process_payment',
        'send_confirmation',
        'update_order'
    ];

    private const COMPENSATIONS = [
        'release_inventory',
        'refund_payment',
        'cancel_confirmation',
        'revert_order'
    ];

    public function __construct(
        private InventoryService $inventoryService,
        private PaymentService $paymentService,
        private NotificationService $notificationService,
        private OrderService $orderService
    ) {}

    public function execute(array $orderData): array
    {
        $saga = $this->createSaga($orderData);
        $executedSteps = [];

        try {
            foreach (self::STEPS as $step) {
                $result = $this->executeStep($saga, $step, $orderData);
                $this->recordStep($saga, $step, 'completed', $result);
                $executedSteps[] = $step;
            }

            $this->completeSaga($saga);
            return ['success' => true, 'saga_id' => $saga->id, 'result' => $result];

        } catch (\Exception $e) {
            Log::error("Saga failed: {$e->getMessage()}", [
                'saga_id' => $saga->id,
                'step' => $step ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            $this->compensate($saga, array_reverse($executedSteps));
            return ['success' => false, 'saga_id' => $saga->id, 'error' => $e->getMessage()];
        }
    }

    private function createSaga(array $orderData): Saga
    {
        return Saga::create([
            'type' => 'order_processing',
            'status' => 'running',
            'data' => $orderData,
            'created_at' => now(),
        ]);
    }

    private function executeStep(Saga $saga, string $step, array $orderData): array
    {
        return match ($step) {
            'reserve_inventory' => $this->inventoryService->reserveInventory($orderData),
            'process_payment' => $this->paymentService->processPayment($orderData),
            'send_confirmation' => $this->notificationService->sendConfirmation($orderData),
            'update_order' => $this->orderService->updateOrderStatus($orderData, 'completed'),
            default => throw new \InvalidArgumentException("Unknown step: {$step}")
        };
    }

    private function compensate(Saga $saga, array $steps): void
    {
        $saga->update(['status' => 'compensating']);

        foreach ($steps as $step) {
            try {
                $this->executeCompensation($saga, $step);
                $this->recordStep($saga, $step, 'compensated');
            } catch (\Exception $e) {
                Log::error("Compensation failed: {$e->getMessage()}", [
                    'saga_id' => $saga->id,
                    'step' => $step,
                    'error' => $e->getMessage()
                ]);
                $this->recordStep($saga, $step, 'compensation_failed', ['error' => $e->getMessage()]);
            }
        }

        $saga->update(['status' => 'compensated']);
    }

    private function executeCompensation(Saga $saga, string $step): array
    {
        $orderData = $saga->data;
        $stepData = $this->getStepData($saga, $step);

        return match ($step) {
            'reserve_inventory' => $this->inventoryService->releaseInventory($stepData['reservation_id']),
            'process_payment' => $this->paymentService->refundPayment($stepData['transaction_id']),
            'send_confirmation' => $this->notificationService->cancelConfirmation($stepData['notification_id']),
            'update_order' => $this->orderService->revertOrderStatus($orderData),
            default => throw new \InvalidArgumentException("Unknown compensation: {$step}")
        };
    }

    private function recordStep(Saga $saga, string $step, string $status, array $data = []): void
    {
        SagaStep::create([
            'saga_id' => $saga->id,
            'step_name' => $step,
            'status' => $status,
            'data' => $data,
            'executed_at' => now(),
        ]);
    }

    private function completeSaga(Saga $saga): void
    {
        $saga->update(['status' => 'completed']);
    }

    private function getStepData(Saga $saga, string $step): array
    {
        $sagaStep = SagaStep::where('saga_id', $saga->id)
            ->where('step_name', $step)
            ->where('status', 'completed')
            ->first();

        return $sagaStep ? $sagaStep->data : [];
    }

    public function getSagaStatus(string $sagaId): ?array
    {
        $saga = Saga::find($sagaId);
        if (!$saga) {
            return null;
        }

        $steps = SagaStep::where('saga_id', $sagaId)
            ->orderBy('executed_at')
            ->get()
            ->toArray();

        return [
            'saga' => $saga->toArray(),
            'steps' => $steps
        ];
    }

    public function getAllSagas(): array
    {
        return Saga::with('steps')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
