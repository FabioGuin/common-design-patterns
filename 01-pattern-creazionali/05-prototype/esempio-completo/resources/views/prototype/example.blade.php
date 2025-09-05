<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prototype Pattern Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Prototype Pattern Demo</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Test Prototype Pattern</h2>
                <p class="text-gray-600 mb-4">Il Prototype clona oggetti esistenti invece di crearli da zero. Testa la clonazione di documenti.</p>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium mb-3">Clona Documento</h3>
                        <form id="documentForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento</label>
                                <select name="type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="report">Report</option>
                                    <option value="contract">Contract</option>
                                    <option value="invoice">Invoice</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Titolo Personalizzato (opzionale)</label>
                                <input type="text" name="title" placeholder="Lascia vuoto per usare il template" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contenuto Personalizzato (opzionale)</label>
                                <textarea name="content" rows="3" placeholder="Lascia vuoto per usare il template" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Clona Documento
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium mb-3">Risultato</h3>
                        <div id="result" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[200px]">
                            <p class="text-gray-500">Clona un documento per vedere il risultato...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Test API</h2>
                <div class="space-y-4">
                    <button onclick="testAPI()" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Test API Prototype
                    </button>
                    <div id="apiResult" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[100px]">
                        <p class="text-gray-500">Clicca "Test API Prototype" per vedere il risultato...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('documentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/prototype/clone', {
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
                            <p><strong>ID:</strong> ${result.data.id}</p>
                            <p><strong>Tipo:</strong> ${result.data.type}</p>
                            <p><strong>Titolo:</strong> ${result.data.title}</p>
                            <p><strong>Contenuto:</strong></p>
                            <div class="bg-white p-3 border rounded">${result.data.content}</div>
                            <p><strong>Metadata:</strong></p>
                            <pre class="bg-white p-2 border rounded text-xs">${JSON.stringify(result.data.metadata, null, 2)}</pre>
                            <p><strong>Creato:</strong> ${result.data.created_at}</p>
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
                const response = await fetch('/api/prototype/test');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('apiResult').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Pattern:</strong> ${result.data.pattern_description}</p>
                            <p><strong>Documenti clonati:</strong> ${result.data.documents_cloned}</p>
                            <p><strong>Dettagli:</strong></p>
                            <pre class="bg-white p-2 border rounded text-xs overflow-auto">${JSON.stringify(result.data.documents, null, 2)}</pre>
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
