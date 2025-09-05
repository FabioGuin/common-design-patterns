<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caching Strategies Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <h1 class="text-3xl font-bold text-gray-900">Caching Strategies Demo</h1>
                    <div class="flex space-x-4">
                        <button @click="warmUpCache()" 
                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Warm Up Cache
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
            <div x-data="cachingDemo()" class="space-y-8">
                <!-- Cache Statistics -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Cache Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-medium text-blue-900">Cache Driver</h3>
                            <p class="text-blue-700" x-text="cacheStats.driver || 'Loading...'"></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-medium text-green-900">Memory Usage</h3>
                            <p class="text-green-700" x-text="formatBytes(cacheStats.memory_usage) || 'Loading...'"></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-medium text-purple-900">Peak Memory</h3>
                            <p class="text-purple-700" x-text="formatBytes(cacheStats.peak_memory) || 'Loading...'"></p>
                        </div>
                    </div>
                </div>

                <!-- Product Operations -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Get All Products -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Get All Products</h2>
                        <button @click="getAllProducts()" 
                                :disabled="loading.allProducts"
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!loading.allProducts">Load All Products</span>
                            <span x-show="loading.allProducts">Loading...</span>
                        </button>
                        <div x-show="results.allProducts" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.allProducts.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600 mb-2">Cached: <span x-text="results.allProducts.cached ? 'Yes' : 'No'"></span></p>
                                <p class="text-sm text-gray-600">Products Count: <span x-text="results.allProducts.data?.length || 0"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Get Featured Products -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Get Featured Products</h2>
                        <button @click="getFeaturedProducts()" 
                                :disabled="loading.featuredProducts"
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50">
                            <span x-show="!loading.featuredProducts">Load Featured Products</span>
                            <span x-show="loading.featuredProducts">Loading...</span>
                        </button>
                        <div x-show="results.featuredProducts" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.featuredProducts.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600 mb-2">Cached: <span x-text="results.featuredProducts.cached ? 'Yes' : 'No'"></span></p>
                                <p class="text-sm text-gray-600">Products Count: <span x-text="results.featuredProducts.data?.length || 0"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Search Products -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Search Products</h2>
                        <div class="flex space-x-2">
                            <input x-model="searchQuery" 
                                   type="text" 
                                   placeholder="Enter search query..."
                                   class="flex-1 border border-gray-300 rounded-md px-3 py-2">
                            <button @click="searchProducts()" 
                                    :disabled="loading.searchProducts || !searchQuery"
                                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 disabled:opacity-50">
                                Search
                            </button>
                        </div>
                        <div x-show="results.searchProducts" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.searchProducts.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600 mb-2">Cached: <span x-text="results.searchProducts.cached ? 'Yes' : 'No'"></span></p>
                                <p class="text-sm text-gray-600">Results Count: <span x-text="results.searchProducts.data?.length || 0"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Get Product Stats -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Get Product Statistics</h2>
                        <button @click="getProductStats()" 
                                :disabled="loading.productStats"
                                class="w-full bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 disabled:opacity-50">
                            <span x-show="!loading.productStats">Load Statistics</span>
                            <span x-show="loading.productStats">Loading...</span>
                        </button>
                        <div x-show="results.productStats" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.productStats.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600 mb-2">Cached: <span x-text="results.productStats.cached ? 'Yes' : 'No'"></span></p>
                                <div x-show="results.productStats.data" class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-600">Total Products: <span x-text="results.productStats.data.total_products"></span></p>
                                    <p class="text-sm text-gray-600">Featured Products: <span x-text="results.productStats.data.featured_products"></span></p>
                                    <p class="text-sm text-gray-600">Average Price: $<span x-text="results.productStats.data.average_price"></span></p>
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
                            <h3 class="font-medium text-blue-900">First Load (Cache Miss)</h3>
                            <p class="text-blue-700" x-text="performance.firstLoad + 'ms' || 'Not measured'"></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-medium text-green-900">Second Load (Cache Hit)</h3>
                            <p class="text-green-700" x-text="performance.secondLoad + 'ms' || 'Not measured'"></p>
                        </div>
                    </div>
                    <div x-show="performance.firstLoad && performance.secondLoad" class="mt-4">
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h3 class="font-medium text-yellow-900">Performance Improvement</h3>
                            <p class="text-yellow-700">
                                <span x-text="Math.round(((performance.firstLoad - performance.secondLoad) / performance.firstLoad) * 100)"></span>% faster with cache
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function cachingDemo() {
            return {
                loading: {
                    allProducts: false,
                    featuredProducts: false,
                    searchProducts: false,
                    productStats: false
                },
                results: {
                    allProducts: null,
                    featuredProducts: null,
                    searchProducts: null,
                    productStats: null
                },
                searchQuery: '',
                cacheStats: {},
                performance: {
                    firstLoad: null,
                    secondLoad: null
                },

                async init() {
                    await this.loadCacheStats();
                },

                async loadCacheStats() {
                    try {
                        const response = await fetch('/cache/stats');
                        const data = await response.json();
                        this.cacheStats = data.data;
                    } catch (error) {
                        console.error('Error loading cache stats:', error);
                    }
                },

                async getAllProducts() {
                    this.loading.allProducts = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch('/products');
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.allProducts = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                        
                        this.updatePerformance(endTime - startTime);
                    } catch (error) {
                        console.error('Error loading products:', error);
                    } finally {
                        this.loading.allProducts = false;
                    }
                },

                async getFeaturedProducts() {
                    this.loading.featuredProducts = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch('/products/featured');
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.featuredProducts = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                    } catch (error) {
                        console.error('Error loading featured products:', error);
                    } finally {
                        this.loading.featuredProducts = false;
                    }
                },

                async searchProducts() {
                    if (!this.searchQuery) return;
                    
                    this.loading.searchProducts = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch(`/products/search?q=${encodeURIComponent(this.searchQuery)}`);
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.searchProducts = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                    } catch (error) {
                        console.error('Error searching products:', error);
                    } finally {
                        this.loading.searchProducts = false;
                    }
                },

                async getProductStats() {
                    this.loading.productStats = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch('/products/stats');
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.productStats = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                    } catch (error) {
                        console.error('Error loading product stats:', error);
                    } finally {
                        this.loading.productStats = false;
                    }
                },

                async clearCache() {
                    try {
                        const response = await fetch('/cache/clear', { method: 'POST' });
                        const data = await response.json();
                        
                        if (data.success) {
                            alert('Cache cleared successfully');
                            await this.loadCacheStats();
                        }
                    } catch (error) {
                        console.error('Error clearing cache:', error);
                    }
                },

                async warmUpCache() {
                    try {
                        const response = await fetch('/cache/warm-up', { method: 'POST' });
                        const data = await response.json();
                        
                        if (data.success) {
                            alert('Cache warmed up successfully');
                            await this.loadCacheStats();
                        }
                    } catch (error) {
                        console.error('Error warming up cache:', error);
                    }
                },

                updatePerformance(responseTime) {
                    if (!this.performance.firstLoad) {
                        this.performance.firstLoad = responseTime;
                    } else if (!this.performance.secondLoad) {
                        this.performance.secondLoad = responseTime;
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
