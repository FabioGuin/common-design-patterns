<?php

namespace Tests\Feature;

use App\Services\LoadBalancerService;
use App\Services\HealthCheckerService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoadBalancerTest extends TestCase
{
    use RefreshDatabase;

    private LoadBalancerService $loadBalancer;
    private HealthCheckerService $healthChecker;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->healthChecker = new HealthCheckerService();
        $this->loadBalancer = new LoadBalancerService($this->healthChecker);
    }

    /** @test */
    public function it_can_add_servers()
    {
        $this->loadBalancer->addServer('test-server-1', 'http://localhost:8001', 1);
        $this->loadBalancer->addServer('test-server-2', 'http://localhost:8002', 2);

        $servers = $this->loadBalancer->getServers();

        $this->assertCount(5, $servers); // 3 default + 2 aggiunti
        $this->assertArrayHasKey('test-server-1', $servers);
        $this->assertArrayHasKey('test-server-2', $servers);
        $this->assertEquals(1, $servers['test-server-1']['weight']);
        $this->assertEquals(2, $servers['test-server-2']['weight']);
    }

    /** @test */
    public function it_can_remove_servers()
    {
        $this->loadBalancer->addServer('test-server', 'http://localhost:8001');
        
        $removed = $this->loadBalancer->removeServer('test-server');
        
        $this->assertTrue($removed);
        $this->assertArrayNotHasKey('test-server', $this->loadBalancer->getServers());
    }

    /** @test */
    public function it_can_set_algorithm()
    {
        $this->loadBalancer->setAlgorithm('least_connections');
        
        $this->assertEquals('least_connections', $this->loadBalancer->getAlgorithm());
    }

    /** @test */
    public function it_can_select_server_with_round_robin()
    {
        $this->loadBalancer->setAlgorithm('round_robin');
        
        $servers = [];
        for ($i = 0; $i < 6; $i++) {
            $server = $this->loadBalancer->selectServer();
            $servers[] = $server['id'];
        }

        // Con 3 server, dovremmo vedere una distribuzione ciclica
        $this->assertEquals('server-1', $servers[0]);
        $this->assertEquals('server-2', $servers[1]);
        $this->assertEquals('server-3', $servers[2]);
        $this->assertEquals('server-1', $servers[3]);
        $this->assertEquals('server-2', $servers[4]);
        $this->assertEquals('server-3', $servers[5]);
    }

    /** @test */
    public function it_can_route_requests()
    {
        $result = $this->loadBalancer->routeRequest(['ip' => '127.0.0.1']);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('server', $result);
        $this->assertArrayHasKey('response', $result);
        $this->assertArrayHasKey('response_time', $result);
    }

    /** @test */
    public function it_returns_null_when_no_servers_available()
    {
        // Rimuovi tutti i server
        $servers = $this->loadBalancer->getServers();
        foreach (array_keys($servers) as $serverId) {
            $this->loadBalancer->removeServer($serverId);
        }

        $server = $this->loadBalancer->selectServer();

        $this->assertNull($server);
    }

    /** @test */
    public function it_tracks_server_statistics()
    {
        $this->loadBalancer->routeRequest();
        $this->loadBalancer->routeRequest();

        $stats = $this->loadBalancer->getServerStats();

        $this->assertNotEmpty($stats);
        // Verifica che almeno un server abbia ricevuto richieste
        $totalRequests = array_sum(array_column($stats, 'requests'));
        $this->assertGreaterThan(0, $totalRequests);
    }

    /** @test */
    public function it_handles_weighted_selection()
    {
        $this->loadBalancer->setAlgorithm('weighted');
        
        // Server-3 ha peso 2, altri hanno peso 1
        $selections = [];
        for ($i = 0; $i < 100; $i++) {
            $server = $this->loadBalancer->selectServer();
            $selections[] = $server['id'];
        }

        $counts = array_count_values($selections);
        
        // Server-3 dovrebbe essere selezionato più spesso
        $this->assertGreaterThan($counts['server-1'], $counts['server-3']);
        $this->assertGreaterThan($counts['server-2'], $counts['server-3']);
    }

    /** @test */
    public function it_handles_ip_hash_selection()
    {
        $this->loadBalancer->setAlgorithm('ip_hash');
        
        $server1 = $this->loadBalancer->selectServer(['ip' => '192.168.1.100']);
        $server2 = $this->loadBalancer->selectServer(['ip' => '192.168.1.100']); // Stesso IP
        
        // Stesso IP dovrebbe dare stesso server
        $this->assertEquals($server1['id'], $server2['id']);
    }

    /** @test */
    public function it_has_unique_pattern_id()
    {
        $id1 = $this->loadBalancer->getId();
        $id2 = $this->loadBalancer->getId();

        $this->assertStringStartsWith('load-balancer-pattern-', $id1);
        $this->assertStringStartsWith('load-balancer-pattern-', $id2);
        $this->assertNotEquals($id1, $id2);
    }

    /** @test */
    public function it_can_handle_health_checker_integration()
    {
        // Simula server sano
        $this->healthChecker->simulateHealthCheck('http://localhost:8001', true);
        
        $isHealthy = $this->healthChecker->isHealthy('http://localhost:8001');
        
        $this->assertTrue($isHealthy);
    }

    /** @test */
    public function it_can_handle_unhealthy_servers()
    {
        // Simula server non sano
        $this->healthChecker->simulateHealthCheck('http://localhost:8001', false);
        
        $isHealthy = $this->healthChecker->isHealthy('http://localhost:8001');
        
        $this->assertFalse($isHealthy);
    }

    /** @test */
    public function it_can_get_health_stats()
    {
        $this->healthChecker->simulateHealthCheck('http://localhost:8001', true);
        $this->healthChecker->simulateHealthCheck('http://localhost:8002', false);
        
        $stats = $this->healthChecker->getHealthStats();
        
        $this->assertEquals(2, $stats['total_servers']);
        $this->assertEquals(1, $stats['healthy_servers']);
        $this->assertEquals(1, $stats['unhealthy_servers']);
        $this->assertEquals(50.0, $stats['health_rate']);
    }

    /** @test */
    public function it_can_clear_health_cache()
    {
        $this->healthChecker->simulateHealthCheck('http://localhost:8001', true);
        $this->healthChecker->clearHealthCache();
        
        $stats = $this->healthChecker->getHealthStats();
        
        $this->assertEquals(0, $stats['total_servers']);
    }
}
