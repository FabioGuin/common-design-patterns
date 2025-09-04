<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Cache Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .health-excellent { background-color: #10b981; }
        .health-good { background-color: #3b82f6; }
        .health-fair { background-color: #f59e0b; }
        .health-poor { background-color: #ef4444; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">AI Cache Dashboard</h1>
                        <p class="text-gray-600">Sistema di caching per risposte AI</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('ai-cache.analytics') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                            Analytics
                        </a>
                        <a href="{{ route('ai-cache.management') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                            Gestione
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Health Status -->
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Stato di Salute della Cache</h2>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded-full health-{{ $healthMetrics['health_status'] }}"></div>
                            <span class="font-medium capitalize">{{ $healthMetrics['health_status'] }}</span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">{{ $healthMetrics['health_score'] }}/100</div>
                        @if(!empty($healthMetrics['health_issues']))
                            <div class="text-sm text-red-600">
                                Problemi: {{ implode(', ', $healthMetrics['health_issues']) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="metric-card text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Hit Rate</p>
                            <p class="text-3xl font-bold">{{ $stats['hit_rate'] }}%</p>
                        </div>
                        <div class="text-4xl opacity-50">🎯</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Risparmio Costi</p>
                            <p class="text-3xl font-bold">${{ number_format($costSavings['net_savings'], 2) }}</p>
                        </div>
                        <div class="text-4xl opacity-50">💰</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Chiamate Evitate</p>
                            <p class="text-3xl font-bold">{{ number_format($costSavings['calls_avoided']) }}</p>
                        </div>
                        <div class="text-4xl opacity-50">📞</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Entry Cache</p>
                            <p class="text-3xl font-bold">{{ number_format($stats['total_entries']) }}</p>
                        </div>
                        <div class="text-4xl opacity-50">🗄️</div>
                    </div>
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Hit Rate Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Hit Rate nel Tempo</h3>
                    <canvas id="hitRateChart" width="400" height="200"></canvas>
                </div>

                <!-- Response Time Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Tempo di Risposta</h3>
                    <canvas id="responseTimeChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Cache Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Cache Size -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Dimensione Cache</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Totale Entry:</span>
                            <span class="font-medium">{{ number_format($stats['total_entries']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Dimensione Totale:</span>
                            <span class="font-medium">{{ number_format($stats['cache_size'] / 1024, 2) }} KB</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Uso Memoria:</span>
                            <span class="font-medium">{{ number_format($stats['memory_usage'] / 1024, 2) }} KB</span>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Metriche Performance</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Tempo Medio:</span>
                            <span class="font-medium">{{ $performance['avg_response_time'] }}ms</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Hit Rate:</span>
                            <span class="font-medium">{{ $performance['hit_rate'] }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Miss Rate:</span>
                            <span class="font-medium">{{ $performance['miss_rate'] }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Cost Analysis -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Analisi Costi</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Costo API Risparmiato:</span>
                            <span class="font-medium text-green-600">${{ number_format($costSavings['api_cost_saved'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Costo Cache:</span>
                            <span class="font-medium text-red-600">${{ number_format($costSavings['cache_cost'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Risparmio Netto:</span>
                            <span class="font-medium text-green-600">${{ number_format($costSavings['net_savings'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            @if(!empty($recommendations))
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">Raccomandazioni per l'Ottimizzazione</h3>
                <div class="space-y-3">
                    @foreach($recommendations as $recommendation)
                    <div class="flex items-start space-x-3 p-3 rounded-lg 
                        @if($recommendation['priority'] === 'high') bg-red-50 border-l-4 border-red-400
                        @elseif($recommendation['priority'] === 'medium') bg-yellow-50 border-l-4 border-yellow-400
                        @else bg-blue-50 border-l-4 border-blue-400
                        @endif">
                        <div class="flex-shrink-0">
                            @if($recommendation['priority'] === 'high')
                                <span class="text-red-500">⚠️</span>
                            @elseif($recommendation['priority'] === 'medium')
                                <span class="text-yellow-500">⚡</span>
                            @else
                                <span class="text-blue-500">💡</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $recommendation['message'] }}</p>
                            <p class="text-xs text-gray-600 mt-1">Azione suggerita: {{ $recommendation['action'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Azioni Rapide</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <button onclick="optimizeCache()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Ottimizza Cache
                    </button>
                    <button onclick="warmCache()" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                        Pre-riscalda Cache
                    </button>
                    <button onclick="flushCache()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        Pulisci Cache
                    </button>
                    <button onclick="refreshStats()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                        Aggiorna Statistiche
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Hit Rate Chart
        const hitRateCtx = document.getElementById('hitRateChart').getContext('2d');
        new Chart(hitRateCtx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'],
                datasets: [{
                    label: 'Hit Rate %',
                    data: [85, 87, 82, 89, 91, 88, 86],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Response Time Chart
        const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
        new Chart(responseTimeCtx, {
            type: 'bar',
            data: {
                labels: ['0-10ms', '10-50ms', '50-100ms', '100-500ms', '500ms+'],
                datasets: [{
                    label: 'Numero di Richieste',
                    data: [120, 85, 45, 20, 5],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(156, 163, 175, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Quick Actions
        async function optimizeCache() {
            try {
                const response = await fetch('/ai-cache/api/optimize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const result = await response.json();
                alert(result.message);
                location.reload();
            } catch (error) {
                alert('Errore durante l\'ottimizzazione della cache');
            }
        }

        async function warmCache() {
            try {
                const response = await fetch('/ai-cache/api/warm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const result = await response.json();
                alert(result.message);
                location.reload();
            } catch (error) {
                alert('Errore durante il pre-riscaldamento della cache');
            }
        }

        async function flushCache() {
            if (confirm('Sei sicuro di voler pulire tutta la cache?')) {
                try {
                    const response = await fetch('/ai-cache/api/flush', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const result = await response.json();
                    alert(result.message);
                    location.reload();
                } catch (error) {
                    alert('Errore durante la pulizia della cache');
                }
            }
        }

        function refreshStats() {
            location.reload();
        }

        // Auto-refresh ogni 30 secondi
        setInterval(refreshStats, 30000);
    </script>
</body>
</html>
