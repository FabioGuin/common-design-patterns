<?php

namespace App\Models;

use App\Events\Order\OrderCreated;
use App\Events\Order\OrderPaid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'payment_method',
        'shipping_address',
        'billing_address',
        'notes',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'coupon_code',
        'tracking_number',
        'shipped_at',
        'delivered_at'
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_PAYPAL = 'paypal';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Order $order) {
            // Fire order created event
            event(new OrderCreated($order, [
                'created_via' => 'web',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]));
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products for the order.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot(['quantity', 'price'])
                    ->withTimestamps();
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if order is shipped.
     */
    public function isShipped(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    /**
     * Check if order is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PAID
        ]);
    }

    /**
     * Mark order as paid and fire event.
     */
    public function markAsPaid(string $paymentMethod, string $transactionId): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_method' => $paymentMethod
        ]);

        event(new OrderPaid($this, $paymentMethod, $transactionId, [
            'paid_via' => 'web',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]));
    }

    /**
     * Mark order as shipped.
     */
    public function markAsShipped(string $trackingNumber = null): void
    {
        $this->update([
            'status' => self::STATUS_SHIPPED,
            'tracking_number' => $trackingNumber,
            'shipped_at' => now()
        ]);
    }

    /**
     * Mark order as delivered.
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now()
        ]);
    }

    /**
     * Cancel order.
     */
    public function cancel(): void
    {
        if (!$this->canBeCancelled()) {
            throw new \InvalidArgumentException('Order cannot be cancelled');
        }
        
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Get order total with all costs.
     */
    public function getTotalWithCosts(): float
    {
        return $this->total_amount + $this->shipping_cost + $this->tax_amount - ($this->discount_amount ?? 0);
    }

    /**
     * Get order summary.
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'shipping_cost' => $this->shipping_cost,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'final_total' => $this->getTotalWithCosts(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
