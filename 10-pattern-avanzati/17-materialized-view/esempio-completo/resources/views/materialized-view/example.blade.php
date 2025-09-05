<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materialized View Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Materialized View Pattern - Dashboard Vendite
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Test del Pattern -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test del Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern Materialized View per verificare le performance e la funzionalità.
                    </p>
                    
                    <div class="space-y-2">
                        <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                            Test Completo
                        </button>
                        
                        <button id="createDataBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                            Crea Dati Test
                        </button>
                        
                        <button id="performanceBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 w-full">
                            Confronto Performance
                        </button>
                        
                        <button id="refreshBtn" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 w-full">
                            Aggiorna Viste
                        </button>
                    </div>
                    
                    <div id="result" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="resultContent" class="text-sm overflow-auto max-h-96"></pre>
                    </div>
                </div>

                <!-- Report Vendite per Categoria -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Vendite per Categoria</h2>
                    
                    <button id="loadCategoryBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 mb-4">
                        Carica Dati
                    </button>
                    
                    <div id="categoryData" class="hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vendite</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ordini</th>
                                    </tr>
                                </thead>
                                <tbody id="categoryTableBody" class="bg-white divide-y divide-gray-200">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top Prodotti -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Top Prodotti</h2>
                    
                    <button id="loadProductsBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 mb-4">
                        Carica Dati
                    </button>
                    
                    <div id="productsData" class="hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prodotto</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vendite</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stato delle Viste -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Stato delle Viste Materializzate</h2>
                
                <button id="statusBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 mb-4">
                    Aggiorna Stato
                </button>
                
                <div id="statusData" class="hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vista</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Righe</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ultimo Aggiornamento</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequenza</th>
                                </tr>
                            </thead>
                            <tbody id="statusTableBody" class="bg-white divide-y divide-gray-200">
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
                const response = await fetch('/api/materialized-view/test');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Crea dati di test
        document.getElementById('createDataBtn').addEventListener('click', async function() {
            if (!confirm('Questo creerà 100 ordini con prodotti e categorie. Continuare?')) {
                return;
            }
            
            try {
                const response = await fetch('/api/materialized-view/create-test-data', {
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

        // Confronto performance
        document.getElementById('performanceBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/materialized-view/performance-comparison');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Aggiorna viste
        document.getElementById('refreshBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/materialized-view/refresh', {
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

        // Carica dati categorie
        document.getElementById('loadCategoryBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/materialized-view/reports/sales-by-category');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('categoryTableBody');
                    tbody.innerHTML = '';
                    
                    data.data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-4 py-2 text-sm text-gray-900">${item.category_name || 'N/A'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">€${parseFloat(item.total_sales || 0).toFixed(2)}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${item.total_orders || 0}</td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    document.getElementById('categoryData').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Errore nel caricamento categorie:', error);
            }
        });

        // Carica dati prodotti
        document.getElementById('loadProductsBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/materialized-view/reports/top-products?limit=10');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('productsTableBody');
                    tbody.innerHTML = '';
                    
                    data.data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-4 py-2 text-sm text-gray-900">${item.product_name || 'N/A'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${item.category_name || 'N/A'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">€${parseFloat(item.total_sales || 0).toFixed(2)}</td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    document.getElementById('productsData').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Errore nel caricamento prodotti:', error);
            }
        });

        // Carica stato viste
        document.getElementById('statusBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/materialized-view/status');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('statusTableBody');
                    tbody.innerHTML = '';
                    
                    Object.values(data.data).forEach(view => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-4 py-2 text-sm text-gray-900">${view.view_name || 'N/A'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${view.row_count || 0}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${view.last_updated || 'Never'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${view.refresh_frequency || 'N/A'}</td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    document.getElementById('statusData').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Errore nel caricamento stato:', error);
            }
        });
    </script>
</body>
</html>
