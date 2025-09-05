<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write-Through Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Write-Through Pattern - Esempio
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test del Pattern -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test del Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern Write-Through per verificare la coerenza tra cache e database.
                    </p>
                    
                    <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">
                        Esegui Test Completo
                    </button>
                    
                    <button id="performanceBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-4 ml-2">
                        Test Performance
                    </button>
                    
                    <div id="result" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="resultContent" class="text-sm overflow-auto"></pre>
                    </div>
                </div>

                <!-- Gestione Prodotti -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Gestione Prodotti</h2>
                    
                    <form id="productForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome</label>
                            <input type="text" name="name" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descrizione</label>
                            <textarea name="description" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prezzo</label>
                                <input type="number" name="price" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Stock</label>
                                <input type="number" name="stock" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                            Crea Prodotto (Write-Through)
                        </button>
                    </form>
                    
                    <div id="productResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="productResultContent" class="text-sm overflow-auto"></pre>
                    </div>
                </div>
            </div>

            <!-- Lista Prodotti -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Prodotti Esistenti</h2>
                
                <button id="loadProductsBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 mb-4">
                    Carica Prodotti
                </button>
                
                <div id="productsList" class="hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prezzo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Azioni</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test del pattern
        document.getElementById('testBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/write-through/test');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Test di performance
        document.getElementById('performanceBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/write-through/performance');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Creazione prodotto
        document.getElementById('productForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/api/write-through/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                document.getElementById('productResultContent').textContent = JSON.stringify(result, null, 2);
                document.getElementById('productResult').classList.remove('hidden');
                
                // Ricarica la lista prodotti
                loadProducts();
                
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('productResultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('productResult').classList.remove('hidden');
            }
        });

        // Carica prodotti
        document.getElementById('loadProductsBtn').addEventListener('click', loadProducts);

        async function loadProducts() {
            try {
                const response = await fetch('/api/write-through/products');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('productsTableBody');
                    tbody.innerHTML = '';
                    
                    data.data.forEach(product => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">€${product.price}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.stock}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button onclick="viewProduct(${product.id})" class="text-blue-600 hover:text-blue-900 mr-2">Vedi</button>
                                <button onclick="deleteProduct(${product.id})" class="text-red-600 hover:text-red-900">Elimina</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    document.getElementById('productsList').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Errore nel caricamento prodotti:', error);
            }
        }

        // Visualizza prodotto
        async function viewProduct(id) {
            try {
                const response = await fetch(`/api/write-through/products/${id}`);
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
            }
        }

        // Elimina prodotto
        async function deleteProduct(id) {
            if (!confirm('Sei sicuro di voler eliminare questo prodotto?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/write-through/products/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
                
                // Ricarica la lista prodotti
                loadProducts();
                
            } catch (error) {
                console.error('Errore:', error);
            }
        }
    </script>
</body>
</html>
