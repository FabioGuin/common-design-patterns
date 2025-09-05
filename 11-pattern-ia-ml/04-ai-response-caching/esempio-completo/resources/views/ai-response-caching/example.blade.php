<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Response Caching Pattern Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">AI Response Caching Pattern Demo</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Test AI Response Caching Pattern</h2>
                <p class="text-gray-600 mb-4">L'AI Response Caching ottimizza le performance e riduce i costi. Testa il caching delle risposte AI.</p>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium mb-3">Test Query</h3>
                        <form id="queryForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Query</label>
                                <textarea name="query" rows="3" placeholder="Inserisci la tua query..." class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Esegui Query
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium mb-3">Risultato</h3>
                        <div id="result" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[200px]">
                            <p class="text-gray-500">Esegui una query per vedere il risultato...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Test API</h2>
                <div class="space-y-4">
                    <button onclick="testAPI()" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Test API AI Response Caching
                    </button>
                    <div id="apiResult" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[100px]">
                        <p class="text-gray-500">Clicca "Test API AI Response Caching" per vedere il risultato...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('queryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/ai-response-caching/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const cacheStatus = result.data.from_cache ? 
                        '<span class="text-green-600 font-semibold">CACHED</span>' : 
                        '<span class="text-blue-600 font-semibold">NEW</span>';
                    
                    document.getElementById('result').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Stato Cache:</strong> ${cacheStatus}</p>
                            <p><strong>Risposta:</strong></p>
                            <div class="bg-white p-3 border rounded">${result.data.response}</div>
                            <p><strong>Hit Count:</strong> ${result.data.hit_count}</p>
                            <p><strong>Tempo Risposta:</strong> ${result.data.response_time}ms</p>
                            ${result.data.cost_saved ? `<p><strong>Costo Risparmiato:</strong> $${result.data.cost_saved}</p>` : ''}
                            ${result.data.cost ? `<p><strong>Costo:</strong> $${result.data.cost}</p>` : ''}
                        </div>
                    `;
                } else {
                    document.getElementById('result').innerHTML = `<p class="text-red-600">Errore: ${result.message}</p>`;
                }
            } catch (error) {
                document.getElementById('result').innerHTML = `<p class="text-red-600">Errore: ${error.message}</p>`;
            }
        });

        async function testAPI() {
            try {
                const response = await fetch('/api/ai-response-caching/test');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('apiResult').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Pattern:</strong> ${result.data.pattern_description}</p>
                            <p><strong>Statistiche Cache:</strong></p>
                            <div class="bg-white p-2 border rounded text-sm">
                                <p>Hit Rate: ${(result.data.cache_stats.hit_rate * 100).toFixed(1)}%</p>
                                <p>Total Hits: ${result.data.cache_stats.total_hits}</p>
                                <p>Cost Saved: $${result.data.cache_stats.cost_saved}</p>
                            </div>
                            <p><strong>Risultati test:</strong></p>
                            <pre class="bg-white p-2 border rounded text-xs overflow-auto">${JSON.stringify(result.data.test_results, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    document.getElementById('apiResult').innerHTML = `<p class="text-red-600">Errore: ${result.message}</p>`;
                }
            } catch (error) {
                document.getElementById('apiResult').innerHTML = `<p class="text-red-600">Errore: ${error.message}</p>`;
            }
        }
    </script>
</body>
</html>
