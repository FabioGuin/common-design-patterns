<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 120;

    private int $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing order job', ['order_id' => $this->orderId]);

        try {
            $order = Order::find($this->orderId);
            
            if (!$order) {
                throw new \Exception("Order {$this->orderId} not found");
            }

            // Process order steps
            $this->validateOrder($order);
            $this->processPayment($order);
            $this->updateInventory($order);
            $this->sendConfirmation($order);
            $this->notifyWarehouse($order);

            $order->update(['status' => 'processed']);
            
            Log::info('Order processed successfully', ['order_id' => $this->orderId]);

        } catch (\Exception $e) {
            Log::error('Order processing failed', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Validate order
     */
    private function validateOrder(Order $order): void
    {
        Log::info('Validating order', ['order_id' => $this->orderId]);
        
        // Simulate validation delay
        usleep(200000); // 200ms
        
        if ($order->total <= 0) {
            throw new \Exception('Invalid order total');
        }
    }

    /**
     * Process payment
     */
    private function processPayment(Order $order): void
    {
        Log::info('Processing payment', ['order_id' => $this->orderId]);
        
        // Simulate payment processing delay
        usleep(1000000); // 1 second
        
        // Simulate occasional payment failures
        if (rand(1, 20) === 1) {
            throw new \Exception('Payment processing failed');
        }
    }

    /**
     * Update inventory
     */
    private function updateInventory(Order $order): void
    {
        Log::info('Updating inventory', ['order_id' => $this->orderId]);
        
        // Simulate inventory update delay
        usleep(300000); // 300ms
    }

    /**
     * Send confirmation
     */
    private function sendConfirmation(Order $order): void
    {
        Log::info('Sending confirmation', ['order_id' => $this->orderId]);
        
        // Dispatch email job
        SendEmailJob::dispatch(
            $order->customer_email,
            'Order Confirmation',
            "Your order #{$order->id} has been processed successfully.",
            'html'
        );
    }

    /**
     * Notify warehouse
     */
    private function notifyWarehouse(Order $order): void
    {
        Log::info('Notifying warehouse', ['order_id' => $this->orderId]);
        
        // Simulate warehouse notification delay
        usleep(150000); // 150ms
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Order job failed permanently', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update order status to failed
        Order::where('id', $this->orderId)->update(['status' => 'failed']);
    }
}
