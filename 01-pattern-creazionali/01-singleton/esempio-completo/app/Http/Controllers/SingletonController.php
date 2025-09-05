<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\SingletonModel;

class SingletonController extends Controller
{
    /**
     * Endpoint principale per testare il Singleton
     */
    public function index(Request $request)
    {
        $singleton = SingletonModel::getInstance();
        
        // Aggiungi dati se forniti
        if ($request->has('data')) {
            $singleton->addData('user_input', $request->input('data'));
        }
        
        $result = $singleton->getInfo();
        
        return response()->json([
            'success' => true,
            'message' => 'Singleton Pattern Demo',
            'data' => $result
        ]);
    }

    /**
     * Endpoint di test per dimostrare l'unicità dell'istanza
     */
    public function test()
    {
        // Crea multiple "istanze" per dimostrare che sono la stessa
        $instance1 = SingletonModel::getInstance();
        $instance2 = SingletonModel::getInstance();
        $instance3 = SingletonModel::getInstance();
        
        // Aggiungi alcuni dati per dimostrare la condivisione dello stato
        $instance1->addData('test_key', 'test_value');
        $instance2->addData('another_key', 'another_value');
        
        $result = [
            'instance1_id' => $instance1->getId(),
            'instance2_id' => $instance2->getId(),
            'instance3_id' => $instance3->getId(),
            'are_same_instance' => $instance1 === $instance2 && $instance2 === $instance3,
            'shared_data' => $instance3->getData(),
            'total_accesses' => $instance3->getAccessCount(),
            'memory_usage' => memory_get_usage(true),
            'timestamp' => now()->toDateTimeString()
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Singleton Test Completed',
            'data' => $result
        ]);
    }

    /**
     * Endpoint per testare la protezione contro la clonazione
     */
    public function testClone()
    {
        try {
            $singleton = SingletonModel::getInstance();
            $cloned = clone $singleton;
            
            return response()->json([
                'success' => false,
                'message' => 'Clone test failed - should not reach here',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => 'Clone protection working correctly',
                'data' => [
                    'error' => $e->getMessage(),
                    'singleton_id' => SingletonModel::getInstance()->getId()
                ]
            ]);
        }
    }

    /**
     * Endpoint per mostrare la vista di esempio
     */
    public function show()
    {
        $singleton = SingletonModel::getInstance();
        $info = $singleton->getInfo();
        
        return view('singleton.example', compact('info'));
    }
}
