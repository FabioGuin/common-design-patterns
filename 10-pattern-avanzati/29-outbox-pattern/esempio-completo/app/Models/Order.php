<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'amount',
        'status',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

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
        return $query->whereBetween('amount', [$min, $max]);
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
    public function getFormattedAmountAttribute(): string
    {
        return '€ ' . number_format($this->amount, 2, ',', '.');
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
     * Relazione con gli eventi outbox
     */
    public function outboxEvents()
    {
        return $this->hasMany(OutboxEvent::class, 'aggregate_id');
    }

    /**
     * Ottiene gli eventi outbox per questo ordine
     */
    public function getOutboxEvents()
    {
        return $this->outboxEvents()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Ottiene l'ultimo evento outbox per questo ordine
     */
    public function getLastOutboxEvent()
    {
        return $this->outboxEvents()
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Verifica se l'ordine ha eventi outbox pendenti
     */
    public function hasPendingOutboxEvents(): bool
    {
        return $this->outboxEvents()
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Ottiene statistiche dell'ordine
     */
    public function getStats(): array
    {
        return [
            'total_events' => $this->outboxEvents()->count(),
            'pending_events' => $this->outboxEvents()->where('status', 'pending')->count(),
            'published_events' => $this->outboxEvents()->where('status', 'published')->count(),
            'failed_events' => $this->outboxEvents()->where('status', 'failed')->count(),
            'last_event_at' => $this->outboxEvents()->latest()->first()?->created_at
        ];
    }
}
