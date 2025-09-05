<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retry Pattern Demo</title>
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
        .services-section {
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
        .service-btn {
            background-color: #28a745;
        }
        .service-btn:hover {
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
        .service-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        .service-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-high { background-color: #e8f5e8; color: #388e3c; }
        .status-medium { background-color: #fff3e0; color: #f57c00; }
        .status-low { background-color: #f5f5f5; color: #666; }
        .status-unknown { background-color: #f5f5f5; color: #666; }
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
        .priority-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .priority-high { background-color: #ffebee; color: #d32f2f; }
        .priority-medium { background-color: #fff3e0; color: #f57c00; }
        .priority-low { background-color: #e8f5e8; color: #388e3c; }
        .strategy-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #e3f2fd;
            color: #1976d2;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Retry Pattern Demo</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Sistema e-commerce con retry automatico e strategie di backoff
        </p>

        <div class="grid">
            <!-- SERVICES -->
            <div class="section services-section">
                <h2>Servizi con Retry</h2>
                
                <h3>Processa Pagamento (Retry Critico)</h3>
                <form id="paymentForm">
                    <div class="form-group">
                        <label>Amount:</label>
                        <input type="number" name="amount" step="0.01" value="99.99" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method:</label>
                        <select name="payment_method">
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Currency:</label>
                        <select name="currency">
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                            <option value="GBP">GBP</option>
                        </select>
                    </div>
                    <button type="submit" class="service-btn">Processa Pagamento</button>
                </form>

                <h3>Verifica Inventario (Retry Importante)</h3>
                <form id="inventoryForm">
                    <div class="form-group">
                        <label>Product ID:</label>
                        <input type="text" name="product_id" value="PROD-001" required>
                    </div>
                    <div class="form-group">
                        <label>Quantity:</label>
                        <input type="number" name="quantity" value="2" required>
                    </div>
                    <button type="submit" class="service-btn">Verifica Disponibilità</button>
                </form>

                <h3>Invia Notifica (Retry Non Critico)</h3>
                <form id="notificationForm">
                    <div class="form-group">
                        <label>Type:</label>
                        <select name="type">
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="push">Push Notification</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To:</label>
                        <input type="text" name="to" value="user@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Subject:</label>
                        <input type="text" name="subject" value="Test Notification">
                    </div>
                    <div class="form-group">
                        <label>Body:</label>
                        <textarea name="body">This is a test notification</textarea>
                    </div>
                    <button type="submit" class="service-btn">Invia Notifica</button>
                </form>
            </div>

            <!-- MONITORING -->
            <div class="section monitoring-section">
                <h2>Monitoraggio Retry</h2>
                
                <h3>Stato Servizi</h3>
                <button onclick="getAllServicesStatus()" class="monitor-btn">Aggiorna Stato</button>
                <div id="servicesStatus"></div>

                <h3>Metriche Retry</h3>
                <button onclick="getMetrics()" class="monitor-btn">Carica Metriche</button>
                <div id="metricsContainer"></div>

                <h3>Tentativi di Retry</h3>
                <button onclick="getRetryAttempts()" class="monitor-btn">Carica Tentativi</button>
                <div id="attemptsContainer"></div>
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
        document.getElementById('paymentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.amount = parseFloat(data.amount);
            await makeRequest('/retry/payment', 'POST', data);
        });

        document.getElementById('inventoryForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.quantity = parseInt(data.quantity);
            await makeRequest('/retry/inventory', 'POST', data);
        });

        document.getElementById('notificationForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            await makeRequest('/retry/notification', 'POST', data);
        });

        async function getAllServicesStatus() {
            const response = await fetch('/retry/status');
            const result = await response.json();
            
            if (result.success) {
                displayServicesStatus(result.services);
            } else {
                showResult(result, true);
            }
        }

        async function getMetrics() {
            const response = await fetch('/retry/metrics');
            const result = await response.json();
            
            if (result.success) {
                displayMetrics(result.metrics);
            } else {
                showResult(result, true);
            }
        }

        async function getRetryAttempts() {
            const response = await fetch('/retry/attempts');
            const result = await response.json();
            
            if (result.success) {
                displayRetryAttempts(result.attempts);
            } else {
                showResult(result, true);
            }
        }

        function displayServicesStatus(services) {
            const container = document.getElementById('servicesStatus');
            container.innerHTML = '';
            
            Object.entries(services).forEach(([serviceName, status]) => {
                const serviceCard = document.createElement('div');
                serviceCard.className = 'service-card';
                
                const priorityClass = `priority-${status.config?.priority || 'unknown'}`;
                const statusClass = `status-${status.config?.priority || 'unknown'}`;
                
                serviceCard.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div>
                            <strong>${serviceName.toUpperCase()}</strong><br>
                            <small>Service: ${status.service_name || 'N/A'}</small>
                        </div>
                        <div>
                            <span class="service-status ${statusClass}">${status.config?.priority || 'UNKNOWN'}</span>
                        </div>
                    </div>
                    <div style="font-size: 12px; color: #666;">
                        <div>Max Attempts: ${status.config?.max_attempts || 0}</div>
                        <div>Base Delay: ${status.config?.base_delay || 0}ms</div>
                        <div>Strategy: <span class="strategy-badge">${status.config?.backoff_strategy || 'UNKNOWN'}</span></div>
                        <div>Success Rate: ${status.success_rate?.toFixed(1) || 0}%</div>
                        <div>Avg Attempts: ${status.avg_attempts_per_operation?.toFixed(2) || 0}</div>
                        <div>Priority: <span class="priority-badge ${priorityClass}">${status.config?.priority || 'UNKNOWN'}</span></div>
                    </div>
                `;
                container.appendChild(serviceCard);
            });
        }

        function displayMetrics(metrics) {
            const container = document.getElementById('metricsContainer');
            container.innerHTML = '<div class="metrics-grid"></div>';
            const grid = container.querySelector('.metrics-grid');
            
            Object.entries(metrics).forEach(([serviceName, metric]) => {
                const metricCard = document.createElement('div');
                metricCard.className = 'metric-card';
                
                metricCard.innerHTML = `
                    <div class="metric-value">${serviceName.toUpperCase()}</div>
                    <div class="metric-label">Service</div>
                    <hr style="margin: 10px 0;">
                    <div class="metric-value">${metric.total_attempts || 0}</div>
                    <div class="metric-label">Total Attempts</div>
                    <div class="metric-value">${metric.successful_attempts || 0}</div>
                    <div class="metric-label">Successful</div>
                    <div class="metric-value">${metric.failed_attempts || 0}</div>
                    <div class="metric-label">Failed</div>
                    <div class="metric-value">${metric.success_rate?.toFixed(1) || 0}%</div>
                    <div class="metric-label">Success Rate</div>
                `;
                grid.appendChild(metricCard);
            });
        }

        function displayRetryAttempts(attempts) {
            const container = document.getElementById('attemptsContainer');
            container.innerHTML = '<div class="metrics-grid"></div>';
            const grid = container.querySelector('.metrics-grid');
            
            Object.entries(attempts).forEach(([serviceName, attempt]) => {
                const attemptCard = document.createElement('div');
                attemptCard.className = 'metric-card';
                
                attemptCard.innerHTML = `
                    <div class="metric-value">${serviceName.toUpperCase()}</div>
                    <div class="metric-label">Service</div>
                    <hr style="margin: 10px 0;">
                    <div class="metric-value">${attempt.total_attempts || 0}</div>
                    <div class="metric-label">Total Attempts</div>
                    <div class="metric-value">${attempt.error_codes?.length || 0}</div>
                    <div class="metric-label">Error Types</div>
                    <div class="metric-value">${attempt.avg_execution_time?.toFixed(3) || 0}s</div>
                    <div class="metric-label">Avg Time</div>
                    <div class="metric-value">${attempt.avg_memory_used || 0}</div>
                    <div class="metric-label">Avg Memory</div>
                `;
                grid.appendChild(attemptCard);
            });
        }

        // Carica stato iniziale
        getAllServicesStatus();
    </script>
</body>
</html>
