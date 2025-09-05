@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Test Doubles Pattern Demo
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Dimostrazione pratica dei Test Doubles in Laravel: Mock, Stub, Fake e Spy
        </p>
    </div>

    <!-- Pattern Overview -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Tipi di Test Doubles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-semibold text-blue-900 mb-2">Mock</h3>
                <p class="text-sm text-blue-700">Verifica interazioni e comportamenti</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-semibold text-green-900 mb-2">Stub</h3>
                <p class="text-sm text-green-700">Fornisce risposte predefinite</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="font-semibold text-yellow-900 mb-2">Fake</h3>
                <p class="text-sm text-yellow-700">Implementazioni semplificate</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="font-semibold text-purple-900 mb-2">Spy</h3>
                <p class="text-sm text-purple-700">Registra chiamate per verifiche</p>
            </div>
        </div>
    </div>

    <!-- Demo Section -->
    <div class="bg-white rounded-lg shadow-md p-6" x-data="testDoublesDemo()">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Demo Interattiva</h2>
        
        <!-- Test Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <button 
                @click="testPayment()" 
                :disabled="loading"
                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium py-3 px-6 rounded-lg transition-colors"
            >
                <span x-show="!loading">Test Payment Service</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Testing...
                </span>
            </button>

            <button 
                @click="testNotification()" 
                :disabled="loading"
                class="bg-green-600 hover:bg-green-700 disabled:bg-green-300 text-white font-medium py-3 px-6 rounded-lg transition-colors"
            >
                <span x-show="!loading">Test Notification Service</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Testing...
                </span>
            </button>

            <button 
                @click="testOrderService()" 
                :disabled="loading"
                class="bg-purple-600 hover:bg-purple-700 disabled:bg-purple-300 text-white font-medium py-3 px-6 rounded-lg transition-colors"
            >
                <span x-show="!loading">Test Order Service</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Testing...
                </span>
            </button>
        </div>

        <!-- Results -->
        <div x-show="results.length > 0" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Risultati dei Test</h3>
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

        <!-- Code Examples -->
        <div class="mt-8 space-y-6">
            <h3 class="text-lg font-semibold text-gray-900">Esempi di Codice</h3>
            
            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                <pre class="text-green-400 text-sm"><code>// Mock Object Example
$mockPaymentService = Mockery::mock(PaymentServiceInterface::class);
$mockPaymentService
    ->shouldReceive('processPayment')
    ->once()
    ->with($order, $paymentData)
    ->andReturn(true);

// Stub Object Example
$stubRepository = Mockery::mock(OrderRepositoryInterface::class);
$stubRepository
    ->shouldReceive('create')
    ->andReturn($expectedOrder);

// Fake Object Example
Notification::fake();
Mail::fake();

// Spy Object Example
$spyLogger = Mockery::spy(LoggerInterface::class);
$spyLogger->shouldHaveReceived('info')
    ->with('Order created', ['order_id' => 123]);</code></pre>
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Vantaggi dei Test Doubles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Isolamento</h3>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Test indipendenti da servizi esterni
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Controllo completo del comportamento
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Test deterministici e riproducibili
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Performance</h3>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Esecuzione rapida senza I/O
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Basso consumo di memoria
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Facilmente scalabili per test complessi
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function testDoublesDemo() {
    return {
        loading: false,
        results: [],
        
        async testPayment() {
            this.loading = true;
            try {
                const response = await fetch('/test-payment');
                const data = await response.json();
                
                this.results.unshift({
                    title: 'Payment Service Test',
                    success: data.payment_result,
                    message: data.payment_result ? 
                        'Payment processed successfully' : 
                        'Payment failed',
                    details: JSON.stringify(data, null, 2)
                });
            } catch (error) {
                this.results.unshift({
                    title: 'Payment Service Test',
                    success: false,
                    message: 'Test failed: ' + error.message,
                    details: null
                });
            } finally {
                this.loading = false;
            }
        },
        
        async testNotification() {
            this.loading = true;
            try {
                const response = await fetch('/test-notification');
                const data = await response.json();
                
                this.results.unshift({
                    title: 'Notification Service Test',
                    success: data.email_sent && data.sms_sent,
                    message: `Email: ${data.email_sent ? 'Sent' : 'Failed'}, SMS: ${data.sms_sent ? 'Sent' : 'Failed'}`,
                    details: JSON.stringify(data, null, 2)
                });
            } catch (error) {
                this.results.unshift({
                    title: 'Notification Service Test',
                    success: false,
                    message: 'Test failed: ' + error.message,
                    details: null
                });
            } finally {
                this.loading = false;
            }
        },
        
        async testOrderService() {
            this.loading = true;
            try {
                const response = await fetch('/test-order-service');
                const data = await response.json();
                
                this.results.unshift({
                    title: 'Order Service Test',
                    success: data.order_created,
                    message: data.order_created ? 
                        `Order created successfully (ID: ${data.order_id})` : 
                        'Order creation failed',
                    details: JSON.stringify(data, null, 2)
                });
            } catch (error) {
                this.results.unshift({
                    title: 'Order Service Test',
                    success: false,
                    message: 'Test failed: ' + error.message,
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
