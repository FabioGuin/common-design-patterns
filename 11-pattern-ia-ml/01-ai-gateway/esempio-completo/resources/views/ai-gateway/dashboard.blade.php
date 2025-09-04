<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Gateway Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <h1 class="text-2xl font-bold text-gray-900">AI Gateway Dashboard</h1>
                    <div class="flex space-x-4">
                        <button onclick="refreshData()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Aggiorna
                        </button>
                        <button onclick="testProviders()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Test Provider
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Provider Status -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach($providerStatus as $provider)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">{{ ucfirst($provider['name']) }}</h3>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $provider['available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $provider['available'] ? 'Disponibile' : 'Non disponibile' }}
                        </span>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Priorità:</span>
                            <span class="font-medium">{{ $provider['priority'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Costo/token:</span>
                            <span class="font-medium">${{ number_format($provider['cost_per_token'], 6) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Capacità:</span>
                            <span class="font-medium">{{ implode(', ', $provider['capabilities']) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Metrics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Richieste Totali</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $metrics['total_requests'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Successo</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $metrics['successful_requests'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Costo Totale</p>
                            <p class="text-2xl font-semibold text-gray-900">${{ number_format($metrics['total_cost'] ?? 0, 4) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Tempo Medio</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['average_response_time'] ?? 0, 2) }}s</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Interface -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Test AI Gateway</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Test Text Generation -->
                        <div>
                            <h3 class="text-md font-medium text-gray-900 mb-4">Generazione Testo</h3>
                            <form id="textForm">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prompt</label>
                                    <textarea id="textPrompt" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Inserisci il tuo prompt..."></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Tokens</label>
                                    <input type="number" id="maxTokens" value="200" min="1" max="4000" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    Genera Testo
                                </button>
                            </form>
                            <div id="textResult" class="mt-4 p-4 bg-gray-50 rounded-md hidden">
                                <h4 class="font-medium text-gray-900 mb-2">Risultato:</h4>
                                <div id="textContent" class="text-gray-700"></div>
                            </div>
                        </div>

                        <!-- Test Translation -->
                        <div>
                            <h3 class="text-md font-medium text-gray-900 mb-4">Traduzione</h3>
                            <form id="translationForm">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Testo da tradurre</label>
                                    <textarea id="translationText" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Inserisci il testo da tradurre..."></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lingua di destinazione</label>
                                    <select id="targetLanguage" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="it">Italiano</option>
                                        <option value="en">Inglese</option>
                                        <option value="es">Spagnolo</option>
                                        <option value="fr">Francese</option>
                                        <option value="de">Tedesco</option>
                                    </select>
                                </div>
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    Traduci
                                </button>
                            </form>
                            <div id="translationResult" class="mt-4 p-4 bg-gray-50 rounded-md hidden">
                                <h4 class="font-medium text-gray-900 mb-2">Traduzione:</h4>
                                <div id="translationContent" class="text-gray-700"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cache Management -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Gestione Cache</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="text-center">
                            <p class="text-2xl font-semibold text-gray-900">{{ $cacheStats['total_keys'] ?? 0 }}</p>
                            <p class="text-sm text-gray-500">Chiavi Cache</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-semibold text-gray-900">{{ $cacheStats['total_size_mb'] ?? 0 }} MB</p>
                            <p class="text-sm text-gray-500">Dimensione Cache</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-semibold text-gray-900">{{ $cacheStats['hit_rate'] ?? 0 }}%</p>
                            <p class="text-sm text-gray-500">Hit Rate</p>
                        </div>
                    </div>
                    <div class="flex space-x-4">
                        <button onclick="clearCache()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            Pulisci Cache
                        </button>
                        <button onclick="refreshCacheStats()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Aggiorna Statistiche
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test Text Generation
        document.getElementById('textForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const prompt = document.getElementById('textPrompt').value;
            const maxTokens = document.getElementById('maxTokens').value;
            
            if (!prompt.trim()) {
                alert('Inserisci un prompt');
                return;
            }
            
            try {
                const response = await fetch('/ai-gateway/api/generate-text', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        prompt: prompt,
                        options: {
                            max_tokens: parseInt(maxTokens)
                        }
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('textContent').textContent = data.data.text;
                    document.getElementById('textResult').classList.remove('hidden');
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        });
        
        // Test Translation
        document.getElementById('translationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const text = document.getElementById('translationText').value;
            const targetLanguage = document.getElementById('targetLanguage').value;
            
            if (!text.trim()) {
                alert('Inserisci il testo da tradurre');
                return;
            }
            
            try {
                const response = await fetch('/ai-gateway/api/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        text: text,
                        target_language: targetLanguage
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('translationContent').textContent = data.data.text;
                    document.getElementById('translationResult').classList.remove('hidden');
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        });
        
        // Test Providers
        async function testProviders() {
            try {
                const response = await fetch('/ai-gateway/api/test-providers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let message = 'Risultati test provider:\n\n';
                    for (const [provider, result] of Object.entries(data.data)) {
                        message += `${provider}: ${result.success ? 'OK' : 'ERRORE'}\n`;
                        if (result.success) {
                            message += `  - Durata: ${result.duration.toFixed(2)}s\n`;
                            message += `  - Costo: $${result.cost.toFixed(4)}\n`;
                        } else {
                            message += `  - Errore: ${result.error}\n`;
                        }
                        message += '\n';
                    }
                    alert(message);
                } else {
                    alert('Errore nel test: ' + data.error);
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        }
        
        // Clear Cache
        async function clearCache() {
            if (!confirm('Sei sicuro di voler pulire la cache?')) {
                return;
            }
            
            try {
                const response = await fetch('/ai-gateway/api/cache', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        action: 'clear'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Cache pulita con successo');
                    location.reload();
                } else {
                    alert('Errore: ' + data.message);
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        }
        
        // Refresh Data
        function refreshData() {
            location.reload();
        }
        
        // Refresh Cache Stats
        async function refreshCacheStats() {
            try {
                const response = await fetch('/ai-gateway/api/cache', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        action: 'stats'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`Statistiche Cache:\n\nChiavi: ${data.data.total_keys}\nDimensione: ${data.data.total_size_mb} MB\nHit Rate: ${data.data.hit_rate}%`);
                } else {
                    alert('Errore nel recupero delle statistiche');
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        }
    </script>
</body>
</html>
