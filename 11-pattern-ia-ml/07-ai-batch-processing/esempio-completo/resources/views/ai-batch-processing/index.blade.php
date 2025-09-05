<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Batch Processing Pattern - Esempio Completo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">AI Batch Processing Pattern</h1>
            <p class="text-gray-600">Sistema completo per l'elaborazione efficiente di grandi quantità di richieste AI</p>
        </div>

        <!-- Statistiche -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Batch Totali</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_batches'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Completati</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $statistics['completed_batches'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">In Elaborazione</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $statistics['processing_batches'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Falliti</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $statistics['failed_batches'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Azioni -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Azioni Rapide</h2>
            <div class="flex flex-wrap gap-4">
                <button 
                    @click="createSampleBatch()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Crea Batch di Esempio
                </button>
                <button 
                    @click="refreshBatches()"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Aggiorna Lista
                </button>
                <button 
                    @click="showCreateForm = !showCreateForm"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Crea Nuovo Batch
                </button>
            </div>
        </div>

        <!-- Form Creazione Batch -->
        <div x-show="showCreateForm" x-transition class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Crea Nuovo Batch</h3>
            <form @submit.prevent="createBatch()">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Provider AI</label>
                        <select x-model="newBatch.provider" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="openai">OpenAI</option>
                            <option value="claude">Claude</option>
                            <option value="gemini">Gemini</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Modello</label>
                        <input type="text" x-model="newBatch.model" placeholder="gpt-3.5-turbo" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Richieste (una per riga)</label>
                    <textarea 
                        x-model="newBatch.requests" 
                        rows="4" 
                        placeholder="Inserisci le richieste, una per riga..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" @click="showCreateForm = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Annulla
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Crea Batch
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista Batch -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Batch Recenti</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progresso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($batches as $batch)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $batch->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $batch->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($batch->status === 'completed') bg-green-100 text-green-800
                                    @elseif($batch->status === 'processing') bg-yellow-100 text-yellow-800
                                    @elseif($batch->status === 'failed') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $batch->getProgressPercentage() }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ $batch->getProgressPercentage() }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $batch->provider }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button 
                                        @click="getBatchStatus({{ $batch->id }})"
                                        class="text-blue-600 hover:text-blue-900">
                                        Dettagli
                                    </button>
                                    @if($batch->status === 'pending')
                                    <button 
                                        @click="processBatch({{ $batch->id }})"
                                        class="text-green-600 hover:text-green-900">
                                        Processa
                                    </button>
                                    @endif
                                    @if($batch->status === 'failed')
                                    <button 
                                        @click="retryBatch({{ $batch->id }})"
                                        class="text-yellow-600 hover:text-yellow-900">
                                        Riprova
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Dettagli Batch -->
    <div x-show="showDetails" x-transition class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Dettagli Batch</h3>
                    <button @click="showDetails = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div x-show="batchDetails" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ID Batch</label>
                            <p class="text-sm text-gray-900" x-text="batchDetails?.id"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stato</label>
                            <p class="text-sm text-gray-900" x-text="batchDetails?.status"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Progresso</label>
                            <p class="text-sm text-gray-900" x-text="batchDetails?.progress_percentage + '%'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provider</label>
                            <p class="text-sm text-gray-900" x-text="batchDetails?.provider"></p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Richieste</label>
                        <div class="mt-2 space-y-2 max-h-64 overflow-y-auto">
                            <template x-for="request in batchDetails?.requests" :key="request.id">
                                <div class="border rounded-lg p-3">
                                    <p class="text-sm text-gray-900" x-text="request.input"></p>
                                    <div class="mt-2 flex justify-between items-center">
                                        <span class="text-xs text-gray-500" x-text="'Stato: ' + request.status"></span>
                                        <span x-show="request.actual_output" class="text-xs text-green-600" x-text="'Output: ' + request.actual_output"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('batchProcessing', () => ({
                showCreateForm: false,
                showDetails: false,
                batchDetails: null,
                newBatch: {
                    provider: 'openai',
                    model: 'gpt-3.5-turbo',
                    requests: ''
                },

                async createSampleBatch() {
                    try {
                        const response = await fetch('/api/batch/sample', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            alert('Batch di esempio creato con successo!');
                            this.refreshBatches();
                        } else {
                            alert('Errore: ' + data.message);
                        }
                    } catch (error) {
                        alert('Errore nella creazione del batch: ' + error.message);
                    }
                },

                async createBatch() {
                    const requests = this.newBatch.requests.split('\n').filter(r => r.trim()).map(r => ({
                        input: r.trim(),
                        priority: 'normal'
                    }));

                    try {
                        const response = await fetch('/api/batch/create', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            },
                            body: JSON.stringify({
                                requests: requests,
                                provider: this.newBatch.provider,
                                model: this.newBatch.model
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            alert('Batch creato con successo!');
                            this.showCreateForm = false;
                            this.newBatch = { provider: 'openai', model: 'gpt-3.5-turbo', requests: '' };
                            this.refreshBatches();
                        } else {
                            alert('Errore: ' + data.message);
                        }
                    } catch (error) {
                        alert('Errore nella creazione del batch: ' + error.message);
                    }
                },

                async processBatch(batchId) {
                    try {
                        const response = await fetch(`/api/batch/${batchId}/process`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            alert('Batch processato con successo!');
                            this.refreshBatches();
                        } else {
                            alert('Errore: ' + data.message);
                        }
                    } catch (error) {
                        alert('Errore nel processing del batch: ' + error.message);
                    }
                },

                async retryBatch(batchId) {
                    try {
                        const response = await fetch(`/api/batch/${batchId}/retry`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            alert('Batch riavviato con successo!');
                            this.refreshBatches();
                        } else {
                            alert('Errore: ' + data.message);
                        }
                    } catch (error) {
                        alert('Errore nel riavvio del batch: ' + error.message);
                    }
                },

                async getBatchStatus(batchId) {
                    try {
                        const response = await fetch(`/api/batch/${batchId}/status`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.batchDetails = data.data;
                            this.showDetails = true;
                        } else {
                            alert('Errore: ' + data.message);
                        }
                    } catch (error) {
                        alert('Errore nel recupero dei dettagli: ' + error.message);
                    }
                },

                refreshBatches() {
                    window.location.reload();
                }
            }));
        });
    </script>
</body>
</html>
