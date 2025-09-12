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
     * Relazione con gli eventi inbox
     */
    public function inboxEvents()
    {
        return $this->hasMany(InboxEvent::class, 'event_data->order_id');
    }

    /**
     * Ottiene gli eventi inbox per questo ordine
     */
    public function getInboxEvents()
    {
        return InboxEvent::whereJsonContains('event_data->order_id', $this->id)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Ottiene l'ultimo evento inbox per questo ordine
     */
    public function getLastInboxEvent()
    {
        return InboxEvent::whereJsonContains('event_data->order_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Verifica se l'ordine ha eventi inbox pendenti
     */
    public function hasPendingInboxEvents(): bool
    {
        return InboxEvent::whereJsonContains('event_data->order_id', $this->id)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Ottiene statistiche dell'ordine
     */
    public function getStats(): array
    {
        $events = $this->getInboxEvents();
        
        return [
            'total_events' => $events->count(),
            'pending_events' => $events->where('status', 'pending')->count(),
            'processed_events' => $events->where('status', 'processed')->count(),
            'failed_events' => $events->where('status', 'failed')->count(),
            'last_event_at' => $events->max('created_at')
        ];
    }

    /**
     * Crea un ordine da un evento
     */
    public static function createFromEvent(array $eventData): self
    {
        return static::create([
            'customer_name' => $eventData['customer_name'] ?? 'Unknown',
            'customer_email' => $eventData['customer_email'] ?? 'unknown@example.com',
            'amount' => $eventData['amount'] ?? 0,
            'status' => $eventData['status'] ?? 'pending',
            'notes' => $eventData['notes'] ?? 'Created from event'
        ]);
    }

    /**
     * Aggiorna un ordine da un evento
     */
    public function updateFromEvent(array $eventData): bool
    {
        $updateData = [];
        
        if (isset($eventData['customer_name'])) {
            $updateData['customer_name'] = $eventData['customer_name'];
        }
        
        if (isset($eventData['customer_email'])) {
            $updateData['customer_email'] = $eventData['customer_email'];
        }
        
        if (isset($eventData['amount'])) {
            $updateData['amount'] = $eventData['amount'];
        }
        
        if (isset($eventData['status'])) {
            $updateData['status'] = $eventData['status'];
        }
        
        if (isset($eventData['notes'])) {
            $updateData['notes'] = $eventData['notes'];
        }

        return $this->update($updateData);
    }
}
