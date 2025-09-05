<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hexagonal Architecture Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Hexagonal Architecture Pattern - Sistema Ordini
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test del Pattern -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test del Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern Hexagonal Architecture per verificare l'isolamento della logica di business.
                    </p>
                    
                    <div class="space-y-2">
                        <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                            Test Completo
                        </button>
                        
                        <button id="statsBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                            Statistiche
                        </button>
                        
                        <button id="architectureBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 w-full">
                            Info Architettura
                        </button>
                    </div>
                    
                    <div id="result" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="resultContent" class="text-sm overflow-auto max-h-96"></pre>
                    </div>
                </div>

                <!-- Commands -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Operazioni (Core Domain)</h2>
                    
                    <div class="space-y-4">
                        <!-- Crea Ordine -->
                        <div>
                            <h3 class="font-medium mb-2">Crea Ordine</h3>
                            <form id="createOrderForm" class="space-y-2">
                                <input type="text" name="customer_name" placeholder="Nome Cliente" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                <input type="email" name="customer_email" placeholder="Email Cliente" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" name="items[0][product_id]" placeholder="Product ID" class="border border-gray-300 rounded px-3 py-2" required>
                                    <input type="number" name="items[0][quantity]" placeholder="Qty" min="1" class="border border-gray-300 rounded px-3 py-2" required>
                                    <input type="number" name="items[0][price]" placeholder="Price" step="0.01" min="0" class="border border-gray-300 rounded px-3 py-2" required>
                                </div>
                                <input type="number" name="discount" placeholder="Sconto" step="0.01" min="0" class="w-full border border-gray-300 rounded px-3 py-2">
                                <button type="submit" class="w-full bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                    Crea Ordine
                                </button>
                            </form>
                        </div>
                        
                        <!-- Aggiorna Ordine -->
                        <div>
                            <h3 class="font-medium mb-2">Aggiorna Ordine</h3>
                            <form id="updateOrderForm" class="space-y-2">
                                <input type="text" name="order_id" placeholder="ID Ordine" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                <select name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                                    <option value="">Seleziona Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                                <button type="submit" class="w-full bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                    Aggiorna Ordine
                                </button>
                            </form>
                        </div>
                        
                        <!-- Cancella Ordine -->
                        <div>
                            <h3 class="font-medium mb-2">Cancella Ordine</h3>
                            <form id="cancelOrderForm" class="space-y-2">
                                <input type="text" name="order_id" placeholder="ID Ordine" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                <input type="text" name="reason" placeholder="Motivo Cancellazione" class="w-full border border-gray-300 rounded px-3 py-2">
                                <button type="submit" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                    Cancella Ordine
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Queries -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Query (Ports & Adapters)</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Lista Ordini -->
                    <div>
                        <h3 class="font-medium mb-2">Lista Ordini</h3>
                        <button id="loadOrdersBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 mb-4">
                            Carica Ordini
                        </button>
                        
                        <div id="ordersList" class="hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Importo</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dettaglio Ordine -->
                    <div>
                        <h3 class="font-medium mb-2">Dettaglio Ordine</h3>
                        <div class="flex space-x-2 mb-4">
                            <input type="text" id="orderIdInput" placeholder="ID Ordine" class="flex-1 border border-gray-300 rounded px-3 py-2">
                            <button id="getOrderBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Carica
                            </button>
                        </div>
                        
                        <div id="orderDetails" class="hidden">
                            <div class="p-4 bg-gray-50 rounded">
                                <h4 class="font-semibold mb-2">Dettagli Ordine</h4>
                                <pre id="orderDetailsContent" class="text-sm overflow-auto"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servizi Esterni -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Servizi Esterni (Outbound Adapters)</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Pagamenti -->
                    <div>
                        <h3 class="font-medium mb-2">Pagamenti (Stripe)</h3>
                        <div class="flex space-x-2 mb-4">
                            <input type="text" id="paymentOrderIdInput" placeholder="ID Ordine" class="flex-1 border border-gray-300 rounded px-3 py-2">
                            <button id="processPaymentBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                Processa Pagamento
                            </button>
                        </div>
                        
                        <div id="paymentResult" class="hidden">
                            <div class="p-3 bg-gray-50 rounded">
                                <div id="paymentContent" class="text-sm"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notifiche -->
                    <div>
                        <h3 class="font-medium mb-2">Notifiche (Email)</h3>
                        <div class="space-y-2">
                            <div class="flex space-x-2">
                                <input type="text" id="notificationOrderIdInput" placeholder="ID Ordine" class="flex-1 border border-gray-300 rounded px-3 py-2">
                                <select id="notificationTypeSelect" class="border border-gray-300 rounded px-3 py-2">
                                    <option value="confirmation">Conferma</option>
                                    <option value="cancellation">Cancellazione</option>
                                    <option value="shipping">Spedizione</option>
                                    <option value="delivery">Consegna</option>
                                </select>
                            </div>
                            <button id="sendNotificationBtn" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Invia Notifica
                            </button>
                        </div>
                        
                        <div id="notificationResult" class="hidden mt-2">
                            <div class="p-3 bg-gray-50 rounded">
                                <div id="notificationContent" class="text-sm"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Architettura -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Architettura Hexagonal</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Core Domain -->
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-800 mb-2">Core Domain</h3>
                            <p class="text-sm text-blue-600">Logica di business pura e isolata</p>
                            <ul class="text-xs text-blue-600 mt-2 text-left">
                                <li>• OrderService</li>
                                <li>• Order Entity</li>
                                <li>• Business Rules</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Ports -->
                    <div class="text-center">
                        <div class="bg-green-100 rounded-lg p-4">
                            <h3 class="font-semibold text-green-800 mb-2">Ports</h3>
                            <p class="text-sm text-green-600">Interfacce che definiscono i contratti</p>
                            <ul class="text-xs text-green-600 mt-2 text-left">
                                <li>• OrderRepositoryInterface</li>
                                <li>• PaymentServiceInterface</li>
                                <li>• NotificationServiceInterface</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Adapters -->
                    <div class="text-center">
                        <div class="bg-purple-100 rounded-lg p-4">
                            <h3 class="font-semibold text-purple-800 mb-2">Adapters</h3>
                            <p class="text-sm text-purple-600">Implementazioni concrete</p>
                            <ul class="text-xs text-purple-600 mt-2 text-left">
                                <li>• EloquentOrderRepository</li>
                                <li>• StripePaymentService</li>
                                <li>• EmailNotificationService</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test del pattern
        document.getElementById('testBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/hexagonal-architecture/test');
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
                const response = await fetch('/api/hexagonal-architecture/stats');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Info architettura
        document.getElementById('architectureBtn').addEventListener('click', function() {
            const architectureInfo = {
                "pattern": "Hexagonal Architecture",
                "description": "Isola la logica di business da tutti i sistemi esterni",
                "components": {
                    "core_domain": "Logica di business pura e isolata",
                    "ports": "Interfacce che definiscono i contratti",
                    "adapters": "Implementazioni concrete dei port",
                    "inbound_adapters": "Gestiscono input (web, API, CLI)",
                    "outbound_adapters": "Gestiscono output (database, servizi esterni)"
                },
                "benefits": [
                    "Logica di business completamente isolata",
                    "Facile da testare e mantenere",
                    "Indipendente da tecnologie esterne",
                    "Flessibile nell'integrazione"
                ]
            };
            
            document.getElementById('resultContent').textContent = JSON.stringify(architectureInfo, null, 2);
            document.getElementById('result').classList.remove('hidden');
        });

        // Crea ordine
        document.getElementById('createOrderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Converte items in array
            data.items = [{
                product_id: data['items[0][product_id]'],
                quantity: parseInt(data['items[0][quantity]']),
                price: parseFloat(data['items[0][price]'])
            }];
            
            // Rimuove i campi originali
            delete data['items[0][product_id]'];
            delete data['items[0][quantity]'];
            delete data['items[0][price]'];
            
            try {
                const response = await fetch('/api/hexagonal-architecture/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Ordine creato: ' + result.data.id);
                    loadOrders();
                } else {
                    alert('Errore: ' + result.message);
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore: ' + error.message);
            }
        });

        // Aggiorna ordine
        document.getElementById('updateOrderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const orderId = data.order_id;
            delete data.order_id;
            
            try {
                const response = await fetch(`/api/hexagonal-architecture/orders/${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Ordine aggiornato: ' + result.data.id);
                    loadOrders();
                } else {
                    alert('Errore: ' + result.message);
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore: ' + error.message);
            }
        });

        // Cancella ordine
        document.getElementById('cancelOrderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const orderId = data.order_id;
            delete data.order_id;
            
            try {
                const response = await fetch(`/api/hexagonal-architecture/orders/${orderId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Ordine cancellato: ' + result.data.id);
                    loadOrders();
                } else {
                    alert('Errore: ' + result.message);
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore: ' + error.message);
            }
        });

        // Carica ordini
        document.getElementById('loadOrdersBtn').addEventListener('click', loadOrders);

        function loadOrders() {
            fetch('/api/hexagonal-architecture/orders')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('ordersTableBody');
                        tbody.innerHTML = '';
                        
                        data.data.forEach(order => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="px-4 py-2 text-sm text-gray-900">${order.id}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">${order.customer_name}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">${order.status}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">€${order.total_amount}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <button onclick="viewOrder('${order.id}')" class="text-blue-600 hover:text-blue-900">Vedi</button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                        
                        document.getElementById('ordersList').classList.remove('hidden');
                    }
                })
                .catch(error => console.error('Errore:', error));
        }

        // Visualizza ordine
        document.getElementById('getOrderBtn').addEventListener('click', function() {
            const orderId = document.getElementById('orderIdInput').value;
            if (orderId) {
                viewOrder(orderId);
            }
        });

        function viewOrder(orderId) {
            fetch(`/api/hexagonal-architecture/orders/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('orderDetailsContent').textContent = JSON.stringify(data.data, null, 2);
                        document.getElementById('orderDetails').classList.remove('hidden');
                    } else {
                        alert('Errore: ' + data.message);
                    }
                })
                .catch(error => console.error('Errore:', error));
        }

        // Processa pagamento
        document.getElementById('processPaymentBtn').addEventListener('click', function() {
            const orderId = document.getElementById('paymentOrderIdInput').value;
            if (orderId) {
                fetch(`/api/hexagonal-architecture/process-payment/${orderId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('paymentContent').textContent = JSON.stringify(data, null, 2);
                    document.getElementById('paymentResult').classList.remove('hidden');
                })
                .catch(error => console.error('Errore:', error));
            }
        });

        // Invia notifica
        document.getElementById('sendNotificationBtn').addEventListener('click', function() {
            const orderId = document.getElementById('notificationOrderIdInput').value;
            const type = document.getElementById('notificationTypeSelect').value;
            
            if (orderId) {
                fetch(`/api/hexagonal-architecture/send-notification/${orderId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ type: type })
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('notificationContent').textContent = JSON.stringify(data, null, 2);
                    document.getElementById('notificationResult').classList.remove('hidden');
                })
                .catch(error => console.error('Errore:', error));
            }
        });
    </script>
</body>
</html>
