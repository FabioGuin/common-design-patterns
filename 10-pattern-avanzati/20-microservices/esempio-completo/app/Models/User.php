<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'status'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Hash della password prima del salvataggio
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Verifica se l'utente è attivo
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se l'utente è inattivo
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Attiva l'utente
     */
    public function activate()
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Disattiva l'utente
     */
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();
    }

    /**
     * Converte il modello in array per API
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
