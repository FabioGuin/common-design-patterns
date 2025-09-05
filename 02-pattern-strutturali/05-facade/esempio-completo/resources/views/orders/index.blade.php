<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facade Pattern - Sistema E-Commerce</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        .stats-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
        }
        .form-group.full-width {
            flex: 100%;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        textarea {
            height: 80px;
            resize: vertical;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        button.danger {
            background: #dc3545;
        }
        button.danger:hover {
            background: #c82333;
        }
        button.success {
            background: #28a745;
        }
        button.success:hover {
            background: #218838;
        }
        .products-section {
            margin-bottom: 30px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .product-card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
        }
        .product-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .product-price {
            font-size: 1.5em;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }
        .product-stock {
            color: #666;
            margin-bottom: 15px;
        }
        .product-stock.low {
            color: #dc3545;
        }
        .product-stock.out {
            color: #dc3545;
            font-weight: bold;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .order-form {
            background: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .order-form h3 {
            margin-top: 0;
            color: #495057;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Facade Pattern - Sistema E-Commerce</h1>
        
        <div class="stats-section">
            <h3>Statistiche Sistema</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $systemStats['inventory']['total_products'] ?? 0 }}</div>
                    <div class="stat-label">Prodotti Totali</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $systemStats['inventory']['available_products'] ?? 0 }}</div>
                    <div class="stat-label">Prodotti Disponibili</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $systemStats['payments']['total_payments'] ?? 0 }}</div>
                    <div class="stat-label">Pagamenti Totali</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">€{{ number_format($systemStats['payments']['total_amount'] ?? 0, 2) }}</div>
                    <div class="stat-label">Fatturato Totale</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $systemStats['shipments']['total_shipments'] ?? 0 }}</div>
                    <div class="stat-label">Spedizioni Totali</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $systemStats['notifications']['total_notifications'] ?? 0 }}</div>
                    <div class="stat-label">Notifiche Totali</div>
                </div>
            </div>
        </div>

        <div class="products-section">
            <h3>Prodotti Disponibili</h3>
            <div class="products-grid">
                @foreach($products as $product)
                    <div class="product-card">
                        <div class="product-name">{{ $product['name'] }}</div>
                        <div class="product-price">€{{ number_format($product['price'], 2) }}</div>
                        <div class="product-stock {{ $product['quantity'] == 0 ? 'out' : ($product['quantity'] < 5 ? 'low' : '') }}">
                            Stock: {{ $product['quantity'] }} unità
                        </div>
                        <div style="color: #666; margin-bottom: 15px;">
                            Categoria: {{ $product['category'] }}
                        </div>
                        <button onclick="selectProduct('{{ $product['id'] }}', '{{ $product['name'] }}', {{ $product['price'] }}, {{ $product['quantity'] }})">
                            Seleziona Prodotto
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div id="orderForm" class="order-form hidden">
            <h3>Nuovo Ordine</h3>
            <form id="orderFormElement">
                <div class="form-row">
                    <div class="form-group">
                        <label for="productId">Prodotto:</label>
                        <input type="text" id="productId" readonly>
                    </div>
                    <div class="form-group">
                        <label for="productName">Nome Prodotto:</label>
                        <input type="text" id="productName" readonly>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantità:</label>
                        <input type="number" id="quantity" min="1" value="1" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="customerEmail">Email Cliente:</label>
                        <input type="email" id="customerEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="cardNumber">Numero Carta:</label>
                        <input type="text" id="cardNumber" placeholder="1234567890123456" required>
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV:</label>
                        <input type="text" id="cvv" placeholder="123" required>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="shippingAddress">Indirizzo di Spedizione:</label>
                    <textarea id="shippingAddress" placeholder="Via, Città, CAP, Paese" required></textarea>
                </div>

                <button type="submit">Processa Ordine</button>
                <button type="button" onclick="hideOrderForm()">Annulla</button>
            </form>
        </div>

        <div class="form-section">
            <h3>Operazioni Sistema</h3>
            <button onclick="refreshStats()" class="success">Aggiorna Statistiche</button>
            <button onclick="generateReport()" class="success">Genera Report</button>
            <button onclick="testOrderInfo()">Test Info Ordine</button>
        </div>

        <div id="result"></div>
    </div>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let selectedProduct = null;
        
        function showResult(data, type = 'info') {
            const resultDiv = document.getElementById('result');
            resultDiv.className = `result ${type}`;
            resultDiv.textContent = JSON.stringify(data, null, 2);
        }

        function selectProduct(productId, productName, price, stock) {
            selectedProduct = { id: productId, name: productName, price: price, stock: stock };
            
            document.getElementById('productId').value = productId;
            document.getElementById('productName').value = productName;
            document.getElementById('quantity').max = stock;
            document.getElementById('quantity').value = 1;
            
            document.getElementById('orderForm').classList.remove('hidden');
        }

        function hideOrderForm() {
            document.getElementById('orderForm').classList.add('hidden');
            selectedProduct = null;
        }

        function calculateTotal() {
            if (selectedProduct) {
                const quantity = parseInt(document.getElementById('quantity').value) || 1;
                const total = selectedProduct.price * quantity;
                return total;
            }
            return 0;
        }

        document.getElementById('orderFormElement').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedProduct) {
                showResult({ error: 'Seleziona un prodotto' }, 'error');
                return;
            }

            const orderData = {
                product_id: selectedProduct.id,
                quantity: parseInt(document.getElementById('quantity').value),
                customer_email: document.getElementById('customerEmail').value,
                shipping_address: document.getElementById('shippingAddress').value,
                payment: {
                    card_number: document.getElementById('cardNumber').value,
                    cvv: document.getElementById('cvv').value,
                    amount: calculateTotal()
                }
            };

            fetch('/orders/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
                if (data.success) {
                    hideOrderForm();
                    refreshStats();
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        });

        function refreshStats() {
            fetch('/orders/stats', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(data, 'success');
                    location.reload(); // Ricarica la pagina per mostrare le statistiche aggiornate
                } else {
                    showResult(data, 'error');
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function generateReport() {
            fetch('/orders/report', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function testOrderInfo() {
            const orderId = 'ORD_' + Math.random().toString(36).substr(2, 9);
            
            fetch('/orders/info', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ order_id: orderId })
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }
    </script>
</body>
</html>
