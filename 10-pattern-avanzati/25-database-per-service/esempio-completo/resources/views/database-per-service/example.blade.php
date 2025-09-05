<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Per Service Pattern - Esempio Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Database Per Service Pattern</h1>
            
            <!-- Pattern Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h2 class="text-xl font-semibold text-blue-800 mb-2">Informazioni Pattern</h2>
                <p class="text-blue-700">
                    <strong>Pattern ID:</strong> <span id="pattern-id">{{ $patternId ?? 'N/A' }}</span>
                </p>
                <p class="text-blue-700">
                    <strong>Servizi:</strong> <span id="services-count">{{ count($services) }}</span> microservizi
                </p>
            </div>

            <!-- Controls -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button onclick="testSystem()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                    Test Sistema
                </button>
                <button onclick="refreshStats()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                    Aggiorna Statistiche
                </button>
                <button onclick="showServices()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition">
                    Mostra Servizi
                </button>
                <button onclick="showEvents()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition">
                    Mostra Eventi
                </button>
            </div>

            <!-- Services Status -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Stato Servizi</h3>
                    <div id="services-status" class="space-y-2">
                        @foreach($services as $serviceName => $service)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <span class="font-medium">{{ $service['service'] }}</span>
                                <span class="text-sm text-gray-600 ml-2">{{ $service['database'] }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs rounded-full {{ $service['connection_status'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $service['connection_status'] ? 'Connesso' : 'Disconnesso' }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiche Eventi</h3>
                    <div id="event-stats" class="space-y-2">
                        <div class="flex justify-between">
                            <span>Eventi Totali:</span>
                            <span class="font-medium">{{ $eventStats['total_events'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Eventi Pubblicati:</span>
                            <span class="font-medium text-green-600">{{ $eventStats['published_events'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Eventi Falliti:</span>
                            <span class="font-medium text-red-600">{{ $eventStats['failed_events'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tasso di Successo:</span>
                            <span class="font-medium">{{ round($eventStats['success_rate'] ?? 0, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Chart -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribuzione Dati per Servizio</h3>
                <canvas id="servicesChart" width="400" height="200"></canvas>
            </div>

            <!-- Test Results -->
            <div id="test-results" class="hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Risultati Test</h3>
                <div id="test-output" class="bg-gray-50 rounded-lg p-4"></div>
            </div>

            <!-- Services Details -->
            <div id="services-details" class="hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Dettagli Servizi</h3>
                <div id="services-output" class="bg-gray-50 rounded-lg p-4"></div>
            </div>

            <!-- Events Details -->
            <div id="events-details" class="hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Dettagli Eventi</h3>
                <div id="events-output" class="bg-gray-50 rounded-lg p-4"></div>
            </div>
        </div>
    </div>

    <script>
        let servicesChart;

        // Inizializza il grafico
        function initChart() {
            const ctx = document.getElementById('servicesChart').getContext('2d');
            servicesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Dati per Servizio',
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

        // Testa il sistema
        async function testSystem() {
            try {
                const response = await fetch('/api/database-per-service/test', {
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

        // Aggiorna le statistiche
        async function refreshStats() {
            try {
                const response = await fetch('/api/database-per-service/stats');
                const data = await response.json();
                updateStats(data);
            } catch (error) {
                console.error('Errore nell\'aggiornamento statistiche:', error);
            }
        }

        // Mostra i servizi
        async function showServices() {
            try {
                const response = await fetch('/api/database-per-service/services');
                const data = await response.json();
                displayServices(data);
            } catch (error) {
                console.error('Errore nel caricamento servizi:', error);
            }
        }

        // Mostra gli eventi
        async function showEvents() {
            try {
                const response = await fetch('/api/database-per-service/stats');
                const data = await response.json();
                displayEvents(data);
            } catch (error) {
                console.error('Errore nel caricamento eventi:', error);
            }
        }

        // Mostra risultato del test
        function displayTestResult(data) {
            const resultsDiv = document.getElementById('test-results');
            const outputDiv = document.getElementById('test-output');
            
            resultsDiv.classList.remove('hidden');
            
            if (data.success) {
                const testData = data.data;
                outputDiv.innerHTML = `
                    <div class="text-green-600 font-medium mb-2">✓ Test completato con successo</div>
                    <div class="mb-4">
                        <div class="text-sm text-gray-600 mb-2">Tempo di esecuzione: ${testData.execution_time.toFixed(2)}ms</div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-800">Utente Creato:</h4>
                            <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(testData.user_created, null, 2)}</pre>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">Prodotto Creato:</h4>
                            <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(testData.product_created, null, 2)}</pre>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">Ordine Creato:</h4>
                            <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(testData.order_created, null, 2)}</pre>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">Pagamento Processato:</h4>
                            <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(testData.payment_processed, null, 2)}</pre>
                        </div>
                    </div>
                `;
            } else {
                outputDiv.innerHTML = `
                    <div class="text-red-600 font-medium mb-2">✗ Test fallito</div>
                    <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(data, null, 2)}</pre>
                `;
            }
        }

        // Mostra i servizi
        function displayServices(data) {
            const resultsDiv = document.getElementById('services-details');
            const outputDiv = document.getElementById('services-output');
            
            resultsDiv.classList.remove('hidden');
            
            if (data.success) {
                const services = data.data;
                outputDiv.innerHTML = Object.entries(services).map(([serviceName, service]) => `
                    <div class="mb-4 p-4 bg-white rounded border">
                        <h4 class="font-medium text-gray-800 mb-2">${service.service}</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Database:</span>
                                <span class="font-medium">${service.database}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Stato:</span>
                                <span class="px-2 py-1 text-xs rounded-full ${service.connection_status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${service.connection_status ? 'Connesso' : 'Disconnesso'}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Dati:</span>
                                <span class="font-medium">${getServiceDataCount(service)}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Mostra gli eventi
        function displayEvents(data) {
            const resultsDiv = document.getElementById('events-details');
            const outputDiv = document.getElementById('events-output');
            
            resultsDiv.classList.remove('hidden');
            
            if (data.success) {
                const events = data.data.events;
                outputDiv.innerHTML = `
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">${events.total_events}</div>
                            <div class="text-sm text-gray-600">Eventi Totali</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">${events.published_events}</div>
                            <div class="text-sm text-gray-600">Pubblicati</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">${events.failed_events}</div>
                            <div class="text-sm text-gray-600">Falliti</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">${events.success_rate.toFixed(1)}%</div>
                            <div class="text-sm text-gray-600">Tasso Successo</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Tipi di Eventi:</h4>
                        <div class="space-y-1">
                            ${Object.entries(events.event_types || {}).map(([type, count]) => 
                                `<div class="flex justify-between text-sm">
                                    <span>${type}:</span>
                                    <span class="font-medium">${count} eventi</span>
                                </div>`
                            ).join('')}
                        </div>
                    </div>
                `;
            }
        }

        // Ottiene il conteggio dei dati per servizio
        function getServiceDataCount(service) {
            if (service.service === 'UserService') {
                return service.total_users + ' utenti';
            } else if (service.service === 'ProductService') {
                return service.total_products + ' prodotti';
            } else if (service.service === 'OrderService') {
                return service.total_orders + ' ordini';
            } else if (service.service === 'PaymentService') {
                return service.total_payments + ' pagamenti';
            }
            return 'N/A';
        }

        // Aggiorna le statistiche
        function updateStats(data) {
            if (data.success) {
                const services = data.data.services;
                const events = data.data.events;
                
                // Aggiorna stato servizi
                const servicesStatusDiv = document.getElementById('services-status');
                servicesStatusDiv.innerHTML = Object.entries(services).map(([serviceName, service]) => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium">${service.service}</span>
                            <span class="text-sm text-gray-600 ml-2">${service.database}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs rounded-full ${service.connection_status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${service.connection_status ? 'Connesso' : 'Disconnesso'}
                            </span>
                        </div>
                    </div>
                `).join('');
                
                // Aggiorna statistiche eventi
                const eventStatsDiv = document.getElementById('event-stats');
                eventStatsDiv.innerHTML = `
                    <div class="flex justify-between">
                        <span>Eventi Totali:</span>
                        <span class="font-medium">${events.total_events}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Eventi Pubblicati:</span>
                        <span class="font-medium text-green-600">${events.published_events}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Eventi Falliti:</span>
                        <span class="font-medium text-red-600">${events.failed_events}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tasso di Successo:</span>
                        <span class="font-medium">${events.success_rate.toFixed(1)}%</span>
                    </div>
                `;
                
                // Aggiorna grafico
                updateChart(services);
            }
        }

        // Aggiorna il grafico
        function updateChart(services) {
            const labels = Object.keys(services).map(key => services[key].service);
            const data = Object.keys(services).map(key => {
                const service = services[key];
                if (service.service === 'UserService') return service.total_users;
                if (service.service === 'ProductService') return service.total_products;
                if (service.service === 'OrderService') return service.total_orders;
                if (service.service === 'PaymentService') return service.total_payments;
                return 0;
            });
            
            servicesChart.data.labels = labels;
            servicesChart.data.datasets[0].data = data;
            servicesChart.update();
        }

        // Inizializza la pagina
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
            refreshStats();
        });
    </script>
</body>
</html>
