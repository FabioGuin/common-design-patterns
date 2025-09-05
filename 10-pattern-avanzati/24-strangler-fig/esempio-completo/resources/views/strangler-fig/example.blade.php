<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strangler Fig Pattern - Esempio Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Strangler Fig Pattern</h1>
            
            <!-- Pattern Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h2 class="text-xl font-semibold text-blue-800 mb-2">Informazioni Pattern</h2>
                <p class="text-blue-700">
                    <strong>Pattern ID:</strong> <span id="pattern-id">{{ $patternId ?? 'N/A' }}</span>
                </p>
                <p class="text-blue-700">
                    <strong>Stato Migrazione:</strong> <span id="migration-status">Caricamento...</span>
                </p>
            </div>

            <!-- Controls -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button onclick="testMigration()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                    Test Migrazione
                </button>
                <button onclick="refreshStatus()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                    Aggiorna Stato
                </button>
                <button onclick="showMigrationModal()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition">
                    Avvia Migrazione
                </button>
                <button onclick="showRollbackModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                    Rollback
                </button>
            </div>

            <!-- Migration Status -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Stato Funzionalità</h3>
                    <div id="feature-status" class="space-y-2">
                        <!-- Feature status will be loaded here -->
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiche Migrazione</h3>
                    <div id="migration-stats" class="space-y-2">
                        <!-- Migration stats will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Migration Progress Chart -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progresso Migrazione</h3>
                <canvas id="migrationChart" width="400" height="200"></canvas>
            </div>

            <!-- Test Results -->
            <div id="test-results" class="hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Risultati Test</h3>
                <div id="test-output" class="bg-gray-50 rounded-lg p-4"></div>
            </div>

            <!-- Migration Modal -->
            <div id="migration-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4">Avvia Migrazione</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Funzionalità:</label>
                        <select id="migrate-feature" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                            <option value="users">Users</option>
                            <option value="products">Products</option>
                            <option value="orders">Orders</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Percentuale iniziale:</label>
                        <input type="number" id="migrate-percentage" min="0" max="100" value="0" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="startMigration()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                            Avvia
                        </button>
                        <button onclick="hideMigrationModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            Annulla
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rollback Modal -->
            <div id="rollback-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4">Rollback Migrazione</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Funzionalità:</label>
                        <select id="rollback-feature" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                            <option value="users">Users</option>
                            <option value="products">Products</option>
                            <option value="orders">Orders</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="rollbackMigration()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                            Rollback
                        </button>
                        <button onclick="hideRollbackModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            Annulla
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let migrationChart;

        // Inizializza il grafico
        function initChart() {
            const ctx = document.getElementById('migrationChart').getContext('2d');
            migrationChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Legacy', 'Migrating', 'Modern'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.5)',
                            'rgba(245, 158, 11, 0.5)',
                            'rgba(34, 197, 94, 0.5)'
                        ],
                        borderColor: [
                            'rgba(239, 68, 68, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(34, 197, 94, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Testa la migrazione
        async function testMigration() {
            const feature = prompt('Funzionalità da testare (users/products/orders):', 'users');
            if (!feature) return;

            try {
                const response = await fetch('/api/strangler-fig/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ feature, requests: 10 })
                });

                const data = await response.json();
                displayTestResult(data);
            } catch (error) {
                console.error('Errore nel test:', error);
                displayTestResult({ success: false, error: error.message });
            }
        }

        // Aggiorna lo stato
        async function refreshStatus() {
            try {
                const response = await fetch('/api/strangler-fig/status');
                const data = await response.json();
                updateStatus(data);
            } catch (error) {
                console.error('Errore nell\'aggiornamento stato:', error);
            }
        }

        // Avvia migrazione
        async function startMigration() {
            const feature = document.getElementById('migrate-feature').value;
            const percentage = document.getElementById('migrate-percentage').value;

            try {
                const response = await fetch('/api/strangler-fig/migrate-feature', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ feature, percentage: parseInt(percentage) })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Migrazione avviata con successo!');
                    hideMigrationModal();
                    refreshStatus();
                } else {
                    alert('Errore nell\'avvio della migrazione: ' + data.message);
                }
            } catch (error) {
                console.error('Errore nell\'avvio migrazione:', error);
                alert('Errore nell\'avvio della migrazione');
            }
        }

        // Rollback migrazione
        async function rollbackMigration() {
            const feature = document.getElementById('rollback-feature').value;

            try {
                const response = await fetch('/api/strangler-fig/rollback-feature', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ feature })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Rollback completato con successo!');
                    hideRollbackModal();
                    refreshStatus();
                } else {
                    alert('Errore nel rollback: ' + data.message);
                }
            } catch (error) {
                console.error('Errore nel rollback:', error);
                alert('Errore nel rollback');
            }
        }

        // Mostra modal migrazione
        function showMigrationModal() {
            document.getElementById('migration-modal').classList.remove('hidden');
        }

        // Nascondi modal migrazione
        function hideMigrationModal() {
            document.getElementById('migration-modal').classList.add('hidden');
        }

        // Mostra modal rollback
        function showRollbackModal() {
            document.getElementById('rollback-modal').classList.remove('hidden');
        }

        // Nascondi modal rollback
        function hideRollbackModal() {
            document.getElementById('rollback-modal').classList.add('hidden');
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
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">${testData.total_requests}</div>
                            <div class="text-sm text-gray-600">Richieste Totali</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">${testData.legacy_requests}</div>
                            <div class="text-sm text-gray-600">Legacy</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">${testData.modern_requests}</div>
                            <div class="text-sm text-gray-600">Modern</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">${testData.modern_percentage.toFixed(1)}%</div>
                            <div class="text-sm text-gray-600">% Modern</div>
                        </div>
                    </div>
                    <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(testData, null, 2)}</pre>
                `;
            } else {
                outputDiv.innerHTML = `
                    <div class="text-red-600 font-medium mb-2">✗ Test fallito</div>
                    <pre class="text-sm bg-white p-3 rounded border overflow-auto">${JSON.stringify(data, null, 2)}</pre>
                `;
            }
        }

        // Aggiorna lo stato
        function updateStatus(data) {
            if (data.success) {
                const status = data.data.status;
                const stats = data.data.stats;
                
                // Aggiorna info pattern
                document.getElementById('migration-status').textContent = 
                    `${stats.legacy_features} Legacy, ${stats.migrating_features} Migrating, ${stats.modern_features} Modern`;
                
                // Aggiorna stato funzionalità
                const featureStatusDiv = document.getElementById('feature-status');
                featureStatusDiv.innerHTML = Object.entries(status).map(([feature, config]) => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium capitalize">${feature}</span>
                            <span class="text-sm text-gray-600 ml-2">${config.percentage}%</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(config.status)}">
                                ${config.status}
                            </span>
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${config.percentage}%"></div>
                            </div>
                        </div>
                    </div>
                `).join('');
                
                // Aggiorna statistiche
                const migrationStatsDiv = document.getElementById('migration-stats');
                migrationStatsDiv.innerHTML = `
                    <div class="flex justify-between">
                        <span>Funzionalità Totali:</span>
                        <span class="font-medium">${stats.total_features}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Legacy:</span>
                        <span class="font-medium text-red-600">${stats.legacy_features}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Migrating:</span>
                        <span class="font-medium text-yellow-600">${stats.migrating_features}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Modern:</span>
                        <span class="font-medium text-green-600">${stats.modern_features}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Progresso:</span>
                        <span class="font-medium">${stats.migration_progress.toFixed(1)}%</span>
                    </div>
                `;
                
                // Aggiorna grafico
                updateChart(stats);
            }
        }

        // Ottiene il colore per lo stato
        function getStatusColor(status) {
            switch (status) {
                case 'legacy': return 'bg-red-100 text-red-800';
                case 'migrating': return 'bg-yellow-100 text-yellow-800';
                case 'modern': return 'bg-green-100 text-green-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        // Aggiorna il grafico
        function updateChart(stats) {
            migrationChart.data.datasets[0].data = [
                stats.legacy_features,
                stats.migrating_features,
                stats.modern_features
            ];
            migrationChart.update();
        }

        // Inizializza la pagina
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
            refreshStatus();
        });
    </script>
</body>
</html>
