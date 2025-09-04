<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Model Abstraction - Confronto Modelli</title>
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
                <h1 class="text-3xl font-bold">Confronto Modelli AI</h1>
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
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Filtri</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Provider</label>
                    <select id="providerFilter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">Tutti i provider</option>
                        @foreach($providers as $provider)
                        <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Capacità</label>
                    <select id="capabilityFilter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">Tutte le capacità</option>
                        @foreach($capabilities as $capability)
                        <option value="{{ $capability }}">{{ ucfirst(str_replace('_', ' ', $capability)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Costo Max</label>
                    <input type="number" id="costFilter" step="0.000001" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2" 
                           placeholder="0.000001">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priorità Max</label>
                    <input type="number" id="priorityFilter" min="1" max="10" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2" 
                           placeholder="5">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button onclick="applyFilters()" 
                        class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                    Applica Filtri
                </button>
            </div>
        </div>

        <!-- Models Comparison Table -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Confronto Dettagliato</h2>
                <p class="text-gray-600">Confronta tutti i modelli disponibili</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modello</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo/Token</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Tokens</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorità</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacità</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="modelsTableBody" class="bg-white divide-y divide-gray-200">
                        @foreach($models as $model)
                        <tr class="model-row" 
                            data-provider="{{ $model['provider'] }}" 
                            data-capabilities="{{ json_encode($model['capabilities']) }}"
                            data-cost="{{ $model['cost_per_token'] }}"
                            data-priority="{{ $model['priority'] }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $model['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $model['description'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $model['provider'] === 'openai' ? 'bg-green-100 text-green-800' : 
                                       ($model['provider'] === 'claude' ? 'bg-purple-100 text-purple-800' : 
                                        ($model['provider'] === 'gemini' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($model['provider']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $model['cost_per_token'] ? '$' . number_format($model['cost_per_token'], 6) : 'Gratuito' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($model['max_tokens']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" 
                                             style="width: {{ (6 - $model['priority']) * 20 }}%"></div>
                                    </div>
                                    <span>{{ $model['priority'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($model['capabilities'], 0, 3) as $capability)
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                                        {{ ucfirst(str_replace('_', ' ', $capability)) }}
                                    </span>
                                    @endforeach
                                    @if(count($model['capabilities']) > 3)
                                    <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs rounded">
                                        +{{ count($model['capabilities']) - 3 }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($model['available'])
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Disponibile
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Non disponibile
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="testModel('{{ $model['name'] }}')" 
                                            class="text-blue-600 hover:text-blue-900">
                                        Test
                                    </button>
                                    <button onclick="viewModelDetails('{{ $model['name'] }}')" 
                                            class="text-green-600 hover:text-green-900">
                                        Dettagli
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Cost Comparison Chart -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Confronto Costi</h3>
                    <p class="text-gray-600">Costo per token dei modelli</p>
                </div>
                <div class="p-6">
                    <canvas id="costChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Capabilities Comparison Chart -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Confronto Capacità</h3>
                    <p class="text-gray-600">Numero di capacità per modello</p>
                </div>
                <div class="p-6">
                    <canvas id="capabilitiesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Provider Statistics -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Statistiche per Provider</h3>
                <p class="text-gray-600">Analisi dei modelli per provider</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php
                        $providerStats = [];
                        foreach($models as $model) {
                            $provider = $model['provider'];
                            if(!isset($providerStats[$provider])) {
                                $providerStats[$provider] = ['count' => 0, 'total_cost' => 0, 'avg_priority' => 0];
                            }
                            $providerStats[$provider]['count']++;
                            $providerStats[$provider]['total_cost'] += $model['cost_per_token'];
                            $providerStats[$provider]['avg_priority'] += $model['priority'];
                        }
                        foreach($providerStats as $provider => $stats) {
                            $providerStats[$provider]['avg_cost'] = $stats['total_cost'] / $stats['count'];
                            $providerStats[$provider]['avg_priority'] = $stats['avg_priority'] / $stats['count'];
                        }
                    @endphp
                    @foreach($providerStats as $provider => $stats)
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-2">{{ ucfirst($provider) }}</h4>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Modelli:</span>
                                <span class="font-medium">{{ $stats['count'] }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Costo medio:</span>
                                <span class="font-medium">${{ number_format($stats['avg_cost'], 6) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Priorità media:</span>
                                <span class="font-medium">{{ number_format($stats['avg_priority'], 1) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Quick Compare -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Confronto Rapido</h3>
                <p class="text-gray-600">Seleziona modelli per confronto diretto</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Modelli da Confrontare</label>
                        <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3">
                            @foreach($models as $model)
                            <label class="flex items-center">
                                <input type="checkbox" name="quickCompare" value="{{ $model['name'] }}" 
                                       class="mr-2" {{ $loop->index < 3 ? 'checked' : '' }}>
                                <span class="text-sm">{{ $model['name'] }} ({{ $model['provider'] }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prompt di Test</label>
                        <textarea id="quickComparePrompt" class="w-full border border-gray-300 rounded-md px-3 py-2" rows="4" 
                                  placeholder="Inserisci il prompt per il confronto...">Crea una descrizione dettagliata per un iPhone 15 Pro, evidenziando le caratteristiche principali e i vantaggi rispetto ai modelli precedenti.</textarea>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Iterazioni</label>
                            <input type="number" id="quickCompareIterations" class="w-full border border-gray-300 rounded-md px-3 py-2" 
                                   value="3" min="1" max="10">
                        </div>
                        <button onclick="runQuickCompare()" 
                                class="mt-4 w-full bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                            Esegui Confronto
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Model Details Modal -->
    <div id="modelDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Dettagli Modello</h3>
                </div>
                <div class="p-6">
                    <div id="modelDetailsContent"></div>
                </div>
                <div class="p-6 border-t border-gray-200">
                    <button onclick="closeModelDetailsModal()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        Chiudi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Modal -->
    <div id="resultsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Risultati Confronto</h3>
                </div>
                <div class="p-6">
                    <div id="resultsContent"></div>
                </div>
                <div class="p-6 border-t border-gray-200">
                    <button onclick="closeResultsModal()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        Chiudi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filter Functions
        function applyFilters() {
            const providerFilter = document.getElementById('providerFilter').value;
            const capabilityFilter = document.getElementById('capabilityFilter').value;
            const costFilter = parseFloat(document.getElementById('costFilter').value) || Infinity;
            const priorityFilter = parseInt(document.getElementById('priorityFilter').value) || 10;

            const rows = document.querySelectorAll('.model-row');
            
            rows.forEach(row => {
                const provider = row.dataset.provider;
                const capabilities = JSON.parse(row.dataset.capabilities);
                const cost = parseFloat(row.dataset.cost);
                const priority = parseInt(row.dataset.priority);

                let show = true;

                if (providerFilter && provider !== providerFilter) show = false;
                if (capabilityFilter && !capabilities.includes(capabilityFilter)) show = false;
                if (cost > costFilter) show = false;
                if (priority > priorityFilter) show = false;

                row.style.display = show ? '' : 'none';
            });
        }

        // Model Functions
        function testModel(modelName) {
            // Implement test functionality
            alert('Test per ' + modelName + ' - Funzionalità da implementare');
        }

        async function viewModelDetails(modelName) {
            try {
                const response = await fetch(`/ai-models/api/models/${modelName}`);
                const result = await response.json();
                
                if (result.success) {
                    displayModelDetails(result.data);
                } else {
                    alert('Errore: ' + result.error);
                }
            } catch (error) {
                alert('Errore di connessione: ' + error.message);
            }
        }

        function displayModelDetails(model) {
            const content = document.getElementById('modelDetailsContent');
            content.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-900">${model.name}</h4>
                        <p class="text-gray-600">${model.description}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h5 class="font-medium text-gray-900">Provider</h5>
                            <p class="text-gray-600">${model.provider}</p>
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Costo per Token</h5>
                            <p class="text-gray-600">$${model.cost_per_token}</p>
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Max Tokens</h5>
                            <p class="text-gray-600">${model.max_tokens.toLocaleString()}</p>
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Context Window</h5>
                            <p class="text-gray-600">${model.context_window.toLocaleString()}</p>
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Priorità</h5>
                            <p class="text-gray-600">${model.priority}</p>
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Disponibile</h5>
                            <p class="text-gray-600">${model.available ? 'Sì' : 'No'}</p>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-900">Capacità</h5>
                        <div class="flex flex-wrap gap-2 mt-2">
                            ${model.capabilities.map(cap => `
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                    ${cap.replace('_', ' ')}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                    ${model.tags ? `
                        <div>
                            <h5 class="font-medium text-gray-900">Tag</h5>
                            <div class="flex flex-wrap gap-2 mt-2">
                                ${model.tags.map(tag => `
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                                        ${tag}
                                    </span>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
            openModelDetailsModal();
        }

        function openModelDetailsModal() {
            document.getElementById('modelDetailsModal').classList.remove('hidden');
        }

        function closeModelDetailsModal() {
            document.getElementById('modelDetailsModal').classList.add('hidden');
        }

        // Quick Compare Functions
        async function runQuickCompare() {
            const selectedModels = Array.from(document.querySelectorAll('input[name="quickCompare"]:checked'))
                .map(input => input.value);

            if (selectedModels.length < 2) {
                alert('Seleziona almeno 2 modelli per il confronto');
                return;
            }

            const data = {
                models: selectedModels,
                test_prompt: document.getElementById('quickComparePrompt').value,
                iterations: parseInt(document.getElementById('quickCompareIterations').value)
            };

            try {
                const response = await fetch('/ai-models/api/compare', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    displayComparisonResults(result.data);
                } else {
                    alert('Errore: ' + result.error);
                }
            } catch (error) {
                alert('Errore di connessione: ' + error.message);
            }
        }

        function displayComparisonResults(data) {
            const content = document.getElementById('resultsContent');
            content.innerHTML = `
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Risultati Confronto</h4>
                    <p class="text-gray-600 mb-4">Prompt: "${data.test_prompt}"</p>
                    <p class="text-gray-600 mb-6">Iterazioni: ${data.iterations}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    ${Object.entries(data.results).map(([modelName, result]) => `
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h5 class="font-semibold text-gray-900 mb-3">${modelName}</h5>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Success Rate:</span>
                                    <span class="font-medium">${result.analysis.success_rate}%</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Durata Media:</span>
                                    <span class="font-medium">${result.analysis.average_duration}s</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Costo Medio:</span>
                                    <span class="font-medium">$${result.analysis.average_cost}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Costo Totale:</span>
                                    <span class="font-medium">$${result.analysis.total_cost}</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                ${data.comparison ? `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h5 class="font-medium text-gray-900 mb-3">Raccomandazioni</h5>
                        <ul class="space-y-1">
                            ${data.comparison.recommendations.map(rec => `<li class="text-sm text-gray-700">• ${rec}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            `;
            openResultsModal();
        }

        function openResultsModal() {
            document.getElementById('resultsModal').classList.remove('hidden');
        }

        function closeResultsModal() {
            document.getElementById('resultsModal').classList.add('hidden');
        }

        // Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Cost Comparison Chart
            const costCtx = document.getElementById('costChart').getContext('2d');
            const costData = @json(array_map(function($model) {
                return [
                    'label' => $model['name'],
                    'cost' => $model['cost_per_token']
                ];
            }, $models));

            new Chart(costCtx, {
                type: 'bar',
                data: {
                    labels: costData.map(item => item.label),
                    datasets: [{
                        label: 'Costo per Token',
                        data: costData.map(item => item.cost),
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Costo per Token ($)'
                            }
                        }
                    }
                }
            });

            // Capabilities Comparison Chart
            const capabilitiesCtx = document.getElementById('capabilitiesChart').getContext('2d');
            const capabilitiesData = @json(array_map(function($model) {
                return [
                    'label' => $model['name'],
                    'capabilities' => count($model['capabilities'])
                ];
            }, $models));

            new Chart(capabilitiesCtx, {
                type: 'bar',
                data: {
                    labels: capabilitiesData.map(item => item.label),
                    datasets: [{
                        label: 'Numero di Capacità',
                        data: capabilitiesData.map(item => item.capabilities),
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
                            title: {
                                display: true,
                                text: 'Numero di Capacità'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
