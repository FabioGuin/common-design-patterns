<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ConnectionPool;
use App\Models\DatabaseConnection;

class ConnectionPoolTest extends TestCase
{
    public function test_pool_acquires_connection()
    {
        $pool = new ConnectionPool(2);
        $connection = $pool->acquire();
        
        $this->assertInstanceOf(DatabaseConnection::class, $connection);
        $this->assertTrue($connection->isInUse());
    }

    public function test_pool_respects_max_size()
    {
        $pool = new ConnectionPool(2);
        
        $conn1 = $pool->acquire();
        $conn2 = $pool->acquire();
        $conn3 = $pool->acquire(); // Dovrebbe essere null
        
        $this->assertNotNull($conn1);
        $this->assertNotNull($conn2);
        $this->assertNull($conn3);
    }

    public function test_pool_releases_connection()
    {
        $pool = new ConnectionPool(2);
        $connection = $pool->acquire();
        
        $this->assertTrue($connection->isInUse());
        
        $pool->release($connection);
        
        $this->assertFalse($connection->isInUse());
    }

    public function test_pool_reuses_released_connection()
    {
        $pool = new ConnectionPool(1);
        
        $conn1 = $pool->acquire();
        $this->assertNotNull($conn1);
        
        $pool->release($conn1);
        
        $conn2 = $pool->acquire();
        $this->assertSame($conn1, $conn2);
    }

    public function test_pool_status_tracking()
    {
        $pool = new ConnectionPool(3);
        
        $status = $pool->getPoolStatus();
        $this->assertEquals(0, $status['total']);
        $this->assertEquals(0, $status['available']);
        $this->assertEquals(0, $status['in_use']);
        $this->assertEquals(3, $status['max_size']);
        
        $conn1 = $pool->acquire();
        $conn2 = $pool->acquire();
        
        $status = $pool->getPoolStatus();
        $this->assertEquals(2, $status['total']);
        $this->assertEquals(0, $status['available']);
        $this->assertEquals(2, $status['in_use']);
        
        $pool->release($conn1);
        
        $status = $pool->getPoolStatus();
        $this->assertEquals(2, $status['total']);
        $this->assertEquals(1, $status['available']);
        $this->assertEquals(1, $status['in_use']);
    }

    public function test_connection_implements_poolable_interface()
    {
        $connection = new DatabaseConnection();
        $this->assertInstanceOf(\App\Models\PoolableInterface::class, $connection);
    }

    public function test_connection_reset_functionality()
    {
        $connection = new DatabaseConnection();
        $connection->setInUse(true);
        
        $this->assertTrue($connection->isInUse());
        
        $connection->reset();
        
        $this->assertFalse($connection->isInUse());
    }

    public function test_connection_to_array_conversion()
    {
        $connection = new DatabaseConnection('localhost', 'test_db');
        $array = $connection->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('host', $array);
        $this->assertArrayHasKey('database', $array);
        $this->assertArrayHasKey('in_use', $array);
        $this->assertArrayHasKey('created_at', $array);
    }

    public function test_pool_connections_list()
    {
        $pool = new ConnectionPool(2);
        $conn1 = $pool->acquire();
        $conn2 = $pool->acquire();
        
        $connections = $pool->getPoolConnections();
        
        $this->assertCount(2, $connections);
        $this->assertArrayHasKey('id', $connections[0]);
        $this->assertArrayHasKey('host', $connections[0]);
    }
}
