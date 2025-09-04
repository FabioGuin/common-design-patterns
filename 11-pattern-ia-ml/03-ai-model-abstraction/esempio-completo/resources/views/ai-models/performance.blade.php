<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Model Abstraction - Performance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Performance e Analytics</h1>
                <nav class="space-x-4">
                    <a href="{{ route('ai-models.dashboard') }}" class="text-white hover:text-gray-200 font-medium">Dashboard</a>
                    <a href="{{ route('ai-models.comparison') }}" class="text-white hover:text-gray-200 font-medium">Confronto</a>
                    <a href="{{ route('ai-models.performance') }}" class="text-white hover:text-gray-200 font-medium">Performance</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        <!-- Real-time Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Richieste Ultima Ora</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $realTimeStats['requests_last_hour'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Success Rate Ultima Ora</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $realTimeStats['success_rate_last_hour'] ?? 0 }}%</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Costo Ultima Ora</p>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($realTimeStats['cost_last_hour'] ?? 0, 4) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Durata Media Ultima Ora</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($realTimeStats['average_duration_last_hour'] ?? 0, 2) }}s</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistiche Generali</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Richieste Totali:</span>
                        <span class="font-medium">{{ number_format($stats['total_requests'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Richieste Riuscite:</span>
                        <span class="font-medium">{{ number_format($stats['successful_requests'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Success Rate:</span>
                        <span class="font-medium">{{ number_format($stats['success_rate'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Costo Totale:</span>
                        <span class="font-medium">${{ number_format($stats['total_cost'] ?? 0, 4) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Costo Medio:</span>
                        <span class="font-medium">${{ number_format($stats['average_cost'] ?? 0, 4) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Durata Media:</span>
                        <span class="font-medium">{{ number_format($stats['average_duration'] ?? 0, 2) }}s</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Modelli Utilizzati</h3>
                <div class="space-y-2">
                    @forelse(($stats['models_used'] ?? []) as $model)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span class="text-sm font-medium">{{ $model }}</span>
                        <span class="text-xs text-gray-500">Attivo</span>
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm">Nessun modello utilizzato</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Ultima Ora</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Richieste:</span>
                        <span class="font-medium">{{ $realTimeStats['requests_last_hour'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Success Rate:</span>
                        <span class="font-medium">{{ $realTimeStats['success_rate_last_hour'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Costo:</span>
                        <span class="font-medium">${{ number_format($realTimeStats['cost_last_hour'] ?? 0, 4) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Durata Media:</span>
                        <span class="font-medium">{{ number_format($realTimeStats['average_duration_last_hour'] ?? 0, 2) }}s</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Modelli Attivi</h3>
                <div class="space-y-2">
                    @forelse(($realTimeStats['models_used_last_hour'] ?? []) as $model)
                    <div class="flex items-center justify-between p-2 bg-green-50 rounded">
                        <span class="text-sm font-medium text-green-800">{{ $model }}</span>
                        <span class="text-xs text-green-600">Ultima ora</span>
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm">Nessun modello attivo</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Daily Performance Chart -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Performance Giornaliere</h3>
                    <p class="text-gray-600">Ultimi 7 giorni</p>
                </div>
                <div class="p-6">
                    <canvas id="dailyChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Success Rate Chart -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Success Rate per Modello</h3>
                    <p class="text-gray-600">Confronto affidabilità</p>
                </div>
                <div class="p-6">
                    <canvas id="successRateChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Models -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Most Used Models -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Modelli Più Utilizzati</h3>
                    <p class="text-gray-600">Classifica per numero di richieste</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($topModels as $index => $model)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $model['model_name'] }}</h4>
                                    <p class="text-sm text-gray-600">{{ number_format($model['usage_count']) }} richieste</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ number_format($model['avg_duration'], 2) }}s</p>
                                <p class="text-xs text-gray-500">durata media</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-gray-500 py-8">
                            <p>Nessun dato disponibile</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Best Performing Models -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Modelli con Migliori Performance</h3>
                    <p class="text-gray-600">Classifica per success rate e velocità</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($bestPerforming as $index => $model)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $model['model_name'] }}</h4>
                                    <p class="text-sm text-gray-600">{{ number_format($model['success_rate'], 1) }}% success rate</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ number_format($model['average_response_time'], 2) }}s</p>
                                <p class="text-xs text-gray-500">tempo medio</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-gray-500 py-8">
                            <p>Nessun dato disponibile</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Analysis -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Analisi Costi</h3>
                <p class="text-gray-600">Distribuzione dei costi per modello</p>
            </div>
            <div class="p-6">
                <canvas id="costChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Performance Trends -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tendenze Performance</h3>
                <p class="text-gray-600">Evoluzione nel tempo</p>
            </div>
            <div class="p-6">
                <canvas id="trendsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </main>

    <script>
        // Daily Performance Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyData = @json($dailyStats);
        
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: Object.keys(dailyData),
                datasets: [
                    {
                        label: 'Richieste Totali',
                        data: Object.values(dailyData).map(day => day.total_requests),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Success Rate (%)',
                        data: Object.values(dailyData).map(day => day.success_rate),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Richieste'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Success Rate (%)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Success Rate Chart
        const successRateCtx = document.getElementById('successRateChart').getContext('2d');
        const successRateData = @json($topModels);
        
        new Chart(successRateCtx, {
            type: 'bar',
            data: {
                labels: successRateData.map(model => model.model_name),
                datasets: [{
                    label: 'Success Rate (%)',
                    data: successRateData.map(model => model.success_rate || 0),
                    backgroundColor: 'rgba(16, 185, 129, 0.5)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Success Rate (%)'
                        }
                    }
                }
            }
        });

        // Cost Chart
        const costCtx = document.getElementById('costChart').getContext('2d');
        const costData = @json($topModels);
        
        new Chart(costCtx, {
            type: 'doughnut',
            data: {
                labels: costData.map(model => model.model_name),
                datasets: [{
                    data: costData.map(model => model.total_cost || 0),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
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

        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        const trendsData = @json($dailyStats);
        
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: Object.keys(trendsData),
                datasets: [
                    {
                        label: 'Durata Media (s)',
                        data: Object.values(trendsData).map(day => day.average_duration),
                        borderColor: 'rgb(245, 158, 11)',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Costo Totale ($)',
                        data: Object.values(trendsData).map(day => day.total_cost),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Durata (s)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Costo ($)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
