<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Load Balancer Pattern - Esempio Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Load Balancer Pattern</h1>
            
            <!-- Pattern Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h2 class="text-xl font-semibold text-blue-800 mb-2">Informazioni Pattern</h2>
                <p class="text-blue-700">
                    <strong>Pattern ID:</strong> <span id="pattern-id">{{ $patternId ?? 'N/A' }}</span>
                </p>
                <p class="text-blue-700">
                    <strong>Algoritmo:</strong> <span id="current-algorithm">{{ $algorithm }}</span>
                </p>
            </div>

            <!-- Controls -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button onclick="testLoadBalancer()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                    Test Richiesta
                </button>
                <button onclick="runLoadTest()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                    Test di Carico
                </button>
                <button onclick="checkHealth()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition">
                    Controlla Salute
                </button>
                <button onclick="refreshStats()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition">
                    Aggiorna Statistiche
                </button>
            </div>

            <!-- Algorithm Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Algoritmo di Distribuzione:</label>
                <select id="algorithm-select" class="border border-gray-300 rounded-lg px-3 py-2 w-full md:w-auto">
                    <option value="round_robin" {{ $algorithm === 'round_robin' ? 'selected' : '' }}>Round Robin</option>
                    <option value="least_connections" {{ $algorithm === 'least_connections' ? 'selected' : '' }}>Least Connections</option>
                    <option value="weighted" {{ $algorithm === 'weighted' ? 'selected' : '' }}>Weighted</option>
                    <option value="ip_hash" {{ $algorithm === 'ip_hash' ? 'selected' : '' }}>IP Hash</option>
                </select>
                <button onclick="changeAlgorithm()" class="ml-2 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg transition">
                    Cambia
                </button>
            </div>

            <!-- Server Status -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Stato Server</h3>
                    <div id="server-status" class="space-y-2">
                        @foreach($servers as $id => $server)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <span class="font-medium">{{ $id }}</span>
                                <span class="text-sm text-gray-600 ml-2">{{ $server['url'] }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs rounded-full {{ $server['is_healthy'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $server['is_healthy'] ? 'Sano' : 'Non Sano' }}
                                </span>
                                <span class="text-sm text-gray-600">Peso: {{ $server['weight'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiche Salute</h3>
                    <div id="health-stats" class="space-y-2">
                        <div class="flex justify-between">
                            <span>Server Totali:</span>
                            <span class="font-medium">{{ $healthStats['total_servers'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Server Sani:</span>
                            <span class="font-medium text-green-600">{{ $healthStats['healthy_servers'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Server Non Sani:</span>
                            <span class="font-medium text-red-600">{{ $healthStats['unhealthy_servers'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tasso di Salute:</span>
                            <span class="font-medium">{{ round($healthStats['health_rate'] ?? 0, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Statistics Chart -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribuzione Richieste</h3>
                <canvas id="requestsChart" width="400" height="200"></canvas>
            </div>

            <!-- Test Results -->
            <div id="test-results" class="hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Risultati Test</h3>
                <div id="test-output" class="bg-gray-50 rounded-lg p-4"></div>
            </div>

            <!-- Load Test Results -->
            <div id="load-test-results" class="hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Risultati Test di Carico</h3>
                <div id="load-test-output" class="bg-gray-50 rounded-lg p-4"></div>
            </div>
        </div>
    </div>

    <script>
        let requestsChart;

        // Inizializza il grafico
        function initChart() {
            const ctx = document.getElementById('requestsChart').getContext('2d');
            requestsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Richieste per Server',
                        data: [],
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
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
        }

        // Testa il load balancer
        async function testLoadBalancer() {
            try {
                const response = await fetch('/api/load-balancer/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                const data = await response.json();
                displayTestResult(data);
            } catch (error) {
                console.error('Errore nel test:', error);
                displayTestResult({ success: false, error: error.message });
            }
        }

        // Esegue test di carico
        async function runLoadTest() {
            const numRequests = prompt('Numero di richieste (1-100):', '10');
            if (!numRequests || numRequests < 1 || numRequests > 100) return;

            try {
                const response = await fetch('/api/load-balancer/load-test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ requests: parseInt(numRequests) })
                });

                const data = await response.json();
                displayLoadTestResult(data);
            } catch (error) {
                console.error('Errore nel test di carico:', error);
                displayLoadTestResult({ success: false, error: error.message });
            }
        }

        // Controlla la salute dei server
        async function checkHealth() {
            try {
                const response = await fetch('/api/load-balancer/health');
                const data = await response.json();
                displayHealthResults(data);
            } catch (error) {
                console.error('Errore nel controllo salute:', error);
            }
        }

        // Aggiorna le statistiche
        async function refreshStats() {
            try {
                const response = await fetch('/api/load-balancer/stats');
                const data = await response.json();
                updateServerStats(data);
            } catch (error) {
                console.error('Errore nell\'aggiornamento statistiche:', error);
            }
        }

        // Cambia algoritmo
        async function changeAlgorithm() {
            const algorithm = document.getElementById('algorithm-select').value;
            
            try {
                const response = await fetch('/api/load-balancer/set-algorithm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ algorithm })
                });

                const data = await response.json();
                if (data.success) {
                    document.getElementById('current-algorithm').textContent = algorithm;
                    alert('Algoritmo cambiato con successo!');
                }
            } catch (error) {
                console.error('Errore nel cambio algoritmo:', error);
            }
        }

        // Mostra risultato del test
        function displayTestResult(data) {
            const resultsDiv = document.getElementById('test-results');
            const outputDiv = document.getElementById('test-output');
            
            resultsDiv.classList.remove('hidden');
            
            if (data.success) {
                outputDiv.innerHTML = `
                    <div class="text-green-600 font-medium mb-2">✓ Test completato con successo</div>
                    <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(data.data, null, 2)}</pre>
                `;
            } else {
                outputDiv.innerHTML = `
                    <div class="text-red-600 font-medium mb-2">✗ Test fallito</div>
                    <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(data, null, 2)}</pre>
                `;
            }
        }

        // Mostra risultato del test di carico
        function displayLoadTestResult(data) {
            const resultsDiv = document.getElementById('load-test-results');
            const outputDiv = document.getElementById('load-test-output');
            
            resultsDiv.classList.remove('hidden');
            
            if (data.success) {
                const stats = data.data;
                outputDiv.innerHTML = `
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">${stats.total_requests}</div>
                            <div class="text-sm text-gray-600">Richieste Totali</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">${stats.successful_requests}</div>
                            <div class="text-sm text-gray-600">Successo</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">${stats.failed_requests}</div>
                            <div class="text-sm text-gray-600">Fallite</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">${stats.success_rate.toFixed(1)}%</div>
                            <div class="text-sm text-gray-600">Tasso Successo</div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="text-sm text-gray-600 mb-2">Tempo Totale: ${stats.total_time_ms}ms</div>
                        <div class="text-sm text-gray-600 mb-2">Tempo Medio per Richiesta: ${stats.avg_time_per_request_ms}ms</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium mb-2">Distribuzione per Server:</div>
                        <div class="space-y-1">
                            ${Object.entries(stats.server_distribution).map(([server, count]) => 
                                `<div class="flex justify-between text-sm">
                                    <span>${server}:</span>
                                    <span class="font-medium">${count} richieste</span>
                                </div>`
                            ).join('')}
                        </div>
                    </div>
                `;
                
                // Aggiorna il grafico
                updateChart(stats.server_distribution);
            } else {
                outputDiv.innerHTML = `
                    <div class="text-red-600 font-medium mb-2">✗ Test di carico fallito</div>
                    <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(data, null, 2)}</pre>
                `;
            }
        }

        // Mostra risultati del controllo salute
        function displayHealthResults(data) {
            if (data.success) {
                const servers = data.data.servers;
                const serverStatusDiv = document.getElementById('server-status');
                
                serverStatusDiv.innerHTML = Object.entries(servers).map(([id, server]) => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium">${id}</span>
                            <span class="text-sm text-gray-600 ml-2">${server.url}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs rounded-full ${server.is_healthy ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${server.is_healthy ? 'Sano' : 'Non Sano'}
                            </span>
                            <span class="text-xs text-gray-500">${server.last_check}</span>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Aggiorna statistiche server
        function updateServerStats(data) {
            if (data.success) {
                const servers = data.data.servers;
                const serverStatusDiv = document.getElementById('server-status');
                
                serverStatusDiv.innerHTML = servers.map(server => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium">${server.id}</span>
                            <span class="text-sm text-gray-600 ml-2">${server.url}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs rounded-full ${server.is_healthy ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${server.is_healthy ? 'Sano' : 'Non Sano'}
                            </span>
                            <span class="text-xs text-gray-500">${server.total_requests} richieste</span>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Aggiorna il grafico
        function updateChart(distribution) {
            const labels = Object.keys(distribution);
            const data = Object.values(distribution);
            
            requestsChart.data.labels = labels;
            requestsChart.data.datasets[0].data = data;
            requestsChart.update();
        }

        // Inizializza la pagina
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
            refreshStats();
        });
    </script>
</body>
</html>
