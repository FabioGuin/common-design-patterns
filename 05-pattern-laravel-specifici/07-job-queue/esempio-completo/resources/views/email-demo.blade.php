@extends('layouts.app')

@section('title', 'Job Queue Demo - Sistema Email')

@section('content')
<div x-data="emailDemo()" class="space-y-8">
    <!-- Status Dashboard -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Dashboard Queue</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-medium text-blue-800">Job in Coda</h3>
                <p class="text-2xl font-bold text-blue-600" x-text="status.pending_jobs">0</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <h3 class="font-medium text-red-800">Job Falliti</h3>
                <p class="text-2xl font-bold text-red-600" x-text="status.failed_jobs">0</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-medium text-green-800">Utenti Totali</h3>
                <p class="text-2xl font-bold text-green-600" x-text="status.total_users">0</p>
            </div>
        </div>
        <button @click="refreshStatus()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Aggiorna Status
        </button>
    </div>

    <!-- User Registration -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Registrazione Utente</h2>
        <form @submit.prevent="registerUser()" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input type="text" x-model="newUser.name" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" x-model="newUser.email" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" :disabled="loading" 
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 disabled:opacity-50">
                <span x-show="!loading">Registra Utente</span>
                <span x-show="loading">Registrazione...</span>
            </button>
        </form>
    </div>

    <!-- Newsletter -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Invio Newsletter</h2>
        <form @submit.prevent="sendNewsletter()" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Oggetto</label>
                <input type="text" x-model="newsletter.subject" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Contenuto</label>
                <textarea x-model="newsletter.content" required rows="4"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <button type="submit" :disabled="loading" 
                    class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 disabled:opacity-50">
                <span x-show="!loading">Invia Newsletter</span>
                <span x-show="loading">Invio in corso...</span>
            </button>
        </form>
    </div>

    <!-- Notification -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Invio Notifica</h2>
        <form @submit.prevent="sendNotification()" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Utente</label>
                <select x-model="notification.user_id" required 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleziona utente</option>
                    <template x-for="user in users" :key="user.id">
                        <option :value="user.id" x-text="user.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Titolo</label>
                <input type="text" x-model="notification.title" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Messaggio</label>
                <textarea x-model="notification.message" required rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <button type="submit" :disabled="loading" 
                    class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 disabled:opacity-50">
                <span x-show="!loading">Invia Notifica</span>
                <span x-show="loading">Invio in corso...</span>
            </button>
        </form>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Utenti Registrati</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrato</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="user in users" :key="user.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="user.id"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="user.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="user.email"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="new Date(user.created_at).toLocaleString()"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Messages -->
    <div x-show="message" x-transition class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg">
        <span x-text="message"></span>
    </div>

    <div x-show="error" x-transition class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded shadow-lg">
        <span x-text="error"></span>
    </div>
</div>
@endsection

@section('scripts')
<script>
function emailDemo() {
    return {
        loading: false,
        message: '',
        error: '',
        users: @json($users),
        status: {
            pending_jobs: {{ $pendingJobs }},
            failed_jobs: {{ $failedJobs }},
            total_users: {{ $users->count() }}
        },
        newUser: {
            name: '',
            email: ''
        },
        newsletter: {
            subject: '',
            content: ''
        },
        notification: {
            user_id: '',
            title: '',
            message: ''
        },

        async registerUser() {
            this.loading = true;
            this.clearMessages();
            
            try {
                const response = await fetch('/email/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(this.newUser)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = data.message;
                    this.users.push(data.user);
                    this.newUser = { name: '', email: '' };
                    this.refreshStatus();
                } else {
                    this.error = data.message;
                }
            } catch (error) {
                this.error = 'Errore nella registrazione: ' + error.message;
            }
            
            this.loading = false;
        },

        async sendNewsletter() {
            this.loading = true;
            this.clearMessages();
            
            try {
                const response = await fetch('/email/newsletter', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(this.newsletter)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = data.message;
                    this.newsletter = { subject: '', content: '' };
                    this.refreshStatus();
                } else {
                    this.error = data.message;
                }
            } catch (error) {
                this.error = 'Errore nell\'invio newsletter: ' + error.message;
            }
            
            this.loading = false;
        },

        async sendNotification() {
            this.loading = true;
            this.clearMessages();
            
            try {
                const response = await fetch('/email/notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(this.notification)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = data.message;
                    this.notification = { user_id: '', title: '', message: '' };
                    this.refreshStatus();
                } else {
                    this.error = data.message;
                }
            } catch (error) {
                this.error = 'Errore nell\'invio notifica: ' + error.message;
            }
            
            this.loading = false;
        },

        async refreshStatus() {
            try {
                const response = await fetch('/email/status');
                const data = await response.json();
                
                if (data.success) {
                    this.status = data.data;
                }
            } catch (error) {
                console.error('Errore nel refresh status:', error);
            }
        },

        clearMessages() {
            this.message = '';
            this.error = '';
        }
    }
}
</script>
@endsection
