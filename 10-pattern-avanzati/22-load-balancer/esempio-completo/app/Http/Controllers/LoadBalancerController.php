<?php

namespace App\Http\Controllers;

use App\Services\LoadBalancerService;
use App\Services\HealthCheckerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LoadBalancerController extends Controller
{
    private LoadBalancerService $loadBalancer;
    private HealthCheckerService $healthChecker;

    public function __construct(LoadBalancerService $loadBalancer, HealthCheckerService $healthChecker)
    {
        $this->loadBalancer = $loadBalancer;
        $this->healthChecker = $healthChecker;
    }

    /**
     * Mostra la dashboard del load balancer
     */
    public function index(): View
    {
        $servers = $this->loadBalancer->getServers();
        $stats = $this->loadBalancer->getServerStats();
        $algorithm = $this->loadBalancer->getAlgorithm();
        $healthStats = $this->healthChecker->getHealthStats();

        return view('load-balancer.example', compact(
            'servers', 
            'stats', 
            'algorithm', 
            'healthStats'
        ));
    }

    /**
     * Testa il load balancer
     */
    public function test(Request $request): JsonResponse
    {
        $requestData = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ];

        $result = $this->loadBalancer->routeRequest($requestData);

        return response()->json([
            'success' => true,
            'data' => $result,
            'pattern_id' => $this->loadBalancer->getId(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Ottiene la lista dei server
     */
    public function servers(): JsonResponse
    {
        $servers = $this->loadBalancer->getServers();
        $stats = $this->loadBalancer->getServerStats();

        $serversWithStats = [];
        foreach ($servers as $id => $server) {
            $serversWithStats[] = array_merge($server, [
                'stats' => $stats[$id] ?? []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $serversWithStats,
            'count' => count($servers)
        ]);
    }

    /**
     * Controlla la salute dei server
     */
    public function health(): JsonResponse
    {
        $servers = $this->loadBalancer->getServers();
        $healthResults = $this->healthChecker->checkAllServers($servers);
        $healthStats = $this->healthChecker->getHealthStats();

        return response()->json([
            'success' => true,
            'data' => [
                'servers' => $healthResults,
                'stats' => $healthStats
            ]
        ]);
    }

    /**
     * Aggiunge un server
     */
    public function addServer(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string|max:255',
            'url' => 'required|url',
            'weight' => 'integer|min:1|max:100'
        ]);

        $id = $request->input('id');
        $url = $request->input('url');
        $weight = $request->input('weight', 1);

        $this->loadBalancer->addServer($id, $url, $weight);

        return response()->json([
            'success' => true,
            'message' => 'Server aggiunto con successo',
            'data' => [
                'id' => $id,
                'url' => $url,
                'weight' => $weight
            ]
        ]);
    }

    /**
     * Rimuove un server
     */
    public function removeServer(string $id): JsonResponse
    {
        $removed = $this->loadBalancer->removeServer($id);

        if ($removed) {
            return response()->json([
                'success' => true,
                'message' => 'Server rimosso con successo',
                'data' => ['id' => $id]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Server non trovato',
            'data' => ['id' => $id]
        ], 404);
    }

    /**
     * Imposta l'algoritmo di distribuzione
     */
    public function setAlgorithm(Request $request): JsonResponse
    {
        $request->validate([
            'algorithm' => 'required|string|in:round_robin,least_connections,weighted,ip_hash'
        ]);

        $algorithm = $request->input('algorithm');
        $this->loadBalancer->setAlgorithm($algorithm);

        return response()->json([
            'success' => true,
            'message' => 'Algoritmo aggiornato con successo',
            'data' => ['algorithm' => $algorithm]
        ]);
    }

    /**
     * Esegue test di carico
     */
    public function loadTest(Request $request): JsonResponse
    {
        $request->validate([
            'requests' => 'integer|min:1|max:100'
        ]);

        $numRequests = $request->input('requests', 10);
        $results = [];
        $startTime = microtime(true);

        for ($i = 0; $i < $numRequests; $i++) {
            $result = $this->loadBalancer->routeRequest([
                'ip' => '127.0.0.1',
                'user_agent' => 'LoadTest/1.0',
                'request_id' => $i + 1
            ]);
            $results[] = $result;
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // in millisecondi

        // Analizza i risultati
        $successful = array_filter($results, fn($r) => $r['success']);
        $failed = array_filter($results, fn($r) => !$r['success']);
        
        $serverDistribution = [];
        foreach ($results as $result) {
            if ($result['success'] && isset($result['server']['id'])) {
                $serverId = $result['server']['id'];
                $serverDistribution[$serverId] = ($serverDistribution[$serverId] ?? 0) + 1;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_requests' => $numRequests,
                'successful_requests' => count($successful),
                'failed_requests' => count($failed),
                'success_rate' => (count($successful) / $numRequests) * 100,
                'total_time_ms' => round($totalTime, 2),
                'avg_time_per_request_ms' => round($totalTime / $numRequests, 2),
                'server_distribution' => $serverDistribution,
                'results' => $results
            ]
        ]);
    }

    /**
     * Ottiene le statistiche dettagliate
     */
    public function stats(): JsonResponse
    {
        $servers = $this->loadBalancer->getServers();
        $stats = $this->loadBalancer->getServerStats();
        $healthStats = $this->healthChecker->getHealthStats();

        $detailedStats = [];
        foreach ($servers as $id => $server) {
            $detailedStats[] = [
                'id' => $id,
                'url' => $server['url'],
                'weight' => $server['weight'],
                'active_connections' => $server['active_connections'],
                'total_requests' => $server['total_requests'],
                'last_used' => $server['last_used'],
                'is_healthy' => $server['is_healthy'],
                'performance' => $stats[$id] ?? []
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'servers' => $detailedStats,
                'health' => $healthStats,
                'algorithm' => $this->loadBalancer->getAlgorithm(),
                'pattern_id' => $this->loadBalancer->getId()
            ]
        ]);
    }
}
