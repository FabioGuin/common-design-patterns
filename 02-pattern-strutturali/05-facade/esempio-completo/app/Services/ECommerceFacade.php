<?php

namespace App\Services;

class ECommerceFacade
{
    private InventoryService $inventoryService;
    private PaymentService $paymentService;
    private ShippingService $shippingService;
    private NotificationService $notificationService;
    private ReportingService $reportingService;

    public function __construct(
        InventoryService $inventoryService,
        PaymentService $paymentService,
        ShippingService $shippingService,
        NotificationService $notificationService,
        ReportingService $reportingService
    ) {
        $this->inventoryService = $inventoryService;
        $this->paymentService = $paymentService;
        $this->shippingService = $shippingService;
        $this->notificationService = $notificationService;
        $this->reportingService = $reportingService;
    }

    /**
     * Processa un ordine completo
     */
    public function processOrder(array $orderData): array
    {
        \Log::info('Processing order through facade', $orderData);

        try {
            // 1. Verifica disponibilità inventario
            $inventoryCheck = $this->inventoryService->checkStock($orderData['product_id']);
            if (!$inventoryCheck['available']) {
                return [
                    'success' => false,
                    'message' => 'Product not available',
                    'inventory_check' => $inventoryCheck,
                ];
            }

            // 2. Riserva il prodotto
            $reservation = $this->inventoryService->reserveItem($orderData['product_id'], $orderData['quantity']);
            if (!$reservation['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to reserve product',
                    'reservation' => $reservation,
                ];
            }

            // 3. Processa il pagamento
            $payment = $this->paymentService->processPayment($orderData['payment']);
            if (!$payment['success']) {
                // Rilascia la riserva se il pagamento fallisce
                $this->inventoryService->releaseReservation($orderData['product_id'], $orderData['quantity']);
                return [
                    'success' => false,
                    'message' => 'Payment failed',
                    'payment' => $payment,
                ];
            }

            // 4. Crea la spedizione
            $shipment = $this->shippingService->createShipment([
                'order_id' => $orderData['order_id'],
                'shipping_address' => $orderData['shipping_address'],
            ]);
            if (!$shipment['success']) {
                // Rilascia la riserva e rimborsa se la spedizione fallisce
                $this->inventoryService->releaseReservation($orderData['product_id'], $orderData['quantity']);
                $this->paymentService->refundPayment($payment['payment_id']);
                return [
                    'success' => false,
                    'message' => 'Shipping failed',
                    'shipment' => $shipment,
                ];
            }

            // 5. Invia notifiche
            $this->notificationService->sendOrderConfirmation([
                'order_id' => $orderData['order_id'],
                'customer_email' => $orderData['customer_email'],
                'total' => $payment['total'],
            ]);

            $this->notificationService->sendShippingNotification([
                'order_id' => $orderData['order_id'],
                'customer_email' => $orderData['customer_email'],
                'tracking_number' => $shipment['tracking_number'],
            ]);

            return [
                'success' => true,
                'message' => 'Order processed successfully',
                'order_id' => $orderData['order_id'],
                'payment_id' => $payment['payment_id'],
                'shipment_id' => $shipment['shipment_id'],
                'tracking_number' => $shipment['tracking_number'],
                'total' => $payment['total'],
            ];

        } catch (\Exception $e) {
            \Log::error('Error processing order', [
                'order_id' => $orderData['order_id'],
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while processing the order',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancella un ordine
     */
    public function cancelOrder(string $orderId, array $orderData): array
    {
        \Log::info('Cancelling order through facade', ['order_id' => $orderId]);

        try {
            // 1. Rilascia la riserva dell'inventario
            $releaseResult = $this->inventoryService->releaseReservation(
                $orderData['product_id'],
                $orderData['quantity']
            );

            // 2. Rimborsa il pagamento
            $refundResult = $this->paymentService->refundPayment($orderData['payment_id']);

            // 3. Aggiorna lo stato della spedizione
            $shipmentUpdate = $this->shippingService->updateShipmentStatus(
                $orderData['shipment_id'],
                'cancelled'
            );

            // 4. Invia notifica di rimborso
            $this->notificationService->sendRefundNotification([
                'order_id' => $orderId,
                'customer_email' => $orderData['customer_email'],
                'refund_amount' => $refundResult['refund_amount'] ?? 0,
            ]);

            return [
                'success' => true,
                'message' => 'Order cancelled successfully',
                'order_id' => $orderId,
                'inventory_released' => $releaseResult['success'],
                'payment_refunded' => $refundResult['success'],
                'shipment_cancelled' => $shipmentUpdate['success'],
            ];

        } catch (\Exception $e) {
            \Log::error('Error cancelling order', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while cancelling the order',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Ottiene le informazioni di un ordine
     */
    public function getOrderInfo(string $orderId): array
    {
        \Log::info('Getting order info through facade', ['order_id' => $orderId]);

        try {
            // Simula la raccolta di informazioni da tutti i servizi
            $inventory = $this->inventoryService->getAllProducts();
            $payments = $this->paymentService->getAllPayments();
            $shipments = $this->shippingService->getAllShipments();
            $notifications = $this->notificationService->getAllNotifications();

            return [
                'success' => true,
                'order_id' => $orderId,
                'inventory_count' => count($inventory),
                'payments_count' => count($payments),
                'shipments_count' => count($shipments),
                'notifications_count' => count($notifications),
                'message' => 'Order information retrieved successfully',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving order information',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Genera un report completo
     */
    public function generateCompleteReport(): array
    {
        \Log::info('Generating complete report through facade');

        try {
            $salesReport = $this->reportingService->generateSalesReport();
            $inventoryReport = $this->reportingService->generateInventoryReport();
            $paymentReport = $this->reportingService->generatePaymentReport();
            $shippingReport = $this->reportingService->generateShippingReport();

            return [
                'success' => true,
                'message' => 'Complete report generated successfully',
                'reports' => [
                    'sales' => $salesReport,
                    'inventory' => $inventoryReport,
                    'payments' => $paymentReport,
                    'shipping' => $shippingReport,
                ],
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while generating the report',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Ottiene le statistiche del sistema
     */
    public function getSystemStats(): array
    {
        \Log::info('Getting system stats through facade');

        try {
            $inventory = $this->inventoryService->getAllProducts();
            $payments = $this->paymentService->getAllPayments();
            $shipments = $this->shippingService->getAllShipments();
            $notifications = $this->notificationService->getAllNotifications();
            $reports = $this->reportingService->getAllReports();

            return [
                'success' => true,
                'stats' => [
                    'inventory' => [
                        'total_products' => count($inventory),
                        'available_products' => count(array_filter($inventory, fn($p) => $p['quantity'] > 0)),
                    ],
                    'payments' => [
                        'total_payments' => count($payments),
                        'total_amount' => array_sum(array_column($payments, 'total')),
                    ],
                    'shipments' => [
                        'total_shipments' => count($shipments),
                        'active_shipments' => count(array_filter($shipments, fn($s) => $s['status'] !== 'delivered')),
                    ],
                    'notifications' => [
                        'total_notifications' => count($notifications),
                        'by_type' => $this->notificationService->getNotificationStats()['by_type'],
                    ],
                    'reports' => [
                        'total_reports' => count($reports),
                        'by_type' => $this->reportingService->getReportStats()['by_type'],
                    ],
                ],
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving system stats',
                'error' => $e->getMessage(),
            ];
        }
    }
}
