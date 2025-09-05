<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write-Behind Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Write-Behind Pattern - Esempio
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test del Pattern -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test del Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern Write-Behind per verificare la scrittura asincrona e le performance.
                    </p>
                    
                    <div class="space-y-2">
                        <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Test Completo
                        </button>
                        
                        <button id="performanceBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Test Performance
                        </button>
                        
                        <button id="stressBtn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            Test Stress
                        </button>
                        
                        <button id="statsBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                            Statistiche
                        </button>
                    </div>
                    
                    <div id="result" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="resultContent" class="text-sm overflow-auto max-h-96"></pre>
                    </div>
                </div>

                <!-- Creazione Log -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Crea Log</h2>
                    
                    <form id="logForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Livello</label>
                            <select name="level" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required>
                                <option value="debug">Debug</option>
                                <option value="info" selected>Info</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Messaggio</label>
                            <textarea name="message" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contesto (JSON)</label>
                            <textarea name="context" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" placeholder='{"key": "value"}'></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">User ID</label>
                                <input type="number" name="user_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IP Address</label>
                                <input type="text" name="ip_address" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="127.0.0.1">
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                            Crea Log (Write-Behind)
                        </button>
                    </form>
                    
                    <div id="logResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="logResultContent" class="text-sm overflow-auto"></pre>
                    </div>
                </div>
            </div>

            <!-- Lista Log -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Log Recenti</h2>
                
                <div class="flex space-x-2 mb-4">
                    <button id="loadLogsBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                        Carica Log
                    </button>
                    
                    <button id="clearLogsBtn" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Pulisci Cache
                    </button>
                </div>
                
                <div id="logsList" class="hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livello</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Messaggio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Azioni</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test del pattern
        document.getElementById('testBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/write-behind/test');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Test di performance
        document.getElementById('performanceBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/write-behind/performance');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Test di stress
        document.getElementById('stressBtn').addEventListener('click', async function() {
            if (!confirm('Il test di stress creerà 5000 log. Continuare?')) {
                return;
            }
            
            try {
                const response = await fetch('/api/write-behind/stress');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Statistiche
        document.getElementById('statsBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/write-behind/stats');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Creazione log
        document.getElementById('logForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Parse context JSON
            if (data.context) {
                try {
                    data.context = JSON.parse(data.context);
                } catch (e) {
                    data.context = {};
                }
            } else {
                data.context = {};
            }
            
            try {
                const response = await fetch('/api/write-behind/logs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                document.getElementById('logResultContent').textContent = JSON.stringify(result, null, 2);
                document.getElementById('logResult').classList.remove('hidden');
                
                // Ricarica la lista log
                loadLogs();
                
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('logResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('logResult').classList.remove('hidden');
            }
        });

        // Carica log
        document.getElementById('loadLogsBtn').addEventListener('click', loadLogs);

        // Pulisci cache
        document.getElementById('clearLogsBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/write-behind/clear-cache', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                alert('Cache pulita: ' + data.message);
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nella pulizia della cache: ' + error.message);
            }
        });

        async function loadLogs() {
            try {
                const response = await fetch('/api/write-behind/logs');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('logsTableBody');
                    tbody.innerHTML = '';
                    
                    data.data.forEach(log => {
                        const row = document.createElement('tr');
                        const levelColor = {
                            'debug': 'text-gray-500',
                            'info': 'text-blue-500',
                            'warning': 'text-yellow-500',
                            'error': 'text-red-500',
                            'critical': 'text-red-700 font-bold'
                        }[log.level] || 'text-gray-500';
                        
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm ${levelColor}">${log.level.toUpperCase()}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">${log.message}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(log.created_at).toLocaleString()}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button onclick="viewLog('${log.id}')" class="text-blue-600 hover:text-blue-900">Vedi</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    document.getElementById('logsList').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Errore nel caricamento log:', error);
            }
        }

        // Visualizza log
        async function viewLog(id) {
            try {
                const response = await fetch(`/api/write-behind/logs/${id}`);
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
            }
        }
    </script>
</body>
</html>
