<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factory Method Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Factory Method Pattern - Esempio
            </h1>
            
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Test Base -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern Factory Method creando utenti con diversi ruoli.
                    </p>
                    
                    <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">
                        Esegui Test
                    </button>
                    
                    <div id="testResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="testResultContent" class="text-sm overflow-x-auto"></pre>
                    </div>
                </div>

                <!-- Crea Utente -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Crea Utente</h2>
                    <p class="text-gray-600 mb-4">
                        Crea un utente specifico usando il Factory Method.
                    </p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ruolo</label>
                            <select id="userRole" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($supportedRoles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                            <input type="text" id="userName" placeholder="Nome utente" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="userEmail" placeholder="email@example.com" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <button id="createBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                            Crea Utente
                        </button>
                    </div>
                    
                    <div id="createResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="createResultContent" class="text-sm overflow-x-auto"></pre>
                    </div>
                </div>
            </div>

            <!-- Ruoli Supportati -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Ruoli Supportati</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach($supportedRoles as $role)
                        <div class="p-4 border border-gray-200 rounded">
                            <h3 class="font-semibold text-lg">{{ ucfirst($role) }}</h3>
                            <p class="text-sm text-gray-600">
                                @switch($role)
                                    @case('admin')
                                        Accesso completo al sistema
                                        @break
                                    @case('regular')
                                        Utente standard con accesso limitato
                                        @break
                                    @case('guest')
                                        Utente ospite con accesso minimo
                                        @break
                                @endswitch
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test Pattern
        document.getElementById('testBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/factory-method/test');
                const data = await response.json();
                
                document.getElementById('testResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('testResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('testResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('testResult').classList.remove('hidden');
            }
        });

        // Crea Utente
        document.getElementById('createBtn').addEventListener('click', async function() {
            const role = document.getElementById('userRole').value;
            const name = document.getElementById('userName').value;
            const email = document.getElementById('userEmail').value;
            
            if (!name || !email) {
                alert('Inserisci nome e email');
                return;
            }
            
            try {
                const response = await fetch('/api/factory-method/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ role, name, email })
                });
                const data = await response.json();
                
                document.getElementById('createResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('createResult').classList.remove('hidden');
                
                // Pulisci i campi
                document.getElementById('userName').value = '';
                document.getElementById('userEmail').value = '';
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('createResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('createResult').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
