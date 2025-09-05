<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\EventBusService;
use App\Models\SagaEvent;

class SagaChoreographyTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_saga_choreography()
    {
        $eventBus = new EventBusService();
        
        // Simula un saga di successo
        $sagaId = 'test-saga-' . time();
        
        // Avvia il saga
        $response = $this->postJson('/saga-choreography/start', [
            'user_id' => 1,
            'product_id' => 1,
            'quantity' => 2,
            'amount' => 100.00,
            'scenario' => 'success'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'saga_id',
                    'message'
                ]);
        
        $this->assertTrue($response->json('success'));
    }

    public function test_user_validation_failure_scenario()
    {
        $response = $this->postJson('/saga-choreography/start', [
            'user_id' => 999, // Utente inesistente
            'product_id' => 1,
            'quantity' => 2,
            'amount' => 100.00,
            'scenario' => 'user_validation_fail'
        ]);
        
        $response->assertStatus(200);
        
        // Verifica che il saga sia stato avviato ma fallirà
        $this->assertTrue($response->json('success'));
    }

    public function test_inventory_failure_scenario()
    {
        $response = $this->postJson('/saga-choreography/start', [
            'user_id' => 1,
            'product_id' => 999, // Prodotto inesistente
            'quantity' => 1000, // Quantità eccessiva
            'amount' => 100.00,
            'scenario' => 'inventory_fail'
        ]);
        
        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_payment_failure_scenario()
    {
        $response = $this->postJson('/saga-choreography/start', [
            'user_id' => 1,
            'product_id' => 1,
            'quantity' => 2,
            'amount' => 0.01, // Importo troppo basso
            'scenario' => 'payment_fail'
        ]);
        
        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_get_events_endpoint()
    {
        // Prima crea un saga
        $response = $this->postJson('/saga-choreography/start', [
            'user_id' => 1,
            'product_id' => 1,
            'quantity' => 2,
            'amount' => 100.00,
            'scenario' => 'success'
        ]);
        
        $sagaId = $response->json('saga_id');
        
        // Poi recupera gli eventi
        $eventsResponse = $this->getJson("/saga-choreography/events/{$sagaId}");
        
        $eventsResponse->assertStatus(200)
                     ->assertJsonStructure([
                         'events' => [
                             '*' => [
                                 'event_type',
                                 'description',
                                 'status',
                                 'timestamp'
                             ]
                         ],
                         'completed',
                         'success'
                     ]);
    }

    public function test_get_status_endpoint()
    {
        // Prima crea un saga
        $response = $this->postJson('/saga-choreography/start', [
            'user_id' => 1,
            'product_id' => 1,
            'quantity' => 2,
            'amount' => 100.00,
            'scenario' => 'success'
        ]);
        
        $sagaId = $response->json('saga_id');
        
        // Poi recupera lo status
        $statusResponse = $this->getJson("/saga-choreography/status/{$sagaId}");
        
        $statusResponse->assertStatus(200)
                      ->assertJsonStructure([
                          'saga_id',
                          'status',
                          'completed',
                          'success',
                          'events_count'
                      ]);
    }

    public function test_event_bus_service()
    {
        $eventBus = new EventBusService();
        
        // Testa la registrazione di un listener
        $eventBus->subscribe('UserValidated', function($event) {
            return ['handled' => true];
        });
        
        // Testa la pubblicazione di un evento
        $result = $eventBus->publish('UserValidated', [
            'user_id' => 1,
            'validated' => true
        ]);
        
        $this->assertNotEmpty($result);
    }

    public function test_saga_event_model()
    {
        $sagaEvent = SagaEvent::create([
            'saga_id' => 'test-saga-123',
            'event_type' => 'UserValidated',
            'description' => 'Utente validato con successo',
            'status' => 'success',
            'data' => ['user_id' => 1]
        ]);
        
        $this->assertDatabaseHas('saga_events', [
            'saga_id' => 'test-saga-123',
            'event_type' => 'UserValidated',
            'status' => 'success'
        ]);
        
        $this->assertEquals('test-saga-123', $sagaEvent->saga_id);
        $this->assertEquals('UserValidated', $sagaEvent->event_type);
        $this->assertEquals('success', $sagaEvent->status);
    }

    public function test_compensation_events()
    {
        $eventBus = new EventBusService();
        
        // Registra listener per eventi di compensazione
        $eventBus->subscribe('InventoryReleaseRequested', function($event) {
            return ['compensated' => true];
        });
        
        $eventBus->subscribe('OrderCancellationRequested', function($event) {
            return ['compensated' => true];
        });
        
        // Pubblica eventi di compensazione
        $inventoryResult = $eventBus->publish('InventoryReleaseRequested', [
            'product_id' => 1,
            'quantity' => 2
        ]);
        
        $orderResult = $eventBus->publish('OrderCancellationRequested', [
            'order_id' => 123
        ]);
        
        $this->assertNotEmpty($inventoryResult);
        $this->assertNotEmpty($orderResult);
    }
}
