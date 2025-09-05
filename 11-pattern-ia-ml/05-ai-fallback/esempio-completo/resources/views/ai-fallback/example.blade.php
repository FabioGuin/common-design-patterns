<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Fallback Pattern Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">AI Fallback Pattern Demo</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Test AI Fallback Pattern</h2>
                <p class="text-gray-600 mb-4">L'AI Fallback gestisce automaticamente i fallimenti dei provider. Testa il fallback automatico.</p>
                
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
                        Test API AI Fallback
                    </button>
                    <div id="apiResult" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[100px]">
                        <p class="text-gray-500">Clicca "Test API AI Fallback" per vedere il risultato...</p>
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
                const response = await fetch('/ai-fallback/query', {
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
                    const statusText = result.data.success ? 'SUCCESS' : 'FAILED';
                    
                    document.getElementById('result').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Stato:</strong> <span class="${statusColor} font-semibold">${statusText}</span></p>
                            <p><strong>Risposta:</strong></p>
                            <div class="bg-white p-3 border rounded">${result.data.response}</div>
                            <p><strong>Provider provati:</strong> ${result.data.providers_tried}</p>
                            <p><strong>Fallback usato:</strong> ${result.data.fallback_used ? 'Sì' : 'No'}</p>
                            <p><strong>Tentativi:</strong></p>
                            <div class="bg-white p-2 border rounded text-sm">
                                ${result.data.attempts.map(attempt => 
                                    `<p><strong>${attempt.provider}:</strong> ${attempt.success ? 'Successo' : 'Fallito'} (${attempt.response_time}ms)</p>`
                                ).join('')}
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
                const response = await fetch('/api/ai-fallback/test');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('apiResult').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Pattern:</strong> ${result.data.pattern_description}</p>
                            <p><strong>Stato Provider:</strong></p>
                            <div class="bg-white p-2 border rounded text-sm">
                                ${Object.entries(result.data.provider_status).map(([name, status]) => 
                                    `<p><strong>${name}:</strong> ${status.status} (${(status.success_rate * 100).toFixed(1)}%)</p>`
                                ).join('')}
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
