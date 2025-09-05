<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'items',
        'total_amount',
        'status',
        'shipping_address',
        'notes',
        'payment_id',
        'cancellation_reason',
        'cancelled_at'
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Verifica se l'ordine è in stato pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se l'ordine è pagato
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Verifica se l'ordine è spedito
     */
    public function isShipped()
    {
        return $this->status === 'shipped';
    }

    /**
     * Verifica se l'ordine è consegnato
     */
    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    /**
     * Verifica se l'ordine è cancellato
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Verifica se l'ordine può essere cancellato
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'paid']);
    }

    /**
     * Verifica se l'ordine può essere aggiornato
     */
    public function canBeUpdated()
    {
        return in_array($this->status, ['pending', 'paid']);
    }

    /**
     * Verifica se l'ordine può essere spedito
     */
    public function canBeShipped()
    {
        return $this->status === 'paid';
    }

    /**
     * Verifica se l'ordine può essere consegnato
     */
    public function canBeDelivered()
    {
        return $this->status === 'shipped';
    }

    /**
     * Aggiorna lo status dell'ordine
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    /**
     * Cancella l'ordine
     */
    public function cancel($reason = null)
    {
        $this->status = 'cancelled';
        $this->cancellation_reason = $reason;
        $this->cancelled_at = now();
        $this->save();
    }

    /**
     * Spedisce l'ordine
     */
    public function ship()
    {
        $this->status = 'shipped';
        $this->save();
    }

    /**
     * Consegna l'ordine
     */
    public function deliver()
    {
        $this->status = 'delivered';
        $this->save();
    }

    /**
     * Ottiene il numero di item nell'ordine
     */
    public function getItemCount()
    {
        return count($this->items ?? []);
    }

    /**
     * Ottiene la quantità totale di item
     */
    public function getTotalQuantity()
    {
        return collect($this->items ?? [])->sum('quantity');
    }

    /**
     * Ottiene il totale dell'ordine
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    /**
     * Calcola il totale dell'ordine
     */
    public function calculateTotal()
    {
        $total = 0;
        
        foreach ($this->items ?? [] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        $this->total_amount = $total;
        $this->save();
        
        return $total;
    }

    /**
     * Aggiunge un item all'ordine
     */
    public function addItem($productId, $quantity, $price)
    {
        $items = $this->items ?? [];
        
        $items[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price
        ];
        
        $this->items = $items;
        $this->calculateTotal();
    }

    /**
     * Rimuove un item dall'ordine
     */
    public function removeItem($productId)
    {
        $items = $this->items ?? [];
        
        $items = collect($items)->reject(function($item) use ($productId) {
            return $item['product_id'] === $productId;
        })->values()->toArray();
        
        $this->items = $items;
        $this->calculateTotal();
    }

    /**
     * Converte il modello in array per API
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'items' => $this->items,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'payment_id' => $this->payment_id,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'item_count' => $this->getItemCount(),
            'total_quantity' => $this->getTotalQuantity(),
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_updated' => $this->canBeUpdated(),
            'can_be_shipped' => $this->canBeShipped(),
            'can_be_delivered' => $this->canBeDelivered(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
