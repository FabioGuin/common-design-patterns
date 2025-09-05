<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Event Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Domain Event Pattern - Esempio
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
                                <option value="order-confirmed">Order Confirmed</option>
                                <option value="order-cancelled">Order Cancelled</option>
                                <option value="order-shipped">Order Shipped</option>
                                <option value="payment-processed">Payment Processed</option>
                                <option value="payment-failed">Payment Failed</option>
                                <option value="all">Tutti gli Scenari</option>
                            </select>
                        </div>
                        
                        <button id="testBtn" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Esegui Test
                        </button>
                        
                        <div id="singleResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                            <h3 class="font-semibold mb-2">Risultato:</h3>
                            <pre id="singleResultContent" class="text-sm overflow-auto max-h-96"></pre>
                        </div>
                    </div>
                </div>

                <!-- Test conferma ordine -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Conferma Ordine</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                                <input type="text" id="confirmOrderId" placeholder="order-123" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Customer ID</label>
                                <input type="text" id="confirmCustomerId" placeholder="customer-456" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                                <input type="number" id="confirmTotal" step="0.01" placeholder="150.00" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Items (JSON)</label>
                                <input type="text" id="confirmItems" placeholder='[{"productId":"PROD-001","quantity":2,"price":50.00}]' class="w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                        </div>
                        
                        <button id="confirmOrderBtn" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Conferma Ordine
                        </button>
                        
                        <div id="confirmResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                            <h3 class="font-semibold mb-2">Risultato:</h3>
                            <pre id="confirmResultContent" class="text-sm overflow-auto max-h-96"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test operazioni ordine -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Test Operazioni Ordine</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                        <input type="text" id="orderId" placeholder="order-123" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer ID</label>
                        <input type="text" id="customerId" placeholder="customer-456" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                        <input type="number" id="total" step="0.01" placeholder="150.00" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                </div>
                
                <div class="mt-4 flex flex-wrap gap-2">
                    <button id="cancelOrderBtn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Cancella Ordine
                    </button>
                    <button id="shipOrderBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Spedisci Ordine
                    </button>
                    <button id="processPaymentBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Processa Pagamento
                    </button>
                    <button id="failPaymentBtn" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                        Fallisci Pagamento
                    </button>
                </div>
                
                <div id="operationResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                    <h3 class="font-semibold mb-2">Risultato:</h3>
                    <pre id="operationResultContent" class="text-sm overflow-auto max-h-96"></pre>
                </div>
            </div>

            <!-- Spiegazione del Pattern -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-blue-900">Come Funziona il Domain Event Pattern</h2>
                
                <div class="space-y-3 text-blue-800">
                    <p>
                        <strong>1. Disaccoppiamento:</strong> I servizi non si conoscono tra loro, comunicano solo tramite eventi
                    </p>
                    <p>
                        <strong>2. Estensibilità:</strong> Puoi aggiungere nuovi listener senza modificare il codice esistente
                    </p>
                    <p>
                        <strong>3. Testabilità:</strong> Ogni servizio può essere testato indipendentemente
                    </p>
                    <p>
                        <strong>4. Manutenibilità:</strong> Codice più pulito e organizzato
                    </p>
                    <p>
                        <strong>5. Flessibilità:</strong> Puoi aggiungere/rimuovere listener facilmente
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
                const response = await fetch('/api/domain-event/test', {
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

        // Test conferma ordine
        document.getElementById('confirmOrderBtn').addEventListener('click', async function() {
            const orderId = document.getElementById('confirmOrderId').value;
            const customerId = document.getElementById('confirmCustomerId').value;
            const total = document.getElementById('confirmTotal').value;
            const items = document.getElementById('confirmItems').value;
            
            if (!orderId || !customerId || !total || !items) {
                alert('Compila tutti i campi');
                return;
            }
            
            try {
                const response = await fetch('/api/domain-event/order/confirm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        customerId: customerId,
                        total: parseFloat(total),
                        items: JSON.parse(items)
                    })
                });
                
                const data = await response.json();
                
                document.getElementById('confirmResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('confirmResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('confirmResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('confirmResult').classList.remove('hidden');
            }
        });

        // Test cancellazione ordine
        document.getElementById('cancelOrderBtn').addEventListener('click', async function() {
            const orderId = document.getElementById('orderId').value;
            const customerId = document.getElementById('customerId').value;
            const total = document.getElementById('total').value;
            
            if (!orderId || !customerId || !total) {
                alert('Compila tutti i campi');
                return;
            }
            
            try {
                const response = await fetch('/api/domain-event/order/cancel', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        customerId: customerId,
                        total: parseFloat(total),
                        reason: 'Customer request'
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

        // Test spedizione ordine
        document.getElementById('shipOrderBtn').addEventListener('click', async function() {
            const orderId = document.getElementById('orderId').value;
            const customerId = document.getElementById('customerId').value;
            
            if (!orderId || !customerId) {
                alert('Compila Order ID e Customer ID');
                return;
            }
            
            try {
                const response = await fetch('/api/domain-event/order/ship', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        customerId: customerId,
                        trackingNumber: 'TRK-' + Math.random().toString(36).substr(2, 9).toUpperCase(),
                        carrier: 'DHL'
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

        // Test processamento pagamento
        document.getElementById('processPaymentBtn').addEventListener('click', async function() {
            const orderId = document.getElementById('orderId').value;
            const customerId = document.getElementById('customerId').value;
            const total = document.getElementById('total').value;
            
            if (!orderId || !customerId || !total) {
                alert('Compila tutti i campi');
                return;
            }
            
            try {
                const response = await fetch('/api/domain-event/payment/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        customerId: customerId,
                        amount: parseFloat(total),
                        paymentMethod: 'CREDIT_CARD',
                        transactionId: 'TXN-' + Math.random().toString(36).substr(2, 9).toUpperCase()
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

        // Test fallimento pagamento
        document.getElementById('failPaymentBtn').addEventListener('click', async function() {
            const orderId = document.getElementById('orderId').value;
            const customerId = document.getElementById('customerId').value;
            const total = document.getElementById('total').value;
            
            if (!orderId || !customerId || !total) {
                alert('Compila tutti i campi');
                return;
            }
            
            try {
                const response = await fetch('/api/domain-event/payment/fail', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        customerId: customerId,
                        amount: parseFloat(total),
                        paymentMethod: 'CREDIT_CARD',
                        reason: 'Insufficient funds'
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
    </script>
</body>
</html>
