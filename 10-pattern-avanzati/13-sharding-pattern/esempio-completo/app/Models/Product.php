<?php

namespace App\Models;

use App\Sharding\ShardingManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = $this->getShardConnection();
    }

    public function getShardConnection(): string
    {
        if (isset($this->attributes['category'])) {
            $shardingManager = app(ShardingManager::class);
            $shard = $shardingManager->getShardForKey('products', $this->attributes['category']);
            return $this->getShardConnectionName($shard);
        }

        return 'shard_1'; // Default shard for new records
    }

    private function getShardConnectionName(string $shard): string
    {
        return match ($shard) {
            'shard_1' => 'shard_1',
            'shard_2' => 'shard_2',
            'shard_3' => 'shard_3',
            default => 'shard_1'
        };
    }

    public function getShardInfo(): array
    {
        if (isset($this->attributes['category'])) {
            $shardingManager = app(ShardingManager::class);
            return $shardingManager->getShardingStatus('products');
        }

        return [];
    }

    public function getShardForCategory(string $category): string
    {
        $shardingManager = app(ShardingManager::class);
        return $shardingManager->getShardForKey('products', $category);
    }

    public static function findByShard(int $productId): ?self
    {
        $shardingManager = app(ShardingManager::class);
        
        // Try to find in all shards
        foreach (['shard_1', 'shard_2', 'shard_3'] as $shard) {
            try {
                $connection = $shardingManager->getConnection($shard);
                $productData = $connection->table('products')->where('id', $productId)->first();
                
                if ($productData) {
                    $product = new static();
                    $product->setRawAttributes((array) $productData);
                    $product->exists = true;
                    $product->connection = $shardingManager->getShardConnectionName($shard);
                    return $product;
                }
            } catch (\Exception $e) {
                // Continue to next shard
                continue;
            }
        }

        return null;
    }

    public static function getAllProducts(): array
    {
        $shardingManager = app(ShardingManager::class);
        return $shardingManager->executeQueryOnAllShards('products', 'SELECT * FROM products');
    }

    public static function getProductsByCategory(string $category): array
    {
        $shardingManager = app(ShardingManager::class);
        $shard = $shardingManager->getShardForKey('products', $category);
        $connection = $shardingManager->getConnection($shard);
        
        return $connection->table('products')
            ->where('category', $category)
            ->get()
            ->toArray();
    }

}
