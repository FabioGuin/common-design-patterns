<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'shipping_address',
        'billing_address',
        'notes',
        'payment_method',
        'tax_amount',
        'discount_amount',
        'shipping_cost'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relazione con l'utente
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione con i prodotti
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot(['quantity', 'price', 'tax_rate'])
            ->withTimestamps();
    }

    /**
     * Relazione con gli item dell'ordine
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relazione con la cronologia dell'ordine
     */
    public function orderHistory()
    {
        return $this->hasMany(OrderHistory::class);
    }

    /**
     * Scope per ordini attivi
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'processing', 'shipped']);
    }

    /**
     * Scope per ordini completati
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope per ordini cancellati
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope per ordini per importo
     */
    public function scopeByAmountRange($query, float $min, float $max)
    {
        return $query->whereBetween('total_amount', [$min, $max]);
    }

    /**
     * Scope per ordini per data
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Verifica se l'ordine può essere modificato
     */
    public function canBeModified(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Verifica se l'ordine può essere cancellato
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Ottiene il totale formattato
     */
    public function getFormattedTotalAttribute(): string
    {
        return '€ ' . number_format($this->total_amount, 2, ',', '.');
    }

    /**
     * Ottiene lo status formattato
     */
    public function getFormattedStatusAttribute(): string
    {
        $statuses = [
            'pending' => 'In Attesa',
            'processing' => 'In Elaborazione',
            'shipped' => 'Spedito',
            'completed' => 'Completato',
            'cancelled' => 'Cancellato'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Ottiene il colore dello status per l'interfaccia
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Ottiene statistiche dell'ordine
     */
    public function getStats(): array
    {
        return [
            'total_items' => $this->products->sum('pivot.quantity'),
            'total_tax' => $this->tax_amount,
            'total_discount' => $this->discount_amount,
            'shipping_cost' => $this->shipping_cost,
            'net_amount' => $this->total_amount - $this->tax_amount - $this->shipping_cost + $this->discount_amount
        ];
    }
}
