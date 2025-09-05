<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Database Anti-pattern - Esempio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .header h1 {
            color: #d32f2f;
            margin: 0;
        }
        .header p {
            color: #666;
            margin: 10px 0 0 0;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .section h2 {
            color: #333;
            margin-top: 0;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .result {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        .result.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .problems {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .problems h3 {
            margin-top: 0;
        }
        .problems ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shared Database Anti-pattern</h1>
            <p>Dimostrazione dei problemi dell'utilizzo di un database condiviso tra multiple servizi</p>
        </div>

        <div class="warning">
            <strong>⚠️ ATTENZIONE:</strong> Questo è un <strong>anti-pattern</strong> da evitare in produzione. 
            Dimostra i problemi dell'architettura monolitica con database condiviso.
        </div>

        <div class="section">
            <h2>Test Operazioni</h2>
            
            <div class="form-group">
                <label for="userName">Nome Utente:</label>
                <input type="text" id="userName" value="Test User">
            </div>
            
            <div class="form-group">
                <label for="userEmail">Email Utente:</label>
                <input type="email" id="userEmail" value="test@example.com">
            </div>
            
            <div class="form-group">
                <label for="productName">Nome Prodotto:</label>
                <input type="text" id="productName" value="Test Product">
            </div>
            
            <div class="form-group">
                <label for="productPrice">Prezzo Prodotto:</label>
                <input type="number" id="productPrice" value="29.99" step="0.01">
            </div>
            
            <div class="form-group">
                <label for="orderTotal">Totale Ordine:</label>
                <input type="number" id="orderTotal" value="59.98" step="0.01">
            </div>
            
            <button class="btn" onclick="createUser()">Crea Utente</button>
            <button class="btn" onclick="createProduct()">Crea Prodotto</button>
            <button class="btn" onclick="createOrder()">Crea Ordine</button>
            <button class="btn" onclick="createPayment()">Crea Pagamento</button>
            <button class="btn" onclick="processPayment()">Processa Pagamento</button>
            
            <div id="result" class="result"></div>
        </div>

        <div class="section">
            <h2>Test Problemi del Pattern</h2>
            
            <button class="btn btn-warning" onclick="simulateDeadlock()">Simula Deadlock</button>
            <button class="btn btn-warning" onclick="simulateComplexTransaction()">Transazione Complessa</button>
            <button class="btn btn-warning" onclick="testScalability()">Test Scalabilità</button>
            
            <div id="problemResult" class="result"></div>
        </div>

        <div class="section">
            <h2>Statistiche Sistema</h2>
            
            <button class="btn" onclick="loadStats()">Carica Statistiche</button>
            <button class="btn" onclick="loadConflictHistory()">Cronologia Conflitti</button>
            <button class="btn" onclick="loadLockHistory()">Cronologia Lock</button>
            
            <div id="statsResult" class="result"></div>
        </div>

        <div class="stats" id="statsContainer">
            <!-- Le statistiche verranno caricate qui -->
        </div>

        <div class="problems">
            <h3>Problemi del Shared Database Anti-pattern:</h3>
            <ul>
                <li><strong>Accoppiamento forte:</strong> Modifiche a un servizio impattano altri</li>
                <li><strong>Scalabilità limitata:</strong> Impossibile scalare servizi indipendentemente</li>
                <li><strong>Conflitti di schema:</strong> Modifiche al database bloccano tutti i servizi</li>
                <li><strong>Transazioni complesse:</strong> Lock su tabelle condivise</li>
                <li><strong>Difficoltà di testing:</strong> Test isolati impossibili</li>
                <li><strong>Single point of failure:</strong> Database condiviso</li>
                <li><strong>Performance degradate:</strong> Lock e conflitti</li>
            </ul>
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Caricamento...</p>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let currentProductId = null;
        let currentOrderId = null;
        let currentPaymentId = null;

        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        function showResult(elementId, message, isSuccess = true) {
            const result = document.getElementById(elementId);
            result.className = `result ${isSuccess ? 'success' : 'error'}`;
            result.innerHTML = message;
            result.style.display = 'block';
        }

        function createUser() {
            showLoading();
            const data = {
                name: document.getElementById('userName').value,
                email: document.getElementById('userEmail').value
            };
            
            fetch('/shared-database/create-user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    currentUserId = data.data.id;
                    showResult('result', `Utente creato: ${data.data.name} (ID: ${data.data.id})`);
                } else {
                    showResult('result', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('result', `Errore: ${error.message}`, false);
            });
        }

        function createProduct() {
            showLoading();
            const data = {
                name: document.getElementById('productName').value,
                description: 'A test product',
                price: parseFloat(document.getElementById('productPrice').value),
                category: 'Electronics',
                inventory: 10
            };
            
            fetch('/shared-database/create-product', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    currentProductId = data.data.id;
                    showResult('result', `Prodotto creato: ${data.data.name} (ID: ${data.data.id})`);
                } else {
                    showResult('result', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('result', `Errore: ${error.message}`, false);
            });
        }

        function createOrder() {
            if (!currentUserId) {
                showResult('result', 'Crea prima un utente', false);
                return;
            }
            
            showLoading();
            const data = {
                user_id: currentUserId,
                total: parseFloat(document.getElementById('orderTotal').value),
                items: [
                    {
                        product_id: currentProductId || 1,
                        quantity: 2,
                        price: parseFloat(document.getElementById('productPrice').value)
                    }
                ]
            };
            
            fetch('/shared-database/create-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    currentOrderId = data.data.id;
                    showResult('result', `Ordine creato: ID ${data.data.id} (Totale: $${data.data.total})`);
                } else {
                    showResult('result', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('result', `Errore: ${error.message}`, false);
            });
        }

        function createPayment() {
            if (!currentOrderId) {
                showResult('result', 'Crea prima un ordine', false);
                return;
            }
            
            showLoading();
            const data = {
                order_id: currentOrderId,
                user_id: currentUserId,
                amount: parseFloat(document.getElementById('orderTotal').value),
                method: 'credit_card'
            };
            
            fetch('/shared-database/create-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    currentPaymentId = data.data.id;
                    showResult('result', `Pagamento creato: ID ${data.data.id} (Importo: $${data.data.amount})`);
                } else {
                    showResult('result', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('result', `Errore: ${error.message}`, false);
            });
        }

        function processPayment() {
            if (!currentPaymentId) {
                showResult('result', 'Crea prima un pagamento', false);
                return;
            }
            
            showLoading();
            const data = {
                payment_id: currentPaymentId
            };
            
            fetch('/shared-database/process-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showResult('result', `Pagamento processato: ${data.data.status} (Transaction ID: ${data.data.transaction_id})`);
                } else {
                    showResult('result', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('result', `Errore: ${error.message}`, false);
            });
        }

        function simulateDeadlock() {
            showLoading();
            fetch('/shared-database/simulate-deadlock', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showResult('problemResult', `Deadlock simulato: ${data.data.error}`, false);
                } else {
                    showResult('problemResult', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('problemResult', `Errore: ${error.message}`, false);
            });
        }

        function simulateComplexTransaction() {
            showLoading();
            fetch('/shared-database/simulate-complex-transaction', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showResult('problemResult', `Transazione complessa eseguita: ${data.data.success ? 'Successo' : 'Fallita'}`);
                } else {
                    showResult('problemResult', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('problemResult', `Errore: ${error.message}`, false);
            });
        }

        function testScalability() {
            showLoading();
            fetch('/shared-database/test-scalability', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    const result = data.data;
                    showResult('problemResult', `Test scalabilità completato: ${result.successful_operations}/${result.total_operations} operazioni riuscite in ${result.duration.toFixed(2)}s`);
                } else {
                    showResult('problemResult', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('problemResult', `Errore: ${error.message}`, false);
            });
        }

        function loadStats() {
            showLoading();
            fetch('/shared-database/stats')
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    displayStats(data.data);
                } else {
                    showResult('statsResult', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('statsResult', `Errore: ${error.message}`, false);
            });
        }

        function loadConflictHistory() {
            showLoading();
            fetch('/shared-database/conflict-history')
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    displayConflictHistory(data.data);
                } else {
                    showResult('statsResult', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('statsResult', `Errore: ${error.message}`, false);
            });
        }

        function loadLockHistory() {
            showLoading();
            fetch('/shared-database/lock-history')
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    displayLockHistory(data.data);
                } else {
                    showResult('statsResult', `Errore: ${data.message}`, false);
                }
            })
            .catch(error => {
                hideLoading();
                showResult('statsResult', `Errore: ${error.message}`, false);
            });
        }

        function displayStats(stats) {
            const container = document.getElementById('statsContainer');
            container.innerHTML = '';
            
            Object.entries(stats).forEach(([service, data]) => {
                const card = document.createElement('div');
                card.className = 'stat-card';
                card.innerHTML = `
                    <h3>${service}</h3>
                    <div class="stat-value">${data.success_rate}%</div>
                    <p>Success Rate</p>
                    <p>Operazioni: ${data.total_operations}</p>
                    <p>Fallite: ${data.failed_operations}</p>
                    <p>Database: ${data.database}</p>
                `;
                container.appendChild(card);
            });
        }

        function displayConflictHistory(conflicts) {
            const result = document.getElementById('statsResult');
            result.className = 'result error';
            result.innerHTML = `
                <h3>Cronologia Conflitti (${conflicts.length})</h3>
                ${conflicts.map(conflict => `
                    <div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <strong>${conflict.table}</strong> - ${conflict.operation}<br>
                        <small>${conflict.timestamp}</small><br>
                        <em>${conflict.error}</em>
                    </div>
                `).join('')}
            `;
            result.style.display = 'block';
        }

        function displayLockHistory(locks) {
            const result = document.getElementById('statsResult');
            result.className = 'result';
            result.innerHTML = `
                <h3>Cronologia Lock (${locks.length})</h3>
                ${locks.map(lock => `
                    <div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <strong>${lock.table}</strong> - ${lock.operation}<br>
                        <small>${lock.acquired_at}</small><br>
                        <em>Service: ${lock.service}</em>
                    </div>
                `).join('')}
            `;
            result.style.display = 'block';
        }
    </script>
</body>
</html>
