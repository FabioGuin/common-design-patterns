<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Null Object Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Null Object Pattern - Esempio
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test singolo servizio -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Singolo Servizio</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo di Servizio
                            </label>
                            <select id="serviceType" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="email">Email Service</option>
                                <option value="sms">SMS Service</option>
                                <option value="disabled">Null Object (Disabled)</option>
                            </select>
                        </div>
                        
                        <button id="testBtn" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Testa Servizio
                        </button>
                        
                        <div id="singleResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                            <h3 class="font-semibold mb-2">Risultato:</h3>
                            <pre id="singleResultContent" class="text-sm overflow-auto"></pre>
                        </div>
                    </div>
                </div>

                <!-- Test tutti i servizi -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Tutti i Servizi</h2>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">
                            Testa tutti i servizi disponibili per vedere come il Null Object Pattern
                            gestisce i diversi scenari.
                        </p>
                        
                        <button id="testAllBtn" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Testa Tutti i Servizi
                        </button>
                        
                        <div id="allResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                            <h3 class="font-semibold mb-2">Risultati:</h3>
                            <pre id="allResultContent" class="text-sm overflow-auto"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informazioni sui servizi -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Informazioni sui Servizi</h2>
                
                <div class="space-y-4">
                    <button id="infoBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                        Mostra Informazioni
                    </button>
                    
                    <div id="infoResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Informazioni:</h3>
                        <pre id="infoResultContent" class="text-sm overflow-auto"></pre>
                    </div>
                </div>
            </div>

            <!-- Spiegazione del Pattern -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-blue-900">Come Funziona il Null Object Pattern</h2>
                
                <div class="space-y-3 text-blue-800">
                    <p>
                        <strong>1. Interfaccia Comune:</strong> Tutti i servizi implementano la stessa interfaccia
                    </p>
                    <p>
                        <strong>2. Servizi Reali:</strong> Email e SMS inviano notifiche quando disponibili
                    </p>
                    <p>
                        <strong>3. Null Object:</strong> Quando i servizi non sono disponibili, viene usato un oggetto "fittizio"
                    </p>
                    <p>
                        <strong>4. Comportamento Sicuro:</strong> Il null object non fa nulla ma non rompe il codice
                    </p>
                    <p>
                        <strong>5. Eliminazione Controlli Null:</strong> Non serve controllare se un oggetto è null
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test singolo servizio
        document.getElementById('testBtn').addEventListener('click', async function() {
            const serviceType = document.getElementById('serviceType').value;
            
            try {
                const response = await fetch('/api/null-object/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ type: serviceType })
                });
                
                const data = await response.json();
                
                document.getElementById('singleResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('singleResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('singleResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('singleResult').classList.remove('hidden');
            }
        });

        // Test tutti i servizi
        document.getElementById('testAllBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/null-object/test-all');
                const data = await response.json();
                
                document.getElementById('allResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('allResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('allResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('allResult').classList.remove('hidden');
            }
        });

        // Informazioni sui servizi
        document.getElementById('infoBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/null-object/info');
                const data = await response.json();
                
                document.getElementById('infoResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('infoResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('infoResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('infoResult').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
