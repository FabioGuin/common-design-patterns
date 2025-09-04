<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Model Abstraction - Dashboard</title>
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
                <h1 class="text-3xl font-bold">AI Model Abstraction</h1>
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
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Modelli Totali</p>
                        <p class="text-2xl font-bold text-gray-900">{{ count($models) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Richieste Totali</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_requests'] ?? 0) }}</p>
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
                        <p class="text-sm font-medium text-gray-600">Costo Totale</p>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($stats['total_cost'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Success Rate</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['success_rate'] ?? 0, 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Models Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Available Models -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Modelli Disponibili</h2>
                    <p class="text-gray-600">Tutti i modelli AI configurati nel sistema</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach($models as $model)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $model['name'] }}</h3>
                                <p class="text-sm text-gray-600">{{ $model['provider'] }} • {{ $model['description'] }}</p>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        {{ $model['cost_per_token'] ? '$' . number_format($model['cost_per_token'], 6) . '/token' : 'Gratuito' }}
                                    </span>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                        {{ $model['max_tokens'] }} max tokens
                                    </span>
                                    @if($model['available'])
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Disponibile</span>
                                    @else
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Non disponibile</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4">
                                <button onclick="testModel('{{ $model['name'] }}')" 
                                        class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                    Test
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Usage -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Utilizzo Recente</h2>
                    <p class="text-gray-600">Ultime richieste processate dal sistema</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @forelse($recentUsage as $usage)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $usage->model_name }}</h3>
                                <p class="text-sm text-gray-600">{{ Str::limit($usage->prompt, 50) }}</p>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span class="text-xs text-gray-500">{{ $usage->created_at->diffForHumans() }}</span>
                                    <span class="text-xs text-gray-500">{{ number_format($usage->duration, 2) }}s</span>
                                    <span class="text-xs text-gray-500">${{ number_format($usage->cost, 4) }}</span>
                                    @if($usage->success)
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Successo</span>
                                    @else
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Errore</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-gray-500 py-8">
                            <p>Nessun utilizzo recente</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Models -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Modelli Più Utilizzati</h2>
                <p class="text-gray-600">Classifica dei modelli basata sull'utilizzo</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($topModels as $index => $model)
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">{{ $model['model_name'] }}</h3>
                            <span class="text-sm font-bold text-blue-600">#{{ $index + 1 }}</span>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Richieste:</span>
                                <span class="font-medium">{{ number_format($model['usage_count']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Durata media:</span>
                                <span class="font-medium">{{ number_format($model['avg_duration'], 2) }}s</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Costo totale:</span>
                                <span class="font-medium">${{ number_format($model['total_cost'], 4) }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full text-center text-gray-500 py-8">
                        <p>Nessun dato disponibile</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Azioni Rapide</h2>
                <p class="text-gray-600">Testa e confronta i modelli AI</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button onclick="openTestModal()" 
                            class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Test Modello</span>
                        </div>
                    </button>
                    
                    <button onclick="openCompareModal()" 
                            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span>Confronta Modelli</span>
                        </div>
                    </button>
                    
                    <button onclick="openBenchmarkModal()" 
                            class="bg-purple-500 text-white px-6 py-3 rounded-lg hover:bg-purple-600 transition-colors">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>Benchmark</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Test Model Modal -->
    <div id="testModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Test Modello</h3>
                </div>
                <div class="p-6">
                    <form id="testForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modello</label>
                            <select id="testModelSelect" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @foreach($models as $model)
                                <option value="{{ $model['name'] }}">{{ $model['name'] }} ({{ $model['provider'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prompt di Test</label>
                            <textarea id="testPrompt" class="w-full border border-gray-300 rounded-md px-3 py-2" rows="3" 
                                      placeholder="Inserisci il prompt di test...">Test prompt per verificare il funzionamento del modello</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Iterazioni</label>
                            <input type="number" id="testIterations" class="w-full border border-gray-300 rounded-md px-3 py-2" 
                                   value="5" min="1" max="20">
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeTestModal()" 
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Annulla
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Esegui Test
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Compare Models Modal -->
    <div id="compareModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Confronta Modelli</h3>
                </div>
                <div class="p-6">
                    <form id="compareForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modelli da Confrontare</label>
                            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3">
                                @foreach($models as $model)
                                <label class="flex items-center">
                                    <input type="checkbox" name="compareModels" value="{{ $model['name'] }}" 
                                           class="mr-2" {{ $loop->index < 3 ? 'checked' : '' }}>
                                    <span class="text-sm">{{ $model['name'] }} ({{ $model['provider'] }})</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prompt di Test</label>
                            <textarea id="comparePrompt" class="w-full border border-gray-300 rounded-md px-3 py-2" rows="3" 
                                      placeholder="Inserisci il prompt per il confronto...">Crea una descrizione per un iPhone 15 Pro</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Iterazioni</label>
                            <input type="number" id="compareIterations" class="w-full border border-gray-300 rounded-md px-3 py-2" 
                                   value="3" min="1" max="10">
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeCompareModal()" 
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Annulla
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                Confronta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Modal -->
    <div id="resultsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Risultati</h3>
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
        // Test Model Functions
        function testModel(modelName) {
            document.getElementById('testModelSelect').value = modelName;
            openTestModal();
        }

        function openTestModal() {
            document.getElementById('testModal').classList.remove('hidden');
        }

        function closeTestModal() {
            document.getElementById('testModal').classList.add('hidden');
        }

        // Compare Models Functions
        function openCompareModal() {
            document.getElementById('compareModal').classList.remove('hidden');
        }

        function closeCompareModal() {
            document.getElementById('compareModal').classList.add('hidden');
        }

        // Results Modal Functions
        function openResultsModal() {
            document.getElementById('resultsModal').classList.remove('hidden');
        }

        function closeResultsModal() {
            document.getElementById('resultsModal').classList.add('hidden');
        }

        // Form Submissions
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                model_name: document.getElementById('testModelSelect').value,
                prompt: document.getElementById('testPrompt').value,
                iterations: parseInt(document.getElementById('testIterations').value)
            };

            try {
                const response = await fetch('/ai-models/api/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    displayResults(result.data);
                    closeTestModal();
                } else {
                    alert('Errore: ' + result.error);
                }
            } catch (error) {
                alert('Errore di connessione: ' + error.message);
            }
        });

        document.getElementById('compareForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const selectedModels = Array.from(document.querySelectorAll('input[name="compareModels"]:checked'))
                .map(input => input.value);

            if (selectedModels.length < 2) {
                alert('Seleziona almeno 2 modelli per il confronto');
                return;
            }

            const data = {
                models: selectedModels,
                test_prompt: document.getElementById('comparePrompt').value,
                iterations: parseInt(document.getElementById('compareIterations').value)
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
                    closeCompareModal();
                } else {
                    alert('Errore: ' + result.error);
                }
            } catch (error) {
                alert('Errore di connessione: ' + error.message);
            }
        });

        function displayResults(data) {
            const content = document.getElementById('resultsContent');
            content.innerHTML = `
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Test Modello: ${data.model}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h5 class="font-medium text-blue-900">Success Rate</h5>
                            <p class="text-2xl font-bold text-blue-600">${data.analysis.success_rate}%</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h5 class="font-medium text-green-900">Durata Media</h5>
                            <p class="text-2xl font-bold text-green-600">${data.analysis.average_duration}s</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h5 class="font-medium text-yellow-900">Costo Medio</h5>
                            <p class="text-2xl font-bold text-yellow-600">$${data.analysis.average_cost}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <h5 class="font-medium text-gray-900 mb-3">Dettagli Test</h5>
                    <div class="space-y-2">
                        ${data.results.map((result, index) => `
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <span class="font-medium">Iterazione ${result.iteration}</span>
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-600">${result.duration}s</span>
                                    <span class="text-sm text-gray-600">$${result.cost}</span>
                                    <span class="px-2 py-1 rounded text-xs ${result.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${result.success ? 'Successo' : 'Errore'}
                                    </span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            openResultsModal();
        }

        function displayComparisonResults(data) {
            const content = document.getElementById('resultsContent');
            content.innerHTML = `
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Confronto Modelli</h4>
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
    </script>
</body>
</html>
