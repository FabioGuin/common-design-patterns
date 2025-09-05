<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepositoryInterface;
use App\Services\PaymentServiceInterface;
use App\Services\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PaymentServiceInterface $paymentService,
        private NotificationServiceInterface $notificationService
    ) {}

    public function createOrder(User $user, array $orderData): Order
    {
        $order = $this->orderRepository->create([
            'user_id' => $user->id,
            'total_amount' => $orderData['total_amount'],
            'status' => Order::STATUS_PENDING,
            'payment_method' => $orderData['payment_method'],
            'shipping_address' => $orderData['shipping_address'],
            'billing_address' => $orderData['billing_address'],
            'notes' => $orderData['notes'] ?? null
        ]);

        Log::info('Order created', ['order_id' => $order->id, 'user_id' => $user->id]);

        return $order;
    }

    public function processPayment(Order $order, array $paymentData): bool
    {
        if (!$order->isPending()) {
            throw new \InvalidArgumentException('Order is not in pending status');
        }

        $paymentResult = $this->paymentService->processPayment($order, $paymentData);

        if ($paymentResult) {
            $this->notificationService->sendOrderConfirmation($order);
            Log::info('Payment processed successfully', ['order_id' => $order->id]);
        } else {
            Log::error('Payment failed', ['order_id' => $order->id]);
        }

        return $paymentResult;
    }

    public function cancelOrder(Order $order, string $reason = null): bool
    {
        if (!$order->canBeCancelled()) {
            throw new \InvalidArgumentException('Order cannot be cancelled');
        }

        $order->cancel();

        if ($order->isPaid()) {
            $this->paymentService->refundPayment($order);
        }

        $this->notificationService->sendOrderUpdate(
            $order, 
            "Ordine cancellato" . ($reason ? ": {$reason}" : "")
        );

        Log::info('Order cancelled', [
            'order_id' => $order->id, 
            'reason' => $reason
        ]);

        return true;
    }

    public function updateOrderStatus(Order $order, string $status): bool
    {
        $oldStatus = $order->status;
        
        switch ($status) {
            case Order::STATUS_PAID:
                $order->markAsPaid();
                break;
            case Order::STATUS_SHIPPED:
                $order->markAsShipped();
                break;
            case Order::STATUS_DELIVERED:
                $order->markAsDelivered();
                break;
            default:
                throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $this->notificationService->sendOrderUpdate(
            $order, 
            "Stato aggiornato da {$oldStatus} a {$status}"
        );

        Log::info('Order status updated', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $status
        ]);

        return true;
    }

    public function getUserOrders(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orderRepository->findByUser($user);
    }

    public function getOrderById(int $orderId): ?Order
    {
        return $this->orderRepository->find($orderId);
    }
}
