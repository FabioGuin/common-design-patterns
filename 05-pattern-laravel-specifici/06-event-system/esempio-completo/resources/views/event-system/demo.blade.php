@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Event System Pattern Demo
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Dimostrazione pratica del sistema di eventi di Laravel per gestire notifiche, logging e azioni asincrone
        </p>
    </div>

    <!-- Event Overview -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Eventi Disponibili</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-semibold text-blue-900 mb-2">User Events</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• UserRegistered</li>
                    <li>• UserLoggedIn</li>
                    <li>• UserProfileUpdated</li>
                </ul>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-semibold text-green-900 mb-2">Order Events</h3>
                <ul class="text-sm text-green-700 space-y-1">
                    <li>• OrderCreated</li>
                    <li>• OrderPaid</li>
                    <li>• OrderShipped</li>
                    <li>• OrderDelivered</li>
                </ul>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="font-semibold text-purple-900 mb-2">Listeners</h3>
                <ul class="text-sm text-purple-700 space-y-1">
                    <li>• SendWelcomeEmail</li>
                    <li>• LogUserActivity</li>
                    <li>• SendOrderConfirmation</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Demo Section -->
    <div class="bg-white rounded-lg shadow-md p-6" x-data="eventSystemDemo()">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Demo Interattiva</h2>
        
        <!-- Demo Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <button 
                @click="demoUserRegistration()" 
                :disabled="loading"
                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium py-3 px-6 rounded-lg transition-colors"
            >
                <span x-show="!loading">Demo User Registration</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>

            <button 
                @click="demoUserLogin()" 
                :disabled="loading || !demoUser"
                class="bg-green-600 hover:bg-green-700 disabled:bg-green-300 text-white font-medium py-3 px-6 rounded-lg transition-colors"
            >
                <span x-show="!loading">Demo User Login</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>

            <button 
                @click="demoOrderCreation()" 
                :disabled="loading || !demoUser"
                class="bg-purple-600 hover:bg-purple-700 disabled:bg-purple-300 text-white font-medium py-3 px-6 rounded-lg transition-colors"
            >
                <span x-show="!loading">Demo Order Creation</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>

            <button 
                @click="demoOrderPayment()" 
                :disabled="loading || !demoOrder"
                class="bg-orange-600 hover:bg-orange-700 disabled:bg-orange-300 text-white font-medium py-3 px-6 rounded-lg transition-colors"
            >
                <span x-show="!loading">Demo Order Payment</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>
        </div>

        <!-- Results -->
        <div x-show="results.length > 0" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Risultati delle Demo</h3>
            <div class="space-y-3">
                <template x-for="(result, index) in results" :key="index">
                    <div class="border rounded-lg p-4" :class="result.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium" :class="result.success ? 'text-green-900' : 'text-red-900'" x-text="result.title"></h4>
                                <p class="text-sm mt-1" :class="result.success ? 'text-green-700' : 'text-red-700'" x-text="result.message"></p>
                            </div>
                            <div class="flex items-center">
                                <svg x-show="result.success" class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="!result.success" class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div x-show="result.details" class="mt-2 text-xs font-mono bg-gray-100 p-2 rounded" x-text="result.details"></div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Event Flow Diagram -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Flusso degli Eventi</h3>
            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                <pre class="text-green-400 text-sm"><code>// User Registration Flow
User::create() → UserRegistered Event → SendWelcomeEmail Listener
                                    → LogUserActivity Listener

// User Login Flow  
User->handleLogin() → UserLoggedIn Event → LogUserActivity Listener

// Order Creation Flow
Order::create() → OrderCreated Event → SendOrderConfirmation Listener

// Order Payment Flow
Order->markAsPaid() → OrderPaid Event → SendOrderConfirmation Listener</code></pre>
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Vantaggi del Sistema di Eventi</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Decoupling</h3>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Separazione tra business logic e side effects
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Facile aggiunta di nuove funzionalità
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Testabilità migliorata
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Scalabilità</h3>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Esecuzione asincrona dei listener
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Gestione di operazioni costose
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Broadcasting in tempo reale
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function eventSystemDemo() {
    return {
        loading: false,
        demoUser: null,
        demoOrder: null,
        results: [],
        
        async demoUserRegistration() {
            this.loading = true;
            try {
                const response = await fetch('/demo/register');
                const data = await response.json();
                
                this.demoUser = { id: data.user_id };
                
                this.results.unshift({
                    title: 'User Registration Demo',
                    success: true,
                    message: `User created with ID: ${data.user_id}. UserRegistered event fired.`,
                    details: JSON.stringify(data, null, 2)
                });
            } catch (error) {
                this.results.unshift({
                    title: 'User Registration Demo',
                    success: false,
                    message: 'Demo failed: ' + error.message,
                    details: null
                });
            } finally {
                this.loading = false;
            }
        },
        
        async demoUserLogin() {
            if (!this.demoUser) {
                this.results.unshift({
                    title: 'User Login Demo',
                    success: false,
                    message: 'Please create a user first',
                    details: null
                });
                return;
            }
            
            this.loading = true;
            try {
                const response = await fetch(`/demo/login/${this.demoUser.id}`);
                const data = await response.json();
                
                this.results.unshift({
                    title: 'User Login Demo',
                    success: true,
                    message: `User ${this.demoUser.id} logged in. UserLoggedIn event fired.`,
                    details: JSON.stringify(data, null, 2)
                });
            } catch (error) {
                this.results.unshift({
                    title: 'User Login Demo',
                    success: false,
                    message: 'Demo failed: ' + error.message,
                    details: null
                });
            } finally {
                this.loading = false;
            }
        },
        
        async demoOrderCreation() {
            if (!this.demoUser) {
                this.results.unshift({
                    title: 'Order Creation Demo',
                    success: false,
                    message: 'Please create a user first',
                    details: null
                });
                return;
            }
            
            this.loading = true;
            try {
                const response = await fetch(`/demo/order/${this.demoUser.id}`);
                const data = await response.json();
                
                this.demoOrder = { id: data.order_id };
                
                this.results.unshift({
                    title: 'Order Creation Demo',
                    success: true,
                    message: `Order created with ID: ${data.order_id}. OrderCreated event fired.`,
                    details: JSON.stringify(data, null, 2)
                });
            } catch (error) {
                this.results.unshift({
                    title: 'Order Creation Demo',
                    success: false,
                    message: 'Demo failed: ' + error.message,
                    details: null
                });
            } finally {
                this.loading = false;
            }
        },
        
        async demoOrderPayment() {
            if (!this.demoOrder) {
                this.results.unshift({
                    title: 'Order Payment Demo',
                    success: false,
                    message: 'Please create an order first',
                    details: null
                });
                return;
            }
            
            this.loading = true;
            try {
                const response = await fetch(`/demo/payment/${this.demoOrder.id}`);
                const data = await response.json();
                
                this.results.unshift({
                    title: 'Order Payment Demo',
                    success: true,
                    message: `Order ${this.demoOrder.id} payment processed. OrderPaid event fired.`,
                    details: JSON.stringify(data, null, 2)
                });
            } catch (error) {
                this.results.unshift({
                    title: 'Order Payment Demo',
                    success: false,
                    message: 'Demo failed: ' + error.message,
                    details: null
                });
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
