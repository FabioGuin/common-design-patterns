<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saga Pattern Demo</title>
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
        .saga-section {
            background-color: #e8f5e8;
            border-color: #4caf50;
        }
        .monitoring-section {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .services-section {
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
        .saga-btn {
            background-color: #28a745;
        }
        .saga-btn:hover {
            background-color: #218838;
        }
        .monitor-btn {
            background-color: #17a2b8;
        }
        .monitor-btn:hover {
            background-color: #138496;
        }
        .service-btn {
            background-color: #ff9800;
        }
        .service-btn:hover {
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
        .saga-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        .saga-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-running { background-color: #e3f2fd; color: #1976d2; }
        .status-completed { background-color: #e8f5e8; color: #388e3c; }
        .status-compensated { background-color: #ffebee; color: #d32f2f; }
        .status-compensating { background-color: #fff3e0; color: #f57c00; }
        .step-item {
            padding: 8px;
            margin: 4px 0;
            border-left: 3px solid #ddd;
            background: #f5f5f5;
        }
        .step-completed { border-left-color: #4caf50; }
        .step-compensated { border-left-color: #ff9800; }
        .step-failed { border-left-color: #f44336; }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Saga Pattern Demo</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Sistema di gestione ordini con transazioni distribuite e compensazioni automatiche
        </p>

        <div class="grid">
            <!-- SAGA EXECUTION -->
            <div class="section saga-section">
                <h2>Esegui Saga Ordine</h2>
                
                <form id="executeSagaForm">
                    <div class="form-group">
                        <label>Order ID:</label>
                        <input type="text" name="order_id" value="ORD-001" required>
                    </div>
                    <div class="form-group">
                        <label>Customer ID:</label>
                        <input type="text" name="customer_id" value="CUST-001" required>
                    </div>
                    <div class="form-group">
                        <label>Customer Email:</label>
                        <input type="email" name="customer_email" value="customer@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Product ID:</label>
                        <input type="text" name="product_id" value="PROD-001" required>
                    </div>
                    <div class="form-group">
                        <label>Quantity:</label>
                        <input type="number" name="quantity" value="2" required>
                    </div>
                    <div class="form-group">
                        <label>Total Amount:</label>
                        <input type="number" name="total_amount" step="0.01" value="199.99" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method:</label>
                        <select name="payment_method">
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <button type="submit" class="saga-btn">Esegui Saga Ordine</button>
                </form>
            </div>

            <!-- MONITORING -->
            <div class="section monitoring-section">
                <h2>Monitoraggio Saga</h2>
                
                <h3>Stato Saga</h3>
                <form id="getSagaStatusForm">
                    <div class="form-group">
                        <label>Saga ID:</label>
                        <input type="text" name="saga_id" required>
                    </div>
                    <button type="submit" class="monitor-btn">Ottieni Stato Saga</button>
                </form>

                <h3>Statistiche</h3>
                <button onclick="getSagaStats()" class="monitor-btn">Statistiche Saga</button>
                <button onclick="getAllSagas()" class="monitor-btn">Tutte le Saga</button>
            </div>
        </div>

        <!-- SERVICES MONITORING -->
        <div class="section services-section full-width">
            <h2>Monitoraggio Servizi</h2>
            
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button onclick="getInventoryReservations()" class="service-btn">Riserve Inventario</button>
                <button onclick="getPayments()" class="service-btn">Pagamenti</button>
                <button onclick="getNotifications()" class="service-btn">Notifiche</button>
                <button onclick="getOrders()" class="service-btn">Ordini</button>
            </div>
        </div>

        <div id="result" class="result" style="display: none;"></div>
        <div id="sagasList" style="margin-top: 20px;"></div>
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

        // Form handlers
        document.getElementById('executeSagaForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.quantity = parseInt(data.quantity);
            data.total_amount = parseFloat(data.total_amount);
            await makeRequest('/saga/execute', 'POST', data);
        });

        document.getElementById('getSagaStatusForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const sagaId = formData.get('saga_id');
            await makeRequest(`/saga/status/${sagaId}`);
        });

        async function getSagaStats() {
            await makeRequest('/saga/stats');
        }

        async function getAllSagas() {
            const response = await fetch('/saga/all');
            const result = await response.json();
            
            if (result.success) {
                displaySagas(result.sagas);
            } else {
                showResult(result, true);
            }
        }

        async function getInventoryReservations() {
            await makeRequest('/saga/inventory');
        }

        async function getPayments() {
            await makeRequest('/saga/payments');
        }

        async function getNotifications() {
            await makeRequest('/saga/notifications');
        }

        async function getOrders() {
            await makeRequest('/saga/orders');
        }

        function displaySagas(sagas) {
            const sagasList = document.getElementById('sagasList');
            sagasList.innerHTML = '<h3>Saga Attive</h3>';
            
            if (sagas.length === 0) {
                sagasList.innerHTML += '<p>Nessuna saga trovata</p>';
                return;
            }
            
            sagas.forEach(saga => {
                const sagaCard = document.createElement('div');
                sagaCard.className = 'saga-card';
                
                const stepsHtml = saga.steps ? saga.steps.map(step => 
                    `<div class="step-item step-${step.status}">
                        <strong>${step.step_name}</strong> - ${step.status} 
                        <small>(${new Date(step.executed_at).toLocaleString()})</small>
                    </div>`
                ).join('') : '';
                
                sagaCard.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div>
                            <strong>Saga ID:</strong> ${saga.id}<br>
                            <strong>Tipo:</strong> ${saga.type}<br>
                            <strong>Creata:</strong> ${new Date(saga.created_at).toLocaleString()}
                        </div>
                        <div>
                            <span class="saga-status status-${saga.status}">${saga.status}</span>
                        </div>
                    </div>
                    <div>
                        <strong>Steps:</strong>
                        ${stepsHtml || '<div class="step-item">Nessun step eseguito</div>'}
                    </div>
                `;
                sagasList.appendChild(sagaCard);
            });
        }
    </script>
</body>
</html>
