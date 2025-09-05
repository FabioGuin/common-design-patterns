<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;
use App\Models\OutboxEvent;
use App\Services\OutboxService;
use App\Services\EventPublisherService;

class OutboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_with_outbox_event()
    {
        $outboxService = new OutboxService();
        
        $orderData = [
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario@example.com',
            'amount' => 100.50,
            'status' => 'pending',
            'notes' => 'Test order'
        ];

        $order = $outboxService->createOrderWithEvent($orderData);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Mario Rossi',
            'amount' => 100.50
        ]);

        $this->assertDatabaseHas('outbox_events', [
            'event_type' => 'OrderCreated',
            'status' => 'pending',
            'aggregate_id' => $order->id
        ]);

        $this->assertEquals('Mario Rossi', $order->customer_name);
        $this->assertEquals(100.50, $order->amount);
    }

    public function test_update_order_with_outbox_event()
    {
        $outboxService = new OutboxService();
        
        // Crea un ordine
        $order = Order::create([
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario@example.com',
            'amount' => 100.50,
            'status' => 'pending'
        ]);

        // Aggiorna l'ordine
        $updatedOrder = $outboxService->updateOrderWithEvent($order->id, [
            'status' => 'processing',
            'amount' => 150.75
        ]);

        $this->assertEquals('processing', $updatedOrder->status);
        $this->assertEquals(150.75, $updatedOrder->amount);

        $this->assertDatabaseHas('outbox_events', [
            'event_type' => 'OrderUpdated',
            'status' => 'pending',
            'aggregate_id' => $order->id
        ]);
    }

    public function test_delete_order_with_outbox_event()
    {
        $outboxService = new OutboxService();
        
        // Crea un ordine
        $order = Order::create([
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario@example.com',
            'amount' => 100.50,
            'status' => 'pending'
        ]);

        // Cancella l'ordine
        $deleted = $outboxService->deleteOrderWithEvent($order->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);

        $this->assertDatabaseHas('outbox_events', [
            'event_type' => 'OrderDeleted',
            'status' => 'pending',
            'aggregate_id' => $order->id
        ]);
    }

    public function test_get_pending_events()
    {
        $outboxService = new OutboxService();
        
        // Crea alcuni eventi
        OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'pending',
            'scheduled_at' => now()
        ]);

        OutboxEvent::create([
            'event_type' => 'OrderUpdated',
            'event_data' => ['order_id' => 2],
            'status' => 'published',
            'scheduled_at' => now()
        ]);

        $pendingEvents = $outboxService->getPendingEvents();

        $this->assertCount(1, $pendingEvents);
        $this->assertEquals('OrderCreated', $pendingEvents->first()->event_type);
    }

    public function test_mark_event_as_published()
    {
        $outboxService = new OutboxService();
        
        $event = OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processing',
            'scheduled_at' => now()
        ]);

        $result = $outboxService->markEventAsPublished($event);

        $this->assertTrue($result);
        $this->assertDatabaseHas('outbox_events', [
            'id' => $event->id,
            'status' => 'published'
        ]);
    }

    public function test_mark_event_as_failed_with_retry()
    {
        $outboxService = new OutboxService();
        
        $event = OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processing',
            'retry_count' => 0,
            'scheduled_at' => now()
        ]);

        $result = $outboxService->markEventAsFailed($event, 'Test error');

        $this->assertTrue($result);
        $this->assertDatabaseHas('outbox_events', [
            'id' => $event->id,
            'status' => 'pending',
            'retry_count' => 1,
            'error_message' => 'Test error'
        ]);
    }

    public function test_mark_event_as_failed_max_retries()
    {
        config(['outbox.max_retries' => 2]);
        
        $outboxService = new OutboxService();
        
        $event = OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processing',
            'retry_count' => 2,
            'scheduled_at' => now()
        ]);

        $result = $outboxService->markEventAsFailed($event, 'Max retries reached');

        $this->assertTrue($result);
        $this->assertDatabaseHas('outbox_events', [
            'id' => $event->id,
            'status' => 'failed',
            'retry_count' => 3,
            'error_message' => 'Max retries reached'
        ]);
    }

    public function test_get_outbox_stats()
    {
        $outboxService = new OutboxService();
        
        // Crea eventi con diversi status
        OutboxEvent::create(['event_type' => 'OrderCreated', 'status' => 'pending', 'scheduled_at' => now()]);
        OutboxEvent::create(['event_type' => 'OrderCreated', 'status' => 'pending', 'scheduled_at' => now()]);
        OutboxEvent::create(['event_type' => 'OrderUpdated', 'status' => 'processing', 'scheduled_at' => now()]);
        OutboxEvent::create(['event_type' => 'OrderUpdated', 'status' => 'published', 'scheduled_at' => now()]);
        OutboxEvent::create(['event_type' => 'OrderDeleted', 'status' => 'failed', 'scheduled_at' => now()]);

        $stats = $outboxService->getOutboxStats();

        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(1, $stats['processing']);
        $this->assertEquals(1, $stats['published']);
        $this->assertEquals(1, $stats['failed']);
        $this->assertEquals(5, $stats['total']);
    }

    public function test_cleanup_published_events()
    {
        $outboxService = new OutboxService();
        
        // Crea eventi pubblicati con date diverse
        OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'status' => 'published',
            'published_at' => now()->subDays(10)
        ]);

        OutboxEvent::create([
            'event_type' => 'OrderUpdated',
            'status' => 'published',
            'published_at' => now()->subDays(5)
        ]);

        OutboxEvent::create([
            'event_type' => 'OrderDeleted',
            'status' => 'pending',
            'scheduled_at' => now()
        ]);

        $deletedCount = $outboxService->cleanupPublishedEvents(7);

        $this->assertEquals(1, $deletedCount);
        $this->assertDatabaseMissing('outbox_events', [
            'event_type' => 'OrderCreated',
            'status' => 'published'
        ]);
        $this->assertDatabaseHas('outbox_events', [
            'event_type' => 'OrderUpdated',
            'status' => 'published'
        ]);
    }

    public function test_event_publisher_service()
    {
        $eventPublisher = new EventPublisherService();
        
        $result = $eventPublisher->publishEvent('OrderCreated', [
            'order_id' => 1,
            'customer_name' => 'Mario Rossi'
        ]);

        $this->assertIsBool($result);
    }

    public function test_outbox_event_model_scopes()
    {
        // Crea eventi con diversi status
        OutboxEvent::create(['event_type' => 'OrderCreated', 'status' => 'pending', 'scheduled_at' => now()]);
        OutboxEvent::create(['event_type' => 'OrderUpdated', 'status' => 'processing', 'scheduled_at' => now()]);
        OutboxEvent::create(['event_type' => 'OrderDeleted', 'status' => 'published', 'scheduled_at' => now()]);
        OutboxEvent::create(['event_type' => 'OrderCancelled', 'status' => 'failed', 'scheduled_at' => now()]);

        $this->assertCount(1, OutboxEvent::pending()->get());
        $this->assertCount(1, OutboxEvent::processing()->get());
        $this->assertCount(1, OutboxEvent::published()->get());
        $this->assertCount(1, OutboxEvent::failed()->get());
    }

    public function test_outbox_event_ready_for_processing()
    {
        $event = OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'status' => 'pending',
            'scheduled_at' => now()->subMinute()
        ]);

        $this->assertTrue($event->isReadyForProcessing());

        $event->update(['scheduled_at' => now()->addMinute()]);
        $this->assertFalse($event->isReadyForProcessing());
    }

    public function test_outbox_event_can_retry()
    {
        config(['outbox.max_retries' => 3]);
        
        $event = OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'status' => 'pending',
            'retry_count' => 2
        ]);

        $this->assertTrue($event->canRetry());

        $event->update(['retry_count' => 3]);
        $this->assertFalse($event->canRetry());
    }

    public function test_outbox_event_is_stuck()
    {
        $event = OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'status' => 'processing',
            'processing_started_at' => now()->subMinutes(10)
        ]);

        $this->assertTrue($event->isStuck());

        $event->update(['processing_started_at' => now()->subMinute()]);
        $this->assertFalse($event->isStuck());
    }

    public function test_order_model_relationships()
    {
        $order = Order::create([
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario@example.com',
            'amount' => 100.50,
            'status' => 'pending'
        ]);

        OutboxEvent::create([
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => $order->id],
            'aggregate_id' => $order->id,
            'status' => 'pending',
            'scheduled_at' => now()
        ]);

        $this->assertCount(1, $order->outboxEvents);
        $this->assertTrue($order->hasPendingOutboxEvents());
    }
}
