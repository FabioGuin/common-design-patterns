<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CQRS + Event Sourcing Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                CQRS + Event Sourcing Pattern - Sistema Ordini
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test del Pattern -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test del Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern CQRS + Event Sourcing per verificare la separazione di Command e Query.
                    </p>
                    
                    <div class="space-y-2">
                        <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                            Test Completo
                        </button>
                        
                        <button id="statsBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                            Statistiche
                        </button>
                        
                        <button id="rebuildBtn" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 w-full">
                            Rebuild Projection
                        </button>
                    </div>
                    
                    <div id="result" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="resultContent" class="text-sm overflow-auto max-h-96"></pre>
                    </div>
                </div>

                <!-- Commands -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Commands (Scrittura)</h2>
                    
                    <div class="space-y-4">
                        <!-- Crea Ordine -->
                        <div>
                            <h3 class="font-medium mb-2">Crea Ordine</h3>
                            <form id="createOrderForm" class="space-y-2">
                                <input type="text" name="customer_name" placeholder="Nome Cliente" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                <input type="email" name="customer_email" placeholder="Email Cliente" class="w-full border border-gray-300 rounded px-3 py-2" required>
                                <input type="number" name="total_amount" placeholder="Importo Totale" step="0.01" class="w-full border border-gray-300 rounded px-3 py-2" required>
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
                                    <option value="confirmed">Confirmed</option>
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
                <h2 class="text-xl font-semibold mb-4">Queries (Lettura)</h2>
                
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

            <!-- Eventi e Audit -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Eventi e Audit Trail</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Eventi Ordine -->
                    <div>
                        <h3 class="font-medium mb-2">Eventi Ordine</h3>
                        <div class="flex space-x-2 mb-4">
                            <input type="text" id="eventOrderIdInput" placeholder="ID Ordine" class="flex-1 border border-gray-300 rounded px-3 py-2">
                            <button id="getEventsBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Carica Eventi
                            </button>
                        </div>
                        
                        <div id="eventsList" class="hidden">
                            <div class="space-y-2">
                                <div id="eventsContent" class="text-sm"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Audit Trail -->
                    <div>
                        <h3 class="font-medium mb-2">Audit Trail</h3>
                        <div class="flex space-x-2 mb-4">
                            <input type="text" id="auditOrderIdInput" placeholder="ID Ordine" class="flex-1 border border-gray-300 rounded px-3 py-2">
                            <button id="getAuditBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Carica Audit
                            </button>
                        </div>
                        
                        <div id="auditList" class="hidden">
                            <div class="space-y-2">
                                <div id="auditContent" class="text-sm"></div>
                            </div>
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
                const response = await fetch('/api/cqrs-event-sourcing/test');
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
                const response = await fetch('/api/cqrs-event-sourcing/stats');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Rebuild projection
        document.getElementById('rebuildBtn').addEventListener('click', async function() {
            if (!confirm('Questo ricostruirà la projection da zero. Continuare?')) {
                return;
            }
            
            try {
                const response = await fetch('/api/cqrs-event-sourcing/rebuild', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Crea ordine
        document.getElementById('createOrderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/api/cqrs-event-sourcing/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Ordine creato: ' + result.data.order_id);
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
                const response = await fetch(`/api/cqrs-event-sourcing/orders/${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Ordine aggiornato: ' + result.data.order_id);
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
                const response = await fetch(`/api/cqrs-event-sourcing/orders/${orderId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Ordine cancellato: ' + result.data.order_id);
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
            fetch('/api/cqrs-event-sourcing/orders')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('ordersTableBody');
                        tbody.innerHTML = '';
                        
                        data.data.forEach(order => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="px-4 py-2 text-sm text-gray-900">${order.order_id}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">${order.customer_name}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">${order.status}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">€${order.total_amount}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <button onclick="viewOrder('${order.order_id}')" class="text-blue-600 hover:text-blue-900">Vedi</button>
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
            fetch(`/api/cqrs-event-sourcing/orders/${orderId}`)
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

        // Carica eventi
        document.getElementById('getEventsBtn').addEventListener('click', function() {
            const orderId = document.getElementById('eventOrderIdInput').value;
            if (orderId) {
                loadEvents(orderId);
            }
        });

        function loadEvents(orderId) {
            fetch(`/api/cqrs-event-sourcing/events/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '';
                        data.data.forEach(event => {
                            html += `
                                <div class="p-3 border rounded">
                                    <div class="font-semibold">${event.event_type}</div>
                                    <div class="text-sm text-gray-600">${event.created_at}</div>
                                    <div class="text-sm">${JSON.stringify(event.data, null, 2)}</div>
                                </div>
                            `;
                        });
                        document.getElementById('eventsContent').innerHTML = html;
                        document.getElementById('eventsList').classList.remove('hidden');
                    } else {
                        alert('Errore: ' + data.message);
                    }
                })
                .catch(error => console.error('Errore:', error));
        }

        // Carica audit
        document.getElementById('getAuditBtn').addEventListener('click', function() {
            const orderId = document.getElementById('auditOrderIdInput').value;
            if (orderId) {
                loadAudit(orderId);
            }
        });

        function loadAudit(orderId) {
            fetch(`/api/cqrs-event-sourcing/audit/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '';
                        data.data.forEach(audit => {
                            html += `
                                <div class="p-3 border rounded">
                                    <div class="font-semibold">${audit.event_type}</div>
                                    <div class="text-sm text-gray-600">${audit.timestamp}</div>
                                    <div class="text-sm">${JSON.stringify(audit.data, null, 2)}</div>
                                </div>
                            `;
                        });
                        document.getElementById('auditContent').innerHTML = html;
                        document.getElementById('auditList').classList.remove('hidden');
                    } else {
                        alert('Errore: ' + data.message);
                    }
                })
                .catch(error => console.error('Errore:', error));
        }
    </script>
</body>
</html>
