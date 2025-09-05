<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sku',
        'category',
        'stock_quantity',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Verifica se il prodotto è attivo
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se il prodotto è inattivo
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Verifica se il prodotto è disponibile
     */
    public function isAvailable()
    {
        return $this->isActive() && $this->stock_quantity > 0;
    }

    /**
     * Verifica se il prodotto è esaurito
     */
    public function isOutOfStock()
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Verifica se il prodotto ha stock basso
     */
    public function isLowStock($threshold = 10)
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $threshold;
    }

    /**
     * Attiva il prodotto
     */
    public function activate()
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Disattiva il prodotto
     */
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();
    }

    /**
     * Aggiunge stock
     */
    public function addStock($quantity)
    {
        $this->stock_quantity += $quantity;
        $this->save();
    }

    /**
     * Rimuove stock
     */
    public function removeStock($quantity)
    {
        $this->stock_quantity = max(0, $this->stock_quantity - $quantity);
        $this->save();
    }

    /**
     * Imposta stock
     */
    public function setStock($quantity)
    {
        $this->stock_quantity = max(0, $quantity);
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
            'description' => $this->description,
            'price' => $this->price,
            'sku' => $this->sku,
            'category' => $this->category,
            'stock_quantity' => $this->stock_quantity,
            'status' => $this->status,
            'available' => $this->isAvailable(),
            'out_of_stock' => $this->isOutOfStock(),
            'low_stock' => $this->isLowStock(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
