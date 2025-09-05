<?php

namespace App\Domain;

use App\Ports\OrderRepositoryInterface;
use App\Ports\PaymentServiceInterface;
use App\Ports\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $orderRepository;
    protected $paymentService;
    protected $notificationService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PaymentServiceInterface $paymentService,
        NotificationServiceInterface $notificationService
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentService = $paymentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Crea un nuovo ordine
     */
    public function createOrder(array $orderData): Order
    {
        try {
            // Logica di business pura - isolata da framework e database
            $order = new Order($orderData);
            
            // Validazione business
            $this->validateOrder($order);
            
            // Calcoli business
            $this->calculateOrderTotal($order);
            
            // Applica regole business
            $this->applyBusinessRules($order);
            
            // Salva usando il port (non dipende da implementazione specifica)
            $savedOrder = $this->orderRepository->save($order);
            
            // Processa pagamento usando il port
            $paymentResult = $this->paymentService->processPayment($savedOrder);
            
            if ($paymentResult['success']) {
                $savedOrder->setStatus('paid');
                $savedOrder->setPaymentId($paymentResult['payment_id']);
                
                // Aggiorna l'ordine
                $savedOrder = $this->orderRepository->save($savedOrder);
                
                // Invia notifica usando il port
                $this->notificationService->sendOrderConfirmation($savedOrder);
            } else {
                $savedOrder->setStatus('payment_failed');
                $savedOrder = $this->orderRepository->save($savedOrder);
                
                throw new \Exception('Pagamento fallito: ' . $paymentResult['error']);
            }
            
            Log::info("Hexagonal Architecture: Ordine creato", [
                'order_id' => $savedOrder->getId(),
                'customer_email' => $savedOrder->getCustomerEmail(),
                'total_amount' => $savedOrder->getTotalAmount()
            ]);
            
            return $savedOrder;
            
        } catch (\Exception $e) {
            Log::error("Hexagonal Architecture: Errore nella creazione dell'ordine", [
                'error' => $e->getMessage(),
                'order_data' => $orderData
            ]);
            throw $e;
        }
    }

    /**
     * Aggiorna un ordine esistente
     */
    public function updateOrder(string $orderId, array $updateData): Order
    {
        try {
            // Recupera l'ordine usando il port
            $order = $this->orderRepository->findById($orderId);
            
            if (!$order) {
                throw new \Exception("Ordine non trovato: {$orderId}");
            }
            
            // Logica di business per l'aggiornamento
            $this->validateOrderUpdate($order, $updateData);
            
            // Applica gli aggiornamenti
            $this->applyOrderUpdates($order, $updateData);
            
            // Ricalcola se necessario
            if (isset($updateData['items']) || isset($updateData['discount'])) {
                $this->calculateOrderTotal($order);
            }
            
            // Salva usando il port
            $updatedOrder = $this->orderRepository->save($order);
            
            Log::info("Hexagonal Architecture: Ordine aggiornato", [
                'order_id' => $orderId,
                'updated_fields' => array_keys($updateData)
            ]);
            
            return $updatedOrder;
            
        } catch (\Exception $e) {
            Log::error("Hexagonal Architecture: Errore nell'aggiornamento dell'ordine", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'update_data' => $updateData
            ]);
            throw $e;
        }
    }

    /**
     * Cancella un ordine
     */
    public function cancelOrder(string $orderId, string $reason = null): Order
    {
        try {
            // Recupera l'ordine usando il port
            $order = $this->orderRepository->findById($orderId);
            
            if (!$order) {
                throw new \Exception("Ordine non trovato: {$orderId}");
            }
            
            // Logica di business per la cancellazione
            $this->validateOrderCancellation($order);
            
            // Applica la cancellazione
            $order->setStatus('cancelled');
            $order->setCancellationReason($reason);
            $order->setCancelledAt(now());
            
            // Salva usando il port
            $cancelledOrder = $this->orderRepository->save($order);
            
            // Invia notifica di cancellazione
            $this->notificationService->sendOrderCancellation($cancelledOrder);
            
            Log::info("Hexagonal Architecture: Ordine cancellato", [
                'order_id' => $orderId,
                'reason' => $reason
            ]);
            
            return $cancelledOrder;
            
        } catch (\Exception $e) {
            Log::error("Hexagonal Architecture: Errore nella cancellazione dell'ordine", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'reason' => $reason
            ]);
            throw $e;
        }
    }

    /**
     * Ottiene un ordine per ID
     */
    public function getOrder(string $orderId): ?Order
    {
        try {
            return $this->orderRepository->findById($orderId);
        } catch (\Exception $e) {
            Log::error("Hexagonal Architecture: Errore nel recupero dell'ordine", [
                'error' => $e->getMessage(),
                'order_id' => $orderId
            ]);
            return null;
        }
    }

    /**
     * Lista tutti gli ordini
     */
    public function getAllOrders(int $limit = 100, int $offset = 0): array
    {
        try {
            return $this->orderRepository->findAll($limit, $offset);
        } catch (\Exception $e) {
            Log::error("Hexagonal Architecture: Errore nel recupero degli ordini", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Ottiene ordini per cliente
     */
    public function getOrdersByCustomer(string $customerEmail): array
    {
        try {
            return $this->orderRepository->findByCustomerEmail($customerEmail);
        } catch (\Exception $e) {
            Log::error("Hexagonal Architecture: Errore nel recupero degli ordini per cliente", [
                'error' => $e->getMessage(),
                'customer_email' => $customerEmail
            ]);
            return [];
        }
    }

    /**
     * Processa un pagamento
     */
    public function processPayment(string $orderId): array
    {
        try {
            $order = $this->orderRepository->findById($orderId);
            
            if (!$order) {
                throw new \Exception("Ordine non trovato: {$orderId}");
            }
            
            $paymentResult = $this->paymentService->processPayment($order);
            
            if ($paymentResult['success']) {
                $order->setStatus('paid');
                $order->setPaymentId($paymentResult['payment_id']);
                $this->orderRepository->save($order);
            }
            
            return $paymentResult;
            
        } catch (\Exception $e) {
            Log::error("Hexagonal Architecture: Errore nel processing del pagamento", [
                'error' => $e->getMessage(),
                'order_id' => $orderId
            ]);
            throw $e;
        }
    }

    /**
     * Invia una notifica
     */
    public function sendNotification(string $orderId, string $type): array
    {
        try {
            $order = $this->orderRepository->findById($orderId);
            
            if (!$order) {
                throw new \Exception("Ordine non trovato: {$orderId}");
            }
            
            switch ($type) {
                case 'confirmation':
                    return $this->notificationService->sendOrderConfirmation($order);
                case 'cancellation':
                    return $this->notificationService->sendOrderCancellation($order);
                case 'shipping':
                    return $this->notificationService->sendShippingNotification($order);
                default:
                    throw new \Exception("Tipo di notifica non supportato: {$type}");
            }
            
        } catch (\Exception $e) {
            Log::error("Hexagonal Architecture: Errore nell'invio della notifica", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'type' => $type
            ]);
            throw $e;
        }
    }

    /**
     * Valida un ordine
     */
    private function validateOrder(Order $order): void
    {
        if (empty($order->getCustomerName())) {
            throw new \InvalidArgumentException("Nome cliente obbligatorio");
        }
        
        if (empty($order->getCustomerEmail()) || !filter_var($order->getCustomerEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email cliente non valida");
        }
        
        if (empty($order->getItems()) || count($order->getItems()) === 0) {
            throw new \InvalidArgumentException("Ordine deve contenere almeno un item");
        }
        
        foreach ($order->getItems() as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                throw new \InvalidArgumentException("Item non valido: mancano campi obbligatori");
            }
            
            if ($item['quantity'] <= 0) {
                throw new \InvalidArgumentException("Quantità deve essere maggiore di zero");
            }
            
            if ($item['price'] < 0) {
                throw new \InvalidArgumentException("Prezzo non può essere negativo");
            }
        }
    }

    /**
     * Valida un aggiornamento ordine
     */
    private function validateOrderUpdate(Order $order, array $updateData): void
    {
        if ($order->getStatus() === 'cancelled') {
            throw new \InvalidArgumentException("Impossibile aggiornare un ordine cancellato");
        }
        
        if ($order->getStatus() === 'shipped' || $order->getStatus() === 'delivered') {
            throw new \InvalidArgumentException("Impossibile aggiornare un ordine già spedito");
        }
        
        if (isset($updateData['customer_email']) && !filter_var($updateData['customer_email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email cliente non valida");
        }
    }

    /**
     * Valida una cancellazione ordine
     */
    private function validateOrderCancellation(Order $order): void
    {
        if ($order->getStatus() === 'cancelled') {
            throw new \InvalidArgumentException("Ordine già cancellato");
        }
        
        if ($order->getStatus() === 'shipped' || $order->getStatus() === 'delivered') {
            throw new \InvalidArgumentException("Impossibile cancellare un ordine già spedito");
        }
    }

    /**
     * Applica gli aggiornamenti all'ordine
     */
    private function applyOrderUpdates(Order $order, array $updateData): void
    {
        if (isset($updateData['customer_name'])) {
            $order->setCustomerName($updateData['customer_name']);
        }
        
        if (isset($updateData['customer_email'])) {
            $order->setCustomerEmail($updateData['customer_email']);
        }
        
        if (isset($updateData['items'])) {
            $order->setItems($updateData['items']);
        }
        
        if (isset($updateData['discount'])) {
            $order->setDiscount($updateData['discount']);
        }
        
        if (isset($updateData['status'])) {
            $order->setStatus($updateData['status']);
        }
    }

    /**
     * Calcola il totale dell'ordine
     */
    private function calculateOrderTotal(Order $order): void
    {
        $subtotal = 0;
        
        foreach ($order->getItems() as $item) {
            $subtotal += $item['quantity'] * $item['price'];
        }
        
        $discount = $order->getDiscount() ?? 0;
        $total = $subtotal - $discount;
        
        $order->setSubtotal($subtotal);
        $order->setTotalAmount(max(0, $total)); // Non può essere negativo
    }

    /**
     * Applica le regole di business
     */
    private function applyBusinessRules(Order $order): void
    {
        // Regola: sconto massimo del 50%
        if ($order->getDiscount() > $order->getSubtotal() * 0.5) {
            $order->setDiscount($order->getSubtotal() * 0.5);
        }
        
        // Regola: ordini sopra 100€ hanno spedizione gratuita
        if ($order->getTotalAmount() >= 100) {
            $order->setShippingCost(0);
        } else {
            $order->setShippingCost(10); // Spedizione standard
        }
        
        // Regola: status iniziale
        $order->setStatus('pending');
    }
}
