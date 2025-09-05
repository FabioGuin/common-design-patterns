<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Commands\CreateOrderCommand;
use App\Commands\UpdateOrderCommand;
use App\Commands\CancelOrderCommand;
use App\Services\EventStoreService;
use App\Projections\OrderProjection;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $eventStore;
    protected $orderProjection;

    public function __construct(EventStoreService $eventStore, OrderProjection $orderProjection)
    {
        $this->eventStore = $eventStore;
        $this->orderProjection = $orderProjection;
    }

    /**
     * Mostra l'interfaccia per testare il pattern
     */
    public function index()
    {
        return view('cqrs-event-sourcing.example');
    }

    /**
     * Test del pattern CQRS + Event Sourcing
     */
    public function test()
    {
        try {
            // Test Event Store
            $eventStoreResults = $this->eventStore->testCqrsEventSourcing();
            
            // Test Commands
            $commandResults = $this->testCommands();
            
            // Test Queries
            $queryResults = $this->testQueries();
            
            return response()->json([
                'success' => true,
                'message' => 'Test CQRS + Event Sourcing completato',
                'event_store' => $eventStoreResults,
                'commands' => $commandResults,
                'queries' => $queryResults
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un nuovo ordine (Command)
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'items' => 'sometimes|array',
            'total_amount' => 'sometimes|numeric|min:0'
        ]);

        try {
            $command = new CreateOrderCommand($this->eventStore);
            $result = $command->execute($request->all());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ordine creato con successo',
                    'data' => [
                        'order_id' => $result['order_id'],
                        'event_id' => $result['event_id']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aggiorna un ordine (Command)
     */
    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'status' => 'sometimes|string|in:pending,confirmed,shipped,delivered,cancelled',
            'total_amount' => 'sometimes|numeric|min:0',
            'items' => 'sometimes|array'
        ]);

        try {
            $command = new UpdateOrderCommand($this->eventStore);
            $result = $command->execute($id, $request->all());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ordine aggiornato con successo',
                    'data' => [
                        'order_id' => $result['order_id'],
                        'event_id' => $result['event_id']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancella un ordine (Command)
     */
    public function cancelOrder(Request $request, $id)
    {
        $request->validate([
            'reason' => 'sometimes|string|max:500'
        ]);

        try {
            $command = new CancelOrderCommand($this->eventStore);
            $result = $command->execute($id, $request->input('reason'));

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ordine cancellato con successo',
                    'data' => [
                        'order_id' => $result['order_id'],
                        'event_id' => $result['event_id']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella cancellazione: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene un ordine (Query)
     */
    public function getOrder($id)
    {
        try {
            $order = $this->orderProjection->getOrder($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordine non trovato'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista tutti gli ordini (Query)
     */
    public function listOrders(Request $request)
    {
        try {
            $limit = $request->get('limit', 100);
            $offset = $request->get('offset', 0);
            
            $orders = $this->orderProjection->getAllOrders($limit, $offset);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'count' => count($orders)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene gli eventi di un ordine
     */
    public function getOrderEvents($id)
    {
        try {
            $events = $this->eventStore->getEvents($id);

            return response()->json([
                'success' => true,
                'data' => $events,
                'count' => count($events)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero degli eventi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene l'audit trail di un ordine
     */
    public function getAuditTrail($id)
    {
        try {
            $events = $this->eventStore->getEvents($id);
            
            // Formatta gli eventi per l'audit trail
            $auditTrail = array_map(function($event) {
                return [
                    'event_id' => $event['event_id'],
                    'event_type' => $event['event_type'],
                    'timestamp' => $event['created_at'],
                    'data' => $event['data'],
                    'metadata' => $event['metadata'] ?? []
                ];
            }, $events);

            return response()->json([
                'success' => true,
                'data' => $auditTrail,
                'count' => count($auditTrail)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dell\'audit trail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Replay degli eventi per un ordine
     */
    public function replayEvents($id)
    {
        try {
            $result = $this->eventStore->replayEvents($id);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Eventi riprodotti con successo',
                    'events_replayed' => $result['events_replayed']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel replay: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche degli ordini
     */
    public function getOrderStats()
    {
        try {
            $stats = $this->orderProjection->getOrderStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero delle statistiche: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche dell'Event Store
     */
    public function getEventStoreStats()
    {
        try {
            $stats = $this->eventStore->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero delle statistiche: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test dei Commands
     */
    private function testCommands()
    {
        $results = [];
        
        try {
            // Test CreateOrderCommand
            $createCommand = new CreateOrderCommand($this->eventStore);
            $createResult = $createCommand->execute([
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'items' => [
                    ['product_id' => 1, 'quantity' => 2, 'price' => 50.00]
                ],
                'total_amount' => 100.00
            ]);
            $results['create_command'] = $createResult['success'] ? 'success' : 'failed';
            
            if ($createResult['success']) {
                $orderId = $createResult['order_id'];
                
                // Test UpdateOrderCommand
                $updateCommand = new UpdateOrderCommand($this->eventStore);
                $updateResult = $updateCommand->execute($orderId, [
                    'status' => 'confirmed',
                    'total_amount' => 120.00
                ]);
                $results['update_command'] = $updateResult['success'] ? 'success' : 'failed';
                
                // Test CancelOrderCommand
                $cancelCommand = new CancelOrderCommand($this->eventStore);
                $cancelResult = $cancelCommand->execute($orderId, 'Test cancellation');
                $results['cancel_command'] = $cancelResult['success'] ? 'success' : 'failed';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test delle Queries
     */
    private function testQueries()
    {
        $results = [];
        
        try {
            // Test getOrder
            $orders = $this->orderProjection->getAllOrders(1);
            $results['get_order'] = count($orders) > 0 ? 'success' : 'no_data';
            
            // Test getOrderStats
            $stats = $this->orderProjection->getOrderStats();
            $results['get_stats'] = !empty($stats) ? 'success' : 'failed';
            
            // Test getEvents
            if (count($orders) > 0) {
                $events = $this->eventStore->getEvents($orders[0]['order_id']);
                $results['get_events'] = count($events) > 0 ? 'success' : 'no_events';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
