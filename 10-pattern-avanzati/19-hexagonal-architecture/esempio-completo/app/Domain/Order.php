<?php

namespace App\Domain;

class Order
{
    protected $id;
    protected $customerName;
    protected $customerEmail;
    protected $items;
    protected $subtotal;
    protected $discount;
    protected $shippingCost;
    protected $totalAmount;
    protected $status;
    protected $paymentId;
    protected $cancellationReason;
    protected $cancelledAt;
    protected $createdAt;
    protected $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? uniqid('order_', true);
        $this->customerName = $data['customer_name'] ?? '';
        $this->customerEmail = $data['customer_email'] ?? '';
        $this->items = $data['items'] ?? [];
        $this->subtotal = $data['subtotal'] ?? 0;
        $this->discount = $data['discount'] ?? 0;
        $this->shippingCost = $data['shipping_cost'] ?? 0;
        $this->totalAmount = $data['total_amount'] ?? 0;
        $this->status = $data['status'] ?? 'pending';
        $this->paymentId = $data['payment_id'] ?? null;
        $this->cancellationReason = $data['cancellation_reason'] ?? null;
        $this->cancelledAt = $data['cancelled_at'] ?? null;
        $this->createdAt = $data['created_at'] ?? now();
        $this->updatedAt = $data['updated_at'] ?? now();
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function getShippingCost(): float
    {
        return $this->shippingCost;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function getCancelledAt(): ?\DateTime
    {
        return $this->cancelledAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    // Setters
    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;
        $this->updatedAt = now();
        return $this;
    }

    public function setCustomerEmail(string $customerEmail): self
    {
        $this->customerEmail = $customerEmail;
        $this->updatedAt = now();
        return $this;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;
        $this->updatedAt = now();
        return $this;
    }

    public function setSubtotal(float $subtotal): self
    {
        $this->subtotal = $subtotal;
        $this->updatedAt = now();
        return $this;
    }

    public function setDiscount(?float $discount): self
    {
        $this->discount = $discount;
        $this->updatedAt = now();
        return $this;
    }

    public function setShippingCost(float $shippingCost): self
    {
        $this->shippingCost = $shippingCost;
        $this->updatedAt = now();
        return $this;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        $this->updatedAt = now();
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->updatedAt = now();
        return $this;
    }

    public function setPaymentId(?string $paymentId): self
    {
        $this->paymentId = $paymentId;
        $this->updatedAt = now();
        return $this;
    }

    public function setCancellationReason(?string $cancellationReason): self
    {
        $this->cancellationReason = $cancellationReason;
        $this->updatedAt = now();
        return $this;
    }

    public function setCancelledAt(?\DateTime $cancelledAt): self
    {
        $this->cancelledAt = $cancelledAt;
        $this->updatedAt = now();
        return $this;
    }

    // Business methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'paid']);
    }

    public function canBeUpdated(): bool
    {
        return in_array($this->status, ['pending', 'paid']);
    }

    public function getTotalWithShipping(): float
    {
        return $this->totalAmount + $this->shippingCost;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function getTotalQuantity(): int
    {
        return array_sum(array_column($this->items, 'quantity'));
    }

    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }

    public function getDiscountPercentage(): float
    {
        if ($this->subtotal == 0) {
            return 0;
        }
        
        return ($this->discount / $this->subtotal) * 100;
    }

    // Convert to array for persistence
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'shipping_cost' => $this->shippingCost,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'payment_id' => $this->paymentId,
            'cancellation_reason' => $this->cancellationReason,
            'cancelled_at' => $this->cancelledAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    // Create from array (for persistence)
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    // Validation methods
    public function isValid(): bool
    {
        try {
            $this->validate();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function validate(): void
    {
        if (empty($this->customerName)) {
            throw new \InvalidArgumentException("Nome cliente obbligatorio");
        }
        
        if (empty($this->customerEmail) || !filter_var($this->customerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email cliente non valida");
        }
        
        if (empty($this->items) || count($this->items) === 0) {
            throw new \InvalidArgumentException("Ordine deve contenere almeno un item");
        }
        
        if ($this->totalAmount < 0) {
            throw new \InvalidArgumentException("Importo totale non può essere negativo");
        }
        
        if ($this->discount < 0) {
            throw new \InvalidArgumentException("Sconto non può essere negativo");
        }
        
        if ($this->shippingCost < 0) {
            throw new \InvalidArgumentException("Costo spedizione non può essere negativo");
        }
        
        $validStatuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($this->status, $validStatuses)) {
            throw new \InvalidArgumentException("Status non valido: {$this->status}");
        }
    }

    // Business rules
    public function applyBusinessRules(): void
    {
        // Regola: sconto massimo del 50%
        if ($this->discount > $this->subtotal * 0.5) {
            $this->discount = $this->subtotal * 0.5;
        }
        
        // Regola: ordini sopra 100€ hanno spedizione gratuita
        if ($this->totalAmount >= 100) {
            $this->shippingCost = 0;
        } else {
            $this->shippingCost = 10; // Spedizione standard
        }
        
        // Regola: ricalcola il totale
        $this->totalAmount = max(0, $this->subtotal - $this->discount);
    }
}
