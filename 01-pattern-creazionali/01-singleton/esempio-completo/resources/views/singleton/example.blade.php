<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Singleton Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Singleton Pattern - Esempio
            </h1>
            
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Test Base -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Base</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern Singleton ottenendo l'istanza e visualizzando le informazioni.
                    </p>
                    
                    <button id="basicTestBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">
                        Esegui Test Base
                    </button>
                    
                    <div id="basicResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="basicResultContent" class="text-sm overflow-x-auto"></pre>
                    </div>
                </div>

                <!-- Test Unicità -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Unicità</h2>
                    <p class="text-gray-600 mb-4">
                        Dimostra che multiple chiamate restituiscono la stessa istanza.
                    </p>
                    
                    <button id="uniquenessTestBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-4">
                        Testa Unicità
                    </button>
                    
                    <div id="uniquenessResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="uniquenessResultContent" class="text-sm overflow-x-auto"></pre>
                    </div>
                </div>

                <!-- Test Protezione Clonazione -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Protezione</h2>
                    <p class="text-gray-600 mb-4">
                        Testa la protezione contro la clonazione dell'istanza.
                    </p>
                    
                    <button id="cloneTestBtn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 mb-4">
                        Testa Clonazione
                    </button>
                    
                    <div id="cloneResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="cloneResultContent" class="text-sm overflow-x-auto"></pre>
                    </div>
                </div>

                <!-- Informazioni Attuali -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Stato Attuale</h2>
                    <p class="text-gray-600 mb-4">
                        Informazioni sull'istanza Singleton corrente.
                    </p>
                    
                    <div class="space-y-2">
                        <div><strong>ID Istanza:</strong> <span class="font-mono text-sm">{{ $info['id'] }}</span></div>
                        <div><strong>Accessi Totali:</strong> <span class="font-mono text-sm">{{ $info['access_count'] }}</span></div>
                        <div><strong>Memoria:</strong> <span class="font-mono text-sm">{{ number_format($info['memory_usage'] / 1024, 2) }} KB</span></div>
                        <div><strong>Creato:</strong> <span class="font-mono text-sm">{{ $info['data']['created_at'] }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Aggiungi Dati -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Aggiungi Dati</h2>
                <p class="text-gray-600 mb-4">
                    Aggiungi dati all'istanza Singleton per dimostrare la condivisione dello stato.
                </p>
                
                <div class="flex gap-4">
                    <input type="text" id="dataKey" placeholder="Chiave" class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" id="dataValue" placeholder="Valore" class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button id="addDataBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                        Aggiungi
                    </button>
                </div>
                
                <div id="addDataResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                    <h3 class="font-semibold mb-2">Risultato:</h3>
                    <pre id="addDataResultContent" class="text-sm overflow-x-auto"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test Base
        document.getElementById('basicTestBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/singleton/');
                const data = await response.json();
                
                document.getElementById('basicResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('basicResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('basicResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('basicResult').classList.remove('hidden');
            }
        });

        // Test Unicità
        document.getElementById('uniquenessTestBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/singleton/test');
                const data = await response.json();
                
                document.getElementById('uniquenessResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('uniquenessResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('uniquenessResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('uniquenessResult').classList.remove('hidden');
            }
        });

        // Test Clonazione
        document.getElementById('cloneTestBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/singleton/clone-test');
                const data = await response.json();
                
                document.getElementById('cloneResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('cloneResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('cloneResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('cloneResult').classList.remove('hidden');
            }
        });

        // Aggiungi Dati
        document.getElementById('addDataBtn').addEventListener('click', async function() {
            const key = document.getElementById('dataKey').value;
            const value = document.getElementById('dataValue').value;
            
            if (!key || !value) {
                alert('Inserisci sia la chiave che il valore');
                return;
            }
            
            try {
                const response = await fetch('/api/singleton/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ data: { [key]: value } })
                });
                const data = await response.json();
                
                document.getElementById('addDataResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('addDataResult').classList.remove('hidden');
                
                // Pulisci i campi
                document.getElementById('dataKey').value = '';
                document.getElementById('dataValue').value = '';
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('addDataResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('addDataResult').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
