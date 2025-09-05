<?php

namespace App\Aggregates;

use App\Entities\OrderItem;
use App\ValueObjects\OrderAddress;
use App\ValueObjects\OrderPayment;
use App\Events\OrderConfirmed;
use App\Events\OrderCancelled;
use Illuminate\Support\Collection;

/**
 * Order Aggregate Root
 * 
 * Controlla tutte le modifiche all'ordine e garantisce
 * la consistenza dei dati attraverso regole di business centralizzate.
 */
class Order extends AggregateRoot
{
    private const MAX_ITEMS_PER_ORDER = 50;
    private const MIN_ORDER_TOTAL = 10.00;
    private const MAX_ORDER_TOTAL = 10000.00;

    private string $customerId;
    private string $status;
    private Collection $items;
    private ?OrderAddress $shippingAddress = null;
    private ?OrderAddress $billingAddress = null;
    private ?OrderPayment $payment = null;
    private ?\DateTime $confirmedAt = null;
    private ?\DateTime $cancelledAt = null;
    private float $total = 0.0;

    public function __construct(string $id, string $customerId)
    {
        parent::__construct($id);
        $this->customerId = $customerId;
        $this->status = 'DRAFT';
        $this->items = collect();
    }

    /**
     * Aggiunge un item all'ordine
     */
    public function addItem(string $productId, int $quantity, float $price): void
    {
        $this->validateCanModify();
        $this->validateItemQuantity($quantity);
        $this->validateMaxItems();

        $existingItem = $this->findItemByProductId($productId);
        
        if ($existingItem) {
            $existingItem->updateQuantity($existingItem->getQuantity() + $quantity);
        } else {
            $item = new OrderItem($productId, $quantity, $price);
            $this->items->push($item);
        }

        $this->recalculateTotal();
        $this->incrementVersion();
    }

    /**
     * Rimuove un item dall'ordine
     */
    public function removeItem(string $productId): void
    {
        $this->validateCanModify();

        $this->items = $this->items->reject(function (OrderItem $item) use ($productId) {
            return $item->getProductId() === $productId;
        });

        $this->recalculateTotal();
        $this->incrementVersion();
    }

    /**
     * Aggiorna la quantità di un item
     */
    public function updateItemQuantity(string $productId, int $quantity): void
    {
        $this->validateCanModify();
        $this->validateItemQuantity($quantity);

        $item = $this->findItemByProductId($productId);
        if (!$item) {
            throw new \InvalidArgumentException("Item with product ID {$productId} not found");
        }

        if ($quantity === 0) {
            $this->removeItem($productId);
        } else {
            $item->updateQuantity($quantity);
            $this->recalculateTotal();
        }

        $this->incrementVersion();
    }

    /**
     * Imposta l'indirizzo di spedizione
     */
    public function setShippingAddress(OrderAddress $address): void
    {
        $this->validateCanModify();
        $this->shippingAddress = $address;
        $this->incrementVersion();
    }

    /**
     * Imposta l'indirizzo di fatturazione
     */
    public function setBillingAddress(OrderAddress $address): void
    {
        $this->validateCanModify();
        $this->billingAddress = $address;
        $this->incrementVersion();
    }

    /**
     * Imposta le informazioni di pagamento
     */
    public function setPayment(OrderPayment $payment): void
    {
        $this->validateCanModify();
        $this->payment = $payment;
        $this->incrementVersion();
    }

    /**
     * Conferma l'ordine
     */
    public function confirm(): void
    {
        $this->validateCanConfirm();
        
        $this->status = 'CONFIRMED';
        $this->confirmedAt = new \DateTime();
        
        $this->addDomainEvent(new OrderConfirmed($this->id, $this->customerId, $this->total));
        $this->incrementVersion();
    }

    /**
     * Cancella l'ordine
     */
    public function cancel(): void
    {
        $this->validateCanCancel();
        
        $this->status = 'CANCELLED';
        $this->cancelledAt = new \DateTime();
        
        $this->addDomainEvent(new OrderCancelled($this->id, $this->customerId, $this->total));
        $this->incrementVersion();
    }

    /**
     * Restituisce l'ID del cliente
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * Restituisce lo status dell'ordine
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Restituisce gli items dell'ordine
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Restituisce l'indirizzo di spedizione
     */
    public function getShippingAddress(): ?OrderAddress
    {
        return $this->shippingAddress;
    }

