<?php

namespace Tests\Builders;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Faker\Factory as Faker;

class OrderBuilder
{
    private array $attributes = [];
    private Faker $faker;
    private ?User $user = null;
    private array $products = [];

    public function __construct()
    {
        $this->faker = Faker::create('it_IT');
        $this->reset();
    }

    public static function new(): self
    {
        return new self();
    }

    public function reset(): self
    {
        $this->attributes = [
            'total_amount' => $this->faker->randomFloat(2, 50, 500),
            'status' => Order::STATUS_PENDING,
            'payment_method' => $this->faker->randomElement([
                Order::PAYMENT_METHOD_CREDIT_CARD,
                Order::PAYMENT_METHOD_PAYPAL,
                Order::PAYMENT_METHOD_BANK_TRANSFER
            ]),
            'shipping_address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'country' => 'IT',
                'region' => $this->faker->state()
            ],
            'billing_address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'country' => 'IT',
                'region' => $this->faker->state()
            ],
            'notes' => $this->faker->optional(0.3)->sentence(),
            'shipping_cost' => $this->faker->randomFloat(2, 5, 25),
            'tax_amount' => $this->faker->randomFloat(2, 5, 50),
            'discount_amount' => $this->faker->optional(0.2)->randomFloat(2, 5, 30),
            'coupon_code' => $this->faker->optional(0.2)->bothify('SAVE####'),
            'tracking_number' => null,
            'shipped_at' => null,
            'delivered_at' => null,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $this->user = null;
        $this->products = [];
        
