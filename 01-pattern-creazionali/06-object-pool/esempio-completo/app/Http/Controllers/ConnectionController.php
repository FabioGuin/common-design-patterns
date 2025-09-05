<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConnectionPool;

class ConnectionController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Object Pool Pattern Demo',
            'data' => [
                'pattern_description' => 'Object Pool riutilizza oggetti costosi da creare',
                'pool_management' => 'Gestisce connessioni database riutilizzabili'
            ]
        ]);
    }

    public function test()
    {
        $pool = new ConnectionPool(3); // Pool con max 3 connessioni
        
        $connections = [];
        
        // Testa acquisizione di connessioni
        for ($i = 0; $i < 5; $i++) {
            $conn = $pool->acquire();
            if ($conn) {
                $connections[] = $conn;
            }
        }
        
        $status = $pool->getPoolStatus();
        
        return response()->json([
            'success' => true,
            'message' => 'Object Pool Test Completed',
            'data' => [
                'pool_status' => $status,
                'connections_acquired' => count($connections),
                'connections' => array_map(fn($conn) => $conn->toArray(), $connections)
            ]
        ]);
    }

    public function acquireConnection(Request $request)
    {
        $pool = new ConnectionPool(5);
        $connection = $pool->acquire();
        
        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Pool pieno, impossibile acquisire connessione'
            ], 503);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Connessione acquisita con successo',
            'data' => [
                'connection' => $connection->toArray(),
                'pool_status' => $pool->getPoolStatus()
            ]
        ]);
    }

    public function show()
    {
        return view('object-pool.example');
    }
}
