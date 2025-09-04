<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Relazione con gli utenti
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope per cercare per nome
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
