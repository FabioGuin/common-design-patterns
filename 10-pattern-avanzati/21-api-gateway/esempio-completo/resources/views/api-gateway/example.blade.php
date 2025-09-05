<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Gateway Pattern - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                API Gateway Pattern - Gateway Unificato per Servizi Multipli
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Test del Pattern -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Test del Pattern</h2>
                    <p class="text-gray-600 mb-4">
                        Testa il pattern API Gateway per verificare routing, autenticazione e autorizzazione.
                    </p>
                    
                    <div class="space-y-2">
                        <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                            Test Completo
                        </button>
                        
                        <button id="healthBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                            Health Check
                        </button>
                        
                        <button id="statsBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 w-full">
                            Statistiche
                        </button>
                    </div>
                    
                    <div id="result" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultato:</h3>
                        <pre id="resultContent" class="text-sm overflow-auto max-h-96"></pre>
                    </div>
                </div>

                <!-- Servizi -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Servizi Backend</h2>
                    
                    <div class="space-y-4">
                        <!-- User Service -->
                        <div class="border rounded p-4">
                            <h3 class="font-medium text-blue-600 mb-2">User Service</h3>
                            <div class="space-y-2">
                                <button onclick="testUserService()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                    Test
                                </button>
                                <button onclick="listUsers()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Lista Utenti
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
                                <button onclick="listProducts()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Lista Prodotti
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
                                <button onclick="listOrders()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Lista Ordini
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
                                <button onclick="listPayments()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Lista Pagamenti
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Funzionalità del Gateway -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Funzionalità del Gateway</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Autenticazione -->
                    <div>
                        <h3 class="font-medium mb-2">Autenticazione</h3>
                        <div class="space-y-2">
                            <button onclick="testAuth()" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Test Auth
                            </button>
                            <button onclick="testJWT()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                Test JWT
                            </button>
                        </div>
                    </div>
                    
                    <!-- Rate Limiting -->
                    <div>
                        <h3 class="font-medium mb-2">Rate Limiting</h3>
                        <div class="space-y-2">
                            <button onclick="testRateLimit()" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Test Rate Limit
                            </button>
                            <button onclick="getRateLimitStats()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                Statistiche
                            </button>
                        </div>
                    </div>
                    
                    <!-- Monitoring -->
                    <div>
                        <h3 class="font-medium mb-2">Monitoring</h3>
                        <div class="space-y-2">
                            <button onclick="getMetrics()" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Metriche
                            </button>
                            <button onclick="getLogs()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                                Logs
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Architettura -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Architettura API Gateway</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                    <!-- Client -->
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-800 mb-2">Client</h3>
                            <p class="text-sm text-blue-600">Applicazioni client</p>
                            <ul class="text-xs text-blue-600 mt-2 text-left">
                                <li>• Web App</li>
                                <li>• Mobile App</li>
                                <li>• Desktop App</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- API Gateway -->
                    <div class="text-center">
                        <div class="bg-purple-100 rounded-lg p-4">
                            <h3 class="font-semibold text-purple-800 mb-2">API Gateway</h3>
                            <p class="text-sm text-purple-600">Gateway unificato</p>
                            <ul class="text-xs text-purple-600 mt-2 text-left">
                                <li>• Routing</li>
                                <li>• Autenticazione</li>
                                <li>• Rate Limiting</li>
                                <li>• Caching</li>
                                <li>• Monitoring</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- User Service -->
                    <div class="text-center">
                        <div class="bg-green-100 rounded-lg p-4">
                            <h3 class="font-semibold text-green-800 mb-2">User Service</h3>
                            <p class="text-sm text-green-600">Gestione utenti</p>
                            <ul class="text-xs text-green-600 mt-2 text-left">
                                <li>• Autenticazione</li>
                                <li>• Profili</li>
                                <li>• Autorizzazione</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Product Service -->
                    <div class="text-center">
                        <div class="bg-yellow-100 rounded-lg p-4">
                            <h3 class="font-semibold text-yellow-800 mb-2">Product Service</h3>
                            <p class="text-sm text-yellow-600">Catalogo prodotti</p>
                            <ul class="text-xs text-yellow-600 mt-2 text-left">
                                <li>• Gestione prodotti</li>
                                <li>• Inventario</li>
                                <li>• Prezzi</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Order Service -->
                    <div class="text-center">
                        <div class="bg-red-100 rounded-lg p-4">
                            <h3 class="font-semibold text-red-800 mb-2">Order Service</h3>
                            <p class="text-sm text-red-600">Gestione ordini</p>
                            <ul class="text-xs text-red-600 mt-2 text-left">
                                <li>• Creazione ordini</li>
                                <li>• Gestione carrello</li>
                                <li>• Processing</li>
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
                const response = await fetch('/api/v1/gateway/test');
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
                const response = await fetch('/api/v1/gateway/health');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        });

        // Statistiche
        document.getElementById('statsBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/v1/gateway/stats');
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
                const response = await fetch('/api/v1/users');
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
                const response = await fetch('/api/v1/products');
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
                const response = await fetch('/api/v1/orders');
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
                const response = await fetch('/api/v1/payments');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Lista utenti
        async function listUsers() {
            await testUserService();
        }

        // Lista prodotti
        async function listProducts() {
            await testProductService();
        }

        // Lista ordini
        async function listOrders() {
            await testOrderService();
        }

        // Lista pagamenti
        async function listPayments() {
            await testPaymentService();
        }

        // Test autenticazione
        async function testAuth() {
            try {
                const response = await fetch('/api/v1/gateway/health');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Test JWT
        async function testJWT() {
            try {
                const response = await fetch('/api/v1/gateway/health');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Test rate limit
        async function testRateLimit() {
            try {
                const response = await fetch('/api/v1/gateway/health');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Statistiche rate limit
        async function getRateLimitStats() {
            try {
                const response = await fetch('/api/v1/gateway/stats');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Metriche
        async function getMetrics() {
            try {
                const response = await fetch('/api/v1/gateway/stats');
                const data = await response.json();
                
                document.getElementById('resultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('result').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('resultContent').textContent = 'Errore: ' + error.message;
                document.getElementById('result').classList.remove('hidden');
            }
        }

        // Logs
        async function getLogs() {
            try {
                const response = await fetch('/api/v1/gateway/stats');
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
