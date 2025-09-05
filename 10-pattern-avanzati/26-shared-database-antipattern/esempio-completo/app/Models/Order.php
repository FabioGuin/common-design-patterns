<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modello Order per il Shared Database Anti-pattern
 * 
 * Questo modello dimostra i problemi dell'utilizzo di un database condiviso
 * dove le modifiche al schema impattano altri servizi.
 */
class Order extends Model
{
    protected $connection = 'shared_database';
    protected $table = 'orders';
    
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'shipping_address',
        'billing_address',
        'notes',
        'shipped_at',
        'delivered_at'
    ];
    
    protected $casts = [
        'total' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Relazione con l'utente
     * 
     * Problema: Relazione diretta con tabella condivisa
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relazione con gli item dell'ordine
     * 
     * Problema: Relazione diretta con tabella condivisa
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Relazione con i pagamenti
     * 
     * Problema: Relazione diretta con tabella condivisa
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    /**
     * Verifica se l'ordine è completato
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }
    
    /**
     * Verifica se l'ordine è in sospeso
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isPending()
    {
        return in_array($this->status, ['pending', 'processing']);
    }
    
    /**
     * Verifica se l'ordine è pagato
     * 
     * Problema: Verifica su multiple tabelle condivise
     */
    public function isPaid()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->exists();
    }
    
    /**
     * Ottiene il totale pagato per l'ordine
     * 
     * Problema: Aggregazione su tabella condivisa
     */
    public function getTotalPaid()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }
    
    /**
     * Verifica se l'ordine è completamente pagato
     * 
     * Problema: Verifica su multiple tabelle condivise
     */
    public function isFullyPaid()
    {
        return $this->getTotalPaid() >= $this->total;
    }
    
    /**
     * Ottiene le statistiche dell'ordine
     * 
     * Problema: Query complessa su multiple tabelle condivise
     */
    public function getStats()
    {
        return [
            'total_items' => $this->items()->count(),
            'total_quantity' => $this->items()->sum('quantity'),
            'total_paid' => $this->getTotalPaid(),
            'remaining_balance' => $this->total - $this->getTotalPaid(),
            'is_fully_paid' => $this->isFullyPaid(),
            'payment_count' => $this->payments()->count(),
            'successful_payments' => $this->payments()->where('status', 'completed')->count(),
            'failed_payments' => $this->payments()->where('status', 'failed')->count()
        ];
    }
    
    /**
     * Ottiene gli ordini per stato
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getByStatus($status)
    {
        return static::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene gli ordini per utente
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getByUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene gli ordini con pagamenti in sospeso
     * 
     * Problema: Query complessa su multiple tabelle condivise
     */
    public static function getWithPendingPayments()
    {
        return static::whereDoesntHave('payments', function ($query) {
            $query->where('status', 'completed');
        })->get();
    }
    
    /**
     * Verifica se l'ordine può essere cancellato
     * 
     * Problema: Verifica su multiple tabelle condivise
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'processing']) && !$this->isPaid();
    }
    
    /**
     * Cancella l'ordine
     * 
     * Problema: Modifica su multiple tabelle condivise
     */
    public function cancel()
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled');
        }
        
        $this->status = 'cancelled';
        $this->save();
        
        return $this;
    }
}