    /**
     * Restituisce l'indirizzo di fatturazione
     */
    public function getBillingAddress(): ?OrderAddress
    {
        return $this->billingAddress;
    }

    /**
     * Restituisce le informazioni di pagamento
     */
    public function getPayment(): ?OrderPayment
    {
        return $this->payment;
    }

    /**
     * Restituisce il totale dell'ordine
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * Restituisce la data di conferma
     */
    public function getConfirmedAt(): ?\DateTime
    {
        return $this->confirmedAt;
    }

    /**
     * Restituisce la data di cancellazione
     */
    public function getCancelledAt(): ?\DateTime
    {
        return $this->cancelledAt;
    }

    /**
     * Verifica se l'ordine può essere modificato
     */
    public function canBeModified(): bool
    {
        return $this->status === 'DRAFT';
    }

    /**
     * Verifica se l'ordine può essere confermato
     */
    public function canBeConfirmed(): bool
    {
        return $this->status === 'DRAFT' && 
               $this->items->isNotEmpty() && 
               $this->shippingAddress !== null && 
               $this->billingAddress !== null &&
               $this->total >= self::MIN_ORDER_TOTAL;
    }

    /**
     * Verifica se l'ordine può essere cancellato
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['DRAFT', 'CONFIRMED']) && $this->status !== 'SHIPPED';
    }

    /**
     * Restituisce una rappresentazione JSON dell'ordine
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'status' => $this->status,
            'total' => $this->total,
            'items' => $this->items->map(function (OrderItem $item) {
                return $item->toArray();
            })->toArray(),
            'shippingAddress' => $this->shippingAddress?->toArray(),
            'billingAddress' => $this->billingAddress?->toArray(),
            'payment' => $this->payment?->toArray(),
            'confirmedAt' => $this->confirmedAt?->format('Y-m-d H:i:s'),
            'cancelledAt' => $this->cancelledAt?->format('Y-m-d H:i:s'),
            'version' => $this->version
        ];
    }

    /**
     * Trova un item per product ID
     */
    private function findItemByProductId(string $productId): ?OrderItem
    {
        return $this->items->first(function (OrderItem $item) use ($productId) {
            return $item->getProductId() === $productId;
        });
    }

    /**
     * Ricalcola il totale dell'ordine
     */
    private function recalculateTotal(): void
    {
        $this->total = $this->items->sum(function (OrderItem $item) {
            return $item->getQuantity() * $item->getPrice();
        });
    }

    /**
     * Valida che l'ordine possa essere modificato
     */
    private function validateCanModify(): void
    {
        if (!$this->canBeModified()) {
            throw new \InvalidArgumentException("Cannot modify order in status: {$this->status}");
        }
    }

    /**
     * Valida che l'ordine possa essere confermato
     */
    private function validateCanConfirm(): void
    {
        if (!$this->canBeConfirmed()) {
            $reasons = [];
            
            if ($this->items->isEmpty()) {
                $reasons[] = 'Order is empty';
            }
            if ($this->shippingAddress === null) {
                $reasons[] = 'Shipping address is required';
            }
            if ($this->billingAddress === null) {
                $reasons[] = 'Billing address is required';
            }
            if ($this->total < self::MIN_ORDER_TOTAL) {
                $reasons[] = "Order total must be at least " . self::MIN_ORDER_TOTAL;
            }
            
            throw new \InvalidArgumentException("Cannot confirm order: " . implode(', ', $reasons));
        }
    }

    /**
     * Valida che l'ordine possa essere cancellato
     */
    private function validateCanCancel(): void
    {
        if (!$this->canBeCancelled()) {
            throw new \InvalidArgumentException("Cannot cancel order in status: {$this->status}");
        }
    }

    /**
     * Valida la quantità di un item
     */
    private function validateItemQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }
        
        if ($quantity > 100) {
            throw new \InvalidArgumentException('Quantity cannot exceed 100');
        }
    }

    /**
     * Valida il numero massimo di items
     */
    private function validateMaxItems(): void
    {
        if ($this->items->count() >= self::MAX_ITEMS_PER_ORDER) {
            throw new \InvalidArgumentException("Cannot add more than " . self::MAX_ITEMS_PER_ORDER . " items");
        }
    }
}
