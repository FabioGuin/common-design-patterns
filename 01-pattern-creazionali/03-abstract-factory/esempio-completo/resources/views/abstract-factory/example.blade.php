<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abstract Factory Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Abstract Factory Pattern - Esempio
            </h1>
            
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Test Pattern -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa l'Abstract Factory creando componenti per tutti i temi disponibili.
                    </p>
                    
                    <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">
                        Esegui Test
                    </button>
                    
                    <div id="testResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="testResultContent" class="text-sm overflow-x-auto"></pre>
                    </div>
                </div>

                <!-- Crea Componenti -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Crea Componenti</h2>
                    <p class="text-gray-600 mb-4">
                        Crea componenti UI per un tema specifico usando l'Abstract Factory.
                    </p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tema</label>
                            <select id="themeSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($supportedThemes as $theme)
                                    <option value="{{ $theme }}">{{ ucfirst($theme) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Testo Button</label>
                            <input type="text" id="buttonText" placeholder="Testo del button" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contenuto Card</label>
                            <textarea id="cardContent" placeholder="Contenuto della card" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contenuto Modal</label>
                            <textarea id="modalContent" placeholder="Contenuto del modal" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                        </div>
                        
                        <button id="createBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                            Crea Componenti
                        </button>
                    </div>
                    
                    <div id="createResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="createResultContent" class="text-sm overflow-x-auto"></pre>
                    </div>
                </div>
            </div>

            <!-- Temi Supportati -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Temi Supportati</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach($supportedThemes as $theme)
                        <div class="p-4 border border-gray-200 rounded">
                            <h3 class="font-semibold text-lg">{{ ucfirst($theme) }}</h3>
                            <p class="text-sm text-gray-600">
                                @switch($theme)
                                    @case('dark')
                                        Tema scuro con colori contrastanti
                                        @break
                                    @case('light')
                                        Tema chiaro e minimalista
                                        @break
                                    @case('colorful')
                                        Tema colorato con gradienti
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
                const response = await fetch('/api/abstract-factory/test');
                const data = await response.json();
                
                document.getElementById('testResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('testResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('testResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('testResult').classList.remove('hidden');
            }
        });

        // Crea Componenti
        document.getElementById('createBtn').addEventListener('click', async function() {
            const theme = document.getElementById('themeSelect').value;
            const buttonText = document.getElementById('buttonText').value;
            const cardContent = document.getElementById('cardContent').value;
            const modalContent = document.getElementById('modalContent').value;
            
            if (!buttonText || !cardContent || !modalContent) {
                alert('Inserisci tutti i campi');
                return;
            }
            
            try {
                const response = await fetch('/api/abstract-factory/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ 
                        theme, 
                        button_text: buttonText, 
                        card_content: cardContent, 
                        modal_content: modalContent 
                    })
                });
                const data = await response.json();
                
                document.getElementById('createResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('createResult').classList.remove('hidden');
                
                // Pulisci i campi
                document.getElementById('buttonText').value = '';
                document.getElementById('cardContent').value = '';
                document.getElementById('modalContent').value = '';
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('createResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('createResult').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
