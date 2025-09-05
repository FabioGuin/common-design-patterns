<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SingletonModel;

class SingletonTest extends TestCase
{
    /** @test */
    public function it_ensures_single_instance()
    {
        $instance1 = SingletonModel::getInstance();
        $instance2 = SingletonModel::getInstance();
        $instance3 = SingletonModel::getInstance();
        
        // Verifica che tutte le istanze siano la stessa
        $this->assertSame($instance1, $instance2);
        $this->assertSame($instance2, $instance3);
        $this->assertSame($instance1, $instance3);
    }

    /** @test */
    public function it_shares_state_between_instances()
    {
        $instance1 = SingletonModel::getInstance();
        $instance2 = SingletonModel::getInstance();
        
        // Aggiungi dati tramite la prima istanza
        $instance1->addData('test_key', 'test_value');
        
        // Verifica che i dati siano visibili dalla seconda istanza
        $data = $instance2->getData();
        $this->assertArrayHasKey('test_key', $data);
        $this->assertEquals('test_value', $data['test_key']);
    }

    /** @test */
    public function it_tracks_access_count()
    {
        $instance = SingletonModel::getInstance();
        
        // Reset del contatore (per test puliti)
        $initialCount = $instance->getAccessCount();
        
        // Esegui alcune operazioni
        $instance->getId();
        $instance->getData();
        $instance->addData('key', 'value');
        
        // Verifica che il contatore sia aumentato
        $this->assertGreaterThan($initialCount, $instance->getAccessCount());
    }

    /** @test */
    public function it_prevents_cloning()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot clone a singleton instance');
        
        $instance = SingletonModel::getInstance();
        clone $instance;
    }

    /** @test */
    public function it_prevents_unserialization()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot unserialize a singleton instance');
        
        $instance = SingletonModel::getInstance();
        $serialized = serialize($instance);
        unserialize($serialized);
    }

    /** @test */
    public function it_returns_same_id_for_all_instances()
    {
        $instance1 = SingletonModel::getInstance();
        $instance2 = SingletonModel::getInstance();
        
        $this->assertEquals($instance1->getId(), $instance2->getId());
    }

    /** @test */
    public function it_has_consistent_data_structure()
    {
        $instance = SingletonModel::getInstance();
        $data = $instance->getData();
        
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('description', $data);
        
        $this->assertIsString($data['created_at']);
        $this->assertIsString($data['version']);
        $this->assertIsString($data['description']);
    }
}
