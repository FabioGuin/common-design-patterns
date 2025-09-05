<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lazy Loading Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <h1 class="text-3xl font-bold text-gray-900">Lazy Loading Demo</h1>
                    <div class="flex space-x-4">
                        <button @click="getLazyLoadingStats()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Stats
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
            <div x-data="lazyLoadingDemo()" class="space-y-8">
                <!-- Lazy Loading Statistics -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Lazy Loading Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-medium text-blue-900">Loaded Objects</h3>
                            <p class="text-blue-700" x-text="lazyStats.loaded_objects || 'Loading...'"></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-medium text-green-900">Average Loading Time</h3>
                            <p class="text-green-700" x-text="(lazyStats.average_loading_time * 1000).toFixed(2) + 'ms' || 'Loading...'"></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-medium text-purple-900">Memory Usage</h3>
                            <p class="text-purple-700" x-text="formatBytes(lazyStats.memory_usage) || 'Loading...'"></p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h3 class="font-medium text-orange-900">Peak Memory</h3>
                            <p class="text-orange-700" x-text="formatBytes(lazyStats.peak_memory) || 'Loading...'"></p>
                        </div>
                    </div>
                </div>

                <!-- Lazy Loading Operations -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Lazy User Loading -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Lazy User Loading</h2>
                        <form @submit.prevent="loadLazyUser()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">User ID</label>
                                <input x-model="userForm.id" 
                                       type="number" 
                                       min="1" 
                                       max="100"
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <button type="submit" 
                                    :disabled="loading.lazyUser"
                                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                                <span x-show="!loading.lazyUser">Load User</span>
                                <span x-show="loading.lazyUser">Loading...</span>
                            </button>
                        </form>
                        <div x-show="results.lazyUser" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.lazyUser.response_time_ms + 'ms'"></span></p>
                                <p class="text-sm text-gray-600 mb-2">Lazy Loaded: <span x-text="results.lazyUser.lazy_loaded ? 'Yes' : 'No'"></span></p>
                                <div x-show="results.lazyUser.data" class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-600">Name: <span x-text="results.lazyUser.data.name"></span></p>
                                    <p class="text-sm text-gray-600">Email: <span x-text="results.lazyUser.data.email"></span></p>
                                    <p class="text-sm text-gray-600">Orders: <span x-text="results.lazyUser.data.statistics?.orders_count || 0"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lazy Product Loading -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Lazy Product Loading</h2>
                        <form @submit.prevent="loadLazyProduct()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Product ID</label>
                                <input x-model="productForm.id" 
                                       type="number" 
                                       min="1" 
                                       max="1000"
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <button type="submit" 
                                    :disabled="loading.lazyProduct"
                                    class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50">
                                <span x-show="!loading.lazyProduct">Load Product</span>
                                <span x-show="loading.lazyProduct">Loading...</span>
                            </button>
                        </form>
                        <div x-show="results.lazyProduct" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.lazyProduct.response_time_ms + 'ms'"></span></p>
                                <p class="text-sm text-gray-600 mb-2">Lazy Loaded: <span x-text="results.lazyProduct.lazy_loaded ? 'Yes' : 'No'"></span></p>
                                <div x-show="results.lazyProduct.data" class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-600">Name: <span x-text="results.lazyProduct.data.name"></span></p>
                                    <p class="text-sm text-gray-600">Price: $<span x-text="results.lazyProduct.data.price"></span></p>
                                    <p class="text-sm text-gray-600">Stock: <span x-text="results.lazyProduct.data.inventory?.stock || 0"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lazy Order Loading -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Lazy Order Loading</h2>
                        <form @submit.prevent="loadLazyOrder()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Order ID</label>
                                <input x-model="orderForm.id" 
                                       type="number" 
                                       min="1" 
                                       max="1000"
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <button type="submit" 
                                    :disabled="loading.lazyOrder"
                                    class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 disabled:opacity-50">
                                <span x-show="!loading.lazyOrder">Load Order</span>
                                <span x-show="loading.lazyOrder">Loading...</span>
                            </button>
                        </form>
                        <div x-show="results.lazyOrder" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.lazyOrder.response_time_ms + 'ms'"></span></p>
                                <p class="text-sm text-gray-600 mb-2">Lazy Loaded: <span x-text="results.lazyOrder.lazy_loaded ? 'Yes' : 'No'"></span></p>
                                <div x-show="results.lazyOrder.data" class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-600">Status: <span x-text="results.lazyOrder.data.status"></span></p>
                                    <p class="text-sm text-gray-600">Total: $<span x-text="results.lazyOrder.data.total"></span></p>
                                    <p class="text-sm text-gray-600">Items: <span x-text="results.lazyOrder.data.items?.length || 0"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Test -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Performance Test</h2>
                    <form @submit.prevent="runPerformanceTest()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Iterations</label>
                            <input x-model="performanceForm.iterations" 
                                   type="number" 
                                   min="1" 
                                   max="100"
                                   value="10"
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <button type="submit" 
                                :disabled="loading.performanceTest"
                                class="w-full bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 disabled:opacity-50">
                            <span x-show="!loading.performanceTest">Run Performance Test</span>
                            <span x-show="loading.performanceTest">Running...</span>
                        </button>
                    </form>
                    <div x-show="results.performanceTest" class="mt-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Average Response Time: <span x-text="results.performanceTest.average_response_time_ms + 'ms'"></span></p>
                            <p class="text-sm text-gray-600 mb-2">Total Objects Loaded: <span x-text="results.performanceTest.total_objects_loaded"></span></p>
                            <div x-show="results.performanceTest.results" class="mt-2 max-h-40 overflow-y-auto">
                                <div class="space-y-1">
                                    <template x-for="result in results.performanceTest.results" :key="result.iteration">
                                        <div class="text-xs text-gray-600">
                                            Iteration <span x-text="result.iteration"></span>: 
                                            <span x-text="result.response_time_ms"></span>ms, 
                                            <span x-text="result.objects_loaded"></span> objects
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lazy Loading Benefits -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Lazy Loading Benefits</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-medium text-blue-900">Memory Efficiency</h3>
                            <p class="text-blue-700 text-sm">Objects are loaded only when needed, reducing memory usage</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-medium text-green-900">Faster Initialization</h3>
                            <p class="text-green-700 text-sm">Application starts faster by deferring expensive operations</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-medium text-purple-900">On-Demand Loading</h3>
                            <p class="text-purple-700 text-sm">Data is loaded only when actually accessed by the user</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function lazyLoadingDemo() {
            return {
                loading: {
                    lazyUser: false,
                    lazyProduct: false,
                    lazyOrder: false,
                    performanceTest: false
                },
                results: {
                    lazyUser: null,
                    lazyProduct: null,
                    lazyOrder: null,
                    performanceTest: null
                },
                userForm: {
                    id: 1
                },
                productForm: {
                    id: 1
                },
                orderForm: {
                    id: 1
                },
                performanceForm: {
                    iterations: 10
                },
                lazyStats: {},

                async init() {
                    await this.getLazyLoadingStats();
                },

                async getLazyLoadingStats() {
                    try {
                        const response = await fetch('/lazy/stats');
                        const data = await response.json();
                        this.lazyStats = data.data;
                    } catch (error) {
                        console.error('Error loading lazy loading stats:', error);
                    }
                },

                async loadLazyUser() {
                    this.loading.lazyUser = true;
                    
                    try {
                        const response = await fetch(`/lazy/user/${this.userForm.id}`);
                        const data = await response.json();
                        this.results.lazyUser = data;
                    } catch (error) {
                        console.error('Error loading lazy user:', error);
                    } finally {
                        this.loading.lazyUser = false;
                    }
                },

                async loadLazyProduct() {
                    this.loading.lazyProduct = true;
                    
                    try {
                        const response = await fetch(`/lazy/product/${this.productForm.id}`);
                        const data = await response.json();
                        this.results.lazyProduct = data;
                    } catch (error) {
                        console.error('Error loading lazy product:', error);
                    } finally {
                        this.loading.lazyProduct = false;
                    }
                },

                async loadLazyOrder() {
                    this.loading.lazyOrder = true;
                    
                    try {
                        const response = await fetch(`/lazy/order/${this.orderForm.id}`);
                        const data = await response.json();
                        this.results.lazyOrder = data;
                    } catch (error) {
                        console.error('Error loading lazy order:', error);
                    } finally {
                        this.loading.lazyOrder = false;
                    }
                },

                async runPerformanceTest() {
                    this.loading.performanceTest = true;
                    
                    try {
                        const response = await fetch('/lazy/test-performance', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(this.performanceForm)
                        });
                        const data = await response.json();
                        this.results.performanceTest = data.data;
                    } catch (error) {
                        console.error('Error running performance test:', error);
                    } finally {
                        this.loading.performanceTest = false;
                    }
                },

                async clearCache() {
                    try {
                        const response = await fetch('/lazy/clear-cache', { method: 'POST' });
                        const data = await response.json();
                        
                        if (data.success) {
                            alert('Cache cleared successfully');
                            await this.getLazyLoadingStats();
                        }
                    } catch (error) {
                        console.error('Error clearing cache:', error);
                    }
                },

                formatBytes(bytes) {
                    if (!bytes) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }
        }
    </script>
</body>
</html>
