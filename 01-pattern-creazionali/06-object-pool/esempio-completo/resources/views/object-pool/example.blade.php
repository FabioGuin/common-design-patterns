<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Object Pool Pattern Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Object Pool Pattern Demo</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Test Object Pool Pattern</h2>
                <p class="text-gray-600 mb-4">L'Object Pool riutilizza oggetti costosi da creare. Testa la gestione di connessioni database.</p>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium mb-3">Gestisci Pool</h3>
                        <div class="space-y-4">
                            <button onclick="acquireConnection()" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Acquisisci Connessione
                            </button>
                            <button onclick="testPool()" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                Test Pool Completo
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium mb-3">Risultato</h3>
                        <div id="result" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[200px]">
                            <p class="text-gray-500">Clicca un pulsante per testare il pool...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Test API</h2>
                <div class="space-y-4">
                    <button onclick="testAPI()" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Test API Object Pool
                    </button>
                    <div id="apiResult" class="bg-gray-50 border border-gray-200 rounded-md p-4 min-h-[100px]">
                        <p class="text-gray-500">Clicca "Test API Object Pool" per vedere il risultato...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function acquireConnection() {
            try {
                const response = await fetch('/object-pool/acquire', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('result').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Connessione Acquisita:</strong></p>
                            <div class="bg-white p-3 border rounded">
                                <p><strong>ID:</strong> ${result.data.connection.id}</p>
                                <p><strong>Host:</strong> ${result.data.connection.host}</p>
                                <p><strong>Database:</strong> ${result.data.connection.database}</p>
                                <p><strong>In Uso:</strong> ${result.data.connection.in_use ? 'Sì' : 'No'}</p>
                            </div>
                            <p><strong>Stato Pool:</strong></p>
                            <div class="bg-white p-2 border rounded text-sm">
                                <p>Totale: ${result.data.pool_status.total}</p>
                                <p>Disponibili: ${result.data.pool_status.available}</p>
                                <p>In Uso: ${result.data.pool_status.in_use}</p>
                                <p>Max: ${result.data.pool_status.max_size}</p>
                            </div>
                        </div>
                    `;
                } else {
                    document.getElementById('result').innerHTML = `<p class="text-red-600">Errore: ${result.message}</p>`;
                }
            } catch (error) {
                document.getElementById('result').innerHTML = `<p class="text-red-600">Errore: ${error.message}</p>`;
            }
        }

        async function testPool() {
            try {
                const response = await fetch('/api/object-pool/test');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('result').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Test Pool Completato:</strong></p>
                            <div class="bg-white p-3 border rounded">
                                <p><strong>Connessioni Acquisite:</strong> ${result.data.connections_acquired}</p>
                                <p><strong>Stato Pool:</strong></p>
                                <div class="text-sm">
                                    <p>Totale: ${result.data.pool_status.total}</p>
                                    <p>Disponibili: ${result.data.pool_status.available}</p>
                                    <p>In Uso: ${result.data.pool_status.in_use}</p>
                                    <p>Max: ${result.data.pool_status.max_size}</p>
                                </div>
                            </div>
                            <p><strong>Connessioni:</strong></p>
                            <pre class="bg-white p-2 border rounded text-xs overflow-auto">${JSON.stringify(result.data.connections, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    document.getElementById('result').innerHTML = `<p class="text-red-600">Errore: ${result.message}</p>`;
                }
            } catch (error) {
                document.getElementById('result').innerHTML = `<p class="text-red-600">Errore: ${error.message}</p>`;
            }
        }

        async function testAPI() {
            try {
                const response = await fetch('/api/object-pool/');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('apiResult').innerHTML = `
                        <div class="space-y-2">
                            <p><strong>Pattern:</strong> ${result.data.pattern_description}</p>
                            <p><strong>Gestione Pool:</strong> ${result.data.pool_management}</p>
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
