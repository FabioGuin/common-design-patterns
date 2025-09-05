<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Integration Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <h1 class="text-3xl font-bold text-gray-900">API Integration Demo</h1>
                    <div class="flex space-x-4">
                        <button @click="getApiStats()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            API Stats
                        </button>
                        <button @click="clearCache()" 
                                class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            Clear Cache
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div x-data="apiIntegrationDemo()" class="space-y-8">
                <!-- API Statistics -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">API Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-medium text-blue-900">Base URL</h3>
                            <p class="text-blue-700" x-text="apiStats.base_url || 'Loading...'"></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-medium text-green-900">Timeout</h3>
                            <p class="text-green-700" x-text="apiStats.timeout + 's' || 'Loading...'"></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-medium text-purple-900">Retry Attempts</h3>
                            <p class="text-purple-700" x-text="apiStats.retry_attempts || 'Loading...'"></p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h3 class="font-medium text-orange-900">Cache Enabled</h3>
                            <p class="text-orange-700" x-text="apiStats.cache_enabled ? 'Yes' : 'No'"></p>
                        </div>
                    </div>
                </div>

                <!-- API Operations -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Process Payment -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Process Payment</h2>
                        <form @submit.prevent="processPayment()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount</label>
                                <input x-model="paymentForm.amount" 
                                       type="number" 
                                       step="0.01" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Currency</label>
                                <select x-model="paymentForm.currency" 
                                        required
                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select x-model="paymentForm.payment_method" 
                                        required
                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="credit_card">Credit Card</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Customer ID</label>
                                <input x-model="paymentForm.customer_id" 
                                       type="text" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <button type="submit" 
                                    :disabled="loading.processPayment"
                                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                                <span x-show="!loading.processPayment">Process Payment</span>
                                <span x-show="loading.processPayment">Processing...</span>
                            </button>
                        </form>
                        <div x-show="results.processPayment" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.processPayment.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600">Status: <span x-text="results.processPayment.success ? 'Success' : 'Failed'"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Get Payment Status -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Get Payment Status</h2>
                        <form @submit.prevent="getPaymentStatus()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment ID</label>
                                <input x-model="paymentId" 
                                       type="text" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <button type="submit" 
                                    :disabled="loading.getPaymentStatus"
                                    class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50">
                                <span x-show="!loading.getPaymentStatus">Get Status</span>
                                <span x-show="loading.getPaymentStatus">Loading...</span>
                            </button>
                        </form>
                        <div x-show="results.getPaymentStatus" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.getPaymentStatus.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600">Status: <span x-text="results.getPaymentStatus.success ? 'Success' : 'Failed'"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Create Customer -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Create Customer</h2>
                        <form @submit.prevent="createCustomer()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input x-model="customerForm.name" 
                                       type="text" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input x-model="customerForm.email" 
                                       type="email" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input x-model="customerForm.phone" 
                                       type="text"
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <button type="submit" 
                                    :disabled="loading.createCustomer"
                                    class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 disabled:opacity-50">
                                <span x-show="!loading.createCustomer">Create Customer</span>
                                <span x-show="loading.createCustomer">Creating...</span>
                            </button>
                        </form>
                        <div x-show="results.createCustomer" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.createCustomer.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600">Status: <span x-text="results.createCustomer.success ? 'Success' : 'Failed'"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Get Payment Methods -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Get Payment Methods</h2>
                        <button @click="getPaymentMethods()" 
                                :disabled="loading.getPaymentMethods"
                                class="w-full bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 disabled:opacity-50">
                            <span x-show="!loading.getPaymentMethods">Get Payment Methods</span>
                            <span x-show="loading.getPaymentMethods">Loading...</span>
                        </button>
                        <div x-show="results.getPaymentMethods" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.getPaymentMethods.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600">Status: <span x-text="results.getPaymentMethods.success ? 'Success' : 'Failed'"></span></p>
                                <div x-show="results.getPaymentMethods.data" class="mt-2">
                                    <p class="text-sm text-gray-600">Methods: <span x-text="results.getPaymentMethods.data.length || 0"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Comparison -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Performance Comparison</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-medium text-blue-900">First Request (Cache Miss)</h3>
                            <p class="text-blue-700" x-text="performance.firstRequest + 'ms' || 'Not measured'"></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-medium text-green-900">Second Request (Cache Hit)</h3>
                            <p class="text-green-700" x-text="performance.secondRequest + 'ms' || 'Not measured'"></p>
                        </div>
                    </div>
                    <div x-show="performance.firstRequest && performance.secondRequest" class="mt-4">
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h3 class="font-medium text-yellow-900">Performance Improvement</h3>
                            <p class="text-yellow-700">
                                <span x-text="Math.round(((performance.firstRequest - performance.secondRequest) / performance.firstRequest) * 100)"></span>% faster with cache
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function apiIntegrationDemo() {
            return {
                loading: {
                    processPayment: false,
                    getPaymentStatus: false,
                    createCustomer: false,
                    getPaymentMethods: false
                },
                results: {
                    processPayment: null,
                    getPaymentStatus: null,
                    createCustomer: null,
                    getPaymentMethods: null
                },
                paymentForm: {
                    amount: 100.00,
                    currency: 'USD',
                    payment_method: 'credit_card',
                    customer_id: 'cust_123'
                },
                customerForm: {
                    name: 'John Doe',
                    email: 'john@example.com',
                    phone: '+1234567890'
                },
                paymentId: 'pay_123',
                apiStats: {},
                performance: {
                    firstRequest: null,
                    secondRequest: null
                },

                async init() {
                    await this.getApiStats();
                },

                async getApiStats() {
                    try {
                        const response = await fetch('/api/stats');
                        const data = await response.json();
                        this.apiStats = data.data;
                    } catch (error) {
                        console.error('Error loading API stats:', error);
                    }
                },

                async processPayment() {
                    this.loading.processPayment = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch('/payments', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.paymentForm)
                        });
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.processPayment = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                        
                        this.updatePerformance(endTime - startTime);
                    } catch (error) {
                        console.error('Error processing payment:', error);
                    } finally {
                        this.loading.processPayment = false;
                    }
                },

                async getPaymentStatus() {
                    this.loading.getPaymentStatus = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch(`/payments/${this.paymentId}`);
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.getPaymentStatus = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                    } catch (error) {
                        console.error('Error getting payment status:', error);
                    } finally {
                        this.loading.getPaymentStatus = false;
                    }
                },

                async createCustomer() {
                    this.loading.createCustomer = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch('/customers', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.customerForm)
                        });
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.createCustomer = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                    } catch (error) {
                        console.error('Error creating customer:', error);
                    } finally {
                        this.loading.createCustomer = false;
                    }
                },

                async getPaymentMethods() {
                    this.loading.getPaymentMethods = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch('/payments/methods');
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.getPaymentMethods = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                    } catch (error) {
                        console.error('Error getting payment methods:', error);
                    } finally {
                        this.loading.getPaymentMethods = false;
                    }
                },

                async clearCache() {
                    try {
                        const response = await fetch('/api/cache/clear', { method: 'POST' });
                        const data = await response.json();
                        
                        if (data.success) {
                            alert('Cache cleared successfully');
                            await this.getApiStats();
                        }
                    } catch (error) {
                        console.error('Error clearing cache:', error);
                    }
                },

                updatePerformance(responseTime) {
                    if (!this.performance.firstRequest) {
                        this.performance.firstRequest = responseTime;
                    } else if (!this.performance.secondRequest) {
                        this.performance.secondRequest = responseTime;
                    }
                }
            }
        }
    </script>
</body>
</html>
