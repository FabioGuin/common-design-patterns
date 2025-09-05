<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Sourcing Pattern Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .section h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        .command-section {
            background-color: #e8f5e8;
            border-color: #4caf50;
        }
        .query-section {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .events-section {
            background-color: #fff3e0;
            border-color: #ff9800;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .command-btn {
            background-color: #28a745;
        }
        .command-btn:hover {
            background-color: #218838;
        }
        .query-btn {
            background-color: #17a2b8;
        }
        .query-btn:hover {
            background-color: #138496;
        }
        .events-btn {
            background-color: #ff9800;
        }
        .events-btn:hover {
            background-color: #f57c00;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        .order-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-created { background-color: #e3f2fd; color: #1976d2; }
        .status-paid { background-color: #e8f5e8; color: #388e3c; }
        .status-shipped { background-color: #fff3e0; color: #f57c00; }
        .status-delivered { background-color: #e8f5e8; color: #2e7d32; }
        .status-cancelled { background-color: #ffebee; color: #d32f2f; }
        .status-refunded { background-color: #f3e5f5; color: #7b1fa2; }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Event Sourcing Pattern Demo</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Sistema di gestione ordini con tracciamento completo degli eventi
        </p>

        <div class="grid">
            <!-- COMMAND SIDE -->
            <div class="section command-section">
                <h2>Command Side (Azioni)</h2>
                
                <h3>Creare Ordine</h3>
                <form id="createOrderForm">
                    <div class="form-group">
                        <label>Customer ID:</label>
                        <input type="text" name="customer_id" value="CUST-001" required>
                    </div>
                    <div class="form-group">
                        <label>Items (JSON):</label>
                        <textarea name="items" placeholder='[{"product": "Laptop", "quantity": 1, "price": 999.99}]' required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Total Amount:</label>
                        <input type="number" name="total_amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Shipping Address:</label>
                        <input type="text" name="shipping_address" required>
                    </div>
                    <button type="submit" class="command-btn">Crea Ordine</button>
                </form>

                <h3>Gestisci Ordine</h3>
                <form id="manageOrderForm">
                    <div class="form-group">
                        <label>Order ID:</label>
                        <input type="text" name="order_id" required>
                    </div>
                    <div class="form-group">
                        <label>Azione:</label>
                        <select name="action" id="actionSelect">
                            <option value="pay">Paga Ordine</option>
                            <option value="ship">Spedisci Ordine</option>
                            <option value="deliver">Consegna Ordine</option>
                            <option value="cancel">Cancella Ordine</option>
                            <option value="refund">Rimborsa Ordine</option>
                        </select>
                    </div>
                    
                    <div id="payFields" class="action-fields">
                        <div class="form-group">
                            <label>Payment Method:</label>
                            <input type="text" name="payment_method" placeholder="Credit Card">
                        </div>
                        <div class="form-group">
                            <label>Transaction ID:</label>
                            <input type="text" name="transaction_id" placeholder="TXN-123456">
                        </div>
                    </div>
                    
                    <div id="shipFields" class="action-fields" style="display: none;">
                        <div class="form-group">
                            <label>Tracking Number:</label>
                            <input type="text" name="tracking_number" placeholder="TRK-123456">
                        </div>
                        <div class="form-group">
                            <label>Carrier:</label>
                            <input type="text" name="carrier" placeholder="DHL">
                        </div>
                    </div>
                    
                    <div id="deliverFields" class="action-fields" style="display: none;">
                        <div class="form-group">
                            <label>Delivery Confirmation:</label>
                            <input type="text" name="delivery_confirmation" placeholder="DEL-123456">
                        </div>
                    </div>
                    
                    <div id="cancelFields" class="action-fields" style="display: none;">
                        <div class="form-group">
                            <label>Reason:</label>
                            <input type="text" name="reason" placeholder="Customer request">
                        </div>
                    </div>
                    
                    <div id="refundFields" class="action-fields" style="display: none;">
                        <div class="form-group">
                            <label>Refund Amount:</label>
                            <input type="number" name="refund_amount" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Reason:</label>
                            <input type="text" name="reason" placeholder="Defective product">
                        </div>
                    </div>
                    
                    <button type="submit" class="command-btn">Esegui Azione</button>
                </form>
            </div>

            <!-- QUERY SIDE -->
            <div class="section query-section">
                <h2>Query Side (Visualizzazione)</h2>
                
                <h3>Visualizza Ordini</h3>
                <button onclick="getAllOrders()" class="query-btn">Carica Tutti gli Ordini</button>
                
                <h3>Dettagli Ordine</h3>
                <form id="getOrderForm">
                    <div class="form-group">
                        <label>Order ID:</label>
                        <input type="text" name="order_id" required>
                    </div>
                    <button type="submit" class="query-btn">Ottieni Dettagli</button>
                </form>

                <h3>Statistiche</h3>
                <button onclick="getOrderStats()" class="query-btn">Statistiche Ordini</button>
            </div>
        </div>

        <!-- EVENTS SIDE -->
        <div class="section events-section full-width">
            <h2>Event Store (Audit Trail)</h2>
            
            <h3>Eventi Ordine</h3>
            <form id="getOrderEventsForm">
                <div class="form-group">
                    <label>Order ID:</label>
                    <input type="text" name="order_id" required>
                </div>
                <button type="submit" class="events-btn">Visualizza Eventi Ordine</button>
            </form>
            
            <h3>Tutti gli Eventi</h3>
            <button onclick="getAllEvents()" class="events-btn">Carica Tutti gli Eventi</button>
        </div>

        <div id="result" class="result" style="display: none;"></div>
        <div id="ordersList" style="margin-top: 20px;"></div>
    </div>

    <script>
        // Helper per mostrare risultati
        function showResult(data, isError = false) {
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.className = 'result ' + (isError ? 'error' : 'success');
            resultDiv.textContent = JSON.stringify(data, null, 2);
        }

        // Helper per fare richieste
        async function makeRequest(url, method = 'GET', data = null) {
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                };

                if (data) {
                    options.body = JSON.stringify(data);
                }

                const response = await fetch(url, options);
                const result = await response.json();
                
                if (response.ok) {
                    showResult(result);
                } else {
                    showResult(result, true);
                }
            } catch (error) {
                showResult({ error: error.message }, true);
            }
        }

        // Gestione campi dinamici
        document.getElementById('actionSelect').addEventListener('change', function() {
            const action = this.value;
            const allFields = document.querySelectorAll('.action-fields');
            allFields.forEach(field => field.style.display = 'none');
            
            const targetField = document.getElementById(action + 'Fields');
            if (targetField) {
                targetField.style.display = 'block';
            }
        });

        // Form handlers
        document.getElementById('createOrderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.items = JSON.parse(data.items);
            data.total_amount = parseFloat(data.total_amount);
            await makeRequest('/event-sourcing/orders', 'POST', data);
        });

        document.getElementById('manageOrderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const orderId = data.order_id;
            const action = data.action;
            delete data.order_id;
            delete data.action;
            
            // Rimuovi campi vuoti
            Object.keys(data).forEach(key => {
                if (data[key] === '') delete data[key];
            });
            
            if (data.refund_amount) data.refund_amount = parseFloat(data.refund_amount);
            
            await makeRequest(`/event-sourcing/orders/${orderId}/${action}`, 'POST', data);
        });

        document.getElementById('getOrderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const orderId = formData.get('order_id');
            await makeRequest(`/event-sourcing/orders/${orderId}`);
        });

        document.getElementById('getOrderEventsForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const orderId = formData.get('order_id');
            await makeRequest(`/event-sourcing/orders/${orderId}/events`);
        });

        async function getAllOrders() {
            const response = await fetch('/event-sourcing/orders');
            const result = await response.json();
            
            if (result.success) {
                displayOrders(result.orders);
            } else {
                showResult(result, true);
            }
        }

        async function getOrderStats() {
            // Implementa statistiche se necessario
            showResult({ message: 'Statistiche non ancora implementate' });
        }

        async function getAllEvents() {
            await makeRequest('/event-sourcing/events');
        }

        function displayOrders(orders) {
            const ordersList = document.getElementById('ordersList');
            ordersList.innerHTML = '<h3>Ordini Attuali</h3>';
            
            if (orders.length === 0) {
                ordersList.innerHTML += '<p>Nessun ordine trovato</p>';
                return;
            }
            
            orders.forEach(order => {
                const orderCard = document.createElement('div');
                orderCard.className = 'order-card';
                orderCard.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Order ID:</strong> ${order.order_id}<br>
                            <strong>Customer:</strong> ${order.customer_id}<br>
                            <strong>Total:</strong> €${order.total_amount}<br>
                            <strong>Version:</strong> ${order.version}
                        </div>
                        <div>
                            <span class="order-status status-${order.status}">${order.status}</span>
                        </div>
                    </div>
                `;
                ordersList.appendChild(orderCard);
            });
        }
    </script>
</body>
</html>
