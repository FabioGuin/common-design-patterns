<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'total_amount',
        'status',
        'order_date'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_date' => 'datetime'
    ];

    /**
     * Relazione con gli elementi dell'ordine
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope per ordini completati
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope per ordini in un periodo specifico
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    /**
     * Scope per ordini per mese
     */
    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('order_date', $year)
                    ->whereMonth('order_date', $month);
    }

    /**
     * Calcola il totale degli ordini completati
     */
    public static function getTotalSales()
    {
        return static::completed()->sum('total_amount');
    }

    /**
     * Calcola il totale delle vendite per mese
     */
    public static function getSalesByMonth($year, $month)
    {
        return static::completed()
            ->byMonth($year, $month)
            ->sum('total_amount');
    }

    /**
     * Calcola il numero di ordini completati
     */
    public static function getTotalOrders()
    {
        return static::completed()->count();
    }

    /**
     * Calcola il valore medio degli ordini
     */
    public static function getAverageOrderValue()
    {
        return static::completed()->avg('total_amount');
    }

    /**
     * Ottiene le statistiche dell'ordine
     */
    public function getStats()
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'order_date' => $this->order_date,
            'items_count' => $this->orderItems()->count()
        ];
    }
}
