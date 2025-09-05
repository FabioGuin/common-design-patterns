<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Rate Limiting Pattern Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">AI Rate Limiting Pattern Demo</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Test AI Rate Limiting Pattern</h2>
                <p class="text-gray-600 mb-4">L'AI Rate Limiting controlla l'uso delle API AI. Testa il rate limiting e le quote.</p>
                
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
                        Test API AI Rate Limiting
                    </button>
                    <div id="apiResult" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[100px]">
                        <p class="text-gray-500">Clicca "Test API AI Rate Limiting" per vedere il risultato...</p>
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
                const response = await fetch('/ai-rate-limit/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const statusColor = result.data.success ? 'text-green-600' : 'text-red-600';
                    const statusText = result.data.success ? 'SUCCESS' : 'RATE LIMITED';
                    
                    document.getElementById('result').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Stato:</strong> <span class="${statusColor} font-semibold">${statusText}</span></p>
                            ${result.data.response ? `
                                <p><strong>Risposta:</strong></p>
                                <div class="bg-white p-3 border rounded">${result.data.response}</div>
                            ` : ''}
                            ${result.data.error ? `<p class="text-red-600"><strong>Errore:</strong> ${result.data.error}</p>` : ''}
                            <p><strong>Rate Limit:</strong></p>
                            <div class="bg-white p-2 border rounded text-sm">
                                <p>Limite: ${result.data.rate_limit.limit}</p>
                                <p>Rimanenti: ${result.data.rate_limit.remaining}</p>
                                <p>Richieste correnti: ${result.data.rate_limit.current_requests}</p>
                                <p>Reset alle: ${result.data.rate_limit.reset_at}</p>
                            </div>
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
                const response = await fetch('/api/ai-rate-limit/test');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('apiResult').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Pattern:</strong> ${result.data.pattern_description}</p>
                            <p><strong>Statistiche Rate Limit:</strong></p>
                            <div class="bg-white p-2 border rounded text-sm">
                                <p>Richieste totali oggi: ${result.data.rate_limit_stats.total_requests_today}</p>
                                <p>Richieste rate limited: ${result.data.rate_limit_stats.rate_limited_requests}</p>
                                <p>Media richieste per utente: ${result.data.rate_limit_stats.average_requests_per_user}</p>
                                <p>Picco richieste per ora: ${result.data.rate_limit_stats.peak_requests_per_hour}</p>
                                <p>Utenti attivi: ${result.data.rate_limit_stats.active_users}</p>
                                <p>Uso quota: ${result.data.rate_limit_stats.quota_usage_percentage}%</p>
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
