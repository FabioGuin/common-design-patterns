<?php

namespace App\Adapters;

use App\Ports\OrderRepositoryInterface;
use App\Domain\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    protected $tableName = 'orders';

    public function save(Order $order): Order
    {
        try {
            $orderData = $order->toArray();
            
            // Converte DateTime in string per il database
            $orderData['created_at'] = $orderData['created_at']->format('Y-m-d H:i:s');
            $orderData['updated_at'] = $orderData['updated_at']->format('Y-m-d H:i:s');
            $orderData['cancelled_at'] = $orderData['cancelled_at'] ? $orderData['cancelled_at']->format('Y-m-d H:i:s') : null;
            
            // Converte items in JSON
            $orderData['items'] = json_encode($orderData['items']);
            
            // Verifica se l'ordine esiste già
            if ($this->exists($order->getId())) {
                // Aggiorna
                DB::table($this->tableName)
                    ->where('id', $order->getId())
                    ->update($orderData);
            } else {
                // Inserisci
                DB::table($this->tableName)->insert($orderData);
            }
            
            Log::info("Eloquent Order Repository: Ordine salvato", [
                'order_id' => $order->getId(),
                'status' => $order->getStatus()
            ]);
            
            return $order;
            
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nel salvataggio", [
                'order_id' => $order->getId(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function findById(string $id): ?Order
    {
        try {
            $orderData = DB::table($this->tableName)
                ->where('id', $id)
                ->first();
            
            if (!$orderData) {
                return null;
            }
            
            $orderData = (array) $orderData;
            $orderData['items'] = json_decode($orderData['items'], true) ?? [];
            $orderData['created_at'] = new \DateTime($orderData['created_at']);
            $orderData['updated_at'] = new \DateTime($orderData['updated_at']);
            $orderData['cancelled_at'] = $orderData['cancelled_at'] ? new \DateTime($orderData['cancelled_at']) : null;
            
            return Order::fromArray($orderData);
            
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nel recupero per ID", [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $ordersData = DB::table($this->tableName)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->toArray();
            
            return $this->convertToOrders($ordersData);
            
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nel recupero di tutti gli ordini", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function findByCustomerEmail(string $customerEmail): array
    {
        try {
            $ordersData = DB::table($this->tableName)
                ->where('customer_email', $customerEmail)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
            
            return $this->convertToOrders($ordersData);
            
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nel recupero per email cliente", [
                'customer_email' => $customerEmail,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function findByStatus(string $status): array
    {
        try {
            $ordersData = DB::table($this->tableName)
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
            
            return $this->convertToOrders($ordersData);
            
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nel recupero per status", [
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        try {
            $ordersData = DB::table($this->tableName)
                ->whereBetween('created_at', [
                    $startDate->format('Y-m-d H:i:s'),
                    $endDate->format('Y-m-d H:i:s')
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
            
            return $this->convertToOrders($ordersData);
            
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nel recupero per range di date", [
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function count(): int
    {
        try {
            return DB::table($this->tableName)->count();
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nel conteggio", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function countByStatus(string $status): int
    {
        try {
            return DB::table($this->tableName)
                ->where('status', $status)
                ->count();
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nel conteggio per status", [
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function delete(string $id): bool
    {
        try {
            $deleted = DB::table($this->tableName)
                ->where('id', $id)
                ->delete();
            
            Log::info("Eloquent Order Repository: Ordine eliminato", [
                'order_id' => $id,
                'deleted' => $deleted > 0
            ]);
            
            return $deleted > 0;
            
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nell'eliminazione", [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function exists(string $id): bool
    {
        try {
            return DB::table($this->tableName)
                ->where('id', $id)
                ->exists();
        } catch (\Exception $e) {
            Log::error("Eloquent Order Repository: Errore nella verifica esistenza", [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Converte i dati del database in oggetti Order
     */
    private function convertToOrders(array $ordersData): array
    {
        $orders = [];
        
        foreach ($ordersData as $orderData) {
            $orderData = (array) $orderData;
            $orderData['items'] = json_decode($orderData['items'], true) ?? [];
            $orderData['created_at'] = new \DateTime($orderData['created_at']);
            $orderData['updated_at'] = new \DateTime($orderData['updated_at']);
            $orderData['cancelled_at'] = $orderData['cancelled_at'] ? new \DateTime($orderData['cancelled_at']) : null;
            
            $orders[] = Order::fromArray($orderData);
        }
        
        return $orders;
    }
}
