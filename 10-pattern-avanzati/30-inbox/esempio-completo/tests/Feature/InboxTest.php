<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;
use App\Models\InboxEvent;
use App\Services\InboxService;
use App\Services\EventConsumerService;

class InboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_receive_new_event()
    {
        $inboxService = new InboxService();
        
        $eventId = 'test-event-123';
        $eventType = 'OrderCreated';
        $eventData = ['order_id' => 1, 'customer_name' => 'Mario Rossi'];

        $inboxEvent = $inboxService->receiveEvent($eventId, $eventType, $eventData);

        $this->assertDatabaseHas('inbox_events', [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'status' => 'pending'
        ]);

        $this->assertEquals($eventId, $inboxEvent->event_id);
        $this->assertEquals($eventType, $inboxEvent->event_type);
        $this->assertEquals('pending', $inboxEvent->status);
    }

    public function test_receive_duplicate_event()
    {
        $inboxService = new InboxService();
        
        $eventId = 'test-event-123';
        $eventType = 'OrderCreated';
        $eventData = ['order_id' => 1];

        // Ricevi l'evento la prima volta
        $firstEvent = $inboxService->receiveEvent($eventId, $eventType, $eventData);
        
        // Prova a ricevere lo stesso evento
        $secondEvent = $inboxService->receiveEvent($eventId, $eventType, $eventData);

        // Dovrebbe restituire lo stesso evento
        $this->assertEquals($firstEvent->id, $secondEvent->id);
        $this->assertEquals($firstEvent->event_id, $secondEvent->event_id);
    }

    public function test_get_pending_events()
    {
        $inboxService = new InboxService();
        
        // Crea alcuni eventi
        InboxEvent::create([
            'event_id' => 'event-1',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'pending',
            'scheduled_at' => now()
        ]);

        InboxEvent::create([
            'event_id' => 'event-2',
            'event_type' => 'OrderUpdated',
            'event_data' => ['order_id' => 2],
            'status' => 'processed',
            'scheduled_at' => now()
        ]);

        $pendingEvents = $inboxService->getPendingEvents();

        $this->assertCount(1, $pendingEvents);
        $this->assertEquals('OrderCreated', $pendingEvents->first()->event_type);
    }

    public function test_mark_event_as_processing()
    {
        $inboxService = new InboxService();
        
        $event = InboxEvent::create([
            'event_id' => 'test-event-123',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'pending',
            'scheduled_at' => now()
        ]);

        $result = $inboxService->markEventAsProcessing($event);

        $this->assertTrue($result);
        $this->assertDatabaseHas('inbox_events', [
            'id' => $event->id,
            'status' => 'processing'
        ]);
    }

    public function test_mark_event_as_processed()
    {
        $inboxService = new InboxService();
        
        $event = InboxEvent::create([
            'event_id' => 'test-event-123',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processing',
            'scheduled_at' => now()
        ]);

        $result = $inboxService->markEventAsProcessed($event);

        $this->assertTrue($result);
        $this->assertDatabaseHas('inbox_events', [
            'id' => $event->id,
            'status' => 'processed'
        ]);
    }

    public function test_mark_event_as_failed_with_retry()
    {
        $inboxService = new InboxService();
        
        $event = InboxEvent::create([
            'event_id' => 'test-event-123',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processing',
            'retry_count' => 0,
            'scheduled_at' => now()
        ]);

        $result = $inboxService->markEventAsFailed($event, 'Test error');

        $this->assertTrue($result);
        $this->assertDatabaseHas('inbox_events', [
            'id' => $event->id,
            'status' => 'pending',
            'retry_count' => 1,
            'error_message' => 'Test error'
        ]);
    }

    public function test_mark_event_as_failed_max_retries()
    {
        config(['inbox.max_retries' => 2]);
        
        $inboxService = new InboxService();
        
        $event = InboxEvent::create([
            'event_id' => 'test-event-123',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processing',
            'retry_count' => 2,
            'scheduled_at' => now()
        ]);

        $result = $inboxService->markEventAsFailed($event, 'Max retries reached');

        $this->assertTrue($result);
        $this->assertDatabaseHas('inbox_events', [
            'id' => $event->id,
            'status' => 'failed',
            'retry_count' => 3,
            'error_message' => 'Max retries reached'
        ]);
    }

    public function test_is_event_processed()
    {
        $inboxService = new InboxService();
        
        $eventId = 'test-event-123';
        
        // Evento non processato
        $this->assertFalse($inboxService->isEventProcessed($eventId));
        
        // Crea evento processato
        InboxEvent::create([
            'event_id' => $eventId,
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processed',
            'scheduled_at' => now()
        ]);
        
        $this->assertTrue($inboxService->isEventProcessed($eventId));
    }

    public function test_is_event_stuck()
    {
        $inboxService = new InboxService();
        
        $event = InboxEvent::create([
            'event_id' => 'test-event-123',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processing',
            'processing_started_at' => now()->subMinutes(10)
        ]);

        $this->assertTrue($inboxService->isEventStuck($event));

        $event->update(['processing_started_at' => now()->subMinute()]);
        $this->assertFalse($inboxService->isEventStuck($event));
    }

    public function test_restore_stuck_events()
    {
        $inboxService = new InboxService();
        
        // Crea evento stuck
        InboxEvent::create([
            'event_id' => 'stuck-event-123',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processing',
            'processing_started_at' => now()->subMinutes(10)
        ]);

        $restoredCount = $inboxService->restoreStuckEvents();

        $this->assertEquals(1, $restoredCount);
        $this->assertDatabaseHas('inbox_events', [
            'event_id' => 'stuck-event-123',
            'status' => 'failed'
        ]);
    }

    public function test_cleanup_processed_events()
    {
        $inboxService = new InboxService();
        
        // Crea eventi processati con date diverse
        InboxEvent::create([
            'event_id' => 'old-event-123',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => 1],
            'status' => 'processed',
            'processed_at' => now()->subDays(10)
        ]);

        InboxEvent::create([
            'event_id' => 'recent-event-123',
            'event_type' => 'OrderUpdated',
            'event_data' => ['order_id' => 2],
            'status' => 'processed',
            'processed_at' => now()->subDays(5)
        ]);

        InboxEvent::create([
            'event_id' => 'pending-event-123',
            'event_type' => 'OrderDeleted',
            'event_data' => ['order_id' => 3],
            'status' => 'pending',
            'scheduled_at' => now()
        ]);

        $deletedCount = $inboxService->cleanupProcessedEvents(7);

        $this->assertEquals(1, $deletedCount);
        $this->assertDatabaseMissing('inbox_events', [
            'event_id' => 'old-event-123'
        ]);
        $this->assertDatabaseHas('inbox_events', [
            'event_id' => 'recent-event-123'
        ]);
    }

    public function test_get_inbox_stats()
    {
        $inboxService = new InboxService();
        
        // Crea eventi con diversi status
        InboxEvent::create(['event_id' => 'event-1', 'event_type' => 'OrderCreated', 'status' => 'pending', 'scheduled_at' => now()]);
        InboxEvent::create(['event_id' => 'event-2', 'event_type' => 'OrderCreated', 'status' => 'pending', 'scheduled_at' => now()]);
        InboxEvent::create(['event_id' => 'event-3', 'event_type' => 'OrderUpdated', 'status' => 'processing', 'scheduled_at' => now()]);
        InboxEvent::create(['event_id' => 'event-4', 'event_type' => 'OrderUpdated', 'status' => 'processed', 'scheduled_at' => now()]);
        InboxEvent::create(['event_id' => 'event-5', 'event_type' => 'OrderDeleted', 'status' => 'failed', 'scheduled_at' => now()]);

        $stats = $inboxService->getInboxStats();

        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(1, $stats['processing']);
        $this->assertEquals(1, $stats['processed']);
        $this->assertEquals(1, $stats['failed']);
        $this->assertEquals(5, $stats['total']);
    }

    public function test_event_consumer_service()
    {
        $eventConsumer = new EventConsumerService();
        
        $result = $eventConsumer->consumeEvent([
            'event_id' => 'test-123',
            'event_type' => 'OrderCreated',
            'order_id' => 1
        ]);

        $this->assertIsBool($result);
    }

    public function test_inbox_event_model_scopes()
    {
        // Crea eventi con diversi status
        InboxEvent::create(['event_id' => 'event-1', 'event_type' => 'OrderCreated', 'status' => 'pending', 'scheduled_at' => now()]);
        InboxEvent::create(['event_id' => 'event-2', 'event_type' => 'OrderUpdated', 'status' => 'processing', 'scheduled_at' => now()]);
        InboxEvent::create(['event_id' => 'event-3', 'event_type' => 'OrderDeleted', 'status' => 'processed', 'scheduled_at' => now()]);
        InboxEvent::create(['event_id' => 'event-4', 'event_type' => 'PaymentProcessed', 'status' => 'failed', 'scheduled_at' => now()]);

        $this->assertCount(1, InboxEvent::pending()->get());
        $this->assertCount(1, InboxEvent::processing()->get());
        $this->assertCount(1, InboxEvent::processed()->get());
        $this->assertCount(1, InboxEvent::failed()->get());
    }

    public function test_inbox_event_ready_for_processing()
    {
        $event = InboxEvent::create([
            'event_id' => 'test-event-123',
            'event_type' => 'OrderCreated',
            'status' => 'pending',
            'scheduled_at' => now()->subMinute()
        ]);

        $this->assertTrue($event->isReadyForProcessing());

        $event->update(['scheduled_at' => now()->addMinute()]);
        $this->assertFalse($event->isReadyForProcessing());
    }

    public function test_inbox_event_can_retry()
    {
        config(['inbox.max_retries' => 3]);
        
        $event = InboxEvent::create([
            'event_id' => 'test-event-123',
            'event_type' => 'OrderCreated',
            'status' => 'pending',
            'retry_count' => 2
        ]);

        $this->assertTrue($event->canRetry());

        $event->update(['retry_count' => 3]);
        $this->assertFalse($event->canRetry());
    }

    public function test_inbox_event_is_stuck()
    {
        $event = InboxEvent::create([
            'event_id' => 'test-event-123',
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

        InboxEvent::create([
            'event_id' => 'test-event-123',
            'event_type' => 'OrderCreated',
            'event_data' => ['order_id' => $order->id],
            'status' => 'pending',
            'scheduled_at' => now()
        ]);

        $this->assertCount(1, $order->getInboxEvents());
        $this->assertTrue($order->hasPendingInboxEvents());
    }
}
