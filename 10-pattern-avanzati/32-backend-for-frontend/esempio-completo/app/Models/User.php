<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relazione con gli ordini
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope per utenti attivi
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope per utenti inattivi
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope per ricerca
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Verifica se l'utente è attivo
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Ottiene statistiche dell'utente
     */
    public function getStats(): array
    {
        return [
            'total_orders' => $this->orders->count(),
            'total_spent' => $this->orders->where('status', 'completed')->sum('total_amount'),
            'pending_orders' => $this->orders->where('status', 'pending')->count(),
            'completed_orders' => $this->orders->where('status', 'completed')->count(),
            'average_order_value' => $this->orders->where('status', 'completed')->avg('total_amount') ?? 0
        ];
    }
}
