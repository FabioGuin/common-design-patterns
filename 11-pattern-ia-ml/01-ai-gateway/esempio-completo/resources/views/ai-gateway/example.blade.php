<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Gateway Pattern Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">AI Gateway Pattern Demo</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Test AI Gateway Pattern</h2>
                <p class="text-gray-600 mb-4">L'AI Gateway astrae le differenze tra provider AI. Testa la chat con diversi provider.</p>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium mb-3">Chat AI</h3>
                        <form id="chatForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Messaggio</label>
                                <textarea name="prompt" rows="3" placeholder="Scrivi il tuo messaggio..." class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Provider (opzionale)</label>
                                <select name="provider" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Auto (fallback automatico)</option>
                                    <option value="openai">OpenAI</option>
                                    <option value="claude">Claude</option>
                                    <option value="gemini">Gemini</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Invia Messaggio
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium mb-3">Risposta</h3>
                        <div id="response" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[200px]">
                            <p class="text-gray-500">Invia un messaggio per vedere la risposta...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Test API</h2>
                <div class="space-y-4">
                    <button onclick="testAPI()" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Test API AI Gateway
                    </button>
                    <div id="apiResult" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[100px]">
                        <p class="text-gray-500">Clicca "Test API AI Gateway" per vedere il risultato...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('chatForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/ai-gateway/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('response').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Provider:</strong> ${result.data.provider}</p>
                            <p><strong>Risposta:</strong></p>
                            <div class="bg-white p-3 border rounded">${result.data.response}</div>
                            <p><strong>Token usati:</strong> ${result.data.tokens_used}</p>
                            <p><strong>Costo:</strong> $${result.data.cost}</p>
                            <p><strong>Tempo risposta:</strong> ${result.data.response_time}ms</p>
                        </div>
                    `;
                } else {
                    document.getElementById('response').innerHTML = `<p class="text-red-600">Errore: ${result.data.error}</p>`;
                }
            } catch (error) {
                document.getElementById('response').innerHTML = `<p class="text-red-600">Errore: ${error.message}</p>`;
            }
        });

        async function testAPI() {
            try {
                const response = await fetch('/api/ai-gateway/test');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('apiResult').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Pattern:</strong> ${result.data.pattern_description}</p>
                            <p><strong>Provider disponibili:</strong> ${Object.keys(result.data.available_providers).join(', ')}</p>
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
