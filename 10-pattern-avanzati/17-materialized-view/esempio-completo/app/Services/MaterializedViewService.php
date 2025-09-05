<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MaterializedViewService
{
    protected $views = [
        'sales_by_category' => [
            'query' => 'SELECT c.id as category_id, c.name as category_name, 
                       SUM(oi.quantity * oi.price) as total_sales,
                       SUM(oi.quantity) as total_quantity,
                       COUNT(DISTINCT o.id) as total_orders,
                       AVG(oi.quantity * oi.price) as avg_order_value
                       FROM categories c
                       LEFT JOIN products p ON c.id = p.category_id
                       LEFT JOIN order_items oi ON p.id = oi.product_id
                       LEFT JOIN orders o ON oi.order_id = o.id AND o.status = "completed"
                       GROUP BY c.id, c.name
                       ORDER BY total_sales DESC',
            'table' => 'mv_sales_by_category',
            'refresh_frequency' => 'hourly'
        ],
        'sales_by_month' => [
            'query' => 'SELECT YEAR(o.order_date) as year,
                       MONTH(o.order_date) as month,
                       SUM(o.total_amount) as total_sales,
                       COUNT(o.id) as total_orders,
                       AVG(o.total_amount) as avg_order_value
                       FROM orders o
                       WHERE o.status = "completed"
                       GROUP BY YEAR(o.order_date), MONTH(o.order_date)
                       ORDER BY year DESC, month DESC',
            'table' => 'mv_sales_by_month',
            'refresh_frequency' => 'daily'
        ],
        'top_products' => [
            'query' => 'SELECT p.id as product_id, p.name as product_name,
                       c.name as category_name,
                       SUM(oi.quantity * oi.price) as total_sales,
                       SUM(oi.quantity) as total_quantity,
                       COUNT(DISTINCT o.id) as total_orders
                       FROM products p
                       LEFT JOIN categories c ON p.category_id = c.id
                       LEFT JOIN order_items oi ON p.id = oi.product_id
                       LEFT JOIN orders o ON oi.order_id = o.id AND o.status = "completed"
                       GROUP BY p.id, p.name, c.name
                       HAVING total_sales > 0
                       ORDER BY total_sales DESC
                       LIMIT 100',
            'table' => 'mv_top_products',
            'refresh_frequency' => 'hourly'
        ],
        'daily_sales' => [
            'query' => 'SELECT DATE(o.order_date) as sale_date,
                       SUM(o.total_amount) as total_sales,
                       COUNT(o.id) as total_orders,
                       AVG(o.total_amount) as avg_order_value
                       FROM orders o
                       WHERE o.status = "completed"
                       GROUP BY DATE(o.order_date)
                       ORDER BY sale_date DESC
                       LIMIT 365',
            'table' => 'mv_daily_sales',
            'refresh_frequency' => 'daily'
        ]
    ];

    /**
     * Crea tutte le viste materializzate
     */
    public function createAllViews()
    {
        $results = [];
        
        foreach ($this->views as $viewName => $config) {
            try {
                $this->createView($viewName, $config);
                $results[$viewName] = 'created';
                Log::info("Materialized View: Vista {$viewName} creata");
            } catch (\Exception $e) {
                $results[$viewName] = 'error: ' . $e->getMessage();
                Log::error("Materialized View: Errore nella creazione di {$viewName}: " . $e->getMessage());
            }
        }
        
        return $results;
    }

    /**
     * Crea una vista materializzata specifica
     */
    public function createView($viewName, $config)
    {
        $tableName = $config['table'];
        
        // Crea la tabella se non esiste
        if (!$this->tableExists($tableName)) {
            $this->createTable($tableName, $config['query']);
        }
        
        // Popola la vista
        $this->refreshView($viewName);
        
        return true;
    }

    /**
     * Aggiorna una vista materializzata specifica
     */
    public function refreshView($viewName)
    {
        if (!isset($this->views[$viewName])) {
            throw new \InvalidArgumentException("Vista {$viewName} non trovata");
        }
        
        $config = $this->views[$viewName];
        $tableName = $config['table'];
        
        try {
            // Truncate e ricarica
            DB::table($tableName)->truncate();
            
            // Esegue la query e inserisce i risultati
            $results = DB::select($config['query']);
            
            if (!empty($results)) {
                $chunks = array_chunk($results, 1000);
                foreach ($chunks as $chunk) {
                    DB::table($tableName)->insert($this->prepareDataForInsert($chunk));
                }
            }
            
            // Aggiorna il timestamp
            $this->updateViewTimestamp($viewName);
            
            Log::info("Materialized View: Vista {$viewName} aggiornata");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Materialized View: Errore nell'aggiornamento di {$viewName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Aggiorna tutte le viste materializzate
     */
    public function refreshAllViews()
    {
        $results = [];
        
        foreach ($this->views as $viewName => $config) {
            try {
                $this->refreshView($viewName);
                $results[$viewName] = 'refreshed';
            } catch (\Exception $e) {
                $results[$viewName] = 'error: ' . $e->getMessage();
            }
        }
        
        return $results;
    }

    /**
     * Ottiene i dati da una vista materializzata
     */
    public function getViewData($viewName, $conditions = [])
    {
        if (!isset($this->views[$viewName])) {
            throw new \InvalidArgumentException("Vista {$viewName} non trovata");
        }
        
        $tableName = $this->views[$viewName]['table'];
        $query = DB::table($tableName);
        
        // Applica le condizioni
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }
        
        return $query->get();
    }

    /**
     * Ottiene le statistiche di una vista
     */
    public function getViewStats($viewName)
    {
        if (!isset($this->views[$viewName])) {
            throw new \InvalidArgumentException("Vista {$viewName} non trovata");
        }
        
        $tableName = $this->views[$viewName]['table'];
        
        return [
            'view_name' => $viewName,
            'table_name' => $tableName,
            'row_count' => DB::table($tableName)->count(),
            'last_updated' => $this->getViewTimestamp($viewName),
            'refresh_frequency' => $this->views[$viewName]['refresh_frequency']
        ];
    }

    /**
     * Ottiene lo stato di tutte le viste
     */
    public function getAllViewsStatus()
    {
        $status = [];
        
        foreach ($this->views as $viewName => $config) {
            try {
                $status[$viewName] = $this->getViewStats($viewName);
            } catch (\Exception $e) {
                $status[$viewName] = [
                    'view_name' => $viewName,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $status;
    }

    /**
     * Verifica se una tabella esiste
     */
    private function tableExists($tableName)
    {
        return DB::getSchemaBuilder()->hasTable($tableName);
    }

    /**
     * Crea una tabella per la vista materializzata
     */
    private function createTable($tableName, $query)
    {
        // Esegue la query per ottenere la struttura
        $sample = DB::select($query . ' LIMIT 1');
        
        if (empty($sample)) {
            // Se non ci sono dati, crea una tabella vuota con struttura base
            DB::statement("CREATE TABLE {$tableName} (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
        } else {
            // Crea la tabella basata sulla struttura dei dati
            $columns = array_keys((array) $sample[0]);
            $columnDefs = [];
            
            foreach ($columns as $column) {
                $columnDefs[] = "`{$column}` TEXT";
            }
            
            $columnDefs[] = "`id` BIGINT AUTO_INCREMENT PRIMARY KEY";
            $columnDefs[] = "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $columnDefs[] = "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
            
            DB::statement("CREATE TABLE {$tableName} (" . implode(', ', $columnDefs) . ")");
        }
    }

    /**
     * Prepara i dati per l'inserimento
     */
    private function prepareDataForInsert($data)
    {
        $prepared = [];
        
        foreach ($data as $row) {
            $prepared[] = (array) $row;
        }
        
        return $prepared;
    }

    /**
     * Aggiorna il timestamp di una vista
     */
    private function updateViewTimestamp($viewName)
    {
        Cache::put("materialized_view_timestamp_{$viewName}", now(), 86400); // 24 ore
    }

    /**
     * Ottiene il timestamp di una vista
     */
    private function getViewTimestamp($viewName)
    {
        return Cache::get("materialized_view_timestamp_{$viewName}", 'Never');
    }

    /**
     * Test del pattern Materialized View
     */
    public function testMaterializedView()
    {
        $results = [];
        
        try {
            // Test 1: Creazione viste
            $createResults = $this->createAllViews();
            $results['creation'] = $createResults;
            
            // Test 2: Verifica esistenza
            $status = $this->getAllViewsStatus();
            $results['status'] = $status;
            
            // Test 3: Lettura dati
            $salesData = $this->getViewData('sales_by_category');
            $results['data_read'] = count($salesData) > 0 ? 'success' : 'no_data';
            
            // Test 4: Performance test
            $start = microtime(true);
            $this->getViewData('top_products', ['total_sales' => ['>', 0]]);
            $results['performance'] = microtime(true) - $start;
            
            // Test 5: Aggiornamento
            $refreshResults = $this->refreshAllViews();
            $results['refresh'] = $refreshResults;
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
