<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Value Object Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Value Object Pattern - Esempio
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test singolo Value Object -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Singolo Value Object</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo di Value Object
                            </label>
                            <select id="valueObjectType" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="price">Price (Prezzi)</option>
                                <option value="address">Address (Indirizzi)</option>
                                <option value="sku">ProductSku (SKU Prodotti)</option>
                                <option value="email">Email (Indirizzi Email)</option>
                                <option value="all">Tutti i Value Object</option>
                            </select>
                        </div>
                        
                        <button id="testBtn" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Testa Value Object
                        </button>
                        
                        <div id="singleResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                            <h3 class="font-semibold mb-2">Risultato:</h3>
                            <pre id="singleResultContent" class="text-sm overflow-auto"></pre>
                        </div>
                    </div>
                </div>

                <!-- Test operazioni prezzi -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Operazioni Prezzi</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prezzo 1</label>
                                <input type="number" id="price1Amount" step="0.01" placeholder="10.50" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <select id="price1Currency" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1">
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prezzo 2</label>
                                <input type="number" id="price2Amount" step="0.01" placeholder="5.25" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <select id="price2Currency" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1">
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Operazione</label>
                            <select id="operation" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="add">Somma</option>
                                <option value="subtract">Sottrazione</option>
                                <option value="multiply">Moltiplicazione</option>
                            </select>
                        </div>
                        
                        <button id="calculateBtn" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Calcola
                        </button>
                        
                        <div id="calculationResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                            <h3 class="font-semibold mb-2">Risultato:</h3>
                            <pre id="calculationResultContent" class="text-sm overflow-auto"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test validazione indirizzi -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Test Validazione Indirizzi</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Via</label>
                        <input type="text" id="addressStreet" placeholder="Via Roma 123" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Città</label>
                        <input type="text" id="addressCity" placeholder="Milano" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Codice Postale</label>
                        <input type="text" id="addressPostalCode" placeholder="20100" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Paese</label>
                        <select id="addressCountry" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="IT">Italia</option>
                            <option value="US">Stati Uniti</option>
                            <option value="GB">Regno Unito</option>
                            <option value="FR">Francia</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stato/Provincia</label>
                        <input type="text" id="addressState" placeholder="Lombardia" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div class="flex items-end">
                        <button id="validateAddressBtn" class="w-full bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                            Valida Indirizzo
                        </button>
                    </div>
                </div>
                
                <div id="addressResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                    <h3 class="font-semibold mb-2">Risultato:</h3>
                    <pre id="addressResultContent" class="text-sm overflow-auto"></pre>
                </div>
            </div>

            <!-- Spiegazione del Pattern -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-blue-900">Come Funziona il Value Object Pattern</h2>
                
                <div class="space-y-3 text-blue-800">
                    <p>
                        <strong>1. Immutabilità:</strong> I Value Object non possono essere modificati dopo la creazione
                    </p>
                    <p>
                        <strong>2. Validazione:</strong> Tutti i valori vengono validati automaticamente alla creazione
                    </p>
                    <p>
                        <strong>3. Type Safety:</strong> Il compilatore ti aiuta a evitare errori di tipo
                    </p>
                    <p>
                        <strong>4. Confronto per Valore:</strong> Due Value Object sono uguali se hanno lo stesso valore
                    </p>
                    <p>
                        <strong>5. Semantica Chiara:</strong> Il codice esprime meglio l'intenzione
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test singolo Value Object
        document.getElementById('testBtn').addEventListener('click', async function() {
            const valueObjectType = document.getElementById('valueObjectType').value;
            
            try {
                const response = await fetch('/api/value-object/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ type: valueObjectType })
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

        // Test operazioni prezzi
        document.getElementById('calculateBtn').addEventListener('click', async function() {
            const price1Amount = document.getElementById('price1Amount').value;
            const price1Currency = document.getElementById('price1Currency').value;
            const price2Amount = document.getElementById('price2Amount').value;
            const price2Currency = document.getElementById('price2Currency').value;
            const operation = document.getElementById('operation').value;
            
            if (!price1Amount || !price2Amount) {
                alert('Inserisci entrambi i prezzi');
                return;
            }
            
            try {
                const response = await fetch('/api/value-object/price/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        amount1: parseFloat(price1Amount),
                        currency1: price1Currency,
                        amount2: parseFloat(price2Amount),
                        currency2: price2Currency,
                        operation: operation
                    })
                });
                
                const data = await response.json();
                
                document.getElementById('calculationResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('calculationResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('calculationResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('calculationResult').classList.remove('hidden');
            }
        });

        // Test validazione indirizzi
        document.getElementById('validateAddressBtn').addEventListener('click', async function() {
            const street = document.getElementById('addressStreet').value;
            const city = document.getElementById('addressCity').value;
            const postalCode = document.getElementById('addressPostalCode').value;
            const country = document.getElementById('addressCountry').value;
            const state = document.getElementById('addressState').value;
            
            if (!street || !city || !postalCode || !country) {
                alert('Compila tutti i campi obbligatori');
                return;
            }
            
            try {
                const response = await fetch('/api/value-object/address/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        street: street,
                        city: city,
                        postalCode: postalCode,
                        country: country,
                        state: state || null
                    })
                });
                
                const data = await response.json();
                
                document.getElementById('addressResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('addressResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('addressResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('addressResult').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
