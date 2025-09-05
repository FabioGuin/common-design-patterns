<?php

namespace Tests\Builders;

use App\Models\Product;
use Faker\Factory as Faker;

class ProductBuilder
{
    private array $attributes = [];
    private Faker $faker;

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
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'sku' => $this->faker->unique()->bothify('SKU-####-????'),
            'category' => $this->faker->randomElement(['Electronics', 'Clothing', 'Books', 'Home', 'Sports']),
            'brand' => $this->faker->company(),
            'stock' => $this->faker->numberBetween(0, 100),
            'weight' => $this->faker->randomFloat(2, 0.1, 50),
            'dimensions' => [
                'length' => $this->faker->numberBetween(1, 100),
                'width' => $this->faker->numberBetween(1, 100),
                'height' => $this->faker->numberBetween(1, 100)
            ],
            'images' => [$this->faker->imageUrl()],
            'tags' => $this->faker->words(5),
            'is_active' => true,
            'is_featured' => false,
            'is_digital' => false,
            'requires_shipping' => true,
            'created_at' => now(),
            'updated_at' => now()
        ];
        return $this;
    }

    public function withName(string $name): self
    {
        $this->attributes['name'] = $name;
        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->attributes['description'] = $description;
        return $this;
    }

    public function withPrice(float $price): self
    {
        $this->attributes['price'] = $price;
        return $this;
    }

    public function withSku(string $sku): self
    {
        $this->attributes['sku'] = $sku;
        return $this;
    }

    public function withCategory(string $category): self
    {
        $this->attributes['category'] = $category;
        return $this;
    }

    public function withBrand(string $brand): self
    {
        $this->attributes['brand'] = $brand;
        return $this;
    }

    public function withStock(int $stock): self
    {
        $this->attributes['stock'] = $stock;
        return $this;
    }

    public function withWeight(float $weight): self
    {
        $this->attributes['weight'] = $weight;
        return $this;
    }

    public function withDimensions(array $dimensions): self
    {
        $this->attributes['dimensions'] = $dimensions;
        return $this;
    }

    public function withImages(array $images): self
    {
        $this->attributes['images'] = $images;
        return $this;
    }

    public function withTags(array $tags): self
    {
        $this->attributes['tags'] = $tags;
        return $this;
    }

    public function withIsActive(bool $isActive): self
    {
        $this->attributes['is_active'] = $isActive;
        return $this;
    }

    public function withIsFeatured(bool $isFeatured): self
    {
        $this->attributes['is_featured'] = $isFeatured;
        return $this;
    }

    public function withIsDigital(bool $isDigital): self
    {
        $this->attributes['is_digital'] = $isDigital;
        return $this;
    }

    public function withRequiresShipping(bool $requiresShipping): self
    {
        $this->attributes['requires_shipping'] = $requiresShipping;
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

    // Convenience methods for common scenarios
    public function asElectronics(): self
    {
        return $this->withCategory('Electronics')
                    ->withBrand('TechCorp')
                    ->withIsDigital(false)
                    ->withRequiresShipping(true);
    }

    public function asClothing(): self
    {
        return $this->withCategory('Clothing')
                    ->withBrand('FashionBrand')
                    ->withIsDigital(false)
                    ->withRequiresShipping(true);
    }

    public function asDigital(): self
    {
        return $this->withIsDigital(true)
                    ->withRequiresShipping(false)
                    ->withWeight(0);
    }

    public function asFeatured(): self
    {
        return $this->withIsFeatured(true)
                    ->withIsActive(true);
    }

    public function asOutOfStock(): self
    {
        return $this->withStock(0)
                    ->withIsActive(false);
    }

    public function asLowStock(): self
    {
        return $this->withStock($this->faker->numberBetween(1, 5));
    }

    public function asHighStock(): self
    {
        return $this->withStock($this->faker->numberBetween(50, 100));
    }

    public function asExpensive(): self
    {
        return $this->withPrice($this->faker->randomFloat(2, 500, 2000));
    }

    public function asCheap(): self
    {
        return $this->withPrice($this->faker->randomFloat(2, 1, 50));
    }

    public function asHeavy(): self
    {
        return $this->withWeight($this->faker->randomFloat(2, 10, 100));
    }

    public function asLight(): self
    {
        return $this->withWeight($this->faker->randomFloat(2, 0.1, 1));
    }

    public function withMultipleImages(int $count = 3): self
    {
        $images = [];
        for ($i = 0; $i < $count; $i++) {
            $images[] = $this->faker->imageUrl(800, 600, 'products');
        }
        return $this->withImages($images);
    }

    public function withPopularTags(): self
    {
        $tags = ['popular', 'trending', 'bestseller', 'new', 'sale'];
        return $this->withTags($tags);
    }

    public function build(): Product
    {
        $product = new Product($this->attributes);
        
        // Reset for next use
        $this->reset();
        
        return $product;
    }

    public function create(): Product
    {
        $product = $this->build();
        $product->save();
        return $product;
    }

    public function make(): Product
    {
        return $this->build();
    }

    // Static factory methods for common scenarios
    public static function electronics(): Product
    {
        return self::new()->asElectronics()->create();
    }

    public static function clothing(): Product
    {
        return self::new()->asClothing()->create();
    }

    public static function digital(): Product
    {
        return self::new()->asDigital()->create();
    }

    public static function featured(): Product
    {
        return self::new()->asFeatured()->create();
    }

    public static function outOfStock(): Product
    {
        return self::new()->asOutOfStock()->create();
    }

    public static function lowStock(): Product
    {
        return self::new()->asLowStock()->create();
    }

    public static function highStock(): Product
    {
        return self::new()->asHighStock()->create();
    }

    public static function expensive(): Product
    {
        return self::new()->asExpensive()->create();
    }

    public static function cheap(): Product
    {
        return self::new()->asCheap()->create();
    }

    public static function heavy(): Product
    {
        return self::new()->asHeavy()->create();
    }

    public static function light(): Product
    {
        return self::new()->asLight()->create();
    }

    // Bulk creation methods
    public static function createMany(int $count): array
    {
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $products[] = self::new()->create();
        }
        return $products;
    }

    public static function createElectronics(int $count): array
    {
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $products[] = self::electronics();
        }
        return $products;
    }

    public static function createClothing(int $count): array
    {
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $products[] = self::clothing();
        }
        return $products;
    }

    public static function createFeatured(int $count): array
    {
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $products[] = self::featured();
        }
        return $products;
    }

    public static function createOutOfStock(int $count): array
    {
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $products[] = self::outOfStock();
        }
        return $products;
    }
}
