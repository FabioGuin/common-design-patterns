<?php

namespace App\Projections;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderProjection
{
    protected $tableName = 'order_projections';

    /**
     * Gestisce l'evento OrderCreated
     */
    public function handleOrderCreated($event)
    {
        try {
            $data = $event->getData();
            
            DB::table($this->tableName)->insert([
                'order_id' => $data['order_id'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'items' => json_encode($data['items'] ?? []),
                'total_amount' => $data['total_amount'] ?? 0,
                'status' => $data['status'] ?? 'pending',
                'created_at' => $data['created_at'] ?? now(),
                'updated_at' => now(),
                'last_event_id' => $event->getEventId(),
                'last_event_type' => 'OrderCreated'
            ]);
            
            Log::info("Order Projection: Ordine creato", [
                'order_id' => $data['order_id'],
                'event_id' => $event->getEventId()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nella gestione OrderCreated", [
                'error' => $e->getMessage(),
                'event_data' => $event->getData()
            ]);
        }
    }

    /**
     * Gestisce l'evento OrderUpdated
     */
    public function handleOrderUpdated($event)
    {
        try {
            $data = $event->getData();
            $orderId = $data['order_id'];
            $updatedFields = $data['updated_fields'] ?? [];
            
            // Prepara i dati da aggiornare
            $updateData = [
                'updated_at' => now(),
                'last_event_id' => $event->getEventId(),
                'last_event_type' => 'OrderUpdated'
            ];
            
            // Aggiorna solo i campi specificati
            if (isset($updatedFields['customer_name'])) {
                $updateData['customer_name'] = $updatedFields['customer_name'];
            }
            if (isset($updatedFields['customer_email'])) {
                $updateData['customer_email'] = $updatedFields['customer_email'];
            }
            if (isset($updatedFields['items'])) {
                $updateData['items'] = json_encode($updatedFields['items']);
            }
            if (isset($updatedFields['total_amount'])) {
                $updateData['total_amount'] = $updatedFields['total_amount'];
            }
            if (isset($updatedFields['status'])) {
                $updateData['status'] = $updatedFields['status'];
            }
            
            DB::table($this->tableName)
                ->where('order_id', $orderId)
                ->update($updateData);
            
            Log::info("Order Projection: Ordine aggiornato", [
                'order_id' => $orderId,
                'event_id' => $event->getEventId(),
                'updated_fields' => array_keys($updatedFields)
            ]);
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nella gestione OrderUpdated", [
                'error' => $e->getMessage(),
                'event_data' => $event->getData()
            ]);
        }
    }

    /**
     * Gestisce l'evento OrderCancelled
     */
    public function handleOrderCancelled($event)
    {
        try {
            $data = $event->getData();
            $orderId = $data['order_id'];
            
            DB::table($this->tableName)
                ->where('order_id', $orderId)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => $data['cancelled_at'] ?? now(),
                    'cancellation_reason' => $data['reason'] ?? null,
                    'updated_at' => now(),
                    'last_event_id' => $event->getEventId(),
                    'last_event_type' => 'OrderCancelled'
                ]);
            
            Log::info("Order Projection: Ordine cancellato", [
                'order_id' => $orderId,
                'event_id' => $event->getEventId(),
                'reason' => $data['reason'] ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nella gestione OrderCancelled", [
                'error' => $e->getMessage(),
                'event_data' => $event->getData()
            ]);
        }
    }

    /**
     * Ottiene un ordine per ID
     */
    public function getOrder(string $orderId)
    {
        try {
            $order = DB::table($this->tableName)
                ->where('order_id', $orderId)
                ->first();
            
            if ($order) {
                $order = (array) $order;
                $order['items'] = json_decode($order['items'], true) ?? [];
                return $order;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nel recupero dell'ordine", [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Ottiene tutti gli ordini
     */
    public function getAllOrders($limit = 100, $offset = 0)
    {
        try {
            $orders = DB::table($this->tableName)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->toArray();
            
            return array_map(function($order) {
                $order = (array) $order;
                $order['items'] = json_decode($order['items'], true) ?? [];
                return $order;
            }, $orders);
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nel recupero degli ordini", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Ottiene ordini per status
     */
    public function getOrdersByStatus(string $status, $limit = 100)
    {
        try {
            $orders = DB::table($this->tableName)
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
            
            return array_map(function($order) {
                $order = (array) $order;
                $order['items'] = json_decode($order['items'], true) ?? [];
                return $order;
            }, $orders);
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nel recupero degli ordini per status", [
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Ottiene ordini per cliente
     */
    public function getOrdersByCustomer(string $customerEmail, $limit = 100)
    {
        try {
            $orders = DB::table($this->tableName)
                ->where('customer_email', $customerEmail)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
            
            return array_map(function($order) {
                $order = (array) $order;
                $order['items'] = json_decode($order['items'], true) ?? [];
                return $order;
            }, $orders);
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nel recupero degli ordini per cliente", [
                'customer_email' => $customerEmail,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Ottiene statistiche degli ordini
     */
    public function getOrderStats()
    {
        try {
            $totalOrders = DB::table($this->tableName)->count();
            
            $ordersByStatus = DB::table($this->tableName)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();
            
            $totalRevenue = DB::table($this->tableName)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            
            $averageOrderValue = DB::table($this->tableName)
                ->where('status', '!=', 'cancelled')
                ->avg('total_amount');
            
            return [
                'total_orders' => $totalOrders,
                'orders_by_status' => $ordersByStatus,
                'total_revenue' => $totalRevenue,
                'average_order_value' => $averageOrderValue
            ];
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nel recupero delle statistiche", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Ricostruisce la projection da zero
     */
    public function rebuild()
    {
        try {
            // Pulisce la projection
            DB::table($this->tableName)->truncate();
            
            // Ottiene tutti gli eventi ordinati per data
            $events = DB::table('events')
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
            
            $processed = 0;
            
            foreach ($events as $eventData) {
                $eventData = (array) $eventData;
                
                switch ($eventData['event_type']) {
                    case 'OrderCreated':
                        $this->handleOrderCreatedFromData($eventData);
                        break;
                    case 'OrderUpdated':
                        $this->handleOrderUpdatedFromData($eventData);
                        break;
                    case 'OrderCancelled':
                        $this->handleOrderCancelledFromData($eventData);
                        break;
                }
                
                $processed++;
            }
            
            Log::info("Order Projection: Rebuild completato", [
                'events_processed' => $processed
            ]);
            
            return [
                'success' => true,
                'events_processed' => $processed
            ];
            
        } catch (\Exception $e) {
            Log::error("Order Projection: Errore nel rebuild", [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Gestisce OrderCreated dai dati salvati
     */
    private function handleOrderCreatedFromData(array $eventData)
    {
        $data = $eventData['data'];
        
        DB::table($this->tableName)->insert([
            'order_id' => $data['order_id'],
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'items' => json_encode($data['items'] ?? []),
            'total_amount' => $data['total_amount'] ?? 0,
            'status' => $data['status'] ?? 'pending',
            'created_at' => $data['created_at'] ?? now(),
            'updated_at' => now(),
            'last_event_id' => $eventData['event_id'],
            'last_event_type' => 'OrderCreated'
        ]);
    }

    /**
     * Gestisce OrderUpdated dai dati salvati
     */
    private function handleOrderUpdatedFromData(array $eventData)
    {
        $data = $eventData['data'];
        $orderId = $data['order_id'];
        $updatedFields = $data['updated_fields'] ?? [];
        
        $updateData = [
            'updated_at' => now(),
            'last_event_id' => $eventData['event_id'],
            'last_event_type' => 'OrderUpdated'
        ];
        
        if (isset($updatedFields['customer_name'])) {
            $updateData['customer_name'] = $updatedFields['customer_name'];
        }
        if (isset($updatedFields['customer_email'])) {
            $updateData['customer_email'] = $updatedFields['customer_email'];
        }
        if (isset($updatedFields['items'])) {
            $updateData['items'] = json_encode($updatedFields['items']);
        }
        if (isset($updatedFields['total_amount'])) {
            $updateData['total_amount'] = $updatedFields['total_amount'];
        }
        if (isset($updatedFields['status'])) {
            $updateData['status'] = $updatedFields['status'];
        }
        
        DB::table($this->tableName)
            ->where('order_id', $orderId)
            ->update($updateData);
    }

    /**
     * Gestisce OrderCancelled dai dati salvati
     */
    private function handleOrderCancelledFromData(array $eventData)
    {
        $data = $eventData['data'];
        $orderId = $data['order_id'];
        
        DB::table($this->tableName)
            ->where('order_id', $orderId)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => $data['cancelled_at'] ?? now(),
                'cancellation_reason' => $data['reason'] ?? null,
                'updated_at' => now(),
                'last_event_id' => $eventData['event_id'],
                'last_event_type' => 'OrderCancelled'
            ]);
    }
}
