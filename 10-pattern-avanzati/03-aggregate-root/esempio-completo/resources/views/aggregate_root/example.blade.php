<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggregate Root Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Aggregate Root Pattern - Esempio
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test singolo scenario -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Singolo Scenario</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo di Test
                            </label>
                            <select id="testType" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="order">Order Aggregate</option>
                                <option value="business-rules">Regole di Business</option>
                                <option value="events">Eventi di Dominio</option>
                                <option value="all">Tutti gli Scenari</option>
                            </select>
                        </div>
                        
                        <button id="testBtn" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Esegui Test
                        </button>
                        
                        <div id="singleResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                            <h3 class="font-semibold mb-2">Risultato:</h3>
                            <pre id="singleResultContent" class="text-sm overflow-auto"></pre>
                        </div>
                    </div>
                </div>

                <!-- Test creazione ordine -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Creazione Ordine</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer ID</label>
                            <input type="text" id="customerId" placeholder="customer-123" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        
                        <button id="createOrderBtn" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Crea Ordine
                        </button>
                        
                        <div id="createResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                            <h3 class="font-semibold mb-2">Risultato:</h3>
                            <pre id="createResultContent" class="text-sm overflow-auto"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test operazioni ordine -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Test Operazioni Ordine</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                        <input type="text" id="orderId" placeholder="order-1" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product ID</label>
                        <input type="text" id="productId" placeholder="PROD-001" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantità</label>
                        <input type="number" id="quantity" placeholder="2" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prezzo</label>
                        <input type="number" id="price" step="0.01" placeholder="10.50" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                </div>
                
                <div class="mt-4 flex space-x-2">
                    <button id="addItemBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Aggiungi Item
                    </button>
                    <button id="confirmOrderBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Conferma Ordine
                    </button>
                    <button id="cancelOrderBtn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Cancella Ordine
                    </button>
                </div>
                
                <div id="operationResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                    <h3 class="font-semibold mb-2">Risultato:</h3>
                    <pre id="operationResultContent" class="text-sm overflow-auto"></pre>
                </div>
            </div>

            <!-- Spiegazione del Pattern -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-blue-900">Come Funziona l'Aggregate Root Pattern</h2>
                
                <div class="space-y-3 text-blue-800">
                    <p>
                        <strong>1. Controllo Centralizzato:</strong> Solo l'Aggregate Root può essere modificato dall'esterno
                    </p>
                    <p>
                        <strong>2. Regole di Business:</strong> Tutte le regole sono centralizzate nell'aggregate root
                    </p>
                    <p>
                        <strong>3. Consistenza Garantita:</strong> Tutte le modifiche mantengono la coerenza dei dati
                    </p>
                    <p>
                        <strong>4. Transazioni Atomiche:</strong> Una modifica all'aggregate root = una transazione
                    </p>
                    <p>
                        <strong>5. Eventi di Dominio:</strong> L'aggregate root emette eventi per notificare cambiamenti
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test singolo scenario
        document.getElementById('testBtn').addEventListener('click', async function() {
            const testType = document.getElementById('testType').value;
            
            try {
                const response = await fetch('/api/aggregate-root/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ type: testType })
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

        // Test creazione ordine
        document.getElementById('createOrderBtn').addEventListener('click', async function() {
            const customerId = document.getElementById('customerId').value;
            
            if (!customerId) {
                alert('Inserisci un Customer ID');
                return;
            }
            
            try {
                const response = await fetch('/api/aggregate-root/order/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ customerId: customerId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('orderId').value = data.data.id;
                }
                
                document.getElementById('createResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('createResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('createResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('createResult').classList.remove('hidden');
            }
        });

        // Test aggiunta item
        document.getElementById('addItemBtn').addEventListener('click', async function() {
            const orderId = document.getElementById('orderId').value;
            const productId = document.getElementById('productId').value;
            const quantity = document.getElementById('quantity').value;
            const price = document.getElementById('price').value;
            
            if (!orderId || !productId || !quantity || !price) {
                alert('Compila tutti i campi');
                return;
            }
            
            try {
                const response = await fetch(`/api/aggregate-root/order/${orderId}/add-item`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        productId: productId,
                        quantity: parseInt(quantity),
                        price: parseFloat(price)
                    })
                });
                
                const data = await response.json();
                
                document.getElementById('operationResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('operationResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('operationResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('operationResult').classList.remove('hidden');
            }
        });

        // Test conferma ordine
        document.getElementById('confirmOrderBtn').addEventListener('click', async function() {
            const orderId = document.getElementById('orderId').value;
            
            if (!orderId) {
                alert('Inserisci un Order ID');
                return;
            }
            
            try {
                const response = await fetch(`/api/aggregate-root/order/${orderId}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        shippingAddress: {
                            street: 'Via Roma 123',
                            city: 'Milano',
                            postalCode: '20100',
                            country: 'IT',
                            state: 'Lombardia'
                        },
                        billingAddress: {
                            street: 'Via Roma 123',
                            city: 'Milano',
                            postalCode: '20100',
                            country: 'IT',
                            state: 'Lombardia'
                        },
                        payment: {
                            method: 'CREDIT_CARD',
                            status: 'PENDING',
                            transactionId: 'TXN-123',
                            cardLastFour: '1234',
                            cardBrand: 'VISA'
                        }
                    })
                });
                
                const data = await response.json();
                
                document.getElementById('operationResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('operationResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('operationResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('operationResult').classList.remove('hidden');
            }
        });

        // Test cancellazione ordine
        document.getElementById('cancelOrderBtn').addEventListener('click', async function() {
            const orderId = document.getElementById('orderId').value;
            
            if (!orderId) {
                alert('Inserisci un Order ID');
                return;
            }
            
            try {
                const response = await fetch(`/api/aggregate-root/order/${orderId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                document.getElementById('operationResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('operationResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('operationResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('operationResult').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
