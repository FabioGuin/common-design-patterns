<?php

namespace App\Models;

use App\Sharding\ShardingManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'total_amount',
        'status',
        'order_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_date' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = $this->getShardConnection();
    }

    public function getShardConnection(): string
    {
        if (isset($this->attributes['order_date'])) {
            $shardingManager = app(ShardingManager::class);
            $shard = $shardingManager->getShardForKey('orders', $this->attributes['order_date']);
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
        if (isset($this->attributes['order_date'])) {
            $shardingManager = app(ShardingManager::class);
            return $shardingManager->getShardingStatus('orders');
        }

        return [];
    }

    public function getShardForDate(string $date): string
    {
        $shardingManager = app(ShardingManager::class);
        return $shardingManager->getShardForKey('orders', $date);
    }

    public static function findByShard(int $orderId): ?self
    {
        $shardingManager = app(ShardingManager::class);
        
        // Try to find in all shards
        foreach (['shard_1', 'shard_2', 'shard_3'] as $shard) {
            try {
                $connection = $shardingManager->getConnection($shard);
                $orderData = $connection->table('orders')->where('id', $orderId)->first();
                
                if ($orderData) {
                    $order = new static();
                    $order->setRawAttributes((array) $orderData);
                    $order->exists = true;
                    $order->connection = $shardingManager->getShardConnectionName($shard);
                    return $order;
                }
            } catch (\Exception $e) {
                // Continue to next shard
                continue;
            }
        }

        return null;
    }

    public static function getAllOrders(): array
    {
        $shardingManager = app(ShardingManager::class);
        return $shardingManager->executeQueryOnAllShards('orders', 'SELECT * FROM orders');
    }

    public static function getOrdersByDateRange(string $startDate, string $endDate): array
    {
        $shardingManager = app(ShardingManager::class);
        $results = [];
        
        // Get orders from all shards and filter by date range
        $allOrders = $shardingManager->executeQueryOnAllShards('orders', 'SELECT * FROM orders');
        
        foreach ($allOrders as $order) {
            $orderDate = $order->order_date ?? $order->created_at;
            if ($orderDate >= $startDate && $orderDate <= $endDate) {
                $results[] = $order;
            }
        }

        return $results;
    }

}
