<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caching Aside Pattern Demo</title>
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
        .cache-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .cache-hit { background-color: #d4edda; color: #155724; }
        .cache-miss { background-color: #fff3cd; color: #856404; }
        .cache-error { background-color: #f8d7da; color: #721c24; }
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
        .strategy-read-through { background-color: #e8f5e8; color: #388e3c; }
        .strategy-write-through { background-color: #fff3e0; color: #f57c00; }
        .strategy-write-behind { background-color: #e3f2fd; color: #1976d2; }
        .strategy-refresh-ahead { background-color: #f3e5f5; color: #7b1fa2; }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Caching Aside Pattern Demo</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Sistema e-commerce con caching intelligente per ottimizzare performance
        </p>

        <div class="grid">
            <!-- ENTITIES -->
            <div class="section entities-section">
                <h2>Entità con Cache</h2>
                
                <h3>Prodotti (Read-Through Cache)</h3>
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
                <button onclick="getAllProducts()" class="entity-btn">Tutti i Prodotti</button>
                <button onclick="preloadProducts()" class="entity-btn">Preload Prodotti</button>

                <h3>Utenti (Write-Through Cache)</h3>
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
                    <div class="form-group">
                        <label>Status:</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <button type="submit" class="entity-btn">Crea Utente</button>
                </form>
                <button onclick="getAllUsers()" class="entity-btn">Tutti gli Utenti</button>
                <button onclick="preloadUsers()" class="entity-btn">Preload Utenti</button>

                <h3>Ordini (Write-Behind Cache)</h3>
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
                    <div class="form-group">
                        <label>Status:</label>
                        <select name="status">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="entity-btn">Crea Ordine</button>
                </form>
                <button onclick="getAllOrders()" class="entity-btn">Tutti gli Ordini</button>
                <button onclick="preloadOrders()" class="entity-btn">Preload Ordini</button>
            </div>

            <!-- MONITORING -->
            <div class="section monitoring-section">
                <h2>Monitoraggio Cache</h2>
                
                <h3>Statistiche Cache</h3>
                <button onclick="getAllCacheStats()" class="monitor-btn">Aggiorna Statistiche</button>
                <div id="cacheStats"></div>

                <h3>Test Cache</h3>
                <div class="form-group">
                    <label>Entity:</label>
                    <select id="testEntity">
                        <option value="products">Products</option>
                        <option value="users">Users</option>
                        <option value="orders">Orders</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ID:</label>
                    <input type="number" id="testId" value="1">
                </div>
                <button onclick="testCache()" class="monitor-btn">Test Cache</button>
                <button onclick="refreshCache()" class="monitor-btn">Refresh Cache</button>
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
        document.getElementById('productForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.price = parseFloat(data.price);
            await makeRequest('/caching-aside/products', 'POST', data);
        });

        document.getElementById('userForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            await makeRequest('/caching-aside/users', 'POST', data);
        });

        document.getElementById('orderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.user_id = parseInt(data.user_id);
            data.product_id = parseInt(data.product_id);
            data.quantity = parseInt(data.quantity);
            data.total_amount = parseFloat(data.total_amount);
            await makeRequest('/caching-aside/orders', 'POST', data);
        });

        async function getAllProducts() {
            await makeRequest('/caching-aside/products');
        }

        async function getAllUsers() {
            await makeRequest('/caching-aside/users');
        }

        async function getAllOrders() {
            await makeRequest('/caching-aside/orders');
        }

        async function preloadProducts() {
            await makeRequest('/caching-aside/products/preload', 'POST');
        }

        async function preloadUsers() {
            await makeRequest('/caching-aside/users/preload', 'POST');
        }

        async function preloadOrders() {
            await makeRequest('/caching-aside/orders/preload', 'POST');
        }

        async function getAllCacheStats() {
            const response = await fetch('/caching-aside/stats');
            const result = await response.json();
            
            if (result.success) {
                displayCacheStats(result.stats);
            } else {
                showResult(result, true);
            }
        }

        async function testCache() {
            const entity = document.getElementById('testEntity').value;
            const id = document.getElementById('testId').value;
            
            const url = `/caching-aside/${entity}/${id}`;
            await makeRequest(url);
        }

        async function refreshCache() {
            const entity = document.getElementById('testEntity').value;
            const id = document.getElementById('testId').value;
            
            const url = `/caching-aside/${entity}/${id}/refresh`;
            await makeRequest(url, 'POST');
        }

        function displayCacheStats(stats) {
            const container = document.getElementById('cacheStats');
            container.innerHTML = '<div class="metrics-grid"></div>';
            const grid = container.querySelector('.metrics-grid');
            
            Object.entries(stats).forEach(([entity, stat]) => {
                const metricCard = document.createElement('div');
                metricCard.className = 'metric-card';
                
                const strategyClass = `strategy-${entity === 'products' ? 'read-through' : entity === 'users' ? 'write-through' : 'write-behind'}`;
                const strategyName = entity === 'products' ? 'READ-THROUGH' : entity === 'users' ? 'WRITE-THROUGH' : 'WRITE-BEHIND';
                
                metricCard.innerHTML = `
                    <div class="metric-value">${entity.toUpperCase()}</div>
                    <div class="metric-label">Entity</div>
                    <div style="margin: 10px 0;">
                        <span class="strategy-badge ${strategyClass}">${strategyName}</span>
                    </div>
                    <hr style="margin: 10px 0;">
                    <div class="metric-value">${stat.total_operations || 0}</div>
                    <div class="metric-label">Total Operations</div>
                    <div class="metric-value">${stat.cache_hits || 0}</div>
                    <div class="metric-label">Cache Hits</div>
                    <div class="metric-value">${stat.cache_misses || 0}</div>
                    <div class="metric-label">Cache Misses</div>
                    <div class="metric-value">${stat.hit_ratio?.toFixed(1) || 0}%</div>
                    <div class="metric-label">Hit Ratio</div>
                    <div class="metric-value">${stat.avg_execution_time?.toFixed(3) || 0}s</div>
                    <div class="metric-label">Avg Time</div>
                `;
                grid.appendChild(metricCard);
            });
        }

        // Carica statistiche iniziali
        getAllCacheStats();
    </script>
</body>
</html>
