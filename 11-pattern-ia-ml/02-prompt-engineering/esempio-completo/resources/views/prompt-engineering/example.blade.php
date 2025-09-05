<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt Engineering Pattern Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Prompt Engineering Pattern Demo</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Test Prompt Engineering Pattern</h2>
                <p class="text-gray-600 mb-4">Il Prompt Engineering ottimizza i prompt per migliori risultati AI. Testa la generazione e ottimizzazione dei prompt.</p>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium mb-3">Genera Prompt</h3>
                        <form id="promptForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Prompt</label>
                                <select name="type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="chat">Chat</option>
                                    <option value="code">Code</option>
                                    <option value="translation">Translation</option>
                                    <option value="summary">Summary</option>
                                    <option value="analysis">Analysis</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Variabili (JSON)</label>
                                <textarea name="variables" rows="3" placeholder='{"question": "Come funziona Laravel?", "language": "PHP"}' class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Genera Prompt
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium mb-3">Risultato</h3>
                        <div id="result" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[200px]">
                            <p class="text-gray-500">Genera un prompt per vedere il risultato...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Test API</h2>
                <div class="space-y-4">
                    <button onclick="testAPI()" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Test API Prompt Engineering
                    </button>
                    <div id="apiResult" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[100px]">
                        <p class="text-gray-500">Clicca "Test API Prompt Engineering" per vedere il risultato...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('promptForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Parse variables JSON
            try {
                data.variables = data.variables ? JSON.parse(data.variables) : {};
            } catch (e) {
                data.variables = {};
            }
            
            try {
                const response = await fetch('/prompt-engineering/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('result').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Prompt Generato:</strong></p>
                            <div class="bg-white p-3 border rounded">${result.data.generation.prompt}</div>
                            <p><strong>Tipo:</strong> ${result.data.generation.type}</p>
                            <p><strong>Variabili usate:</strong> ${result.data.generation.variables_used.join(', ')}</p>
                            <p><strong>Conteggio parole:</strong> ${result.data.generation.word_count}</p>
                            <p><strong>Conteggio caratteri:</strong> ${result.data.generation.character_count}</p>
                            <p><strong>Validazione:</strong> ${result.data.validation.valid ? 'Valido' : 'Non valido'}</p>
                            ${!result.data.validation.valid ? `<p class="text-red-600">Errori: ${result.data.validation.errors.join(', ')}</p>` : ''}
                            <p><strong>Ottimizzazione:</strong></p>
                            <div class="bg-white p-2 border rounded text-sm">
                                <p>Miglioramenti: ${Object.keys(result.data.optimization.improvements).length}</p>
                                <p>Parole originali: ${result.data.optimization.word_count_original}</p>
                                <p>Parole ottimizzate: ${result.data.optimization.word_count_optimized}</p>
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
                const response = await fetch('/api/prompt-engineering/test');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('apiResult').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Pattern:</strong> ${result.data.pattern_description}</p>
                            <p><strong>Tipi disponibili:</strong> ${Object.keys(result.data.available_types).join(', ')}</p>
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
