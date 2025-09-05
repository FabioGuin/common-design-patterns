<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CQRS Pattern Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
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
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
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
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CQRS Pattern Demo</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Dimostrazione della separazione Command/Query in un sistema e-commerce
        </p>

        <div class="grid">
            <!-- COMMAND SIDE -->
            <div class="section command-section">
                <h2>Command Side (Scrittura)</h2>
                
                <h3>Creare Prodotto</h3>
                <form id="createProductForm">
                    <div class="form-group">
                        <label>Nome:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Descrizione:</label>
                        <textarea name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Prezzo:</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Stock:</label>
                        <input type="number" name="stock" required>
                    </div>
                    <div class="form-group">
                        <label>Categoria:</label>
                        <input type="text" name="category" required>
                    </div>
                    <button type="submit" class="command-btn">Crea Prodotto</button>
                </form>

                <h3>Aggiornare Prodotto</h3>
                <form id="updateProductForm">
                    <div class="form-group">
                        <label>ID Prodotto:</label>
                        <input type="number" name="id" required>
                    </div>
                    <div class="form-group">
                        <label>Nuovo Nome:</label>
                        <input type="text" name="name">
                    </div>
                    <div class="form-group">
                        <label>Nuovo Prezzo:</label>
                        <input type="number" name="price" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Nuovo Stock:</label>
                        <input type="number" name="stock">
                    </div>
                    <button type="submit" class="command-btn">Aggiorna Prodotto</button>
                </form>

                <h3>Creare Ordine</h3>
                <form id="createOrderForm">
                    <div class="form-group">
                        <label>User ID:</label>
                        <input type="number" name="user_id" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Items (JSON):</label>
                        <textarea name="items" placeholder='[{"product_id": 1, "quantity": 2}]' required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Indirizzo Spedizione:</label>
                        <input type="text" name="shipping_address" required>
                    </div>
                    <div class="form-group">
                        <label>Indirizzo Fatturazione:</label>
                        <input type="text" name="billing_address" required>
                    </div>
                    <button type="submit" class="command-btn">Crea Ordine</button>
                </form>
            </div>

            <!-- QUERY SIDE -->
            <div class="section query-section">
                <h2>Query Side (Lettura)</h2>
                
                <h3>Cerca Prodotti</h3>
                <form id="searchProductsForm">
                    <div class="form-group">
                        <label>Ricerca:</label>
                        <input type="text" name="search" placeholder="Nome o descrizione">
                    </div>
                    <div class="form-group">
                        <label>Categoria:</label>
                        <input type="text" name="category" placeholder="Categoria">
                    </div>
                    <div class="form-group">
                        <label>Prezzo Min:</label>
                        <input type="number" name="min_price" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Prezzo Max:</label>
                        <input type="number" name="max_price" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="available" value="1"> Solo disponibili
                        </label>
                    </div>
                    <button type="submit" class="query-btn">Cerca Prodotti</button>
                </form>

                <h3>Ottieni Prodotto</h3>
                <form id="getProductForm">
                    <div class="form-group">
                        <label>ID Prodotto:</label>
                        <input type="number" name="id" required>
                    </div>
                    <button type="submit" class="query-btn">Ottieni Prodotto</button>
                </form>

                <h3>Statistiche</h3>
                <button onclick="getProductStats()" class="query-btn">Statistiche Prodotti</button>
                <button onclick="getOrderStats()" class="query-btn">Statistiche Ordini</button>

                <h3>Ordini Utente</h3>
                <form id="getOrdersForm">
                    <div class="form-group">
                        <label>User ID:</label>
                        <input type="number" name="user_id" value="1" required>
                    </div>
                    <button type="submit" class="query-btn">Ottieni Ordini</button>
                </form>
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
        document.getElementById('createProductForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.price = parseFloat(data.price);
            data.stock = parseInt(data.stock);
            await makeRequest('/cqrs/products', 'POST', data);
        });

        document.getElementById('updateProductForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const id = data.id;
            delete data.id;
            
            // Rimuovi campi vuoti
            Object.keys(data).forEach(key => {
                if (data[key] === '') delete data[key];
            });
            
            if (data.price) data.price = parseFloat(data.price);
            if (data.stock) data.stock = parseInt(data.stock);
            
            await makeRequest(`/cqrs/products/${id}`, 'PUT', data);
        });

        document.getElementById('createOrderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.user_id = parseInt(data.user_id);
            data.items = JSON.parse(data.items);
            await makeRequest('/cqrs/orders', 'POST', data);
        });

        document.getElementById('searchProductsForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const params = new URLSearchParams();
            formData.forEach((value, key) => {
                if (value) params.append(key, value);
            });
            await makeRequest(`/cqrs/products/search?${params.toString()}`);
        });

        document.getElementById('getProductForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const id = formData.get('id');
            await makeRequest(`/cqrs/products/${id}`);
        });

        document.getElementById('getOrdersForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const userId = formData.get('user_id');
            await makeRequest(`/cqrs/orders/user/${userId}`);
        });

        async function getProductStats() {
            await makeRequest('/cqrs/products/stats');
        }

        async function getOrderStats() {
            await makeRequest('/cqrs/orders/stats');
        }
    </script>
</body>
</html>
