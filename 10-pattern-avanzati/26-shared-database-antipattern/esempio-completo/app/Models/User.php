<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modello User per il Shared Database Anti-pattern
 * 
 * Questo modello dimostra i problemi dell'utilizzo di un database condiviso
 * dove le modifiche al schema impattano altri servizi.
 */
class User extends Model
{
    protected $connection = 'shared_database';
    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address'
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
    /**
     * Relazione con gli ordini
     * 
     * Problema: Relazione diretta con tabella condivisa
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
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
     * Ottiene gli ordini attivi dell'utente
     * 
     * Problema: Query complessa su database condiviso
     */
    public function getActiveOrders()
    {
        return $this->orders()
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene il totale speso dall'utente
     * 
     * Problema: Aggregazione su database condiviso
     */
    public function getTotalSpent()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }
    
    /**
     * Verifica se l'utente ha ordini in sospeso
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function hasPendingOrders()
    {
        return $this->orders()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }
    
    /**
     * Ottiene le statistiche dell'utente
     * 
     * Problema: Query complessa su multiple tabelle condivise
     */
    public function getStats()
    {
        return [
            'total_orders' => $this->orders()->count(),
            'completed_orders' => $this->orders()->where('status', 'completed')->count(),
            'pending_orders' => $this->orders()->whereIn('status', ['pending', 'processing'])->count(),
            'total_spent' => $this->getTotalSpent(),
            'total_payments' => $this->payments()->count(),
            'successful_payments' => $this->payments()->where('status', 'completed')->count(),
            'failed_payments' => $this->payments()->where('status', 'failed')->count()
        ];
    }
}
