<?php

namespace Tests\Feature;

use App\Services\StranglerFigService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StranglerFigTest extends TestCase
{
    use RefreshDatabase;

    private StranglerFigService $stranglerFig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stranglerFig = new StranglerFigService();
    }

    /** @test */
    public function it_can_route_requests_to_legacy_system()
    {
        $result = $this->stranglerFig->routeRequest('users', ['user_id' => 1]);

        $this->assertTrue($result['success']);
        $this->assertEquals('legacy', $result['target_system']);
        $this->assertEquals('users', $result['feature']);
    }

    /** @test */
    public function it_can_start_migration()
    {
        $success = $this->stranglerFig->startMigration('users', 10);

        $this->assertTrue($success);
        
        $status = $this->stranglerFig->getFeatureStatus('users');
        $this->assertEquals('migrating', $status['status']);
        $this->assertEquals(10, $status['percentage']);
    }

    /** @test */
    public function it_can_complete_migration()
    {
        $this->stranglerFig->startMigration('users', 50);
        $success = $this->stranglerFig->completeMigration('users');

        $this->assertTrue($success);
        
        $status = $this->stranglerFig->getFeatureStatus('users');
        $this->assertEquals('modern', $status['status']);
        $this->assertEquals(100, $status['percentage']);
    }

    /** @test */
    public function it_can_rollback_migration()
    {
        $this->stranglerFig->startMigration('users', 50);
        $success = $this->stranglerFig->rollbackMigration('users');

        $this->assertTrue($success);
        
        $status = $this->stranglerFig->getFeatureStatus('users');
        $this->assertEquals('legacy', $status['status']);
        $this->assertEquals(0, $status['percentage']);
    }

    /** @test */
    public function it_can_update_migration_percentage()
    {
        $this->stranglerFig->startMigration('users', 10);
        
        $success = $this->stranglerFig->updateMigrationPercentage('users', 75);
        $this->assertTrue($success);
        
        $status = $this->stranglerFig->getFeatureStatus('users');
        $this->assertEquals(75, $status['percentage']);
    }

    /** @test */
    public function it_cannot_update_percentage_for_non_migrating_feature()
    {
        $success = $this->stranglerFig->updateMigrationPercentage('users', 50);
        $this->assertFalse($success);
    }

    /** @test */
    public function it_can_get_migration_status()
    {
        $status = $this->stranglerFig->getMigrationStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('users', $status);
        $this->assertArrayHasKey('products', $status);
        $this->assertArrayHasKey('orders', $status);
    }

    /** @test */
    public function it_can_get_migration_stats()
    {
        $stats = $this->stranglerFig->getMigrationStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_features', $stats);
        $this->assertArrayHasKey('legacy_features', $stats);
        $this->assertArrayHasKey('migrating_features', $stats);
        $this->assertArrayHasKey('modern_features', $stats);
        $this->assertArrayHasKey('migration_progress', $stats);
    }

    /** @test */
    public function it_can_test_feature_routing()
    {
        $result = $this->stranglerFig->testFeature('users', 5);

        $this->assertIsArray($result);
        $this->assertEquals('users', $result['feature']);
        $this->assertEquals(5, $result['total_requests']);
        $this->assertArrayHasKey('legacy_requests', $result);
        $this->assertArrayHasKey('modern_requests', $result);
        $this->assertArrayHasKey('results', $result);
    }

    /** @test */
    public function it_throws_exception_for_unknown_feature()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->stranglerFig->routeRequest('unknown_feature', []);
    }

    /** @test */
    public function it_has_unique_pattern_id()
    {
        $id1 = $this->stranglerFig->getId();
        $id2 = $this->stranglerFig->getId();

        $this->assertStringStartsWith('strangler-fig-pattern-', $id1);
        $this->assertStringStartsWith('strangler-fig-pattern-', $id2);
        $this->assertNotEquals($id1, $id2);
    }

    /** @test */
    public function it_handles_migrating_status_correctly()
    {
        $this->stranglerFig->startMigration('users', 50);
        
        // Test multiple requests to see distribution
        $legacyCount = 0;
        $modernCount = 0;
        
        for ($i = 0; $i < 20; $i++) {
            $result = $this->stranglerFig->routeRequest('users', ['user_id' => $i]);
            
            if ($result['target_system'] === 'legacy') {
                $legacyCount++;
            } else {
                $modernCount++;
            }
        }
        
        // Should have some distribution (not all legacy or all modern)
        $this->assertGreaterThan(0, $legacyCount);
        $this->assertGreaterThan(0, $modernCount);
    }

    /** @test */
    public function it_handles_modern_status_correctly()
    {
        $this->stranglerFig->completeMigration('users');
        
        $result = $this->stranglerFig->routeRequest('users', ['user_id' => 1]);
        
        $this->assertEquals('modern', $result['target_system']);
    }

    /** @test */
    public function it_handles_legacy_status_correctly()
    {
        $result = $this->stranglerFig->routeRequest('users', ['user_id' => 1]);
        
        $this->assertEquals('legacy', $result['target_system']);
    }
}
