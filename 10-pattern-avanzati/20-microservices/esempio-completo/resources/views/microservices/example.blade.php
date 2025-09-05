<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Microservices Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Microservices Pattern - Sistema E-commerce Distribuito
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test del Pattern -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test del Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern Microservices per verificare la comunicazione tra servizi.
                    </p>
                    
                    <div class="space-y-2">
                        <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                            Test Completo
                        </button>
                        
                        <button id="healthBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                            Health Check
                        </button>
                        
                        <button id="servicesBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 w-full">
                            Lista Servizi
                        </button>
                    </div>
                    
                    <div id="result" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="resultContent" class="text-sm overflow-auto max-h-96"></pre>
                    </div>
                </div>

                <!-- Servizi -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Servizi</h2>
                    
                    <div class="space-y-4">
                        <!-- User Service -->
                        <div class="border rounded p-4">
                            <h3 class="font-medium text-blue-600 mb-2">User Service</h3>
                            <div class="space-y-2">
                                <button onclick="testUserService()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                    Test
                                </button>
                                <button onclick="createUser()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Crea Utente
                                </button>
                            </div>
                        </div>
                        
                        <!-- Product Service -->
                        <div class="border rounded p-4">
                            <h3 class="font-medium text-green-600 mb-2">Product Service</h3>
                            <div class="space-y-2">
                                <button onclick="testProductService()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                    Test
                                </button>
                                <button onclick="createProduct()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Crea Prodotto
                                </button>
                            </div>
                        </div>
                        
                        <!-- Order Service -->
                        <div class="border rounded p-4">
                            <h3 class="font-medium text-yellow-600 mb-2">Order Service</h3>
                            <div class="space-y-2">
                                <button onclick="testOrderService()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                    Test
                                </button>
                                <button onclick="createOrder()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Crea Ordine
                                </button>
                            </div>
                        </div>
                        
                        <!-- Payment Service -->
                        <div class="border rounded p-4">
                            <h3 class="font-medium text-red-600 mb-2">Payment Service</h3>
                            <div class="space-y-2">
                                <button onclick="testPaymentService()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                    Test
                                </button>
                                <button onclick="processPayment()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Processa Pagamento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Gateway -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">API Gateway</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Routing -->
                    <div>
                        <h3 class="font-medium mb-2">Routing</h3>
                        <div class="space-y-2">
                            <button onclick="testRouting()" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Test Routing
                            </button>
                            <button onclick="getServicesStatus()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                Status Servizi
                            </button>
                        </div>
                    </div>
                    
                    <!-- Service Discovery -->
                    <div>
                        <h3 class="font-medium mb-2">Service Discovery</h3>
                        <div class="space-y-2">
                            <button onclick="listServices()" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Lista Servizi
                            </button>
                            <button onclick="getDiscoveryStats()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                Statistiche
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Architettura -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Architettura Microservices</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                    <!-- User Service -->
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-800 mb-2">User Service</h3>
                            <p class="text-sm text-blue-600">Gestione utenti</p>
                            <ul class="text-xs text-blue-600 mt-2 text-left">
                                <li>• Autenticazione</li>
                                <li>• Profili utenti</li>
                                <li>• Database: users</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Product Service -->
                    <div class="text-center">
                        <div class="bg-green-100 rounded-lg p-4">
                            <h3 class="font-semibold text-green-800 mb-2">Product Service</h3>
                            <p class="text-sm text-green-600">Catalogo prodotti</p>
                            <ul class="text-xs text-green-600 mt-2 text-left">
                                <li>• Gestione prodotti</li>
                                <li>• Inventario</li>
                                <li>• Database: products</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Order Service -->
                    <div class="text-center">
                        <div class="bg-yellow-100 rounded-lg p-4">
                            <h3 class="font-semibold text-yellow-800 mb-2">Order Service</h3>
                            <p class="text-sm text-yellow-600">Gestione ordini</p>
                            <ul class="text-xs text-yellow-600 mt-2 text-left">
                                <li>• Creazione ordini</li>
                                <li>• Gestione carrello</li>
                                <li>• Database: orders</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Payment Service -->
                    <div class="text-center">
                        <div class="bg-red-100 rounded-lg p-4">
                            <h3 class="font-semibold text-red-800 mb-2">Payment Service</h3>
                            <p class="text-sm text-red-600">Pagamenti</p>
                            <ul class="text-xs text-red-600 mt-2 text-left">
                                <li>• Processing pagamenti</li>
                                <li>• Rimborsi</li>
                                <li>• Database: payments</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- API Gateway -->
                    <div class="text-center">
                        <div class="bg-purple-100 rounded-lg p-4">
                            <h3 class="font-semibold text-purple-800 mb-2">API Gateway</h3>
                            <p class="text-sm text-purple-600">Routing e orchestrazione</p>
                            <ul class="text-xs text-purple-600 mt-2 text-left">
                                <li>• Routing richieste</li>
                                <li>• Load balancing</li>
                                <li>• Service discovery</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test del pattern
        document.getElementById('testBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/microservices/test');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Health check
        document.getElementById('healthBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/microservices/health');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Lista servizi
        document.getElementById('servicesBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/microservices/services');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Test User Service
        async function testUserService() {
            try {
                const response = await fetch('/api/microservices/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        name: 'Test User',
                        email: 'test@example.com',
                        password: 'password123'
                    })
                });
                
                const data = await response.json();
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Test Product Service
        async function testProductService() {
            try {
                const response = await fetch('/api/microservices/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        name: 'Test Product',
                        description: 'Test Description',
                        price: 99.99,
                        stock_quantity: 10
                    })
                });
                
                const data = await response.json();
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Test Order Service
        async function testOrderService() {
            try {
                const response = await fetch('/api/microservices/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        user_id: 'test_user_123',
                        items: [
                            { product_id: 'test_product_123', quantity: 2 }
                        ]
                    })
                });
                
                const data = await response.json();
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Test Payment Service
        async function testPaymentService() {
            try {
                const response = await fetch('/api/microservices/payments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        order_id: 'test_order_123',
                        amount: 199.99,
                        currency: 'EUR',
                        payment_method: 'card'
                    })
                });
                
                const data = await response.json();
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Crea utente
        async function createUser() {
            await testUserService();
        }

        // Crea prodotto
        async function createProduct() {
            await testProductService();
        }

        // Crea ordine
        async function createOrder() {
            await testOrderService();
        }

        // Processa pagamento
        async function processPayment() {
            await testPaymentService();
        }

        // Test routing
        async function testRouting() {
            try {
                const response = await fetch('/api/microservices/users');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Status servizi
        async function getServicesStatus() {
            try {
                const response = await fetch('/api/microservices/health');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Lista servizi
        async function listServices() {
            try {
                const response = await fetch('/api/microservices/services');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Statistiche discovery
        async function getDiscoveryStats() {
            try {
                const response = await fetch('/api/microservices/services');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
