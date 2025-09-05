<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\NotificationServiceFactory;

/**
 * Controller per dimostrare il Null Object Pattern
 * 
 * Questo controller mostra come il Null Object Pattern elimina
 * la necessità di controlli null nel codice, fornendo sempre
 * un comportamento sicuro e prevedibile.
 */
class NullObjectController extends Controller
{
    /**
     * Endpoint principale - mostra l'interfaccia web
     */
    public function index()
    {
        return view('null_object.example');
    }

    /**
     * Endpoint di test - dimostra il pattern
     */
    public function test(Request $request): JsonResponse
    {
        $serviceType = $request->input('type', 'disabled');
        
        // Crea il servizio usando la factory
        $service = NotificationServiceFactory::create($serviceType);
        
        // Test del servizio
        $testMessage = 'Test message from Null Object Pattern';
        $testRecipient = 'user@example.com';
        
        $result = $service->send($testMessage, $testRecipient);
        
        return response()->json([
            'success' => true,
            'message' => 'Null Object Pattern test completed',
            'data' => [
                'service_type' => $service->getType(),
                'service_available' => $service->isAvailable(),
                'notification_sent' => $result,
                'debug_info' => $service->getDebugInfo(),
                'test_message' => $testMessage,
                'test_recipient' => $testRecipient
            ]
        ]);
    }

    /**
     * Endpoint per testare tutti i servizi disponibili
     */
    public function testAll(): JsonResponse
    {
        $services = ['email', 'sms', 'disabled'];
        $results = [];
        
        foreach ($services as $type) {
            $service = NotificationServiceFactory::create($type);
            $testMessage = "Test message for {$type} service";
            $testRecipient = 'user@example.com';
            
            $results[$type] = [
                'type' => $service->getType(),
                'available' => $service->isAvailable(),
                'sent' => $service->send($testMessage, $testRecipient),
                'debug_info' => $service->getDebugInfo()
            ];
        }
        
        return response()->json([
            'success' => true,
            'message' => 'All services tested',
            'data' => $results
        ]);
    }

    /**
     * Endpoint per ottenere informazioni sui servizi
     */
    public function info(): JsonResponse
    {
        $servicesInfo = NotificationServiceFactory::getServicesInfo();
        
        return response()->json([
            'success' => true,
            'message' => 'Services information',
            'data' => $servicesInfo
        ]);
    }
}
