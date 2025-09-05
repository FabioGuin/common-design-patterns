<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modello Payment per il Shared Database Anti-pattern
 * 
 * Questo modello dimostra i problemi dell'utilizzo di un database condiviso
 * dove le modifiche al schema impattano altri servizi.
 */
class Payment extends Model
{
    protected $connection = 'shared_database';
    protected $table = 'payments';
    
    protected $fillable = [
        'order_id',
        'user_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'error_message',
        'processed_at',
        'refunded_at'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Relazione con l'ordine
     * 
     * Problema: Relazione diretta con tabella condivisa
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
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
     * Verifica se il pagamento è completato
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }
    
    /**
     * Verifica se il pagamento è fallito
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }
    
    /**
     * Verifica se il pagamento è in sospeso
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    /**
     * Verifica se il pagamento è rimborsato
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isRefunded()
    {
        return $this->status === 'refunded';
    }
    
    /**
     * Verifica se il pagamento è un rimborso
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isRefund()
    {
        return $this->amount < 0;
    }
    
    /**
     * Ottiene le statistiche del pagamento
     * 
     * Problema: Query complessa su multiple tabelle condivise
     */
    public function getStats()
    {
        return [
            'is_completed' => $this->isCompleted(),
            'is_failed' => $this->isFailed(),
            'is_pending' => $this->isPending(),
            'is_refunded' => $this->isRefunded(),
            'is_refund' => $this->isRefund(),
            'amount_absolute' => abs($this->amount),
            'transaction_id' => $this->transaction_id,
            'error_message' => $this->error_message,
            'processed_at' => $this->processed_at,
            'refunded_at' => $this->refunded_at
        ];
    }
    
    /**
     * Ottiene i pagamenti per stato
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
     * Ottiene i pagamenti per utente
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
     * Ottiene i pagamenti per ordine
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getByOrder($orderId)
    {
        return static::where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene i pagamenti completati
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getCompleted()
    {
        return static::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene i pagamenti falliti
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getFailed()
    {
        return static::where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene i rimborsi
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getRefunds()
    {
        return static::where('amount', '<', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Verifica se il pagamento può essere rimborsato
     * 
     * Problema: Verifica su multiple tabelle condivise
     */
    public function canBeRefunded()
    {
        return $this->isCompleted() && !$this->isRefunded() && $this->amount > 0;
    }
    
    /**
     * Rimborsa il pagamento
     * 
     * Problema: Modifica su multiple tabelle condivise
     */
    public function refund($amount = null)
    {
        if (!$this->canBeRefunded()) {
            throw new \Exception('Payment cannot be refunded');
        }
        
        $refundAmount = $amount ?? $this->amount;
        
        if ($refundAmount > $this->amount) {
            throw new \Exception('Refund amount cannot exceed payment amount');
        }
        
        $this->status = 'refunded';
        $this->refunded_at = now();
        $this->save();
        
        return $this;
    }
    
    /**
     * Ottiene il totale dei pagamenti per un ordine
     * 
     * Problema: Aggregazione su tabella condivisa
     */
    public static function getTotalForOrder($orderId)
    {
        return static::where('order_id', $orderId)
            ->where('status', 'completed')
            ->sum('amount');
    }
    
    /**
     * Verifica se un ordine è completamente pagato
     * 
     * Problema: Verifica su multiple tabelle condivise
     */
    public static function isOrderFullyPaid($orderId, $orderTotal)
    {
        $totalPaid = static::getTotalForOrder($orderId);
        return $totalPaid >= $orderTotal;
    }
}
