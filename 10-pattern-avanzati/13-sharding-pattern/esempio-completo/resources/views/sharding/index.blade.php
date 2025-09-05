<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sharding Pattern Demo</title>
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
        .entities-section {
            background-color: #e8f5e8;
            border-color: #4caf50;
        }
        .monitoring-section {
            background-color: #e3f2fd;
            border-color: #2196f3;
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
        .entity-btn {
            background-color: #28a745;
        }
        .entity-btn:hover {
            background-color: #218838;
        }
        .monitor-btn {
            background-color: #17a2b8;
        }
        .monitor-btn:hover {
            background-color: #138496;
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
        .entity-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        .shard-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .metric-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .metric-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .strategy-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .strategy-key { background-color: #e8f5e8; color: #388e3c; }
        .strategy-range { background-color: #fff3e0; color: #f57c00; }
        .strategy-hash { background-color: #e3f2fd; color: #1976d2; }
        .strategy-directory { background-color: #f3e5f5; color: #7b1fa2; }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sharding Pattern Demo</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Sistema e-commerce con sharding per distribuzione dati e scalabilità
        </p>

        <div class="grid">
            <!-- ENTITIES -->
            <div class="section entities-section">
                <h2>Entità con Sharding</h2>
                
                <h3>Crea Utente (Key-based Sharding)</h3>
                <form id="userForm">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" value="John Doe" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="john@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" value="password" required>
                    </div>
                    <button type="submit" class="entity-btn">Crea Utente</button>
                </form>

                <h3>Crea Prodotto (Range-based Sharding)</h3>
                <form id="productForm">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" value="Test Product" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description">This is a test product</textarea>
                    </div>
                    <div class="form-group">
                        <label>Price:</label>
                        <input type="number" name="price" step="0.01" value="99.99" required>
                    </div>
                    <div class="form-group">
                        <label>Category:</label>
                        <select name="category">
                            <option value="Electronics">Electronics</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Books">Books</option>
                            <option value="Home">Home</option>
                            <option value="Sports">Sports</option>
                        </select>
                    </div>
                    <button type="submit" class="entity-btn">Crea Prodotto</button>
                </form>

                <h3>Crea Ordine (Hash-based Sharding)</h3>
                <form id="orderForm">
                    <div class="form-group">
                        <label>User ID:</label>
                        <input type="number" name="user_id" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Product ID:</label>
                        <input type="number" name="product_id" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Quantity:</label>
                        <input type="number" name="quantity" value="2" required>
                    </div>
                    <div class="form-group">
                        <label>Total Amount:</label>
                        <input type="number" name="total_amount" step="0.01" value="199.98" required>
                    </div>
                    <button type="submit" class="entity-btn">Crea Ordine</button>
                </form>
            </div>

            <!-- MONITORING -->
            <div class="section monitoring-section">
                <h2>Monitoraggio Sharding</h2>
                
                <h3>Stato Shard</h3>
                <button onclick="getAllShardingStatus()" class="monitor-btn">Aggiorna Stato</button>
                <div id="shardingStatus"></div>

                <h3>Metriche Sharding</h3>
                <button onclick="getMetrics()" class="monitor-btn">Carica Metriche</button>
                <div id="metricsContainer"></div>

                <h3>Lista Entità</h3>
                <button onclick="getAllUsers()" class="monitor-btn">Tutti gli Utenti</button>
                <button onclick="getAllProducts()" class="monitor-btn">Tutti i Prodotti</button>
                <button onclick="getAllOrders()" class="monitor-btn">Tutti gli Ordini</button>
            </div>
        </div>

        <div id="result" class="result" style="display: none;"></div>
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
        document.getElementById('userForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            await makeRequest('/sharding/users', 'POST', data);
        });

        document.getElementById('productForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.price = parseFloat(data.price);
            await makeRequest('/sharding/products', 'POST', data);
        });

        document.getElementById('orderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.user_id = parseInt(data.user_id);
            data.product_id = parseInt(data.product_id);
            data.quantity = parseInt(data.quantity);
            data.total_amount = parseFloat(data.total_amount);
            await makeRequest('/sharding/orders', 'POST', data);
        });

        async function getAllShardingStatus() {
            const response = await fetch('/sharding/status');
            const result = await response.json();
            
            if (result.success) {
                displayShardingStatus(result.status);
            } else {
                showResult(result, true);
            }
        }

        async function getMetrics() {
            const response = await fetch('/sharding/metrics');
            const result = await response.json();
            
            if (result.success) {
                displayMetrics(result.metrics);
            } else {
                showResult(result, true);
            }
        }

        async function getAllUsers() {
            const response = await fetch('/sharding/users');
            const result = await response.json();
            
            if (result.success) {
                showResult(result);
            } else {
                showResult(result, true);
            }
        }

        async function getAllProducts() {
            const response = await fetch('/sharding/products');
            const result = await response.json();
            
            if (result.success) {
                showResult(result);
            } else {
                showResult(result, true);
            }
        }

        async function getAllOrders() {
            const response = await fetch('/sharding/orders');
            const result = await response.json();
            
            if (result.success) {
                showResult(result);
            } else {
                showResult(result, true);
            }
        }

        function displayShardingStatus(status) {
            const container = document.getElementById('shardingStatus');
            container.innerHTML = '';
            
            Object.entries(status).forEach(([entity, entityStatus]) => {
                const entityCard = document.createElement('div');
                entityCard.className = 'entity-card';
                
                entityCard.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div>
                            <strong>${entity.toUpperCase()}</strong><br>
                            <small>Entity: ${entity}</small>
                        </div>
                        <div>
                            <span class="strategy-badge strategy-${entity === 'users' ? 'key' : entity === 'products' ? 'range' : entity === 'orders' ? 'hash' : 'directory'}">${entity === 'users' ? 'KEY' : entity === 'products' ? 'RANGE' : entity === 'orders' ? 'HASH' : 'DIRECTORY'}</span>
                        </div>
                    </div>
                    <div style="font-size: 12px; color: #666;">
                        ${Object.entries(entityStatus).map(([shardName, shardStatus]) => `
                            <div style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                                <div><strong>${shardName.toUpperCase()}</strong></div>
                                <div>Status: <span style="color: ${shardStatus.status === 'connected' ? 'green' : 'red'}">${shardStatus.status.toUpperCase()}</span></div>
                                <div>Host: ${shardStatus.host}</div>
                                <div>Database: ${shardStatus.database}</div>
                                <div>Records: ${shardStatus.record_count || 0}</div>
                                ${shardStatus.error ? `<div style="color: red;">Error: ${shardStatus.error}</div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                `;
                container.appendChild(entityCard);
            });
        }

        function displayMetrics(metrics) {
            const container = document.getElementById('metricsContainer');
            container.innerHTML = '<div class="metrics-grid"></div>';
            const grid = container.querySelector('.metrics-grid');
            
            Object.entries(metrics).forEach(([entity, metric]) => {
                const metricCard = document.createElement('div');
                metricCard.className = 'metric-card';
                
                metricCard.innerHTML = `
                    <div class="metric-value">${entity.toUpperCase()}</div>
                    <div class="metric-label">Entity</div>
                    <hr style="margin: 10px 0;">
                    <div class="metric-value">${metric.total_queries || 0}</div>
                    <div class="metric-label">Total Queries</div>
                    <div class="metric-value">${metric.successful_queries || 0}</div>
                    <div class="metric-label">Successful</div>
                    <div class="metric-value">${metric.failed_queries || 0}</div>
                    <div class="metric-label">Failed</div>
                    <div class="metric-value">${metric.success_rate?.toFixed(1) || 0}%</div>
                    <div class="metric-label">Success Rate</div>
                `;
                grid.appendChild(metricCard);
            });
        }

        // Carica stato iniziale
        getAllShardingStatus();
    </script>
</body>
</html>
