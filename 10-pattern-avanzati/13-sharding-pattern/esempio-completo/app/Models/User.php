<?php

namespace App\Models;

use App\Sharding\ShardingManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = $this->getShardConnection();
    }

    public function getShardConnection(): string
    {
        if (isset($this->attributes['id'])) {
            $shardingManager = app(ShardingManager::class);
            $shard = $shardingManager->getShardForKey('users', $this->attributes['id']);
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
        if (isset($this->attributes['id'])) {
            $shardingManager = app(ShardingManager::class);
            return $shardingManager->getShardingStatus('users');
        }

        return [];
    }

    public function getShardForUser(int $userId): string
    {
        $shardingManager = app(ShardingManager::class);
        return $shardingManager->getShardForKey('users', $userId);
    }

    public static function findByShard(int $userId): ?self
    {
        $shardingManager = app(ShardingManager::class);
        $shard = $shardingManager->getShardForKey('users', $userId);
        
        $connection = $shardingManager->getConnection($shard);
        $userData = $connection->table('users')->where('id', $userId)->first();
        
        if (!$userData) {
            return null;
        }

        $user = new static();
        $user->setRawAttributes((array) $userData);
        $user->exists = true;
        $user->connection = $shardingManager->getShardConnectionName($shard);

        return $user;
    }

    public static function getAllUsers(): array
    {
        $shardingManager = app(ShardingManager::class);
        return $shardingManager->executeQueryOnAllShards('users', 'SELECT * FROM users');
    }

}