        return $this;
    }

    public function withUser(User $user): self
    {
        $this->user = $user;
        $this->attributes['user_id'] = $user->id;
        return $this;
    }

    public function withTotalAmount(float $totalAmount): self
    {
        $this->attributes['total_amount'] = $totalAmount;
        return $this;
    }

    public function withStatus(string $status): self
    {
        $this->attributes['status'] = $status;
        return $this;
    }

    public function withPaymentMethod(string $paymentMethod): self
    {
        $this->attributes['payment_method'] = $paymentMethod;
        return $this;
    }

    public function withShippingAddress(array $address): self
    {
        $this->attributes['shipping_address'] = $address;
        return $this;
    }

    public function withBillingAddress(array $address): self
    {
        $this->attributes['billing_address'] = $address;
        return $this;
    }

    public function withNotes(string $notes): self
    {
        $this->attributes['notes'] = $notes;
        return $this;
    }

    public function withShippingCost(float $shippingCost): self
    {
        $this->attributes['shipping_cost'] = $shippingCost;
        return $this;
    }

    public function withTaxAmount(float $taxAmount): self
    {
        $this->attributes['tax_amount'] = $taxAmount;
        return $this;
    }

    public function withDiscountAmount(float $discountAmount): self
    {
        $this->attributes['discount_amount'] = $discountAmount;
        return $this;
    }

    public function withCouponCode(string $couponCode): self
    {
        $this->attributes['coupon_code'] = $couponCode;
        return $this;
    }

    public function withTrackingNumber(string $trackingNumber): self
    {
        $this->attributes['tracking_number'] = $trackingNumber;
        return $this;
    }

    public function withShippedAt(string $shippedAt): self
    {
        $this->attributes['shipped_at'] = $shippedAt;
        return $this;
    }

    public function withDeliveredAt(string $deliveredAt): self
    {
        $this->attributes['delivered_at'] = $deliveredAt;
        return $this;
    }

    public function withCreatedAt(string $createdAt): self
    {
        $this->attributes['created_at'] = $createdAt;
        return $this;
    }

    public function withUpdatedAt(string $updatedAt): self
    {
        $this->attributes['updated_at'] = $updatedAt;
        return $this;
    }

    public function withProducts(array $products): self
    {
        $this->products = $products;
        return $this;
    }

    public function addProduct(Product $product, int $quantity = 1): self
    {
        $this->products[] = [
            'product' => $product,
            'quantity' => $quantity,
            'price' => $product->price
        ];
        return $this;
    }

    // Convenience methods for common scenarios
    public function asPending(): self
    {
        return $this->withStatus(Order::STATUS_PENDING);
    }

    public function asPaid(): self
    {
        return $this->withStatus(Order::STATUS_PAID);
    }

    public function asShipped(): self
    {
        return $this->withStatus(Order::STATUS_SHIPPED)
                    ->withTrackingNumber($this->faker->bothify('TRK#######'))
                    ->withShippedAt(now()->toDateTimeString());
    }

    public function asDelivered(): self
    {
        return $this->withStatus(Order::STATUS_DELIVERED)
                    ->withTrackingNumber($this->faker->bothify('TRK#######'))
                    ->withShippedAt(now()->subDays(3)->toDateTimeString())
                    ->withDeliveredAt(now()->toDateTimeString());
    }

    public function asCancelled(): self
    {
        return $this->withStatus(Order::STATUS_CANCELLED);
    }

    public function withCreditCard(): self
    {
        return $this->withPaymentMethod(Order::PAYMENT_METHOD_CREDIT_CARD);
    }

    public function withPayPal(): self
    {
        return $this->withPaymentMethod(Order::PAYMENT_METHOD_PAYPAL);
    }

    public function withBankTransfer(): self
    {
        return $this->withPaymentMethod(Order::PAYMENT_METHOD_BANK_TRANSFER);
    }

    public function withSameBillingAddress(): self
    {
        $this->attributes['billing_address'] = $this->attributes['shipping_address'];
        return $this;
    }

    public function withItalianAddress(): self
    {
        $address = [
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'IT',
            'region' => $this->faker->state()
        ];
        
        return $this->withShippingAddress($address)
                    ->withBillingAddress($address);
    }

    public function withAmericanAddress(): self
    {
        $address = [
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'US',
            'state' => $this->faker->stateAbbr()
        ];
        
        return $this->withShippingAddress($address)
                    ->withBillingAddress($address);
    }

    public function withDiscount(): self
    {
        $discountAmount = $this->faker->randomFloat(2, 5, 30);
        $couponCode = $this->faker->bothify('SAVE####');
        
        return $this->withDiscountAmount($discountAmount)
                    ->withCouponCode($couponCode);
    }

    public function asExpensive(): self
    {
        return $this->withTotalAmount($this->faker->randomFloat(2, 500, 2000));
    }

    public function asCheap(): self
    {
        return $this->withTotalAmount($this->faker->randomFloat(2, 20, 100));
    }

    public function withHighShipping(): self
    {
        return $this->withShippingCost($this->faker->randomFloat(2, 20, 50));
    }

    public function withFreeShipping(): self
    {
        return $this->withShippingCost(0);
    }

    public function withHighTax(): self
    {
        return $this->withTaxAmount($this->faker->randomFloat(2, 20, 100));
    }

    public function withNoTax(): self
    {
        return $this->withTaxAmount(0);
    }

    public function build(): Order
    {
        $order = new Order($this->attributes);
        
        // Reset for next use
        $this->reset();
        
        return $order;
    }

    public function create(): Order
    {
        $order = $this->build();
        $order->save();
        
        // Attach products if any
        foreach ($this->products as $productData) {
            $order->products()->attach($productData['product']->id, [
                'quantity' => $productData['quantity'],
                'price' => $productData['price']
            ]);
        }
        
        return $order;
    }

    public function make(): Order
    {
        return $this->build();
    }

    // Static factory methods for common scenarios
    public static function pending(): Order
    {
        return self::new()->asPending()->create();
    }

    public static function paid(): Order
    {
        return self::new()->asPaid()->create();
    }

    public static function shipped(): Order
    {
        return self::new()->asShipped()->create();
    }

    public static function delivered(): Order
    {
        return self::new()->asDelivered()->create();
    }

    public static function cancelled(): Order
    {
        return self::new()->asCancelled()->create();
    }

    public static function expensive(): Order
    {
        return self::new()->asExpensive()->create();
    }

    public static function cheap(): Order
    {
        return self::new()->asCheap()->create();
    }

    // Bulk creation methods
    public static function createMany(int $count): array
    {
        $orders = [];
        for ($i = 0; $i < $count; $i++) {
            $orders[] = self::new()->create();
        }
        return $orders;
    }

    public static function createPending(int $count): array
    {
        $orders = [];
        for ($i = 0; $i < $count; $i++) {
            $orders[] = self::pending();
        }
        return $orders;
    }

    public static function createPaid(int $count): array
    {
        $orders = [];
        for ($i = 0; $i < $count; $i++) {
            $orders[] = self::paid();
        }
        return $orders;
    }

    public static function createShipped(int $count): array
    {
        $orders = [];
        for ($i = 0; $i < $count; $i++) {
            $orders[] = self::shipped();
        }
        return $orders;
    }

    public static function createDelivered(int $count): array
    {
        $orders = [];
        for ($i = 0; $i < $count; $i++) {
            $orders[] = self::delivered();
        }
        return $orders;
    }
}
