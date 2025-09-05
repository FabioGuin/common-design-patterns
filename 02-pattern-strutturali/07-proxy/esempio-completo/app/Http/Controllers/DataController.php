<?php

namespace App\Http\Controllers;

use App\Services\AccessControlDataProxy;
use App\Services\CachingDataProxy;
use App\Services\ExternalDataService;
use App\Services\LoggingDataProxy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataController extends Controller
{
    private $dataService;
    
    public function __construct()
    {
        // Creiamo una catena di proxy per dimostrare la composizione
        $externalService = new ExternalDataService();
        $cachingProxy = new CachingDataProxy($externalService);
        $accessControlProxy = new AccessControlDataProxy($cachingProxy);
        $this->dataService = new LoggingDataProxy($accessControlProxy);
    }
    
    /**
     * Mostra la pagina principale con esempi di utilizzo dei proxy
     */
    public function index(): View
    {
        $examples = [
            'caching' => $this->getCachingExample(),
            'access_control' => $this->getAccessControlExample(),
            'logging' => $this->getLoggingExample(),
            'combined' => $this->getCombinedExample()
        ];
        
        return view('data.index', compact('examples'));
    }
    
    /**
     * Esempio di caching proxy
     */
    public function cachingExample(Request $request)
    {
        $userId = $request->get('user_id', 1);
        
        try {
            // Simula il ruolo utente per il test
            $this->dataService->setUserRole('admin');
            
            $startTime = microtime(true);
            $userData = $this->dataService->getUserData($userId);
            $firstCallTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Seconda chiamata - dovrebbe essere dalla cache
            $startTime = microtime(true);
            $userData = $this->dataService->getUserData($userId);
            $secondCallTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return response()->json([
                'success' => true,
                'data' => $userData,
                'first_call_time' => $firstCallTime . 'ms',
                'second_call_time' => $secondCallTime . 'ms',
                'cache_benefit' => $firstCallTime > $secondCallTime ? 'Cache working!' : 'Cache not working'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Esempio di access control proxy
     */
    public function accessControlExample(Request $request)
    {
        $userId = $request->get('user_id', 1);
        $userRole = $request->get('user_role', 'guest');
        
        try {
            $this->dataService->setUserRole($userRole);
            
            $userData = $this->dataService->getUserData($userId);
            
            return response()->json([
                'success' => true,
                'data' => $userData,
                'user_role' => $userRole,
                'message' => 'Access granted!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'user_role' => $userRole
            ], 403);
        }
    }
    
    /**
     * Esempio di logging proxy
     */
    public function loggingExample(Request $request)
    {
        $userId = $request->get('user_id', 1);
        
        try {
            $this->dataService->setUserRole('admin');
            
            $userData = $this->dataService->getUserData($userId);
            
            return response()->json([
                'success' => true,
                'data' => $userData,
                'message' => 'Check the logs for detailed information!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Esempio combinato di tutti i proxy
     */
    public function combinedExample(Request $request)
    {
        $userId = $request->get('user_id', 1);
        $userRole = $request->get('user_role', 'admin');
        
        try {
            $this->dataService->setUserRole($userRole);
            
            $startTime = microtime(true);
            $userData = $this->dataService->getUserData($userId);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return response()->json([
                'success' => true,
                'data' => $userData,
                'execution_time' => $executionTime . 'ms',
                'features_used' => [
                    'caching' => 'Data cached for future requests',
                    'access_control' => "Access granted for role: {$userRole}",
                    'logging' => 'Operation logged with timing information'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Invalida la cache
     */
    public function invalidateCache(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            
            if ($userId) {
                $this->dataService->invalidateUserCache($userId);
                $message = "Cache invalidated for user {$userId}";
            } else {
                $this->dataService->invalidateAllCache();
                $message = "All cache invalidated";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ottiene esempi per la pagina principale
     */
    private function getCachingExample(): array
    {
        return [
            'title' => 'Caching Proxy',
            'description' => 'Memorizza i risultati delle chiamate API per evitare richieste ripetute',
            'benefits' => [
                'Riduce le chiamate API',
                'Migliora le performance',
                'Riduce i costi di banda'
            ]
        ];
    }
    
    private function getAccessControlExample(): array
    {
        return [
            'title' => 'Access Control Proxy',
            'description' => 'Controlla i permessi prima di permettere l\'accesso ai dati',
            'benefits' => [
                'Sicurezza integrata',
                'Controllo granulare',
                'Logging degli accessi'
            ]
        ];
    }
    
    private function getLoggingExample(): array
    {
        return [
            'title' => 'Logging Proxy',
            'description' => 'Traccia tutte le operazioni con timing e dettagli',
            'benefits' => [
                'Monitoraggio completo',
                'Debug facilitato',
                'Analisi delle performance'
            ]
        ];
    }
    
    private function getCombinedExample(): array
    {
        return [
            'title' => 'Proxy Combinati',
            'description' => 'Composizione di più proxy per funzionalità complete',
            'benefits' => [
                'Sicurezza + Performance',
                'Flessibilità massima',
                'Codice modulare'
            ]
        ];
    }
}
